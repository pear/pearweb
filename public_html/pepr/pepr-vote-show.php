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
   | Authors:       Tobias Schlitt <toby@php.net>                         |
   +----------------------------------------------------------------------+
   $Id$
*/
	require_once 'pepr/pepr.php';

	if (empty($id) || empty($handle)) {
		PEAR::raiseError("No vote selected.");
	}

	$proposal = proposal::get($dbh, $id);
	
	$vote = ppVote::get($dbh, $proposal->id, $handle);
	
	$conditional = ($vote->is_conditional) ? "This vote is conditional." : "";
	
	response_header("PEPr :: Vote details");
	
	$bb = new BorderBox("Proposal Vote details", "100%", "", 2, true);
	
	$bb->horizHeadRow('Package:', $proposal->pkg_category.'::'.$proposal->pkg_name);
	$bb->horizHeadRow('Voter:', user_link($handle));
	$bb->horizHeadRow('Vote:', $vote->value);
	$bb->horizHeadRow('', $conditional);
	$label = "Reviews:";
	foreach ($vote->getReviews(true) as $review) {
		$bb->horizHeadRow($label, $review);
		$label = "";
	}
	
	$bb->horizHeadRow('Comment:', nl2br(stripslashes($vote->comment)));
		
	$bb->end();
	
	echo make_link('/pepr/pepr-proposal-show.php?id='.$id, 'Back to proposal');
	
	response_footer();
?>
