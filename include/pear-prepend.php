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

if (!DEVBOX) {
    define('PEAR_REST_PATH', '/var/lib/pearweb/rest');
} else {
    define('PEAR_REST_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
}

require_once 'PEAR.php';
include_once 'pear-format-html.php';
include_once 'pear-auth.php';

if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER']) && !auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    auth_kill_cookies();
    auth_reject(null, 'Invalid username or password');
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