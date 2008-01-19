--TEST--
PEAR_REST->saveCategoryREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM categories WHERE name = 'Testing'", array(), DB_FETCHMODE_ASSOC));
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
$mock->addDataQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'Testing/Stuff' AND p.approved = 1",
    array(
        array('name' => 'Blah'),
        array('name' => 'Blah2'),
        array('name' => 'Blah3'),
        array('name' => 'Blah4'),
    ), array('name'));

// ======= test =========
$rest->saveCategoryREST('Testing/Stuff');
$phpt->assertNoErrors('after');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/c/') & 0777, 'folder category permissions');
}
$phpt->assertFileExists($rdir . '/c/Testing%2FStuff/info.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/c/Testing%2FStuff/info.xml') & 0777, 'permissions');
}
$phpt->assertFileExists($rdir . '/c/Testing%2FStuff/packages.xml', 'packages');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/c/Testing%2FStuff/packages.xml') & 0777, 'packages permissions');
}
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/c/Testing%2FStuff/') & 0777, 'folder package permissions');
}

$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<c xmlns="http://pear.php.net/dtd/rest.category"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.category
    http://pear.php.net/dtd/rest.category.xsd">
 <n>Testing/Stuff</n>
 <c>pear.php.net</c>
 <a>Testing/Stuff</a>
 <d>Packages for creating test suites</d>
</c>', file_get_contents($rdir . '/c/Testing%2FStuff/info.xml'), 'contents');
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<l xmlns="http://pear.php.net/dtd/rest.categorypackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackages
    http://pear.php.net/dtd/rest.categorypackages.xsd">
 <p xlink:href="/rest/p/blah">Blah</p>
 <p xlink:href="/rest/p/blah2">Blah2</p>
 <p xlink:href="/rest/p/blah3">Blah3</p>
 <p xlink:href="/rest/p/blah4">Blah4</p>
</l>', file_get_contents($rdir . '/c/Testing%2FStuff/packages.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===