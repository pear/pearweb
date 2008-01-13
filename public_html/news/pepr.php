<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 The PEAR Group                               |
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

response_header("Announcing PEPr");
?>

<h1>Announcing PEPr</h1>

<div style="margin-left:2em;margin-right:2em">
<p>As of today (25th January 2004) <?php echo make_link("/pepr/", "PEPr"); ?>
is the official tool to handle all proposals for new packages in
PEAR.</p>

<p>PEPr is a web-based interface that helps us to manage the
lifecycle of a package proposal from its first draft until the
final acceptance. It has been put together by
<?php echo make_link("/user/toby", "Tobias Schlitt"); ?>.</p>

<p>Details can be found in the
<?php echo make_link("http://news.php.net/article.php?group=php.pear.dev&article=25264", "announcement mail"); ?>
. The <?php echo make_link("/manual/en/guide-developers.php", "Developers Guide"); ?>
will soon contain information about PEPr as well.</p>
</div>

<?php response_footer(); ?>
