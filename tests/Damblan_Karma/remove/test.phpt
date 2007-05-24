--TEST--
Damblan_Karma->remove()
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
$mock->addDeleteQuery("DELETE FROM karma WHERE user = 'foo' AND level = 'pear.dev'", array(),
    1);
$r = $karma->remove('foo', 'pear.dev');
$phpunit->assertTrue($r, 'return');
$phpunit->assertNoErrors('errors');
$phpunit->assertEquals(array (
  0 => 
  array (
    'priority' => NULL,
    'message' => 'cellog has updated karma for foo: Removed level "pear.dev"',
  ),
), $o->events, 'events');
?>
===DONE===
--EXPECT--
===DONE===