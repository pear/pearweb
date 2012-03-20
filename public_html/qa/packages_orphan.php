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
   | Authors: Arnaud Limbourg <arnaud@limbourg.com>                       |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * List orphan packages
 */

response_header('Quality Assurance Initiative - Orphan packages',
    false);

$query = '
    SELECT name, newpackagename, newchannel
    FROM packages
    WHERE unmaintained = 1 AND package_type = "pear" AND approved = 1
    ORDER BY name';

$packages = $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);

if (count($packages) == 0) {
    echo '<p>There are no orphan packages</p>';
    response_footer();
    exit();
}

$superseded_packages = $orphan_packages = '';
foreach ($packages as $pck => $info) {
    $link = make_link('/package/' . $info['name'],
        $info['name'], '', 'title="' . $info['name'] . '"');

    $link_superseding = '';

    if (!empty($info['newpackagename'])) {
        $link_superseding = 'There is a superseding package: ';
        if ($info['newchannel'] != PEAR_CHANNELNAME) {
            $host = 'http://' . $info['newchannel'];
        } else {
            $host = '/package/' . $info['newpackagename'];
        }
        $link_superseding .= make_link($host, $info['newpackagename'], '',
                                       'title="' . $info['newpackagename'] . '"');
    }

    if ($link_superseding !== '') {
        $superseded_packages .= '<li>' . $link . ' ' . $link_superseding . "</li>\n";
    } else {
        $orphan_packages .= '<li>' . $link . ' ' . $link_superseding . "</li>\n";
    }
}

echo "<h2>List of orphan packages</h2>\n";

echo '<table>';
echo '<th>Orphaned Packages</th><th>Superseded Packages</th>';
echo '<tr><td style="width: 50%; vertical-align: top;">';
echo "<ul>\n";
echo $orphan_packages;
echo "</ul>\n";
echo '</td><td style="width: 50%; vertical-align: top;">';
echo "<ul>\n";
echo $superseded_packages;
echo "</ul>\n";
echo '</td></tr></table>';

response_footer();
