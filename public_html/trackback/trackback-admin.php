<?php

require_once 'Damblan/Trackback.php';

auth_require('pear.dev');

$action = (isset($_GET['action'])) ? $_GET['action'] : '';

if (!empty($action)) {
    if (!isset($_GET['id'])) {
        PEAR::raiseError('Missing data. No ID set. Exiting.');
    }
    if (!isset($_GET['timestamp'])) {
        PEAR::raiseError('Missing data. No timestamp set. Exiting.');
    }
    $trackback = new Damblan_Trackback(array('id' => $id), $timestamp);
    $res = $trackback->load($dbh);
}

switch ($action) {
case 'approve':
    try {
        $trackback->approve($dbh);
    } catch (Exception $e) {
        PEAR::raiseError('Unable to approve trackback.');
    }
    $msg = '<div class="success">Trackback successfully approved.</div>';
    break;
case 'delete':
    $msg = '<div class="warnings">Really <a href="/trackback/trackback-admin.php?action=delete_verified&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">delete</a> trackback '.$timestamp.' for '.$id.'?</div>';
    break;
case 'delete_verified':
    try {
        $trackback->delete($dbh);
    } catch (Exception $e) {
        PEAR::raiseError('Unable to verify trackback.');
    }
    $msg = '<div class="success">RIP trackback.</div>';
    break;
}

response_header('Trackback admin');
echo $msg;
response_footer();
?>
