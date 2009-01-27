#!/usr/local/bin/php
<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2009  The PEAR Group                                   |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Author: Christian Weiske <cweiske@php.net>                           |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once dirname(dirname(__FILE__)) . '/include/pear-config.php';
require_once 'DB.php';

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (DB::isError($dbh)) {
    echo $dbh->getMessage() . "\n";
    echo $dbh->getUserInfo() . "\n";
    exit(1);
}
$dbh->setFetchMode(DB_FETCHMODE_OBJECT);

$query = <<<SQL
SELECT r1.releasedate AS releasedate, r1.version AS version, packages.name AS name
FROM packages, releases AS r1
 LEFT JOIN releases AS r2
  ON r1.releasedate < r2.releasedate
  AND r1.package = r2.package
WHERE packages.id = r1.package
 AND packages.package_type = 'pear'
 AND r2.releasedate IS NULL
SQL;
$res = $dbh->query($query);

$errors = 0;
$fixed  = 0;
$ok     = 0;
while ($row = $res->fetchRow()) {
    $pkg       = $row->name;
    $version   = $row->version;
    $dir       = PEAR_APIDOC_DIR . $pkg . '-' . $version;
    $latestdir = PEAR_APIDOC_DIR . $pkg . '-latest';

    //check if apidoc of current version exists
    if (file_exists($latestdir)) {
        'ok: ' . $latestdir . ' -> ' . $dir . "\n";
        ++$ok;
        continue;
    }
    if (!file_exists($dir)) {
        echo 'No apidoc for current version: ' . $dir . "\n";
        ++$errors;
        continue;
    }
    echo 'Making latest: ' . $dir . "\n";
    symlink($dir, $latestdir);
    ++$fixed;
}

echo sprintf("%d errors, %d fixed, %d already ok\n", $errors, $fixed, $ok);
?>