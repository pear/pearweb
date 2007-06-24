--TEST--
account-request-vote.php | [User already exists]
--POST--
email=dufuz@php.net&firstname=Helgi&lastname=Thormar&password=hi&password2=hi&comments_read=1&handle=helgi&captcha=24&submit=1
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
            array(  0 =>
  array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),),
            array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

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

    <h1>Request Account</h1><h1>PLEASE READ THIS BEFORE SUBMITTING!</h1>
<p>
 You have chosen to request an account in order to vote in a general PEAR election.
</p>
<p>
 This account will be restricted only to voting in allowed elections, none of the other
 developer privileges apply, including proposing a new package for inclusion in PEAR.
 If you wish to propose a new (and <strong>complete</strong>) package for inclusion
 in PEAR, please use the <a href="/account-request-newpackage.php">New Package Account
 Request Form</a>.
</p>
<p>
 Note that this account can also be used to report a bug or comment on an existing bug.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>
<a name="requestform" id="requestform"></a><div class="errors">ERROR:<ul><li>Sorry, that username is already taken</li>
</ul></div>
<form action="account-request-vote.php#requestform" method="post" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Request Account
 </caption>
 <tr>
  <th class="form-label_left">Use<span class="accesskey">r</span>name:</th>
  <td class="form-input">
   <input type="text" name="handle" size="12" value="helgi" maxlength="20" accesskey="r"/>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">First Name:</th>
  <td class="form-input">
   <input type="text" name="firstname" size="20" value="Helgi" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Last Name:</th>
  <td class="form-input">
   <input type="text" name="lastname" size="20" value="Thormar" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Password:</th>
  <td class="form-input">
   <input type="password" name="password" size="10" value="" />
   repeat: <input type="password" name="password2" size="10" value="" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Solve the problem:</th>
  <td class="form-input">
  %s = <input type="text" size="4" maxlength="4" name="captcha" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Email Address:</th>
  <td class="form-input">
   <input type="text" name="email" size="20" value="dufuz@php.net" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Show email address?</th>
  <td class="form-input">
   <input type="checkbox" name="showemail" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Homepage:<p class="cell_note">(optional)</p></th>
  <td class="form-input">
   <input type="text" name="homepage" size="20" value="" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">More relevant information about you:<p class="cell_note">(optional)</p></th>
  <td class="form-input">
   <textarea name="moreinfo" cols="40" rows="5" ></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">I have read EVERYTHING on this page:</th>
  <td class="form-input">
   <input type="checkbox" name="comments_read" checked="checked" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="submit" value="Submit Query" />
  </td>
 </tr>
</table>
<input type="hidden" name="_fields" value="handle:firstname:lastname:password:email:showemail:homepage:moreinfo:comments_read:submit" />
</form>

<script language="JavaScript" type="text/javascript">
<!--
if (!document.forms[1].handle.disabled) document.forms[1].handle.focus();

// -->
</script>

  </td>

<!-- END MAIN CONTENT -->
%s