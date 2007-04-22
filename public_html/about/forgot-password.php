<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
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
   |          Gregory Beaver <cellog@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/
$errors = array();
if (isset($_POST['resetpass'])) {
    if (!isset($_POST['handle']) || empty($_POST['handle'])) {
        $errors[] = 'Please enter your username';
    }
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        $errors[] = 'Please enter your new password';
    }
    if (!isset($_POST['password2']) || empty($_POST['password2'])) {
        $errors[] = 'Please confirm your new password';
    }
    if (isset($_POST['password']) && isset($_POST['password2'])) {
        if ($_POST['password'] !== $_POST['password2']) {
            $errors[] = 'Passwords do not match';
        }
    }

    include_once 'pear-database-user.php';
    if (array('handle' => $_POST['handle']) != user::info($_POST['handle'], 'handle')) {
        $errors[] = 'Unknown user "' . $_POST['handle'] . '"';
        $_POST['handle'] = '';
    }
    if (!count($errors)) {
        require 'users/passwordmanage.php';
        $manager = new Users_PasswordManage;
        $errors = $manager->resetPassword($_POST['handle'], $_POST['password'],
                                          $_POST['password2']);
        if (!count($errors)) {
            $user = $_POST['handle'];
            require dirname(dirname(dirname(__FILE__))) . '/templates/users/passwordreset.php';
            exit;
        }
    }
}
response_header("PEAR :: Forgot your password?");
$handle = isset($_POST['handle']) ? $_POST['handle'] : '';
require dirname(dirname(dirname(__FILE__))) . '/templates/users/lostpassword.php';
?>