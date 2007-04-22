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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

// expected url vars: pacid package
if (isset($_GET['package']) && empty($_GET['pacid'])) {
    $pacid = $_GET['package'];
} else {
    $pacid = (isset($_GET['pacid'])) ? (int) $_GET['pacid'] : null;
}

include_once 'pear-database-package.php';
$pkg = package::info($pacid);

if (empty($pkg['name'])) {
    response_header('Error');
    PEAR::raiseError('Invalid package');
    response_footer();
    exit();
}

$name = $pkg['name'];
response_header($name . ' Changelog');
print '<p>' . make_link('/package/' . $name, 'Return') . '</p>';
$bb = new Borderbox('Changelog for ' . $name, '90%', '', 2, true);

if (count($pkg['releases']) == 0) {
    $bb->fullRow('There are no releases for ' . $name . ' yet.');
} else {
    $bb->headRow('Release', 'What has changed?');

    foreach ($pkg['releases'] as $version => $release) {
        $link = make_link('/package/' . $pkg['name'] .
                          '/download/' . urlencode($version), $version);

        $notes = nl2br(make_ticket_links(htmlentities($release['releasenotes'])));
        if (!empty($_GET['release']) && $version == $_GET['release']) {
            $bb->horizHeadRow($link, $notes);
        } else {
            $bb->plainRow($link, $notes);
        }
    }
}
$bb->end();
print '<p>' . make_link('/' . $name, 'Return') . '</p>';
response_footer();
?>
