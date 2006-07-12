<?php

/**
 * Interface for inputing/editing a proposal.
 *
 * The <var>$proposalTypeMap</var> array is defined in
 * pearweb/include/pepr/pepr.php.
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
 * @author    Daniel Convissor <danielc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

auth_require('pear.pepr');

/**
 * Obtain the common functions and classes.
 */
include_once 'pepr/pepr.php';


$karma =& new Damblan_Karma($dbh);

ob_start();

if ($proposal =& proposal::get($dbh, @$_GET['id'])) {
    response_header('PEPr :: Editor :: '
                    . htmlspecialchars($proposal->pkg_name));
    echo '<h1>Proposal Editor for &quot;' . htmlspecialchars($proposal->pkg_name);
    echo '&quot; (' . $proposal->getStatus(true) . ")</h1>\n";

    if (!$proposal->mayEdit($auth_user->handle) && empty($_GET['next_stage'])) {
        report_error('You are not allowed to edit this proposal,'
                     . ' probably due to it having reached the "'
                     . $proposal->getStatus(true) . '" phase.'
                     . ' If this MUST be edited, contact someone ELSE'
                     . ' who has pear.pepr.admin karma.');
        response_footer();
        exit;
    }

    if ($proposal->compareStatus('>', 'proposal') &&
        $karma->has($auth_user->handle, 'pear.pepr.admin') &&
        empty($_GET['next_stage']))
    {
        report_error('This proposal has reached the "'
                     . $proposal->getStatus(true) . '" phase.'
                     . ' Are you SURE you want to edit it?',
                     'warnings', 'WARNING:');
    }

    $proposal->getLinks($dbh);
    $id = $proposal->id;
} else {
    response_header('PEPr :: Editor :: New Proposal');
    echo '<h1>New Package Proposal</h1>' . "\n";
    $id = 0;
    $proposal = null;
}

include_once 'HTML/QuickForm.php';
$form =& new HTML_QuickForm('proposal_edit', 'post',
                            'pepr-proposal-edit.php?id=' . $id);

