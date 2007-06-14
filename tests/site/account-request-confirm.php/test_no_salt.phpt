--TEST--
account-request-confirm.php |  No salt
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '/account-request-confirm.php';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-confirm.php';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
%s
 <title>PEAR :: Account confirmation</title>
%s

  <td class="content">

    <h1>Confirm Account</h1><div class="errors">ERROR:<ul><li>Unknown salt</li>
</ul></div>

  </td>

<!-- END MAIN CONTENT -->

%s