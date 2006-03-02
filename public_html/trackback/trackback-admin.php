<?php

/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Tobias Schlitt <toby@php.net>                               |
   |                                                                      |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require('pear.dev');

$action = isset($_GET['action']) && !empty($_GET['action']) ? $_GET['action'] : false;
$track_id = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] : false;
$timestamp = isset($_GET['timestamp']) && !empty($_GET['timestamp']) ? $_GET['timestamp'] : false;

if (!$action || !$track_id || !$timestamp) {

    response_header('Trackback admin', null, null);
    report_error('Missing arguments. Exiting.');
    response_footer();
    exit();
}

include_once 'Damblan/Trackback.php';
include_once 'Damblan/Mailer.php';

$trackback = new Damblan_Trackback(array('id' => $track_id, 'timestamp' => $timestamp));
$res = $trackback->load($dbh);

$error = false;

if (!$res) {
    $msg = 'No trackback.';
    $error = true;
} elseif (PEAR::isError($res)) {
    $msg = $res->getMessage();
    $error = true;
}

if ($error) {
    response_header('Trackback admin', null, null);
    report_error('Error: ' . $msg);
    response_footer();
    exit();
}

$mailData = array(
    'id' => $trackback->get('id'),
    'blog_name' => $trackback->get('blog_name'),
    'title' => $trackback->get('title'),
    'url' => $trackback->get('url'),
    'excerpt' => $trackback->get('excerpt'),
    'date' => make_utc_date($trackback->get('timestamp')),
    'timestamp' => $trackback->get('timestamp'),
    'user' => $auth_user->handle,
);

$relocator = '<meta http-equiv="refresh" content="5; URL=http://' . PEAR_CHANNELNAME.
    '/package/'.$track_id.'/trackbacks">';

switch ($action) {
case 'approve':
    $trackback->approve($dbh);
    $mailer = Damblan_Mailer::create('Trackback_Approve', $mailData);
    $additionalHeaders['To'] = $trackback->getMaintainers();
    $mailer->send($additionalHeaders);
    $msg = '<div class="success">Trackback successfully approved.</div>';
    break;

case 'delete':
    $msg = '<div class="warnings">Really 
<a href="/trackback/trackback-admin.php?action=delete_verified&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">delete</a> 
or
<a href="/trackback/trackback-admin.php?action=delete_spam&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">delete as spam</a> 
trackback '.$timestamp.' for '.$track_id.'?</div>';

    // Confirmation of the delete action, no auto redirect
    $relocator = '';
    break;
    
case 'delete_spam':
    $spam = true;
case 'delete_verified':
    $spam = isset($spam) ? $spam : false;
    $trackback->delete($dbh, $spam);
    $mailer = Damblan_Mailer::create('Trackback_Delete', $mailData);
    $additionalHeaders['To'] = $trackback->getMaintainers();
    $mailer->send($additionalHeaders);
    $msg = '<div class="success">RIP trackback.</div>';
    break;

default:
    // We should never be here, but who knows within this code ;-)
    response_header('Trackback admin', null, null);
    report_error('Missing arguments. Exiting.');
    response_footer();
    break;
}

response_header('Trackback admin', null, $relocator);
echo $msg;
echo '<p>You should be redirected to the packages trackback page in 5 seconds. if this does not work, please click <a href="http://' . PEAR_CHANNELNAME . '/package/'.$track_id.'/trackbacks">here</a>.</p>';
response_footer();

?>
