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

redirect_to_https();
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer/PEAR.php';
/** @todo Remove once in QF2 */
require_once 'HTML/QuickForm2/Element/InputNumber.php';
require_once 'HTML/QuickForm2/Element/InputEmail.php';
require_once 'HTML/QuickForm2/Element/InputUrl.php';

require_once 'Damblan/Mailer.php';
require_once 'election/pear-election-accountrequest.php';
require_once 'Text/CAPTCHA/Numeral.php';
require_once 'services/HoneyPot.php';

$numeralCaptcha = new Text_CAPTCHA_Numeral();
session_start();

$display_form = true;
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

        if (empty($_POST['read_everything']['comments_read'])) {
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
            $stripped['firstname'], $stripped['lastname'], $stripped['password'],
            $stripped['password2']);

        if (PEAR::isError($salt)) {
            $errors[] = 'This email address has already been registered by another user';
            $display_form = true;
            break;
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

try {
    $sHelper = new Pearweb_Service_HoneyPot(HONEYPOT_API_KEY);
    $ip      = $_SERVER['REMOTE_ADDR'];
    $sHelper->check($ip);

} catch (Exception $e) {
    report_error($e);
    $display_form = false;
}

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

    $form = new HTML_QuickForm2('account-request-vote', 'post', array('action' => 'account-request-vote.php#requestform'));
    $form->removeAttribute('name');

    $renderer = new HTML_QuickForm2_Renderer_PEAR();

    $hsc = array_map('htmlspecialchars', $stripped);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
        'handle'        => @$hsc['handle'],
        'firstname'     => @$hsc['firstname'],
        'lastname'      => @$hsc['lastname'],
        'email'         => @$hsc['email'],
        'showemail'     => @$hsc['showemail'],
        'read_everything' => @$hsc['read_everything'],
    )));


    $form->addElement('text', 'handle', array('placeholder' => 'psmith', 'maxlength' => "20", 'accesskey' => "r", 'required' => 'required'))->setLabel('Use<span class="accesskey">r</span>name:');
    $form->addElement('text', 'firstname', array('placeholder' => 'Peter', 'required' => 'required'))->setLabel('First Name:');
    $form->addElement('text', 'lastname', array('placeholder' => 'Smith', 'required' => 'required'))->setLabel('Last Name:');
    $form->addElement('password', 'password', array('size' => 10, 'required' => 'required'))->setLabel('Password:');
    $form->addElement('password', 'password2', array('size' => 10, 'required' => 'required'))->setLabel('Repeat Password:');
    $form->addElement('number', 'captcha', array('maxlength' => 4, 'required' => 'required'))->setLabel("What is " . $numeralCaptcha->getOperation() . '?');
    $_SESSION['answer'] = $numeralCaptcha->getAnswer();
    $form->addElement('email', 'email', array('placeholder' => 'you@example.com', 'required' => 'required'))->setLabel('Email Address:');
    $form->addElement('checkbox', 'showemail')->setLabel( 'Show email address?');
    $form->addGroup('read_everything')->addElement('checkbox', 'comments_read', array('required' => 'required'))->setLabel('I have read EVERYTHING on this page');
    $form->addElement('submit', 'submit')->setLabel('Submit Request');

    print $form->render($renderer);

}

response_footer();
