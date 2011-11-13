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
$new5 = file_get_contents('http://www.php.net/releases/?serialize=1&version=5&max=40');
if ($new5 === false) {
    die('There was a problem fetching the serialized array from php.net');
}
$n5 = array_keys(unserialize($new5));

usort($n5, 'sort_versions');
function sort_versions($a, $b) {
    // Reverse order.
    return version_compare($b, $a);
}

$file = '@www-dir@/pear.php.net/public_html/bugs/include/php_versions.php';
if (!file_exists($file)) {
    $file = dirname(__FILE__) . '/../public_html/bugs/include/php_versions.php';
}

$output = '<?php
// Update this file using pearweb/cron/bug-update-php-version.
$versions = array(
    "HEAD SVN-" . date("Y-m-d"),
    "5_4 SVN-" . date("Y-m-d"),
';

$output_53_start = '
    "5.4 RC (specify # in Description)",
    "5.4.0 beta",
    "5_3 SVN-" . date("Y-m-d"),
';

$found_53 = false;
foreach ($n5 as $version) {
    if (!$found_53 && strpos($version, '5.3.') !== false) {
        $found_53 = true;
        $output .= "$output_53_start\n";
    }
    $output .= '    "' . $version . "\",\n";
}

$output .= ");\n";

file_put_contents($file, $output);