$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('
 <tr>
  <th class="form-label_left">
   <!-- BEGIN required --><span style="color: #ff0000">*</span><!-- END required -->
   {label}
  </th>
  <td class="form-input">
   <!-- BEGIN error --><span style="color: #ff0000">{error}</span><br /><!-- END error -->
   {element}
  </td>
 </tr>
');

$categories = category::listAll();
$mapCategories['RFC'] = 'RFC (No package category!)';
foreach ($categories as $categorie) {
    $mapCategories[$categorie['name']] = $categorie['name'];
}

$form->addElement('select', 'pkg_category', '<label for="pkg_category" accesskey="o">Categ<span class="accesskey">o</span>ry:</label>', $mapCategories, 'id="pkg_category"');
$categoryNewElements[] =& HTML_QuickForm::createElement('checkbox', 'pkg_category_new_do', '');
$categoryNewElements[] =& HTML_QuickForm::createElement('text', 'pkg_category_new_text', '');
$categoryNew = $form->addGroup($categoryNewElements, 'pkg_category_new', 'New category:', '<br />');

$form->addElement('text', 'pkg_name', 'Package name:');
$form->addElement('text', 'pkg_license', 'License:');

$form->addElement('textarea', 'pkg_describtion', 'Package description:', array('rows' => 20, 'cols' => '80'));
$form->addElement('select', 'markup', 'Markup', array('bbcode' => 'BBCode', 'wiki' => 'Wiki'));

$helpLinks[] =& HTML_QuickForm::createElement('link', 'help_bbcode', '_blank', 'pepr-bbcode-help.php', 'You can use BBCode inside your description', array('target' => '_blank'));
$helpLinks[] =& HTML_QuickForm::createElement('link', 'help_wiki', '_blank', 'http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=WikiRules', 'or Wiki markup', array('target' => '_blank'));
$form->addGroup($helpLinks, 'markup_help', '', ' ');

$form->addElement('textarea', 'pkg_deps', 'Package dependencies <small>(list)</small>:', array('rows' => 6, 'cols' => '80'));
$form->addElement('static', '', '', 'List seperated by linefeeds.');

if (null != $proposal && (false === strpos($proposal->pkg_category, 'RFC'))) {
    $form->addElement('static', '', '', '<small>' . (('draft' == $proposal->status)? 'The first two links are required for a change of status.<br />': '') . 'The first link must be of type &lt;PEAR package file&gt;.</small>');
}

$max = (isset($proposal->links) && (count($proposal->links) > 2)) ? (count($proposal->links) + 1) : 3;

for ($i = 0; $i < $max; $i++) {
    unset($link);
    $link[0] = $form->createElement('select', 'type', '', $proposalTypeMap);
    $link[1] = $form->createElement('text', 'url', '');
    $label = ($i == 0) ? 'Links:': '';
    $links[$i] =& $form->addGroup($link, "link[$i]", $label, ' ');
}

$form->addElement('static', '', '', '<small>To add more links, fill out all link forms and hit save. To delete a link leave the URL field blank.</small>');

if ($proposal != null && ($proposal->getStatus() != 'draft')) {
    $form->addElement('static', '', '', '<strong>If you add any text to the Changelog comment textarea,<br />then a mail will be sent to pear-dev about this update.</strong>');
    $form->addElement('textarea', 'action_comment', 'Changelog comment:', array('cols' => 80, 'rows' => 10));
}

$form->addElement('submit', 'submit', 'Save');

if ($proposal != null) {
    $defaults = array('pkg_name'    => $proposal->pkg_name,
                      'pkg_license' => $proposal->pkg_license,
                      'pkg_describtion' => $proposal->pkg_describtion,
                      'pkg_deps'    => $proposal->pkg_deps,
                      'markup'      => $proposal->markup);
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
            if ($karma->has($auth_user->handle, 'pear.pepr.admin') && ($proposal->user_handle != $auth_user->handle)) {
                $next_stage_text = 'Extend vote time';
            } else {
                $next_stage_text = '';
            }
            break;
    }

    $timeline = $proposal->checkTimeLine();
    if (($timeline === true) || ($karma->has($auth_user->handle, 'pear.pepr.admin') && ($proposal->user_handle != $auth_user->handle))) {
        $form->addElement('checkbox', 'next_stage', $next_stage_text);
    } else {
        $form->addElement('static', 'next_stage', '',
                          'You can set &quot;' . @$next_stage_text
                          . '&quot; after '
                          . make_utc_date($timeline) . '.');
    }
}


$form->applyFilter('pkg_name', 'trim');
$form->applyFilter('pkg_describtion', 'trim');
$form->applyFilter('pkg_deps', 'trim');

$form->addRule('pkg_category', 'You have to select a package category!', 'required', '', 'client');
$form->addRule('pkg_name', 'You have to select a package name!', 'required', '', 'client');
$form->addRule('pkg_license', 'you have to specify the license of your package!', 'required', '', 'client');
$form->addRule('pkg_describtion', 'You have to enter a package description!', 'required', '', 'client');

function checkLinkTypeAndUrl($link, $linkCount) {
    list($key, $type) = each($link);
    list($key, $url) = each($link);

    if (!$GLOBALS['isPeprRfc']) {
        if (0 == $linkCount && ('pkg_file' != $type)) {
            return false;
        }

        if ($linkCount < 2 && ('' == $url)) {
            return false;
        }
    }

    if ('' != $url) {
        return preg_match('@^http\://@i', $url) ? true: false;
    }

    return '' == $url? true: false;
}

$form->registerRule('checkLinkTypeAndUrl', 'callback', 'checkLinkTypeAndUrl');

$peprNextStage = isset($_POST['submit'])? $form->getSubmitValue('next_stage'): false;

if (null !== $proposal && ($peprNextStage || 'draft' !== $proposal->status)) {
    if(!$isPeprRfc = (false !== strpos($proposal->pkg_category, 'RFC'))) {
        $form->addRule('link[0]', '', 'required');
        $form->addRule('link[1]', '', 'required');
    }

    $linksCount = count($proposal->links);
    $peprLinksCount = ($linksCount > 2)? $linksCount + 1: 3;

    for ($i = 0; $i < $peprLinksCount; $i++) {
        $form->addRule('link[' . $i . ']', 'The' . (($isPeprRfc || $i > 1)? ' ': ' required ') . 'link type and the URL do not match!', 'checkLinkTypeAndUrl', $i);
    }
}

