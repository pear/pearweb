<?php
require 'include/functions.inc';
if (!isset($_GET['bug'])) {
    response_header('Error :: no bug selected');
    display_bug_error('No bug selected to add a patch to');
    response_footer();
    exit;
}
require 'include/patchtracker.inc';
$patchinfo = new Bug_Patchtracker;
if (PEAR::isError($buginfo = $patchinfo->getBugInfo($_GET['bug']))) {
    response_header('Error :: invalid bug selected');
    display_bug_error('Invalid bug "' . $GET['bug'] . '" selected');
    response_footer();
    exit;
}
if (isset($_GET['patch']) && isset($_GET['revision'])) {
    $revisions = $patchinfo->listRevisions($buginfo['id'], $_GET['patch']);
    if ($_GET['revision'] == 'latest' && isset($revisions[0])) {
        $_GET['revision'] = $revisions[0][0];
    }
    if (!file_exists($path = $patchinfo->getPatchFullpath($_GET['bug'], $_GET['patch'],
                                                        $_GET['revision']))) {
        response_header('Error :: no such patch/revision');
        display_bug_error('Invalid patch/revision specified');
        response_footer();
        exit;
    }
    require_once 'HTTP.php';
    if (isset($_GET['download'])) {
        header('Last-modified: ' . HTTP::date(filemtime($path)));
        header('Content-type: application/octet-stream');
        header('Content-disposition: attachment; filename="' . $_GET['patch'] . '.patch.txt"');
        header('Content-length: '.filesize($path));
        echo readfile($path);
        exit;
    }
    $patchcontents = file_get_contents($path);
    $package = $buginfo['package_name'];
    $bug = $buginfo['id'];
    $revision = $_GET['revision'];
    $patch = $_GET['patch'];
    include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/patchdisplay.php';
}
response_header('Bug #' . clean($buginfo['id']) . ' :: Patches');
$bug = $buginfo['id'];
$patches = $patchinfo->listPatches($buginfo['id']);
$canpatch = auth_check('pear.bug') || auth_check('pear.dev');
include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/listpatches.php';
response_footer();