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
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

if (isset($_SERVER['PEAR_TMPDIR'])) {
    /**
     * @ignore
     */
    define('PEAR_TMPDIR', $_SERVER['PEAR_TMPDIR']);
    /**
     * @ignore
     */
    define('PEAR_CVS_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/cvs');
    /**
     * @ignore
     */
    define('PEAR_UPLOAD_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/uploads');
} else {
    /**
     * Where pearweb's temporary files should be stored
     */
    define('PEAR_TMPDIR', '/var/tmp/pear');
    /**
     * Where pearweb's temporary CVS files should be stored
     */
    define('PEAR_CVS_TMPDIR', '/var/tmp/pear/cvs');
    /**
     * Where pearweb's temporary uploads should be stored
     */
    define('PEAR_UPLOAD_TMPDIR', '/var/tmp/pear/uploads');
}

if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
    /**
     * @ignore
     */
    define('PEAR_DATABASE_DSN', $_SERVER['PEAR_DATABASE_DSN']);
} else {
    /**
     * The PEAR::DB DSN connection string
     *
     * To override default, set the value in $_SERVER['PEAR_DATABASE_DSN']
     * before this file is included.
     */
    define('PEAR_DATABASE_DSN', 'mysql://pear:pear@localhost/pear'); 
}

if (isset($_SERVER['PEAR_AUTH_REALM'])) {
    define('PEAR_AUTH_REALM', $_SERVER['PEAR_AUTH_REALM']);
} else {
    /**
     * The authorization realm
     *
     * Gets output to a "WWW-authenticate" header upon authentication failures
     *
     * To override default, set the value in $_SERVER['PEAR_AUTH_REALM']
     * before this file is included.
     */
    define('PEAR_AUTH_REALM', 'PEAR');
}

if (isset($_SERVER['PEAR_TARBALL_DIR'])) {
    /**
     * @ignore
     */
    define('PEAR_TARBALL_DIR', $_SERVER['PEAR_TARBALL_DIR']);
} else {
    /**
     * Where package tarballs can be found
     *
     * To override default, set the value in $_SERVER['PEAR_TARBALL_DIR']
     * before this file is included.
     */
    define('PEAR_TARBALL_DIR', '/var/lib/pear'); 
}

if (isset($_SERVER['PEAR_CHM_DIR'])) {
    /**
     * @ignore
     */
    define('PEAR_CHM_DIR', $_SERVER['PEAR_CHM_DIR']);
} else {
    /**
     * Where the CHM builds of the manual are located
     *
     * To override default, set the value in $_SERVER['PEAR_CHM_DIR']
     * before this file is included.
     */
    define('PEAR_CHM_DIR', '/var/lib/pear/chm/'); 
}

if (isset($_SERVER['PEAR_APIDOC_DIR'])) {
    /**
     * @ignore
     */
    define('PEAR_APIDOC_DIR', $_SERVER['PEAR_APIDOC_DIR']);
} else {
    /**
     * Location of the documentation automatically generated for each
     * package by phpDocumentor
     *
     * To override default, set the value in $_SERVER['PEAR_APIDOC_DIR']
     * before this file is included.
     */
    define('PEAR_APIDOC_DIR', '/var/lib/pear/apidoc/'); 
}

if (isset($_SERVER['PEAR_PATCHES'])) {
    /**
     * @ignore
     */
    define('PEAR_PATCHES', $_SERVER['PEAR_PATCHES']);
} else {
    /**
     * Where patches can be found
     *
     * To override default, set the value in $_SERVER['PEAR_PATCHES']
     * before this file is included.
     */
    define('PEAR_PATCHES', '/var/lib/pear/patches/');
}

if (isset($_SERVER['PEAR_CVS'])) {
    /**
     * @ignore
     */
    define('PEAR_CVS', $_SERVER['PEAR_CVS']);
} else {
    /**
     * Where proposed patches reside
     *
     * To override default, set the value in $_SERVER['PEAR_CVS']
     * before this file is included.
     */
    define('PEAR_CVS', '/var/lib/pear/patches/cvs/');
}

/**
 * A preg regular expression for validating user names
 */
define('PEAR_COMMON_USER_NAME_REGEX', '/^[a-z][a-z0-9]+$/i');

/**
 * How long the cache should last
 */
define('CACHE_LIFETIME', 3600);

/**
 * Where the cached output is stored
 */
define('DAMBLAN_RSS_CACHE_DIR', PEAR_TMPDIR . '/rss_cache');

/**
 *
 */
define('DAMBLAN_RSS_CACHE_TIME', 1800);



/**
 * PEPr: how long a proposal must be in the "proposal" phase before
 * a "Call for Votes" can be called
 */
define('PROPOSAL_STATUS_PROPOSAL_TIMELINE', (60 * 60 * 24 * 7)); // 1 week

/**
 * PEPr: how long the "Call for Votes" lasts
 */
define('PROPOSAL_STATUS_VOTE_TIMELINE', (60 * 60 * 24 * 7)); // 1 week

if (isset($_SERVER['PROPOSAL_MAIL_PEAR_DEV'])) {
    /**
     * @ignore
     */
    define('PROPOSAL_MAIL_PEAR_DEV', $_SERVER['PROPOSAL_MAIL_PEAR_DEV']);
} else {
    /**
     * PEPr: the address of the PEAR Developer email list
     *
     * Notices of changes will be sent to this address.
     *
     * To override default, set the value in $_SERVER['PROPOSAL_MAIL_PEAR_DEV']
     * before this file is included.
     */
    define('PROPOSAL_MAIL_PEAR_DEV', 'PEAR developer mailinglist <pear-dev@lists.php.net>');
}

if (isset($_SERVER['PROPOSAL_MAIL_PEAR_GROUP'])) {
    /**
     * @ignore
     */
    define('PROPOSAL_MAIL_PEAR_GROUP', $_SERVER['PROPOSAL_MAIL_PEAR_GROUP']);
} else {
    /**
     * PEPr: the address of the PEAR Group email list
     *
     * Notices of some changes get sent to this address.
     *
     * To override default, set the value in $_SERVER['PROPOSAL_MAIL_PEAR_GROUP']
     * before this file is included.
     */
    define('PROPOSAL_MAIL_PEAR_GROUP', 'PEAR group <pear-group@php.net>');
}

if (isset($_SERVER['PROPOSAL_MAIL_FROM'])) {
    /**
     * @ignore
     */
    define('PROPOSAL_MAIL_FROM', $_SERVER['PROPOSAL_MAIL_FROM']);
} else {
    /**
     * PEPr: the email address used as the From header
     *
     * To override default, set the value in $_SERVER['PROPOSAL_MAIL_FROM']
     * before this file is included.
     */
    define('PROPOSAL_MAIL_FROM', 'PEPr <pear-sys@php.net>');
}

/**
 * PEPr: the string prepended to the subject lines of emails
 */
define('PROPOSAL_EMAIL_PREFIX', '[PEPr]');

/**
 * PEPr: the string put on the end of each email
 */
define('PROPOSAL_EMAIL_POSTFIX', "\n\n-- \nSent by PEPr, the automatic proposal system at http://pear.php.net");

define('PROPOSAL_OVERVIEW_FINISHED', 10);

?>
