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
| Authors:       toby@php.net                                          |
+----------------------------------------------------------------------+
$Id$
*/

require_once 'pepr/pepr.php';
require_once 'HTML/QuickForm.php';

$form = new HTML_QuickForm('filter_proposals', 'get');

$values[''] = 'All';
$values = array_merge($values, $proposalStatiMap);


$filter = $form->addElement('select', 'filter', 'Filter', $values);
$form->addElement('submit', 'submit', 'Filter');

$filter_value = $filter->getValue();

if ($form->validate()) {

    if (trim($filter_value[0]) != "") {
        $selectStatus = $filter_value[0];
    }
}

$proposals =& proposal::getAll($dbh, @$selectStatus);

response_header('Package Proposals');

echo '<h1>Package Proposals</h1>' . "\n";

$form->display();

echo "<ul>";

$last_status = false;

foreach ($proposals as $proposal) {
    if ($proposal->getStatus() != $last_status) {
        echo "</ul>";
        echo '<h2>&raquo; ' . $proposal->getStatus(true) . "</h2>\n";
        echo "<ul>";
        $last_status = $proposal->getStatus();
    }
    if (!isset($users[$proposal->user_handle])) {
        $users[$proposal->user_handle] = user::info($proposal->user_handle);
    }
    echo "<li>";
    echo make_link('pepr-proposal-show.php?id='.$proposal->id, $proposal->pkg_category." :: ".$proposal->pkg_name);
    echo " by ";
    echo make_link('/user/'.$proposal->user_handle, $users[$proposal->user_handle]['name']);
    if (in_array($proposal->getStatus(), array('vote', 'finished'))) {
        $voteSums = ppVote::getSum($dbh, $proposal->id);
        echo " (Vote sum: <b>".$voteSums['all']."</b><small>, ".$voteSums['conditional']." conditional</small>)";
    }
    echo "</li>";
}

echo "</ul>";

response_footer();

?>
