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
	require_once 'HTML/BBCodeParser.php';
	require_once 'HTML/QuickForm.php';

	auth_require('pear.pepr');
	
	$karma = new Damblan_Karma($dbh);
	
	if (!empty($id)) {
		$proposal = proposal::get($dbh, $id);
		if (DB::isError($proposal)) {
			PEAR::raiseError("Package proposal not found.");
		}
		
		if (!$proposal->mayEdit($_COOKIE['PEAR_USER']) && empty($next_stage)) {
			PEAR::raiseError("Proposal can not be edited.");
		}
		$proposal->getLinks($dbh);	
	}
	
	$form = new HTML_QuickForm('proposal_edit', 'post', 'pepr-proposal-edit.php?id='.@$id);
	
	$categories = category::listAll();
	$mapCategories['RFC'] = "RFC (No package category!)";
	foreach ($categories as $categorie) {
		$mapCategories[$categorie['name']] = $categorie['name'];
	}

	$form->addElement('select', 'pkg_category', 'Category:', $mapCategories);
	$categoryNewElements[] =& HTML_QuickForm::createElement('checkbox', 'pkg_category_new_do', '');
	$categoryNewElements[] =& HTML_QuickForm::createElement('text', 'pkg_category_new_text', '');
	$categoryNew = $form->addGroup($categoryNewElements, 'pkg_category_new', 'New category:', '<br />');

	$form->addElement('text', 'pkg_name', 'Package name:');
	$form->addElement('text', 'pkg_license', 'License:');
	
	$form->addElement('textarea', 'pkg_describtion', 'Package description:', array('rows' => 6, 'cols' => '40'));
	$form->addElement('link', 'help_bbcode', '', 'pepr-bbcode-help.php', 'You can use BBCode inside your description');
	
	$form->addElement('textarea', 'pkg_deps', 'Package dependencies <small>(list)</small>:', array('rows' => 6, 'cols' => '40'));
	$form->addElement('static', '', '', 'List seperated by linefeeds.');
	
	$max = (isset($proposal->links) && (count($proposal->links) > 2)) ? (count($proposal->links) + 1) : 3;
	for ($i = 0; $i < $max; $i++) {
		unset($link);
		$link[0] = $form->createElement('select', 'type', "", $proposalTypeMap);
		$link[1] = $form->createElement('text', 'url', "");
		$label = ($i == 0) ? "Links:": "";			
		$links[$i] =& $form->addGroup($link, "link[$i]", $label, ' ');
	}
	
	$form->addElement('static', '', '', '<small>To add more links, fill out all link forms and hit save. To delete a link leave the URL field blank.</small>');
	
	if (isset($proposal) && ($proposal->getStatus() != "draft")) {
			$form->addElement('checkbox', 'action_email', "Send update email to pear-dev");
			$form->addElement('textarea', 'action_comment', "Update comment:", array('cols' => '40'));
	}
	
	
	$form->addElement('submit', 'submit', 'Save');
	
	
	if (isset($proposal)) {
		$defaults = array('pkg_name' 	=> $proposal->pkg_name,
		                  'pkg_license'	=> $proposal->pkg_license,
						  'pkg_describtion' => $proposal->pkg_describtion,
					      'pkg_deps' 	=> $proposal->pkg_deps);
		if (isset($mapCategories[$proposal->pkg_category])) {
			$defaults['pkg_category'] = $proposal->pkg_category;
		} else {
			$defaults['pkg_category_new']['pkg_category_new_text'] = $proposal->pkg_category;
			$defaults['pkg_category_new']['pkg_category_new_do'] = true;
		}
		if ((count($proposal->links) > 0)) {
			$i = 0;
			foreach ($proposal->links as $proposalLink) {
				$defaults['link'][$i]['type'] = $proposalLink->type;
				$defaults['link'][$i]['url'] = $proposalLink->url;
				$i++;
			}
		}
		
		$form->setDefaults($defaults);
		
		switch ($proposal->status) {
			case 'draft':
			    $next_stage_text = "Change status to 'Proposal'";
				break;

			case 'proposal':
			    $next_stage_text = "Change status to 'Call for votes'";
				break;

			case 'vote': 
            default:
                if ($karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin") && ($proposal->user_handle != $_COOKIE['PEAR_USER'])) {
                    $next_stage_text = "Extend vote time";
				} else {
                    $next_stage_text = "";
				}
                break;
		}
				
		$timeline = $proposal->checkTimeLine();
		if (($timeline === true) || ($karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin") && ($proposal->user_handle != $_COOKIE['PEAR_USER']))) {
			$form->addElement('checkbox', 'next_stage', $next_stage_text);
		} else {
			$form->addElement('static', 'next_stage', '', 'You can set "'.@$next_stage_text.'" on '.date("Y-m-d, H:i ", $timeline).'GMT '.date('O'));
		}
	}
	
	
	
	$form->applyFilter('pkg_name', 'trim');
	$form->applyFilter('pkg_describtion', 'trim');
	$form->applyFilter('pkg_deps', 'trim');
	$form->applyFilter('__ALL__', 'addslashes');
	
	$form->addRule('pkg_category', 'You have to select a package category!', 'required', '', 'client');
	$form->addRule('pkg_name', 'You have to select a package name!', 'required', '', 'client');
	$form->addRule('pkg_license', 'you have to specify the license of your package!', 'required', '', 'client');
	$form->addRule('pkg_describtion', 'You have to enter a package description!', 'required', '', 'client');
	$form->addRule('link[0]', '2 links are required as minimum!', 'required', '', 'client');
	$form->addRule('link[1]', '2 links are required as minimum!', 'required', '', 'client');
	
	
	if ($form->validate()) {
		$values = $form->exportValues();
		
		if (isset($values['pkg_category_new']['pkg_category_new_do'])) {
			$values['pkg_category'] = $values['pkg_category_new']['pkg_category_new_text'];
		} 
		if (isset($values['next_stage'])) {
			switch ($proposal->status) {
				case 'draft':	
				    if ($proposal->checkTimeLine()) {
					   $values['proposal_date'] = time();
					   $proposal->status = 'proposal';
					   $proposal->sendActionEmail('change_status_proposal', 'mixed', $_COOKIE['PEAR_USER']);
				    } else {
					   PEAR::raiseError("You can not change the status now.");
				    }
				break;
				
				case 'proposal':	
				    if ($proposal->checkTimeLine()) {
					   $values['vote_date'] = time();
					   $proposal->status = 'vote';
					   $proposal->sendActionEmail('change_status_vote', 'mixed', $_COOKIE['PEAR_USER']);
				    } else {
					   PEAR::raiseError("You can not change the status now.");
    				}
				break;
				
				default:
				    if ($proposal->mayEdit($_COOKIE['PEAR_USER'])) {
					   $values['longened_date'] = time();
					   $proposal->status = 'vote';
					   $proposal->sendActionEmail('longened_timeline_admin', 'mixed', $_COOKIE['PEAR_USER']);
				    }
				break;
			}
		} else {
			if (isset($proposal) && $proposal->status != 'draft') {
				if ((isset($values['action_email']) && $values['action_email']) || ($karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin") && ($proposal->user_handle != $_COOKIE['PEAR_USER']))) {
					if (empty($values['action_comment'])) {
						PEAR::raiseError("You have to apply a comment when you send update emails! For administrative actions, always emails are send.");
					}
					$proposal->addComment($values['action_comment']);
					$proposal->sendActionEmail('edit_proposal', 'mixed', $_COOKIE['PEAR_USER'], $values['action_comment']);	
				}
			}
		} 
		
		$linksData = $values['link'];
		
		if (isset($proposal)) {
			$proposal->fromArray($values);
		} else {
			$proposal = new proposal($values);
			$proposal->user_handle = $_COOKIE['PEAR_USER'];
		}
		
		
		
		unset($proposal->links);
		for ($i = 0; $i < count($linksData); $i += 2) {
				$linkData['type'] = $linksData[$i]['type'];
				$linkData['url'] = $linksData[($i + 1)]['url'];
				$proposal->addLink($dbh, new ppLink($linkData));
		}

		$proposal->store($dbh);

		if (isset($values['next_stage'])) {
			$nextStage = 1;	
		}
		
	    localRedirect("/pepr/pepr-proposal-edit.php?id={$proposal->id}&saved=1&next_stage=".@$nextStage);
	}

	if (!empty($next_stage)) {
		$form = new HTML_QuickForm('no-form');
		switch ($proposal->status) {
				case 'proposal':
					$bbox['header'] = "Proposal";
					$bbox['text'] = "Your package has been proposed on pear-dev. All further changes will result in an update Email.";
					$form->addElement('link', 'link_package_edit', '', 'pepr-proposal-edit.php?id='.$id, 'Edit the proposal');
				break;
			
				case 'vote':
					$bbox['header'] = "Call for votes";
					$bbox['text'] = "For your package has been called for votes on pear-dev. No further changes are allowed.";	
					if ($proposal->mayEdit($_COOKIE['PEAR_USER'])) {
						$form->addElement('link', 'link_package_edit', '', 'pepr-proposal-edit.php?id='.$id, 'Edit the proposal');
					}
				break;
		}
		if ($karma->has($_COOKIE['PEAR_USER'], "pear.pepr.admin")) {
			$bbox['header'] = "Changes saved";
			$bbox['text'] = "The changes you did got recorded, neccessary action mails have been sent.";				
		}
	}
	
	if (!empty($id)) {
	    $form->addElement('link', 'link_package_view', '', 'pepr-proposal-show.php?id='.@$id, 'View the proposal');
	}
	
	response_header("PEPr :: Proposal editor");
	
	if (isset($proposal)) {
		echo "<h1>Proposal for ".$proposal->pkg_name.", Status: <i>".$proposal->getStatus(true)."</i></h1>";
	} else {
		echo "<h1>New package Proposal</h1>";
	}
	
	if (isset($saved) && $saved) {
		echo "<h3>Changes saved successfully!</h3>";
	}
	
	if (isset($bbox)) {
		$bb = new BorderBox($bbox['header'], "40%", null, true);
		$bb->fullRow($bbox['text']);
		$bb->end();
	}
	
	$form->display();
	
	response_footer();

?>
