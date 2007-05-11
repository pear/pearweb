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
$button  = 'Approve';
$caption = 'Approve Notes';
$name    = 'approve';

if (isset($_GET['status']) && $_GET['status'] == 'approved') {
    $status  = 'yes';
    $action  = 'updateApproved';
    $title   = 'Move Approved Notes to Pending';
    $button  = 'Make Pending';
    $caption = 'Pending';
    $name    = 'pending';
} elseif (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $status  = 'no';
    $action  = 'approveMass';
    $title   = 'Deleted notes';
    $button  = 'Un-Delete comments';
    $caption = 'Deleted';
    $name    = 'delete';
}

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $pendingComments = $manualNotes->getPageComments($_GET['url'], $status);
} else {
    $pendingComments = $manualNotes->getPageComments('', $status, true);
}

$url = isset($_GET['url']) ? strip_tags($_GET['url']) : '';
if (!isset($error)) {
    $error = '';
}
require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
