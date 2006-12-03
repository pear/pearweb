<?php

    /**
     * Used variables in this template:
     *
     * %username%       username of the requested account
     * %salt%           salt for confirmation
     */

    $tpl = array(
        'Reply-To' => array('PEAR Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'From' => 'pear-sys@php.net',
        'Subject' => '[PEAR-ACCOUNT-REQUEST] Your account request : %username%',
        'Body' => 'You have requested an account to vote in general PEAR elections.

This account is only for voting, and is not for proposing a new package.

To confirm please follow the link
  http://' . PEAR_CHANNELNAME . '/account-request-confirm.php?salt=%salt%

If you have received this email by mistake we apologize for any
inconvenience. You do not need to reply to this email.
 
PEAR Quality Assurance.');

?>
