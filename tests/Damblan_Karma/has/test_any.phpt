--TEST--
Damblan_Karma->has() [any karma level]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.fronk')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.fronk', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertTrue($karma->has('cellog', 'pear.fronk'), 'pear.fronk');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.fronk')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertFalse($karma->has('cellog', 'pear.fronk'), 'none');
?>
===DONE===
--EXPECT--
===DONE===