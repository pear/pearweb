<?php
auth_require('pear.bug', 'pear.dev');
$canpatch = true;
require 'include/patchtracker.inc';
require 'include/functions.inc';
$patchinfo = new Bug_Patchtracker;
if (isset($_POST['addpatch'])) {
    if (!isset($_POST['bug'])) {
        response_header('Error :: no bug selected');
        display_bug_error('No bug selected to add a patch to');
        response_footer();
        exit;
    }
    if (PEAR::isError($buginfo = $patchinfo->getBugInfo($_POST['bug']))) {
        response_header('Error :: invalid bug selected');
        display_bug_error('Invalid bug "' . $id . '" selected');
        response_footer();
        exit;
    }
    if (!isset($_POST['name']) || empty($_POST['name']) || !is_string($_POST['name'])) {
        $package = $buginfo['package_name'];
        $bug = $buginfo['id'];
        if (!is_string($_POST['name'])) {
            $_POST['name'] = '';
        }
        $name = $_POST['name'];
        $patches = $patchinfo->listPatches($bug);
        $errors = array('No patch name entered');
        include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/addpatch.php';
        exit;
    }
    $bug = $buginfo['id'];
    PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
    $e = $patchinfo->attach($bug, 'patch', $_POST['name'], $auth_user->handle);
    PEAR::popErrorHandling();
    if (PEAR::isError($e)) {
        $package = $buginfo['package_name'];
        $bug = $buginfo['id'];
        if (!is_string($_POST['name'])) {
            $_POST['name'] = '';
        }
        $name = $_POST['name'];
        $patches = $patchinfo->listPatches($bug);
        $errors = array($e->getMessage(),
            'Could not attach patch "' . $_POST['name'] . '" to Bug #' . $bug);
        include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/addpatch.php';
        exit;
    }
    $package = $buginfo['package_name'];
    $bug = $buginfo['id'];
    $name = $_POST['name'];
    $patches = $patchinfo->listPatches($bug);
    $errors = array();
    include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/patchadded.php';
    exit;
}
if (!isset($_GET['bug'])) {
    response_header('Error :: no bug selected');
    display_bug_error('No bug selected to add a patch to');
    response_footer();
    exit;
}
if (PEAR::isError($buginfo = $patchinfo->getBugInfo($_GET['bug']))) {
    response_header('Error :: invalid bug selected');
    display_bug_error('Invalid bug "' . $id . '" selected');
    response_footer();
    exit;
}
$errors = array();
$package = $buginfo['package_name'];
$bug = $buginfo['id'];
$name = isset($_GET['patch']) ? $_GET['patch'] : '';
$patches = $patchinfo->listPatches($bug);
include dirname(dirname(dirname(__FILE__))) . '/templates/bugs/addpatch.php';
?>