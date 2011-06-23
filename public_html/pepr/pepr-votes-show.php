<?php

/**
 * Displays and accepts votes for a given proposal.
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
require_once 'HTML/QuickForm2.php';

if (!isset($_GET['id']) || !($proposal = proposal::get($dbh, $_GET['id']))) {
    response_header('PEPr :: Votes :: Invalid Request');
    echo "<h1>Proposal Votes</h1>\n";
    report_error('The requested proposal does not exist.');
    response_footer();
    exit;
}


response_header('PEPr :: Votes :: ' . htmlspecialchars($proposal->pkg_name));
echo '<h1>Proposal Votes for "' . htmlspecialchars($proposal->pkg_name) . "\"</h1>\n";

if ($auth_user && $proposal->mayVote($dbh, $auth_user->handle)) {
    $form = new HTML_QuickForm2('vote', 'post',
                                array('action' => 'pepr-votes-show.php?id=' . $proposal->id));
    $form->removeAttribute('name');

    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array('value' => 1)));

    $vote = $form->addElement('select', 'value', array('id' => 'vote_field', 'required' => 'required'));
    $vote->loadOptions(array(1 => '+1',
                            0 => '0',
                            -1 => '-1'));
    $vote->setLabel("Vote:");

    $is_conditional = $form->addElement('checkbox', 'is_conditional')->setLabel("Conditional Vote?:");

    $comment = $form->addElement('textarea', 'comment',
                      array('cols' => 70,
                            'rows' => 20, 'placeholder' => 'I am +1* if you change...'))->setLabel("Comment:");

    $review = $form->addElement('select', 'reviews',
                      array('required' => 'required'));

    $review->loadOptions($proposalReviewsMap);
    $review->setLabel("Review:");

    $form->addElement('submit', 'submit')->setLabel('Vote');

    $comment->addFilter('trim');

    if (isset($_POST['submit'])) {
        if ($form->validate()) {
            $voteData['value'] = $vote->getValue();
            $voteData['is_conditional'] = !empty($_POST['is_conditional']);

            $voteData['comment'] = $comment->getValue();

            $voteData['reviews'] = array($review->getValue());
            $voteData['user_handle'] = $auth_user->handle;

            $errors = array();

            if ($voteData['is_conditional'] && empty($voteData['comment'])) {
                $errors[] = 'You have to apply a comment if your vote is'
                          . ' conditional!';
            }
            if ($voteData['is_conditional'] && ($voteData['value'] < 1)) {
                $errors[] = 'Conditional votes have to be formulated positively!'
                          . " Please select '+1' and change your text to a"
                          . " form like 'I am +1 on this if you change...'.";
            }
            if (!array_key_exists($voteData['reviews'][0], $proposalReviewsMap)) {
                $errors[] = 'Reviews contains invalid data';
            }

            if ($errors) {
                report_error($errors);
            } else {
                $proposal->addVote($dbh, new ppVote($voteData));
                $proposal->sendActionEmail('proposal_vote', 'user',
                                           $auth_user->handle);
                report_success('Your vote has been registered successfully');

                $form = false;
            }
        }
    }
} else {
    $form = false;
}

display_pepr_nav($proposal);

?>

<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">

<?php

if ($proposal->status == 'vote') {
    echo " <tr>\n";
    echo '  <th class="headrow">&raquo; Cast Your Vote</th>' . "\n";
    echo " </tr>\n";
    echo " <tr>\n";

    if ($form) {
        echo '  <td class="textcell" valign="top">' . "\n";

        // Cron job runs at 4 am
        $pepr_end = mktime(4, 0, 0, date('m', $proposal->vote_date),
                           date('d', $proposal->vote_date),
                           date('Y', $proposal->vote_date));

        if (date('H', $proposal->vote_date) > '03') {
            // add a day
            $pepr_end += 86400;
        }

        if ($proposal->longened_date) {
            $pepr_end += PROPOSAL_STATUS_VOTE_TIMELINE * 2;
        } else {
            $pepr_end += PROPOSAL_STATUS_VOTE_TIMELINE;
        }
        echo '    <p>Voting Will End approximately ';
        echo format_date($pepr_end);
        echo "</p>\n";

        print $form;


    } else {
        ?>

  <td class="ulcell" valign="top">
   <ul>
    <li>You must be a full-featured PEAR developer.</li>
    <li>You must be logged in.</li>
    <li>Only one vote can be cast.</li>
    <li>Proposers can not vote on their own package.</li>
   </ul>

        <?php
    }

    echo "  </td>\n";
    echo " </tr>\n";

}

echo " <tr>\n";
echo '  <th class="headrow">&raquo; Votes</th>' . "\n";
echo " </tr>\n";
echo " <tr>\n";

switch ($proposal->status) {
    case 'draft':
    case 'proposal':
        echo '  <td class="textcell" valign="top">';
        echo 'Voting has not started yet.';
        break;

    default:
        $proposal->getVotes($dbh);
        if (count($proposal->votes) == 0) {
            echo '  <td class="textcell" valign="top">';
            echo 'No votes have been cast yet.';
        } else {
            $users = array();
            $head  = true;

            echo '  <td class="ulcell" valign="top">' . "\n<ul>\n";

            include_once 'pear-database-user.php';
            foreach ($proposal->votes as $vote) {
                if (!isset($users[$vote->user_handle])) {
                    $users[$vote->user_handle] =& user::info($vote->user_handle);
                }
                if ($vote->value > 0) {
                    $vote->value = '+' . $vote->value;
                }

                echo ' <li><strong>';
                echo make_link('pepr-vote-show.php?id=' . $proposal->id
                           . '&amp;handle='
                           . htmlspecialchars($vote->user_handle),
                           $vote->value);
                echo '</strong>';

                if ($vote->is_conditional) {
                    echo '^';
                } elseif (!empty($vote->comment)) {
                    echo '*';
                }
                echo ' &nbsp;(';
                echo make_link('/user/' . htmlspecialchars($vote->user_handle),
                           htmlspecialchars($users[$vote->user_handle]['name']));
                echo ')&nbsp; ' . format_date($vote->timestamp);
                echo "</li>\n";
            }

            $proposalVotesSum = ppVote::getSum($dbh, $proposal->id);

            echo "</ul>\n" . '<p style="padding-left: 1.2em;"><strong>';
            echo 'Sum: ' . $proposalVotesSum['all'] . '</strong> <small>(';
            echo $proposalVotesSum['conditional'];
            echo ' conditional)</small></p>' . "\n";
            echo '<p style="padding-left: 1.2em;"><small>^ Indicates';
            echo ' the vote is conditional.' . "\n";
            echo '<br />* Indicates';
            echo ' the vote contains a comment.</small></p>' . "\n";
        }
}

echo "  </td>\n";
echo " </tr>\n";
echo "</table>\n";

response_footer();

?>
