--TEST--
account-request-newpackage.php | [Success]
--POST--
purpose=Smurf a lot&newpackage=Foo&email=dufuz@php.net&firstname=Helgi&lastname=Thormar&password=hi&password2=hi&comments_read=1&handle=helgi&captcha=24&submit=1&moreinfo=&homepage=
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'bobo';
$_SERVER['REQUEST_URI'] = '/account-request-newpackage.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['QUERY_STRING'] = 'account-request-newpackage.php#requestform';
require dirname(__FILE__) . '/setup.php.inc';

$mock->addDataQuery("SELECT count(id) FROM packages WHERE packages.name = 'Foo'", array(), array('id'));

$mock->addDataQuery('SELECT * FROM users WHERE handle = \'helgi\'',
            array(),
            array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addInsertQuery('INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'helgi\', \'Helgi Thormar\', \'dufuz@php.net\', \'\', 0, \'49f68a5c8493ec2c0bf489821c21fc3b\', 0, \'a:2:{i:0;s:11:"Smurf a lot";i:1;s:0:"";}\', "pear")',
                array(), array());

require dirname(dirname(dirname(__FILE__))) . '/mock/Session.php';
$_COOKIE['PHPSESSID'] = 'hithere';
$session = new MockSession;
$session->init('hithere', array('answer' => 24));

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-newpackage.php';

$_SESSION['hello'] = array(1,2,3);
session_write_close();

$phpt->assertEquals(array (
 0 => "SELECT count(id) FROM packages WHERE packages.name = 'Foo'",
 1 => 'SELECT * FROM users WHERE handle = \'helgi\'',
 2 => '
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'helgi\', \'Helgi Thormar\', \'dufuz@php.net\', \'\', 0, \'49f68a5c8493ec2c0bf489821c21fc3b\', 0, \'a:2:{i:0;s:11:"Smurf a lot";i:1;s:0:"";}\', "pear")',
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

    <h1>Request Account</h1><div class="success">Your account request has been submitted, it will be reviewed by a human shortly.  This may take from two minutes to several days, depending on how much time people have. You will get an email when your account is open, or if your request was rejected for some reason.</div>

  </td>

<!-- END MAIN CONTENT -->
%s