--TEST--
PEAR_REST->deleteReleaseREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require_once 'System.php';
System::mkdir(array('-p', $rdir . '/r/foo'));
touch($rdir . '/r/foo/1.0.0.xml');
touch($rdir . '/r/foo/v2.1.0.0.xml');
touch($rdir . '/r/foo/package.1.0.0.xml');
touch($rdir . '/r/foo/deps.1.0.0.txt');
// test
$rest->deleteReleaseREST('Foo', '1.0.0');
$phpunit->assertNoErrors('after');
$phpunit->assertFileNotExists($rdir . '/r/foo/1.0.0.xml', '1.0.0.xml');
$phpunit->assertFileNotExists($rdir . '/r/foo/v2.1.0.0.xml', 'v2.1.0.0.xml');
$phpunit->assertFileNotExists($rdir . '/r/foo/package.1.0.0.xml', 'package.1.0.0.xml');
$phpunit->assertFileNotExists($rdir . '/r/foo/deps.1.0.0.txt', 'deps.1.0.0.txt');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===