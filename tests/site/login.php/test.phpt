--TEST--
login.php
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/login.php';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
<!DOCTYPE html>
<html>
<head >
 <title>Login</title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" type="text/css" href="/css/reset-fonts.css" />
 <link rel="stylesheet" type="text/css" href="/css/style.css" />
 <!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/IE7styles.css" /><![endif]-->
 <!--[if IE 6]><link rel="stylesheet" type="text/css" href="/css/IE6styles.css" /><![endif]-->
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://pear.php.net/feeds/latest.rss" />
 <!-- compliance patch for microsoft browsers -->
<!--[if lt IE 8]>
 <script src="/javascript/IE8.js" type="text/javascript"></script>
<![endif]-->
 <link rel="search" type="application/opensearchdescription+xml" title="PEAR Search Plugin" href="http://pear.php.net/osd.xml" />
</head>

<body>
<div>
<a id="TOP"></a>
</div>

<!-- START HEADER -->

%s
<!-- END HEADER -->
<!-- START MIDDLE -->

%s<!-- END LEFT SIDEBAR -->

        
<!-- START MAIN CONTENT -->

  <td class="content">

    <script type="text/javascript" src="/javascript/md5.js"></script>
<script type="text/javascript">
function doMD5(frm) {
    frm.PEAR_PW.value = hex_md5(frm.PEAR_PW.value);
    frm.isMD5.value = 1;
}
</script>
<form onsubmit="javascript:doMD5(document.forms['login'])" name="login" action="/login.php" method="post">
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

%s
