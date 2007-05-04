--TEST--
category::delete() [basic]
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
$id = category::add(array('name' => 'test', 'desc' => 'hi there'));
$phpunit->assertFileExists($restdir . '/c/test/info.xml', 'info.xml');
$phpunit->assertFileExists($restdir . '/c/test/packages.xml', 'packages.xml');
$phpunit->assertFileExists($restdir . '/c/test/packagesinfo.xml', 'packagesinfo.xml');
$phpunit->assertFileExists($restdir . '/c/categories.xml', 'categories.xml');
$mock->queries = array();

$mock->addDataQuery("SELECT name FROM categories WHERE id = 1", array(array('name' => 'test')), array('name'));
$mock->addDataQuery("SELECT parent FROM categories WHERE id = 1", array(array('parent' => null)), array('parent'));
$mock->addDataQuery("SELECT cat_left FROM categories WHERE id = 1", array(array('cat_left' => 1)), array('cat_left'));
$mock->addDataQuery("SELECT cat_right FROM categories WHERE id = 1", array(array('cat_right' => 2)), array('cat_right'));
$mock->addDeleteQuery("DELETE FROM categories WHERE id = 1", array(
    "SELECT * FROM categories WHERE name = 'test'" => array(),
    "SELECT * FROM categories ORDER BY name" => array(),
    ), 1);
$mock->addUpdateQuery("UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > 1 AND cat_right < 2", array(), 0);
$mock->addUpdateQuery("UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > 2", array(), 0);
$mock->addUpdateQuery("UPDATE categories SET parent = NULL WHERE parent = 1", array(), 0);

// test
category::delete(1);
$phpunit->assertEquals(array (
  0 => 'SELECT name FROM categories WHERE id = 1',
  1 => 'SELECT parent FROM categories WHERE id = 1',
  2 => 'SELECT cat_left FROM categories WHERE id = 1',
  3 => 'SELECT cat_right FROM categories WHERE id = 1',
  4 => 'DELETE FROM categories WHERE id = 1',
  5 => 'UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > 1 AND cat_right < 2',
  6 => 'UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > 2',
  7 => 'UPDATE categories SET parent = NULL WHERE parent = 1',
), $mock->queries, 'queries');
$phpunit->assertFileNotExists($restdir . '/c/test/info.xml', 'info.xml');
$phpunit->assertFileNotExists($restdir . '/c/test/packages.xml', 'packages.xml');
$phpunit->assertFileNotExists($restdir . '/c/test/packagesinfo.xml', 'packagesinfo.xml');
$phpunit->assertFileNotExists($restdir . '/c/categories.xml', 'categories.xml');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===