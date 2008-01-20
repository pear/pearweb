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
// Interface to update package information.

auth_require('pear.dev');

require_once 'HTML/QuickForm.php';
require_once 'tags/Manager.php';

response_header('Edit Package');
?>

<script language="javascript">
<!--

function confirmed_goto(url, message) {
    if (confirm(message)) {
        location = url;
    }
}
// -->
</script>

<?php
echo '<h1>Edit Package</h1>';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    report_error('No package ID specified.');
    response_footer();
    exit;
}

include_once 'pear-database-user.php';
if (!user::maintains($auth_user->handle, $_GET['id'], 'lead') &&
    !user::isAdmin($auth_user->handle) &&
    !user::isQA($auth_user->handle))
{
    report_error('Editing only permitted by package leads, PEAR Admins'
                 . ' or PEAR QA');
    response_footer();
    exit;
}

// Update
include_once 'pear-database-package.php';
if (isset($_POST['submit'])) {
    if (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        report_error('You have to enter values for name, license and summary!');
    } elseif (($_POST['new_channel'] && !$_POST['new_package']) ||
              ($_POST['new_package'] && !$_POST['new_channel'])) {
        report_error('You have to enter both channel + package name for packages moved out of PEAR!');
    } else {
        $query = 'UPDATE packages SET name = ?, license = ?,
                  summary = ?, description = ?, category = ?,
                  homepage = ?, package_type = ?, doc_link = ?, cvs_link = ?,
                  unmaintained = ?, newpk_id = ?, newchannel = ?, newpackagename = ?
                  WHERE id = ?';

        if (!empty($_POST['newpk_id'])) {
            $_POST['new_channel'] = 'pear.php.net';
            $_POST['new_package'] = $dbh->getOne('SELECT name from packages WHERE id=?',
                array($_POST['newpk_id']));
            if (!$_POST['new_package']) {
                $_POST['new_channel'] = $_POST['newpk_id'] = null;
            }
        } else {
            if ($_POST['new_channel'] == 'pear.php.net') {
                $_POST['newpk_id'] = $dbh->getOne('SELECT id from packages WHERE name=?',
                    array($_POST['new_package']));
                if (!$_POST['newpk_id']) {
                    $_POST['new_channel'] = $_POST['new_package'] = null;
                }
            }
        }
        $qparams = array(
                      $_POST['name'],
                      $_POST['license'],
                      $_POST['summary'],
                      $_POST['description'],
                      $_POST['category'],
                      $_POST['homepage'],
                      'pear',
                      $_POST['doc_link'],
                      $_POST['cvs_link'],
                      isset($_POST['unmaintained']) ? 1 : 0 ,
                      isset($_POST['newpk_id']) ? $_POST['newpk_id'] : null,
                      $_POST['new_channel'],
                      $_POST['new_package'],
                      $_GET['id']
                    );

        $sth = $dbh->query($query, $qparams);

        if (PEAR::isError($sth)) {
            report_error('Unable to save data!');
        } else {
            if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                $manager = new Tags_Manager;
                $manager->clearTags($_POST['name']);
                foreach ($_POST['tags'] as $tag) {
                    if (!$tag) continue;
                    $manager->createPackageTag($tag, $_POST['name']);
                }
            }
            $pear_rest->saveAllPackagesREST();
            $pear_rest->savePackageREST($_POST['name']);
            $pear_rest->savePackagesCategoryREST(package::info($_POST['name'], 'category'));

            report_success('Package information successfully updated.');
        }
    }
} else if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'release_remove':
            if (!isset($_GET['release'])) {
                report_error('Missing package ID!');
                break;
            }

            include_once 'pear-database-release.php';
            if (release::remove($_GET['id'], $_GET['release'])) {
                echo "<b>Release successfully deleted.</b><br /><br />\n";
            } else {
                report_error('An error occured while deleting the release!');
            }

            break;
    }
}

$row = package::info((int)$_GET['id']);
if (empty($row['name'])) {
    report_error('Illegal package id');
    response_footer();
    exit;
}

print_package_navigation($row['packageid'], $row['name'],
                         '/package-edit.php?id=' . $row['packageid']);

$form = new HTML_QuickForm('package-edit', 'post', '/package-edit.php?id=' . $row['packageid']);

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
  <table border="0" class="form-holder" style="margin-bottom: 2em;" cellspacing="1">
   {content}
  </table>
 </div>
</form>');

