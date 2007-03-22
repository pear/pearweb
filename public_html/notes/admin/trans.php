<?php

auth_require('pear.dev');

require_once 'notes/ManualNotes.class.php';
$manualNotes = new Manual_Notes;

$action = '';

if (isset($_REQUEST['action'])) {
    $action = strtolower($_REQUEST['action']);
}

switch ($action) {
    case 'updateMass':
        if (isset($_POST['noteIds']) && is_array($_POST['noteIds'])) {
            $manualNotes->updateCommentList($_POST['noteIds'], 'yes');
        }
        break;
    case 'updateSingle':
        break;
    default:
       response_header('Note Administration', null, null);
       report_error('Missing action');
       response_footer();
       exit;
}
