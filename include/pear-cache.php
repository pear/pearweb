<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2004 The PHP Group                                |
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

// Adding _no_cache=1 to the URL prevents caching
if (!empty($_GET['_no_cache']) && (int)$_GET['_no_cache'] == 1) {
    $no_cache = 1;
} else {
    $no_cache = 0;
}

$cache_files = array("/credits.php" => "", 
                     "/copyright.php" => "",
                     "/dtd/index.php" => "",
                     "/feeds/index.php" => "",
                     "/download-docs.php" => "",
                     "/account-info.php" => $_SERVER['PHP_SELF'],
                     // "/packages.php" => @$_GET['catpid'] . @$_GET['showempty'] . "__" . @$_GET['hideMoreInfo'] . "__" . @$_GET['showMoreInfo']
                     );

$cache_dirs  = array("/news" => "",
                     "/about" => "",
                     "/group" => "",
                     "/group/docs" => "",
                     "/manual" => "",
                     "/manual/en" => "",
                     "/manual/ja" => "",
                     "/manual/nl" => "",
                     "/manual/ri" => "",
                     "/user" => $_SERVER['PHP_SELF']
                     );

if (!in_array($_SERVER['PHP_SELF'], array_keys($cache_files)) &&
    !in_array(dirname($_SERVER['PHP_SELF']), array_keys($cache_dirs))) {
    $no_cache = 1;
}

if ($no_cache == 0) {
    require_once "pear-config.php";
    require_once "Cache/Lite.php";

    // Initiate caching
    $options = array('cacheDir' => PEAR_TMPDIR . "/webcache/",
                     'lifeTime' => 3600);
    $cache = new Cache_lite($options);

    $id = $_SERVER['PHP_SELF'];
    if (!empty($_COOKIE['PEAR_USER'])) {
        $id .= "user_logged_in";
    }
    if (!empty($cache_files[$_SERVER['PHP_SELF']])) {
        $id .= $cache_files[$_SERVER['PHP_SELF']];
    }

    if (!$cache_data = $cache->get($id)) {
        ob_start();
    } else {
        exit($cache_data);
    }
}
?>