--TEST--
category::listAll()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM categories ORDER BY name",
    array(array('id' => 1,
          'parent' => null,
          'name' => 'test',
          'summary' => null,
          'description' => 'hi there',
          'npackages' => 0,
          'pkg_left' => 0,
          'pkg_right' => 0,
          'cat_left' => 1,
          'cat_right' => 2)),
    array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
          'pkg_right', 'cat_left', 'cat_right'));

// test
$all = category::listAll();
$phpunit->assertEquals(array (
  0 => 
  array (
    'id' => 1,
    'parent' => '',
    'name' => 'test',
    'summary' => '',
    'description' => 'hi there',
    'npackages' => 0,
    'pkg_left' => 0,
    'pkg_right' => 0,
    'cat_left' => 1,
    'cat_right' => 2,
  ),
), $all, 'cats');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===