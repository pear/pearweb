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
   | Authors:       Tobias Schlitt <toby@php.net>                         |
   +----------------------------------------------------------------------+
   $Id$
*/

	require_once 'pepr/pepr.php';
	require_once 'HTML/BBCodeParser.php';
	require_once 'HTML/QuickForm.php';
	require_once 'Damblan/Karma.php';

	if (empty($id)) {
		localRedirect('pepr-proposals.php');
	}
	
	$proposal = proposal::get($dbh, $id);
	
	$proposal->getLinks($dbh);
	$proposal->getVotes($dbh);
	
	if (isset($_COOKIE['PEAR_USER']) && ($proposal->getStatus() == 'vote')) {
		$form = new HTML_QuickForm('vote', 'post', 'pepr-proposal-show.php?id='.$id);
		$form->setDefaults(array('value' => 1));
		$form->addElement('select', 'value', '', array(1 => '+1', 0 => '0', -1 => '-1'));
		$form->addElement('checkbox', 'conditional', 'conditional', '', null, 1);
		$form->addElement('textarea', 'comment', null, array('cols' => 30, 'rows' => 6));
		$form->addElement('select', 'reviews', '', $proposalReviewsMap, array('size' => count($proposalReviewsMap), 'multiple' => 'multiple'));
		$form->addElement('static', '', '', '<small>Note that you can only vote once!<br /><br />For conditional votes, please leave a comment and vote +1 (<i>e.g.</i>, "I\'m +1 if you change ...").</small>');
		$form->addElement('submit', 'submit', 'Vote');
		
		$form->addRule('value', 'A vote value is required!', 'required', null, 'client');
		$form->addRule('reviews', 'A review is required!', 'required', null, 'client');
		
		if ($form->validate()) {
			$value = $form->getElement('value');
			$value = $value->getSelected();
			$voteData['value'] = (int)$value['0'];
			$is_conditional = $form->getElement('conditional');
			$voteData['is_conditional'] = ($is_conditional->getChecked()) ? 1 : 0;
			$comment = $form->getElement('comment');
			$voteData['comment'] = addslashes($comment->getValue());
			$reviews = $form->getElement('reviews');
			$voteData['reviews'] = $reviews->getSelected();
			$voteData['user_handle'] = $_COOKIE['PEAR_USER'];
			
			if ($voteData['is_conditional'] && empty($voteData['comment'])) {
				PEAR::raiseError('You have to apply a comment if your vote is conditional!');
			}
			if ($voteData['is_conditional'] && ($voteData['value'] < 1)) {
				PEAR::raiseError("Conditional votes have to be formulated positively! Please select '+1' and change your text to a form like 'I am +1 on this if you change...'.");
			}
			
			
			$proposal->addVote($dbh, new ppVote($voteData));
			$proposal->sendActionEmail('proposal_vote', 'user', $_COOKIE['PEAR_USER']);
			$form->removeElement('submit');
			$form->addElement('static', '', '<strong>Your vote has been registered successfully!</strong>'); 
			$form->freeze();
			if (!DEVBOX) {
				localredirect('pepr-proposal-show.php?id='.$proposal->id);
			}
		}
		
	}
	
	if (isset($_COOKIE['PEAR_USER']) && ($proposal->getStatus() == 'proposal')) {
		$form = new HTML_QuickForm('comment', 'post', 'pepr-proposal-show.php?id='.$id);
		$form->addElement('textarea', 'comment', null, array('cols' => 70, 'rows' => 6));
		$form->addElement('static', '', '', '<small>Your comment will also be sent to the <strong>pear-dev</strong> mailing list.<br />
		      <strong>Please do not respond to other developers comments</strong>.<br />
		      The author himself is responsible to reflect comments in an acceptable way.</small>');
		$form->addElement('submit', 'submit', 'Add New Comment');
		
		$form->addRule('comment', 'A comment is required!', 'required', null, 'client');
		
		if ($form->validate()) {
			$values = $form->exportValues();
			$proposal->sendActionEmail('proposal_comment', 'user', $_COOKIE['PEAR_USER'], $values['comment']);
			$proposal->addComment($values['comment'], 'package_proposal_comments');
			$form->removeElement('submit');
			$form->addElement('static', '', '<strong>Your comment has been sent successfully!</strong>'); 
			$form->freeze();
			localredirect('pepr-proposal-show.php?id='.$proposal->id.'&comment=1');			
		}
	}
	
	if (!empty($proposal->status)) {
		$proposal->getVotes($dbh);
	}
	
	$proposalVotesSum = ppVote::getSum($dbh, $proposal->id);
	
	if (empty($_COOKIE['PEAR_USER'])) {
		$proposalEditRow = '<div align="right"><small>[You are the author? Login to edit!]</small></div>';
	} else if ($proposal->mayEdit($_COOKIE['PEAR_USER'])) {
		$proposalEditRow = '<div align="right">'.make_link('pepr-proposal-edit.php?id='.$proposal->id, make_image('edit.gif', 'Edit')).' '.make_link('pepr-proposal-delete.php?id='.$proposal->id, make_image('delete.gif', 'Delete package')).'</div>';
	}
	
	$proposer = user::info($proposal->user_handle);
	
	response_header('PEPr :: Proposal details');
	
	if (isset($form)) {
		echo $form->getValidationScript();
	}
	
	echo '<h1>Proposal for '.$proposal->pkg_name.' ('.$proposal->getStatus(true).')</h1>';
	
	$bb = new BorderBox('Proposal details', '90%', '', 2, true);
	
	
	$bb->horizHeadRow('Package name:', $proposal->pkg_name);
	$bb->horizHeadRow('Package category:', $proposal->pkg_category);
	$bb->horizHeadRow('Package License:', $proposal->pkg_license);
	if (!empty($proposal->pkg_deps)) {
		$bb->horizHeadRow('Dependencies:', nl2br($proposal->pkg_deps));
	}
	$bb->horizHeadRow('Proposer:', user_link($proposal->user_handle));
	$bbparser = new HTML_BBCodeParser(array('filters' => 'Basic,Images,Links,Lists,Extended'));
	$bb->fullRow($bbparser->qparse(nl2br(htmlentities($proposal->pkg_describtion))));
	
	$changelog = @ppComment::getAll($proposal->id, 'package_proposal_changelog');
	$changeLogRow = "";
	foreach ($changelog as $comment) {
		if (!isset($userinfos[$comment->user_handle])) {
			$userinfo[$comment->user_handle] = user::info($comment->user_handle);
		}
		$changeLogRow .= '<strong>'.$userinfo[$comment->user_handle]['name'];
		$changeLogRow .= ' ['. date('Y-m-d, H:i', $comment->timestamp) .']</strong><br />';
		$changeLogRow .= $bbparser->qparse(nl2br($comment->comment)).'<br /><br />';
	}
	
	if (!empty($changeLogRow)) {
		$bb->horizHeadRow('Changelog:', $changeLogRow);
	}
	
	if (!empty($proposalEditRow)) {
		$bb->fullRow($proposalEditRow);
	}
	
	$bb->end();
	
	echo spacer(1, 20);
	
	$bb = new BorderBox('Proposal links:', '90%', '', 2, true);
	
	$lastLinkType = false;
	foreach ($proposal->links as $link) {
		if ($link->type != $lastLinkType) {
			$bb->horizHeadRow($link->getType(true), make_link($link->url, shorten_string($link->url)));
			$lastLinkType = $link->getType();
		} else {
			$bb->horizHeadRow("", make_link($link->url, chunk_split($link->url)));
		}
	}
	
	if (!empty($proposalEditRow)) {
		$bb->fullRow($proposalEditRow);
	}
	
	$bb->end();
	
	echo spacer(1, 20);
	
	$bb = new BorderBox('Timeline', '90%', '', 2, true);
	
	$bb->horizHeadRow('First draft:', date('d.m.Y',$proposal->draft_date));
	if ($proposal->proposal_date != 0) {
		$bb->horizHeadRow('Proposal:', date('d.m.Y',$proposal->proposal_date));
	}
	if ($proposal->vote_date != 0) {
		$bb->horizHeadRow('Call for votes:', date('d.m.Y',$proposal->vote_date));
	}
	
	$bb->end();
	
	if ($proposal->getStatus() == 'proposal') {
		if (isset($_COOKIE['PEAR_USER'])) {
			$formArray = $form->toArray();
			echo "<form ".$formArray['attributes'].">";
			$bb = new BorderBox('Comment on this proposal', '90%', '', 2, true);
			$bb->horizHeadRow('Comment:', $formArray['elements'][0]['html']);
			$bb->horizHeadRow('', $formArray['elements'][1]['html']);
			$bb->horizHeadRow('', $formArray['elements'][2]['html']);
			if (isset($_GET['comment']) && ($_GET['comment'] == 1)) {
				$bb->horizHeadRow('', 'Comment sent successfully.');
			}
			$bb->end();
			echo '</form>';
		} else {
			$bb = new BorderBox('Comment on this proposal', '90%', '', 2, true);
			$bb->fullRow('Please login to comment or comment directly on '
                         . make_mailto_link('pear-dev@lists.php.net')
                         . '.');
			$bb->end();
		}
	}
    
	echo '<p align="center" width="90%">'.make_link('/pepr/pepr-comments-show.php?id='.$proposal->id, 'View comments on this proposal.').'</p>';
	
	echo spacer(1, 20);
	
	echo '<table style="width: 90%;">';
	
	echo '<tr><td style="width: 50%; vertical-align: top;">';
	
	$bb = new BorderBox('Votes', '100%', '', 2, true);
	if (count($proposal->votes) > 0) {
	
		$users = array();
	
		$head = true;
		foreach ($proposal->votes as $vote) {
			if (!isset($users[$vote->user_handle])) {
				$users[$vote->user_handle] =& user::info($vote->user_handle);
			}
			$vote->value = ($vote->value > 0) ? '+'.$vote->value : $vote->value;
			$vote_line = '<strong>'.make_link('pepr-vote-show.php?id='.$proposal->id.'&amp;handle='.$vote->user_handle, $vote->value.'</strong>');
			if ($vote->is_conditional || !empty($vote->comment)) {
				$vote_line .= "*";
			}
			$vote_line .= ' ('.make_link('../account-info.php?handle='.$vote->user_handle, $users[$vote->user_handle]['name']).')';
			$vote_line .= ', '.date('Y-m-d', $vote->timestamp);
			
			$bb->horizHeadRow("", $vote_line);
		
		}
	
		$bb->horizHeadRow('Sum:', '<strong>'.$proposalVotesSum['all'].'</strong> <small>('.$proposalVotesSum['conditional'].' conditional)</small>');
	} else {
		$bb->fullRow('No votes yet.');
	}

	$bb->end();
	
	echo '</td><td style="width=: 50%; vertical-align: top";>';
	
	if (($proposal->status == 'vote')) {
	
		$bb = new BorderBox('Vote on this proposal', '100%', '', 2, true);
		
		$karma = new Damblan_Karma($dbh);
		if (isset($_COOKIE['PEAR_USER']) && $karma->has($_COOKIE['PEAR_USER'],'pear.dev')) {
			if (!ppVote::hasVoted($dbh, $_COOKIE['PEAR_USER'], $proposal->id) && !$proposal->isOwner($_COOKIE['PEAR_USER'])) {
				$formArray = $form->toArray();
				echo "<form ".$formArray['attributes'].">";
				
				$bb->horizHeadRow('Vote', $formArray['elements'][0]['html']." ".$formArray['elements'][0]['label']);
				$bb->horizHeadRow('', $formArray['elements'][1]['html']." ".$formArray['elements'][1]['label']);
				$bb->horizHeadRow('Comment', $formArray['elements'][2]['html']);
				$bb->horizHeadRow('Reviews', $formArray['elements'][3]['html'].$formArray['elements'][3]['label']);
				$bb->horizHeadRow('', $formArray['elements'][4]['html']);
				$bb->horizHeadRow('', $formArray['elements'][5]['html']);
				echo "</form>";
			} else if ($proposal->isOwner($_COOKIE['PEAR_USER'])) {
				$bb->fullRow('You can not vote.');
			} else {
				$bb->fullRow('You voted.');
			}
			
		} else {
			$bb->fullRow('Only logged in, full featured PEAR developers may vote.');
		}
		
		$bb->end();
	} else if (($proposal->status == 'finished')) {
		$bb = new BorderBox('Vote on this proposal', '100%', '', 2, true);
		$bb->fullRow('This proposal is finished.');
		$bb->horizHeadRow("Sum votes:", $proposalVotesSum['all']." (".$proposalVotesSum['conditional'].")");
		if ($proposalVotesSum['all'] >= 5) {
			$bb->fullRow('This proposal has been accepted.');
		} else {
			$bb->fullRow('This proposal has not been accepted.');
		}
		$bb->end();
	} else {
		$bb = new BorderBox('Vote on this proposal', '100%', '', 2, true);
		$bb->fullRow("Voting is only enabled during 'Call for votes phase'.");
		$bb->end();
	}
	
	echo '</td></tr>';
	echo '</table>';
	
	response_footer();

?>
