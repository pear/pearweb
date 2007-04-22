<?php
$errors = array();
response_header('PEAR :: Confirm Password Change');
if (!isset($_POST['confirm'])) {
    $handle = '';
    require dirname(dirname(dirname(__FILE__))) . '/templates/users/passwordform.php';
    exit;
} else {
    $errors = array();
    if (!isset($_POST['handle']) || empty($_POST['handle'])) {
        $errors[] = 'Please enter Username';
    } else {
        include_once 'pear-database-user.php';
        if (array('handle' => $_POST['handle']) != user::info($_POST['handle'], 'handle')) {
            $errors[] = 'Unknown user "' . $_POST['handle'] . '"';
        }
    }
    if (!isset($_POST['resetcode']) || empty($_POST['resetcode'])) {
        $errors[] = 'Please enter Password reset code from the email you received';
    }
    if (count($errors)) {
        $handle = $_POST['handle'];
        require dirname(dirname(dirname(__FILE__))) . '/templates/users/passwordform.php';
        exit;
    }
}
require_once 'users/passwordmanage.php';
$manager = new Users_PasswordManage;
$errors = $manager->confirmReset($_POST['handle'], $_POST['resetcode']);
if (count($errors)) {
    $handle = $_POST['handle'];
    require dirname(dirname(dirname(__FILE__))) . '/templates/users/passwordform.php';
    exit;
}
$user = $_POST['handle'];
require dirname(dirname(dirname(__FILE__))) . '/templates/users/passwordresetconfirmed.php';
?>