<?php
auth_require('pear.dev');

$status = isset($_GET['status']) && $_GET['status'] == 'yes' ? 'approved' : false;
$ajax   = isset($_GET['ajax']) && $_GET['ajax'] == 'yes' ? true : false;
$id     = isset($_GET['noteId']) ? (int)$_GET['noteId'] : '';

if (!$id) {
    response_footer();
    exit;
}

require_once 'notes/ManualNotes.class.php';
$manualNotes = new Manual_Notes;
$noteContent = $manualNotes->getSingleCommentById($id);

include PEARWEB_TEMPLATEDIR . '/notes/view-full-note.tpl.php';
