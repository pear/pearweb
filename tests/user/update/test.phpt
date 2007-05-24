--TEST--
user::update()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz'",
    array(array(
        'handle'    => 'dufuz',
        'firstname' => 'Helgi',
        'lastname'  => 'Thormar',
        'email'     => 'dufuz@php.net',
        'purpose'   => 'do nifty tests',
        'moreinfo'  => 'hippie',
        'homepage'  => 'http://www.helgi.ws/',
        'password'  => '5d8052a59cae407c50bf4056bc8c9014',
    )),
    array('handle', 'firstname', 'lastname', 'email', 'purpose', 'moreinfo', 'homepage', 'password')
);

$mock->addInsertQuery("UPDATE users SET 
homepage = 'http://pear.php.net/',
active = 1 WHERE handle = 'dufuz'",
        array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array(
          'id' => 1,
          'name' => 'Helgi Thormar',
          'email' => 'dufuz@php.net',
          'homepage' => 'http://www.helgi.ws/',
          'created' => date('r'),
          'active'  => '1',
          ),
          'cols' => array('id', 'name', 'email', 'homepage', 'created', 'active')
          )), 1);

$mock->addDataQuery("SELECT homepage FROM users WHERE handle = 'dufuz' AND registered = '0'",
    array(array('homepage' => 'http://pear.php.net/')),
    array('homepage')
);

$mock->addInsertQuery("UPDATE users SET 
homepage = 'http://pear.php.net/',
active = 1,
registered = 1 WHERE handle = 'dufuz'", array(), 1);

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '1'",
        array(array(
          'id' => 1,
          'name' => 'Helgi Thormar',
          'email' => 'dufuz@php.net',
          'homepage' => 'http://www.helgi.ws/',
          'created' => date('r'),
          'active'  => '1',
          'registered' => '1',
          )),
          array('id', 'name', 'email', 'homepage', 'created', 'active', 'registered')
);

// ============= test =============
$data = array(
    'handle'   => 'dufuz',
    'homepage' => 'http://pear.php.net/',
    'active'   => true,
);
$res = user::update($data);
$phpunit->assertEquals(true, $res, 'test 1');

$info = user::info('dufuz', 'homepage', false);
$phpunit->assertEquals(array('homepage' => 'http://pear.php.net/'), $info, 'test 2');

$data['registered'] = true;
$res = user::update($data, true);
$phpunit->assertEquals(true, $res, 'test 3');

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