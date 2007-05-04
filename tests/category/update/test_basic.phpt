--TEST--
category::update()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addUpdateQuery("UPDATE categories SET name = 'rename', description = 'new desc' WHERE id = 1", array(), 1);

// test
$id = category::update(1, 'rename', 'new desc');
$phpunit->assertEquals(1, $id, 'id');
$phpunit->assertEquals(array (
  0 => 'UPDATE categories SET name = \'rename\', description = \'new desc\' WHERE id = 1',
), $mock->queries, 'queries');
$phpunit->assertFileExists($restdir . '/c/test/info.xml', 'info.xml');
$phpunit->assertFileExists($restdir . '/c/test/packages.xml', 'packages.xml');
$phpunit->assertFileExists($restdir . '/c/test/packagesinfo.xml', 'packagesinfo.xml');
$phpunit->assertFileExists($restdir . '/c/categories.xml', 'packages.xml');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===