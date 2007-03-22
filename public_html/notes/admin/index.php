<?php
auth_require('pear.dev');

/**
 * Manual notes class
 */
require_once 'notes/ManualNotes.class.php';

$manualNotes = new Manual_Notes;

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $pendingComments = $manualNotes->getPageComments($_GET['url'], 'pending');
} else {
    $pendingComments = $manualNotes->getPageComments('', 'pending', true);
}

$url = isset($_GET['url']) ? strip_tags($_GET['url']) : '';
$error = '';
require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
