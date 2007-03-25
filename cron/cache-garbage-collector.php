<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2004-2005 The PEAR Group                               |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Gregory Beaver <cellog@php.net>                             |
 * |          Martin Jansen <mj@php.net>                                  |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once dirname(__FILE__) . "/../include/pear-config.php";

$basepath = PEAR_TMPDIR . "/webcache/";

$basepath = '/home/cellog/fronk';
$iter = new RecursiveDirectoryIterator($basepath, RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);

foreach (new RecursiveIteratorIterator($iter) as $file) {
    if ($file->isDir()) {
        continue;
    }
    if (time() - $file->getCTime() > CACHE_LIFETIME) {
        unlink($file->getPathName());
    }
}