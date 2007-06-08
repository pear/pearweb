--TEST--
Damblan_Karma->has() [pear.pepr.admin karma levels]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group','pear.pepr.admin')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($karma->has('cellog', 'pear.pepr.admin'), 'pear.admin');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group','pear.pepr.admin')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.group', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($karma->has('cellog', 'pear.pepr.admin'), 'pear.group');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group','pear.pepr.admin')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.pepr.admin', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($karma->has('cellog', 'pear.pepr.admin'), 'pear.pepr.admin');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group','pear.pepr.admin')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertFalse($karma->has('cellog', 'pear.pepr.admin'), 'none');
?>
===DONE===
--EXPECT--
===DONE===