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
	require_once 'HTML/QuickForm.php';
	
	auth_require('pear.pepr');
	
	$karma = new Damblan_Karma($dbh);
	
	if (empty($id)) {
		PEAR::raiseError("Proposal not found.");
	}
	
	if (!empty($isDeleted)) {
	    response_header("PEPr :: Delete proposal");
		echo "<p>";
		echo "<b>Proposal deleted successfully!</b>";;
		echo "</p>";
		echo "<p>";
		echo make_link("/pepr/pepr-overview.php", "Back");
		echo "</p>";
		response_footer();
		exit;
	}
	
	$proposal = proposal::get($dbh, $id);

	if (empty($proposal)) {
		PEAR::raiseError("Proposal not found.");
	}
	
	if (DB::isError($proposal)) {
		PEAR::raiseError($proposal);
	}
	
	if (($_COOKIE['PEAR_USER'] != $proposal->user_handle) && !$karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin")) {
		PEAR::raiseError("You did not create this proposal. You can not delete it.");
	}
	
	if ((($proposal->status == "vote") || ($proposal->status == "finished")) && !$karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin")) {
		PEAR::raiseError("You can not delete proposals later than status '".$mapProposalStatus['proposal']."'.");
	}
	
	$form = new HTML_QuickForm('delete-proposal', 'post', 'pepr-proposal-delete.php?id='.$id);
	
	$form->addElement('checkbox', 'delete', 'Really delete proposal for', $proposal->pkg_category."::".$proposal->pkg_name);
	$form->addElement('submit', 'submit', 'Do it');
	
	$form->addRule('delete', 'You have to check the box to delete!', 'required', '', 'client');
	
	if ($form->validate()) {
		$proposal->delete($dbh);
		$proposal->sendActionEmail('proposal_delete', 'mixed', $_COOKIE['PEAR_USER']);
		$form->removeElement('submit');
		$form->addElement('static', '', '', 'Package deleted successfully');
		$form->freeze();
		localRedirect("pepr-proposal-delete.php?id=".$proposal->id."&isDeleted=1");
	}

	
	
	response_header("PEPr :: Delete proposal");
	
	$form->display();
		
	response_footer();


?>
