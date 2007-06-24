--TEST--
account-request-newpackage.php |  No Request has been made
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '/account-request-newpackage.php';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request-newpackage.php';
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
 You have chosen to request an account for proposing a new (and <strong>complete</strong>)
 package for inclusion in PEAR.
</p>
<p>
 <strong>Before submitting</strong> make sure that you have
 followed all rules concerning PEAR packages.  Especially important are the
 <a href="http://pear.php.net/manual/en/standards.php">PEAR Coding Standards</a>.  Ask
 for help on the <a  href="&#x6d;&#97;&#x69;&#108;&#x74;&#111;&#x3a;&#x70;&#101;&#x61;&#114;&#x2d;&#100;&#x65;&#118;&#x40;&#108;&#x69;&#115;&#x74;&#115;&#x2e;&#112;&#x68;&#112;&#x2e;&#110;&#x65;&#116;">PEAR developers mailing list</a> for any questions you might have prior to proposing your package.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>
<a name="requestform" id="requestform"></a><form action="account-request-newpackage.php#requestform" method="post" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Request Account
 </caption>
 <tr>
  <th class="form-label_left">Use<span class="accesskey">r</span>name:</th>
  <td class="form-input">
   <input type="text" name="handle" size="12" value="" maxlength="20" accesskey="r"/>
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
  <th class="form-label_left">Proposed Package Name:</th>
  <td class="form-input">
   <input type="text" name="newpackage" size="20" value="" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Purpose of your PEAR account:<p class="cell_note">(Check all that apply)</p></th>
  <td class="form-input">
  <input type="checkbox" name="purposecheck[0]" />
Propose a new, incomplete package, or an incomplete idea for a package <br /><input type="checkbox" name="purposecheck[1]" />
Browse pear.php.net. <br />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Short summary of package that you have finished and are ready to propose:</th>
  <td class="form-input">
   <textarea name="purpose" cols="40" rows="5" ></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Link to browseable online source code:</th>
  <td class="form-input">
   <input type="text" name="sourcecode" size="40" value="" />
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
   <input type="checkbox" name="comments_read" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="submit" value="Submit Query" />
  </td>
 </tr>
</table>
<input type="hidden" name="_fields" value="handle:firstname:lastname:password:email:showemail:newpackage:purpose:sourcecode:homepage:moreinfo:comments_read:submit" />
</form>

<script language="JavaScript" type="text/javascript">
<!--
if (!document.forms[1].handle.disabled) document.forms[1].handle.focus();

// -->
</script>

  </td>

<!-- END MAIN CONTENT -->
%s