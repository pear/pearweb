--TEST--
PEAR_Auth->isQA()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$phpt->assertFalse($auth_user->isAdmin(), 'no handle set');
$auth_user->handle = 'cellog';
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.qa','pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-25 01:09:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($auth_user->isQA(), 'test');
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'blah' AND level IN ('pear.qa','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$auth_user->handle = 'blah';
$phpt->assertFalse($auth_user->isQA('blah'), 'test 2');
?>
===DONE===
--EXPECT--
===DONE===