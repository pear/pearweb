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

response_header("Copyright and License");
  
?>

<h1>Copyright and Licence</h1>

<h2>&raquo; PHP License</h2>

<p>
For information on the PHP License (i.e. using the PHP language),
<?php print_link('http://www.php.net/license/', 'click here'); ?>.
</p>

<h2>&raquo; Website Copyright</h2>

<p>
The code, text, PHP logo, and graphical elements on this website 
and the mirror websites (the "Site") are Copyright &copy; 2001-2006 
The PHP Group.  All rights reserved.
</p>

<p>
Except as otherwise indicated elsewhere on this Site, you are free
view, download and print the documents and information available 
on this Site subject to the following conditions:
</p>
<ul>
<li>You may not remove any copyright or other proprietary notices
contained in the documents and information on this Site.</li>
<li>The rights granted to you constitute a license and not a transfer
of title.</li>
<li>The rights specified above to view, download and print the
documents and information available on this Site are not applicable
to the graphical elements, design or layout of this Site.  These
elements of the Site are protected by trade dress and other laws
and may not be copied or imitated in whole or in part.</li>
</ul>

<p>
You can contact the webmaster at
<?php echo make_mailto_link(PEAR_WEBMASTER_EMAIL); ?>.
</p>

<p>
For more information on the PHP Group and the PHP project, please see 
<?php print_link('http://www.php.net/'); ?>.
</p>

<?php
response_footer();
?>
