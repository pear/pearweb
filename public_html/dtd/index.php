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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Document Type Definitions");
?>

<h1>Document Type Definitions</h1>

<p>The following Document Type Definitions are used in PEAR:</p>

<?php $bb = new BorderBox("Available DTDs"); ?>

<table border="0" cellpadding="2" cellspacing="2">
 <tr>
  <td valign="top"><a href="/dtd/package-1.0">package-1.0</a></td>
  <td valign="top">This is the <acronym title="Document Type Definition">DTD</acronym>
  that defines the legal building blocks of the <tt>package.xml</tt>
  file that comes with each package. More information about
  <tt>package.xml</tt> can be found 
  <a href="/manual/en/developers.packagedef.php">in the manual</a>.
  </td>
 </tr>
 <tr>
  <td valign="top"><a href="/dtd/package-1.0.xsd">package-1.0.xsd</a></td>
  <td valign="top">This is the <acronym title="XML Schema Definition">XSD</acronym>
  that defines the legal building blocks of the <tt>package.xml</tt>
  file that comes with each package. More information about
  <tt>package.xml</tt> can be found 
  <a href="/manual/en/developers.packagedef.php">in the manual</a>.
  </td>
 </tr>
 <tr>
  <td valign="top"><a href="/dtd/package-2.0.xsd">package-2.0.xsd</a></td>
  <td valign="top">This is the <acronym title="XML Schema Definition">XSD</acronym>
  that defines the legal building blocks of version 2.0 of <tt>package.xml</tt>
  file that comes with each package. More information about
  <tt>package.xml</tt> version 2.0 can be found 
  <a href="/manual/en/guide.developers.package2.php">in the manual</a>.<br /><br />This
  XSD should be considered <strong>ALPHA</strong> quality until the PEAR package reaches
  1.4.0b1.  This means the format could change at any time.
  </td>
 </tr>
 <tr>
  <td valign="top"><a href="/dtd/channel-1.0.xsd">channel-1.0.xsd</a></td>
  <td valign="top">This is the <acronym title="XML Schema Definition">XSD</acronym>
  that defines the legal building blocks of <tt>channel.xml</tt>
  file that defines the communication protocols of a channel. More information about
  <tt>channel.xml</tt> can be found 
  <a href="/manual/en/guide.migrating.channels.xml.php">in the manual</a>.<br /><br />This
  XSD should be considered <strong>ALPHA</strong> quality until the PEAR package reaches
  1.4.0b1.  This means the format could change at any time.
  </td>
 </tr>
</table>

<?php
$bb->end();
response_footer();
?>