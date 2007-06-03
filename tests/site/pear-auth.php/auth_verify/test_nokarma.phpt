--TEST--
auth_verify() [user doesn't have enough karma]
--FILE--
<?php
// setup
$_ENV['PEAR_TMPDIR'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
mkdir(PEAR_TMPDIR, 0777, true);
touch (PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog' AND registered = '1'", array (
  0 => 
  array (
    'handle' => 'cellog',
    'password' => md5('as if!'),
    'name' => 'Greg Beaver',
    'email' => 'greg@chiaraquartet.net',
    'homepage' => 'http://greg.chiaraquartet.net',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '1',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => 'http://www.chiaraquartet.net',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertFalse(array_key_exists('auth_user', $GLOBALS), 'setup');
$phpunit->assertFalse(auth_verify('cellog', 'as if!'), 'test');
$phpunit->assertEquals("", file_get_contents(PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log'), 'log');
$phpunit->assertTrue(array_key_exists('auth_user', $GLOBALS), 'auth_user set');
?>
===DONE===
--CLEAN--
<?php
unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby' . DIRECTORY_SEPARATOR . 'pear-errors.log');
rmdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testmebaby');
?>
--EXPECT--
===DONE===