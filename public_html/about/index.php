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
response_header('About this site');
?>

<h1>About this site</h1>

<p>This site has been created and is maintained by a number of people,
which are listed on the <a href="/credits.php">credits page</a>.
If you would like to contact them, you can write to
<a href="mailto:pear-webmaster@lists.php.net">pear-webmaster@lists.php.net</a>.
</p>

<p>It has been built with <a href="http://httpd.apache.org/">Apache</a>,
<a href="http://php.net/">PHP</a>, <a href="http://www.mysql.com/">MySQL</a>,
and some (as you might have guessed) PEAR packages. Additionally we 
have started to use a set of utility classes. We call it
<a href="damblan.php">Damblan</a>.</p>

<p><a href="http://validator.w3.org/check/referer">
<img style="border:0px; width:88px; height:31px" 
     src="http://www.w3.org/Icons/valid-xhtml10" 
     alt="(Mostly) Valid XHTML 1.0!" /></a>
<a href="http://jigsaw.w3.org/css-validator/">
<img style="border:0;width:88px;height:31px"
     src="http://jigsaw.w3.org/css-validator/images/vcss" 
     alt="Valid CSS!" /></a></p>

<h2>Website soure code</h2>

<p>The source code of the website is available via CVS. To checkout the 
latest version, use the following commands:</p>

<pre>
$ cvs -d :pserver:cvsread@cvs.php.net:/repository login
  [password is &quot;cvsread&quot;]

$ cvs -d :pserver:cvsread@cvs.php.net:/repository co pearweb
</pre>

<p>One can also view the source code by using the
<a href="http://cvs.php.net/cvs.php/pearweb">CVS web interface</a>.</p>

<h2>Privacy Policy</h2>

<p>Read the <a href="privacy.php">privacy policy</a>.</p>

<h2>License &amp; Copyright</h2>

<p>The PHP code that runs the website is licensed under the PHP License.
Some third-party code such as <a href="http://www.aditnus.no/jpgraph/">jpgraph</a>
is bundled with the website in CVS and may be available under a 
different license.  If you are uncertain about the license or copyright
constraints, please get in touch with the <a href="mailto:pear-webmaster@lists.php.net">
webmaster mailing list</a>.</p>

<?php
response_footer();
?>
