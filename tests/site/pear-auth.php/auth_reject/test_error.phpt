--TEST--
auth_reject() [error message]
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$_GET['redirect'] = null;
$_POST['PEAR_OLDURL'] = null;
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$self = '';
auth_reject(null, 'Hi there <script>');
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
<input type="hidden" name="PEAR_OLDURL" value="login.php" />
</form>
<hr /><p><strong>Note:</strong> If you just want to browse the website, you will not need to log in. For all tasks that require authentication, you will be redirected to this form automatically. You can sign up for an account <a href="/account-request.php">over here</a>.</p><p>If you forgot your password, instructions for resetting it can be found on a <a href="https://pear.php.net/about/forgot-password.php">dedicated page</a>.</p>
  </td>

<!-- END MAIN CONTENT -->


 </tr>
</table>

<!-- END MIDDLE -->
<!-- START FOOTER -->

<table class="foot" cellspacing="0" cellpadding="0">
 <tr>
  <td class="foot-bar" colspan="2">
<a href="/about/privacy.php" class="menuBlack">PRIVACY POLICY</a>&nbsp;|&nbsp;<a href="/about/credits.php" class="menuBlack">CREDITS</a>  </td>
 </tr>

 <tr>
  <td class="foot-copy">
   <small>
    <a href="/copyright.php">Copyright &copy; 2001-%d The PHP Group</a><br />
    All rights reserved.
   </small>
  </td>
  <td class="foot-source">
   <small>
    Bandwidth and hardware provided by:
    <i>This is an unofficial mirror!</i>
   </small>
  </td>
 </tr>
</table>
<!-- Onload focus to pear -->
<script language="javascript">
function makeFocus() {
    document.login.PEAR_USER.focus();}

function addEvent(obj, eventType, functionCall){
    if (obj.addEventListener){
        obj.addEventListener(eventType, functionCall, false);
        return true;
    } else if (obj.attachEvent){
        var r = obj.attachEvent("on"+eventType, functionCall);
        return r;
    } else {
        return false;
    }
}
addEvent(window, 'load', makeFocus);
</script>

<!-- END FOOTER -->

</body>
</html>