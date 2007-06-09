--TEST--
login.php
--COOKIE--
PEAR_USER=cellog;PEAR_PW=hi
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['REQUEST_METHOD'] = 'GET';
$moresetup = dirname(__FILE__) . '/test_loggedin.extra.php.inc';
require dirname(__FILE__) . '/setup.php.inc';

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/login.php';
__halt_compiler();
?>
===DONE===
--EXPECTF--
<?xml version="1.0" encoding="ISO-8859-15" ?>
%s
 <title>PEAR :: Login</title>
%s
  <td class="content">

    <div class="warnings">You are already logged in.</div>
  </td>

<!-- END MAIN CONTENT -->
%s