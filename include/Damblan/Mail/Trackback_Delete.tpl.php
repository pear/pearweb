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
        'Reply-To' => array('PEAR Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'From' => 'pear-sys@php.net',
        'Return-Path' => 'bounces-ignored@php.net',
        'Subject' => '[Trackback] Trackback deleted for %id%',
        'Body' => 'Dear maintainer,

A TrackBack for %id% has been removed:

Weblog:     %blog_name%
Title:      %title%
URL:        %url%
Date:       %date%

Excerpt:
%excerpt%

Executor:    %user%'
     );

?>
