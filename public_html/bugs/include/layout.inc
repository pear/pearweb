<?php

/**
 * PHP classes to simplify website development and maintenance.
 *
 * <p>The Layout Solution holds commonly used variables and page elements,
 * allowing you to focus on designing your pages rather than worrying
 * about correctly duplicating common layouts over and over.</p>
 *
 * <p>It is comprised of Directory Files and two interdependent classes.</p>
 *
 * <p>This file sets class variables relating to the present URI and
 * page.  It operates automatically.  DO NOT MODIFY THIS FILE.</p>
 *
 * <p>Your customizations and settings get put into the layout.custom.inc
 * and directory.inc files.</p>
 *
 * <p>Please make a donation to support our open source development.
 * Update notifications are sent to people who make donations that exceed
 * the small registration threshold.  See the link below.</p>
 *
 * <p>Layout Solution is a trademark of The Analysis and Solutions Company.</p>
 *
 * <pre>
 * ======================================================================
 * SIMPLE PUBLIC LICENSE                        VERSION 1.1   2003-01-21
 *
 * Copyright (c) The Analysis and Solutions Company
 * http://www.analysisandsolutions.com/
 *
 * 1.  Permission to use, copy, modify, and distribute this software and
 * its documentation, with or without modification, for any purpose and
 * without fee or royalty is hereby granted, provided that you include
 * the following on ALL copies of the software and documentation or
 * portions thereof, including modifications, that you make:
 *
 *     a.  The full text of this license in a location viewable to users
 *     of the redistributed or derivative work.
 *
 *     b.  Notice of any changes or modifications to the files,
 *     including the date changes were made.
 *
 * 2.  The name, servicemarks and trademarks of the copyright holders
 * may NOT be used in advertising or publicity pertaining to the
 * software without specific, written prior permission.
 *
 * 3.  Title to copyright in this software and any associated
 * documentation will at all times remain with copyright holders.
 *
 * 4.  THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND
 * COPYRIGHT HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY
 * OR FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE
 * OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,
 * COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.
 *
 * 5.  COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DAMAGES, INCLUDING
 * BUT NOT LIMITED TO, DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL,
 * ARISING OUT OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.
 * ======================================================================
 * </pre>
 *
 * @package    LayoutSolution
 * @author     Daniel Convissor <danielc@AnalysisAndSolutions.com>
 * @copyright  The Analysis and Solutions Company, 2001-2003
 * @version    $Name$ $Id$
 * @link       http://www.LayoutSolution.info/
 * @link       http://www.AnalysisAndSolutions.com/donate/
 */


/**
 * Grab the customizations file.
 */
require $IncludeDir . '/layout.custom.inc';

/**
 * Main Layout Solution class.
 *
 * <p>This sets variables relating to the present URI and page.
 * It operates automatically.  DO NOT MODIFY STUFF IN THIS CLASS.</p>
 *
 * <p>Your customizations and settings go in the
 * LayoutSolution_Customizations class.</p>
 *
 * <p>Some web server environment variables are placed into this object
 * because they are commonly used in scripts and can be spoofed by the
 * viewer.  The spoofer can throw in characters or make them incredibly
 * long which can screw up your programs.  Before placing them into this
 * object, they're cleaned and excessive length truncated.</p>
 *
 * @package    LayoutSolution
 * @author     Daniel Convissor <danielc@AnalysisAndSolutions.com>
 * @copyright  The Analysis and Solutions Company, 2001-2003
 * @version    $Name$
 * @link       http://www.LayoutSolution.info/
 */
class LayoutSolution extends LayoutSolution_Customizations {

    /**
     * URL to the Base Directory of the calling web page using
     * http:// protocol.
     * @var  string
     */
    var $BaseURLNormal = '';

    /**
     * URL to the Base Directory of the calling web page using the
     * present protocol.
     * @var  string
     */
    var $BaseURLPresent = '';

    /**
     * URL to the Base Directory of the calling web page using secured
     * https:// protocol.
     * @var  string
     */
    var $BaseURLSecure = '';

    /**
     * Relative path from the web page to the Base Directory.
     * @var  string
     */
    var $DirLeader = '';

    /**
     * Output from <var>__FILE__</var> in the calling web page.
     * @var  string
     */
    var $File = '';

    /**
     * Unix timestamp of the last time the web page was modified.
     * @var  string
     */
    var $FileMod = '';

    /**
     * URL of the calling web page.
     *
     * <p>The query string is not included.  You can access it in your
     * scripts by using the <var>$URLArray['query']</var> variable.</p>
     *
     * <p>If a script is called from a command line, rather than
     * as a web page, then this variable is the name of the script.</p>
     *
     * <p>Follows the same sanitizing rules as <var>$HTTP_REFERER</var>.</p>
     *
     * @see  $HTTP_REFERER
     * @var  string
     */
    var $FileURL = '';

    /**
     * Environment variable containing the page that sent the viewer to
     * the present page.
     *
     * <p>The maximum length is determined by <var>$URIMaxLen</var>.</p>
     *
     * <p>Drops characters not permitted by RFC 2396: <br /><kbd>
     * A-Z a-z 0-9 !#$%&'()*+,-./:;=?@_~
     * </kbd></p>
     *
     * <p>Warning: Some of those characters are dangerous.  When using 
     * them inside system execution functions, make sure to use 
     * escapeshellcmd()!  When using them inside queries, use 
     * addslashes()!</p>
     *
     * @see  $URIMaxLen
     * @link http://www.rfc-editor.org/rfc/rfc2396.txt
     * @var  string
     */
    var $HTTP_REFERER = '';

