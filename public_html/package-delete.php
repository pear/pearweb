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
 * Interface to delete a package.
 */

@session_start();
$csrf_token_name = 'pear_csrf_token_' . basename(__FILE__, '.php');

response_header('Delete Package');
echo '<h1>Delete Package</h1>';

auth_require('pear.qa', 'pear.admin', 'pear.group');

if (!isset($_GET['id'])) {
    report_error('No package ID specified.');
    response_footer();
    exit;
}

$id = (int)$_GET['id'];

include_once 'pear-database-package.php';
if (!isset($_POST['confirm'])) {
    $pkg = package::info($id);
    print_package_navigation($id, $pkg['name'],
                             '/package-delete.php?id=' . $id);

    echo '<form action="' . 'package-delete.php?id=' . htmlspecialchars($id) . '" method="post">';
    echo '<table class="form-holder" style="margin-bottom: 2em;" cellspacing="1">';
    echo '<caption class="form-caption">Confirm</caption>';

    echo '<tr><td class="form-input">';
    echo 'Are you sure that you want to delete the package?' . "</td></tr>\n";

    echo '<tr><td class="form-input">';
    report_error('Deleting the package will remove all package information'
                 . ' and all releases!', 'warnings', 'WARNING:');
    echo "</td></tr>\n";

    echo '<td class="form-input">';
    echo '<input type="submit" value="yes" name="confirm" />';
    echo '&nbsp;';
    echo '<input type="submit" value="no" name="confirm" />';
    echo "</td></tr>\n";
    echo "</table>";
    echo '<input type="hidden" value="' . create_csrf_token($csrf_token_name) . '" name="' . $csrf_token_name . '" />';
    echo "</form>";

} elseif ($_POST['confirm'] == 'yes'
          && validate_csrf_token($csrf_token_name))
{

    // XXX: Implement backup functionality
    // make_backup($id);

    $tables = array('releases'  => 'package',
                    'maintains' => 'package',
                    'deps'      => 'package',
                    'files'     => 'package',
                    'packages'  => 'id');

    echo "<pre>\n";

    $file_rm = 0;

    $query = 'SELECT p.name, r.version FROM packages p, releases r
                WHERE p.id = r.package AND r.package = ?';

    $row = $dbh->getAll($query, array($id));

    foreach ($row as $value) {
        $file = sprintf("%s/%s-%s.tgz",
                        PEAR_TARBALL_DIR,
                        $value[0],
                        $value[1]);

        if (@unlink($file)) {
            echo "Deleting release archive \"" . $file . "\"\n";
            $file_rm++;
        } else {
            echo "<font color=\"#ff0000\">Unable to delete file " . $file . "</font>\n";
        }
    }

    echo "\n" . $file_rm . " file(s) deleted\n\n";

    $catid       = package::info($id, 'categoryid');
    $packagename = package::info($id, 'name');
    $dbh->query("UPDATE categories SET npackages = npackages - 1 WHERE id = $catid");

    foreach ($tables as $table => $field) {
        $query = sprintf("DELETE FROM %s WHERE %s = '%s'",
                         $table,
                         $field,
                         $id
                         );

        echo "Removing package information from table \"" . $table . "\": ";
        $dbh->query($query);

        echo "<b>" . $dbh->affectedRows() . "</b> rows affected.\n";
    }

    include_once 'pear-rest.php';
    $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
    $pear_rest->deletePackageREST($packagename);
    echo "</pre>\nPackage " . $id . " has been deleted.\n";
} else {
    $pkg = package::info($id);
    print_package_navigation($id, $pkg['name'], '/package-delete.php?id=' . $id);

    echo "The package has not been deleted.\n<br /><br />\n";
}

response_footer();
