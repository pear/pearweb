--TEST--
auth_verify() [user not registered]
--FILE--
<?php
// setup
$_ENV['PEAR_TMPDIR'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
mkdir(PEAR_TMPDIR, 0777, true);
touch (PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog' AND registered = '1'", array (
), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertFalse(array_key_exists('auth_user', $GLOBALS), 'setup');
$phpt->assertFalse(auth_verify('cellog', 'as if!'), 'test');
$phpt->assertEquals("pear-auth: user `cellog' not registered\n", file_get_contents(PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log'), 'log');
$phpt->assertTrue(array_key_exists('auth_user', $GLOBALS), 'auth_user set');
?>
===DONE===
--CLEAN--
<?php
unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby' . DIRECTORY_SEPARATOR . 'pear-errors.log');
rmdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby');
?>
--EXPECT--
===DONE===