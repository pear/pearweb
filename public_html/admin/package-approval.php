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

    $query = "UPDATE packages SET approved = 1 WHERE id = ? AND approved = 0";
    $res = $dbh->query($query, array($_GET['approve']));

    if (!PEAR::isError($res) && $dbh->affectedRows() > 0) {
        // {{{ Logging mechanism
        require_once "Damblan/Log.php";
        require_once "Damblan/Log/Mail.php";

        $logger = new Damblan_Log;
        $observer = new Damblan_Log_Mail;
        $observer->setRecipients("pear-group@php.net");
        $observer->setHeader("In-Reply-To", "<approve-request-" . $row['id'] . "@pear.php.net>");
        $observer->setHeader("Subject", "Package " . $row['name'] . " has been approved");

        $logger->attach($observer);
        $logger->log($auth_user->handle . " approved " . $row['name']);
        // }}}

        $mailtext = "Your package \"" . $row['name'] . "\" has been approved by the PEAR Group.";
        $mailtext = wordwrap($mailtext, 72);

        $query = "SELECT u.email FROM users u, maintains m WHERE m.package = ? AND u.handle = m.handle";
        $rows = $dbh->getAll($query, array($_GET['approve']), DB_FETCHMODE_ASSOC);
        foreach ($rows as $u_row) {
            mail($u_row['email'], "PEAR Package approved", $mailtext, "From: \"PEAR Package Approval System\" <pear-group@php.net>", "-f pear-sys@php.net");
        }

        echo "Successfully <b>approved package</b>.<br /><br />";
    } else {
        echo "There have been problems: Either an error occured while "
            . "updating the database or the package has already been "
            . "approved by someone else.<br /><br />";
    }
}

$query = "SELECT * FROM packages WHERE approved = 0 AND package_type = 'pear'";
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