<?php
/**
 * Handles patch upload.
 * When errors occur, $errors is filled.
 * In case everything is ok, we redirect to uploaded patch detail page
 */
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

if (!isset($_POST['obsoleted'])) {
    $_POST['obsoleted'] = array();
}

$email = isset($_POST['email']) ? $_POST['email'] : '';

if (!isset($_POST['patchname']) || empty($_POST['patchname'])
    || !is_string($_POST['patchname'])
) {
    $package = $buginfo['package_name'];
    //yep, both needed
    $_POST['patchname'] = '';
    $patchname = '';
    $patches   = $patchinfo->listPatches($id);
    $errors[]  = 'No patch name entered';
    $captcha   = $numeralCaptcha->getOperation();
    return;
}

if (!$loggedin) {
    try {
        $errors = array();
        if (empty($_POST['email'])) {
            $errors[] = 'Email address must be valid!';
        }
        $preg = "/^[.\\w+-]+@[.\\w-]+\\.\\w{2,}\z/i";
        if (!preg_match($preg,$_POST['email'])) {
            $errors[] = 'Email address must be valid!';
        }
        /**
         * Check if session answer is set, then compare
         * it with the post captcha value. If it's not
         * the same, then it's an incorrect password.
         */
        if (isset($_SESSION['answer'])
            && strlen(trim($_SESSION['answer'])) > 0
        ) {
            if ($_POST['captcha'] != $_SESSION['answer']) {
                $errors[] = 'Incorrect Captcha';
            }
        }
        if (count($errors)) {
            throw new Exception('');
        }
        // user doesn't exist yet
        require_once 'bugs/pear-bug-accountrequest.php';
        $buggie = new PEAR_Bug_Accountrequest();
        $salt = $buggie->addRequest($_POST['email']);
        if (is_array($salt)) {
            $errors = $salt;
            return;
        }
        if (PEAR::isError($salt)) {
            $errors[] = $salt;
            return;
        }
        if ($salt === false) {
            $errors[] = 'Your account cannot be added to the queue.'
                . ' Please write a mail message to the '
                . ' <i>pear-dev</i> mailing list.';
            return;
        }

        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $e = $patchinfo->attach(
            $id, 'patch', $_POST['patchname'],
            $buggie->handle, $_POST['obsoleted']
        );
        PEAR::popErrorHandling();
        if (PEAR::isError($e)) {
            $buggie->deleteRequest();
            $package = $buginfo['package_name'];
            if (!is_string($_POST['patchname'])) {
                $_POST['patchname'] = '';
            }
            $patchname = $_POST['patchname'];
            $patches = $patchinfo->listPatches($id);
            $errors[] = $e->getMessage();
            $errors[] =
                'Could not attach patch "' .
                htmlspecialchars($patchname) .
                '" to Bug #' . $id;
            $captcha = $numeralCaptcha->getOperation();
            $_SESSION['answer'] = $numeralCaptcha->getAnswer();
            return;
        }

        try {
            $buggie->sendEmail();
        } catch (Exception $e) {
            $errors[] = 'Patch was successfully attached, but account confirmation email not sent, please report to ' .  PEAR_DEV_EMAIL;
            return;
        }
        localRedirect('/bugs/bug.php?id=' . $id . '&edit=12&patch=' .
                      urlencode($_POST['patchname']) . '&revision=' . $e);
        exit;
    } catch (Exception $e) {
        $package = $buginfo['package_name'];
        if (!is_string($_POST['patchname'])) {
            $_POST['patchname'] = '';
        }
        $patchname = $_POST['patchname'];
        $patches   = $patchinfo->listPatches($id);
        $captcha   = $numeralCaptcha->getOperation();
        $_SESSION['answer'] = $numeralCaptcha->getAnswer();
        return;
    }
}

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
$e = $patchinfo->attach(
    $id, 'patch', $_POST['patchname'],
    $auth_user->handle, $_POST['obsoleted']
);
PEAR::popErrorHandling();
if (PEAR::isError($e)) {
    $package = $buginfo['package_name'];
    if (!is_string($_POST['patchname'])) {
        $_POST['patchname'] = '';
    }
    $patchname = $_POST['patchname'];
    $patches   = $patchinfo->listPatches($id);
    $errors    = array(
        $e->getMessage(),
        'Could not attach patch "' .
        htmlspecialchars($patchname) . '" to Bug #' . $id
    );
    $captcha = $numeralCaptcha->getOperation();
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    return;
}

// {{{ Email after the patch is added and add a comment to the bug report.
if (!isset($buggie)) {
    $patchname = $_POST['patchname'];
    $url       = "bug.php?id=$id&edit=12&patch=$patchname&revision=$e";
    $bugurl    ='http://' . PEAR_CHANNELNAME . '/bugs/' . $url;
    // Add a comment about this in the bug report
    $text = <<<TXT
The following patch has been added/updated:
 Patch Name:  $patchname
 Revision:    $e
 URL:         $bugurl
TXT;

    $query = 'INSERT INTO bugdb_comments' .
        ' (bug, email, ts, comment, reporter_name, handle) VALUES (?, ?, NOW(), ?, ?, ?)';
    $dbh->query(
        $query,
        array(
            $id, $auth_user->email, $text,
            $auth_user->name, $auth_user->handle
        )
    );

    /**
     * Email the package maintainers/leaders about
     * the new patch added to their bug request.
     */
    require_once 'bugs/pear-bugs-utils.php';
    $patch = array(
        'patch'        => $patchname,
        'bug_id'       => $id,
        'revision'     => $e,
        'package_name' => $buginfo['package_name'],
    );
    $res = PEAR_Bugs_Utils::sendPatchEmail($patch);

    if (PEAR::isError($res)) {
        // Patch not sent. Let's handle it here but not now..
    }
}
// }}}
localRedirect(
    '/bugs/bug.php'
    . '?id=' . $id
    . '&edit=12'
    . '&patch=' . urlencode($_POST['patchname'])
    . '&revision=' . $e
    . '&thanks=13'
);
//don't execute rest of script
exit();
?>
