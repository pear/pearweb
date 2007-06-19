--TEST--
/get no PATH_INFO
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '';
$_SERVER['REQUEST_URI'] = '/get';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['PATH_INFO'] = '/';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/get';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
no package selected