#!/usr/local/bin/php
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
 * | Author: Martin Jansen <mj@php.net>                                   |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once dirname(__FILE__) . "/../include/pear-config.php";
require_once "DB.php";
require_once "PEAR/Common.php";
require_once "Archive/Tar.php";

$pkg_handler = new PEAR_Common();

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);

$query = "SELECT filename FROM apidoc_queue WHERE finished = '0000-00-00 00:00:00'";
$rows = $dbh->getCol($query);

foreach ($rows as $filename) {
    $info = $pkg_handler->infoFromTgzFile($filename);
    $tar = new Archive_Tar($filename);

    if (PEAR::isError($info)) {
        continue;
    }

    $name = (isset($info['package']) ? $info['package'] : $info['name']);

    echo "Generating documentation for " . $name . " " . $info['version'] . "\n";

    /* Extract files into temporary directory */
    $tmpdir = PEAR_TMPDIR . "/apidoc/" . $name;

    if (!$pkg_handler->mkDirHier($tmpdir)) {
        die("Unable to create temporary directory " . $tmpdir . "\n");
    }

    $tar->extract($tmpdir);

    $command = sprintf("/usr/local/bin/phpdoc -d %s -dn '%s' -ti '%s' -p on -s on -t %s -o %s --ignore */data/*,*/tests/*; rm -rf %s",
                       $tmpdir,
                       $name,
                       $name . " " . $info['version'],
                       PEAR_APIDOC_DIR . "/" . $name . "-" . $info['version'],
                       "HTML:Smarty:PEAR",
                       $tmpdir
                       );

    $output = "";
    $process = popen($command, "r");

    if ($process) {
        while ($line = fgets($process)) {
            $output .= $line;
        }
        pclose($process);

        $cmd = sprintf("ln -shf %s/%s-%s %s/%s-latest",
                       PEAR_APIDOC_DIR,
                       $name,
                       $info['version'],
                       PEAR_APIDOC_DIR,
                       $name);
        `$cmd`;

        $query = "UPDATE apidoc_queue SET finished = NOW(), log = ? WHERE filename = ?";
        $dbh->query($query, array($output, $filename));
    }
}
