<?php
if (!isset($auth_user) || !$auth_user) {
    if (strlen($_SERVER['QUERY_STRING'])) {
        $query = '?' . strip_tags($_SERVER['QUERY_STRING']);
    } else {
        $query = '';
    }
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-register.tpl.php';
} else {
    require 'election/pear-voter.php';
    $voter = &new PEAR_Voter;
    $retrieval = false;
    if (isset($_POST['salt'])) {
        if ($vote = $voter->retrieveVote($_POST['election'], $_POST['salt'])) {
            $info = $voter->electionInfo($_POST['election']);
            $info['vote'] = $vote;
        } else {
            $error = 'No votes found, incorrect salt?';
        }
        $retrieval = true;
    }
    $currentelections = $voter->listCurrentElections();
    $completedelections = $voter->listCompletedElections();
    $allelections = $voter->listAllElections();
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-vote.tpl.php';
}
?>
