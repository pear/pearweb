<?php
require dirname(dirname(__FILE__)) . '/phpt_test.php5.inc';
require dirname(dirname(__FILE__)) . '/firefox_settings.php.inc';
require 'Testing/Selenium.php';
$phpunit = new PEAR_PHPTest;
$selenium = new Testing_Selenium("*firefox", "http://pear.chiara/index.php", 'localhost', 4444, 30000, 'native');
$path = getenv('PATH');
putenv('PATH=' . $path . PATH_SEPARATOR . $firefoxpath);
$selenium->start();
putenv('PATH=' . $path);