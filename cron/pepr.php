<?PHP
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
   | Authors: Tobias Schlitt <toby@php.net>                               |
   +----------------------------------------------------------------------+
   $Id$
*/
		
ini_set("include_path", ini_get("include_path").":../include");

require_once('DB.php');
	
/**
 * DSN for pear packages database
 */
$dsn = "mysql://pear:pear@localhost/pear";
$dbh = DB::connect($dsn);

if (DB::isError($db = DB::connect($dsn))) {
    die ("Failed to connect: $dsn\n");
}

require_once dirname(__FILE__) . '/../include/pepr/pepr.php';

// This checks if a proposal should automatically be finished
	
$proposals = proposal::getAll($dbh, "vote");

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
