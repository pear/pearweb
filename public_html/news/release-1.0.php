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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header('PEAR 1.0 is released!');

?>
<h1>PEAR 1.0 is released!</h1>

[December 27, 2002]

<div id="news-entry">
</p>
As of PHP 4.3.0, PEAR is an officially supported
part of PHP.  From this release, the PEAR installer with all its
prerequisites is installed by default on Unix-style systems (Windows
will follow in 4.3.2).  It has been a long pregnancy.
</p>

<br /><br />
<dl>
 <dt>Some historical highlights:</dt>
 <dd>
<ul>
 <li>1999-11-21 : Malin Bakken was born</li>
 <li>1999-11-22 : the first few lines of PEAR code were committed (DB.php)</li>
 <li>2000-07-24 : the PEAR and PEAR_Error classes were born</li>
 <li>2000-08-01 : first working version of the "pear" command</li>
 <li>2001-05-15 : first contributor to base system</li>
 <li>2001-12-28 : first package uploaded to the current pear.php.net</li>
 <li>2002-05-26 : installer can upgrade itself</li>
 <li>2002-06-13 : first version of Gtk installer</li>
 <li>2002-07-11 : first version of Web installer</li>
</ul>
</dd>
</dl>

<p>
Thanks to all PEAR contributors, and special thanks to those who have
pitching in when I've been too busy with family and work to do any PHP
hacking:
</p>

<ul>
 <li>Tomas V.V.Cox</li>
 <li>Martin Jansen</li>
 <li>Christian Dickmann</li>
 <li>Jon Parise</li>
 <li>Richard Heyes</li>
 <li>Pierre-Alain Joye</li>
</ul>

<a href="/user/ssb">Stig Bakken &lt;stig&#64;php.net&gt;</a>
</div>

<?php
response_footer();