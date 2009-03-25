<?php
require_once 'DB.php';
require_once 'Console/Table.php';

$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
if (PEAR::isError($dbh)) {
    die('Database connection failed');
}

$sql = '
SELECT p.name, p.license
FROM packages p
WHERE p.package_type = "pear" AND p.license LIKE "%PHP%"
ORDER BY p.name';

$data = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

$table = new Console_Table();
$table->setHeaders(array('Package (' . count($data) .')', 'License'));
$table->addData($data);

echo $table->getTable();