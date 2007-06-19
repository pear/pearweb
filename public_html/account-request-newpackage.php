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
require_once 'Text/CAPTCHA/Numeral.php';

$numeralCaptcha = new Text_CAPTCHA_Numeral();
session_start();

$display_form = true;
$width        = 60;
$errors       = array();
$jumpto       = 'handle';

$stripped = @array_map('strip_tags', $_POST);

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

        if (isset($_POST['purposecheck']) && count($_POST['purposecheck'])) {
            $errors[] = 'The purpose(s) you selected do not require a PEAR account.';
            $display_form = true;
        }

        /**
         * Check if session answer is set, then compare
         * it with the post captcha value. If it's not
         * the same, then it's an incorrect password.
         */
        if (isset($_SESSION['answer']) && strlen(trim($_SESSION['answer'])) > 0) {
            if ($stripped['captcha'] != $_SESSION['answer']) {
                $errors[] = 'Incorrect CAPTCHA';
                $display_form = true;
            }
        }

        if ($dbh->getOne('SELECT count(*) FROM packages WHERE packages.name=?',
              array($_POST['newpackage']))) {
            $errors[] = 'Package "' .
                htmlspecialchars($_POST['newpackage']) . '" already ' .
                'exists, please choose a unique package name';
        }

        if ($errors) {
            break;
        }

        //  The add method performs further validation then creates the acct
        include_once 'pear-database-user.php';
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
                'package'   => $stripped['newpackage'],
            );
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
 You have chosen to request an account for proposing a new (and <strong>complete</strong>)
 package for inclusion in PEAR.
</p>
<p>
 <strong>Before submitting</strong> make sure that you have
 followed all rules concerning PEAR packages.  Especially important are the
 <a href="http://pear.php.net/manual/en/standards.php">PEAR Coding Standards</a>.  Ask
 for help on the $mailto for any questions you might have prior to proposing your package.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>

MSG;

    print '<a name="requestform" id="requestform"></a>';

    report_error($errors);

    $invalid_purposes = array(
        'Propose a new, incomplete package, or an incomplete idea for a package',
        'Browse ' . PEAR_CHANNELNAME . '.'
    );
    $purposechecks = '';
    foreach ($invalid_purposes as $i => $purposeKey)
    {
        $purposechecks .= HTML_Form::returnCheckBox("purposecheck[$i]", @$_POST['purposecheck'][$i] ? 'on' : 'off');
        $purposechecks .= "$purposeKey <br />";
    }

    $form = new HTML_Form('account-request-newpackage.php#requestform', 'post');
    $form->setDefaultFromInput(false);

    $hsc = array_map('htmlspecialchars', $stripped);

    $form->addText('handle', 'Use<span class="accesskey">r</span>name:',
            @$hsc['handle'], 12, 20, 'accesskey="r"');
    $form->addText('firstname', 'First Name:',
            @$hsc['firstname'], 20, null);
    $form->addText('lastname', 'Last Name:',
            @$hsc['lastname'], 20, null);
    $form->addPassword('password', 'Password:', '', 10);
    $form->addPlaintext('Solve the problem:', $numeralCaptcha->getOperation() . ' = 
        <input type="text" size="4" maxlength="4" name="captcha" />');
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    $form->addText('email', 'Email Address:',
            @$hsc['email'], 20, null);
    $form->addCheckbox('showemail', 'Show email address?',
            @$hsc['showemail']);
    $form->addText('newpackage', 'Proposed Package Name:',
            @$hsc['newpackage'], 20, null);
    $form->addPlaintext('Purpose of your PEAR account:'
            . '<p class="cell_note">(Check all that apply)</p>',
            $purposechecks);
    $form->addTextarea('purpose',
            'Short summary of package that you have finished and are ready to propose:',
            @$hsc['purpose'], 40, 5);
    $form->addText('sourcecode',
            'Link to browseable online source code:',
            @$hsc['sourcecode'], 40);
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
