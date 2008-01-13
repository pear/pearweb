<?php
require 'bugs/pear-bug-accountrequest.php';
require 'election/pear-election-accountrequest.php';

$stripped = @array_map('strip_tags', $_GET);

response_header('Account confirmation');

if (isset($_POST['confirmdetails'])) {
    $request = new PEAR_Bug_Accountrequest;
    if ($request->find($_POST['salt']) && $request->pending()) {
        $salt = $_POST['salt'];
        if (empty($_POST['isMD5'])) {
            $_POST['PEAR_PW']  = md5($_POST['PEAR_PW']);
            $_POST['PEAR_PW2'] = md5($_POST['PEAR_PW2']);
        }

        if (count($errors = $request->validateRequest($_POST['PEAR_USER'], $_POST['PEAR_PW'],
                                       $_POST['PEAR_PW2'], $_POST['name']))) {
            $email = $request->email;
            $name = $_POST['name'];
            $user = '';
            include dirname(dirname(__FILE__)) . '/templates/bugs/registernewaccount.php';
            response_footer();
            exit;
        }
        $errors = $request->confirmRequest($_POST['PEAR_USER'],
            $_POST['PEAR_PW'], $_POST['name']);
        if ($errors === true) {
            report_success('Your account has been activated, bugs you have opened and comments
        you have made are now available to the public for viewing');
            echo '<a href="login.php">Log In</a> to continue.';
            response_footer();
            exit;
        }
        include dirname(dirname(__FILE__)) . '/templates/bugs/registernewaccount.php';
        response_footer();
        exit;
    } else {
        report_error('Unknown account, or account is not pending approval');
    }
}

if (isset($_GET['type']) && $_GET['type'] == 'bug') {
    echo '<h1>Confirm Bug Tracker Email Address</h1>';
    echo '<p>Please choose a username for opening future bugs/adding comments to existing bugs</p>';
    if (!empty($stripped['salt']) && strlen($salt = htmlspecialchars($stripped['salt'])) == 32) {
        $request = new PEAR_Bug_Accountrequest;
        if ($request->find($salt) && $request->pending()) {
            $email = $request->email;
            $user = $name = '';
            $errors = array();
            include dirname(dirname(__FILE__)) . '/templates/bugs/registernewaccount.php';
            response_footer();
            exit;
        } else {
            report_error('Unknown salt');
        }
    } else {
        report_error('Unknown salt');
    }
} else {
    echo '<h1>Confirm Account</h1>';
    if (empty($stripped['salt']) || strlen($salt = htmlspecialchars($stripped['salt'])) != 32) {
        report_error('Unknown salt');
    } else {
        $request = new PEAR_Election_Accountrequest();
        $result = $request->confirmRequest($salt);
        if (PEAR::isError($result)) {
            report_error($result->getMessage());
        } elseif ($result) {
            report_success('Your account has been activated, you can now vote in
        PEAR elections that are for the general PHP public as well as open bugs in the bug tracker');
        } else {
            report_error('There was a problem activating your account, please contact ' . PEAR_WEBMASTER_EMAIL);
        }
    }
}
response_footer();