<?php
auth_require('pear.dev');

/**
 * Manual notes class
 */
require_once 'notes/ManualNotes.class.php';

$manualNotes = new Manual_Notes;

$pendingComments = $manualNotes->getPageComments('', 'pending', true);

$error = '';
require dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/notes/note-manage-admin.tpl.php';
