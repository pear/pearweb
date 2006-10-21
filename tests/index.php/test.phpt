--TEST--
--SKIPIF--
<?php
if (version_compare(phpversion(), '5.1.0', '<=')) {
    echo 'skip requires PHP 5.1+';
}
?>
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$selenium->open('http://' . $pearsite . '/index.php');
$phpunit->assertEquals('PEAR :: The PHP Extension and Application Repository', $selenium->getTitle(),
    'page title');
$phpunit->assertEquals('Register | Login | Documentation | Packages | Support | Bugs Search for in the Packages This site (using Yahoo!) Developers Developer mailing list General mailing list CVS commits mailing list', $selenium->getText(urlencode('head-menu')), 'head-menu');
$phpunit->assertEquals('Main: Home News Quality Assurance The PEAR Group Documentation: About PEAR Manual FAQ Support Downloads: List Packages Search Packages Statistics Package Proposals: Browse Proposals New Proposal Developers: List Accounts', $selenium->getText(urlencode('sidebar')), 'sidebar');
$selenium->stop();
echo 'tests done';
?>
--EXPECT--
tests done