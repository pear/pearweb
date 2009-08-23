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

function auth_reject($realm = null, $message = null)
{
    if ($realm === null) {
        $realm = PEAR_AUTH_REALM;
    }
    if ($message === null) {
        $message = 'Please enter your username and password:';
    }

    response_header('Login');
    $GLOBALS['ONLOAD'] = 'document.login.PEAR_USER.focus();';
    if ($message) {
        report_error($message);
    }

    $action = DEVBOX ? '/login.php' : 'https://' . $_SERVER['SERVER_NAME'] . '/login.php';

    if (isset($_GET['redirect']) && is_string($_GET['redirect']) &&
          !strpos($_GET['redirect'], '://')) {
        $redirect = htmlspecialchars(urldecode($_GET['redirect']));
    } elseif (isset($_POST['PEAR_OLDURL']) && is_string($_POST['PEAR_OLDURL']) &&
          !strpos($_POST['PEAR_OLDURL'], '://')) {
        $redirect = htmlspecialchars($_POST['PEAR_OLDURL']);
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $redirect = htmlspecialchars($_SERVER['REQUEST_URI']);
    } else {
        $redirect = 'login.php';
    }
    $channelname = PEAR_CHANNELNAME;
echo <<<HTML
    <script type="text/javascript" src="/javascript/md5.js"></script>
    <script type="text/javascript">
    function doMD5(frm) {
        frm.PEAR_PW.value = hex_md5(frm.PEAR_PW.value);
        frm.isMD5.value = 1;
    }
    </script>
    <form onsubmit="javascript:doMD5(document.forms['login'])" name="login" action="$action" method="post">
    <input type="hidden" name="isMD5" value="0" />
    <table class="form-holder" cellspacing="1">
     <tr>
      <th class="form-label_left">
    Use<span class="accesskey">r</span>name or email address:</th>
      <td class="form-input">
    <input size="20" name="PEAR_USER" accesskey="r" type="text" /></td>
     </tr>
     <tr>
      <th class="form-label_left">Password:</th>
      <td class="form-input">
    <input size="20" name="PEAR_PW" type="password" /></td>
     </tr>
     <tr>
      <th class="form-label_left">&nbsp;</th>
      <td class="form-input" style="white-space: nowrap">
    <input type="checkbox" name="PEAR_PERSIST" value="on" id="pear_persist_chckbx" />
    <label for="pear_persist_chckbx">Remember username and password.</label></td>
     </tr>
     <tr>
      <th class="form-label_left">&nbsp;</th>
      <td class="form-input"><input type="submit" value="Log in!" /></td>
     </tr>
    </table>
    <input type="hidden" name="PEAR_OLDURL" value="$redirect" />
    </form>
    <p><strong>Note:</strong> If you just want to browse the website,
    you will not need to log in. For all tasks that require
    authentication, you will be redirected to this form
    automatically. You can sign up for an account
    <a href="/account-request.php">over here</a>.</p>
    <p>If you forgot your password, instructions for resetting
    it can be found on a <a href="https://$channelname/about/forgot-password.php">
    dedicated page</a>.</p>
HTML;
    response_footer();
    exit;
}

