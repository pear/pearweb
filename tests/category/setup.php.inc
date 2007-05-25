<?php
require dirname(dirname(__FILE__)) . '/mockDB/DB.php';
set_include_path(dirname(dirname(dirname(__FILE__))) . '/include' . PATH_SEPARATOR .
    get_include_path());
require_once 'DB.php';
$_ENV['PEAR_DATABASE_DSN'] = 'mock://pear:pear@localhost/pear';
$_SERVER['SERVER_NAME'] = 'localhost';
require_once 'pear-prepend.php';
$pear_rest = new pear_rest($restdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rest');

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
$mock = new mockDB_core;
$dbh->setMock($mock);
require dirname(dirname(__FILE__)) . '/phpt_test.php5.inc';
$phpunit = new PEAR_PHPTest;

require_once 'pear-database-category.php';
?>