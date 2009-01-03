<?php

    /**
     * Used variables in this template:
     *
     * %username%       username of the requested account
     * %salt%           salt for confirmation
     */

    $tpl = array(
        'Reply-To' => array(SITE_BIG . ' Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'Subject' => '[' . SITE_BIG . '] Your account request : %username%',
        'Body' => 'You have requested an account to vote in general ' . SITE_BIG . ' elections.

This account is only for voting, and is not for proposing a new package.

To confirm please follow the link
  http://' . PEAR_CHANNELNAME . '/account-request-confirm.php?salt=%salt%

If you have received this email by mistake we apologize for any
inconvenience. You do not need to reply to this email.

' SITE_BIG . ' Quality Assurance.');