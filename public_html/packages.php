<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes                                               |
   |          Michael Gauthier <mike@silverorange.com>                    |
   +----------------------------------------------------------------------+
   $Id$
*/

/*
 * TODO
 * o Number of packages in brackets does not include packages in subcategories
 */

/**
 * Page class
 */
require_once 'pear-page-packages.php';

/*
 * Check input variables. Expected URI variabless:
 *  - catpid (category parent id),
 *  - catname,
 *  - php
 */
$catpid = isset($_GET['catpid']) ? (int)$_GET['catpid'] : null;
$php    = isset($_GET['php'])    ? (string)$_GET['php'] : 'all';

$page = new page_packages($dbh);
extra_styles('css/packages.css');
response_header($page->getTitle($catpid, $php));
$page->display($catpid, $php);
response_footer();
