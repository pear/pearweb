--TEST--
PEAR_REST->saveAllMaintainersREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT handle FROM users WHERE registered = 1 ORDER BY handle", array(
    array('handle' => 'boo'),
    array('handle' => 'hoo'),
    array('handle' => 'ya'),
    array('handle' => 'big'),
    array('handle' => 'baby'),
), array('handle'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'boo' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'boo', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'hoo' AND level IN ('pear.dev','pear.admin','pear.group')", array(), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'ya' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'ya', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'big' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'big', 'level' => 'pear.admin', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
    array('id' => 1, 'user' => 'big', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'baby' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'baby', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
// ===== test ======
$rest->saveAllMaintainersREST();
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/m/allmaintainers.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/m/allmaintainers.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.allmaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allmaintainers
    http://pear.php.net/dtd/rest.allmaintainers.xsd">
 <h xlink:href="/rest/m/boo">boo</h>
 <h xlink:href="/rest/m/ya">ya</h>
 <h xlink:href="/rest/m/big">big</h>
 <h xlink:href="/rest/m/baby">baby</h>
</m>',
file_get_contents($rdir . '/m/allmaintainers.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===