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

response_header('Credits');
?>

<h1>Credits</h1>

<h2>&raquo; PEAR Website Team</h2>

<ul>
  <li><?php echo user_link('cellog'); ?></li>
  <li><?php echo user_link('dufuz'); ?></li>
  <li><?php echo user_link('mj'); ?></li>
</ul>

<h3>&raquo; Emeritus</h3>

<ul>
  <li><?php echo user_link('danielc'); ?></li>
  <li><?php echo user_link('cox'); ?></li>
  <li><?php echo user_link('pajoye'); ?></li>
  <li><?php echo user_link('ssb'); ?></li>
  <li><?php echo user_link('richard'); ?></li>
  <li><?php echo user_link('cmv'); ?></li>
  <li><?php echo user_link('toby'); ?></li>
</ul>

<small>(In alphabetic order)</small>

<p>The website team can be reached at
<?php echo make_mailto_link(PEAR_WEBMASTER_EMAIL); ?>.</p>

<p>More information about the website can be found on a
<a href="/about/">dedicated page</a>.</p>

<h2>&raquo; PEAR Documentation Team</h2>

<p>The authors of the documentation are listed on a
<a href="/manual/en/authors.php">special page</a> in
the manual. The team can be reached via the mailing list
<?php echo make_mailto_link('pear-doc@lists.php.net'); ?>
 (<a href="/support/lists.php">subscription information</a>).</p>

<?php
response_footer();