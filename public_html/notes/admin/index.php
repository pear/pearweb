<?php
auth_require('pear.dev');

/**
 * Manual notes class
 */
require_once 'notes/ManualNotes.class.php';

$manualNotes = new Manual_Notes;

$status  = 'pending';
$action  = 'approveMass';
$title   = 'Approve Pending User Notes';
$button  = 'Approve selected comments';
$caption = 'Approve';
$name    = 'approve';

if (isset($_GET['status']) && $_GET['status'] == 'approved') {
    $status  = 'yes';
    $action  = 'updateApproved';
    $title   = 'Move Approved Comments to pending';
    $button  = 'Make pending selected notes';
    $caption = 'Pending';
    $name    = 'pending';
}

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $pendingComments = $manualNotes->getPageComments($_GET['url'], $status);
} else {
    $pendingComments = $manualNotes->getPageComments('', $status, true);
}

$url = isset($_GET['url']) ? strip_tags($_GET['url']) : '';
$error = '';
require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
