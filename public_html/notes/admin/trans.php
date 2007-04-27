<?php

auth_require('pear.dev');

require_once 'notes/ManualNotes.class.php';
$manualNotes = new Manual_Notes;

$action = '';

if (isset($_REQUEST['action'])) {
    $action = strtolower($_REQUEST['action']);
}


switch ($action) {
    case 'makedocbug':
        
        if (isset($_POST['noteId'])) {
            $noteId = (int)$_POST['noteId'];
            
            $note = $manualNotes->getSingleCommentById($noteId);

            $registered      = 1;
            $package_name    = 'Documentation';
            $bug_type        = 'Documentation Problem';
            $email           = $auth_user->email;
            $handle          = $auth_user->handle;
            $sdesc           = 'User note that is a documentation problem';
            $ldesc           = str_replace('<br />', '', $note['note_text']);
            $package_version = null;
            $php_version     = 'Irrelevant';
            $php_os          = 'Irrelevant';
            $status          = 'Open';
            $passwd          = null;
            $reporter_name   = $auth_user->name;

            $sql = "
                INSERT INTO bugdb (
                    registered,
                    package_name,
                    bug_type,
                    email,
                    handle,
                    sdesc,
                    ldesc,
                    package_version,
                    php_version,
                    php_os,
                    status,
                    ts1,
                    passwd,
                    reporter_name
                ) VALUES (
                    '$registered', '$package_name', '$bug_type', '$email', '$handle',
                    '$sdesc', '$ldesc', null, '$php_version', '$php_os',
                    '$status', NOW(), null, '$reporter_name'
                )
            ";

            /**
             * Hrmph...
             */
            if ($dbh->phptype == 'mysql') {
                $id = mysql_insert_id();
            } else {
                $id = mysqli_insert_id($dbh->connection);
            }
            
            $emailInfos = array(
                'id'              => $id,
                'php_os'          => $php_os,
                'package_version' => $package_version,
                'php_version'     => $php_version,
                'package_name'    => $package_name,
                'bug_type'        => $bug_type,
                'ldesc'           => $ldesc,
                'sdesc'           => $sdesc,
            );
            
            $dbh->query($sql);

            $manualNotes->deleteSingleComment($noteId);
            
            require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'include/bugs/pear-bug-accountrequest.php';
            $pba = new PEAR_Bug_AccountRequest;
            $pba->sendBugEmail($emailInfos);
        }
        break;
    case 'updateapproved':
        
        if (isset($_POST['noteIds']) && is_array($_POST['noteIds'])) {
            if (isset($_POST['pending'])) {
                $notes = $manualNotes->updateCommentList($_POST['noteIds'], 'pending');
            } elseif (isset($_POST['delete'])) {
                $notes = $manualNotes->updateCommentList($_POST['noteIds'], 'no');
            } else {
                $notes = PEAR::raiseError('Neither delete nor approve was selected');
            }

            if (PEAR::isError($notes)) {
                $error = 'Error while making the note pending, contact webmaster';
            } else {
                $message = 'Comment(s) successfully ';
                $message .= isset($_POST['pending']) ? 'pending' : 'deleted';
            }
            $_GET = $_POST;
            
            include dirname(__FILE__) . '/index.php';
            exit;
        }

        if (isset($_POST['url']) && !empty($_POST['url'])) {
            $pendingComments = $manualNotes->getPageComments($_POST['url'], 'yes');
        } else {
            $pendingComments = $manualNotes->getPageComments('', 'yes', true);
        }

        $url = isset($_POST['url']) ? strip_tags($_POST['url']) : '';
        $error = '';
        require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
        break;
    case 'approvemass':
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
