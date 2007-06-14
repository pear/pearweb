--TEST--
account-request-confirm.php [Election no requested user found]
--GET--
salt=12345678901234567890123456789012
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '/account-request-confirm.php';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.php.inc';

$time = gmdate('Y-m-d H:i', strtotime('-15 minutes'));

$mock->addDeleteQuery("DELETE FROM election_account_request WHERE created_on < '" . $time . "'", array(), array());

$mock->addDataQuery("SELECT handle FROM election_account_request WHERE created_on < '" . $time . "'",
                 array(),
                 array('handle')
                 );

$mock->addDataQuery("SELECT id, created_on, salt, handle
            FROM election_account_request
            WHERE salt='12345678901234567890123456789012'", array(), array());

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-confirm.php';

$phpt->assertEquals(array (
    0 => "SELECT handle FROM election_account_request WHERE created_on < '" . $time . "'",
    1 => "DELETE FROM election_account_request WHERE created_on < '" . $time . "'",
    2 => "
            SELECT id, created_on, salt, handle
            FROM election_account_request
            WHERE salt='12345678901234567890123456789012'
        ",
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
%s
 <title>PEAR :: Account confirmation</title>
%s

  <td class="content">

    <h1>Confirm Account</h1><div class="errors">cannot find request</li>
</ul></div>

  </td>

<!-- END MAIN CONTENT -->

%s