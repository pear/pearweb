--TEST--
PEAR_REST->deleteMaintainerREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require_once 'System.php';
System::mkdir(array('-p', $rdir . '/m/fronk'));
touch($rdir . '/m/fronk/info.xml');
// test
$rest->deleteMaintainerREST('fronk');
$phpunit->assertNoErrors('after');
$phpunit->assertFileNotExists($rdir . '/m/fronk', 'fronk');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===