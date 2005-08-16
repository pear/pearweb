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
 * This page will show a list of packages with latest
 * release information along with bug count and any other info
 * that may be used to identify unmaintained packages
 */
auth_require('pear.qa');

response_header('Quality Assurance Initiative - Packages status',
    false, $extra_header);

$states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');
$tables = array();

echo "View packages status for the following state\n";

echo "<ul>\n";
foreach ($states as $state) {
    $link = make_link(
	'/qa/packages_status_detail.php?state=' . $state, 
        'See packages status for state ' . $state, '',
	'title="Details for state ' . $state . '"');
    echo '<li>' . $state . '</li>';
}
echo "</ul>\n";

response_footer();
?>
