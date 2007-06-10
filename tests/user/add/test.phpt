--TEST--
user::add() [basic]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz'", array(), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addInsertQuery("
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                ('dufuz', 'Helgi Thormar', 'dufuz@php.net', 'http://www.helgi.ws/', 0, '5d8052a59cae407c50bf4056bc8c9014', 0, 'a:2:{i:0;s:14:\"do nifty tests\";i:1;s:6:\"hippie\";}', 'pear')",
                array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array (
    'handle' => 'dufuz',
    'password' => '5d8052a59cae407c50bf4056bc8c9014',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => 'a:2:{i:0;s:14:\"do nifty tests\";i:1;s:6:\"hippie\";}',
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

$mock->addDataQuery("SELECT * FROM users WHERE handle = '1337'", array(), array()); 


/****** test ******/
$data = array(
    'handle'    => 'dufuz',
    'firstname' => 'Helgi',
    'lastname'  => 'Thormar',
    'email'     => 'dufuz@php.net',
    'purpose'   => 'do nifty tests',
    'password'  => 'PEARforThewin',
    'password2' => 'PEARforThewin',
    'moreinfo'  => 'hippie',
    'homepage'  => 'http://www.helgi.ws/',
);
$id = user::add($data, false, false);
$phpt->assertEquals(true, $id, 'id');

// Test for validation issues
$data = array(
    'firstname' => '12',
    'lastname'  => 'h',
    'handle'    => '1337',
    'password'  => '1',
    'password2' => '2',
);
$return = user::add($data, false, false);

$expect = array (
  0 => 'Please enter Email address',
  1 => 'Please enter Intended purpose',
  2 => 'Username must start with a letter and contain only letters and digits',
  3 => 'Your lastname appears to be too short.',
  4 => 'Your firstname must begin with an uppercase letter',
  5 => 'Your lastname must begin with an uppercase letter',
  6 => 'Your firstname must not consist of only uppercase letters.',
  7 => 'Passwords did not match',
  8 => 'Empty passwords not allowed',
);
$phpt->assertEquals($expect, $return, 'validation');

$data = array(
    'firstname' => 'Thormar',
    'lastname'  => 'Helgi',
    'handle'    => 'dufuz',
    'password'  => 'foobar',
    'password2' => 'foobar',
);
$return = user::add($data, false, false);
$expect = array(
  0 => "Please enter Email address",
  1 => "Please enter Intended purpose",
  2 => 'Sorry, that username is already taken',
);

$phpt->assertEquals($return, $expect, 'validation2');

$phpt->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'dufuz\'',
  1 => '
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'dufuz\', \'Helgi Thormar\', \'dufuz@php.net\', \'http://www.helgi.ws/\', 0, \'5d8052a59cae407c50bf4056bc8c9014\', 0, \'a:2:{i:0;s:14:"do nifty tests";i:1;s:6:"hippie";}\', \'pear\')',
  2 => 'SELECT * FROM users WHERE handle = \'1337\'',
  3 => 'SELECT * FROM users WHERE handle = \'dufuz\'',

), $mock->queries, 'queries');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===