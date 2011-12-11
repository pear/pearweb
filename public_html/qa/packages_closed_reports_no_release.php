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

/*
 * This page give you a list of all packages with closed bug report
 * but hasn't had a release in X amount of time
 */
include_once 'HTML/Table.php';

// Sortable tables http://www.kryogenix.org/code/browser/sorttable/
$extra_header = '<script type="text/javascript" src="/javascript/sorttable.js"></script>';

response_header('Quality Assurance Initiative - Packages with closed reports but no release in the last 6 months',
    false, $extra_header);

// Just so we don't fetch bugs for packages that recently had a releases
$min_release_date = strtotime('-6 Months');

$sql = "SELECT
    packages.package_type,
    packages.name,
    bugdb.ts2,
    bugdb.id AS bug_id,
    UNIX_TIMESTAMP(r.releasedate) as releasedate
FROM
    packages
    JOIN bugdb ON packages.name = bugdb.package_name AND bugdb.status = 'Closed'
    LEFT JOIN (
        SELECT package, MAX(releasedate) as releasedate FROM releases GROUP BY package
    ) as r ON packages.id = r.package
WHERE";

// In case we want to show all packages, including the superseeded ones.
if (!isset($_GET['showall'])) {
    $sql .= "
    (packages.newchannel IS NULL OR packages.newchannel = '')
  AND
    (packages.newpackagename IS NULL OR packages.newpackagename = '')
  AND";
}

$sql .= "
    UNIX_TIMESTAMP(r.releasedate) < UNIX_TIMESTAMP(bugdb.ts2)
  AND
    UNIX_TIMESTAMP(r.releasedate) < $min_release_date
GROUP BY
    packages.id, packages.name, bugdb.package_name, bugdb.id, r.package
ORDER BY
    r.releasedate";

$res        = $dbh->getAll($sql, null, DB_FETCHMODE_ASSOC);
$total_rows = $dbh->getOne('SELECT FOUND_ROWS()');

echo 'Checks <a href="#pear">PEAR</a> and <a href="#pecl">PECL</a><br />';
echo 'Found ' . $total_rows . ' reports that have been closed but their package has not had a release in 6 months<br /><br />';

$bugs = array('pear' => array(), 'pecl' => array());
foreach ($res as $data) {
    $bugs[$data['package_type']][$data['name']]['bug_id'][]     = $data['bug_id'];
    $bugs[$data['package_type']][$data['name']]['last_release'] = $data['releasedate'];
}

// PEAR
$table = new HTML_Table(array('class' => 'sortable'));
$table->setHeaderContents(0, 0, 'Package');
$table->setHeaderContents(0, 1, '# bugs');
$table->setHeaderContents(0, 2, 'Last Release Date');


$row = 1;
foreach ($bugs['pear'] as $name => $qa) {
    $table->addRow(array(
        make_link('/package/' . $name . '/', $name),
        make_link('/bugs/search.php?cmd=display&package_name[]=' . $name . '&status=CRSLR', count($qa['bug_id'])),
        format_date($qa['last_release'])
    ));
    $table->setCellAttributes($row, 1, 'style="text-align: center;"');
    $row++;
}

echo '<h2 id="pear">PEAR (' . count($bugs['pear']) . ')</h2>';
echo $table->toHTML();

// PECL
$table = new HTML_Table(array('class' => 'sortable'));
$table->setHeaderContents(0, 0, 'Package');
$table->setHeaderContents(0, 1, '# bugs');
$table->setHeaderContents(0, 2, 'Last Release Date');

response_footer();
