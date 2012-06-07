<?php
/**
 * Establishes constants used throughout PEAR's website.
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

define ('PEARWEB_TEMPLATEDIR', dirname(dirname(__FILE__)) . '/templates/');
if (isset($_ENV['PEAR_CHANNELNAME']) && $_ENV['PEAR_CHANNELNAME'] != 'pear.php.net') {
    define('SITE', $_ENV['SITE']);
    define('SITE_BIG', strtoupper(SITE));
    define('PEAR_CHANNELNAME',           $_ENV['PEAR_CHANNELNAME']);
    define('PEAR_WEBMASTER_EMAIL',       $_ENV['PEAR_WEBMASTER_EMAIL']);
    define('PEAR_QA_EMAIL',              $_ENV['PEAR_QA_EMAIL']);
    define('PEAR_DOC_EMAIL',             $_ENV['PEAR_DOC_EMAIL']);
    define('PEAR_ANNOUNCE_EMAIL',        $_ENV['PEAR_ANNOUNCE_EMAIL']);
    define('PEAR_DEV_EMAIL',             $_ENV['PEAR_DEV_EMAIL']);
    define('PEAR_GENERAL_EMAIL',         $_ENV['PEAR_GENERAL_EMAIL']);
    define('PEAR_CORE_EMAIL',            $_ENV['PEAR_CORE_EMAIL']);
    define('PEAR_BOUNCE_EMAIL',          $_ENV['PEAR_BOUNCE_EMAIL']);
    define('PEAR_CHANNEL_SUMMARY',       $_ENV['PEAR_CHANNEL_SUMMARY']);
    define('PEAR_AUTH_REALM',            $_ENV['PEAR_AUTH_REALM']);
    define('PEAR_TARBALL_DIR',           $_ENV['PEAR_TARBALL_DIR']);
    define('TRACKBACK_AKISMET_KEY_FILE', $_ENV['TRACKBACK_AKISMET_KEY_FILE']);
    define('PEAR_CHM_DIR',               $_ENV['PEAR_CHM_DIR']);
    define('PEAR_APIDOC_DIR',            $_ENV['PEAR_APIDOC_DIR']);
    define('PEAR_PATCHES',               $_ENV['PEAR_PATCHES']);
    define('PEAR_CVS',                   $_ENV['PEAR_CVS']);
    define('PROPOSAL_MAIL_PEAR_DEV',     $_ENV['PROPOSAL_MAIL_PEAR_DEV']);
    define('PROPOSAL_MAIL_PEAR_GROUP',   $_ENV['PROPOSAL_MAIL_PEAR_GROUP']);
    define('PROPOSAL_MAIL_FROM',         $_ENV['PROPOSAL_MAIL_FROM']);
    define('PEAR_WIKI_URL',              $_ENV['PEAR_WIKI_URL']);
    define('PEAR_WIKI_DSN',              $_ENV['PEAR_WIKI_DSN']);
    define('PEARWEB_BUGS_ML_EMAIL',      $_ENV['PEARWEB_BUGS_ML_EMAIL']);
    define('PEAR_DOCUMENTATION_ENGINE',  $_ENV['PEAR_DOCUMENTATION_ENGINE']);
} else {
    define('SITE', 'pear');
    define('SITE_BIG', strtoupper(SITE));
     // The channel server name that is used for all info
    define('PEAR_CHANNELNAME', 'pear.php.net');
    // The channel webmaster email
    define('PEAR_WEBMASTER_EMAIL', 'pear-webmaster@lists.php.net');
    // The channel QA email
    define('PEAR_QA_EMAIL', 'pear-qa@lists.php.net');
    // The channel webmaster email
    define('PEAR_DOC_EMAIL', 'pear-doc@lists.php.net');
    // The channel webmaster email
    define('PEAR_ANNOUNCE_EMAIL', 'pear-dev@lists.php.net');
    // The channel webmaster email
    define('PEAR_DEV_EMAIL', 'pear-dev@lists.php.net');
    // The channel webmaster email
    define('PEAR_GENERAL_EMAIL', 'pear-general@lists.php.net');
    define('PEAR_GROUP_EMAIL', 'pear-group@php.net');
    // The channel webmaster email
    define('PEAR_CORE_EMAIL', 'pear-core@lists.php.net');
    define('PEAR_BOUNCE_EMAIL', 'bounce-no-user@php.net');
    define('PEARWEB_BUGS_ML_EMAIL', 'pear-bugs@lists.php.net');
    // The channel webmaster email
    define('PEAR_CHANNEL_SUMMARY', 'PEAR PHP Extension and Application Repository');

    /**
     * The authorization realm
     *
     * Gets output to a "WWW-authenticate" header upon authentication failures
     */
    define('PEAR_AUTH_REALM', 'PEAR');
    // Where package tarballs can be found
    define('PEAR_TARBALL_DIR', '/home/pear/packages');
    // Key file returning key for Akismet.com spam chek.
    define('TRACKBACK_AKISMET_KEY_FILE', '/var/www/html/pearweb/akismet.key');
    // Where the CHM builds of the manual are located
    define('PEAR_CHM_DIR', '/home/pear/packages/chm/');
    /**
     * Location of the documentation automatically generated for each
     * package by phpDocumentor
     */
    define('PEAR_APIDOC_DIR', '/home/pear/packages/apidoc/');
    // Where patches can be found
    define('PEAR_PATCHES', '/home/pear/pearweb/patches/');
    // Where proposed patches reside
    define('PEAR_CVS', '/home/pear/pearweb/patches/cvs/');
    /**
     * PEPr: the address of the PEAR Developer email list
     *
     * Notices of changes will be sent to this address.
     */
    define('PROPOSAL_MAIL_PEAR_DEV', 'PEAR developer mailinglist <pear-dev@lists.php.net>');
    /**
     * PEPr: the address of the PEAR Group email list
     *
     * Notices of some changes get sent to this address.
     */
    define('PROPOSAL_MAIL_PEAR_GROUP', 'PEAR group <pear-group@php.net>');
    // PEPr: the email address used as the From header
    define('PROPOSAL_MAIL_FROM', 'PEPr <bounce-no-user@php.net>');

    define('PEAR_DOCUMENTATION_ENGINE', 'phpdocumentor');
}

