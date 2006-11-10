<?php

/**
 * Interface for inputing/editing an election.
 *
 * This source file is subject to version 3.01 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   pearweb_election
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright Copyright (c) 2006 The PHP Group
 * @license   http://www.php.net/license/3_01.txt  PHP License
 * @version   $Id$
 */

auth_require('pear.election');
require 'pear-election.php';
$new = 'edit';
$year = date('Y');
$years = array($year + 1, $year--, $year--, $year--);
$election = new PEAR_Election;
if (isset($_GET['election'])) {
    $info = $election->getInfo($_GET['election']);
    if (!$info) {
        $error = 'Unknown election';
        $elections = $election->listElections();
        require dirname(dirname(__FILE__)) . '/templates/election-listforedit.tpl.php';
        exit;
    }
    if (!$election->canEdit($_GET['election'])) {
        $error = 'Cannot edit that election';
        $elections = $election->listElections();
        require dirname(dirname(__FILE__)) . '/templates/election-listforedit.tpl.php';
        exit;
    }
    $error = array();
    $election_id = (int) $_GET['election'];
    require dirname(dirname(__FILE__)) . '/templates/election-new-step1.tpl.php';
    exit;
}
if (isset($_POST['step'])) {
    if (!isset($_POST['election_id']) || !$election->electionExists($_POST['election_id'])) {
        $error = 'Unknown election';
        $elections = $election->listElections();
        require dirname(dirname(__FILE__)) . '/templates/election-listforedit.tpl.php';
        exit;
    }
    $election_id = (int) $_POST['election_id'];
    if (!$election->canEdit($election_id)) {
        $error = 'Cannot edit that election';
        $elections = $election->listElections();
        require dirname(dirname(__FILE__)) . '/templates/election-listforedit.tpl.php';
        exit;
    }
    switch ($_POST['step']) {
        case '2' :
            $error = $election->validateStep1(false);
            $info['purpose'] = $_POST['purpose'];
            $info['detail'] = $_POST['detail'];
            $info['choices'] = (int) $_POST['choices'];
            $info['year'] = (int) $_POST['year'];
            $info['month'] = (int) $_POST['month'];
            $info['day'] = (int) $_POST['day'];
            $info['length'] = (int) $_POST['length'];
            $info['minimum'] = (int) $_POST['minimum'];
            $info['maximum'] = (int) $_POST['maximum'];
            $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
            if ($error) {
                require dirname(dirname(__FILE__)) . '/templates/election-new-step1.tpl.php';
                exit;
            }
            $info = $election->setupChoices($election_id, $info);
            require dirname(dirname(__FILE__)) . '/templates/election-new-step2.tpl.php';
            exit;
        case '3' :
            $error = $election->validateStep1(false);
            $info['purpose'] = $_POST['purpose'];
            $info['detail'] = $_POST['detail'];
            $info['choices'] = (int) $_POST['choices'];
            $info['year'] = (int) $_POST['year'];
            $info['month'] = (int) $_POST['month'];
            $info['day'] = (int) $_POST['day'];
            $info['length'] = (int) $_POST['length'];
            $info['minimum'] = (int) $_POST['minimum'];
            $info['maximum'] = (int) $_POST['maximum'];
            $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
            if ($error) {
                require dirname(dirname(__FILE__)) . '/templates/election-new-step1.tpl.php';
                exit;
            }
            $error = $election->validateStep2();
            $info = $election->setupChoices($election_id, $info);
            if ($error) {
                require dirname(dirname(__FILE__)) . '/templates/election-new-step2.tpl.php';
                exit;
            }
            require dirname(dirname(__FILE__)) . '/templates/election-new-step3.tpl.php';
            exit;
        case '4' :
            if (isset($_POST['cancel'])) {
                $error = 'Cancelled edit';
                break;
            }
            $error = $election->validateStep1(false);
            $info['purpose'] = $_POST['purpose'];
            $info['detail'] = $_POST['detail'];
            $info['choices'] = (int) $_POST['choices'];
            $info['year'] = (int) $_POST['year'];
            $info['month'] = (int) $_POST['month'];
            $info['day'] = (int) $_POST['day'];
            $info['length'] = (int) $_POST['length'];
            $info['minimum'] = (int) $_POST['minimum'];
            $info['maximum'] = (int) $_POST['maximum'];
            $info['eligiblevoters'] = (int) $_POST['eligiblevoters'];
            if ($error) {
                require dirname(dirname(__FILE__)) . '/templates/election-new-step1.tpl.php';
                exit;
            }
            $error = $election->validateStep2();
            for ($i = 1; $i <= $info['choices']; $i++) {
                $info['summary' . $i] =
                    empty($_POST['summary' . $i]) ? '' : $_POST['summary' . $i];
                $info['summary_link' . $i] =
                    empty($_POST['summary_link' . $i]) ? '' : $_POST['summary_link' . $i];
            }
            if ($error) {
                require dirname(dirname(__FILE__)) . '/templates/election-new-step2.tpl.php';
                exit;
            }
            // safe to save
            $election->saveEditedElection($election_id);
            $error = 'Election saved';
    }
}
$elections = $election->listElections();
require dirname(dirname(__FILE__)) . '/templates/election-listforedit.tpl.php';