--TEST--
Damblan_Karma->grant() [karma table insert fails]
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
$mock->addFailingQuery("INSERT INTO karma VALUES (1, 'foo', 'pear.dev', 'cellog', NOW())",
    "oops");
$karma->grant('foo', 'pear.dev');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')
), 'errors');
?>
===DONE===
--EXPECT--
===DONE===