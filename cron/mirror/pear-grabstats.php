<?php
/**
 * Grab download statistics from mirrors
 * 
 * This queries /download_stats.php from each mirror and uses the results to update
 * the aggregated_package_stats and package_stats tables
 */
set_time_limit(0);
require_once 'DB.php';
require_once dirname(dirname(dirname(__FILE__))) . '/include/pear-config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/include/pear-format-html.php';

// Get the database class.
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh = DB::connect(PEAR_DATABASE_DSN, $options);
if (PEAR::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$mirrors = $dbh->getAll('SELECT * from pear_mirrors', array(), DB_FETCHMODE_ASSOC);
foreach ($mirrors as $mirror) {
    $server = 'http://' . $mirror['mirrorserver'] . '/download_stats.php?last_dl=' .
        $mirror['last_sync'];
    $contents = @file_get_contents($server);
    if (!$contents) {
        continue;
    }
    $xml = @simplexml_load_string($contents);
    if (!$xml) {
        continue;
    }
    $curpackage = null;
    foreach ($xml as $tag => $value) {
        if ($tag == 'p') {
            $curpackage = (string) $value;
            $package = $dbh->getOne('SELECT id FROM packages WHERE name=?
                AND package_type="pear"',
                array($curpackage));
            if (!$package) {
                continue;
            }
            $versionmap = array();
        } elseif ($tag == 'r') {
            if (!$package) {
                continue;
            }
            foreach ($value->v as $info) {
                $version = (string) $info->n;
                $id = $dbh->getOne('SELECT id FROM releases WHERE package=?
                    AND version=?', array($package, $version));
                if (!$id) {
                    continue;
                }
                $versionmap[$version] = $id;
                $count = intval($info->c);
                $sql = 'UPDATE package_stats set dl_number=dl_number+' .
                    $count . ' WHERE rid=?';
                $dbh->query($sql, array($id));
            }
            $sql = '';
        } elseif ($tag == 'a') {
            if (!$package) {
                continue;
            }
            foreach ($value->v as $info) {
                $version = (string) $info->n;
                if (!isset($versionmap[$version])) {
                    continue;
                }
                $count = intval($info->c);
                $time = (string) $info->t;
                $year = substr($time, 0, 4);
                $mon = substr($time, 4, 2);
                $sql = 'UPDATE aggregated_package_stats set downloads=downloads+' .
                    $count . ' WHERE release_id=? AND yearmonth=?';
                $dbh->query($sql, array($id, $year . '-' . $mon . '-01'));
            }
        } elseif ($tag == 'l') {
            $dbh->query('UPDATE pear_mirrors set last_sync=? WHERE mirrorserver=?',
                array((string)$value, $mirror['mirrorserver']));
        }
    }
}