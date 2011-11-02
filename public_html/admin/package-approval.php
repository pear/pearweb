<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
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

redirect_to_https();
@session_start();
$csrf_token_name = 'pear_csrf_token_' . basename(__FILE__, '.php');

auth_require('pear.admin');

response_header('PEAR Administration :: Package Approval');

echo "<h1>Package Approval</h1>\n";

// Approve package identified by its id
if (!empty($_GET['approve']) || !empty($_GET['reject'])) {
    if (!validate_csrf_token($csrf_token_name, 'GET')) {
        report_error('Invalid token.');
        response_footer();
        exit();
    }

    if (!empty($_GET['approve'])) {
        $query = "UPDATE packages SET approved = 1 WHERE approved = 0 AND id = " . (int)$_GET['approve'];
        $id = $_GET['approve'];
        $action = "approved";
    } elseif (!empty($_GET['reject'])) {
        $query = "DELETE FROM packages WHERE approved = 0 AND id = " . (int)$_GET['reject'];
        $id = $_GET['reject'];
        $action = "rejected";
    }

    $res = $dbh->query($query);
    if (!PEAR::isError($res) && $dbh->affectedRows() > 0) {
        $sql = 'SELECT * FROM packages WHERE id = ?';
        $row = $dbh->getRow($sql, array($id), DB_FETCHMODE_ASSOC);

        if ($action == 'approved') {
             include_once 'pear-rest.php';
            $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
            $pear_rest->savePackageREST($row['name']);
            $pear_rest->saveAllPackagesREST();
            include_once 'pear-database-package.php';
            $pear_rest->savePackagesCategoryREST(package::info($row['name'], 'category'));
        }
        // {{{ Logging mechanism
        require_once "Damblan/Log.php";
        require_once "Damblan/Log/Mail.php";

        $logger = new Damblan_Log;
        $observer = new Damblan_Log_Mail;
        $observer->setRecipients(PEAR_GROUP_EMAIL);
        $observer->setHeader("In-Reply-To", "<approve-request-" . $row['id'] . "@" . PEAR_CHANNELNAME . ">");
        $observer->setHeader("Subject", "[" . SITE_BIG . " Group] Package " . $row['name'] . " has been " . $action);

        $logger->attach($observer);
        $logger->log($auth_user->handle . " " . $action . " " . $row['name']);
        // }}}

        if (!DEVBOX) {
            $mailtext = 'Your package "' . $row['name'] . '" has been ' . $action . ' by the PEAR Group.';
            $mailtext = wordwrap($mailtext, 72);

            $query = 'SELECT u.email FROM users u, maintains m WHERE m.package = ? AND u.handle = m.handle';
            $rows = $dbh->getAll($query, array($id), DB_FETCHMODE_ASSOC);
            foreach ($rows as $u_row) {
                mail($u_row['email'], SITE_BIG . ' Package ' . $action, $mailtext, 'From: "' . SITE_BIG . ' Package Approval System" <' . PEAR_GROUP_EMAIL . '>', '-f ' . PEAR_BOUNCE_EMAIL);
            }
        }

        echo "Successfully <b>" . $action . " package</b>.<br /><br />";
    } else {
        echo "There have been problems: Either an error occured while "
            . "updating the database or the package has already been "
            . $action . " by someone else.<br /><br />";
    }
}

$query = 'SELECT id, name FROM packages WHERE approved = 0 AND package_type = ?';
$rows = $dbh->getAll($query, array(SITE), DB_FETCHMODE_ASSOC);
$self = htmlspecialchars($_SERVER['PHP_SELF']);

if (count($rows) == 0) {
    echo "<b>Currently there are no unapproved packages.</b>\n";
} else {
    require_once 'HTML/Table.php';
    $table = new HTML_Table('style="width: 90%"');
    $table->setCaption('Unapproved packages', 'style="background-color: #CCCCCC;"');
    $csrf_link = '&amp;' . urlencode($csrf_token_name) . '='
        . urlencode(create_csrf_token($csrf_token_name));

    foreach ($rows as $row) {
        $tmp = array(
            $row['name'],
            make_link("$self?approve=" . $row['id'] . $csrf_link, "Approve") .
                ' / ' .
            make_link("$self?reject=" . $row['id'] . $csrf_link, "Reject")
        );
        $table->addRow($tmp);
    }

    echo $table->toHTML();
}

echo "<br /><br />";
echo make_link('/admin/', 'Back');

response_footer();
