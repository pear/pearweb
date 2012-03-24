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
include_once 'pear-format-html.php';
include_once 'pear-auth.php';

// Installation / configuration type checks
if (!file_exists(PEAR_UPLOAD_TMPDIR)) {
   $log->warn('PEAR_UPLOAD_TMPDIR set to ' . PEAR_UPLOAD_TMPDIR . ' which does not exist');
}

if (!is_dir(PEAR_UPLOAD_TMPDIR)) {
   $log->warn('PEAR_UPLOAD_TMPDIR set to ' . PEAR_UPLOAD_TMPDIR . ' which is not a directory');
   if (!mkdir(PEAR_UPLOAD_TMPDIR)) {
      $log->err("Could not mkdir " . PEAR_UPLOAD_TMPDIR);
   }
}

if (!is_writeable(PEAR_UPLOAD_TMPDIR)) {
   $log->err('PEAR_UPLOAD_TMPDIR set to ' . PEAR_UPLOAD_TMPDIR . ' which is not writeable');
}

if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER']) && !auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    auth_kill_cookies();
    auth_reject(null, 'Invalid username or password');
}
