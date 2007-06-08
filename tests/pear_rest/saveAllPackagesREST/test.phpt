--TEST--
PEAR_REST->saveAllPackagesREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT id, name FROM packages WHERE package_type = 'pear' AND approved = 1 ORDER BY name", array(
    array(
        'id' => 1,
        'name' => 'Blah',
    ),
    array(
        'id' => 1,
        'name' => 'Blah2',
    ),
    array(
        'id' => 1,
        'name' => 'Blah3',
    ),
    array(
        'id' => 1,
        'name' => 'Blah4',
    ),
), array('id', 'name'));

// ======TEST=========== //
$rest->saveAllPackagesREST();
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/p/packages.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/p/packages.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Blah4</p>
</a>', file_get_contents($rdir . '/p/packages.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===