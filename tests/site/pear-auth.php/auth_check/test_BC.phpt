--TEST--
auth_check() [backwards compatibility]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$auth_user = new PEAR_Auth;
$auth_user->handle = 'cellog';
$phpunit->assertTrue(auth_check(false), 'test');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.dev','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertFalse(auth_check(false), 'test 2');


$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertTrue(auth_check(true), 'test 3');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertFalse(auth_check(true), 'test 4');
?>
===DONE===
--EXPECT--
===DONE===