<?php

    /**
     * Used variables in this template:
     * 
     * %id%             The patch ID
     * %url%            The URL of the patch entry
     * %packageUrl%     The package url
     * %date%           The date of the patch in human readable format
     */

    $tpl = array(
        'Reply-To' => array('PEAR Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'Subject' => '[Patch] Patch Added/Updated for bug %id%',
        'Body' => 'Dear maintainer,

The following patch has been added/updated for bug #%id%:

Patch Name: %name%
URL:        %url%
Date:       %date%

View bug report at : %packageUrl%
'
     );

?>
