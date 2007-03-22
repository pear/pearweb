<?php
session_start();

$post = $_POST;
unset($_POST);

/**
 * These are the keys that have to be set
 * in order to get no errors. If someone is
 * missing one of them, then something is 
 * totally wrong.
 */
$keys = array(
        'noteUrl' => 'Note Address', 
        'user'    => 'Username/Email', 
        'note'    => 'User comment/note', 
        'answer'  => 'Captcha Answer');


$errors = array();

/**
 * Check if the spam check is passed. 
 * @todo Use real cpatchas as this is 
 * going to be highly used.
 *
 * If the captcha is wrong, then regenerate it.
 */
if (isset($_SESSION['answer']) && strlen(trim($_SESSION['answer'])) > 0) {
    if ($post['answer'] != $_SESSION['answer']) {

        $errors[] = 'Incorrect Captcha';
        
        require_once 'NumeralCaptcha.php';

        $captcha            = new NumeralCaptcha;
        $spamCheck          = $captcha->getOperation();
        $_SESSION['answer'] = $captcha->getAnswer();
    }
}

/**
 * Check if the keys are set, if not
 * then set an error..
 */
foreach ($keys as $key => $message) {
    if (!isset($post[$key])) {
        $errors[] = 'Error occured, missing: ' . $message;
    }
}

if (empty($errors)) {
    
    require_once 'notes/ManualNotes.class.php';

    $manualNote = new Manual_Notes;
    /**
     * @todo Check the captcha here.
     * @todo Check akismet here aswell ?
     */

    $added = $manualNote->addComment($post['noteUrl'], $post['user'], $post['note']);

    if (PEAR::isError($added)) {
        if (isset($post['noteUrl'])) {
            /**
             * If someone tries to access this page
             * without a noteUrl then it's his problem
             * to get the comment template without 
             * a noteUrl.. this is recursivly not
             * going to be working however this check
             * should not have to be done because
             * in order to get to this point.. you need
             * to have.
             */
            $noteUrl = strip_tags($post['noteUrl']);
        }
        $errors[] = $added->getMessage() . ' please contact <a mailto="pear-webaster@lists.php.net">Webmaster</a> , Thanks';
    }
    /**
     * We need no further answers
     */
    if (isset($_SESSION['answer'])) {
        unset($_SESSION['answer']);
    }
}

require dirname(dirname(dirname(__FILE__))) . '/templates/notes/add-note.tpl.php';
