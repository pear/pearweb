<?php
require dirname(dirname(dirname(__FILE__))) . '/mockDB/DB.php';
set_include_path(dirname(dirname(dirname(dirname(__FILE__)))) . '/include' . PATH_SEPARATOR .
    get_include_path());

$_ENV['PEAR_DATABASE_DSN'] = 'mock://pear:pear@localhost/pear';
require_once 'pear-prepend.php';

require_once 'DB.php';
// so we do local
$mock = new mockDB_core;
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
    'mock' => $mock,
);
$dbh = DB::connect($_ENV['PEAR_DATABASE_DSN'], $options);


require dirname(dirname(dirname(__FILE__))) . '/phpt_test.php5.inc';
$phpt = new PEAR_PHPTest;

?>