function auth_verify($user, $passwd)
{
    global $dbh, $auth_user;

    if (empty($auth_user)) {
        include_once 'pear-database-user.php';
        $data = user::info($user, null, true, false);
        $auth_user = new PEAR_Auth();
        $auth_user->data($data);
    }

    if (!isset($auth_user->password)) {
        $auth_user->password = '';
    }

    $error = '';
    $ok = false;

    // Check if the passwd is already md5()ed
    if (preg_match('/^[a-z0-9]{32}\z/', $passwd)) {
        $crypted = $passwd;
    } else {
        $crypted = md5($passwd);
    }

    if ($crypted == $auth_user->password) {
        $ok = true;
    } else {
        $error = "pear-auth: user `$user': invalid password (md5)";
    }

    if (empty($auth_user->registered)) {
        if ($user) {
            $error = "pear-auth: user `$user' not registered";
        }
        $ok = false;
    }
    if ($ok) {
        $auth_user->_readonly = true;
        return auth_check("pear.user");
    }

    if ($error) {
        error_log("$error\n", 3, PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
    }
    $auth_user = null;
    return false;
}

function auth_check($atom)
{
    global $dbh;
    static $karma;

    require_once "Damblan/Karma.php";

    global $auth_user;

    if (!isset($auth_user)) {
        return false;
    }
    // Check for backwards compatibility
    if (is_bool($atom)) {
        $atom = $atom === true ? 'pear.admin' : 'pear.dev';
    }

    if (!isset($karma)) {
        $karma = new Damblan_Karma($dbh);
    }
    return $karma->has($auth_user->handle, $atom);
}

function auth_require()
{
    global $auth_user;
    $res = true;

    $user   = @$_COOKIE['PEAR_USER'];
    $passwd = @$_COOKIE['PEAR_PW'];
    if (!auth_verify($user, $passwd)) {
        auth_reject(); // exits
    }

    $num = func_num_args();
    for ($i = 0; $i < $num; $i++) {
        $arg = func_get_arg($i);
        $res = auth_check($arg);

        if ($res === true) {
            return true;
        }
    }

    if ($res === false) {
        response_header("Insufficient Privileges");
        report_error("Insufficient Privileges");
        response_footer();
        exit;
    }

    return true;
}

function auth_kill_cookies()
{
    if (isset($_COOKIE['PEAR_USER'])) {
        setcookie('PEAR_USER', '', 0, '/');
        unset($_COOKIE['PEAR_USER']);
    }
    if (isset($_COOKIE['PEAR_PW'])) {
        setcookie('PEAR_PW', '', 0, '/');
        unset($_COOKIE['PEAR_PW']);
    }
}

/**
 * Perform logout for the current user
 */
function auth_logout()
{
    auth_kill_cookies();
    $redirect = $_SERVER['PHP_SELF'];
    if ($_SERVER['QUERY_STRING'] != 'logout=1') {
        $redirect .= '?' . preg_replace('/logout=1/', '', $_SERVER['QUERY_STRING']);
    }
    localRedirect($redirect);
}

/**
 * setup the $auth_user object
 */
function init_auth_user()
{
    global $auth_user, $dbh;
    if (empty($_COOKIE['PEAR_USER']) || empty($_COOKIE['PEAR_PW'])) {
        $auth_user = null;
        return false;
    }
    if (!empty($auth_user)) {
        return true;
    }
    require_once 'pear-database-user.php';
    $data = user::info($_COOKIE['PEAR_USER'], null, true, false);
    $auth_user = new PEAR_Auth();
    $auth_user->data($data);

    if (md5($_COOKIE['PEAR_PW']) == @$auth_user->password) {
        return true;
    }

    $auth_user = null;
    return false;
}

class PEAR_Auth
{
    function data($data)
    {
        if (!is_array($data)) {
            return;
        }
        foreach ($data as $k => $d) {
            $this->{$k} = $d;
        }
    }

    function is($handle)
    {
        global $auth_user;

        if (isset($auth_user) && $auth_user) {
            $ret = strtolower($auth_user->handle);
        } elseif (isset($this->handle)) {
            $ret = strtolower($this->handle);
        } else {
            $ret = false;
        }
        return (strtolower($handle) == $ret);
    }

    function isAdmin()
    {
        if (!isset($this->handle)) {
            return false;
        }
        require_once 'pear-database-user.php';
        return user::isAdmin($this->handle);
    }

    function isQA()
    {
        if (!isset($this->handle)) {
            return false;
        }
        require_once 'pear-database-user.php';
        return user::isQA($this->handle);
    }

    /**
     * Generate link for user
     *
     * @access public
     * @return string
     */
    function makeLink()
    {
        if (!isset($this->handle) || !isset($this->name)) {
            throw new Exception('Programmer error: please report to ' . PEAR_DEV_EMAIL .
                '. $auth_user not initialized with data()');
        }
        return '<a href="/user/' . $this->handle . '/">' . htmlspecialchars($this->name)
            . '</a>';
    }
}
