<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * Details about PEAR accounts
 */
require_once "Damblan/URL.php";
$site = new Damblan_URL();

$params = array("handle" => "");
$site->getElements($params);

$handle = $params['handle'];

/**
 * Redirect to the accounts list if no handle was specified
 */
if (empty($handle)) {
    localRedirect("/accounts.php");
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = user::info($handle);
if ($row === null) {
    // XXX: make_404();
    PEAR::raiseError("No account information found!");
}

response_header($row['name']);

print "<h1>" . $row['name'] . "</h1>\n";

print "<table border=\"0\" cellspacing=\"4\" cellpadding=\"0\">\n";
print "<tr><td valign=\"top\">\n";

$bb = new BorderBox("Account Details", "100%", "", 2, true);
$bb->horizHeadRow("Handle:", $handle);
$bb->horizHeadRow("Name:", $row['name']);
if ($row['showemail'] != 0) {
    $bb->horizHeadRow("Email:", "<a href=\"/account-mail.php?handle=" . $handle . "\">".str_replace(array("@", "."), array(" at ", " dot "), $row['email'])."</a>");
}
if (!empty($row['pgpkeyid'])) {
    $bb->horizHeadRow("PGP Key:", make_link("http://pgp.mit.edu:11371/pks/lookup?search=0x" . $row['pgpkeyid'] . "&op=get", $row['pgpkeyid']));
}
if ($row['homepage'] != "") {
	$bb->horizHeadRow("Homepage:",
					  "<a href=\"$row[homepage]\" target=\"_blank\">".
					  "$row[homepage]</a></td>\n");
}

$bb->horizHeadRow("Registered since:", $row['created']);
$bb->horizHeadRow("Additional information:", empty($row['userinfo'])?"&nbsp;":stripslashes($row['userinfo']));

if ($row['wishlist'] != "") {
    $bb->horizHeadRow("Wishlist:", make_link("/wishlist.php/" . $row['handle'], "Click here to be redirected."));
}

$bb->fullRow("Get the " . make_link("/feeds/user_" . strtolower($handle) . ".rss", "RSS feed"));
$bb->end();

print "</td><td valign=\"top\">\n";

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = ?
          AND p.id = m.package
          ORDER BY p.name";
$maintained_pkg = $dbh->getAll($query, array($handle), DB_FETCHMODE_ASSOC);

$bb = new BorderBox("Maintaining These Packages:", "100%", "", 2, true);

if (count($maintained_pkg) > 0) {
    $bb->headRow("Package Name", "Role");
    foreach ($maintained_pkg as $row) {
		$bb->plainRow("<a href=\"/package/" . $row['name'] . "\">" . $row['name'] . "</a>",
					  $row['role']);
    }
}

$bb->end();

print "<br />\n";

display_user_notes($handle, "100%");

print "<br /><a href=\"/account-edit.php?handle=$handle\">". make_image("edit.gif", "Edit") . "</a>";

print "</td></tr></table>\n";

response_footer();

?>
