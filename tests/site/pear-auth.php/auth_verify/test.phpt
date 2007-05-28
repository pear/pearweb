--TEST--
auth_verify()
--FILE--
<?php
// setup
$_ENV['PEAR_TMPDIR'] = dirname(__FILE__) . '/testmebaby';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM users WHERE registered = '1' AND handle = 'cellog'", array (
  0 => 
  array (
    'handle' => 'cellog',
    'password' => 'as if!',
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

$phpunit->assertFalse(array_key_exists('auth_user', $GLOBALS), 'setup');
$phpunit->assertTrue(auth_verify('cellog', 'bar'), 'test');
$phpunit->assertTrue(array_key_exists('auth_user', $GLOBALS), 'auth_user set');
?>
===DONE===
--EXPECT--
===DONE===