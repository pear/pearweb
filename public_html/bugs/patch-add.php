<?php
if (!isset($id)) {
    header('HTTP/1.0 400 Bad Request');
    exit();
}

$canpatch = true;

require_once 'Text/CAPTCHA/Numeral.php';
$numeralCaptcha = new Text_CAPTCHA_Numeral();

$patchinfo = new Bugs_Patchtracker();
// captcha is not necessary if the user is logged in
if (isset($auth_user) && $auth_user->registered) {
    auth_require('pear.dev', 'pear.bug');
    if (isset($_SESSION['answer'])) {
        unset($_SESSION['answer']);
    }
}

if (PEAR::isError($buginfo = $patchinfo->getBugInfo($id))) {
    response_header('Error :: invalid bug selected');
    report_error('Invalid bug "' . $id . '" selected');
    response_footer();
    exit;
}

$loggedin = isset($auth_user) && $auth_user->registered;

if (!isset($_POST['addpatch'])) {
    /**
     * Normal patch form with predefined variables
     */
    $email     = isset($_GET['email']) ? $_GET['email'] : '';
    $errors    = array();
    $package   = $buginfo['package_name'];
    $bug       = $buginfo['id'];
    $patchname = isset($_GET['patchname']) ? $_GET['patchname'] : '';
    $captcha   = $numeralCaptcha->getOperation();
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
}
$patches = $patchinfo->listPatches($id);
include PEARWEB_TEMPLATEDIR. '/bugs/addpatch.php';
?>
