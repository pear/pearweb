<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2008 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/3_01.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Helgi Þormar Þorbjörnsson <helgi@php.net>                   |
   +----------------------------------------------------------------------+
   $Id$
*/

include_once 'HTML/Table.php';

// Sortable tables http://www.kryogenix.org/code/browser/sorttable/
$extra_header = '<script src="/javascript/sorttable.js"></script>';

response_header('Quality Assurance Initiative - Packages with patches on open reports',
    false, $extra_header);

$sql = "
SELECT
    packages.package_type,
    packages.name,
    bugdb.ts2,
    bugdb.id AS bug_id
FROM
    packages
    JOIN bugdb ON packages.name = bugdb.package_name AND bugdb.status IN ('Open', 'Assigned', 'Analyzed', 'Critical', 'Verified')
    JOIN bugdb_patchtracker ON bugdb.id = bugdb_patchtracker.bugdb_id
WHERE
    (packages.newchannel IS NULL OR packages.newchannel = '')
  AND
    (packages.newpackagename IS NULL OR packages.newpackagename = '')
GROUP BY
    packages.id, packages.name, bugdb.package_name, bugdb.id
ORDER BY
    packages.name";

$res        = $dbh->getAll($sql, null, DB_FETCHMODE_ASSOC);
$total_rows = $dbh->getOne('SELECT FOUND_ROWS()');

echo 'Checks <a href="#pear">PEAR</a> and <a href="#pecl">PECL</a><br />';
echo 'Found ' . $total_rows . ' reports with patches attached<br /><br />';

foreach ($res as $data) {
    $bugs[$data['package_type']][$data['name']]['bug_id'][]     = $data['bug_id'];
}

// PEAR
$table = new HTML_Table(array('class' => 'sortable'));
$table->setHeaderContents(0, 0, 'Package');
$table->setHeaderContents(0, 1, '# bugs');


$row = 1;
foreach ($bugs['pear'] as $name => $qa) {
    $table->addRow(array(
        make_link('/package/' . $name . '/', $name),
        make_link('#package_bugs_' . $name, count($qa['bug_id'])),
    ));
    $table->setCellAttributes($row, 1, 'style="text-align: center;"');
    $row++;
}

echo '<h2 id="pear">PEAR (' . count($bugs['pear']) . ')</h2>';
echo $table->toHTML();

echo '<h2>Details</h2>';
foreach ($bugs['pear'] as $name => $qa) {
    echo '<h3 id="package_bugs_' . $name . '">' . $name . '</h3>';
    echo '<ul>';
    foreach ($qa['bug_id'] as $bug_id) {
        echo '<li>' . make_link('/bugs/' . $bug_id, $bug_id) . '</li>';
    }
    echo '</ul>';
}

response_footer();
