<?php
$mock->addDataQuery("SELECT id FROM releases WHERE package = 1 AND state = 'stable' ORDER BY releasedate DESC", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT id FROM packages p WHERE ((p.package_type = 'pear' AND p.approved = 1) OR p.package_type = 'pecl') AND  p.name = 'Archive_Tar'",
array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT fullpath, basename, id FROM files WHERE release = 1",
array(
    array('fullpath' => dirname(__FILE__) . '/packages/Archive_Tar-1.3.2.tgz', 'basename' => 'Archive_Tar-1.3.2.tgz', 'id' => 1)
), array('fullpath', 'basename', 'id'));
$mock->addUpdateQuery("UPDATE aggregated_package_stats
            SET downloads=downloads+1
            WHERE
                package_id=1 AND
                release_id=1 AND
                yearmonth=\"" . date('Y-m-01') . "\"", array(), 1);
$mock->addUpdateQuery("UPDATE package_stats  SET dl_number = dl_number + 1, last_dl = '2007-06-18 21:35:34' WHERE pid = 1 AND rid = 1", array(), 1,
    array(
        'query' => "/UPDATE package_stats  SET dl_number = dl_number \\+ 1, last_dl = '(.+)' WHERE pid = 1 AND rid = 1/",
        'replace' => ''));