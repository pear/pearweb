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

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Element/InputUrl.php';
require_once 'HTML/QuickForm2/Element/InputEmail.php';

auth_require();
if (isset($_GET['handle'])) {
    $handle = $_GET['handle'];
} elseif (isset($_POST['handle'])) {
    $handle = $_POST['handle'];
} else {
    $handle = false;
}

if ($handle && !ereg('^[0-9a-z_]{2,20}$', $handle)) {
    response_header('Error:');
    report_error("No valid handle given!");
    response_footer();
    exit();
}

$map = '';
if (!empty($_SERVER['Google_API_Key'])) {
    $map = '<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $_SERVER['Google_API_Key'] . '"></script>';
}
response_header('Edit Profile :: ' . $handle, false, $map);

echo '<h1>Edit Profile: ';
echo '<a href="/user/'. htmlspecialchars($handle) . '">'
        . htmlspecialchars($handle) . '</a></h1>' . "\n";

$admin = $auth_user->isAdmin();
$user  = $auth_user->is($handle);

if (!$admin && !$user) {
    PEAR::raiseError("Only the user himself or PEAR administrators can edit the account information.");
    response_footer();
    exit();
}

echo "<ul><li><a href=\"#password\">Manage your password</a></li></ul>";

if (isset($_POST['command']) && strlen($_POST['command'] < 32)) {
    $command = htmlspecialchars($_POST['command']);
} else {
    $command = 'display';
}

if ($command == 'update') {
    $fields_list = array('name', 'email', 'homepage', 'showemail', 'userinfo',
                         'pgpkeyid', 'wishlist', 'latitude', 'longitude', 'active');

    if (!validate_csrf_token($csrf_token_name)) {
        report_error('Invalid submission.');
        response_footer();
        exit();
    }

    $user_post = array('handle' => $handle);
    foreach ($fields_list as $k) {
        if ($k == 'showemail') {
            $user_post['showemail'] =  isset($_POST['showemail']) ? 1 : 0;
            continue;
        }

        if ($k == 'active') {
            $user_post['active'] =  isset($_POST['active']) ? 1 : 0;
            continue;
        }

        if ($k == 'wishlist') {
            $user_post['wishlist'] = isset($_POST['wishlist']) ? strip_tags($_POST['wishlist']) : '';
            continue;
        }

        if ($k == 'latitude') {
            $user_post['latitude'] = isset($_POST['latitude']) ? strip_tags($_POST['latitude']) : '';
        }

        if ($k == 'longitude') {
            $user_post['longitude'] = isset($_POST['longitude']) ? strip_tags($_POST['longitude']) : '';
        }

        if (!isset($_POST[$k])) {
            report_error('Invalid data submitted.');
            response_footer();
            exit();
        }

        if ($k != 'userinfo') {
            $user_post[$k] = htmlspecialchars($_POST[$k]);
        } else {
            $user_post[$k] = $_POST[$k];
            if (strlen($user_post[$k]) > 500) {
                report_error('User information exceeds the allowed length of 500 characters.');
                response_footer();
                exit();
            }
        }
    }

    include_once 'pear-database-user.php';
    $result = user::update($user_post);
    if (DB::isError($result)) {
        PEAR::raiseError('Could not update the user profile, please notify ' . PEAR_WEBMASTER_EMAIL);
        break;
    }

    report_success('Your information was successfully updated.');
}

if ($command == 'change_password') {
    if (!validate_csrf_token($csrf_token_name)) {
        report_error('Invalid token.');
        response_footer();
        exit();
    }

    include_once 'pear-database-user.php';
    $user = user::info($handle, 'password', true, false);

    // If it's an admin we can change ones password without knowing {{{
    // it's old password.
    if (!$auth_user->isAdmin()) {
        if (empty($_POST['password_old'])
            || empty($_POST['password'])
            || empty($_POST['password2'])
        ) {
            PEAR::raiseError('Please fill out all password fields.');
            break;
        }

        if ($user['password'] != md5($_POST['password_old'])) {
            PEAR::raiseError('You provided a wrong old password.');
            break;
        }
    }

    if ($_POST['password'] != $_POST['password2']) {
        PEAR::raiseError('The new passwords do not match.');
        break;
    }

    $data = array(
        'password' => md5($_POST['password']),
        'handle'   => $handle,
    );
    $result = user::update($data);
    if ($result) {
        // TODO do the SVN push here


        $expire = !empty($_POST['PEAR_PERSIST']) ? 2147483647 : 0;
        setcookie('PEAR_PW', md5($_POST['password']), $expire, '/');

        report_success('Your password was successfully updated.');
    }
}


