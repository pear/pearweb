<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
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
response_header("Forgot your password?");
?>

<h1>Forgot your password?</h1>

<p>Forgot your password for logging in to the website?  Don&apos;t 
worry &mdash; this happens to the best of us.  Currently there is no
automated way to reset the password, but you can send a mail to the
<a href="mailto:pear-group@php.net">PEAR Group</a> that includes the
following information:</p>

<ul>
  <li>your PEAR username</li>
  <li>the new password for your account as an MD5 hash</li>
</ul>

<p>Please also make sure that you are sending the mail using the 
address with which you signed up for an account initially.  It will be
used to confirm that your request is valid.  If that isn&apos;t possible
for some reason, include the address in the mail body.</p>

<p>The MD5 hash can be created by using PHP's <code><a href="http://php.net/md5">md5()</a></code>
function.</p>

<?php
response_footer();
?>
