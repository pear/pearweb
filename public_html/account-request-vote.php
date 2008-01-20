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
require_once 'election/pear-election-accountrequest.php';
require_once 'Text/CAPTCHA/Numeral.php';

$numeralCaptcha = new Text_CAPTCHA_Numeral();
session_start();

$display_form = true;
$width        = 60;
$errors       = array();
$jumpto       = 'handle';

$stripped = @array_map('strip_tags', $_POST);

response_header('Request Account');

echo '<h1>Request Account</h1>';

do {
    if (isset($stripped['submit'])) {

        if (empty($stripped['handle'])
            || !preg_match('/^[0-9a-z_]{2,20}\\z/', $stripped['handle']))
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

        if (!isset($stripped['captcha']) || !isset($_SESSION['answer'])
            || $stripped['captcha'] != $_SESSION['answer']
        ) {
            $errors[] = 'Incorrect CAPTCHA';
            $display_form = true;
        }

        if ($errors) {
            break;
        }

        $request = new PEAR_Election_Accountrequest();
        $salt = $request->addRequest($stripped['handle'], $stripped['email'],
            $stripped['firstname'], $stripped['lastname'], $stripped);

        if (PEAR::isError($salt)) {
            $errors[] = 'Database error (e.g. email address already registered).';
            $display_form = true;
            break;
        }

        if (!empty($stripped['jumpto'])) {
            $jumpto = $stripped['jumpto'];
        }

        if (isset($stripped['display_form'])) {
            $display_form = $stripped['display_form'];
        }

        if (is_array($salt)) {
            $errors = $salt;
            break;
        } elseif (strlen($salt) == 32) {
            report_success('Your account request confirmation has been submitted. '
                  . ' You must follow the link provided in the email '
                  . ' in order to activate your account.'
                  . ' Until this is done you cannot vote in any election.');

            $mailData = array(
                'username'  => $stripped['handle'],
                'salt' => $salt,
            );

            if (!DEVBOX) {
                $mailer = Damblan_Mailer::create('pearweb_account_request_vote', $mailData);
                $additionalHeaders['To'] = $stripped['email'];
                $mailer->send($additionalHeaders);
            }
        }

        $display_form = false;
    }
} while (0);


if ($display_form) {
$mailto = '<a href="mailto:' . PEAR_DEV_EMAIL . '">PEAR developers mailing list</a>';
    echo <<<MSG
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
 Note that this account can also be used to report a bug or comment on an existing bug.
</p>

<p>
Please use the &quot;latin counterparts&quot; of non-latin characters (for instance th instead of &thorn;).
</p>

MSG;

    echo '<a name="requestform" id="requestform"></a>';

    report_error($errors);

    $form = new HTML_QuickForm('account-request-vote', 'post', 'account-request-vote.php#requestform');

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
        echo "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n";
        echo "if (!document.forms[1].$jumpto.disabled) document.forms[1].$jumpto.focus();\n";
        echo "\n// -->\n</script>\n";
    }
}

response_footer();

?>
