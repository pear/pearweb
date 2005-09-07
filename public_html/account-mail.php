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

/**
 * Send mail to PEAR contributor
 */
/*
 * Redirect to the accounts list if no handle was specified
 */

$handle = isset($_GET['handle']) ? $_GET['handle'] : false;

if ($handle && !ereg('^[0-9a-z_]{3,20}$', $handle)) {
    localRedirect('/accounts.php');
}

/* 
 * HTML_Form accesses $_GET/$_POST directly and does no filtering, so we need to
 * do this.  Easier to do once and for all at the top than try to track them
 */
$allowed_fields = array('handle' => null, 'email' => null, 'name' => null, 'copy_me' => null, 'subject' => null, 'text' => null, 'captcha' => null);

$input_data = $allowed_fields;
if (isset($_POST['submit'])) {
    $mode_submit = true;
    foreach ($allowed_fields as $field => $v) {
        $input_data[$field] = isset($_POST[$field]) ? strip_tags($_POST[$field]) : '';
    }
    // Some other casts
    $input_data['copy_me'] = (int) $input_data['copy_me'];
    if (strlen($input_data['text'])) {
        $input_data['text'] = htmlentities(strip_tags($input_data['text']));
    }
    if (!empty($input_data['captcha']) && !ereg('^[A-Za-z]{4}$', $input_data['captcha'])) {
        $input_data['captcha'] = '';
    } 
} else {
    $mode_submit = false;
}

// Rewrite _POST with input_data
// I do not like that but HTML_Form does not allow
// to pass custom data
$_POST = &$input_data;
$HTTP_POST_VARS = &$input_data;
$errors = array();

define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');
require_once 'HTML/Form.php';

// {{{ printForm
function printForm($data = array(), $handle = '') 
{
    // The first field that's empty
    $focus = '';

    foreach (array('name', 'email', 'copy_me', 'subject', 'text') as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
            ($focus == '') ? $focus = $key : '';
        }
    }

    $form = new HTML_Form('/account-mail.php?handle=' . $handle,
                          'post', 'contact');

    $form->addText('name', 'Y<span class="accesskey">o</span>ur Name:',
            $data['name'], 40, null, 'accesskey="o"');
    $form->addPlaintext('CAPTCHA:', generate_captcha());
    $form->addText('email', 'Email Address:',
            $data['email'], 40, null);
    $form->addCheckBox('copy_me', 'Send me a copy of this mail:',
            $data['copy_me']);
    $form->addText('subject', 'Subject:',
            $data['subject'], 40, null);
    $form->addTextarea('text', 'Text:',
            $data['text'], 35, 10, null);
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

if ($mode_submit) {

    if (!validate_captcha()) {
        $errors[] = 'Incorrect CAPTCHA';
    }

    if ($input_data['name'] == '') {
        $errors[] = 'You have to specify your name.';
    } elseif (preg_match('/[\r\n\t]/', $input_data['name'])) {
        $errors[] = 'Your name is invalid.';
    }

    if ($input_data['email'] == '') {
        $errors[] = 'You have to specify your email address.';
    } elseif (preg_match('/[,\s]/', $input_data['email'])) {
        $errors[] = 'Your email address is invalid.';
    }

    if ($input_data['subject'] == '') {
        $errors[] = 'You have to specify the subject of your correspondence.';
    } elseif (preg_match('/[\r\n\t]/', $input_data['subject'])) {
        $errors[] = 'Your subject is invalid.';
    }

    if ($input_data['text'] == '') {
        $errors[] = 'You have to specify the text of your correspondence.';
    }

    if (!report_error($errors)) {
        $text = "[This message has been brought to you via " . PEAR_CHANNELNAME . ".]\n\n";
        $text .= wordwrap($input_data['text'], 72);

        if (@mail($row['email'], $input_data['subject'], $text,
                  'From: "' . $input_data['name'] . '" <' . $input_data['email'] . '>',
                  '-f pear-sys@php.net'))
        {
            report_success('Your message was successfully sent.');

            if (!empty($input_data['copy_me'])) {
                $text = "This is a copy of your mail sent to " . $row['email'] . ":\n\n"  . $text;

                @mail($input_data['email'], $input_data['subject'], $text,
                      'From: "' . $input_data['name'] . '" <' . $input_data['email'] . '>',
                      '-f pear-sys@php.net');
            }

        } else {
            report_error('The server could not send your message, sorry.');
        }
    } else {
        printForm($input_data, $handle);
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
    if (isset($_COOKIE['PEAR_USER'])) {
        $user =& new PEAR_User($dbh, $_COOKIE['PEAR_USER']);
        $data = array('email' => $user->email, 'name' => $user->name);
    } else {
        $data = array();
    }

    printForm($input_data, $handle);
}

response_footer();
?>
