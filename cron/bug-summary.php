<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2003-2004 The PEAR Group                               |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Helgi �ormar �orbj�rnsson <dufuz@php.net>                   |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once 'PEAR.php';

// Get common settings.
require_once dirname(dirname(__FILE__)) . '/include/pear-config.php';
require_once dirname(dirname(__FILE__)) . '/include/pear-format-html.php';
require_once dirname(dirname(__FILE__)) . '/public_html/bugs/include/functions.inc';

// Get the database class.
require_once 'DB.php';
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (PEAR::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}

$dbh->setFetchMode(DB_FETCHMODE_ASSOC);

$query = "
    SELECT
        b.id, b.package_name, b.status, b.sdesc, b.email
    FROM bugdb b
    LEFT JOIN packages AS p ON p.name = b.package_name
    WHERE
        b.status NOT IN ('Closed', 'Bogus', 'Duplicate', 'No Feedback', 'Wont fix', 'Suspended', 'Spam')
      AND
        (b.bug_type = 'Bug' OR b.bug_type = 'Documentation Problem' OR
         b.bug_type = 'Feature/Change Request')
      AND
        (p.package_type = '" . SITE . "'
             OR
         b.package_name IN ('Documentation'))
    ORDER BY b.package_name, b.id, b.status";
$result =& $dbh->getAll($query);

if (count($result) > 0 && !PEAR::isError($result)) {
    $body  = ' ' . SITE_BIG . ' Bug Database summary - http://' . PEAR_CHANNELNAME . '/bugs' . "\n\n";
    $body .= '  ID  Status     Summary (' . count($result) . ' total)'."\n";

    // Make the Package -> Bug array
    foreach ($result as $row) {
        $packageBugs[$row['package_name']][$row['id']] = array(
                'sdesc'        => $row['sdesc'],
                'email'        => $row['email'],
                'status'       => $row['status']
        );
    }
    unset($row);
    unset($result);

    // Process bugs for each package
    if (!empty($packageBugs)) {
        foreach ($packageBugs as $package => $value) {
            $dev_text = '';
            $title = '===============================================[' . $package . ']';
            $len = 29-strlen($package);
            for ($i = 0; $i < $len; $i++) {
                $title .= '=';
            }
            $body .= $title .= "\n";
            $dev_text .= ' ' . SITE_BIG . ' Bug Database summary for ' . $package . ' - http://' . PEAR_CHANNELNAME . '/bugs' . "\n\n";
            //$dev_text .= ' Here comes some fun fun text which QA still hasn't decided upon'."\n\n";
            $dev_text .= '  ID  Status     Summary'."\n";

            foreach ($value as $id => $bug_info) {
                $text = sprintf("%5d ", $id);
                $text .= sprintf("%-8s ",$bug_info['status']);
                $text .= ' '.$bug_info['sdesc'].'. ';
                $body .= $text . "\n";

                // format mail so it looks nice, use 72 to make piners happy
                $wrapped_text = wordwrap($text, 72);

                $dev_text .= "\n" . $wrapped_text .
                            "\n\n" . '  Further comments can be seen at http://' . PEAR_CHANNELNAME . '/bugs/' . $id.
                            "\n" . '  Edit this bug report at http://' . PEAR_CHANNELNAME . '/bugs/bug.php?id=' . $id . '&edit=1' . "\n";

            }

            $subject = '[' . SITE_BIG . '-BUG][Reminder] Reminder about open bugs in ' . $package;

            switch ($package) {
                case 'Web Site':
                case 'Bug System':
                // Remember to remove PEPr when it's a package on its own
                case 'PEPr':
                    $to = PEAR_WEBMASTER_EMAIL;
                    break;
                case 'Documentation':
                    $to = PEAR_DOC_EMAIL;
                    // retrieve Documentation Problem bugs for each package
                    $query = "
                        SELECT
                            b.id, b.package_name, b.status, b.sdesc, b.email
                        FROM bugdb b
                        LEFT JOIN packages AS p ON p.name = b.package_name
                        WHERE
                            b.status NOT IN ('Closed', 'Bogus', 'Duplicate', 'No Feedback', 'Wont fix', 'Suspended', 'Feedback', 'Spam')
                          AND
                            b.bug_type = 'Documentation Problem'
                          AND
                            p.package_type = ?
                        ORDER BY b.package_name, b.id";
                    $docbugs =& $dbh->getAll($query, array(SITE));
                    if (count($docbugs)) {
                        $dev_text .= "[Documentation Bugs by Package]\n";
                    }
                    $current_package = '#######';
                    foreach ($docbugs as $bug_info) {
                        if ($current_package != $bug_info['package_name']) {
                            $dev_text .= 'Package ' . $bug_info['package_name'];
                            $current_package = $bug_info['package_name'];
                        }
                        $text  = sprintf("%5d ", $bug_info['id']);
                        $text .= sprintf("%-8s ",$bug_info['status']);
                        $text .= ' '.$bug_info['sdesc'].'. ';

                        // format mail so it looks nice, use 72 to make piners happy
                        $wrapped_text = wordwrap($text, 72);

                        $dev_text .= "\n" . $wrapped_text .
                                    "\n\n" . '  Further comments can be seen at http://' . PEAR_CHANNELNAME . '/bugs/' . $bug_info['id'] .
                                    "\n" . '  Edit this bug report at http://' . PEAR_CHANNELNAME . '/bugs/bug.php?id=' . $bug_info['id'] . '&edit=1' . "\n";
                    }
                    break;
                default:
                    $to = '';
                    break;
            }

            $from = SITE != 'pecl' ?  ' QA' : ' Dev';
            $mail_headers = 'From: ' . SITE_BIG . $from . ' <' . PEAR_QA_EMAIL .">\r\n";

            if ($to == '') {
                $query = "SELECT u.name, u.email
                          FROM maintains m, packages p, users u
                          WHERE
                              m.role IN('lead', 'developer')
                            AND
                              m.handle = u.handle
                            AND
                              m.active = '1'
                            AND
                              p.package_type = '" . SITE . "'
                            AND
                              p.approved = 1
                            AND
                              p.id = m.package
                            AND
                              p.name = '$package'";
                $result =& $dbh->getAll($query);

                if (count($result) > 0 && !PEAR::isError($result)) {
                    $mail_headers .= 'CC: ';
                    foreach ($result as $maintain) {
                        if ($to == '') {
                            $to = $maintain['name'] . '<' . $maintain['email'] . '>';
                        } else {
                            $mail_headers .= $maintain['name'] . '<' . $maintain['email'] . '>,';
                        }
                    }
                    $mail_headers = substr($mail_headers, 0, -1);
                }
            }
            // Email Leads/Developers of X package with a summary of open bugs for the package
            if (!DEVBOX) {
                mail($to, $subject, $dev_text, $mail_headers, '-f ' . PEAR_BOUNCE_EMAIL);
            }
        }
    }
    // Email PEAR-QA the whole bug list
    if (!DEVBOX) {
        mail(PEAR_QA_EMAIL, SITE_BIG . ' Bug Summary Report', $body, 'From: ' . SITE_BIG . $from . ' <' . PEAR_QA_EMAIL .">\r\n", '-f ' . PEAR_BOUNCE_EMAIL);
    }
}
