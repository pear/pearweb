--TEST--
account-request-vote.php | [Success]
--POST--
email=dufuz@php.net&firstname=Helgi&lastname=Thormar&password=hi&password2=hi&comments_read=1&handle=helgi&captcha=24&submit=1&moreinfo=&homepage=
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'bobo';
$_SERVER['REQUEST_URI'] = '/account-request-vote.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['QUERY_STRING'] = 'account-request-vote.php#requestform';
require dirname(__FILE__) . '/setup.php.inc';

$mock->addDataQuery("SELECT handle FROM election_account_request WHERE created_on < '2007-06-22 20:52'", array(), array(),
    array(
        'query' => "/SELECT handle FROM election_account_request WHERE created_on < '(.+)'/",
        'replace' => ''));

$mock->addDeleteQuery("DELETE FROM election_account_request WHERE created_on < '2007-06-22 21:56'", array(), array(),
    array(
        'query' => "/DELETE FROM election_account_request WHERE created_on < '(.+)'/",
        'replace' => ''));

$mock->addDataQuery('SELECT * FROM users WHERE handle = \'helgi\'',
            array(),
            array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addInsertQuery('INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'helgi\', \'Helgi Thormar\', \'dufuz@php.net\', \'\', 0, \'49f68a5c8493ec2c0bf489821c21fc3b\', 0, \'a:2:{i:0;s:24:"vote in general election";i:1;s:0:"";}\', "pear")',
                array(), array());

$mock->addInsertQuery("INSERT INTO election_account_request (created_on, handle, email, salt)
        VALUES ('2007-06-24 01:39:44', 'helgi', 'dufuz@php.net', '8d85e00f05f383d47d1e24418595a7a2')",
        array(), array(),
        array(
         'query' => "/INSERT INTO election_account_request \(created_on, handle, email, salt\)
        VALUES \('(.+)', 'helgi', 'dufuz@php.net', '(.+)'\)/",
         'replace' => ''));

require dirname(dirname(dirname(__FILE__))) . '/mock/Session.php';
$_COOKIE['PHPSESSID'] = 'hithere';
$session = new MockSession;
$session->init('hithere', array('answer' => 24));

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-vote.php';

$_SESSION['hello'] = array(1,2,3);
session_write_close();

$phpt->assertEquals(array (
 0 => 'SELECT handle FROM election_account_request WHERE created_on < \'2007-06-22 20:52\'',
 1 => 'DELETE FROM election_account_request WHERE created_on < \'2007-06-22 21:56\'',
 2 => 'SELECT * FROM users WHERE handle = \'helgi\'',
 3 => '
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'helgi\', \'Helgi Thormar\', \'dufuz@php.net\', \'\', 0, \'49f68a5c8493ec2c0bf489821c21fc3b\', 0, \'a:2:{i:0;s:24:"vote in general election";i:1;s:0:"";}\', "pear")',
 4 => "
        INSERT INTO election_account_request (created_on, handle, email, salt)
        VALUES ('2007-06-24 01:46:19', 'helgi', 'dufuz@php.net', 'adc0fc761600867e22533e02bdf6f401')",
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
%s
 <title>PEAR :: Request Account</title>
%s
<!-- START MAIN CONTENT -->

  <td class="content">

    <h1>Request Account</h1><div class="success">Your account request confirmation has been submitted.  You must follow the link provided in the email  in order to activate your account. Until this is done you cannot vote in any election.</div>

  </td>

<!-- END MAIN CONTENT -->
%s