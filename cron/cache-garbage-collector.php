<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2003 The PEAR Group                                    |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Martin Jansen <mj@php.net>                                  |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once "PEAR.php";
require_once dirname(__FILE__) . "/../include/pear-config.php";
require_once "VFS.php";
require_once "VFS/file.php";

$basepath = PEAR_TMPDIR . "/webcache/";

$vfs = new VFS_file(array("vfsroot" => $basepath));

// {{{ readFolder()

function readFolder($folder) {
    global $vfs, $basepath;

    static $level;
    $level++;

    $result = $vfs->listFolder($folder);

    if ($folder == ".") {
        $folder = "";
    }

    foreach ($result as $file) {
        $age = time() - $file['date'];

        if ($age > CACHE_LIFETIME) {
            $vfs->deleteFile($folder, $file['name']);
        }
    }
}

// }}}

readfolder(".");