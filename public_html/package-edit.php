<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
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

$self = htmlspecialchars(strip_tags($_SERVER['SCRIPT_NAME']));

require_once 'HTML/Form.php';
$form = new HTML_Form($self);

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

if (!isset($_GET['id'])) {
    report_error('No package ID specified.');
    response_footer();
    exit;
}

$package_id = strip_tags(htmlspecialchars($_GET['id']));

if (!user::maintains($_COOKIE['PEAR_USER'], $_GET['id'], 'lead') &&
    !user::isAdmin($_COOKIE['PEAR_USER']) &&
    !user::isQA($_COOKIE['PEAR_USER']))
{
    report_error('Editing only permitted by package leads, PEAR Admins'
                 . ' or PEAR QA');
    response_footer();
    exit;
}

// Update
if (isset($_POST['submit'])) {
    if (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        report_error('You have to enter values for name, license and summary!');
    } else {
        $query = 'UPDATE packages SET name = ?, license = ?,
                  summary = ?, description = ?, category = ?,
                  homepage = ?, package_type = ?, doc_link = ?, cvs_link = ?,
                  wiki_area = ?
                  WHERE id = ?';

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
                      isset($_POST['wiki_area']) ? 1 : 0,
                      $package_id
                    );

        $sth = $dbh->query($query, $qparams);

        if (PEAR::isError($sth)) {
            report_error('Unable to save data!');
        } else {
             $pear_rest->savePackageREST($_POST['name']);
             $wikidb = DB::connect(PEAR_WIKI_DSN);
 
             $area_query = 'INSERT INTO yawiki_areas (name,title) VALUES (?,?)';
             
             $prep = $wikidb->prepare($area_query);
             $res = $wikidb->execute($prep, array($_POST['name'], $_POST['name']));
 
             if (PEAR::isError($res)) {
                 report_error('Cannot create the wiki area');
             }
 
             $area_query = 'INSERT INTO yawiki_store (area,page,body,dt,username) VALUES (?,?,?,?,?)';
             
             $data = array(
                 $_POST['name'], 'HomePage',
                 'This is ' . $_POST['name'] . ' package HomePage',
                 date('Y-m-d H:i:s'),
                 'pear-qa'
             );
 
             $prep = $wikidb->prepare($area_query);
             $res = $wikidb->execute($prep, $data);
 
             if (PEAR::isError($res)) {
                 report_error('Cannot create the main page of the area');
                 response_footer();
                 exit;
             }
 
             $data = array(
                 $_POST['name'], 'AreaMap',
                 'HomePage',
                 date('Y-m-d H:i:s'),
                 'pear-qa'
             );
 
             $prep = $wikidb->prepare($area_query);
             $res = $wikidb->execute($prep, $data);
 
             if (PEAR::isError($res)) {
                 report_error('Cannot create the area map');
                 response_footer();
                 exit;
             }
 
             $query = 'INSERT INTO yawiki_acl (id, seq, flag, username, priv,
             area, page) VALUES (?, ?, ?, ?, ?, ?, ?)';
 
             $maintainers = maintainer::get($_GET['id']);
             $accounts  = array();
 
             foreach ($maintainers as $handle => $row) {
                 if ($row['role'] == 'lead') {
                     $accounts[] = array(
                         $wikidb->nextId('_yawiki_acl_id'),
                         100, 1, $handle, 'area_admin', $_POST['name'], '*'
                     );
                 }
             }
 
             // Add the ACL only if there are lead(s) declared
             if (count($accounts) > 0) {
                 $prep = $wikidb->prepare($query);
                 $res = $wikidb->executeMultiple($prep, $accounts);
             }
 
             if (PEAR::isError($res)) {
                 report_error('Cannot create the wiki area ACL entries');
                 response_footer();
                 exit;
             }
         }
 
            echo "<b>Package information successfully updated.</b><br /><br />\n";
        }

} else if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'release_remove':
            if (!isset($_GET['release'])) {
                report_error('Missing package ID!');
                break;
            }

            if (release::remove($package_id, $_GET['release'])) {
                echo "<b>Release successfully deleted.</b><br /><br />\n";
            } else {
                report_error('An error occured while deleting the release!');
            }

            break;
    }
}

$row = package::info((int)$package_id);
if (empty($row['name'])) {
    report_error('Illegal package id');
    response_footer();
    exit;
}

print_package_navigation($row['packageid'], $row['name'],
                         '/package-edit.php?id=' . $row['packageid']);

?>

<form action="<?php echo $self; ?>?id=<?php echo $_GET['id']; ?>" method="POST">
<table class="form-holder" style="margin-bottom: 2em;" cellspacing="1">
<caption class="form-caption">Edit Package Information</caption>
<tr>
    <th class="form-label_left">Pa<span class="accesskey">c</span>kage Name:</th>
    <td class="form-input">
    <?php $form->displayText('name', $row['name'], 50, 80, 'accesskey="c"'); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">License:</th>
    <td class="form-input">
    <?php $form->displayText('license', $row['license'], 50, 20); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Summary:</th>
    <td class="form-input">
    <?php $form->displayTextarea("summary", $row['summary'], 40, 3, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Description:</th>
    <td class="form-input">
    <?php $form->displayTextarea("description", $row['description']); ?>
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

$form->displaySelect("category", $rows, $row['categoryid']);
?>
    </td>
</tr>
<tr>
    <th class="form-label_left">H<span class="accesskey">o</span>mepage:</th>
    <td class="form-input">
    <?php $form->displayText('homepage', $row['homepage'], 50, 255, 'accesskey="o"'); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Documentation URI:</th>
    <td class="form-input">
    <?php $form->displayText('doc_link', $row['doc_link'], 50, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Web CVS URI:</th>
    <td class="form-input">
    <?php $form->displayText('cvs_link', $row['cvs_link'], 50, 255); ?>
    </td>
</tr>
<tr>
    <th class="form-label_left">Activate wiki area ?</th>
    <td class="form-input">
    <?php $wiki_area = ($row['wiki_area'] == 1) ? 'readonly="readonly" disabled="disabled"' : '';
    $form->displayCheckbox('wiki_area', ($row['wiki_area']) ? true : false, $wiki_area); ?>
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
    echo '  <td class="form-input">' . $version . "</td>\n";
    echo '  <td class="form-input">';
    echo make_utc_date(strtotime($release['releasedate']));
    echo "</td>\n";
    echo '  <td class="form-input">' . "\n";

    $url = $self . '?id=' .
            $_GET['id'] . '&amp;release=' .
            $release['id'] . '&amp;action=release_remove';
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
