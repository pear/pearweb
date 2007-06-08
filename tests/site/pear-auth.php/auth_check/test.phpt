--TEST--
auth_check()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$auth_user = new PEAR_Auth;
$auth_user->handle = 'cellog';
$phpt->assertTrue(auth_check('pear.user'), 'test');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertFalse(auth_check('pear.user'), 'test 2');
?>
===DONE===
--EXPECT--
===DONE===