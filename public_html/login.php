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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/*
 * If the PHPSESSID cookie isn't set, the user MAY have cookies turned off.
 * To figure out cookies are REALLY off, check to see if the person came
 * from within the PEAR website or just submitted the login form.
 */
// when using cgi, a warning is always sent saying the cookie headers couldn't be sent
// there is no way around this.
@session_start();


// If they're already logged in, say so.
if (isset($auth_user) && $auth_user) {
    response_header('Login');
    echo '<div class="warnings">You are already logged in.</div>';
    response_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['PEAR_USER']) || empty($_POST['PEAR_PW'])) {
        auth_reject(PEAR_AUTH_REALM, 'You must provide a username and a password.');
    }
} else {
    auth_reject(PEAR_AUTH_REALM, '');
}

$password = !empty($_POST['isMD5']) ? $_POST['PEAR_PW'] : md5($_POST['PEAR_PW']);

if (auth_verify($_POST['PEAR_USER'], $password)) {
    $expire = !empty($_POST['PEAR_PERSIST']) ? 2147483647 : 0;
    setcookie('PEAR_USER', $_POST['PEAR_USER'], $expire, '/');
    setcookie('PEAR_PW', $password, $expire, '/');

    // mark user as active if they were inactive
    $dbh->query('UPDATE users SET active = 1 WHERE handle = ?', array($_POST['PEAR_USER']));

    // Determine URL
    if (isset($_POST['PEAR_OLDURL'])
        && basename($_POST['PEAR_OLDURL']) != 'login.php'
        && !preg_match('|://|', $_POST['PEAR_OLDURL']))
    {
        localRedirect($_POST['PEAR_OLDURL']);
    } else {
        localRedirect('/index.php');
    }
    exit;

}

$msg = '';
if (isset($_POST['PEAR_USER']) || isset($_POST['PEAR_PW'])) {
    $msg = 'Invalid username or password.';
}

auth_reject(PEAR_AUTH_REALM, $msg);
