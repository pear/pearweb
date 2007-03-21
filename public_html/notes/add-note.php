<?php

$post = $_POST;

unset($_POST);

$keys = array(
    'noteUrl' => 'Note Address', 
    'user'    => 'Username/Email', 
    'note'    => 'User comment/note', 
    'answer'  => 'Captcha Answer');


$errors = array();

foreach ($keys as $key => $message) {
    if (!isset($post[$key])) {
        $errors[] = 'Error occured, missing: ' . $message;
    }
}

require_once 'notes/ManualNotes.class.php';

$manualNote = new ManualNotes;
/**
 * @todo Check the captcha here.
 * @todo Check akismet here aswell ?
 */

$added = $manualNote->addComment($post['noteUrl'], $post['user'], $post['note']);

if (PEAR::isError($added)) {
    $errors[] = $added->getMessage();
}

require dirname(dirname(dirname(__FILE__))) . '/templates/notes/add-note.php';
