--TEST--
Damblan_Karma->grant()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'foo' AND level IN ('pear.dev','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addInsertQuery("INSERT INTO karma VALUES (1, 'foo', 'pear.dev', 'cellog', NOW())",
    array(), 1);
$r = $karma->grant('foo', 'pear.dev');
$phpt->assertTrue($r, 'return');
$phpt->assertNoErrors('errors');
$phpt->assertEquals(array (
  0 => 
  array (
    'priority' => NULL,
    'message' => 'cellog has updated karma for foo: Added level "pear.dev"',
  ),
), $o->events, 'events');
?>
===DONE===
--EXPECT--
===DONE===