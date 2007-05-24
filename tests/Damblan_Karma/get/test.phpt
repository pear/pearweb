--TEST--
Damblan_Karma->get()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'nobody'",
array(), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog'",
array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.dev', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    array('id' => 2, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    array('id' => 3, 'user' => 'cellog', 'level' => 'pear.election', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    ), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertEquals(array(), $karma->get('nobody'), 'nobody');
$phpunit->assertEquals(array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.dev', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    array('id' => 2, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    array('id' => 3, 'user' => 'cellog', 'level' => 'pear.election', 'granted_by' => 'cellog', 'granted_at' => '2007-05-23 00:00:00'),
    ), $karma->get('cellog'), 'nobody');
?>
===DONE===
--EXPECT--
===DONE===