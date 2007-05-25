--TEST--
user::update()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz'",
    array(  array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '0',
  ),
),
    array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active')
);

$mock->addInsertQuery("UPDATE users SET 
homepage = 'http://pear.php.net/',
active = 1 WHERE handle = 'dufuz'",
        array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://pear.php.net',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
          'cols' => array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active')
          )), 1);

$mock->addDataQuery("SELECT homepage FROM users WHERE handle = 'dufuz' AND registered = '0'",
    array(array('homepage' => 'http://pear.php.net/')),
    array('homepage')
);

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '1'",
        array(array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://pear.php.net',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '1',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  )),
          array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active')
);

$mock->addInsertQuery("UPDATE users SET 
homepage = 'http://pear.php.net/',
active = 1,
registered = 1 WHERE handle = 'dufuz'", array(), 1);

// ============= test =============
$data = array(
    'handle'   => 'dufuz',
    'homepage' => 'http://pear.php.net/',
    'active'   => true,
);
$res = user::update($data);
$phpunit->assertTrue($res, 'test 1');

$info = user::info('dufuz', 'homepage', false);
$phpunit->assertEquals(array('homepage' => 'http://pear.php.net/'), $info, 'test 2');

$data['registered'] = true;
$res = user::update($data, true);
$phpunit->assertTrue($res, 'test 3');

$info = user::info('dufuz');
$phpunit->assertEquals('1', $info['registered'], 'test 4');

$phpunit->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'dufuz\'',
  1 => 'UPDATE users SET 
homepage = \'http://pear.php.net/\',
active = 1 WHERE handle = \'dufuz\'',
  2 => 'SELECT homepage FROM users WHERE handle = \'dufuz\' AND registered = \'0\'',
  3 => 'SELECT * FROM users WHERE handle = \'dufuz\'',
  4 => 'UPDATE users SET 
homepage = \'http://pear.php.net/\',
active = 1,
registered = 1 WHERE handle = \'dufuz\'',
  5 => 'SELECT * FROM users WHERE handle = \'dufuz\' AND registered = \'1\'',
), $mock->queries, 'queries');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===