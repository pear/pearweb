<?php

    /**
     * Used variables in this template:
     * 
     * %id%             The patch ID
     * %url%            The URL of the patch entry
     * %date%           The date of the patch in human readable format
     * %user%           The user who sent the patch
     */

    $tpl = array(
        'Reply-To' => array('PEAR Webmaster <' . PEAR_WEBMASTER_EMAIL . '>'),
        'Subject' => '[Patch] Patch Added/Updated for bug %id%',
        'Body' => 'Dear maintainer,

The following patch has been added/updated for bug %id%:

URL:        %url%
Date:       %date%

Executor:   %user%

     );

?>
