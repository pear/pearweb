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
   | Authors: Martin Jansen <mj@php.net>                                  |
   |          Tomas V.V.Cox <cox@idecnet.com>                             |
   +----------------------------------------------------------------------+
   $Id$
*/
require_once 'Damblan/Trackback.php';
require_once 'Damblan/Mail.php';
require_once 'Damblan/URL.php';
$site = new Damblan_URL;


// {{{ setup, queries

$params = array('action' => '', 'id' => '');
$site->getElements($params);

$id = htmlentities($params['id']);
// Sanity check, if package exists
$pkgInfo = package::info($id, 'packageid');
if (!isset($pkgInfo) || PEAR::isError($pkgInfo)) {
    echo Services_Trackback::getResponseError('No package with ID '.$id.' found. Trackback not possible.', 1);
    exit;
}

// Creating new trackback
$trackback = new Damblan_Trackback(array('id' => $id), time());
$res = $trackback->receive();
if (PEAR::isError($res)) {
    echo Services_Trackback::getResponseError('The data you submited was invalid, please recheck.', 1);
    exit;
}

$res = $trackback->save($dbh);
if (PEAR::isError($res)) { 
    echo Services_Trackback::getResponseError('Your trackback could not be saved, please try again or inform the administrator.', 1);
    exit;
}

$mailData = array(
    'id' => $trackback->id,
    'blog_name' => $trackback->blog_name,
    'title' => $trackback->title,
    'url' => $trackback->url,
    'excerpt' => $trackback->excerpt,
    'date' => make_utc_date($trackback->timestamp),
    'timestamp' => $trackback->timestamp,
);

$mailer = Damblan_Mail::create('Trackback_New', $mailData);

$maintainers = maintainer::get($trackback->id, true);
$additionalHeaders = array(
    'To' => array()
);
foreach ($maintainers as $maintainer => $data) {
    $tmpUser = user::info($maintainer, 'email');
    if (empty($tmpUser['email'])) {
        continue;
    }
    $additionalHeaders['To'][] = $tmpUser['email'];
}
$res = $mailer->send($additionalHeaders);

if (PEAR::isError($res)) {
    echo Services_Trackback::getResponseError('The notification email for your trackback could not be send. Please inform pear-webmaster@lists.php.net.', 1);
    exit;
}

echo Services_Trackback::getResponseSuccess();

?>
