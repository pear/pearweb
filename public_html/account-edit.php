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

include_once 'HTML/QuickForm.php';

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

$map = '<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $_SERVER['Google_API_Key'] . '"></script>';
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
        PEAR::raiseError('Could not update the user profile, please notifiy ' . PEAR_WEBMASTER_EMAIL);
        break;
    }

    report_success('Your information was successfully updated.');
}

if ($command == 'change_password') {
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


$form = new HTML_QuickForm('account-edit', 'post', 'account-edit.php', 'test');
$form->removeAttribute('name');

$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('
 <tr>
  <th class="form-label_left">
   <!-- BEGIN required --><span style="color: #ff0000">*</span><!-- END required -->
   {label}
  </th>
  <td class="form-input">
   <!-- BEGIN error --><span style="color: #ff0000">{error}</span><br /><!-- END error -->
   {element}
  </td>
 </tr>
');

$renderer->setFormTemplate('
<form{attributes}>
 <div>
  {hidden}
  <table border="0" class="form-holder" cellspacing="1" style="margin-bottom: 2em;">
   {content}
  </table>
 </div>
</form>');


// Set defaults for the form elements
$form->setDefaults(array(
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
));

$form->addElement('checkbox', 'active', 'Active User?');
$form->addElement('text', 'name', '<span class="accesskey">N</span>ame:', 'size="40" accesskey="n"');
$form->addElement('text', 'email', 'Email:', array('size' => 40));
$form->addElement('checkbox', 'showemail', 'Show email address?');
$form->addElement('text', 'homepage', 'Homepage:', array('size' => 40));
$form->addElement('text', 'wishlist', 'Wishlist URI:', array('size' => 40));
$form->addElement('text', 'pgpkeyid', 'PGP Key ID:'
        . '<p class="cell_note">(Without leading 0x)</p>', array('size' => 40, 'maxlength' => 20));
$form->addElement('textarea', 'userinfo', 'Additional User Information:'
        . '<p class="cell_note">(limited to 255 chars)</p>', 'cols="40" rows="5"');
$form->addElement('text', 'latitude', 'Latitude Point:', 'size="40" id="latitude"');
$form->addElement('text', 'longitude', 'Longitude Point:', 'size="40" id="longitude"');
$form->addElement('static', null, '
<script src="javascript/showmap.js"></script>
<script src="javascript/popmap.js"></script>
');
$form->addElement('static', null, '<a href="#" onclick="pearweb.display_map(event); showmap();">Open map</a>');

$form->addElement('submit', 'submit', 'Submit');
$form->addElement('hidden', 'handle', htmlspecialchars($handle));
$form->addElement('hidden', 'command', 'update');
$form->display();

echo '<div style="position:absolute; visibility: hidden;" id="pearweb_map"></div>';

echo '<a name="password"></a>' . "\n";
echo '<h2>&raquo; Manage your password</h2>' . "\n";


$form = new HTML_QuickForm('account-edit-password', 'post', 'account-edit.php', 'style="padding-top: 20px;"');
$form->removeAttribute('name');

$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('
 <tr>
  <th class="form-label_left">
   <!-- BEGIN required --><span style="color: #ff0000">*</span><!-- END required -->
   {label}
  </th>
  <td class="form-input">
   <!-- BEGIN error --><span style="color: #ff0000">{error}</span><br /><!-- END error -->
   {element}
  </td>
 </tr>
');

$renderer->setFormTemplate('
<form{attributes}>
 <div>
  {hidden}
  <table border="0" class="form-holder" cellspacing="1">
   {content}
  </table>
 </div>
</form>');

$form->addElement('password', 'password_old', '<span class="accesskey">O</span>ld Password:', 'accesskey="0"');
$form->addElement('password', 'password',  'Current Password:');
$form->addElement('password', 'password2', 'Repeat Password:');
$form->addElement('checkbox', 'PEAR_PERSIST', 'Remember username and password?');

$form->addElement('submit', 'submit', 'Submit');
$form->addElement('hidden', 'handle', htmlspecialchars($handle));
$form->addElement('hidden', 'command', 'change_password');
$form->display();

response_footer();
