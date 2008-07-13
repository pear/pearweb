<?php
require_once 'DB.php';
require_once 'Archive/Tar.php';
// before running this file, please run
// ALTER TABLE files ADD packagexml TEXT NOT NULL DEFAULT "";

$dbh = &DB::connect('mysql://pear:pear@localhost/pear');
$rows = $dbh->getAssoc("SELECT fullpath, `release`, id FROM files", false, null, DB_FETCHMODE_ASSOC);
foreach ($rows as $file => $info) {
    $tar = &new Archive_Tar($file);
    if ($packagexml = $tar->extractInString('package.xml')) {
        $dbh->query('UPDATE files SET packagexml = ? WHERE `release` = ? AND id = ?', array($packagexml,
            $info['release'], $info['id']));
    }
}
?>