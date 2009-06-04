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
 <title>Request Account</title>
%s
<h1>Request Account</h1><h1>PLEASE READ THIS BEFORE SUBMITTING!</h1>
<p>
 You have chosen to request an account for proposing a new (and <strong>complete</strong>)
 package for inclusion in PEAR.
</p>
<p>
 <strong>Before submitting</strong> make sure that you have
 followed all rules concerning PEAR packages.  Especially important are the
 <a href="http://pear.php.net/manual/en/standards.php">PEAR Coding Standards</a>.  Ask
 for help on the <a href="mailto:pear-dev@lists.php.net">PEAR developers mailing list</a> for any questions you might have prior to proposing your package.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>
<a name="requestform" id="requestform"></a>
<form action="account-request-newpackage.php#requestform" method="post" id="account-request-newpackage">
 <div>
  
  <table border="0" class="form-holder" cellspacing="1">
   <caption class="form-caption">Request Account</caption>
 <tr>
  <th class="form-label_left">
   Use<span class="accesskey">r</span>name:
  </th>
  <td class="form-input">
   <input size="12" maxlength="20" accesskey="r" name="handle" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   First Name:
  </th>
  <td class="form-input">
   <input size="30" name="firstname" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Last Name:
  </th>
  <td class="form-input">
   <input size="30" name="lastname" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Password:
  </th>
  <td class="form-input">
   <input size="10" name="password" type="password" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Repeat Password:
  </th>
  <td class="form-input">
   <input size="10" name="password2" type="password" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Solve the problem:
  </th>
  <td class="form-input">
   %s = <input type="text" size="4" maxlength="4" name="captcha" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Email Address:
  </th>
  <td class="form-input">
   <input size="20" name="email" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Show email address?
  </th>
  <td class="form-input">
   <input name="showemail" type="checkbox" value="1" id="qf_%s" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Proposed Package Name:
  </th>
  <td class="form-input">
   <input size="20" name="newpackage" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Purpose of your PEAR account:<p class="cell_note">(Check all that apply)</p>
  </th>
  <td class="form-input">
   <input name="purposecheck[0]" type="checkbox" value="1" id="qf_%s" /><label for="qf_%s"> Propose a new, incomplete package, or an incomplete idea for a package</label><br /><input name="purposecheck[1]" type="checkbox" value="1" id="qf_%s" /><label for="qf_%s"> Browse pear.php.net.</label>
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Short summary of package that you have finished and are ready to propose:
  </th>
  <td class="form-input">
   <textarea cols="40" rows="5" name="purpose"></textarea>
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Link to browseable online source code:
  </th>
  <td class="form-input">
   <input size="40" name="sourcecode" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Homepage:<p class="cell_note">(optional)</p>
  </th>
  <td class="form-input">
   <input size="40" name="homepage" type="text" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   More relevant information about you:<p class="cell_note">(optional)</p>
  </th>
  <td class="form-input">
   <textarea cols="40" rows="5" name="moreinfo"></textarea>
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   I have read EVERYTHING on this page:
  </th>
  <td class="form-input">
   <input name="comments_read" type="checkbox" value="1" id="qf_%s" />
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   
  </th>
  <td class="form-input">
   <input name="submit" value="Submit Request" type="submit" />
  </td>
 </tr>

  </table>
 </div>
</form><script type="text/javascript">
<!--
if (!document.forms[1].handle.disabled) document.forms[1].handle.focus();

// -->
</script>
%s
