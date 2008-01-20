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

require_once 'HTML/QuickForm.php';
require_once 'Text/CAPTCHA/Numeral.php';

$stripped = @array_map('strip_tags', $_POST);

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

    $form = new HTML_QuickForm('contect', 'post', '/account-mail.php?handle=' . htmlspecialchars($_GET['handle']));

    $renderer =& $form->defaultRenderer();
    $renderer->setElementTemplate('
 <tr>
  <th class="form-label_left">
   <!-- BEGIN required --><span style="color: #ff0000">*</span><!-- END required -->
   {label}
  </th>
  <td class="form-input">
   <!-- BEGIN error --><span style="color: #ff0000">{error}</span><br /><!-- END error -->
   {element}
  </td>
 </tr>
');

    $renderer->setFormTemplate('
<form{attributes}>
 <div>
  {hidden}
  <table border="0" class="form-holder" cellspacing="1">
   {content}
  </table>
 </div>
</form>');

    // Set defaults for the form elements
    $form->setDefaults(array(
        'name'    => htmlspecialchars($data['name']),
        'email'   => htmlspecialchars($data['email']),
        'copy_me' => htmlspecialchars($data['copy_me']),
        'subject' => htmlspecialchars($data['subject']),
        'text'    => htmlspecialchars($data['text']),
    ));

    $form->addElement('html', '<caption class="form-caption">Send Email</caption>');
    $form->addElement('text', 'name', 'Y<span class="accesskey">o</span>ur Name:','size="40" accesskey="o"');

    if (!auth_check('pear.dev')) {
        $numeralCaptcha = new Text_CAPTCHA_Numeral();
        $text  = $numeralCaptcha->getOperation() . ' = <input type="text" size="4" maxlength="4" name="captcha" />';
        $form->addElement('static', null, 'Solve the problem:', $text);
        $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    }

    $form->addElement('text', 'email', 'Email Address:', array('size' => 40));
    $form->addElement('checkbox', 'copy_me', 'Send me a copy of this mail:');
    $form->addElement('text', 'subject', 'Subject:', array('size' => 40));
    $form->addElement('textarea', 'text', 'Text:', array('cols' => 35, 'row' => 10));
    $form->addElement('submit', 'submit', 'Send Email');
    $form->display();


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