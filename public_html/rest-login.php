<?php
/**
 * Simple REST-based server for remote authentication
 * 
 * To access, first browse to rest-login.php/getsalt and retrieve a salt plus the
 * session idea from the HTTP response headers.  Then, use the salt to create
 * a new hash of the hashed password and send a POST request to rest-login.php/validate
 * and the response will be returned in plain text.  If the first character returned
 * is "8" then the login succeeded.  1-6 are internal errors, 0 and 7 are invalid logins.
 * 
 * Here is some sample code for a client to access this server:
 * 
 * <code>
 * <?php
 * $user = 'username';
 * $password = 'password';
 * 
 * $salt = file_get_contents('http://pear.php.net/rest-login.php/getsalt');
 * $cookies = array_values(preg_grep('/Set-Cookie:/', $http_response_header));
 * preg_match('/PHPSESSID=(.+); /', $cookies[0], $session);
 * $pass = md5($salt . md5($password));
 * $opts = array('http' => array(
 *     'method' => 'POST',
 *     'header' => 'Cookie: PHPSESSID=' . $session[1] . ';',
 *     'content' => http_build_query(array('username' => $user, 'password' => $pass))
 * ));
 * $context = stream_context_create($opts);
 * var_dump(file_get_contents('http://pear.php.net/rest-login.php/validate', false, $context));
 * ?>
 * </code>
 * @author Gregory Beaver <cellog@php.net>
 * @version $Id$
 * @package pearweb
 */
session_start();
header('Content-type: text/plain');
if (!isset($_SERVER['PATH_INFO']) || empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == '/') {
    die('1 Invalid Remote Login');
}

$info = explode('/', $_SERVER['PATH_INFO']);
switch ($info[1]) {
    case 'getsalt' :
        $salt    = sha1(md5(mt_rand(1, 10000) . time()));
        $_SESSION['salt'] = $salt;
        die($salt);
        break;
    case 'validate' :
        if (!isset($_SESSION['salt'])) {
            die('0 Unknown session');
        }
        $salt = $_SESSION['salt'];
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            die('2 Invalid Remote Login');
        }
        $password = $dbh->getOne('SELECT password from users WHERE handle=?',
            array($_POST['username']));
        if (!$password) {
            die('3 Database Error');
        }
        if (md5($salt . $pass) != $_POST['password']) {
            die('7 Invalid Username or Password');
        }
        die('8 Login OK');
        break;
}