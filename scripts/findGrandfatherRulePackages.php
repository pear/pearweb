<?php
require_once 'DB.php';
$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
if (PEAR::isError($dbh)) {
    die('Database connection failed');
}

$sql = '
    SELECT p.name, r.version
    FROM packages p
    LEFT JOIN (
        SELECT MAX(version) as version, package, state FROM releases GROUP BY package
    ) AS r ON r.package = p.id
    WHERE state = "stable" AND version < 1 AND p.package_type = "pear" 
    ORDER BY p.name';
$data = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

foreach ($data as $d) {
    echo $d['name'], "\n";
}

echo "\n\nFound ", count($data), " packages which are marked as stable, but version < 1.0\n";

