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


require_once 'Damblan/Trackback.php';
require_once 'Damblan/Mailer.php';
require_once 'Damblan/URL.php';

$site = new Damblan_URL;

$params = array('action' => '', 'id' => '');
$site->getElements($params);

$id = htmlentities($params['id']);

// This mostly occurs, when the URL is called by a human instead of a script
if (count($_POST) == 0) {
    PEAR::raiseError("This page is not intended for being viewed in a webbrowser, but to communicate with computer programs.");
}


// Switch error handling, to avoid further PEARWeb style error output
PEAR::setErrorHandling(PEAR_ERROR_RETURN);

// Data sanity check
if (!isset($params['id'])) {
    echo Services_Trackback::getResponseError('No package with ID transmited. Trackback not possible.', 1);
    exit;
}

// Sanity check, see if package exists
include_once 'pear-database-package.php';
$testid = package::info($id, 'id');
if (!$testid || PEAR::isError($testid)) {
    echo Services_Trackback::getResponseError('No package with ID '.$id.' found. Trackback not possible.', 1);
    exit;
}
// Now grab package information
$pkgInfo = package::info($testid);
if (!isset($pkgInfo) || PEAR::isError($pkgInfo)) {
    echo Services_Trackback::getResponseError('No package with ID '.$id.' found. Trackback not possible.', 1);
    exit;
}

if ($pkgInfo['blocktrackbacks']) {
    echo Services_Trackback::getResponseError('Package ' . $id . ' does not allow trackbacks.', 1);
    exit;
}

// Creating new trackback
$trackback = new Damblan_Trackback(array(
    'id' => $id,
    'timestamp' => time(),
));

$res = $trackback->receive();
if (PEAR::isError($res)) {
    echo $res->getMessage();
    echo Services_Trackback::getResponseError('The data you submited was invalid, please recheck.', 1);
    exit;
}

if ($trackback->checkRepost($dbh, TRACKBACK_REPOST_COUNT, TRACKBACK_REPOST_TIMESPAN) !== false) {
    echo Services_Trackback::getResponseError('Only '.TRACKBACK_REPOST_COUNT.' trackbacks from 1 IP address within '.TRACKBACK_REPOST_TIMESPAN.' secs are allowed on this site. Please try again later.', 1);
    exit;
}

$trackback->createSpamCheck('Akismet', $trackback->akismetOptions);

if ($trackback->checkSpam() === true) {
    echo Services_Trackback::getResponseError('Your trackback seems to be spam. If it is not, please contact the webmaster of this site.', 1);
    exit;
}


$res = $trackback->save($dbh);
if (PEAR::isError($res)) {
    echo Services_Trackback::getResponseError('Your trackback could not be saved, please try again or inform the administrator.', 1);
    exit;
}

$mailData = array(
    'id' => $trackback->get('id'),
    'blog_name' => $trackback->get('blog_name'),
    'title' => $trackback->get('title'),
    'url' => $trackback->get('url'),
    'excerpt' => $trackback->get('excerpt'),
    'date' => format_date($trackback->get('timestamp')),
    'timestamp' => $trackback->get('timestamp'),
);

$mailer = Damblan_Mailer::create('Trackback_New', $mailData);

// Get active maintainers only
$additionalHeaders['To'] = $trackback->getMaintainers(true);
if (!DEVBOX) {
    $res = $mailer->send($additionalHeaders);
} else {
    $res = true;
}

if (PEAR::isError($res)) {
    echo Services_Trackback::getResponseError('The notification email for your trackback could not be send. Please inform ' . PEAR_WEBMASTER_EMAIL . '.', 1);
    exit;
}

echo Services_Trackback::getResponseSuccess();

?>
