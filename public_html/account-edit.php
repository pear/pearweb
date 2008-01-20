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

ob_start();

$map = '
<script language="javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAjPqDvnoTwt1l2d9kE7aeSRSaX3uuPis-gsi6PocQln0mfq-TehSSt5OZ9q0OyzKSOAfNu8NuLlNgWA"></script>
';
response_header('Edit Profile :: ' . $handle, false, $map);

print '<h1>Edit Profile: ';
print '<a href="/user/'. htmlspecialchars($handle) . '">'
        . htmlspecialchars($handle) . '</a></h1>' . "\n";

print "<ul><li><a href=\"#password\">Manage your password</a></li></ul>";

$admin = $auth_user->isAdmin();
$user  = $auth_user->is($handle);

if (!$admin && !$user) {
    PEAR::raiseError("Only the user himself or PEAR administrators can edit the account information.");
    response_footer();
    exit();
}

if (isset($_POST['command']) && strlen($_POST['command'] < 32)) {
    $command = htmlspecialchars($_POST['command']);
} else {
    $command = 'display';
}

switch ($command) {
    case 'update':
        $fields_list = array("name", "email", "homepage", "showemail", "userinfo", "pgpkeyid", "wishlist", "latitude", "longitude", "active");

        $user_data_post = array('handle' => $handle);
        foreach ($fields_list as $k) {
            if ($k == 'showemail') {
                $user_data_post['showemail'] =  isset($_POST['showemail']) ? 1 : 0;
                continue;
            }

            if ($k == 'active') {
                $user_data_post['active'] =  isset($_POST['active']) ? 1 : 0;
                continue;
            }

            if ($k == 'wishlist') {
                $user_data_post['wishlist'] = isset($_POST['wishlist']) ? strip_tags($_POST['wishlist']) : '';
                continue;
            }

            if ($k == 'latitude') {
                $user_data_post['latitude'] =
                    isset($_POST['latitude']) ?
                    strip_tags($_POST['latitude']) : '';
            }

            if ($k == 'longitude') {
                $user_data_post['longitude'] =
                    isset($_POST['longitude']) ?
                    strip_tags($_POST['longitude']) : '';
            }

            if (!isset($_POST[$k])) {
                report_error('Invalid data submitted.');
                response_footer();
                exit();
            }

            if ($k != 'userinfo') {
                $user_data_post[$k] = htmlspecialchars($_POST[$k]);
            } else {
                $user_data_post[$k] = $_POST[$k];
                if (strlen($user_data_post[$k]) > 500) {
                    report_error('User information exceeds the allowed length of 500 characters.');
                    response_footer();
                    exit();
                }
            }
        }

        include_once 'pear-database-user.php';
        $result = user::update($user_data_post);
        if (DB::isError($result)) {
            PEAR::raiseError('Could not update the user profile, please notifiy ' . PEAR_WEBMASTER_EMAIL);
            break;
        }

        $old_acl = $dbh->getCol('SELECT path FROM cvs_acl '.
                                'WHERE username = ' . "'$handle'" . ' AND access = 1', 0);

        $new_acl = preg_split("/[\r\n]+/", trim($_POST['cvs_acl']));

        $lost_entries = array_diff($old_acl, $new_acl);
        $new_entries = array_diff($new_acl, $old_acl);

        if (sizeof($lost_entries) > 0) {
            $sth = $dbh->prepare("DELETE FROM cvs_acl WHERE username = ? ".
                                 "AND path = ?");
            foreach ($lost_entries as $ent) {
                $del = $dbh->affectedRows();
                $dbh->execute($sth, array($handle, $ent));
                print "Removing CVS access to " . htmlspecialchars($ent)
                        . " for " . htmlspecialchars($handle) . "...<br />\n";
            }
        }

        if (sizeof($new_entries) > 0) {
            $sth = $dbh->prepare("INSERT INTO cvs_acl (username,path,access) ".
                                 "VALUES(?,?,?)");
            foreach ($new_entries as $ent) {
                $dbh->execute($sth, array($handle, $ent, 1));
                print "Adding CVS access to " . htmlspecialchars($ent)
                        . " for " . htmlspecialchars($handle) . "...<br />\n";
            }
        }

        report_success('Your information was successfully updated.');
        break;

    case 'change_password':
        include_once 'pear-database-user.php';
        $user = user::info($handle, 'password', true, false);
        if (empty($_POST['password_old']) || empty($_POST['password']) ||
            empty($_POST['password2'])
        ) {
            PEAR::raiseError('Please fill out all password fields.');
            break;
        }

        if ($user['password'] != md5($_POST['password_old'])) {
            PEAR::raiseError('You provided a wrong old password.');
            break;
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
            $expire = !empty($_POST['PEAR_PERSIST']) ? 2147483647 : 0;
            setcookie('PEAR_PW', md5($_POST['password']), $expire, '/');

            report_success('Your password was successfully updated.');
        }
        break;
}


$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE handle = ?', array($handle));

$cvs_acl_arr = $dbh->getCol('SELECT path FROM cvs_acl'
                            . ' WHERE username = ? AND access = 1', 0,
                            array($handle));
$cvs_acl = implode("\n", $cvs_acl_arr);

if ($row === null) {
    error_handler(htmlspecialchars($handle) . ' is not a valid account name.',
                  'Invalid Account');
}


$form = new HTML_QuickForm('account-edit', 'post', 'account-edit.php', 'test');

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
    'cvs_acl'   => htmlspecialchars($cvs_acl),
    'latitude'  => htmlspecialchars($row['latitude']),
    'longitude' => htmlspecialchars($row['longitude']),
));

$form->addElement('html', '<caption class="form-caption">Edit Your Information</caption>');
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
$form->addElement('textarea', 'cvs_acl', 'CVS Access:', 'cols="40" rows="5"');
$form->addElement('text', 'latitude', 'Latitude Point:', 'size="40" id="latitude"');
$form->addElement('text', 'longitude', 'Longitude Point:', 'size="40" id="longitude"');
$form->addElement('static', null, '
<script language="javascript" src="javascript/showmap.js"></script>
<script language="javascript" src="javascript/popmap.js"></script>
');
$form->addElement('static', null, '<a href="#" onclick="pearweb.display_map(event); showmap();">Open map</a>');

$form->addElement('submit', 'submit', 'Submit');
$form->addElement('hidden', 'handle', htmlspecialchars($handle));
$form->addElement('hidden', 'command', 'update');
$form->display();

print '
<div style="position:absolute; visibility: hidden;" id="pearweb_map"></div>';

print '<a name="password"></a>' . "\n";
print '<h2>&raquo; Manage your password</h2>' . "\n";


$form = new HTML_QuickForm('account-edit-password', 'post', 'account-edit.php', 'test');

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

$form->addElement('html', '<caption class="form-caption">Change Password</caption>');
$form->addElement('password', 'password_old', '<span class="accesskey">O</span>ld Password:', 'accesskey="0"');
$form->addElement('password', 'password',  'Current Password:');
$form->addElement('password', 'password2', 'Repeat Password:');
$form->addElement('checkbox', 'PEAR_PERSIST', 'Remember username and password?');

$form->addElement('submit', 'submit', 'Submit');
$form->addElement('hidden', 'handle', htmlspecialchars($handle));
$form->addElement('hidden', 'command', 'change_password');
$form->display();

ob_end_flush();
response_footer();