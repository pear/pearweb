<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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
if (!isset($_GET['handle'])) {
    localRedirect('/accounts.php');
} else {
    $handle = $_GET['handle'];
    $errors = array();
}

require_once 'HTML/Form.php';

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

    $th = 'class="form-label_left"';
    $td = 'class="form-input"';

    $form = new HTML_Form($_SERVER['PHP_SELF'] . '?handle=' . $_GET['handle'],
                          'post', 'contact');

    $form->addText('name', 'Y<span class="accesskey">o</span>ur Name:',
            $data['name'], 40, null, 'accesskey="o"',
            $th, $td);
    $form->addText('email', 'Email Address:',
            $data['email'], 40, null, '',
            $th, $td);
    $form->addCheckBox('copy_me', 'Send me a copy of this mail:',
            $data['copy_me'], '',
            $th, $td);
    $form->addText('subject', 'Subject:',
            $data['subject'], 40, null, '',
            $th, $td);
    $form->addTextarea('text', 'Text:',
            $data['text'], 35, 10, null, '',
            $th, $td);
    $form->addSubmit('submit', 'Submit', '',
            $th, $td);
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

    //XXX: Add email validation here
    if ($_POST['name'] == '') {
        $errors[] = 'You have to specify your name.';
    }

    if ($_POST['email'] == '') {
        $errors[] = 'You have to specify your email address.';
    }

    if ($_POST['subject'] == '') {
        $errors[] = 'You have to specify the subject of your correspondence.';
    }

    if ($_POST['text'] == '') {
        $errors[] = 'You have to specify the text of your correspondence.';
    }

    if (!report_error($errors)) {
        $text = "[This message has been brought to you via pear.php.net.]\n\n";
        $text .= wordwrap($_POST['text'], 72);

        if (@mail($row['email'], $_POST['subject'], $text,
                  'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>',
                  '-f pear-sys@php.net'))
        {
            report_success('Your message was successfully sent.');

            if (!empty($_POST['copy_me'])) {
                $text = "This is a copy of your mail sent to " . $row['email'] . ":\n\n"  . $text;

                @mail($_POST['email'], $_POST['subject'], $text,
                      'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>',
                      '-f pear-sys@php.net');
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

    // Guess the user if he is logged in
    if (isset($_COOKIE['PEAR_USER'])) {
        $user =& new PEAR_User($dbh, $_COOKIE['PEAR_USER']);
        $data = array('email' => $user->email, 'name' => $user->name);
    } else {
        $data = array();
    }

    printForm($data);
}

response_footer();

?>
