<?php
require_once 'DB.php';
$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
if (PEAR::isError($dbh)) {
    die('Database connection failed');
}

$sql  = '
    SELECT r.id, p.name, files.packagexml
    FROM packages p
    LEFT JOIN (
        SELECT MAX(id) as id, version, package FROM releases GROUP BY package
    ) AS r ON p.id = r.package
    JOIN files ON r.id = files.release
    WHERE p.package_type = "pear"
    GROUP BY p.name';
$data = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

$i = 0;
foreach ($data as $d) {
    if (!preg_match('/<package[^>]+version\s*=\s*"2.0"/', $d['packagexml'])) {
        echo $d['name'] . "\n";
        $i++;
    }
}

echo "\n\nFound $i packages with only package xml v1 format in latest release\n";