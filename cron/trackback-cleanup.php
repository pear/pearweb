<?php

/**
 * Automated cleanup of trackbacks that have not been approved within a certain
 * timespan.
 *
 * @category  pearweb
 * @package   PEPr
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR
        . '/usr/local/www/pearweb/include');

/**
 * Get common settings.
 */
require_once 'pear-config.php';

/**
 * Obtain the system's common functions and classes.
 */
require_once 'pear-database.php';

/**
 * Get the database class.
 */
require_once 'DB.php';
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (DB::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}

$sql = 'DELETE FROM trackbacks WHERE timestamp <= '.(time() - TRACKBACK_PURGE_TIME).' AND approved = "false"';

if (PEAR::isError($res = $dbh->query($sql))) {
    die("SQL <$sql> returned error: <{$res->getMessage()}>.\n");
}

?>