$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE handle = ?', array($handle));

if ($row === null) {
    error_handler(htmlspecialchars($handle) . ' is not a valid account name.',
                  'Invalid Account');
}


$csrf_token_value = create_csrf_token($csrf_token_name);

$form = new HTML_QuickForm2('account-edit', 'post');
$form->removeAttribute('name');

// Set defaults for the form elements
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'active'    => htmlspecialchars($row['active']),
    'name'      => htmlspecialchars($row['name']),
    'email'     => htmlspecialchars($row['email']),
    'showemail' => htmlspecialchars($row['showemail']),
    'homepage'  => htmlspecialchars($row['homepage']),
    'wishlist'  => htmlspecialchars($row['wishlist']),
    'pgpkeyid'  => htmlspecialchars($row['pgpkeyid']),
    'userinfo'  => htmlspecialchars($row['userinfo']),
    'latitude'  => htmlspecialchars($row['latitude']),
    'longitude' => htmlspecialchars($row['longitude']),
)));

$form->addElement('checkbox', 'active')->setLabel('Active User?');
$form->addElement('text', 'name', 'accesskey="n"')->setLabel('<span class="accesskey">N</span>ame:');
$form->addElement('email', 'email')->setLabel('Email:');
$form->addElement('checkbox', 'showemail')->setLabel('Show email address?');
$form->addElement('url', 'homepage')->setLabel('Homepage:');
$form->addElement('url', 'wishlist')->setLabel('Wishlist URI:');
$form->addElement('text', 'pgpkeyid', array('maxlength' => 20))->setLabel('PGP Key ID:'
        . '<p class="cell_note">(Without leading 0x)</p>');
$form->addElement('textarea', 'userinfo','cols="40" rows="5"')->setLabel('Additional User Information:'
        . '<p class="cell_note">(limited to 255 chars)</p>');
$form->addElement('text', 'latitude', 'id="latitude"')->setLabel('Latitude Point:');
$form->addElement('text', 'longitude', 'id="longitude"')->setLabel('Longitude Point:');
if (!empty($_SERVER['Google_API_Key'])) {
    $form->addElement('button', 'show_map', array('onclick' => "pearweb.display_map(event); showmap();"))->setValue('Open map');
}

$form->addElement('submit', 'submit');
$form->addElement('hidden', 'handle')->setValue(htmlspecialchars($handle));
$form->addElement('hidden', 'command')->setValue('update');
$form->addElement('hidden', $csrf_token_name)->setValue($csrf_token_value);
if (!empty($_SERVER['Google_API_Key'])) {
    echo '<script type="text/javascript" src="javascript/showmap.js"></script>';
    echo '<script type="text/javascript" src="javascript/popmap.js"></script>';
}
print $form;

echo '<div style="position:absolute; visibility: hidden;" id="pearweb_map"></div>';

echo '<a name="password"></a>' . "\n";
echo '<h2>&raquo; Manage your password</h2>' . "\n";


$form = new HTML_QuickForm2('account-edit-password', 'post', array('style' => "padding-top: 20px;"));
$form->removeAttribute('name');

$form->addElement('password', 'password_old', array('accesskey' => "O"))->setLabel('<span class="accesskey">O</span>ld Password:');
$form->addElement('password', 'password')->setLabel('Current Password:');
$form->addElement('password', 'password2')->setLabel('Repeat Password:');
$form->addElement('checkbox', 'PEAR_PERSIST')->setLabel('Remember username and password?');

$form->addElement('submit', 'submit');
$form->addElement('hidden', 'handle')->setValue(htmlspecialchars($handle));
$form->addElement('hidden', 'command')->setValue('change_password');
$form->addElement('hidden', $csrf_token_name)->setValue($csrf_token_value);

print $form;

response_footer();