if (isset($_ENV['PEARWEB_PROTOCOL'])) {
    /**
     * @ignore
     */
    define('PEARWEB_PROTOCOL', $_ENV['PEARWEB_PROTOCOL']);
} else {
    /**
     * Which protocol to use: "https://" (preferred) or "http://".
     */
    define('PEARWEB_PROTOCOL', 'https://');
}

if (isset($_ENV['PEARWEB_CI_SERVER'])) {
    define('PEARWEB_CI_SERVER',  $_ENV['PEARWEB_CI_SERVER']);
} else {
    define('PEARWEB_CI_SERVER', 'http://test.pear.php.net:8080/');
}

if (isset($_ENV['PEAR_TMPDIR'])) {
    define('PEAR_TMPDIR', $_ENV['PEAR_TMPDIR']);
    // Where pearweb's temporary uploads should be stored
    define('PEAR_PATCHTRACKER_TMPDIR', PEAR_TMPDIR . '/patches');
} else {
    // Where pearweb's temporary files should be stored
    define('PEAR_TMPDIR', '/var/tmp/pear');
    define('PEAR_PATCHTRACKER_TMPDIR', '/home/pear/pearweb/patches/');
}

// Where pearweb's temporary CVS files should be stored
define('PEAR_CVS_TMPDIR',          PEAR_TMPDIR . '/cvs');
// Where pearweb's temporary uploads should be stored
define('PEAR_UPLOAD_TMPDIR',       PEAR_TMPDIR . '/uploads');

if (isset($_ENV['PEAR_DATABASE_DSN'])) {
    define('PEAR_DATABASE_DSN', $_ENV['PEAR_DATABASE_DSN']);
} else {
    define('PEAR_DATABASE_DSN', 'mysqli://pear:pear@localhost/pear');
}

// A preg regular expression for validating user names
define('PEAR_COMMON_USER_NAME_REGEX', '/^[a-z][a-z0-9_]+\z/i');
// How long the cache should last
define('CACHE_LIFETIME', 3600);
// Where the cached output is stored
define('DAMBLAN_RSS_CACHE_DIR', PEAR_TMPDIR . '/rss_cache');
// RSS cache time
define('DAMBLAN_RSS_CACHE_TIME', 1800);

/**
 * PEPr: how long a proposal must be in the "proposal" phase before
 * a "Call for Votes" can be called
 */
define('PROPOSAL_STATUS_PROPOSAL_TIMELINE', (60 * 60 * 24 * 7)); // 1 week

// PEPr: how long the "Call for Votes" lasts
define('PROPOSAL_STATUS_VOTE_TIMELINE', (60 * 60 * 24 * 7)); // 1 week

// PEPr: the string prepended to the subject lines of emails
define('PROPOSAL_EMAIL_PREFIX', '[PEPr]');

