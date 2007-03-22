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
        include dirname(dirname(dirname(__FILE__))) . 
                '/templates/bugs/addpatch.php';
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
            'Could not attach patch "' . 
            htmlspecialchars($_POST['name']) . 
            '" to Bug #' . $bug);

        include dirname(dirname(dirname(__FILE__))) . 
                '/templates/bugs/addpatch.php';
        exit;
    }
    // {{{ Email after the patch is added.
    /**
     * Email the package maintainers/leaders about
     * the new patch added to their bug request.
     */
    require_once 'Damblan/Mailer.php';
    require_once 'Damblan/Bugs.php';

    $patchName = htmlspecialchars($_POST['name']);

    $rev       = $patchinfo->getBugInfo($bug);
    $rev       = $rev['revision'];

    $mailData = array(
        'id'         => $bug,
        'url'        => 'http://' . PEAR_CHANNELNAME . 
                        "/bugs/patch-display.php?bug=$bug&patch=$patchName&revision=$rev&display=1",

        'date'       => date('Y-m-d H:i:s'),
        'name'       => $_POST['name'],
        'packageUrl' => 'http://' . PEAR_CHANNELNAME .
                        '/bugs/bug.php?id=' . $bug,
    );

    $additionalHeaders['To'] = Damblan_Bugs::getMaintainers($bug);

    $mailer = Damblan_Mailer::create('Patch_Added', $mailData);

    $res = true;

    if (!DEVBOX) {
        $res = $mailer->send($additionalHeaders);
    }

    if (PEAR::isError($res)) {
        // Patch not sent. Let's handle it here but not now..
    }
    // }}}
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
