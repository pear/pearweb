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
auth_require('pear.qa');

response_header('Quality Assurance Initiative - Orphan packages',
    false);

$packages = $dbh->getAll("SELECT p.name " .
    " FROM packages p ".
    "WHERE p.unmaintained=1 AND p.package_type = 'pear' AND p.approved = 1 ".
    "ORDER BY p.name", null, DB_FETCHMODE_ASSOC);

echo "<h3>List of orphan packages</h3>\n";

$text_mode = '';

echo "<ul>\n";
foreach ($packages as $pck => $info) {

    $link = make_link('/package/' . $info['name'], 
        $info['name'], '', 'title="' . $info['name'] . '"');

    echo '<li>' . $link . "</li>\n";
    $text_mode .= $info['name'] . "\n";
}
echo "</ul>\n";

// output the list as plain text. This will be added to a text
// file so people can browse the list
echo "<pre>" . $text_mode . "</pre>";
response_footer();
?>
