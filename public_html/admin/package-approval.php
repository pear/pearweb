<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require("pear.group");

response_header("PEAR Administration :: Package Approval");

echo "<h1>Package Approval</h1>\n";

// Approve package identified by its id
if (!empty($_GET['approve'])) {
    $query = "SELECT * FROM packages WHERE id = ?";
    $row = $dbh->getRow($query, array($_GET['approve']), DB_FETCHMODE_ASSOC);

    $query = "UPDATE packages SET approved = 1 WHERE id = ?";
    $res = $dbh->query($query, array($_GET['approve']));

    if (!PEAR::isError($res)) {
        $mailtext = $auth_user->handle . " approved " . $row['name'];
        $header = "In-Reply-To: <approve-request-" . $row['id'] . "@pear.php.net>";
        mail("pear-group@php.net", "Package " . $row['name'] . " has been approved", $mailtext, $header, "-f pear-sys@php.net");

        echo "Successfully <b>approved package</b>.<br /><br />";
    }
}

$query = "SELECT * FROM packages WHERE approved = 0";
$rows = $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);

if (count($rows) == 0) {
    echo "<b>Currently there are no unapproved packages.</b>\n";
} else {
    $bb = new BorderBox("Unapproved packages", "90%", "", 2, true);

    foreach ($rows as $row) {
        $bb->plainRow($row['name'], make_link($_SERVER['PHP_SELF'] . "?approve=" . $row['id'], "Approve"));
    }

    $bb->end();
}

echo "<br /><br />";
print_link("/admin/", "Back");

response_footer();
?>