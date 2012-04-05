<?php

    /**
     * Used variables in this template:
     *
     * %id%             The patch ID
     * %url%            The URL of the patch entry
     * %package%        The package name
     * %packageUrl%     The package url
     * %date%           The date of the patch in human readable format
     */

    $tpl = array(
        'X-PHP-Bug' => '%id%',
        'X-PHP-Category' => '%package%',
        'Reply-To' => array(SITE_BIG . ' Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'Subject' => '[Patch] Patch Added/Updated for bug %id%',
        'Body' => 'Dear maintainer,

The following patch has been added/updated for bug #%id%:

Bug Package: %package%
Bug Summary: %summary%
Patch Name:  %name%
URL:         %url%
Date:        %date%

View bug report at : %packageUrl%
'
     );
