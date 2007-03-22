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
            if (isset($_POST['approve'])) {
                $notes = $manualNotes->updateCommentList($_POST['noteIds'], 'yes');
            } elseif (isset($_POST['delete'])) {
                $notes = $manualNotes->updateCommentList($_POST['noteIds'], 'no');
            } else {
                $notes = PEAR::raiseError('Neither delete nor approve was selected');
            }

            if (PEAR::isError($notes)) {
                $error = 'Error approving the comments, contact webmaster';
            } else {
                $message = 'Comment(s) successfully ';
                $message .= isset($_POST['approve']) ? 'approved' : 'deleted';
            }
            $_GET = $_POST;
            
            include dirname(__FILE__) . '/index.php';
            exit;
        }

        if (isset($_POST['url']) && !empty($_POST['url'])) {
            $pendingComments = $manualNotes->getPageComments($_POST['url'], 'pending');
        } else {
            $pendingComments = $manualNotes->getPageComments('', 'pending', true);
        }

        $url = isset($_POST['url']) ? strip_tags($_POST['url']) : '';
        $error = '';
        require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
        break;
    case 'updatesingle':
        break;
    default:
       response_header('Note Administration', null, null);
       report_error('Missing action');
       response_footer();
       exit;
}
