--TEST--
account-request-vote.php | [Bad Captcha]
--POST--
comments_read=1&handle=helgi&captcha=&submit=1
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
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-vote.php';
$phpt->assertEquals(array (
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
<a name="requestform" id="requestform"></a><div class="errors">ERROR:<ul><li>Incorrect CAPTCHA</li>
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
   <input type="text" name="firstname" size="20" value="" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Last Name:</th>
  <td class="form-input">
   <input type="text" name="lastname" size="20" value="" />
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
   <input type="text" name="email" size="20" value="" />
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