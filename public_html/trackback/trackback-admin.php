<?php

/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2004 The PHP Group                                |
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

require_once 'Damblan/Trackback.php';
require_once 'Damblan/Mailer.php';

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

$mailData = array(
    'id' => $trackback->id,
    'blog_name' => $trackback->blog_name,
    'title' => $trackback->title,
    'url' => $trackback->url,
    'excerpt' => $trackback->excerpt,
    'date' => make_utc_date($trackback->timestamp),
    'timestamp' => $trackback->timestamp,
    'user' => $_COOKIE['PEAR_USER'],
);

switch ($action) {
case 'approve':
    $trackback->approve($dbh);
    $mailer = Damblan_Mailer::create('Trackback_Approve', $mailData);
    $additionalHeaders['To'] = $trackback->getMaintainers();
    $mailer->send($additionalHeaders);
    $msg = '<div class="success">Trackback successfully approved.</div>';
    break;
case 'delete':
    $msg = '<div class="warnings">Really <a href="/trackback/trackback-admin.php?action=delete_verified&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">delete</a> trackback '.$timestamp.' for '.$id.'?</div>';
    break;
case 'delete_verified':
    $trackback->delete($dbh);
    $mailer = Damblan_Mailer::create('Trackback_Delete', $mailData);
    $additionalHeaders['To'] = $trackback->getMaintainers();
    $mailer->send($additionalHeaders);
    $msg = '<div class="success">RIP trackback.</div>';
    break;
}

$relocator = '<meta http-equiv="refresh" content="5; URL=http://pear.php.net/package/'.$id.'/trackbacks">';

response_header('Trackback admin', null, $relocator);
echo $msg;
echo '<p>You should be redirected to the packages trackback page in 5 seconds. if this does not work, please click <a href="http://pear.php.net/package/'.$id.'/trackbacks">here</a>.</p>';
response_footer();
?>
