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
require_once 'PEAR/Config.php';
require_once 'PEAR/PackageUpdate.php';
require_once 'PEAR/PackageUpdate/Cli.php';
$singleton = &PEAR_Config::singleton();
// even if someone compromises pearweb, there's no way to install anything that would overwrite
// a critical file
$singleton->set('bin_dir', '/dev/null');
$pearweb = &PEAR_PackageUpdate::factory('Cli', 'pearweb', 'pear.php.net');
if ($pearweb) {
    $pearweb->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb->checkUpdate()) {
        if (!$pearweb->update()) {
            if ($pearweb->hasErrors()) {
                $errors = PEAR_ErrorStack::staticGetErrors();
                echo "errors\n";
                foreach ($errors['PEAR_PackageUpdate_Cli'] as $err) {
                    echo $err['message'] . "\n";
                }
            }
        }
    }
}
$pearweb_phars = &PEAR_PackageUpdate::factory('Cli', 'pearweb_phars', 'pear.php.net');
if ($pearweb_phars) {
    $pearweb_phars->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb_phars->checkUpdate()) {
        if (!$pearweb_phars->update()) {
            if ($pearweb_phars->hasErrors()) {
                $errors = PEAR_ErrorStack::staticGetErrors();
                echo "errors\n";
                foreach ($errors['PEAR_PackageUpdate_Cli'] as $err) {
                    echo $err['message'] . "\n";
                }
            }
        }
    }
}
$pearweb_index = &PEAR_PackageUpdate::factory('Cli', 'pearweb_index', 'pear.php.net');
if ($pearweb_index) {
    $pearweb_index->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb_index->checkUpdate()) {
        if (!$pearweb_index->update()) {
            if ($pearweb_index->hasErrors()) {
                $errors = PEAR_ErrorStack::staticGetErrors();
                echo "errors\n";
                foreach ($errors['PEAR_PackageUpdate_Cli'] as $err) {
                    echo $err['message'] . "\n";
                }
            }
        }
    }
}
$pearweb_channel = &PEAR_PackageUpdate::factory('Cli', 'pearweb_channelxml', 'pear.php.net');
if ($pearweb_channel) {
    $pearweb_channel->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb_channel->checkUpdate()) {
        if (!$pearweb_channel->update()) {
            if ($pearweb_channel->hasErrors()) {
                $errors = PEAR_ErrorStack::staticGetErrors();
                echo "errors\n";
                foreach ($errors['PEAR_PackageUpdate_Cli'] as $err) {
                    echo $err['message'] . "\n";
                }
            }
        }
    }
}
$pearweb_gopear = &PEAR_PackageUpdate::factory('Cli', 'pearweb_gopear', 'pear.php.net');
if ($pearweb_gopear) {
    $pearweb_gopear->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    if ($pearweb_gopear->checkUpdate()) {
        if (!$pearweb_gopear->update()) {
            if ($pearweb_gopear->hasErrors()) {
                $errors = PEAR_ErrorStack::staticGetErrors();
                echo "errors\n";
                foreach ($errors['PEAR_PackageUpdate_Cli'] as $err) {
                    echo $err['message'] . "\n";
                }
            }
        }
    }
}