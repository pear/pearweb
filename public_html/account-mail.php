<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2006 The PHP Group                                |
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

/**
 * Send mail to PEAR contributor
 */

/*
 * Redirect to the accounts list if no handle was specified
 */
if (!isset($_GET['handle']) || !ereg('^[0-9a-z_]{2,20}$', $_GET['handle'])) {
    localRedirect('/accounts.php');
} else {
    $handle = $_GET['handle'];
    $errors = array();
}

session_start();

define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');
require_once 'HTML/Form.php';
require_once 'Text/CAPTCHA/Numeral.php';

// {{{ printForm

function printForm($data = array())
{
    // The first field that's empty
    $focus = '';

    foreach (array('name', 'email', 'copy_me', 'subject', 'text') as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
            ($focus == '') ? $focus = $key : '';
        }
    }

    $numeralCaptcha = new Text_CAPTCHA_Numeral();
    $form = new HTML_Form('/account-mail.php?handle=' . htmlspecialchars($_GET['handle']),
                          'post', 'contact');
    $form->setDefaultFromInput(false);

    $form->addText('name', 'Y<span class="accesskey">o</span>ur Name:',
            htmlspecialchars($data['name']), 40, null, 'accesskey="o"');
    $text  = $numeralCaptcha->getOperation() . ' = <input type="text" size="4" maxlength="4" name="captcha" />';
    $form->addPlaintext('Solve the problem:', $text);
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    $form->addText('email', 'Email Address:',
            htmlspecialchars($data['email']), 40, null);
    $form->addCheckBox('copy_me', 'Send me a copy of this mail:',
            htmlspecialchars($data['copy_me']));
    $form->addText('subject', 'Subject:',
            htmlspecialchars($data['subject']), 40, null);
    $form->addTextarea('text', 'Text:',
            htmlspecialchars($data['text']), 35, 10, null);
    $form->addSubmit('submit', 'Submit');
    $form->display('class="form-holder"'
                   . ' cellspacing="1"',
                   'Send Email', 'class="form-caption"');


    echo "<script language=\"JavaScript\">\n";
    echo "<!--\n";
    echo "document.forms.contact." . $focus . ".focus();\n";
    echo "-->\n";
    echo "</script>";
}

// }}}

response_header('Contact');

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE registered = 1 '.
                    'AND handle = ?', array($handle));

if ($row === null) {
    error_handler($handle . ' is not a valid account name.', 'Invalid Account');
}

echo '<h1>Contact ' . $row['name'] . '</h1>';

if (isset($_POST['submit'])) {
    if (!isset($stripped['captcha']) || !isset($_SESSION['answer'])
        || $stripped['captcha'] != $_SESSION['answer']
    ) {
        $errors[] = 'Incorrect CAPTCHA';
    }

    if ($_POST['name'] == '') {
        $errors[] = 'You have to specify your name.';
    } elseif (preg_match('/[\r\n\t]/', $_POST['name'])) {
        $errors[] = 'Your name is invalid.';
    }

    if ($_POST['email'] == '') {
        $errors[] = 'You have to specify your email address.';
    } elseif (preg_match('/[,\s]/', $_POST['email'])) {
        $errors[] = 'Your email address is invalid.';
    }

    if ($_POST['subject'] == '') {
        $errors[] = 'You have to specify the subject of your correspondence.';
    } elseif (preg_match('/[\r\n\t]/', $_POST['subject'])) {
        $errors[] = 'Your subject is invalid.';
    }

    if ($_POST['text'] == '') {
        $errors[] = 'You have to specify the text of your correspondence.';
    }

    if (!report_error($errors)) {
        $text = "[This message has been brought to you via " . PEAR_CHANNELNAME . ".]\n\n";
        $text .= wordwrap($_POST['text'], 72);

        if (@mail($row['email'], $_POST['subject'], $text,
                  'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>',
                  '-f bounce-no-user@php.net'))
        {
            report_success('Your message was successfully sent.');

            if (!empty($_POST['copy_me'])) {
                $text = "This is a copy of your mail sent to " . $row['email'] . ":\n\n"  . $text;

                @mail($_POST['email'], $_POST['subject'], $text,
                      'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>',
                      '-f bounce-no-user@php.net');
            }

        } else {
            report_error('The server could not send your message, sorry.');
        }
    } else {
        printForm($_POST);
    }

} else {
    echo '<p>If you want to get in contact with one of the PEAR contributors,'
        . ' you can do this by filling out the following form.</p>';
    echo '<p style="font-weight: bold; font-size: 110%; color: red;">'
        . 'Do not send email to this developer if you are in need of support for'
        . ' any of his/her package(s), instead we recommend'
        . ' emailing pear-general@lists.php.net where you are more likely to get answer.<br />'
        . ' You can subscribe to the pear-general mailinglist from the ' .
        make_link('/support/lists.php', 'Support - Mailinglist') . ' page.</p>';

    // Guess the user if he is logged in
    if ($auth_user) {
        $data = array('email' => $auth_user->email, 'name' => $auth_user->name);
    } else {
        $data = array();
    }

    printForm($data);
}

response_footer();

?>
