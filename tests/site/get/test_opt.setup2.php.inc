<?php
$mock->addDataQuery("SELECT id FROM packages p WHERE ((p.package_type = 'pear' AND p.approved = 1) OR p.package_type = 'pecl') AND  p.name = 'Archive_Tar'",
array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT fullpath, `release`, id FROM files WHERE UPPER(basename) = 'ARCHIVE_TAR-1.3.2.TGZ'",
array(
    array('fullpath' => dirname(__FILE__) . '/packages/Archive_Tar-1.3.2.tgz', 'release' => 1, 'id' => 1)
), array('fullpath', 'release', 'id'));
$mock->addUpdateQuery("UPDATE aggregated_package_stats
            SET downloads=downloads+1
            WHERE
                package_id=1 AND
                release_id=1 AND
                yearmonth=\"" . date('Y-m-01') . "\"", array(), 0);
$mock->addInsertQuery("INSERT INTO aggregated_package_stats
                (package_id, release_id, yearmonth, downloads)
                VALUES(1,1,'2007-06-01',1)", array(), 1);
$mock->addUpdateQuery("UPDATE package_stats  SET dl_number = dl_number + 1, last_dl = '2007-06-18 21:35:34' WHERE pid = 1 AND rid = 1", array(), 0,
    array(
        'query' => "/UPDATE package_stats  SET dl_number = dl_number \\+ 1, last_dl = '(.+)' WHERE pid = 1 AND rid = 1/",
        'replace' => ''));
$mock->addDataQuery("SELECT version, name, category FROM releases, packages WHERE package = 1 AND id = 1 AND packages.id=releases.package", array(
    array('version' => '1.3.2', 'name' => 'Archive_Tar', 'category' => 1),
), array('version', 'name', 'category'));
$mock->addInsertQuery("INSERT INTO package_stats (dl_number, package, `release`, pid, rid, cid, last_dl) VALUES (1, 'Archive_Tar', '1.3.2', 1, 1, NULL, '2007-06-18 22:22:00')",
    array(), array(), array(
    'query' => "/INSERT INTO package_stats \\(dl_number, package, `release`, pid, rid, cid, last_dl\\) VALUES \\(1, 'Archive_Tar', '1.3.2', 1, 1, NULL, '(.+)'\\)/", 'replace' =>''));