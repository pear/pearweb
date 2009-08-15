<?php
require 'include/functions.inc';
if (!isset($_GET['bug_id']) && !isset($_GET['bug'])) {
    response_header('Error :: no bug selected');
    report_error('No patch selected to view');
    response_footer();
    exit;
}

$revision = isset($_GET['revision']) ? $_GET['revision'] : null;
$patch    = isset($_GET['patch'])    ? $_GET['patch'] : null;
$bug_id   = isset($_GET['bug'])      ? $_GET['bug'] : null;
if (empty($bug_id)) {
    $bug_id = (int)$_GET['bug_id'];
}

require 'bugs/patchtracker.php';
$patchinfo = new Bugs_Patchtracker;
if (PEAR::isError($buginfo = $patchinfo->getBugInfo($bug_id))) {
    response_header('Error :: invalid bug selected');
    report_error('Invalid bug "' . $bug_id . '" selected');
    response_footer();
    exit;
}

if (isset($patch) && isset($revision)) {
    if ($revision == 'latest') {
        $revisions = $patchinfo->listRevisions($buginfo['id'], $patch);
        if (isset($revisions[0])) {
            $revision = $revisions[0][0];
        }
    }

    $path = $patchinfo->getPatchFullpath($bug_id, $patch, $revision);
    if (!file_exists($path)) {
        response_header('Error :: no such patch/revision');
        report_error('Invalid patch/revision specified');
        response_footer();
        exit;
    }

    if ($patchinfo->userNotRegistered($bug_id, $patch, $revision)) {
        response_header('User has not confirmed identity');
        report_error('The user who submitted this patch has not yet confirmed ' .
            'their email address.  ');
        echo '<p>If you submitted this patch, please check your email.</p>' .
            '<p><strong>If you do not have a confirmation message</strong>, <a href="resend-request-email.php?' .
            'handle=' . urlencode($patchinfo->getDeveloper($bug_id, $patch, $revision))
            . '">click here to re-send</a> or write a message to' .
            ' <a href="mailto:' . PEAR_DEV_EMAIL . '">' . PEAR_DEV_EMAIL . '</a> asking for manual approval of your account.</p>';
        response_footer();
        exit;
    }

    require_once 'HTTP.php';
    if (isset($_GET['download'])) {
        header('Last-modified: ' . HTTP::date(filemtime($path)));
        header('Content-type: application/octet-stream');
        header('Content-disposition: attachment; filename="' . $patch . '.patch.txt"');
        header('Content-length: '.filesize($path));
        readfile($path);
        exit;
    }
    $patchcontents = $patchinfo->getPatch($buginfo['id'], $patch, $revision);

    if (PEAR::isError($patchcontents)) {
        response_header('Error :: Cannot retrieve patch');
        report_error('Internal error: Invalid patch/revision specified (is in database, but not in filesystem)');
        response_footer();
        exit;
    }

    $package     = $buginfo['package_name'];
    $bug         = $buginfo['id'];
    $handle      = $patchinfo->getDeveloper($bug, $patch, $revision);
    $obsoletedby = $patchinfo->getObsoletingPatches($bug, $patch, $revision);
    $obsoletes   = $patchinfo->getObsoletePatches($bug, $patch, $revision);
    $patches     = $patchinfo->listPatches($bug);
    $revisions   = $patchinfo->listRevisions($bug, $patch);
    $canpatch    = auth_check('pear.bug') || auth_check('pear.dev');

    response_header('Bug #' . clean($bug) . ' :: Patches');
    show_bugs_menu(clean($buginfo['package_name']));
    include PEARWEB_TEMPLATEDIR . '/bugs/listpatches.php';
    if (isset($_GET['diff']) && $_GET['diff'] && isset($_GET['old']) && is_numeric($_GET['old'])) {
        $old = $patchinfo->getPatchFullpath($bug_id, $patch, $_GET['old']);
        $new = $path;
        if (!realpath($old) || !realpath($new)) {
            response_header('Error :: Cannot retrieve patch');
            report_error('Internal error: Invalid patch revision specified for diff');
            response_footer();
            exit;
        }

        require_once 'Text/Diff.php';
        require_once 'bugs/Diff/pearweb.php';
        assert_options(ASSERT_WARNING, 0);
        $d    = new Text_Diff($orig = file($old), $now = file($new));
        $diff = new Text_Diff_Renderer_pearweb($d);
        include PEARWEB_TEMPLATEDIR . '/bugs/patchdiff.php';
        response_footer();
        exit;
    }
    include PEARWEB_TEMPLATEDIR . '/bugs/patchdisplay.php';
    response_footer();
    exit;
}

$bug      = $buginfo['id'];
$patches  = $patchinfo->listPatches($bug);
$canpatch = auth_check('pear.bug') || auth_check('pear.dev');
response_header('Bug #' . clean($bug) . ' :: Patches');
include PEARWEB_TEMPLATEDIR . '/bugs/listpatches.php';
response_footer();
