<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2007 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Gregory Beaver <cellog@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Innovating the future: Package.xml 1.0 and PEAR 1.3.6 are officially deprecated");
?>

<h1>Innovating the future: Package.xml 1.0, and PEAR 1.3.6 are officially deprecated</h1>

<div style="margin-left:2em;margin-right:2em">
<p>
 If you are using PEAR 1.3.6 or older, you will no longer be able to install or upgrade
 packages as of January 1, 2008.  On this day, the XML-RPC server at pear.php.net will be
 shut down, to be permanently replaced by the newer, more scalable REST interface that was
 introduced with PEAR version 1.4.0.  Users who upgrade to PEAR 1.5.0 or the latest stable
 version of PEAR will be unaffected by this change, and will not notice any difference.  For
 assistance upgrading <a href="http://pear.php.net/PEAR">PEAR</a> to the latest version, or
 for other installation-related questions or concerns, please send an email message to
 the pear-general mailing list at
 <a href="mailto:pear-general@lists.php.net">pear-general@lists.php.net</a>.
</p>

<p>
 Although most people will not have difficulty, for some systems, upgrading from
 ancient versions of PEAR can be tricky.  If you are running a version of PEAR older
 than 1.4.0, follow these simple steps:
</p>

<p>
<kbd>
<pre>
pear upgrade --force PEAR-1.3.6 Archive_Tar-1.3.1 Console_Getopt-1.2
pear upgrade PEAR-1.4.3
pear upgrade --force PEAR-1.4.11
pear upgrade PEAR
</pre>
</kbd>
</p>

<p>
 Those who have PEAR versions 1.4.0 to 1.4.3 should simply skip the first line that
 upgrades to PEAR version 1.3.6.  Users who are using the web frontend would probably
 benefit the most from a clean start - download go-pear from
 <a href="http://pear.php.net/go-pear">http://pear.php.net/go-pear</a>,
 save as <kbd>go-pear.php</kbd> and re-install PEAR from scratch, overwriting your
 existing installation.  Instructions are in a comment at the top of the file, or
 you can read them directly off of the http://pear.php.net/go-pear website.
</p>

<p>
 Effective February 1, 2007, the release uploader at pear.php.net will no longer
 accept packages created using only package.xml 1.0.  This affects developers who
 are authoring and releasing PEAR packages, not end users who wish to install PEAR
 packages.  This only affects packages released after February 1, 2007, and will not
 be &quot;grandfathered&quot; to existing releases.
</p>
<p>
 To maintain compatibility with PEAR 1.3.6 and earlier, package releases may be created
 with both a package.xml (version 1.0) and a package2.xml (version 2.0) using this command:
</p>
<p>
<kbd>
<pre>
pear package package.xml package2.xml
</pre>
</kbd>
</p>

<p>
 To create a package.xml version 2.0 from an existing package.xml version 1.0, simply run
 the <code>pear convert</code> command in the same directory as your package.xml.  Please
 email the <a href="mailto:pear-dev@lists.php.net">pear-dev</a> mailing list for support
 with this process.
</p>

</div>

<?php response_footer(); ?>
