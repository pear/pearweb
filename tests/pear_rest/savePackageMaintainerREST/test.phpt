--TEST--
PEAR_REST->savePackageMaintainerREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'PEAR'", array(
    array('id' => 123),
), array('id'));
$mock->addDataQuery("SELECT * FROM maintains WHERE package = 123", array(
    array('handle' => 'cellog', 'package' => 123, 'role' => 'lead', 'active' => 1),
    array('handle' => 'boober', 'package' => 123, 'role' => 'developer', 'active' => 0),
    array('handle' => 'fluber', 'package' => 123, 'role' => 'contributor', 'active' => 1),
), array('handle', 'package', 'role', 'active'));
// ===== test ======
$rest->savePackageMaintainerREST('PEAR');
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/p/pear/maintainers.xml', 'info');
$phpt->assertFileExists($rdir . '/p/pear/maintainers2.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/p/pear/') & 0777, 'folder permissions');
    $phpt->assertEquals(0666, fileperms($rdir . '/p/pear/maintainers.xml') & 0777, 'permissions');
    $phpt->assertEquals(0666, fileperms($rdir . '/p/pear/maintainers2.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers
    http://pear.php.net/dtd/rest.packagemaintainers.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <m><h>cellog</h><a>1</a></m> <m><h>boober</h><a>0</a></m> <m><h>fluber</h><a>1</a></m></m>',
file_get_contents($rdir . '/p/pear/maintainers.xml'), 'contents');
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers2
    http://pear.php.net/dtd/rest.packagemaintainers2.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <m><h>cellog</h><a>1</a><r>lead</r></m>
 <m><h>boober</h><a>0</a><r>developer</r></m>
 <m><h>fluber</h><a>1</a><r>contributor</r></m>
</m>',
file_get_contents($rdir . '/p/pear/maintainers2.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===