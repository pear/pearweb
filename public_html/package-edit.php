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

@session_start();
$csrf_token_name = 'pear_csrf_token_' . basename(__FILE__, '.php');

auth_require('pear.dev');

require_once 'tags/Manager.php';
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';
/**
 * @todo Remove once part of QF2
*/
require_once 'HTML/QuickForm2/Element/InputUrl.php';


response_header('Edit Package');
?>

<script type="text/javascript">
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

require_once 'pear-database-user.php';
if (!user::maintains($auth_user->handle, $_GET['id'], 'lead')
    && !user::isAdmin($auth_user->handle)
    && !user::isQA($auth_user->handle)
) {
    report_error('Editing only permitted by package leads, PEAR Admins or PEAR QA');
    response_footer();
    exit;
}

// Update
require_once 'pear-database-package.php';
if (isset($_POST['submit'])) {
    if (!validate_csrf_token($csrf_token_name)) {
        report_error('Invalid token.');
    } elseif (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        report_error('You have to enter values for name, license and summary!');
    } elseif (($_POST['new_channel'] && !$_POST['new_package'])
        || ($_POST['new_package'] && !$_POST['new_channel'])
    ) {
        report_error('You have to enter both channel + package name for packages moved out of PEAR!');
    } else {
        $query = '
            UPDATE packages SET
                name = ?,
                license = ?,
                summary = ?,
                description = ?,
                category = ?,
                homepage = ?,
                doc_link = ?,
                bug_link = ?,
                cvs_link = ?,
                unmaintained = ?,
                newpk_id = ?,
                newchannel = ?,
                newpackagename = ?
            WHERE id = ?';

        if (!empty($_POST['newpk_id'])) {
            $_POST['new_channel'] = 'pear.php.net';
            $_POST['new_package'] = $dbh->getOne(
                'SELECT name from packages WHERE id = ?',
                array($_POST['newpk_id'])
            );
            if (!$_POST['new_package']) {
                $_POST['new_channel'] = $_POST['newpk_id'] = null;
            }
        } else {
            if ($_POST['new_channel'] == 'pear.php.net') {
                $_POST['newpk_id'] = $dbh->getOne(
                    'SELECT id from packages WHERE name = ?',
                    array($_POST['new_package'])
                );
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
            $_POST['doc_link'],
            $_POST['bug_link'],
            $_POST['cvs_link'],
            isset($_POST['unmaintained']) ? 1 : 0 ,
            !empty($_POST['newpk_id']) ? $_POST['newpk_id'] : null,
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
                    if (!$tag) {
                        continue;
                    }
                    $manager->createPackageTag($tag, $_POST['name']);
                }
            }

            include_once 'pear-rest.php';
            $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
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

print_package_navigation(
    $row['packageid'], $row['name'],
    '/package-edit.php?id=' . $row['packageid']
);

$sth = $dbh->query('SELECT id, name FROM categories ORDER BY name');

while ($cat_row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rows[$cat_row['id']] = $cat_row['name'];
}

$form = new HTML_QuickForm2('package-edit', 'post', array('action' => '/package-edit.php?id=' . $row['packageid']));
$form->removeAttribute('name');

$renderer = HTML_QuickForm2_Renderer::factory('default');

    // Set defaults for the form elements
    $form->addDataSource(
        new HTML_QuickForm2_DataSource_Array(
            array(
            'name'         => htmlspecialchars($row['name']),
            'license'      => htmlspecialchars($row['license']),
            'summary'      => htmlspecialchars($row['summary']),
            'description'  => htmlspecialchars($row['description']),
            'category'     => (int)$row['categoryid'],
            'homepage'     => htmlspecialchars($row['homepage']),
            'doc_link'     => htmlspecialchars($row['doc_link']),
            'bug_link'     => htmlspecialchars($row['bug_link']),
            'cvs_link'     => htmlspecialchars($row['cvs_link']),
            'unmaintained' => ($row['unmaintained']) ? true : false,
            'newpk_id'     => (int)$row['newpk_id'],
            'new_channel'  => htmlspecialchars($row['new_channel']),
            'new_package'  => htmlspecialchars($row['new_package']),
            )
        )
    );

    $form->addElement('text', 'name', array('maxlength' => "80",  'accesskey' => "c"))->setLabel('Pa<span class="accesskey">c</span>kage Name');
    $form->addElement('text', 'license', array('maxlength' => "50", 'placeholder' => 'BSD'))->setLabel('License:');
    $form->addElement('textarea', 'summary', array('cols' => "75", 'rows' => "7", 'maxlength' => "255"))->setLabel('Summary');
    $form->addElement('textarea', 'description', array('cols' => "75", 'rows' => "12"))->setLabel('Description');
    $form->addElement('select', 'category')->setLabel('Category:')->loadOptions($rows);

    $manager = new Tags_Manager;

    $sl = $form->addElement('select', 'tags', array('multiple' => 'multiple'))->setLabel('Tags:')->loadOptions(array('' => '(none)') + $manager->getTags(false, true));
    $sl->setValue(array_keys($manager->getTags($row['name'], true)));


    $form->addElement('text', 'homepage', array('maxlength' => 255, 'accesskey' => "O"))->setLabel('H<span class="accesskey">o</span>mepage:');
    $form->addElement('text', 'doc_link', array('maxlength' => 255, 'placeholder' => 'http://example.com/manual'))->setLabel('Documentation URI:');
    $form->addElement('url', 'bug_link', array('maxlength' => 255, 'placeholder' => 'http://example.com/bugs'))->setLabel('Bug Tracker URI:');
    $form->addElement('url', 'cvs_link', array('maxlength' => 255, 'placeholder' => 'http://example.com/svn/trunk'))->setLabel('Web version control URI');
    $form->addElement('checkbox', 'unmaintained')->setLabel('Is this package unmaintained ?');

    $packages = package::listAllwithReleases();

    $rows = array(0 => '');
    foreach ($packages as $id => $info) {
        if ($id == $_GET['id']) {
            continue;
        }
        $rows[$id] = $info['name'];
    }

    $form->addElement('select', 'newpk_id')->setLabel('Superseeded by:')->loadOptions($rows);

    $form->addElement('text', 'new_channel', array('maxlength' => 255, 'placeholder' => 'pear.phpunit.de'))->setLabel('Moved to channel:');
    $form->addElement('text', 'new_package', array('maxlength' => 255, 'placeholder' => 'PHPUnit'));


    $form->addElement('submit', 'submit')->setLabel('Save Changes');
    $csrf_token_value = create_csrf_token($csrf_token_name);
    $form->addElement('hidden', $csrf_token_name)->setValue($csrf_token_value);


    print $form->render($renderer);
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
    echo format_date(strtotime($release['releasedate']));
    echo "</td>\n";
    echo '  <td class="form-input">' . "\n";

    $url = 'package-edit.php?id=' .
            (int) $_GET['id'] . '&amp;release=' .
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
