<?php
auth_require('pear.election', 'pear.admin');
$new = 'new';
$year = date('Y') + 1;
$years = array($year--, $year);
if (!isset($_POST['step'])) {
    $error = '';
    $info = array(
        'purpose' => '',
        'detail' => '',
        'choices' => 2,
        'year' => date('Y', strtotime('+30 days')),
        'month' => date('m', strtotime('+30 days')),
        'day' => date('d', strtotime('+30 days')),
        'length' => 7,
        'minimum' => 1,
        'maximum' => 1,
        'eligiblevoters' => 1,
    );
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step1.tpl.php';
} elseif ($_POST['step'] == 2) {
    require 'election/pear-election.php';
    $election = new PEAR_Election;
    $error = $election->validateStep1();
    $info['purpose'] = $_POST['purpose'];
    $info['detail'] = $_POST['detail'];
    $info['choices'] = (int) $_POST['choices'];
    $info['year'] = (int) $_POST['year'];
    $info['month'] = $_POST['month'];
    $info['day'] = $_POST['day'];
    $info['length'] = (int) $_POST['length'];
    $info['minimum'] = (int) $_POST['minimum'];
    $info['maximum'] = (int) $_POST['maximum'];
    $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
    if ($error) {
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step1.tpl.php';
        exit;
    }
    for ($i = 1; $i <= $info['choices']; $i++) {
        $info['summary' . $i] = empty($_POST['summary' . $i]) ? '' : $_POST['summary' . $i];
        $info['summary_link' . $i] =
            empty($_POST['summary_link' . $i]) ? '' : $_POST['summary_link' . $i];
    }
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step2.tpl.php';
} elseif ($_POST['step'] == 3) {
    require 'election/pear-election.php';
    $election = new PEAR_Election;
    $error = $election->validateStep1();
    $info['purpose'] = $_POST['purpose'];
    $info['detail'] = $_POST['detail'];
    $info['choices'] = (int) $_POST['choices'];
    $info['year'] = (int) $_POST['year'];
    $info['month'] = $_POST['month'];
    $info['day'] = $_POST['day'];
    $info['length'] = (int) $_POST['length'];
    $info['minimum'] = (int) $_POST['minimum'];
    $info['maximum'] = (int) $_POST['maximum'];
    $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
    if ($error) {
        // this should never happen.  It will only occur
        // if the user manually fills POST data without going
        // through the official form, and makes a mistake.
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step1.tpl.php';
        exit;
    }
    $error = $election->validateStep2();
    $info['choices'] = (int) $_POST['choices'];
    for ($i = 1; $i <= $info['choices']; $i++) {
        $info['summary' . $i] = empty($_POST['summary' . $i]) ? '' : $_POST['summary' . $i];
        $info['summary_link' . $i] =
            empty($_POST['summary_link' . $i]) ? '' : $_POST['summary_link' . $i];
    }
    if ($error) {
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step2.tpl.php';
        exit;
    }
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step3.tpl.php';
} elseif ($_POST['step'] == 4) {
    if (isset($_POST['cancel'])) {
        require 'election/pear-voter.php';
        $voter = new PEAR_Voter;
        $currentelections = $voter->listCurrentElections();
        $completedelections = $voter->listCompletedElections();
        $allelections = $voter->listAllElections();
        $error = 'Election creation cancelled';
        $retrieval = false;
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-vote.tpl.php';
        exit;
    }
    require 'election/pear-election.php';
    $election = new PEAR_Election;
    $error = $election->validateStep1();
    $info['purpose'] = $_POST['purpose'];
    $info['detail'] = $_POST['detail'];
    $info['choices'] = (int) $_POST['choices'];
    $info['year'] = (int) $_POST['year'];
    $info['month'] = $_POST['month'];
    $info['day'] = $_POST['day'];
    $info['length'] = (int) $_POST['length'];
    $info['minimum'] = (int) $_POST['minimum'];
    $info['maximum'] = (int) $_POST['maximum'];
    $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
    if ($error) {
        // this should never happen.  It will only occur
        // if the user manually fills POST data without going
        // through the official form, and makes a mistake.
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step1.tpl.php';
        exit;
    }
    for ($i = 1; $i <= $info['choices']; $i++) {
        $info['summary' . $i] = empty($_POST['summary' . $i]) ? '' : $_POST['summary' . $i];
        $info['summary_link' . $i] =
            empty($_POST['summary_link' . $i]) ? '' : $_POST['summary_link' . $i];
    }
    $error = $election->validateStep2();
    if ($error) {
        // this should never happen.  It will only occur
        // if the user manually fills POST data without going
        // through the official form, and makes a mistake.
        require dirname(dirname(dirname(__FILE__))) . '/templates/election-new-step2.tpl.php';
        exit;
    }
    $election->saveNewElection();
    $error = '';
    require 'election/pear-voter.php';
    $voter = new PEAR_Voter;
    $currentelections = $voter->listCurrentElections();
    $completedelections = $voter->listCompletedElections();
    $allelections = $voter->listAllElections();
    $error = 'Election saved';
    $retrieval = false;
    require dirname(dirname(dirname(__FILE__))) . '/templates/election-vote.tpl.php';
}