// PEPr: the string put on the end of each email
define('PROPOSAL_EMAIL_POSTFIX', "");
define('PROPOSAL_OVERVIEW_FINISHED', 10);

// Number of trackbacks from 1 IP allowed within given timespan.
define('TRACKBACK_REPOST_COUNT', 3);
// Timespan for above defined repost count. 3600 == 30 mins
define('TRACKBACK_REPOST_TIMESPAN', 3600);
// Auto purging time for trackbacks (14 days).
define('TRACKBACK_PURGE_TIME', 14 * 27 * 60 * 60);

define('HONEYPOT_API_KEY', 'rmclkmerkfxm');

if (@$_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME || !isset($_SERVER['PEAR_BOX'])) {
    //we cannot display deprecation errors since dependent packages are php4
    error_reporting(
        E_ERROR|E_WARNING|E_PARSE|E_NOTICE
        |E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING
        |E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE
    );
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    define('DEVBOX', false);
}

if (!DEVBOX) {
    define('PEAR_REST_PATH', '/home/pear/pearweb/rest');
} else {
    define('PEAR_REST_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
}

require_once 'Validate.php';
require_once 'Log.php';
require_once 'Log/error_log.php';
$log = new Log_error_log(0, PEAR_CHANNELNAME, null, PEAR_LOG_DEBUG);




// Installation / configuration type checks
$dirs = array(
    'PEAR_APIDOC_DIR' => PEAR_APIDOC_DIR,
    'PEAR_PATCHES' => PEAR_PATCHES,
    'PEAR_CVS' => PEAR_CVS, 
    'PEAR_TARBALL_DIR' => PEAR_TARBALL_DIR, 
    'PEAR_CHM_DIR' => PEAR_CHM_DIR, 
    'PEAR_UPLOAD_TMPDIR' => PEAR_UPLOAD_TMPDIR

);
foreach ($dirs as $name => $dir) {

    if (!file_exists($dir)) {
       $log->warning($name . ' set to ' . $dir . ' which does not exist');
    }

    if (!is_dir($dir)) {
       $log->warning($name . ' set to ' . $dir . ' which is not a directory');
       if (!mkdir($dir)) {
          $log->err("Could not mkdir " . $dir);
       }
    }

    if (!is_writeable($dir)) {
       $log->err($name . ' set to ' . $dir . ' which is not writeable');
    }
}
$emails = array(
    'PEAR_WEBMASTER_EMAIL' => PEAR_WEBMASTER_EMAIL,
    'PEAR_QA_EMAIL' => PEAR_QA_EMAIL,
    'PEAR_DOC_EMAIL' => PEAR_DOC_EMAIL,
    'PEAR_ANNOUNCE_EMAIL' => PEAR_ANNOUNCE_EMAIL,
    'PEAR_DEV_EMAIL' => PEAR_DEV_EMAIL,
    'PEAR_GENERAL_EMAIL' => PEAR_GENERAL_EMAIL,
    'PEAR_GROUP_EMAIL' => PEAR_GROUP_EMAIL,
    'PEAR_CORE_EMAIL' => PEAR_CORE_EMAIL,
    'PEAR_BOUNCE_EMAIL' => PEAR_BOUNCE_EMAIL, 
    'PEARWEB_BUGS_ML_EMAIL' => PEARWEB_BUGS_ML_EMAIL,
);
foreach ($emails as $name => $email) {
   if (!Validate::email($email)) {
      $log->err('Configured email ' . $name . ' = "' . $email . '" is not valid');
   }
}
// TODO validate these
/*
    define('SITE', 'pear');
    define('SITE_BIG', strtoupper(SITE));
     // The channel server name that is used for all info
    define('PEAR_CHANNELNAME', 'pear.php.net');
    // The channel webmaster email
    define('PEAR_CHANNEL_SUMMARY', 'PEAR PHP Extension and Application Repository');


    define('PEAR_AUTH_REALM', 'PEAR');


    define('TRACKBACK_AKISMET_KEY_FILE', '/var/www/html/pearweb/akismet.key');

    define('PROPOSAL_MAIL_PEAR_DEV', 'PEAR developer mailinglist <pear-dev@lists.php.net>');

    define('PROPOSAL_MAIL_PEAR_GROUP', 'PEAR group <pear-group@php.net>');
    // PEPr: the email address used as the From header
    define('PROPOSAL_MAIL_FROM', 'PEPr <bounce-no-user@php.net>');

*/

