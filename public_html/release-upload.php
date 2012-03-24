<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2006 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/
@session_start();
$csrf_token_name = 'pear_csrf_token_' . basename(__FILE__, '.php');

auth_require('pear.dev');

define('HTML_FORM_MAX_FILE_SIZE', 16 * 1024 * 1024); // 16 MB

$display_form         = true;
$display_verification = false;
$success              = false;
$errors               = array();

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

do {
    if (isset($_POST['upload'])) {
        if (!validate_csrf_token($csrf_token_name)) {
            $errors[] = 'Invalid token.';
            break;
        }

        // Upload Button
        include_once 'HTTP/Upload.php';
        $upload_obj = new HTTP_Upload('en');
        $file = $upload_obj->getFiles('distfile');
        if (PEAR::isError($file)) {
            $errors[] = $file->getMessage();
            $log->err($file->getMessage() . "\n");
            $log->debug(print_r($file, true));
            break;
        }

        if ($file->isValid()) {
            $file->setName('uniq', 'pear-');
            $file->setValidExtensions('tgz', 'accept');
            $tmpfile = $file->moveTo(PEAR_UPLOAD_TMPDIR);
            if (PEAR::isError($tmpfile)) {
                $errors[] = $tmpfile->getMessage();
                $log->err("Failed to move uploaded file to " . PEAR_UPLOAD_TMPDIR);
                $log->err($tmpfile->getMessage());
                $log->debug(print_r($tmpfile, true));
                break;
            }
            $tmpsize = $file->getProp('size');
        } elseif ($file->isMissing()) {
            $errors[] = 'No file has been uploaded.';
            break;
        } elseif ($file->isError()) {
            $errors[] = $file->errorMsg();
            $log->error($file->errorMsg());
            $log->debug(print_r($file, true));
            break;
        }

        $display_form = false;
        $display_verification = true;
    } elseif (isset($_POST['verify'])) {
        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']);
        if (!is_file($distfile)) {
            $errors[] = 'No verified file found.';
            break;
        }

        set_time_limit(60);
        include_once 'PEAR/Config.php';
        include_once 'PEAR/PackageFile.php';
        // Verify Button
        $config = &PEAR_Config::singleton();
        $pkg    = &new PEAR_PackageFile($config);
        $info   = &$pkg->fromTgzFile($distfile, PEAR_VALIDATE_NORMAL);
        if (PEAR::isError($info)) {
            if (is_array($info->getUserInfo())) {
                foreach ($info->getUserInfo() as $err) {
                    $errors[] = $err['message'];
                }
            }
            $errors[] = $info->getMessage();
            break;
        } else {
            if ($info->getPackageXmlVersion() == '1.0') {
                $errors[] = 'Only packages using package.xml version 2.0 or newer may be' .
                ' released - use the "pear convert" command to create a new package.xml';
                break;
            }

            $tar = &new Archive_Tar($distfile);
            if ($packagexml = $tar->extractInString('package2.xml')) {
                $compatible_pxml = true;
            } else {
                $compatible_pxml = false;
                $packagexml = $tar->extractInString('package.xml');
            }
            if ($packagexml === null) {
                $errors[] = 'No package.xml found in this release';
                break;
            }

            include_once 'pear-database-package.php';
            $pacid = package::info($info->getPackage(), 'id');
            if (PEAR::isError($pacid)) {
                $errors[] = $pacid->getMessage();
                break;
            }

            include_once 'pear-database-user.php';
            if (
                !auth_check('pear.admin') &&
                !auth_check('pear.qa') &&
                !user::maintains($auth_user->handle, $pacid, 'lead')
            ) {
                $errors[] = 'You don\'t have permissions to upload this release.';
                break;
            }
            $license = $info->getLicense();
            if (is_array($license)) {
                $license = $license['_content'];
            }
            $users = array();
            foreach ($info->getMaintainers() as $user) {
                if (!user::exists($user['handle'])) {
                    $errors[] = 'Unknown user: ' . $user['handle'];
                    continue;
                }

                $users[strtolower($user['handle'])] = array(
                    'role'   => $user['role'],
                    'active' => !isset($user['active']) || $user['active'] == 'yes',
                );
            }

            include_once 'pear-database-maintainer.php';
            $e = maintainer::updateAll($pacid, $users, false, true);
            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();
                break;
            }
            $e = package::updateInfo($pacid,
                    array(
                        'summary'     => $info->getSummary(),
                        'description' => $info->getDescription(),
                        'license'     => $license,
                    ));
            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();
                break;
            }

            include_once 'pear-rest.php';
            $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
            $return = $pear_rest->savePackageMaintainerREST($info->getPackage());
            if (PEAR::isError($return)) {
                if (auth_check('pear.admin')) {
                    $errors[] = $return->getMessage();
                } else {
                    $errors[] = 'There seems to have been a problem with saving the REST files - please inform the webmasters at ' . PEAR_WEBMASTER_EMAIL;
                }
            }

            include_once 'pear-database-release.php';
            $file = release::upload($info->getPackage(), $info->getVersion(),
                                    $info->getState(), $info->getNotes(),
                                    $distfile, md5_file($distfile), $info, $packagexml,
                                    $compatible_pxml);
        }

        if (PEAR::isError($file)) {
            $ui = $file->getUserInfo();
            $errors[] = 'Error while uploading package: ' .
                         $file->getMessage() . ($ui ? " ($ui)" : '');
            break;
        }
        @unlink($distfile);

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');
        include_once 'pear-database-release.php';
        release::promote($info, $file);
        PEAR::popErrorHandling();

        // Roadmap thingo
        require_once 'roadmap/info.php';

        $sql = '
            SELECT b.id, b.sdesc, b.email, b.reporter_name, b.bug_type, b.handle
            FROM
                bugdb b, bugdb_roadmap_link l, bugdb_roadmap r
            WHERE
                r.package = ? AND
                r.roadmap_version = ? AND
                l.roadmap_id = r.id AND
                b.id = l.id AND
                b.status = ?
            ORDER BY b.bug_type, b.id';

        $values = array($info->getPackage(), $info->getVersion(), 'Closed');
        $bugs   = $GLOBALS['dbh']->getAll($sql , $values, DB_FETCHMODE_ASSOC);

        $sql = 'SELECT m.handle FROM maintains m, packages p WHERE p.id = m.package AND p.name = ?';
        $m   = $dbh->getCol($sql, 0, $info->getPackage());

        $bug_types = array('Bug', 'Documentation Bug');
        $notes = array();
        foreach ($bugs as $bug) {
            // Ignoring bugs maintainers reported
            if (in_array($bug['handle'], $m)) {
                continue;
            }

            if (!isset($notes[$bug['email']]['note'])) {
                $notes[$bug['email']]['note'] = '';
            }

            $type = in_array($bug['bug_type'], $bug_types) ? 'bugs' : 'features';
            if (!isset($notes[$bug['email']][$type])) {
                $notes[$bug['email']][$type] = '';
            }

            $summary = wordwrap($bug['sdesc'], 70);
            // indent word-wrapped lines
            $summary = implode("\n   ", explode("\n", $summary));
            $notes[$bug['email']]['name']  = $bug['reporter_name'];
            $notes[$bug['email']][$type] .= " * ID #$bug[id]: $summary\n";
        }

        $email_header  = "Hello {name},\n\n";
        $email_header .= "We'd like to inform you that the following issues you reported have been addressed in the new version of {package}:\n";
        $email_footer  = "\nYou can get the new version via http://{channel}/package/{package}/download/{version}\n";
        $email_footer .= "or install with pear install {package}{state} / pear upgrade {package}{state}";
        $mail_headers  = 'From: ' . SITE_BIG . ' QA <' . PEAR_QA_EMAIL .">\r\n";
        $subject       = '[' . SITE_BIG . '-BUG] Bug report submission follow up for package ' . $info->getPackage();
        $state = $info->getState() == 'stable' ? '' : '-' . $info->getState();

        foreach ($notes as $email => $n) {
            $find    = array('{name}', '{package}');
            $replace = array($n['name'], $info->getPackage());
            $header = str_replace($find, $replace, $email_header);

            $find    = array('{channel}', '{package}', '{version}', '{state}');
            $replace = array(PEAR_CHANNELNAME, $info->getPackage(), $info->getVersion(), $state);
            $footer  = str_replace($find, $replace, $email_footer);

            $text = '';
            if (isset($n['bugs'])) {
                $text .= "\nFixed Bugs:\n";
                $text .= $n['bugs'];
            }

            if (isset($n['features'])) {
                $text .= "\nImplemented Features:\n";
                $text .= $n['features'];
            }

            $body = $header . $text . $footer;
            $to   = $n['name'] . '<' . $email . '>';
            if (!DEVBOX) {
                mail($to, $subject, $body, $mail_headers, '-f ' . PEAR_BOUNCE_EMAIL);
            }
        }

        $success              = true;
        $display_form         = true;
        $display_verification = false;

    } elseif (isset($_POST['cancel'])) {
        // Cancel Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']);
        if (@is_file($distfile)) {
            @unlink($distfile);
        }

        $display_form         = true;
        $display_verification = false;
    }
} while (false);

