<?php

/**
 * Automated tasks for the package proposal system (PEPr).
 *
 * 1) Checks if a proposal should automatically be finished.
 *
 * NOTE: Proposal constants are defined in pearweb/include/pear-config.php.
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   PEPr
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright Copyright (c) 1997-2004 The PHP Group
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
$dbh =& DB::connect(PEAR_DATABASE_DSN);
if (DB::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}


/**
 * Obtain PEPr's common functions and classes.
 */
require_once 'pepr/pepr.php';
$proposals =& proposal::getAll($dbh, 'vote');


// This checks if a proposal should automatically be finished

foreach ($proposals AS $id => $proposal) {
    if ($proposal->getStatus() == "vote") {
        $lastVoteDate = ($proposal->longened_date > 0) ? $proposal->longened_date : $proposal->vote_date;
        if (($lastVoteDate + PROPOSAL_STATUS_VOTE_TIMELINE) < time()) {
            if (ppVote::getCount($dbh, $proposal->id) > 4) {
                $proposals[$id]->status = 'finished';
                $proposal->sendActionEmail('change_status_finished', 'pearweb', null);
            } else {
                if ($proposal->longened_date > 0) {
                    $proposals[$id]->status = 'finished';
                    $proposal->sendActionEmail('change_status_finished', 'pearweb', null);
                } else {
                    $proposals[$id]->longened_date = time();
                    $proposal->sendActionEmail('longened_timeline_sys', 'pearweb', null);
                }
            }
            $proposals[$id]->getLinks($dbh);
            $proposals[$id]->store($dbh);
        }
    }
}

?>
