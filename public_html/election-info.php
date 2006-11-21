<?php
if (!isset($auth_user) || !$auth_user) {
    if (isset($_GET['vote'])) {
        if (strlen($_SERVER['QUERY_STRING'])) {
            $query = '?' . strip_tags($_SERVER['QUERY_STRING']);
        } else {
            $query = '';
        }
        require dirname(dirname(__FILE__)) . '/templates/election-register.tpl.php';   
        exit; 
    }
}
require 'pear-voter.php';
$voter = &new PEAR_Voter;
if (isset($_POST['confirm'])) {
    // display vote confirmation page
    if (!$voter->electionExists($_POST['election'])) {
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'No such election id: ' . htmlspecialchars($_GET['election']);
        require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
        exit;
    }
    if ($voter->hasVoted($_POST['election'])) {
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'You have already voted in this election';
        require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
        exit;
    }
    $info = $voter->electionInfo($_POST['election']);
    if (!isset($_POST['vote'])) {
        $_POST['vote'] = array();
    }
    if (isset($_POST['abstain'])) {
        $info['abstain'] = true;
        $info['vote'] = array();
    } else {
        if (!is_array($_POST['vote'])) {
            $_POST['vote'] = array($_POST['vote']);
        }
        if (count($_POST['vote']) < $info['minimum_choices'] ||
              count($_POST['vote']) > $info['maximum_choices']) {
            $error = 'You voted for ' . count($_POST['vote']) . ' choices, but must vote ' .
                'for at least ' . $info['minimum_choices'] . ' choices, and at most ' .
                $info['maximum_choices'] . ' choices';
            require dirname(dirname(__FILE__)) . '/templates/election-dovote.tpl.php';
            exit;
        }
        $info['abstain'] = false;
        $info['vote'] = $_POST['vote'];
    }
    require dirname(dirname(__FILE__)) . '/templates/election-confirm.tpl.php';
    exit;
}
if (isset($_POST['finalvote'])) {
    // vote has been confirmed
    // generate salt for hash
    if (!$voter->electionExists($_POST['election'])) {
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'No such election id: ' . htmlspecialchars($_GET['election']);
        require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
        exit;
    }
    if ($voter->hasVoted($_POST['election'])) {
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'You have already voted in this election';
        require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
        exit;
    }
    if (!isset($_POST['vote']) || !is_array($_POST['vote'])) {
        $_POST['vote'] = array();
    }
    if (!isset($_POST['abstain'])) {
        $_POST['abstain'] = false;
    }
    $info = $voter->electionInfo($_POST['election']);
    $info['vote'] = $_POST['vote'];
    $info['abstain'] = $_POST['abstain'];
    $salt = $voter->getVoteSalt();
    if ($info['abstain']) {
        $success = $voter->abstain($_POST['election']);
    } else {
        $success = $voter->vote($_POST['election'], $_POST['vote']);
    }
    require dirname(dirname(__FILE__)) . '/templates/election-confirmed.tpl.php';
    exit;
}
if (!isset($_GET['election'])) {
    // display summary
    $currentelections = $voter->listCurrentElections();
    $completedelections = $voter->listCompletedElections();
    $allelections = $voter->listAllElections();
    require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
    exit;
}
if (!$voter->electionExists($_GET['election'])) {
    // display summary
    $currentelections = $voter->listCurrentElections();
    $completedelections = $voter->listCompletedElections();
    $allelections = $voter->listAllElections();
    $error = 'No such election id: ' . htmlspecialchars($_GET['election']);
    require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
    exit;
}
if (isset($_GET['vote'])) {
    $info = $voter->electionInfo($_GET['election']);
    if ($voter->hasVoted($_GET['election'])) {
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'You cannot vote twice in the same election';
        require dirname(dirname(__FILE__)) . '/templates/election-vote.tpl.php';
        exit;
    } elseif ($voter->canVote($_GET['election'])) {
        require dirname(dirname(__FILE__)) . '/templates/election-dovote.tpl.php';
    } else {
        $info = $voter->electionInfo($_GET['election']);
        require dirname(dirname(__FILE__)) . '/templates/election-showresults.tpl.php';
    }
}
if (isset($_GET['results'])) {
    $info = $voter->electionInfo($_GET['election']);
    require dirname(dirname(__FILE__)) . '/templates/election-showresults.tpl.php';
}
response_footer();
?>
