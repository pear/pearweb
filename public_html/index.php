<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

$recent = release::getRecent(10);
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    $today = date("D, jS M y");
    foreach ($recent as $release) {
        extract($release);
        $releasedate = make_utc_date(strtotime($releasedate), "D, jS M y");
        if ($releasedate == $today) {
            $releasedate = "today";
        }
        $RSIDEBAR_DATA .= "<tr><td valign=\"top\" class=\"compact\">";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $name . "/\">";
        $RSIDEBAR_DATA .= "$name $version</a><br /> <small>($releasedate)</small></td></tr>";
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" border="0" /></a>';
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_header();
?>

<h1>PEAR - PHP Extension and Application Repository</h1>

<h2>About PEAR</h2>

<p><acronym title="PHP Extension and Application Repository">PEAR</acronym>
is a framework and distribution system for reusable PHP
components. More <b>information</b> about PEAR can be found in the
<a href="/manual/en/">online manual</a> and the
<a href="/manual/en/faq.php">FAQ</a>.</p>

<p>If you are a first time user, you might be especially interested in
the manual chapter &quot;<a href="/manual/en/about-pear.php">About PEAR</a>&quot;.</p>

<p>Recent <b>news</b> about PEAR can be found <a href="/news/">here</a>.</p>

<p>PEAR provides the above mentioned PHP components in the form of so
called &quot;Packages&quot;. If you would like to <b>download</b> PEAR
packages, you can <a href="/packages.php">browse the complete list</a>
here.  Alternatively you  can  search for packages by some keywords
using the search box above. Apart from simply downloading a package,
PEAR also provides a command-line interface that can be used to
automatically <b>install</b> packages. The manual <a href="/manual/en/installation.cli.php">
describes this procedure</a> in detail.</p>

<p>In case you need <b>support</b> for PEAR in general or a package
in special, we have compiled a list of the <a href="/support/">available
support resources</a>.</p>

<?php
echo hdelim();
if ($auth_user) {
    if (auth_check('pear.dev')) {
        echo '<h2>&raquo; Developers</h2>';
        echo '<div class="indent">';

        echo menu_link("Upload Release", "release-upload.php");
        echo menu_link("New Package", "package-new.php");

        echo '</div>';
    }

    echo '<h2>&raquo; Package Proposals (PEPr)</h2>';
	echo '<div class="indent">';
	echo menu_link("Browse Proposals", "pepr/");
	echo menu_link("New Package Proposal", "pepr/pepr-proposal-edit.php");
    echo '</div>';

    if (user::isAdmin($auth_user->handle)) {
        echo '<h2>&raquo; Administrators</h2>';
        echo '<div class="indent">';
        echo menu_link("Overview", "/admin/");
        echo '</div>';
    }

} else {
?>

<p>If you have been told by other PEAR developers to sign up for a
PEAR website account, you can use <a href="/account-request.php">
this interface</a>.</p>

<?php
}

response_footer();

?>
