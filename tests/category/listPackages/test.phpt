--TEST--
category::listPackages()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'test'", array(), array());
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'test2'", array(
                    array('id' => 1, 'name' => 'Foo'),
                    array('id' => 2, 'name' => 'Bar'),
                ), array('id', 'name'));

// test
$packages = category::listPackages('test');
$phpunit->assertEquals(array(), $packages, 'test 1');
$packages = category::listPackages('test2');
$phpunit->assertEquals(array (
  0 => 
  array (
    'id' => 1,
    'name' => 'Foo',
  ),
  1 => 
  array (
    'id' => 2,
    'name' => 'Bar',
  ),
), $packages, 'test 2');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===