    /**
     * Environment variable containing the type of browser being used.
     *
     * <p>Drops anything after the first 60 characters.</p>
     *
     * <p>Only accepts spaces and the following items:</p> <pre>
     * A-Z a-z 0-9 +./:=@_-
     * ¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬁﬂ
     * ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚¸˝˛ˇ
     * </pre>
     *
     * <p>Thus, not all characters which frequent the agent variable are 
     * passed along.  None the less, you can still figure out the primary
     * information.</p>
     *
     * @var  string
     */
    var $HTTP_USER_AGENT = '';

    /**
     * Path to the Include Directory.
     * @var  string
     */
    var $IncludeDir = '';

    /**
     * Environment variable containing the IP address of the viewer.
     *
     * <p>If there's a <var>HTTP_X_FORWARDED_FOR</var> address from a
     * proxy, that IP is used.</p>
     *
     * @var  string
     */
    var $REMOTE_ADDR = '';

    /**
     * Array of components from the URL the browser sent to connect to
     * the calling web page.
     *
     * <p>If a script is called from a command line, rather than as a web
     * page, then <var>$Layout->URLArray['host']</var> is set to
     * <var>$Layout->Host</var> and <var>$Layout->URLArray['path']</var>
     * is the name of the script.</p>
     *
     * <p>Follows the same sanitizing rules as <var>$HTTP_REFERER</var>.</p>
     *
     * @see  $HTTP_REFERER
     * @var  array
     * @link http://www.php.net/parse_url
     */
    var $URLArray = array();

    /**
     * Sets class variables relating to the present URI and page.
     *
     * <p>If the host entered in the URI isn't either <var>Host</var>
     * or <var>$LocalHost</var>, the user is redirected to the same
     * path and query string on <var>$Host</var>.</p>
     *
     * @return  void
     */
    function LayoutSolution() {
        global $File, $DirLeader, $IncludeDir;

        $this->File        = $File;
        $this->FileMod     = filemtime($File);
        $this->IncludeDir  = $IncludeDir;
        $this->DirLeader   = $DirLeader;
        $this->GraphicsDir = $this->DirLeader . '/' . $this->GraphicsDir;

        if (!isset($_SERVER)) {
            $_SERVER = $HTTP_SERVER_VARS;
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->HTTP_REFERER = ereg_replace('[^!#-9:;=?-Z_a-z~]', '',
                substr($_SERVER['HTTP_REFERER'], 0, $this->URIMaxLen));
        } else {
            $this->HTTP_REFERER = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->HTTP_USER_AGENT = ereg_replace(
                '[^A-Za-z¿-÷ÿ-ˆ¯-ˇ0-9 +./:=@_-]',
                '', substr($_SERVER['HTTP_USER_AGENT'], 0, 60));
        } else {
            $this->HTTP_USER_AGENT = '';
        }

        $this->REMOTE_ADDR = ereg_replace('[^0-9.]',
            '', substr($_SERVER['REMOTE_ADDR'], 0, 15));

        if ($_SERVER['SERVER_PORT'] == 443) {
            $Transport = 'https://';
        } else {
            $Transport = 'http://';
        }

        if ($this->URLArray =
                @parse_url($Transport
                           . $_SERVER['HTTP_HOST']
                           . ereg_replace('[^!#-9:;=?-Z_a-z~]',
                                          '',
                                          substr($_SERVER['REQUEST_URI'],
                                                 0,
                                                 $this->URIMaxLen))))
        {
            $this->BaseURLPresent = $Transport . $this->URLArray['host'];
            $this->FileURL = $this->BaseURLPresent . $this->URLArray['path'];
            if (empty($this->URLArray['port'])) {
                $this->URLArray['port'] = $_SERVER['SERVER_PORT'];
            }
        } elseif (!isset($REQUEST_URI)) {
            $this->FileURL = $File;
            $this->URLArray['host'] = $this->Host;
            $this->URLArray['path'] = $File;
        } else {
            $this->URLArray = @parse_url("$Transport$this->Host:"
                    . $_SERVER['SERVER_PORT']
                    . ereg_replace('[^!#-9:;=?-Z_a-z~]', '',
                        substr($_SERVER['REQUEST_URI'], 0, $this->URIMaxLen))
                    );
            $this->BaseURLPresent = $Transport . $this->URLArray['host'];
            $this->FileURL = $this->BaseURLPresent . $this->URLArray['path'];
        }

        switch ($this->URLArray['host']) {
            case $this->Host:
                $this->BaseURLNormal = 'http://' . $this->URLArray['host'];
                $this->BaseURLSecure = 'https://' . $this->URLArray['host'];
                break;
            case $this->LocalHost:
                $this->BaseURLNormal = 'http://' . $this->URLArray['host']
                                       . $this->LocalDirAppend;
                $this->BaseURLSecure = 'https://' . $this->URLArray['host']
                                       . $this->LocalDirAppend;
                break;
            default:
                header('Location: http://' . $this->Host
                       . $this->URLArray['path']
                       . ($this->URLArray['query']
                                ? '?' . $this->URLArray['query']
                                : ''));
                exit;
        }
    }
}
