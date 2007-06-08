--TEST--
auth_reject() [PEAR_OLDURL set]
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$_GET['redirect'] = null;
$_POST['PEAR_OLDURL'] = '/election/info.php';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$self = '';
auth_reject();
?>
===DONE===
--EXPECTF--
%s<form onsubmit="javascript:doMD5(document.forms['login'])" name="login" action="/login.php" method="post">
<input type="hidden" name="isMD5" value="0" />
<table class="form-holder" cellspacing="1">
 <tr>
  <th class="form-label_left">Use<span class="accesskey">r</span>name or email address:</th>
  <td class="form-input"><input size="20" name="PEAR_USER" accesskey="r" /></td>
 </tr>
 <tr>
  <th class="form-label_left">Password:</th>
  <td class="form-input"><input size="20" name="PEAR_PW" type="password" /></td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input" style="white-space: nowrap"><input type="checkbox" name="PEAR_PERSIST" value="on" id="pear_persist_chckbx" /> <label for="pear_persist_chckbx">Remember username and password.</label></td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</td>
  <td class="form-input"><input type="submit" value="Log in!" /></td>
 </tr>
</table>
<input type="hidden" name="PEAR_OLDURL" value="/election/info.php" />
</form>
<hr /><p><strong>Note:</strong> If you just want to browse the website, you will not need to log in. For all tasks that require authentication, you will be redirected to this form automatically. You can sign up for an account <a href="/account-request.php">over here</a>.</p><p>If you forgot your password, instructions for resetting it can be found on a <a href="https://pear.php.net/about/forgot-password.php">dedicated page</a>.</p>
  </td>
%s