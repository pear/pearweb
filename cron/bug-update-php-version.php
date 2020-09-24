<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2003-2007 The PEAR Group                               |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Helgi Þormar Þorbjörnsson <dufuz@php.net>                   |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

// NOTE: this data does not contain alpha, beta or RC releases.
function sort_versions($a, $b) {
    // Reverse order.
    return version_compare($b, $a);
}
function get_versions($version) {

    $new5 = file_get_contents("http://www.php.net/releases/?json=1&version={$version}&max=40");
    if ($new5 === false) {
        die('There was a problem fetching release data from php.net');
    }
    $unj = json_decode($new5, true);
    $n5 = array_keys($unj);
    usort($n5, 'sort_versions');
    return $n5;
}

$v74 = get_versions("7.4");
$v73 = get_versions("7.3");
$v72 = get_versions("7.2");
$v56 = get_versions("5.6");
$v56 = get_versions("5.6");
$n5 = array_merge($v74, $v73, $v72, $v56);
var_dump ($n5);

$file = '@www-dir@/pear.php.net/public_html/bugs/include/php_versions.php';
if (!file_exists($file)) {
    $file = dirname(__FILE__) . '/../public_html/bugs/include/php_versions.php';
}

$output = '<?php
// Update this file using pearweb/cron/bug-update-php-version.
$versions = array(
    "8.0.0 (Specify exact version in description)",
';

foreach ($n5 as $version) {
    $output .= '    "' . $version . "\",\n";
}

$output .= ");\n";

file_put_contents($file, $output);
