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
}

$handle = $_GET['handle'];
$errors = array();

session_start();

require_once 'HTML/QuickForm2.php';
/** @todo Remove once these become available in QF2 */
require_once 'HTML/QuickForm2/Element/InputEmail.php';

require_once 'Text/CAPTCHA/Numeral.php';

$stripped = @array_map('strip_tags', $_POST);

// {{{ printForm

function printForm($data = array())
{
    foreach (array('name', 'email', 'copy_me', 'subject', 'text') as $value) {
        if (!isset($data[$value])) {
            $data[$value] = '';
        }
    }

    $form = new HTML_QuickForm2('contect', 'post', array('action' => '/account-mail.php?handle=' . htmlspecialchars($_GET['handle'])));
    $form->removeAttribute('name');

    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
        'name'    => htmlspecialchars($data['name']),
        'email'   => htmlspecialchars($data['email']),
        'copy_me' => htmlspecialchars($data['copy_me']),
        'subject' => htmlspecialchars($data['subject']),
        'text'    => htmlspecialchars($data['text']),
    )));

    $form->addElement('text', 'name', array('required' => 'required'))->setLabel('Y<span class="accesskey">o</span>ur Name:','size="40" accesskey="o"');



    $form->addElement('email', 'email', array('required' => 'required'))->setLabel('Email Address:');
    $form->addElement('checkbox', 'copy_me')->setLabel('CC me?:');
    $form->addElement('text', 'subject', array('required' => 'required', 'size' => '80'))->setLabel('Subject:');
    $form->addElement('textarea', 'text', array('cols' => 80, 'rows' => 10, 'required' => 'required'))->setLabel('Text:');
    $form->addElement('submit', 'submit')->setLabel('Send Email');

    if (!auth_check('pear.dev')) {
        $numeralCaptcha = new Text_CAPTCHA_Numeral();
        $form->addElement('text', 'captcha', array('maxlength' => 4, 'required' => 'required'))->setLabel("What is " . $numeralCaptcha->getOperation() . ' = ');
        $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    }

    print $form;
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
    if (!auth_check('pear.dev') && (!isset($stripped['captcha']) || !isset($_SESSION['answer'])
        || $stripped['captcha'] != $_SESSION['answer'])
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
        . ' emailing ' . PEAR_GENERAL_EMAIL . ' where you are more likely to get answer.<br />'
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
