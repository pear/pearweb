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

auth_require('pear.dev');

define('HTML_FORM_MAX_FILE_SIZE', 16 * 1024 * 1024); // 16 MB
define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');

require_once 'HTML/Form.php';

$display_form         = true;
$display_verification = false;
$success              = false;
$errors               = array();

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

do {
    if (isset($_POST['upload'])) {
        // Upload Button
        include_once 'HTTP/Upload.php';
        $upload_obj = new HTTP_Upload('en');
        $file = $upload_obj->getFiles('distfile');
        if (PEAR::isError($file)) {
            $errors[] = $file->getMessage();
            break;
        }

        if ($file->isValid()) {
            $file->setName('uniq', 'pear-');
            $file->setValidExtensions('tgz', 'accept');
            $tmpfile = $file->moveTo(PEAR_UPLOAD_TMPDIR);
            if (PEAR::isError($tmpfile)) {
                $errors[] = $tmpfile->getMessage();
                break;
            }
            $tmpsize = $file->getProp('size');
        } elseif ($file->isMissing()) {
            $errors[] = 'No file has been uploaded.';
            break;
        } elseif ($file->isError()) {
            $errors[] = $file->errorMsg();
            break;
        }

        $display_form = false;
        $display_verification = true;

    } elseif (isset($_POST['verify'])) {
        set_time_limit(60);
        include_once 'PEAR/Config.php';
        include_once 'PEAR/PackageFile.php';
        // Verify Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']);
        if (!@is_file($distfile)) {
            $errors[] = 'No verified file found.';
            break;
        }

        $config = &PEAR_Config::singleton();
        $pkg = &new PEAR_PackageFile($config);
        $info = &$pkg->fromTgzFile($distfile, PEAR_VALIDATE_NORMAL);
        if (PEAR::isError($info)) {
            if (is_array($info->getUserInfo())) {
                foreach ($info->getUserInfo() as $err) {
                    $errors[] = $err['message'];
                }
                $errors[] = $info->getMessage();
            }
            break;
        } else {
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
            if (!auth_check('pear.admin') &&
                !auth_check('pear.qa') &&
                !user::maintains($auth_user->handle, $pacid, 'lead')) {
                $errors[] = 'You don\'t have permissions to upload this release.';
                break;
            }
            $license = $info->getLicense();
            if (is_array($license)) {
                $license = $license['_content'];
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
            $users = array();
            foreach ($info->getMaintainers() as $user) {
                $users[strtolower($user['handle'])] = array(
                                                        'role'   => $user['role'],
                                                        'active' => !isset($user['active']) || $user['active'] == 'yes',
                                                      );
            }

            include_once 'pear-database-maintainer.php';
            $e = maintainer::updateAll($pacid, $users);
            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();
                break;
            }
            $pear_rest->savePackageMaintainerREST($info->getPackage());

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
        if (is_a($info, 'PEAR_PackageFile_v1') || is_a($info, 'PEAR_PackageFile_v2')) {
            release::promote_v2($info, $file);
        } else {
            release::promote($info, $file);
        }
        PEAR::popErrorHandling();

        $success              = true;
        $display_form         = true;
        $display_verification = false;

    } elseif (isset($_POST['cancel'])) {
        // Cancel Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($distfile);
        if (@is_file($distfile)) {
            @unlink($distfile);
        }

        $display_form         = true;
        $display_verification = false;
    }
} while (false);

PEAR::popErrorHandling();


if ($display_form) {
    $title = 'Upload New Release';
    response_header($title);

    // Remove that code when release-upload also create new packages
    if (!checkUser($auth_user->handle)) {
        $errors[] = 'You are not registered as lead developer for any packages.';
    }

    echo '<h1>' . $title . "</h1>\n";

    if ($success) {
        if (is_array($info)) {
            report_success('Version ' . $info['version'] . ' of '
                           . $info['package'] . ' has been successfully released, '
                           . 'and its promotion cycle has started.');
            print '<p>';
            print make_link('/package/' . $info['package'], 'Visit package home');
        } else {
            report_success('Version ' . $info->getVersion() . ' of '
                           . $info->getPackage() . ' has been successfully released, '
                           . 'and its promotion cycle has started.');
        }
        print '</p>';
        print '</div>';
    } else {
        report_error($errors);
    }

    print <<<MSG
<p>
Upload a new package distribution file built using &quot;<code>pear
package</code>&quot; here.  The information from your package.xml file will
be displayed on the next screen for verification. The maximum file size
is 16 MB.
</p>

<p>
Uploading new releases is restricted to each package's lead developer(s).
</p>
MSG;

    $form =& new HTML_Form('release-upload.php', 'post', '', '',
            'multipart/form-data');
    $form->setDefaultFromInput(false);

    $form->addFile('distfile',
            '<label for="f" accesskey="i">D<span class="accesskey">i</span>'
            . 'stribution File</label>',
            HTML_FORM_MAX_FILE_SIZE, 40, '', 'id="f"');
    $form->addSubmit('upload', 'Upload!');
    $form->display('class="form-holder" cellspacing="1"',
            'Upload', 'class="form-caption"');
}


if ($display_verification) {
    include_once 'PEAR/Config.php';
    include_once 'PEAR/PackageFile.php';

    response_header('Upload New Release :: Verify');

    // XXX this will leave files in PEAR_UPLOAD_TMPDIR if users don't
    // complete the next screen.  Janitor cron job recommended!
    $config = &PEAR_Config::singleton();
    $pkg = &new PEAR_PackageFile($config);
    $info = &$pkg->fromTgzFile(PEAR_UPLOAD_TMPDIR . '/' . $tmpfile, PEAR_VALIDATE_NORMAL);
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
        }
        if ($version == '1.0.0' && $info->getState() != 'stable') {
            $errors[] = 'Version 1.0.0 must be stable';
        }
        if (strpos($version, 'RC') && $info->getState() != 'beta') {
            $errors[] = 'Release Candidate versions must have stability beta';
        }
        if (substr($version, 0, 4) == '0.0.') {
            $errors[] = 'Version 0.0.X is invalid, use 0.X.0';
        }
        if ($info->getState() == 'stable') {
            $releases = package::info($info->getPackage(), 'releases', true);
            if (!count($releases)) {
                $errors[] = "The first release of a package must be 'alpha' or 'beta', not 'stable'." .
                "  Try releasing version 1.0.0RC1, state 'beta'";
            }
            if ($version{0} < 1) {
                $errors[] = "Versions < 1.0.0 may not be 'stable'";
            }
            if (!preg_match('/^\d+\z/', $verinfo[2])) {
                $errors[] = "Stable versions must not have a postfix (use 'beta' for RC postfix)";
            }
        }
        if (substr($verinfo[2], 1, 2) == 'rc') {
            $errors[] = 'Release Candidate versions MUST use upper-case RC versioning, not rc';
        }
        $filelist = $info->getFilelist();
        if (isset($filelist['package.xml'])) {
            $warnings[] = 'package.xml should not be present in package.xml, installation may fail';
        }
        if (isset($filelist['package2.xml'])) {
            $warnings[] = 'package2.xml should not be present in package.xml, installation may fail';
        }
        if ($info->getPackageXmlVersion() == '1.0') {
            $errors[] = 'Only packages using package.xml version 2.0 or newer may be' .
                ' released - use the "pear convert" command to create a new package.xml';
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
    report_error($errors, 'errors','ERRORS:<br />'
                 . 'You must correct your package.xml file:');
    report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br />'
                 . 'You may want to correct your package.xml file:');

    $form =& new HTML_Form('release-upload.php', 'post');
    $form->setDefaultFromInput(false);

    $form->addPlaintext('Package:', htmlspecialchars($info->getPackage()));
    $form->addPlaintext('Version:', htmlspecialchars($info->getVersion()));
    $form->addPlaintext('Summary:', htmlspecialchars($info->getSummary()));
    $form->addPlaintext('Description:', nl2br(htmlspecialchars($info->getDescription())));
    $form->addPlaintext('Release State:', htmlspecialchars($info->getState()));
    $form->addPlaintext('Release Date:', htmlspecialchars($info->getDate()));
    $form->addPlaintext('Release Notes:', nl2br(htmlspecialchars($info->getNotes())));
    $form->addPlaintext('Package Type:', htmlspecialchars($type));
    // Don't show the next step button when errors found
    if (!count($errors)) {
        $form->addSubmit('verify', 'Verify Release');
    }

    $form->addSubmit('cancel', 'Cancel');
    $form->addHidden('distfile', htmlspecialchars($tmpfile));
    $form->display('class="form-holder" cellspacing="1"',
            'Please verify that the following release information is correct:',
            'class="form-caption"');
}

response_footer();


function checkUser($user)
{
    global $dbh;
    // It's a lead or user of the package
    $query = "SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = ? AND
                 p.id = m.package AND
                 m.role = 'lead'";
    $res = $dbh->getOne($query, array($user));
    if ($res !== null) {
        return true;
    }
    // Try to see if the user is an admin
    return auth_check('pear.qa');
}

?>
