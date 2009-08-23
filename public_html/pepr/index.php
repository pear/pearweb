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
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain the common functions and classes.
 */
require_once 'pepr/pepr.php';

/**
 * Helper to display the pepr proposal information better.
 */
function render_proposal($dbh, $proposal, $user, $already_voted) {
    ob_start();
    if ($already_voted) {
        echo '(Already voted) ';
    }

    echo make_link('pepr-proposal-show.php?id=' . $proposal->id,
               htmlspecialchars($proposal->pkg_category) . ' :: '
               . htmlspecialchars($proposal->pkg_name));
    echo ' by ';
    echo make_link('/user/' . htmlspecialchars($proposal->user_handle),
               htmlspecialchars($user['name']));

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
    return ob_get_clean();
}

/**
 * Has a given user voted on a package?
 */
function has_user_voted($user, $proposal, $dbh) {
    if ($proposal->getStatus(true) == "Called for Votes") {
        $proposal->getVotes($dbh);

        if (in_array($user->handle, array_keys($proposal->votes))) {
            return true;
        }
    }
    return false;
}

function render_status($status, $status_description, $proposals, $dbh, $users) {
    global $auth_user; // :(

    ob_start();
    echo "<div style=\"float: right\"><a href='/feeds/pepr_".$status.".rss'><img src=\"/gifs/feed.png\" width=\"16\" height=\"16\" alt=\"Aggregate this\" border=\"0\" /></a></div>";
    echo '<h2 id="' . $status . '">';
    echo '&raquo; ' . htmlspecialchars($status_description);
    echo "</h2>\n";


    if (empty($proposals)) {
        echo '<p>There are no ' . $status_description . ' proposals to render</p>';
    } else {
        echo "<ul>\n";

        foreach ($proposals as $proposal) {
            $already_voted = false;
            if (isset($auth_user)) {
                $already_voted = has_user_voted($auth_user, $proposal, $dbh);
            }

            echo "  <li>";
            echo render_proposal($dbh, $proposal, $users[$proposal->user_handle], $already_voted);
            echo "</li>\n";
        }

        echo "</ul>\n";
    }
    echo "\n\n";
    return ob_get_clean();
}



if (isset($_GET['filter']) && isset($proposalStatiMap[$_GET['filter']])) {
    $selectStatus = $_GET['filter'];
} else {
    $selectStatus = '';
}

if ($selectStatus != '') {
    $order = ' pkg_category ASC, pkg_name ASC';
}

if (isset($_GET['search'])) {
    $searchString = trim($_GET['search']);
    $searchString = preg_replace('/;/', '', $searchString);
    $proposals = proposal::search($searchString);
    $searchPostfix = '_search_'.urlencode($searchString);
} else {
    $proposals =& proposal::getAll($dbh, @$selectStatus, null, @$order);
    $searchPostfix = '';
}

response_header('PEPr :: Package Proposals');

echo '<h1>Package Proposals</h1>' . "\n";
if (empty($selectStatus)) {
    echo "<p>";
    echo "PEPr is PEAR's system for managing the process of submitting ";
    echo "new packages. If you would like to submit your own package, ";
    echo "please have a look at the <a href=\"/manual/en/developers-newmaint.php\">New Maintainers' Guide</a>.";
    echo "</p>";
    echo "<p>";
    echo "<a href=\"/feeds/pepr$searchPostfix.rss\"><img src=\"/gifs/feed.png\" width=\"16\" height=\"16\" alt=\"Aggregate this\" border=\"0\" /></a>";
    echo "</p>";
}

display_overview_nav();

$users = array();
foreach ($proposals as $proposal) {
    include_once 'pear-database-user.php';
    if (!isset($users[$proposal->user_handle])) {
        $users[$proposal->user_handle] = user::info($proposal->user_handle);
    }
}
$statuses = $proposalStatiMap;

$proposals_by_status = array();
foreach ($statuses as $status => $status_description) {
    $proposals_by_status[$status] = array();
}

foreach ($proposals as $proposal) {
    //Only show 10 finished proposals unless we're specifically looking at everything
    if ($selectStatus != 'finished' && $proposal->getStatus() == 'finished'
        && count($proposals_by_status[$proposal->getStatus()]) >= 10) {
        continue;
    }

    $proposals_by_status[$proposal->getStatus()][] = $proposal;
}



foreach ($statuses as $status => $status_description) {
    // We've chosen all or a specific category
    if (empty($selectStatus) || $selectStatus == $status) {
        echo render_status($status, $status_description, $proposals_by_status[$status], $dbh, $users);
    }
}

if (empty($selectStatus)) {
    echo make_link('/pepr/?filter=finished', 'All finished proposals');
}




response_footer();
