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

define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');
require_once 'HTML/Form.php';
require_once 'Damblan/Mailer.php';

$display_form = true;
$width        = 60;
$errors       = array();
$jumpto       = 'handle';

$stripped = @array_map('strip_tags', $_POST);

// CAPTCHA needs it and we cannot start it in the
// CAPTCHA function, too much mess around here.
session_start();

response_header('Request Account');

print '<h1>Request Account</h1>';

do {
    if (isset($stripped['submit'])) {

        if (empty($stripped['handle'])
            || !ereg('^[0-9a-z_]{2,20}$', $stripped['handle']))
        {
            $errors[] = 'Username is invalid.';
            $display_form = true;
        }

        if (empty($stripped['comments_read'])) {
            $errors[] = 'Obviously you did not read all the comments'
                      . ' concerning the need for an account. Please read '
                      . 'them again.';
            $display_form = true;
        }

        if (!validate_captcha()) {
            $errors[] = 'Incorrect CAPTCHA';
            $display_form = true;
        }

        if ($errors) {
            break;
        }

        //  The add method performs further validation then creates the acct
        $ok = user::add($stripped);

        if (!empty($stripped['jumpto'])) {
            $jumpto = $stripped['jumpto'];
        }

        if (isset($stripped['display_form'])) {
            $display_form = $stripped['display_form'];
        }

        if (is_array($ok)) {
            $errors = $ok;
            break;
        } elseif ($ok === true) {
            report_success('Your account request has been submitted, it will'
                  . ' be reviewed by a human shortly.  This may take from'
                  . ' two minutes to several days, depending on how much'
                  . ' time people have.'
                  . ' You will get an email when your account is open,'
                  . ' or if your request was rejected for some reason.');

            $mailData = array(
                'username'  => $stripped['handle'],
                'firstname' => $stripped['firstname'],
                'lastname'  => $stripped['lastname'],
            );

            if (!DEVBOX) {
                $mailer = Damblan_Mailer::create('pearweb_account_request_vote', $mailData);
                $additionalHeaders['To'] = '"Arnaud Limbourg" <arnaud@limbourg.com>';
                $mailer->send($additionalHeaders);
            }
        } elseif ($ok === false) {
            $msg = 'Your account request has been submitted, but there'
                 . ' were problems mailing one or more administrators.'
                 . ' If you don\'t hear anything about your account in'
                 . ' a few days, please drop a mail about it to the'
                 . ' <i>pear-dev</i> mailing list.';
            report_error($msg, 'warnings', 'WARNING:');
        }

        $display_form = false;
    }
} while (0);


if ($display_form) {
$mailto = make_mailto_link('pear-dev@lists.php.net', 'PEAR developers mailing list');
    print <<<MSG
<h1>PLEASE READ THIS BEFORE SUBMITTING!</h1>
<p>
 You have chosen to request an account in order to vote in a general PEAR election.
</p>
<p>
 This account will be restricted only to voting in allowed elections, none of the other
 developer privileges apply, including proposing a new package for inclusion in PEAR.
 If you wish to propose a new (and <strong>complete</strong>) package for inclusion
 in PEAR, please use the <a href="/account-request-newpackage.php">New Package Account
 Request Form</a>.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>

MSG;

    print '<a name="requestform" id="requestform"></a>';

    report_error($errors);

    $form = new HTML_Form('account-request-vote.php#requestform', 'post');
    $form->setDefaultFromInput(false);

    $hsc = array_map('htmlspecialchars', $stripped);

    $form->addText('handle', 'Use<span class="accesskey">r</span>name:',
            @$hsc['handle'], 12, 20, 'accesskey="r"');
    $form->addText('firstname', 'First Name:',
            @$hsc['firstname'], 20, null);
    $form->addText('lastname', 'Last Name:',
            @$hsc['lastname'], 20, null);
    $form->addPassword('password', 'Password:', '', 10);
    $form->addPlaintext('CAPTCHA:', generate_captcha());
    $form->addText('email', 'Email Address:',
            @$hsc['email'], 20, null);
    $form->addCheckbox('showemail', 'Show email address?',
            @$hsc['showemail']);
    $form->addText('homepage', 'Homepage:'
            . '<p class="cell_note">(optional)</p>',
            @$hsc['homepage'], 20, null);
    $form->addTextarea('moreinfo',
            'More relevant information about you:'
            . '<p class="cell_note">(optional)</p>',
            @$hsc['moreinfo'], 40, 5);
    $form->addCheckbox('comments_read',
            'I have read EVERYTHING on this page:',
            @$hsc['comments_read']);
    $form->addSubmit('submit', 'Submit Query');

    $form->display('class="form-holder" cellspacing="1"',
                   'Request Account', 'class="form-caption"');

    if ($jumpto) {
        print "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n";
        print "if (!document.forms[1].$jumpto.disabled) document.forms[1].$jumpto.focus();\n";
        print "\n// -->\n</script>\n";
    }
}

response_footer();

?>
