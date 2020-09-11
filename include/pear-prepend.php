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
date_default_timezone_set('UTC');
require_once 'pear-config.php';
require_once 'PEAR.php';
require_once 'pear-format-html.php';
require_once 'pear-auth.php';
require_once 'Validate.php';


if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER'])
    && !auth_verify(
        filter_var($_COOKIE['PEAR_USER'], FILTER_SANITIZE_STRING),
        filter_var($_COOKIE['PEAR_PW'], FILTER_SANITIZE_STRING)
    )
) {
    auth_kill_cookies();
    auth_reject(null, 'Invalid username or password');
}