$renderer->setGroupElementTemplate(
'<span>{label}</span>
 <span style="font-size:10px;">
  <!-- BEGIN required --><span style="color: #f00">* </span><!-- END required -->
 </span>{element}', 'm');


    // Set defaults for the form elements
    $form->setDefaults(array(
        'name'         => htmlspecialchars($row['name']),
        'license'      => htmlspecialchars($row['license']),
        'summary'      => htmlspecialchars($row['summary']),
        'description'  => htmlspecialchars($row['description']),
        'category'     => (int)$row['categoryid'],
        'homepage'     => htmlspecialchars($row['homepage']),
        'doc_link'     => htmlspecialchars($row['doc_link']),
        'cvs_link'     => htmlspecialchars($row['cvs_link']),
        'unmaintained' => ($row['unmaintained']) ? true : false,
        'newpk_id'     => (int)$row['newpk_id'],
        'new_channel'  => htmlspecialchars($row['new_channel']),
        'new_package'  => htmlspecialchars($row['new_package']),
    ));

$form->addElement('html', '<caption class="form-caption">Edit Package Information</caption>');
$form->addElement('text', 'name', 'Pa<span class="accesskey">c</span>kage Name:', 'size="50" maxlength="80" accesskey="c"');
$form->addElement('text', 'license', 'License:', 'size="50" maxlength="50"');
$form->addElement('textarea', 'summary', 'Summary', 'cols="50" rows="5" maxlength="255"');
$form->addElement('textarea', 'description', 'Description', 'cols="50" rows="8"');

$sth = $dbh->query('SELECT id, name FROM categories ORDER BY name');

while ($cat_row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rows[$cat_row['id']] = $cat_row['name'];
}
$form->addElement('select', 'category', 'Category:', $rows);

$manager = new Tags_Manager;
$select = array('' => '(none)') + $manager->getTags(false, true);
$sl = $form->addElement('select', 'tags', 'Tags:', $select);
$sl->setValue(array_keys($manager->getTags($row['name'], true)));
$sl->setSize(10);
$sl->setMultiple(true);

$form->addElement('text', 'homepage', 'H<span class="accesskey">o</span>mepage:', 'size="25" maxlength="255" accesskey="0"');
$form->addElement('text', 'doc_link', 'Documentation URI:', 'size="50" maxlength="255"');
$form->addElement('text', 'cvs_link', 'Web CVS URI', 'size="50" maxlength="255"');
$form->addElement('checkbox', 'unmaintained', 'Is this package unmaintained ?');

$packages = package::listAllwithReleases();

$rows = array(0 => "");
foreach ($packages as $id => $info) {
    if ($id == $_GET['id']) {
        continue;
    }
    $rows[$id] = $info['name'];
}

$maintain = array();
$maintain[] = &HTML_QuickForm::createElement('select', 'newpk_id', 'Choose either a PEAR package:', $rows);
$maintain[] = &HTML_QuickForm::createElement('static', null, 'Or a package moved out of PEAR');
$maintain[] = &HTML_QuickForm::createElement('text', 'new_channel', 'Channel:', 'size="50" maxlength="255"');
$maintain[] = &HTML_QuickForm::createElement('text', 'new_package', 'Package:', 'size="50" maxlength="255"');
$form->addGroup($maintain, 'm', 'New package (superseding this one):', '<br />', false);

$buttons = array();
$buttons[] = &HTML_QuickForm::createElement('submit', 'submit', 'Save Changes');
$buttons[] = &HTML_QuickForm::createElement('reset', 'cancel', 'Cancel', 'onClick="javascript:window.location.href=\'/package/' . $_GET['id'] . '\'; return false"\'');
$form->addGroup($buttons, null, null, '&nbsp;');
$form->display();
?>

<table class="form-holder" cellspacing="1">
<caption class="form-caption">Manage Releases</caption>

<tr>
 <th class="form-label_top">Version</th>
 <th class="form-label_top">Release Date</th>
 <th class="form-label_top">Actions</th>
</tr>

<?php

foreach ($row['releases'] as $version => $release) {
    echo "<tr>\n";
    echo '  <td class="form-input">' . htmlspecialchars($version) . "</td>\n";
    echo '  <td class="form-input">';
    echo make_utc_date(strtotime($release['releasedate']));
    echo "</td>\n";
    echo '  <td class="form-input">' . "\n";

    $url = 'package-edit.php?id=' .
            $_GET['id'] . '&amp;release=' .
            htmlspecialchars($release['id']) . '&amp;action=release_remove';
    $msg = 'Are you sure that you want to delete the release?';

    echo "<a href=\"javascript:confirmed_goto('$url', '$msg')\">"
         . make_image('delete.gif')
         . "</a>\n";

    echo "</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

response_footer();