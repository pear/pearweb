<?php

auth_require('pear.dev');

require_once 'notes/ManualNotes.class.php';
$manualNotes = new Manual_Notes;

$action = '';

if (isset($_REQUEST['action'])) {
    $action = strtolower($_REQUEST['action']);
}

switch ($action) {
    case 'updatemass':
        if (isset($_POST['noteIds']) && is_array($_POST['noteIds'])) {
            $notes = $manualNotes->updateCommentList($_POST['noteIds'], 'yes');

            if (PEAR::isError($notes)) {
                $error = 'Error approving the comments, contact webmaster';
            } else {
                $message = 'Comment(s) successfully approved';
            }
            
            include dirnname(__FILE__) . '/index.php';
        }
        
        break;
    case 'updatesingle':
        break;
    default:
       response_header('Note Administration', null, null);
       report_error('Missing action');
       response_footer();
       exit;
}
