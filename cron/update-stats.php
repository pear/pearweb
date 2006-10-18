<?php
require dirname(dirname(__FILE__)) . '/include/pear-config.php';
require 'DB.php';
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
$releases = $dbh->getAll('SELECT id from releases');
foreach ($releases as $release) {
    $all = $dbh->getAll($q = 'SELECT r.package, r.id, DATE_FORMAT(d.dl_when, "%Y-%m-01"), COUNT(*)
                          FROM releases r, downloads d
                          WHERE r.id = ' . $release[0] . '
                            AND d.release = r.id
                            AND d.package = r.package
                          GROUP BY YEAR(d.dl_when), MONTH(d.dl_when)
                          ORDER BY YEAR(d.dl_when) DESC, MONTH(d.dl_when) DESC');
    foreach ($all as $data) {
        $dbh->query('INSERT INTO aggregated_package_stats
            (package_id, release_id, yearmonth, downloads)
            VALUES(' . $data[0] . ',' . $data[1] . ',"' . $data[2] . '",' . $data[3] . ')
            ON DUPLICATE KEY UPDATE downloads=downloads+' . $data[3]);
    }
}