PEAR::popErrorHandling();


if ($display_form) {
    if (!checkUser($auth_user->handle)) {
        $errors[] = 'You are not registered as lead developer for any packages.';
    }
    require PEARWEB_TEMPLATEDIR . '/release/upload-form.php';
}


if ($display_verification) {
    include_once 'PEAR/Config.php';
    include_once 'PEAR/PackageFile.php';

    // XXX this will leave files in PEAR_UPLOAD_TMPDIR if users don't
    // complete the next screen.  Janitor cron job recommended!
    $config = &PEAR_Config::singleton();
    $pkg    = &new PEAR_PackageFile($config);
    $info   = &$pkg->fromTgzFile(PEAR_UPLOAD_TMPDIR . '/' . $tmpfile, PEAR_VALIDATE_NORMAL);
    $errors = $warnings = array();
    if (PEAR::isError($info)) {
        if (is_array($info->getUserInfo())) {
            foreach ($info->getUserInfo() as $err) {
                if ($err['level'] == 'error') {
                    $errors[] = $err['message'];
                } else {
                    $warnings[] = $err['message'];
                }
            }
        }
        $errors[] = $info->getMessage();
    } else {
        if ($info->getPackageXmlVersion() == '1.0') {
            $errors[] = 'Only packages using package.xml version 2.0 or newer may be' .
                ' released - use the "pear convert" command to create a new package.xml';
        }

        include_once 'pear-database-package.php';
        $id   = package::info($info->getPackage(), 'id');
        $name = package::info($info->getPackage(), 'name');
        if ($info->getPackage() != $name) {
            // case does not match
            $errors[] = 'Package name in package.xml "' .
                htmlspecialchars($info->getPackage()) .
                '" MUST match exactly package name on the website "' .
                htmlspecialchars($name) . '"';
        }
        $version = $info->getVersion();
        $verinfo = explode('.', $version);
        if (count($verinfo) != 3) {
            $errors[] = "Versions must have 3 decimals as in x.y.z";
            $stupid = true;
        } else {
            $stupid = false;
            if ($version == '1.0.0' && $info->getState() != 'stable') {
                $errors[] = 'Version 1.0.0 must be stable';
            }
            if (strpos($version, 'RC') && $info->getState() != 'beta') {
                $errors[] = 'Release Candidate versions must have stability beta';
            }
            if (substr($verinfo[2], 1, 2) == 'rc') {
                $errors[] = 'Release Candidate versions MUST use upper-case RC versioning, not rc';
            }
            if (substr($version, 0, 4) == '0.0.') {
                $errors[] = 'Version 0.0.X is invalid, use 0.X.0';
            }
        }
        if ($info->getState() == 'stable') {
            $releases = package::info($info->getPackage(), 'releases');
            if (!count($releases)) {
                $errors[] = "The first release of a package must be 'alpha' or 'beta', not 'stable'." .
                "  Try releasing version 1.0.0RC1, state 'beta'";
            }
            if ($version{0} < 1) {
                $errors[] = "Versions < 1.0.0 may not be 'stable'";
            }
            if (!$stupid && !strpos($version, 'RC') && !preg_match('/^\d+\z/', $verinfo[2])) {
                $errors[] = "Stable versions must not have a postfix (use 'beta' for RC postfix)";
            }
        }
        $filelist = $info->getFilelist();
        if (isset($filelist['package.xml'])) {
            $warnings[] = 'package.xml should not be present in package.xml, installation may fail';
        }
        if (isset($filelist['package2.xml'])) {
            $warnings[] = 'package2.xml should not be present in package.xml, installation may fail';
        }
    }
    if ($info->getChannel() != PEAR_CHANNELNAME) {
        $errors[] = 'Only channel ' . PEAR_CHANNELNAME .
            ' packages may be released at ' . PEAR_CHANNELNAME;
    }
    // this next switch may never be used, but is here in case it turns out to be a good move
    switch ($info->getPackageType()) {
        case 'php' :
            $type = 'PHP package';
        break;
        case 'extsrc' :
            $type = 'Extension Source package';
        break;
        case 'extbin' :
            $type = 'Extension Binary package';
        break;
        default :
    }
    require PEARWEB_TEMPLATEDIR . '/release/verification-form.php';
}

response_footer();


function checkUser($user)
{
    global $dbh;
    // It's a lead or user of the package
    $query = '
        SELECT m.handle
        FROM packages p, maintains m
        WHERE
            m.handle = ? AND
            p.id = m.package AND
            m.role = ?';
    $res = $dbh->getOne($query, array($user, 'lead'));
    if ($res !== null) {
        return true;
    }
    // Try to see if the user is an admin
    return auth_check('pear.qa');
}
