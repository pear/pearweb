<?php
require_once 'DB.php';
require_once 'Console/Table.php';

$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
if (PEAR::isError($dbh)) {
    die('Database connection failed');
}

$sql = '
SELECT p.license, COUNT(p.id) as count
FROM packages p
WHERE p.package_type = "pear"
GROUP BY p.license';

$data = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

$table = new Console_Table();
$table->setHeaders(array('License', 'Amount of packages'));
$table->addData($data);

echo $table->getTable();