<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
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
response_header("The PEAR Group: Forming of the PEAR Core list");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>&raquo; Forming of the PEAR Core list</h2>

<p>Published: 14th November 2003</p>

<p>The goal if this decision is to improve the QA process in PEAR.</p>

<p>There will be a new open mailinglist called pear-core which will supersede
pear-qa (everybody from pear-qa will be automatically subscribed). This
mailing list will handle the following things:</p>

<ul>
  <li>pearweb development</li>
  <li>PEAR installer development</li>
  <li>PEAR QA</li>
  <li>PEAR standards definition</li>
</ul>

<p>We will also encourage people from php-qa and pecl-dev to join the list in
order to get them more involved in pearweb and even more importantly pear
installer.</p>

<p>Motivation:</p>

<p>A lot of the discussions we have had on PEAR Group didn't really belong
there. Instead they should have been discussed on pear-qa (pear-core from
now on) or pear-doc. In order to make things more open we have decided to
create the pear-core list, which sole purpose is to discuss topics of basic
relevance to the whole PEAR project. This will diminish the volume in
pear-dev, which is dedicated to topics related to the development of the
software packages in PEAR.</p>

<p>We expect that from the discussions in the pear-core list useful
community-proposed rules and standards will emerge. These rules and
standards must have a general consensus to be adopted and the backing of the
PEAR group (which has veto power on proposals considered disruptive and
prejudicial to the health of the PEAR project as a whole).  If no clear
consensus is reached the matter might be handled by the PEAR group, or a new
round of discussions initiated, the choice will depend on the reasons and
need for the particular rule(s)/standard(s).</p>

<p>We will strongly encourage the presence of people from other projects on
this list in order to ensure that they also participate as much as possible
in the QA process. Remember that we need the fine PHP QA folks to ensure
that PEAR works flawlessly in the PHP distribution. Also the fine
PECL folks rely on our infrastructure for their website and distribution
process, which is on our best interest to get more feedback and help.</p>

<?php
response_footer();
?>