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
    $lastupdate = $dbh->getOne('SELECT lastupdate FROM stats_updated WHERE release_id = ' . $release[0]);
    if (PEAR::isError($lastupdate) || !$lastupdate) {
        $lastupdate = '0000-00-00 00:00:00';
        $dbh->query('INSERT INTO stats_updated (release_id, lastupdate) VALUES(' . $release[0] .
            ', "' . $lastupdate . '")');
    }
    $curtime = $dbh->getOne('SELECT NOW()');
    $all = $dbh->getAll($q = 'SELECT r.package, r.id, DATE_FORMAT(d.dl_when, "%Y-%m-01"), COUNT(*)
                          FROM releases r, downloads d
                          WHERE r.id = ' . $release[0] . '
                            AND d.release = r.id
                            AND d.dl_when > "' . $lastupdate . '"
                            AND d.package = r.package
                          GROUP BY YEAR(d.dl_when), MONTH(d.dl_when)
                          ORDER BY YEAR(d.dl_when) DESC, MONTH(d.dl_when) DESC');
    foreach ($all as $data) {
        $dbh->query('INSERT INTO aggregated_package_stats
            (package_id, release_id, yearmonth, downloads)
            VALUES(' . $data[0] . ',' . $data[1] . ',"' . $data[2] . '",' . $data[3] . ')
            ON DUPLICATE KEY UPDATE downloads=downloads+' . $data[3]);
    }
    $dbh->query('UPDATE stats_updated SET lastupdate="' . $curtime . '" WHERE release_id=' . $release[0]);
    // remove old download entries
    $dbh->query('DELETE FROM downloads WHERE dl_when < "' . $curtime . '" AND release=' . $release[0]);
}
