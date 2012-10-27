<?php
require_once 'Services/ReCaptcha.php';

$captcha = new Services_ReCaptcha(PEAR_RECAPTCHA_PUBLIC_KEY, PEAR_RECAPTCHA_PRIVATE_KEY);

session_start();

$post = $_POST;


$loggedin = isset($auth_user) && $auth_user->registered;

if ($loggedin) {
    /**
     * These are the keys that have to be set
     * in order to get no errors. If someone is
     * missing one of them, then something is
     * totally wrong.
     */
    $keys = array(
            'noteUrl'  => 'Note Address',
            'redirect' => 'Original Manual Page',
            'note'     => 'User note',
    );
    $post['user'] = $auth_user->name;
} else {
    /**
     * These are the keys that have to be set
     * in order to get no errors. If someone is
     * missing one of them, then something is
     * totally wrong.
     */
    $keys = array(
            'noteUrl'  => 'Note Address',
            'redirect' => 'Original Manual Page',
            'user'     => 'Username/Email',
            'note'     => 'User note',
            'answer'   => 'Captcha Answer'
    );
}


$errors = array();

/**
 * Check if the spam check is passed.
 * @todo Use real cpatchas as this is
 * going to be highly used.
 *
 * If the captcha is wrong, then regenerate it.
 */
if (!$loggedin) {
    if (!$captcha->validate()) {
        $errors[] = 'Incorrect Captcha';       
    }
    /**
     * @todo Check akismet here aswell ?
     */
}

/**
 * Check if the keys are set, if not
 * then set an error..
 */
foreach ($keys as $key => $message) {
    if (!isset($post[$key]) || empty($post[$key])) {
        $errors[] = 'Error occured, missing: ' . $message;
    }
}

$redirect = $post['redirect'];

if (empty($errors)) {

    require_once 'notes/ManualNotes.class.php';

    $manualNote = new Manual_Notes;

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
        $errors[] = $added->getMessage() . ' please contact <a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">Webmaster</a> , Thanks';
    }
    /**
     * We need no further answers
     */
    if (isset($_SESSION['answer'])) {
        unset($_SESSION['answer']);
    }
    require PEARWEB_TEMPLATEDIR . '/notes/add-note.tpl.php';
} else {
    $email = $post['user'];
    $note = $post['note'];
    $noteUrl = $post['noteUrl'];
    require PEARWEB_TEMPLATEDIR . '/notes/add-note-form.tpl.php';
}