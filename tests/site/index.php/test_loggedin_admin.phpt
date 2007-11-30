--TEST--
index.php, admin logged in
--COOKIE--
PEAR_USER=cellog;PEAR_PW=hi
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
$_COOKIE['PEAR_USER'] = 'cellog';
$_COOKIE['PEAR_PW'] = md5('hi');
require dirname(__FILE__) . '/setup.test_loggedin_admin.phpt.inc';

include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/index.php';
$phpt->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'cellog\' AND registered = \'1\'',
  1 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  2 => 'SELECT packages.id AS id, packages.name AS name, packages.summary AS summary, releases.version AS version, releases.releasedate AS releasedate, releases.releasenotes AS releasenotes, releases.doneby AS doneby, releases.state AS state FROM packages, releases WHERE packages.id = releases.package AND packages.approved = 1 AND packages.package_type = \'pear\' ORDER BY releases.releasedate DESC LIMIT 0, 5',
  3 => '
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
  4 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  5 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  6 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  7 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
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
   <small class="menuWhite">Logged in as CELLOG (<a class="menuWhite" href="/user/cellog">Info</a> | <a class="menuWhite" href="/account-edit.php?handle=cellog">Profile</a> | <a class="menuWhite" href="/bugs/search.php?handle=cellog&amp;cmd=display&amp;status=OpenFeedback&amp;showmenu=1">Bugs</a> | <a class="menuWhite" href="/bugs/search.php?cmd=display&amp;status=All&amp;bug_type=All&amp;author_email=cellog&amp;direction=DESC&amp;order_by=ts1&amp;showmenu=1">My Bugs</a>)</small><br />
<a href="?logout=1" class="menuBlack">Logout</a>&nbsp;|&nbsp;<a href="/manual/" class="menuBlack">Documentation</a>&nbsp;|&nbsp;<a href="/packages.php" class="menuBlack">Packages</a>&nbsp;|&nbsp;<a href="/support/" class="menuBlack">Support</a>&nbsp;|&nbsp;<a href="/bugs/" class="menuBlack">Bugs</a>
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
 <li class="side_page"><a href="/map/">Find a Developer</a></li>
 <li class="side_page"><a href="/accounts.php">List Accounts</a></li>
 <li class="side_page"><a href="/release-upload.php">Upload Release</a></li>
 <li class="side_page"><a href="/package-new.php">New Package</a></li>
 <li class="side_page"><a href="/notes/admin">Manage User Notes</a></li>
 <li class="side_page"><a href="/election/">View Elections</a></li>
</ul>


<strong>Administrators:</strong>
<ul class="side_pages">
 <li class="side_page"><a href="/admin/">Overview</a></li>
</ul>

   </span>
  </td>
<!-- END LEFT SIDEBAR -->


<!-- START MAIN CONTENT -->

  <td class="content">


<h1>PEAR - PHP Extension and Application Repository</h1>

<h2>&raquo; Hot off the Press</h2>
%s
<h2>&raquo; Users</h2>
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

<hr /><h2>&raquo; Developers</h2><div class="indent"><p>
<a href="release-upload.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Upload Release"  /></a>&nbsp;<a href="release-upload.php"><strong>Upload Release</strong></a></p>
<p>
<a href="package-new.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="New Package"  /></a>&nbsp;<a href="package-new.php"><strong>New Package</strong></a></p>
</div><h2>&raquo; Package Proposals (PEPr)</h2><div class="indent"><p>
<a href="pepr/"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Browse Proposals"  /></a>&nbsp;<a href="pepr/"><strong>Browse Proposals</strong></a></p>
<p>
<a href="pepr/pepr-proposal-edit.php"><img src="/gifs/pear_item.gif" style="border: 0;" alt="New Package Proposal"  /></a>&nbsp;<a href="pepr/pepr-proposal-edit.php"><strong>New Package Proposal</strong></a></p>
</div><h2>&raquo; Administrators</h2><div class="indent"><p>
<a href="/admin/"><img src="/gifs/pear_item.gif" style="border: 0;" alt="Overview"  /></a>&nbsp;<a href="/admin/"><strong>Overview</strong></a></p>
</div>
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