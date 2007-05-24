--TEST--
user::add() [basic]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz'", array(), array());

$mock->addInsertQuery("
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo)
            VALUES
                ('dufuz', 'Helgi Thormar', 'dufuz@php.net', 'http://www.helgi.ws/', 0, '5d8052a59cae407c50bf4056bc8c9014', 0, 'a:2:{i:0;s:14:\"do nifty tests\";i:1;s:6:\"hippie\";}')",
                array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array('id' => 1,
          'name' => 'Helgi Thormar',
          'email' => 'dufuz@php.net',
          'homepage' => 'http://www.helgi.ws/',
          'created' => date('r')
          ),
          'cols' => array('id', 'name', 'email', 'homepage', 'created')
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
$phpunit->assertEquals(true, $id, 'id');

// Test for validation issues
$data = array(
    'firstname' => 'þ',
    'lastname'  => 'h',
    'handle'    => '1337',
    'password'  => '1',
    'password2' => '2',
);
$return = user::add($data, false, false);

$expect = array(
  0 => "Please enter Email address",
  1 => "Please enter Intended purpose",
  2 => "Username must start with a letter and contain only letters and digits",
  3 => "Your firstname appears to be too short.",
  4 => "Your lastname appears to be too short.",
  5 => "Your firstname must begin with an uppercase letter",
  6 => "Your lastname must begin with an uppercase letter",
  7 => "Passwords did not match",
  8 => "Empty passwords not allowed",
);
$phpunit->assertEquals($return, $expect, 'validation');

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

$phpunit->assertEquals($return, $expect, 'validation2');

$phpunit->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'dufuz\'',
  1 => '
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo)
            VALUES
                (\'dufuz\', \'Helgi Thormar\', \'dufuz@php.net\', \'http://www.helgi.ws/\', 0, \'5d8052a59cae407c50bf4056bc8c9014\', 0, \'a:2:{i:0;s:14:"do nifty tests";i:1;s:6:"hippie";}\')',
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