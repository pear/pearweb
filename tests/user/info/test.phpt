--TEST--
user::info()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = 0",
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

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '0'",
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

$mock->addDataQuery("SELECT email FROM users WHERE handle = 'dufuz' AND registered = '0'",
    array(array('email' => 'dufuz@php.net')),
    array('email')
);

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '1'",
    array(),
    array()
);

// test
$data = array(
    'handle'    => 'dufuz',
    'firstname' => 'Helgi',
    'lastname'  => 'Thormar',
    'email'     => 'dufuz@php.net',
    'purpose'   => 'do nifty tests',
    'moreinfo'  => 'hippie',
    'homepage'  => 'http://www.helgi.ws/',
);
$user = user::info('dufuz', null, false);
$phpunit->assertEquals($data, $user, 'test 1');

$user = user::info('dufuz', null, false, false);
$data['password'] = '5d8052a59cae407c50bf4056bc8c9014';
$phpunit->assertEquals($data, $user, 'test 2');

$user = user::info('dufuz', 'password', false);
$phpunit->assertEquals(null, $user, 'password fetching');

$user = user::info('dufuz', 'email', false);
$phpunit->assertEquals(array('email' => 'dufuz@php.net'), $user, 'field fetching');

$info = user::info('dufuz', null, true);
$phpunit->assertEquals(null, $info, 'test 3');

?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===