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

require_once 'HTML/QuickForm.php';
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

        if (!isset($stripped['captcha']) || !isset($_SESSION['answer'])
            || $stripped['captcha'] != $_SESSION['answer']
        ) {
            $errors[] = 'Incorrect CAPTCHA';
            $display_form = true;
        }

        $p = isset($stripped['newpackage']) ? $stripped['newpackage'] : '';
        $package = $dbh->getOne('SELECT count(id) FROM packages WHERE packages.name = ?',
              array($p));
        if ($package) {
            $errors[] = 'Package "' .
                htmlspecialchars($p) . '" already ' .
                'exists, please choose a unique package name';
        }

        if ($errors) {
            break;
        }

        //  The add method performs further validation then creates the account
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
$mailto = '<a href="mailto:' . PEAR_DEV_EMAIL . '">PEAR developers mailing list</a>';
    echo <<<MSG
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

    $form = new HTML_QuickForm('account-request-newpackage', 'post', 'account-request-newpackage.php#requestform');

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

    $hsc = array_map('htmlspecialchars', $stripped);
    // Set defaults for the form elements
    $form->setDefaults(array(
        'handle'        => @$hsc['handle'],
        'firstname'     => @$hsc['firstname'],
        'lastname'      => @$hsc['lastname'],
        'email'         => @$hsc['email'],
        'showemail'     => @$hsc['showemail'],
        'newpackage'    => @$hsc['newpackage'],
        'purpose'       => @$hsc['purpose'],
        'sourcecode'    => @$hsc['sourcecode'],
        'homepage'      => @$hsc['homepage'],
        'moreinfo'      => @$hsc['moreinfo'],
        'comments_read' => @$hsc['comments_read'],
    ));

    $form->addElement('html', '<caption class="form-caption">Request Account</caption>');
    $form->addElement('text', 'handle', 'Use<span class="accesskey">r</span>name:',
            'size="12" maxlength="20" accesskey="r"');
    $form->addElement('text', 'firstname', 'First Name:', array('size' => 30));
    $form->addElement('text', 'lastname', 'Last Name:', array('size' => 30));
    $form->addElement('password', 'password', 'Password:', array('size' => 10));
    $form->addElement('password', 'password2', 'Repeat Password:', array('size' => 10));
    $text  = $numeralCaptcha->getOperation() . ' = <input type="text" size="4" maxlength="4" name="captcha" />';
    $form->addElement('static', null, 'Solve the problem:', $text);
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    $form->addElement('text', 'email', 'Email Address:', array('size' => 20));
    $form->addElement('checkbox', 'showemail', 'Show email address?');
    $form->addElement('text', 'newpackage', 'Proposed Package Name:', array('size' => 20));

    $invalid_purposes = array(
        'Propose a new, incomplete package, or an incomplete idea for a package',
        'Browse ' . PEAR_CHANNELNAME . '.'
    );

    $checkbox = array();
    foreach ($invalid_purposes as $i => $purposeKey) {
        $el = &HTML_QuickForm::createElement('checkbox', $i, null, ' ' . $purposeKey);
        $el->setValue(@$_POST['purposecheck'][$i]);
        $checkbox[] = $el;
    }
    $form->addGroup($checkbox, 'purposecheck', 'Purpose of your PEAR account:'
            . '<p class="cell_note">(Check all that apply)</p>', '<br />');

    $form->addElement('textarea', 'purpose',
            'Short summary of package that you have finished and are ready to propose:',
            array('cols' => 40, 'rows' => 5));
    $form->addElement('text', 'sourcecode', 'Link to browseable online source code:', array('size' => 40));
    $form->addElement('text', 'homepage', 'Homepage:'
            . '<p class="cell_note">(optional)</p>', array('size' => 40));
    $form->addElement('textarea', 'moreinfo',
            'More relevant information about you:'
            . '<p class="cell_note">(optional)</p>',
            array('cols' => 40, 'rows' => 5));
    $form->addElement('checkbox', 'comments_read', 'I have read EVERYTHING on this page:');
    $form->addElement('submit', 'submit', 'Submit Request');
    $form->display();

    if ($jumpto) {
        print "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n";
        print "if (!document.forms[1].$jumpto.disabled) document.forms[1].$jumpto.focus();\n";
        print "\n// -->\n</script>\n";
    }
}

response_footer();