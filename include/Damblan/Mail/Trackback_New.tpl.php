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
     */

    $tpl = array(
        'To' => array('PEAR Webmaster <pear-webmaster@lists.php.net>'),
        'Reply-To' => array('PEAR Webmaster <pear-webmaster@lists.php.net>'),
        'From' => 'pear-sys@php.net',
        'Subject' => '[Trackback] New trackback discovered for %id%',
        'Body' => 'Dear maintainer.

A new trackback has been discovered for the package %id%:

Weblog:     %blog_name%
Title:      %title%
URL:        %url%
Date:       %date%

Excerpt:
%excerpt%

Please choose one of the following actions:
Approve - http://pear.php.net/trackback/trackback-admin.php?action=approve&id=%id%&timestamp=%timestamp%.
Delete - http://pear.php.net/trackback/trackback-admin.php?action=delete&id=%id%&timestamp=%timestamp%.'
     );

?>
