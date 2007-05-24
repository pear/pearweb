--TEST--
category::add() [basic]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addInsertQuery("INSERT INTO categories (id, name, description, parent)VALUES (1, 'test', 'hi there', NULL)", array(), 1);
$mock->addDataQuery("select max(cat_right) + 1 from categories
                              where parent is null", array(
    array('max(cat_right) + 1' => '1')), array('max(cat_right) + 1'));
$mock->addUpdateQuery("update categories
                        set cat_left = 1, cat_right = 2
                        where id = 1", array(), 1);
$mock->addDataQuery("SELECT * FROM categories WHERE name = 'test'",
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
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'test'", array(
                ), array('id', 'name'));    

// test
$id = category::add(array('name' => 'test', 'desc' => 'hi there'));
$phpunit->assertEquals(1, $id, 'id');
$phpunit->assertEquals(array (
  0 => 'INSERT INTO categories (id, name, description, parent)VALUES (1, \'test\', \'hi there\', NULL)',
  1 => 'select max(cat_right) + 1 from categories
                              where parent is null',
  2 => 'update categories
                        set cat_left = 1, cat_right = 2
                        where id = 1',
  3 => 'SELECT * FROM categories WHERE name = \'test\'',
  4 => 'SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = \'pear\' AND p.category = c.id AND c.name = \'test\' AND p.approved = 1',
  5 => 'SELECT * FROM categories ORDER BY name',
  6 => 'SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = \'test\'',
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