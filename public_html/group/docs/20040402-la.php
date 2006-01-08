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
response_header("The PEAR Group: License Announcement");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>&raquo; License Announcement</h2>

<p>Published: 02 April 2004</p>

<p>Revised: 08 January 2006 (MIT License added)</p>

<p>The PEAR Group would like to announce the following refinement of the
<a href="/manual/en/faq.licenses.php">current license faq entry</a>.</p>

<p>Vote result: 5 (+1), 0 (-1), 3 (+0)</p>

<p>The current list allows a great number of licenses which vary greatly.
This means that users may have to learn the in's and out's of alot of
licenses. Also some of the license choices impose comparitively high
restrictions to the standard PHP license (GPL, QPL ..). As PEAR aims to
extend the functionality provided by PHP users of PHP should fairly
safely be able to also use any PEAR package without licensing worries,
be it for commercial or non commercial, closed or opensource use.</p>

<p>Therefore with this announcement the license choices are reduced to the
following short list:</p>

<ul>
  <li><a href="http://www.php.net/license/">PHP</a></li>
  <li><a href="http://www.apache.org/licenses/">Apache</a></li>
  <li><a href="http://www.gnu.org/copyleft/lesser.html">LGPL</a></li>
  <li><a href="http://www.opensource.org/licenses/bsd-license.php">BSD style</a></li>
  <li><a href="http://www.opensource.org/licenses/mit-license.html">MIT</a></li>
</ul>

<p>Other licenses may be accepted on a case by case basis, but will have to
fit the above criterias. This decision has been made to simplify the
current situation, and as with all decisions is open to be refined in
the future using the RFC proposal methodology.</p>

<p>All packages, which are already part of PEAR as of now, which use other
licenses, do not need to follow this regulation.</p>

<?php

echo make_link('/group/', 'Back');

response_footer();
