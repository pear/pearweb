<?php

/**
 * Let's define some sample errors so
 * it's easier to maintain later on..
 */
define ('NOTE_ADD_ERROR_NO_URI', 'No URI passed to the form');

/**
 * This parameter should be passed from the 
 * template manual with basically the 
 * $_SERVER['REQUEST_URI'] in the uri get
 * parameter.
 */
if (!isset($_GET['uri'])) {
    $error = NOTE_ADD_ERROR_NO_URI;
}

$noteUrl = strip_tags($_GET['uri']);

/**
 * Template of the form to add a note
 */
require dirname(dirname(dirname(__FILE__))) . '/templates/notes/add-note-form.tpl.php';
