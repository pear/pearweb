<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
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

auth_require("pear.dev");
response_header("API Documentation Queue Log");

echo "<h1>API Documentation Queue Log</h1>";

if (!empty($_GET['filename'])) {
    $filename = urldecode($_GET['filename']);

    $query = "SELECT queued, finished, log FROM apidoc_queue WHERE filename = ?";
    $info = $dbh->getRow($query, array($filename), DB_FETCHMODE_ASSOC);

    if (!is_array($info)) {
        echo "<div class=\"errors\">No such file " . strip_tags($filename) . ".</div>\n";
    } else {
        echo "<p>Log for <strong>" . $filename . "</strong>:</p>\n";
        echo "<ul>\n";
        echo "  <li>Queued: " . $info['queued'] . "</li>\n";
        echo "  <li>Finished: " . $info['finished'] . "</li>\n";
        echo "  <li>Command output:<br /><pre>" . strip_tags($info['log']) . "</pre></li>\n";
        echo "</ul>\n";
    }
}

$query = "SELECT filename FROM apidoc_queue ORDER BY queued ASC";
$rows = $dbh->getAll($query, array(), DB_FETCHMODE_ASSOC);

echo "<p>Select a filename:</p>\n\n";

echo "<ul>";
foreach ($rows as $row) {
    printf("<li><a href=\"%s?filename=%s\">%s</a></li>\n",
           $_SERVER['PHP_SELF'],
           urlencode($row['filename']),
           $row['filename']
           );
}
echo "</ul>";

response_footer();
?>
