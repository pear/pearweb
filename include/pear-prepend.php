<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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

if ($_SERVER['SERVER_NAME'] != 'pear.php.net') {
    error_reporting(E_ALL);
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
    define('DEVBOX', false);
}

require_once "pear-cache.php";

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";

$encoding = "iso-8859-1";

if (substr($_SERVER['PHP_SELF'], 0, 7) == '/manual') {
    require_once "pear-manual.php";

    // The Japanese and Russian manual translations neeed UTF-8 encodings
    if (preg_match("=^/manual/(ja|ru)=", $_SERVER['PHP_SELF'])) {
        $encoding = "utf-8";
    }
}

if (empty($format)) {
    if (basename($_SERVER['PHP_SELF']) == "xmlrpc.php") {
        $format = "xmlrpc";
    } else {
        $format = "html";
    }
}

include_once "pear-format-$format.php";

function get($name)
{
    if (!empty($_GET[$name])) {
        return $_GET[$name];
    } else if (!empty($_POST[$name])) {
        return $_POST[$name];
    } else {
        return "";
    }
}

if (empty($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => false));
}

$LAST_UPDATED = date("D M d H:i:s Y T", filectime($_SERVER['SCRIPT_FILENAME']));

if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER']) && !@auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    $__user = $_COOKIE['PEAR_USER'];
    setcookie('PEAR_USER', '', 0, '/');
    unset($_COOKIE['PEAR_USER']);
    setcookie('PEAR_PW', '', 0, '/');
    unset($_COOKIE['PEAR_PW']);
    $msg = "Invalid username ($__user) or password";
    if ($format == 'html') {
        $msg .= " <a href=\"/?logout=1\">[logout]</a>";
    }
    auth_reject(null, $msg);
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file, $use_include_path = false) {
        if (!$fp = fopen($file, 'r', $use_include_path)) {
            return false;
        }
        $data = fread($fp, filesize($file));
        fclose($fp);
        return $data;
    }
}

session_start();
?>