if (isset($_POST['submit'])) {
    if ($form->validate()) {
        $values = $form->exportValues();

        if (isset($values['pkg_category_new']['pkg_category_new_do'])) {
            $values['pkg_category'] = $values['pkg_category_new']['pkg_category_new_text'];
        }

        $actionComment = !empty($values['action_comment']) ? true : false;

        if (isset($values['next_stage'])) {
            switch ($proposal->status) {
                case 'draft':
                    if ($proposal->checkTimeLine()) {
                       $values['proposal_date'] = time();
                       $proposal->status = 'proposal';
                       $proposal->sendActionEmail('change_status_proposal', 'mixed', $auth_user->handle);
                    } else {
                       PEAR::raiseError('You can not change the status now.');
                    }
                    break;

                case 'proposal':
                    if ($proposal->checkTimeLine()) {
                       $values['vote_date'] = time();
                       $proposal->status = 'vote';
                       !$actionComment or $proposal->addComment($values['action_comment']);
                       $proposal->sendActionEmail('change_status_vote', 'mixed', $auth_user->handle, $actionComment ? $values['action_comment'] : '');
                    } else {
                       PEAR::raiseError('You can not change the status now.');
                    }
                    break;

                default:
                    if ($proposal->mayEdit($auth_user->handle)) {
                       $values['longened_date'] = time();
                       $proposal->status = 'vote';
                       $proposal->sendActionEmail('longened_timeline_admin', 'mixed', $auth_user->handle);
                    }
            }
        } else {
            if (isset($proposal) && $proposal->status != 'draft') {
                if ($actionComment || ($karma->has($auth_user->handle, "pear.pepr.admin") && ($proposal->user_handle != $auth_user->handle))) {
                    if (!$actionComment) {
                        PEAR::raiseError('A changelog comment is required.');
                    }
                    $proposal->addComment($values['action_comment']);
                    $proposal->sendActionEmail('edit_proposal', 'mixed', $auth_user->handle, $values['action_comment']);
                }
            }
        }

        $linksData = $values['link'];

        if (isset($proposal)) {
            $proposal->fromArray($values);
        } else {
            $proposal = new proposal($values);
            $proposal->user_handle = $auth_user->handle;
        }

        unset($proposal->links);
        for ($i = 0; $i < count($linksData); $i++) {
            $linkData['type'] = $linksData[$i]['type'];
            $linkData['url']  = $linksData[$i]['url'];

            if ($linksData[$i]['url']) {
                $proposal->addLink($dbh, new ppLink($linkData));
            }
        }

        $proposal->store($dbh);

        if (isset($values['next_stage'])) {
            $nextStage = 1;
        }

        ob_end_clean();
        localRedirect('/pepr/pepr-proposal-edit.php?id='
                      . $proposal->id . '&saved=1&next_stage=' . @$nextStage);
    } else {
        $pepr_form = $form->toArray();
        report_error($pepr_form['errors']);
    }
}

ob_end_flush();

if (!empty($_GET['next_stage'])) {
    $form =& new HTML_QuickForm('no-form');
    $bbox = array();
    switch ($proposal->status) {
        case 'proposal':
            $bbox[] = 'The package has been proposed on pear-dev.'
                    . ' All further changes will produce an update email.';
            break;

        case 'vote':
            $bbox[] = 'The package has been called for votes on pear-dev.'
                    . ' No further changes are allowed.';
            break;
    }
    if ($karma->has($auth_user->handle, 'pear.pepr.admin')) {
        $bbox[] = 'Your changes were recorded and necessary emails'
                . ' were sent.';
    }
    if ($bbox) {
        report_success(implode(' ', $bbox));
    }
} else {
    if (!empty($_GET['saved'])) {
        report_success('Changes saved successfully.');
    }
}

display_pepr_nav($proposal);

$form->display();

response_footer();

?>
