--TEST--
account-request-confirm.php [Election Success]
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
            WHERE salt='12345678901234567890123456789012'",
            array(array(
                'id'         => 1,
                'created_on' => gmdate('Y-m-d H:i', strtotime('-10 minutes')),
                'salt'       => '12345678901234567890123456789012',
                'handle'     => 'helgi',
            )),
            array('id', 'created_on', 'salt', 'handle'));

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'helgi' AND registered = '0'",
 array(array(
    'handle' => 'helgi',
    'password' => md5('hi'),
    'name' => 'Helgi ?ormar ?orbj?rnsson',
    'email' => 'helgith@gmail.com',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '1',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => '',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  )), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addDeleteQuery("DELETE FROM notes WHERE uid = 'helgi'", array(), array());

$mock->addInsertQuery("INSERT INTO karma VALUES (1, 'helgi', 'pear.voter', 'pearweb', NOW())",
    array(),
    array());

$mock->addInsertQuery("INSERT INTO karma VALUES (1, 'helgi', 'pear.bug', 'pearweb', NOW())",
    array(),
    array());

$mock->addInsertQuery("INSERT INTO notes (id,uid,nby,ntime,note) VALUES(1,'helgi','pearweb','" . gmdate('Y-m-d H:i', time()) . "','Account opened')",
    array(),
    array());

$mock->addDeleteQuery("DELETE FROM election_account_request WHERE salt = '12345678901234567890123456789012'", array(), array());

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-confirm.php';

$phpt->assertEquals(array (
    0 => "SELECT handle FROM election_account_request WHERE created_on < '" . $time . "'",
    1 => "DELETE FROM election_account_request WHERE created_on < '" . $time . "'",
    2 => "
            SELECT id, created_on, salt, handle
            FROM election_account_request
            WHERE salt='12345678901234567890123456789012'
        ",
    3 => 'SELECT * FROM users WHERE handle = \'helgi\' AND registered = \'0\'',
    4 => 'DELETE FROM notes WHERE uid = \'helgi\'',
    5 => "INSERT INTO karma VALUES (1, 'helgi', 'pear.voter', 'pearweb', NOW())",
    6 => "INSERT INTO karma VALUES (1, 'helgi', 'pear.bug', 'pearweb', NOW())",
    7 => "INSERT INTO notes (id,uid,nby,ntime,note) VALUES(1,'helgi','pearweb','" . gmdate('Y-m-d H:i', time()) . "','Account opened')",
    8 => "DELETE FROM election_account_request WHERE salt = '12345678901234567890123456789012'",
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
%s
 <title>PEAR :: Account confirmation</title>
%s
<!-- START MAIN CONTENT -->

  <td class="content">

    <h1>Confirm Account</h1><div class="success">Your account has been activated, you can now vote in
        PEAR elections that are for the general PHP public as well as open bugs in the bug tracker</div>

  </td>

<!-- END MAIN CONTENT -->
%s