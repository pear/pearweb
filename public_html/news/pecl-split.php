<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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

response_header("Own website for PECL");
?>

<h1>Own infrastructure for PECL</h1>

<div style="margin-left:2em;margin-right:2em">
<p>PECL, formerly known as PHP Extension Code Library, has been renamed
to PHP Extension Community Library. Additionally all PECL related
services have been moved to their own website: 
<?php print_link("http://pecl.php.net/"); ?>.</p>

<p>This means that if you are looking for PECL packages, you won't
find them here on pear.php.net anymore; you will have to search for them
on the <?php print_link("http://pecl.php.net/", "PECL website"); ?>
instead. The PECL project also has an independent mailing list battery
now. An overview of the PECL mailing lists can be found
<?php print_link("http://pecl.php.net/support.php", "here"); ?>.
</p>

<p>More information about PECL can be found in the
<?php print_link("http://pear.php.net/manual/en/introduction.php#about-pecl", "PEAR Manual"); ?>.
</p>

</div>

<?php response_footer(); ?>
