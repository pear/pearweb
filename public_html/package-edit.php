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

/*
 * Interface to update package information.
 */

auth_require('pear.dev');

require_once 'HTML/Form.php';
require_once 'tags/Manager.php';
$form = new HTML_Form('package-edit.php');
$form->setDefaultFromInput(false);

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
            if (is_array($_POST['tags'])) {
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
            echo "<b>Package information successfully updated.</b><br /><br />\n";
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

?>

<form action="package-edit.php?id=<?php echo $_GET['id']; ?>" method="POST">
<table class="form-holder" style="margin-bottom: 2em;" cellspacing="1">
<caption class="form-caption">Edit Package Information</caption>
<tr>
    <th class="form-label_left">Pa<span class="accesskey">c</span>kage Name:</th>
    <td class="form-input">
    <?php $form->displayText('name',
            htmlspecialchars($row['name']), 50, 80, 'accesskey="c"'); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">License:</th>
    <td class="form-input">
    <?php $form->displayText('license',
            htmlspecialchars($row['license']), 50, 50); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Summary:</th>
    <td class="form-input">
    <?php $form->displayTextarea('summary',
            htmlspecialchars($row['summary']), 40, 3, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Description:</th>
    <td class="form-input">
    <?php $form->displayTextarea('description',
            htmlspecialchars($row['description'])); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Category:</th>
    <td class="form-input">
<?php
$sth = $dbh->query('SELECT id, name FROM categories ORDER BY name');

while ($cat_row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rows[$cat_row['id']] = $cat_row['name'];
}
$form->displaySelect("category", $rows, (int)$row['categoryid']);
?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Tags:</th>
    <td class="form-input">
<?php
$manager = new Tags_Manager;
$form->displaySelect("tags", $manager->getTags(false, true), array_keys($manager->getTags($row['name'], true)), 10, '(none)', true);
?>
    </td>
</tr>
<tr>
    <th class="form-label_left">H<span class="accesskey">o</span>mepage:</th>
    <td class="form-input">
    <?php $form->displayText('homepage',
            htmlspecialchars($row['homepage']), 50, 255, 'accesskey="o"'); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Documentation URI:</th>
    <td class="form-input">
    <?php $form->displayText('doc_link',
            htmlspecialchars($row['doc_link']), 50, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Web CVS URI:</th>
    <td class="form-input">
    <?php $form->displayText('cvs_link',
            htmlspecialchars($row['cvs_link']), 50, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Is this package unmaintained ?</th>
    <td class="form-input">
    <?php $form->displayCheckbox('unmaintained', ($row['unmaintained']) ? true : false); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">New package (superseding this one):</th>
    <td class="form-input">
    Choose either a PEAR package:
<?php
$packages = package::listAllwithReleases();

$rows = array(0 => "");
foreach ($packages as $id => $info) {
    if ($id == $_GET['id']) {
        continue;
    }
    $rows[$id] = $info['name'];
}

$form->displaySelect('newpk_id', $rows, (int)$row['newpk_id']);
?><br />
Or a package moved out of PEAR<br />Channel:
<?php $form->displayText('new_channel',
            htmlspecialchars($row['new_channel']), 50, 255); ?><br />
Package:
            <?php $form->displayText('new_package',
            htmlspecialchars($row['new_package']), 50, 255); ?>

    </td>
</tr>
<tr>
    <th class="form-label_left">&nbsp;</th>
    <td class="form-input"><input type="submit" name="submit" value="Save changes" />&nbsp;
    <input type="reset" name="cancel" value="Cancel" onClick="javascript:window.location.href='/package/<?php echo $_GET['id']; ?>'; return false" />
    </td>
</tr>
</table>
</form>

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

?>
