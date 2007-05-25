--TEST--
PEAR_Auth->isAdmin()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$phpunit->assertFalse($auth_user->isAdmin(), 'no handle set');
$auth_user->handle = 'cellog';
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-25 01:09:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertTrue($auth_user->isAdmin(), 'test');
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'blah' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$auth_user->handle = 'blah';
$phpunit->assertFalse($auth_user->isAdmin('blah'), 'test 2');
?>
===DONE===
--EXPECT--
===DONE===