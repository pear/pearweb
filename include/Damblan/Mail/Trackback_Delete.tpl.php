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
     * %user%        The user who deleted the trackback
     */

    $tpl = array(
        'To' => array('PEAR Webmaster <pear-webmaster@lists.php.net>'),
        'Reply-To' => 'PEAR Webmaster <pear-webmaster@lists.php.net>',
        'From' => 'pear-sys@php.net',
        'Subject' => '[Trackback] Trackback deleted for %id%',
        'Body' => 'Dear maintainer.

A TrackBack for %id% has been removed:

Weblog:     %blog_name%
Title:      %title%
URL:        %url%
Date:       %date%

Excerpt:
%excerpt%

Executor:    %user%
-- 
This Email is brought to you by http://pear.php.net',
     );

?>
