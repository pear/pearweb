<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("Channels");

$tabs = array("List" => array("url" => "/channels/index.php",
                              "title" => "List Sites."),
              "Add Site" => array("url" => "/channels/add.php",
                                  "title" => "Add your site.")
              );
?>

<h1>Channels</h1>

<?php print_tabbed_navigation($tabs); ?>

<h2>What&apos;s that?</h2>

<p>A number of third-party sites provides their software in form of
packages that are installable using the <a href="/package/PEAR/">PEAR
installer</a>.  Some of them even provide <a href="/manual/en/guide.migrating.channels.php">channels</a> 
for PEAR &gt;= 1.4.0.  Specific installation instructures are provided
on the individual pages.</p>

<h2>List of Sites</h2>

<ul>
  <li><a href="http://pear.horde.org/">Horde</a></li>
  <li><a href="http://pearified.com/">Pearified</a></li>
  <li><a href="http://seagull.phpkitchen.com/docs/wakka.php?wakka=UsingThePearPackageManager&v=18nk">Seagull</a></li>
  <li><a href="http://solarphp.com/home/index.php?area=Main&page=DownloadInstall#toc1">Solar</a></li>
</ul>

<p><a href="/channels/add.php">Add your site</a></p>

<?php
response_footer();
?>
