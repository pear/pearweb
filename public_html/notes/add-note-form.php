<?php
session_start();
/**
 * Let's define some sample errors so
 * it's easier to maintain later on..
 */
define ('NOTE_ADD_ERROR_NO_URI', 'No URI passed to the form');

require_once 'Services/ReCaptcha.php';

$captcha = new Services_ReCaptcha(PEAR_RECAPTCHA_PUBLIC_KEY, PEAR_RECAPTCHA_PRIVATE_KEY);


/**
 * This parameter should be passed from the
 * template manual with basically the
 * $_SERVER['REQUEST_URI'] in the uri get
 * parameter.
 */
if (!isset($_GET['uri'])) {
    $error = NOTE_ADD_ERROR_NO_URI;
}

$loggedin = isset($auth_user) && $auth_user->registered;
$email = 'user@example.com';
$note = '';
$noteUrl   = strip_tags($_GET['uri']);
$redirect  = strip_tags($_GET['redirect']);
if (!$loggedin) {
    $spamCheck = $captcha->getOperation();
    $_SESSION['answer'] = $captcha->getAnswer();
}

// Template of the form to add a note
require PEARWEB_TEMPLATEDIR . '/notes/add-note-form.tpl.php';
