<?php
/**
 * Update cron job for the PEAR website
 *
 * @category  pearweb
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright Copyright (c) 2007 The PHP Group
 * @license   http://www.php.net/license/3_01.txt  PHP License
 * @version   $Id: trackback-cleanup.php,v 1.3 2006/02/07 18:14:38 mj Exp $
 */
require_once 'PEAR/PackageUpdate.php';
$pearweb = &PEAR_PackageUpdate::factory('CLI', 'pearweb', 'pear.php.net');
if ($pearweb) {
    $pearweb->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb->checkUpdate()) {
        if (!$pearweb->update()) {
            if ($pearweb->hasErrors()) {
                echo "errors\n";
            }
        }
    }
}
$pearweb_phars = &PEAR_PackageUpdate::factory('CLI', 'pearweb_phars', 'pear.php.net');
if ($pearweb_phars) {
    $pearweb_phars->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb_phars->checkUpdate()) {
        if (!$pearweb_phars->update()) {
            if ($pearweb_phars->hasErrors()) {
                echo "errors\n";
            }
        }
    }
}
?>