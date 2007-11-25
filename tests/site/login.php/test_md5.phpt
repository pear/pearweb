--TEST--
login.php [md5]
--POST--
PEAR_USER=cellog&PEAR_PW=49f68a5c8493ec2c0bf489821c21fc3b&isMD5=1
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$moresetup = dirname(__FILE__) . '/test_md5.extra.php.inc';
require dirname(__FILE__) . '/setup.php.inc';
$mock->addUpdateQuery("UPDATE users SET active = 1 WHERE handle = 'cellog'", array(), array());
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/login.php';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTHEADERS--
Location: http://localhost/index.php
Set-Cookie: PEAR_USER=cellog; path=/
Set-Cookie: PEAR_PW=49f68a5c8493ec2c0bf489821c21fc3b; path=/
--EXPECT--