<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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
        // Verify Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']);
        if (!@is_file($distfile)) {
            $errors[] = 'No verified file found.';
            break;
        }

        include_once 'PEAR/Common.php';
        $util =& new PEAR_Common;
        $info = $util->infoFromTgzFile($distfile);

        $pacid = package::info($info['package'], 'id');
        if (PEAR::isError($pacid)) {
            $errors[] = $pacid->getMessage();
            break;
        }
        if (!user::isAdmin($_COOKIE['PEAR_USER']) &&
            !user::maintains($_COOKIE['PEAR_USER'], $pacid, 'lead')) {
            $errors[] = 'You don\'t have permissions to upload this release.';
            break;
        }

        $e = package::updateInfo($pacid,
                array(
                    'summary'     => $info['summary'],
                    'description' => $info['description'],
                    'license'     => $info['release_license'],
                ));
        if (PEAR::isError($e)) {
            $errors[] = $e->getMessage();
            break;
        }

        $users = array();
        foreach ($info['maintainers'] as $user) {
            $users[strtolower($user['handle'])] = array(
                                                    'role'   => $user['role'],
                                                    'active' => 1,
                                                  );
        }

        $e = maintainer::updateAll($pacid, $users);
        if (PEAR::isError($e)) {
            $errors[] = $e->getMessage();
            break;
        }
        $file = release::upload($info['package'], $info['version'],
                                $info['release_state'], $info['release_notes'],
                                $distfile, md5_file($distfile));
        if (PEAR::isError($file)) {
            $ui = $file->getUserInfo();
            $errors[] = 'Error while uploading package: ' .
                         $file->getMessage() . ($ui ? " ($ui)" : '');
            break;
        }
        @unlink($distfile);

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');
        release::promote($info, $file);
        PEAR::popErrorHandling();

        $success              = true;
        $display_form         = true;
        $display_verification = false;

    } elseif (isset($cancel)) {
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
        report_success('Version ' . $info['version'] . ' of '
                       . $info['package'] . ' has been successfully released'
                       . ' and the promotion cycle for it has started.');
        print '<p>';
        print make_link('/package/' . $info['package'], 'Visit package home');
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

    $form =& new HTML_Form($_SERVER['PHP_SELF'], 'post', '', '',
            'multipart/form-data');
    $form->addFile('distfile',
            '<label for="f" accesskey="i">D<span class="accesskey">i</span>'
            . 'stribution File</label>',
            HTML_FORM_MAX_FILE_SIZE, 40, '', 'id="f"');
    $form->addSubmit('upload', 'Upload!');
    $form->display('class="form-holder" cellspacing="1"',
            'Upload', 'class="form-caption"');
}


if ($display_verification) {
    include_once 'PEAR/Common.php';

    response_header('Upload New Release :: Verify');

    $util =& new PEAR_Common;

    // XXX this will leave files in PEAR_UPLOAD_TMPDIR if users don't
    // complete the next screen.  Janitor cron job recommended!
    $info = $util->infoFromTgzFile(PEAR_UPLOAD_TMPDIR . '/' . $tmpfile);

    // packge.xml conformance
    $errors   = array();
    $warnings = array();

    $util->validatePackageInfo($info, $errors, $warnings);

    // XXX ADD MASSIVE SANITY CHECKS HERE

    if (!preg_match('/^\d+\.\d+\.\d+(?:[a-zA-Z]+\d*)?$/', $info['version'])) {
        $errors[] = 'Version must in format digit.digit.digit[alpha[digits]]';
    }

    if ($info['release_state'] == 'stable') {
        // see if this is the first release
        $releases = package::info($info['name'], 'releases');
        if (!count($releases)) {
            $errors[] = 'The first release of a package must be alpha or beta, not stable.' .
                '  Try releasing version 1.0.0RC1, state beta';
        }
        if ($info['version']{0} != '1') {
            $errors[] = 'Versions < 1.0.0 may not be stable';
        }
        $verinfo = explode('.', $info['version']);
        if (!preg_match('/^\d+$/', $verinfo[2])) {
            $errors[] = 'Stable versions must not have a post-fix (use beta for RC postfix)';
        }
    }

    report_error($errors, 'errors','ERRORS:<br />'
                 . 'You must correct your package.xml file:');
    report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br />'
                 . 'You may want to correct your package.xml file:');

    $check = array(
        'summary',
        'description',
        'release_state',
        'release_date',
        'releases_notes',
    );
    foreach ($check as $key) {
        if (!isset($info[$key])) {
            $info[$key] = 'n/a';
        }
    }

    $form =& new HTML_Form($_SERVER['PHP_SELF'], 'post');
    $form->addPlaintext('Package:', $info['package']);
    $form->addPlaintext('Version:', $info['version']);
    $form->addPlaintext('Summary:', htmlspecialchars($info['summary']));
    $form->addPlaintext('Description:', nl2br(htmlspecialchars($info['description'])));
    $form->addPlaintext('Release State:', $info['release_state']);
    $form->addPlaintext('Release Date:', $info['release_date']);
    $form->addPlaintext('Release Notes:', nl2br(htmlspecialchars($info['release_notes'])));

    // Don't show the next step button when errors found
    if (!count($errors)) {
        $form->addSubmit('verify', 'Verify Release');
    }

    $form->addSubmit('cancel', 'Cancel');
    $form->addHidden('distfile', $tmpfile);
    $form->display('class="form-holder" cellspacing="1"',
            'Please verify that the following release information is correct',
            'class="form-caption"');
}

response_footer();


function checkUser($user, $pacid = null)
{
    global $dbh;
    $add = ($pacid) ? 'AND p.id = ' . $dbh->quoteSmart($pacid) : '';
    // It's a lead or user of the package
    $query = "SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = ? AND
                 p.id = m.package $add AND
                 (m.role IN ('lead', 'developer'))";
    $res = $dbh->getOne($query, array($user));
    if ($res !== null) {
        return true;
    }
    // Try to see if the user is an admin
    $res = user::isAdmin($user);
    return ($res === true);
}

?>
