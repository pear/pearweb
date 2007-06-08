--TEST--
PEAR_Auth->is()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$auth_user = null;

$x = new PEAR_Auth;

$phpt->assertFalse($x->is('cellog'), 'no $auth_user');

$x->handle = 'cellog';
$phpt->assertTrue($x->is('cellog'), 'no $auth_user, handle is set');

$auth_user = clone $x;

$x->handle = 'blah';
$phpt->assertTrue($x->is('cellog'), '$auth_user');
?>
===DONE===
--EXPECT--
===DONE===