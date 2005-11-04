<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("The PEAR Group: Security Vulnerability Announcement");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>&raquo; Security Vulnerability Announcement</h2>

<p>Published: 04th November 2005</p>

<p>A vulnerability in the <a href="/package/PEAR/">PEAR installer</a> 
has been found which allows arbitrary code execution.  All versions of 
the installer up to and including release 1.4.2 are affected by this.</p>

<p>An new release of the installer is available which fixes
this issue.  One is strongly encouraged to upgrade to it by
using <tt>pear upgrade PEAR</tt>.</p>

<p>Details about the vulnerability can be found in a 
<a href="/advisory-20051104.txt">separate document</a>.</p>

<?php

echo make_link('/group/', 'Back');

response_footer();
