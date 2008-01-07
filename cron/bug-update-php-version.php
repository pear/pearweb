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

// fetch the data
$new5 = file_get_contents('http://www.php.net/releases/?serialize=1&version=5');
if ($new5 === false) {
    die('There was a problem fetching the serialized array from php.net');
}

$new4 = file_get_contents('http://www.php.net/releases/?serialize=1&version=4');
if ($new4 === false) {
    die('There was a problem fetching the serialized array from php.net');
}

$n5 = unserialize($new5);
$n4 = unserialize($new4);

$file = '@www_dir@/public_html/bugs/include/php_versions.php';
include $file;

$php4 = $php = array();
foreach ($versions as $v) {
    if ($v[0] != '4') {
        $php[] = $v;
    } else {
        $php4[] = $v;
    }
}

if (!in_array($n5['version'], $versions) && strpos($n5['version'], 'RC') === false) {
    $merge = array_merge(array($n5['version']), $php);
} else {
    $merge = $php;
}

if (!in_array($n4['version'], $versions) && strpos($n4['version'], 'RC') === false) {
    $merge4 = array_merge(array($n4['version']), $php4);
} else {
    $merge4 = $php4;
}

$final  = array_merge($merge, $merge4);
$data   = var_export($final, true);

$output = '<?php
$versions = ' . $data . ';';

file_put_contents($file, $output);
