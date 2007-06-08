--TEST--
index.php basic loading and display
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.test.phpt.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/index.php';
$phpt->assertEquals(array (
  0 => 'SELECT packages.id AS id, packages.name AS name, packages.summary AS summary, releases.version AS version, releases.releasedate AS releasedate, releases.releasenotes AS releasenotes, releases.doneby AS doneby, releases.state AS state FROM packages, releases WHERE packages.id = releases.package AND packages.approved = 1 AND packages.package_type = \'pear\' ORDER BY releases.releasedate DESC LIMIT 0, 5',
  1 => '
            SELECT
                packages.name, releases.version, downloads,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as d
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = \'pear\' AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "2007-06-01 00:00:00"
                ORDER BY d DESC LIMIT 0, 5',
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
<?xml version="1.0" encoding="ISO-8859-15" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: The PHP Extension and Application Repository</title>
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
   <a href="/account-request.php" class="menuBlack">Register</a>&nbsp;|&nbsp;<a href="/login.php?redirect=hithere" class="menuBlack">Login</a>&nbsp;|&nbsp;<a href="/manual/" class="menuBlack">Documentation</a>&nbsp;|&nbsp;<a href="/packages.php" class="menuBlack">Packages</a>&nbsp;|&nbsp;<a href="/support/" class="menuBlack">Support</a>&nbsp;|&nbsp;<a href="/bugs/" class="menuBlack">Bugs</a>
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
 <li class="side_page"><a href="/mirrors.php">Mirrors</a></li>
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

    
<h1>PEAR - PHP Extension and Application Repository</h1>

<h2>&raquo; Hot off the Press</h2>
%s<h2>&raquo; Users</h2>
<div class="indent">
<p><acronym title="PHP Extension and Application Repository">PEAR</acronym>
is a framework and distribution system for reusable PHP
components. You can find help using PEAR packages in the
<a href="/manual/en/">online manual</a> and the
<a href="/manual/en/faq.php">FAQ</a>.</p>
<p>
<a href="/packages.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Download Packages"  /></a>&nbsp;<a href="/packages.php"><strong>Download Packages</strong></a></p>
<p>
<a href="/support"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Support"  /></a>&nbsp;<a href="/support"><strong>Support</strong></a></p>
<p>
<a href="/manual/en/installation.cli.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Installation Help"  /></a>&nbsp;<a href="/manual/en/installation.cli.php"><strong>Installation Help</strong></a></p>
<p>
<a href="/manual/en/about-pear.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="About PEAR"  /></a>&nbsp;<a href="/manual/en/about-pear.php"><strong>About PEAR</strong></a></p>
<p>
<a href="/news/"><img src="/gifs/pear_item.gif" style="border: 0;" alt="News"  /></a>&nbsp;<a href="/news/"><strong>News</strong></a></p>
<p>
<a href="/packages.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="List Packages"  /></a>&nbsp;<a href="/packages.php"><strong>List Packages</strong></a></p>
<p>
<a href="/search.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Search"  /></a>&nbsp;<a href="/search.php"><strong>Search</strong></a></p>
</div>

<hr />
<p>If you have been told by other PEAR developers to sign up for a
PEAR website account, you can use <a href="/account-request.php">
this interface</a>.</p>


  </td>

<!-- END MAIN CONTENT -->

    
<!-- START RIGHT SIDEBAR -->
  <td class="sidebar_right">
   <strong>Recent&nbsp;Releases:</strong>
<table class="sidebar-releases">
<tr><td valign="top" class="compact"><a href="/package/PEAR/">PEAR 1.5.1</a><br /> <small>(Tue, 20th Mar 07)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/PEAR/">PEAR 1.5.0RC2</a><br /> <small>(Fri, 2nd Feb 07)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/PEAR/">PEAR 1.5.0RC1</a><br /> <small>(Fri, 2nd Feb 07)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/Math_Derivative/">Math_Derivative 0.1.0</a><br /> <small>(Sat, 18th Nov 06)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/Games_Chess/">Games_Chess 1.0.0RC1</a><br /> <small>(Sat, 18th Nov 06)</small></td></tr><tr><td>&nbsp;</td></tr>
<tr><td align="right"><a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" border="0" /></a></td></tr>
</table>
<strong>Popular&nbsp;Packages*:</strong>
<table class="sidebar-releases">
<tr><td valign="top" class="compact"><a href="/package/XML_RPC/">XML_RPC 1.4.4</a><br /> <small>(120.37)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/XML_RPC/">XML_RPC 1.4.5</a><br /> <small>(91.05)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/Mail/">Mail 1.1.9</a><br /> <small>(56.46)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/XML_Parser/">XML_Parser 1.2.7</a><br /> <small>(52.87)</small></td></tr><tr><td valign="top" class="compact"><a href="/package/Net_Socket/">Net_Socket 1.0.6</a><br /> <small>(37.17)</small></td></tr><tr><td><small>* downloads per day</small></td></tr>
<tr><td align="right"><a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" border="0" /></a></td></tr>
</table>
  </td>
<!-- END RIGHT SIDEBAR -->

        
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
    Last updated: %s %s %d %d:%d:%d %d UTC<br />
    Bandwidth and hardware provided by:
    <i>This is an unofficial mirror!</i>
   </small>
  </td>
 </tr>
</table>
<!-- Onload focus to pear -->

<!-- END FOOTER -->

</body>
</html>