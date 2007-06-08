--TEST--
auth_logout()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require 'pear-database-user.php';

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog' AND registered = '1'",
array (
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

$data = user::info('cellog', null, true, false);
$auth_user = new PEAR_Auth;
$auth_user->data($data);
$phpt->assertEquals(array (
  'handle' => 'cellog',
  'password' => 'as if!',
  'name' => 'Greg Beaver',
  'email' => 'greg@chiaraquartet.net',
  'homepage' => 'http://greg.chiaraquartet.net',
  'created' => '2002-11-22 16:16:00',
  'createdby' => 'richard',
  'lastlogin' => '',
  'showemail' => '0',
  'registered' => '1',
  'admin' => '0',
  'userinfo' => '',
  'pgpkeyid' => '1F81E560',
  'pgpkey' => '',
  'wishlist' => 'http://www.chiaraquartet.net',
  'longitude' => '-96.6831931472',
  'latitude' => '40.7818087725',
  'active' => '1',
), (array) $auth_user, 'test');
?>
===DONE===
--EXPECT--
===DONE===