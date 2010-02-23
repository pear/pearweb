<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The source code for the PEAR website
 *
 * PHP version 5
 *
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2001-2009 The PHP Group                                |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors:  Michael Gauthier <mike@silverorange.com>                   |
 * +----------------------------------------------------------------------+
 *
 * @category  PEAR Website
 * @package   pearweb
 * @copyright The PHP Group
 * @license   PHP License http://www.php.net/license/2_02.txt
 * @version   $Id:$
 */

/**
 * Class containing static methods to handle lincense-related tasks
 *
 * @category  PEAR Website
 * @package   pearweb
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 silverorange
 * @license   PHP License http://www.php.net/license/3_01.txt
 */
abstract class license
{
    /**
     * Gets a normalized, canonical form for the specified license
     *
     * Many PEAR packages use slight variations for their license property. This
     * method normalizes those differences. Important information like license
     * version is maintained.
     *
     * @param string $license the license to normalize.
     *
     * @return string the normalized license. If the license is unknown, the
     *                specified license is returned verbatim.
     */
    public static function normalize($license)
    {
        $normalized = trim($license);

        switch (strtolower($license)) {
        case 'php':
        case 'php license':
        case 'php license 4.0':
            $normalized = 'PHP';
            break;

        case 'php 2.02':
        case 'php license 2.02':
            $normalized = 'PHP 2.02';
            break;

        case 'php 3.0':
        case 'php 3.01':
        case 'php license v3.0':
        case 'php license 3.01':
            $normalized = 'PHP 3.01';
            break;

        case 'lgpl':
        case 'lgpl license':
        case 'gnu lgpl':
            $normalized = 'LGPL';
            break;

        case 'lgpl 2.1':
        case 'lgplv2.1':
        case 'lgpl version 2.1':
            $normalized = 'LGPL 2.1';
            break;

        case 'lgplv3 license':
            $normalized = 'LGPL 3';
            break;

        case 'bsd':
        case 'bsd (3 clause)':
        case 'new bsd license':
        case 'the bsd license':
        case 'bsd license':
        case 'modified bsd license':
        case 'bsd style':
        case 'bsd-style':
        case 'bsd, revised':
            $normalized = 'BSD';
            break;

        case 'mit':
        case 'mit license':
        case 'mit / beerware':
            $normalized = 'MIT';
            break;

        case 'gpl':
            $normalized = 'GPL';
            break;

        case 'apache':
        case 'apache 2.0':
        case 'apache license 2.0':
        case 'apache license, version 2.0':
        case 'the apache 2.0 license':
            $normalized = 'Apache 2.0';
            break;

        case 'w3c':
            $normalized = 'W3C';
            break;

        case 'php/bsd':
            $normalized = 'PHP or BSD';
            break;

        case 'php or gpl':
            $normalized = 'PHP or GPL';
            break;

        default:
            $normalized = $license;
            break;
        }

        return $normalized;
    }

    /**
     * Gets whether or not the specified license conforms to the PEAR Group
     * license announcement
     *
     * The license announcement states the following license families are
     * allowed for PEAR packages:
     *
     * - Apache 2
     * - BSD
     * - LGPL
     * - MIT
     * - PHP
     *
     * These licenses are friendly for both commercial and open-source
     * developers who wish to use PEAR packages.
     *
     * @param string $license the license to check.
     *
     * @return boolean true if the license conforms to the PEAR Group license
     *                 agreement. Otherwise false.
     *
     * @see http://pear.php.net/manual/en/group.licenses.php
     */
    public static function isGood($license)
    {
        $good = false;

        switch (self::normalize($license)) {
        case 'PHP':
        case 'PHP 2.02':
        case 'PHP 3.01':
        case 'LGPL':
        case 'LGPL 2.1':
        case 'LGPL 3':
        case 'BSD':
        case 'MIT':
        case 'Apache 2.0':
        case 'PHP or BSD':
        case 'PHP or GPL':
            $good = true;
            break;

        default:
            $good = false;
            break;
        }

        return $good;
    }

    /**
     * Gets the URI of the specified license
     *
     * @param string $license the license for which to get the URI.
     *
     * @return string the license URI or null if no URI could be determined from
     *                the specified license.
     */
    public static function getLink($license)
    {
        $normalised = self::normalize($license);

        switch ($normalised) {

        case 'PHP':
            return 'http://www.php.net/license';

        case 'PHP 2.02':
            return 'http://www.php.net/license/2_02.txt';

        case 'PHP 3.01':
            return 'http://www.php.net/license/3_01.txt';

        case 'LGPL':
            return 'http://www.gnu.org/copyleft/lesser.html';

        case 'LGPL 2.1':
            return 'http://www.gnu.org/licenses/lgpl-2.1.html';

        case 'LGPL 3':
            return 'http://www.gnu.org/licenses/lgpl-3.0.html';

        case 'BSD':
            return 'http://www.opensource.org/licenses/bsd-license.php';

        case 'MIT':
            return 'http://www.opensource.org/licenses/mit-license.php';

        case 'GPL':
            return 'http://www.gnu.org/copyleft/gpl.html';

        case 'Apache 2.0':
            return 'http://www.opensource.org/licenses/apache2.0.php';

        case 'W3C':
            return 'http://www.w3.org/Consortium/Legal/2002/copyright-software-20021231';
        }

        return null;
    }

    /**
     * Prevent instantiating this class
     */
    private function __construct()
    {
    }
}

?>
