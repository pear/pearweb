--TEST--
category::add() [complex]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addInsertQuery("INSERT INTO categories (id, name, description, parent)VALUES (1, 'test', 'hi there', 20)", array(), 1);
$mock->addDataQuery("select cat_right from categories where id = 20", array(
    array('cat_right' => '40')), array('cat_right'));
$mock->addUpdateQuery("update categories
                        set cat_left = 40, cat_right = 41
                        where id = 1", array(), 1);
$mock->addUpdateQuery("update categories set cat_left = cat_left+2
                        where cat_left > 40", array(), 25);
$mock->addUpdateQuery("update categories set cat_right = cat_right+2
                        where cat_right >= 40 and id <> 1", array(), 24);
$mock->addDataQuery("SELECT * FROM categories WHERE name = 'test'",
    array(array('id' => 1,
          'parent' => 20,
          'name' => 'test',
          'summary' => null,
          'description' => 'hi there',
          'npackages' => 0,
          'pkg_left' => 0,
          'pkg_right' => 0,
          'cat_left' => 40,
          'cat_right' => 41)),
    array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
          'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'test' AND p.approved = 1", array(), array());
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
$id = category::add(array('name' => 'test', 'desc' => 'hi there', 'parent' => 20));
$phpunit->assertEquals(1, $id, 'id');
$phpunit->assertEquals(array (
  0 => 'INSERT INTO categories (id, name, description, parent)VALUES (1, \'test\', \'hi there\', 20)',
  1 => 'select cat_right from categories where id = 20',
  2 => 'update categories
                        set cat_left = 40, cat_right = 41
                        where id = 1',
  3 => 'update categories set cat_left = cat_left+2
                        where cat_left > 40',
  4 => 'update categories set cat_right = cat_right+2
                        where cat_right >= 40 and id <> 1',
  5 => 'SELECT * FROM categories WHERE name = \'test\'',
  6 => 'SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = \'pear\' AND p.category = c.id AND c.name = \'test\' AND p.approved = 1',
  7 => 'SELECT * FROM categories ORDER BY name',
  8 => 'SELECT * FROM categories ORDER BY name',
), $mock->queries, 'queries');
$phpunit->assertFileExists($restdir . '/c/test/info.xml', 'info.xml');
$phpunit->assertFileExists($restdir . '/c/test/packages.xml', 'packages.xml');
$phpunit->assertFileExists($restdir . '/c/test/packagesinfo.xml', 'packagesinfo.xml');
$phpunit->assertFileExists($restdir . '/c/categories.xml', 'categories.xml');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===