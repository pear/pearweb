--TEST--
auth_reject() [PEAR_OLDURL set like PEAR_OLDURL=http://externalsite.com]
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$_GET['redirect'] = null;
$_POST['PEAR_OLDURL'] = 'http://www.example.com';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$self = '';
auth_reject();
__halt_compiler();
?>
===DONE===
--EXPECTF--
<?xml version="1.0" encoding="ISO-8859-15" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: Login</title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" href="/css/style.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://localhost/feeds/latest.rss" />
</head>

<body>
<div>
<a id="TOP"></a>
</div>

<!-- START HEADER -->

<table id="head-menu" class="head" cellspacing="0" cellpadding="0">
 <tr>
  <td class="head-logo">
   <a href="/"><img src="/gifs/pearsmall.gif" style="border: 0; margin: 5px;" alt="PEAR"  /></a><br />
  </td>
  <td class="head-menu">
   <a href="/account-request.php" class="menuBlack">Register</a>&nbsp;|&nbsp;<a href="/login.php?redirect=" class="menuBlack">Login</a>&nbsp;|&nbsp;<a href="/manual/" class="menuBlack">Documentation</a>&nbsp;|&nbsp;<a href="/packages.php" class="menuBlack">Packages</a>&nbsp;|&nbsp;<a href="/support/" class="menuBlack">Support</a>&nbsp;|&nbsp;<a href="/bugs/" class="menuBlack">Bugs</a>
  </td>
 </tr>

 <tr>
  <td class="head-search" colspan="2">
   <form method="get" action="/search.php">
    <p class="head-search"><span class="accesskey">S</span>earch for
    <input class="small" type="text" name="q" value="" size="20" accesskey="s" />
    in the
    <select name="in" class="small">
        <option value="packages">Packages</option>
        <option value="site">This site (using Yahoo!)</option>
        <option value="users">Developers</option>
        <option value="pear-dev">Developer mailing list</option>
        <option value="pear-general">General mailing list</option>
        <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" style="vertical-align: middle;" />
    </p>
   </form>
  </td>
 </tr>
</table>

<!-- END HEADER -->
<!-- START MIDDLE -->

<table class="middle" cellspacing="0" cellpadding="0">
 <tr>

    
<!-- START LEFT SIDEBAR -->
  <td class="sidebar_left">
   <span id="sidebar">
   
<strong>Main:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/index.php">Home</a></li>
 <li class="side_page"><a href="/news/">News</a></li>
 <li class="side_page"><a href="/qa/">Quality Assurance</a></li>
 <li class="side_page"><a href="/group/">The PEAR Group</a></li>
</ul>


<strong>Documentation:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/manual/en/about-pear.php">About PEAR</a></li>
 <li class="side_page"><a href="/manual/index.php">Manual</a></li>
 <li class="side_page"><a href="/manual/en/faq.php">FAQ</a></li>
 <li class="side_page"><a href="/support/">Support</a></li>
</ul>


<strong>Downloads:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/packages.php">List Packages</a></li>
 <li class="side_page"><a href="/search.php">Search Packages</a></li>
 <li class="side_page"><a href="/package-stats.php">Statistics</a></li>
</ul>


<strong>Package Proposals:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/pepr/">Browse Proposals</a></li>
 <li class="side_page"><a href="/pepr/pepr-proposal-edit.php">New Proposal</a></li>
</ul>


<strong>Developers:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/accounts.php">List Accounts</a></li>
</ul>

   </span>
  </td>
<!-- END LEFT SIDEBAR -->

        
<!-- START MAIN CONTENT -->

  <td class="content">

    <div class="errors">ERROR:<ul><li>Please enter your username and password:</li>
</ul></div>
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
    Last updated: %s %s %d %d:%d:%d %d %s<br />
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

    