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

require_once 'pear-config.php';
if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
    error_reporting(E_ALL);
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
    define('DEVBOX', false);
}

if (!DEVBOX) {
    define('PEAR_REST_PATH', '/var/lib/pearweb/rest');
} else {
    define('PEAR_REST_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
}

require_once 'PEAR.php';

if (empty($format)) {
    $format = 'html';
}

include_once "pear-format-$format.php";
include_once 'pear-auth.php';
include_once 'pear-rest.php';

if (!isset($pear_rest)) {
    $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH);
}

if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER']) && !auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    $__user = $_COOKIE['PEAR_USER'];
    setcookie('PEAR_USER', '', 0, '/');
    unset($_COOKIE['PEAR_USER']);
    setcookie('PEAR_PW', '', 0, '/');
    unset($_COOKIE['PEAR_PW']);
    $msg = "Invalid username ($__user) or password";
    if ($format == 'html') {
        $msg .= ' <a href="/?logout=1">[logout]</a>';
    }
    auth_reject(null, $msg);
}

/**
 * Converts a Unix timestamp to a date() formatted string in the UTC time zone
 *
 * @param int    $ts      a Unix timestamp from the local machine.  If none
 *                         is provided the current time is used.
 * @param string $format  a format string, as per http://php.net/date
 *
 * @return string  the time formatted time
 */
function make_utc_date($ts = null, $format = 'Y-m-d H:i \U\T\C')
{
    if (!$ts) {
        $ts = time();
    }
    return gmdate($format, $ts);
}