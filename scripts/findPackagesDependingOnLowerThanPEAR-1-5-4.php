<?php
require_once 'DB.php';
$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
if (PEAR::isError($dbh)) {
    die('Database connection failed');
}

$sql = '
    SELECT r.id, p.name, deps.version
    FROM packages p
    LEFT JOIN (
        SELECT MAX(id) as id, version, package FROM releases GROUP BY package
    ) AS r ON p.id = r.package
    JOIN deps on r.id = deps.release
    WHERE p.package_type = "pear"
        AND deps.name = "PEAR Installer"
        AND deps.version < "1.5.4"
    ORDER BY p.name';
$data = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

foreach ($data as $d) {
    echo $d['name'], ' [', $d['version'], "] \n";
}

echo "\n\nFound ", count($data), " packages which depends on PEAR Installer older than 1.5.4\n";

