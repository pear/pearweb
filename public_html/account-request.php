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

define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');
require_once 'HTML/Form.php';

$display_form = true;
$width        = 60;
$errors       = array();
$jumpto       = 'handle';

response_header('Request Account');

print '<h1>Request Account</h1>';

do {
    if (isset($_POST['submit'])) {
        if (empty($_POST['comments_read'])) {
            $errors[] = 'Obviously you did not read all the comments'
                      . ' concerning the need for an account. Please read '
                      . 'them again.';
            $display_form = true;
        }

        if (isset($_POST['purposecheck']) && count($_POST['purposecheck'])) {
            $errors[] = 'We could not have said it more clearly. Read '
                      . 'everything on this page and look at the form '
                      . 'you are submitting carefully.';
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
        $ok = user::add($_POST);

        if (!empty($_POST['jumpto'])) {
            $jumpto = $_POST['jumpto'];
        }

        if (isset($_POST['display_form'])) {
            $display_form = $_POST['display_form'];
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
    print <<<MSG
<p>
 You only need to request an account if you:
</p>

<ul>
 <li>
  Are planning to contribute a new package to PEAR and want to propose this
  to the PEAR developer community.
 </li>
 <li>
  Are going to help in the maintenance of an existing package. This needs
  to be approved by the current maintainers of the package or by the <a
  href="/group/">PEAR Group</a>.
 </li>
</ul>

<p>
 If the reason for your request does not fall under one of the
 reasons above, please contact the
 <?php echo make_mailto_link('pear-dev@lists.php.net', 'PEAR developers mailing list'); ?>
</p>

<p>
 You do <strong>not</strong> need an account to:
</p>

<ul>
 <li>
  Download, install and/or use PEAR packages.
 </li>
 <li>
  Submit patches or code improvements for a particular package.
  Please use the <a href="/bugs/">bug reporting system</a> for that purpose.
 </li>
 <li>
  Propose modifications to an existing package. Please use the
  <?php echo make_mailto_link('pear-dev@lists.php.net', 'PEAR developers mailing list'); ?>
  for that or directly contact the maintainers of the package in question.
 </li>
</ul>

<p>
 These are not the only reasons for requests being rejected, but the most
 common ones.
</p>

<p>
 If you want to contribute a package to PEAR, make sure that you have
 followed all rules concerning PEAR packages.  Also, before a package
 may be released, the code code must comply with the
 <a href="http://pear.php.net/manual/en/standards.php">PEAR Coding
 Standards</a>.
</p>

<p>
 Bogus, incomplete or incorrect requests will be summarily denied.
</p>

MSG;

    print '<a name="requestform" id="requestform"></a>';

    report_error($errors);

    $invalid_purposes = array(
        'Learn about PEAR.',
        'Use PEAR.',
        'Download PEAR Packages.',
        'Submit patches/bugs.',
        'Suggest new features.',
        'Browse pear.php.net.'
        );
    $purposechecks = '';
    foreach ($invalid_purposes as $i => $purposeKey)
    {
        $purposechecks .= HTML_Form::returnCheckBox("purposecheck[$i]", @$_POST['purposecheck'][$i] ? 'on' : 'off');
        $purposechecks .= "$purposeKey <br />";
    }

    $form = new HTML_Form($_SERVER['PHP_SELF'] . '#requestform', 'post');

    $form->addText('handle', 'Use<span class="accesskey">r</span>name:',
            @$_POST['handle'], 12, 20, 'accesskey="r"');
    $form->addText('firstname', 'First Name:',
            @$_POST['firstname'], 20, null);
    $form->addText('lastname', 'Last Name:',
            @$_POST['lastname'], 20, null);
    $form->addPassword('password', 'Password:',
            '', 10, null);
    $form->addPlaintext('CAPTCHA:', generate_captcha());
    $form->addText('email', 'Email Address:',
            @$_POST['email'], 20, null);
    $form->addCheckbox('showemail', 'Show email address?',
            @$_POST['showemail']);
    $form->addText('homepage', 'Homepage:',
            @$_POST['homepage'], 20, null);
    $form->addPlaintext('Purpose of your PEAR account:'
            . '<p class="cell_note">(Check all that apply)</p>',
            $purposechecks);
    $form->addTextarea('purpose',
            'If your intended purpose is not in the list, please state it here:',
            stripslashes(@$_POST['purpose']), 40, 5, null);
    $form->addTextarea('moreinfo',
            'More relevant information about you:'
            . '<p class="cell_note">(optional)</p>',
            stripslashes(@$_POST['moreinfo']), 40, 5, null);
    $form->addCheckbox('comments_read',
            'You have read all of the comments above:',
            @$_POST['comments_read']);
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
