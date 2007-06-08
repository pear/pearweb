--TEST--
PEAR_REST->saveCategoryREST() [database failure 2]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM categories WHERE name = 'Testing/Stuff'", array(
  array (
    'id' => '43',
    'parent' => '29',
    'name' => 'Testing/Stuff',
    'summary' => NULL,
    'description' => 'Packages for creating test suites',
    'npackages' => '-1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '58',
    'cat_right' => '59',
  )
  ),
  array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left', 'pkg_right',
    'cat_left', 'cat_right'));
$mock->addFailingQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'Testing/Stuff' AND p.approved = 1", "stupid person");

// ===== test ======
$rest->saveCategoryREST('Testing/Stuff');
$phpt->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')), 'after');
$phpt->assertFileNotExists($rdir . '/c/categories.xml', 'info');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===