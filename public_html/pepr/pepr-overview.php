<?php

/**
 * Displays a list of all proposals.
 *
 * The <var>$proposalStatiMap</var> array is defined in
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
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain the common functions and classes.
 */
require_once 'pepr/pepr.php';

$form =& new HTML_QuickForm('filter_proposals', 'get');

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

response_header('PEPr :: Package Proposals');

echo '<h1>Package Proposals</h1>' . "\n";

$form->display();

echo "<ul>";

$last_status = false;

foreach ($proposals as $proposal) {
    if ($proposal->getStatus() != $last_status) {
        echo "</ul>";
        echo '<h2 name="' . $proposal->getStatus() . '" id="';
        echo $proposal->getStatus() . '">';
        echo '&raquo; ' . htmlspecialchars($proposal->getStatus(true));
        echo "</h2>\n";
        echo "<ul>";
        $last_status = $proposal->getStatus();
    }
    if (!isset($users[$proposal->user_handle])) {
        $users[$proposal->user_handle] = user::info($proposal->user_handle);
    }
    echo "<li>";
    print_link('pepr-proposal-show.php?id=' . $proposal->id,
               htmlspecialchars($proposal->pkg_category) . ' :: '
               . htmlspecialchars($proposal->pkg_name));
    echo ' by ';
    print_link('/user/' . htmlspecialchars($proposal->user_handle),
               htmlspecialchars($users[$proposal->user_handle]['name']));
    switch ($proposal->getStatus()) {
        case 'proposal':
            echo ' &nbsp;(<a href="pepr-comments-show.php?id=' . $proposal->id;
            echo '">Comments</a>)';
            break;
        case 'vote':
        case 'finished':
            $voteSums = ppVote::getSum($dbh, $proposal->id);
            echo ' &nbsp;(<a href="pepr-votes-show.php?id=' . $proposal->id;
            echo '">Vote</a> sum: <strong>' . $voteSums['all'] . '</strong>';
            echo '<small>, ' . $voteSums['conditional'];
            echo ' conditional</small>)';
    }
    echo "</li>\n";
}

echo "</ul>";

response_footer();

?>
