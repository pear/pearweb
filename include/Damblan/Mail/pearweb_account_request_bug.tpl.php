<?php

    /**
     * Used variables in this template:
     *
     * %username%       username of the requested account
     * %salt%           salt for confirmation
     */

    $tpl = array(
        'To'       => array(),
        'Reply-To' => array('PEAR Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'Subject' => '[PEAR-ACCOUNT-REQUEST] Your bug tracker account request : %username%',
        'Body' => 'You have requested an account to open bugs or comment on existing bugs.

This account is only for use with the bug tracker, but can be updated to allow voting
in general elections or other privileges later.

To confirm please follow the link
  http://' . PEAR_CHANNELNAME . '/account-request-confirm.php?salt=%salt%&type=bug

If you have received this email by mistake we apologize for any
inconvenience. You do not need to reply to this email.
 
PEAR Quality Assurance.');

?>
