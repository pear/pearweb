<?php

    /**
     * Used variables in this template:
     * 
     * %id%             The trackback ID
     * %blog_name%      The name of the blog tracking back
     * %title%          The title of the backtracking entry
     * %url%            The URL of the backtracking entry
     * %excerpt%        The excerpt of the backtracking entry
     * %date%           The date of the trackback in human readable format
     * %timestamp%      The date of the trackback in unix timestamp format
     * %user%       The user who approved the trackback
     */

    $tpl = array(
        'To' => array('PEAR Webmaster <pear-webmaster@lists.php.net>'),
        'Reply-To' => array('PEAR Webmaster <pear-webmaster@lists.php.net>'),
        'From' => 'pear-sys@php.net',
        'Subject' => '[Trackback] Trackback approved for %id%',
        'Body' => 'Dear maintainer.

The following trackback has been approved for the package %id%. See the details below:

Weblog:     %blog_name%
Title:      %title%
URL:        %url%
Date:       %date%

Excerpt:
%excerpt%

Approver:   %user%

You can choose one of the following actions:
Delete - http://pear.php.net/trackback/trackback-admin.php?action=delete&id=%id%&timestamp=%timestamp%.
    
Kind regards,
the PEAR Website Team.
-- 
This Email is brought to you by http://pear.php.net',
     );

?>
