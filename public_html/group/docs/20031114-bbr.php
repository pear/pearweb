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
response_header("The PEAR Group: New guidelines for BC breaking releases");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>New guidelines for BC breaking releases</h2>

<p>Published: 14th November 2003</p>

<p>The goal is to make it possible to be able to run multiple major versions in
one script.</p>

For this reason new major versions (BC break or when the maintainer feels it
makes sense as a result of dramatic feature additions or rewrites) require a
new package using the following naming convention in the following order of
preference (where 'Foo' is the package name and 'X' the major version number):
</p>

<ol>
 <li>FooX</li>
 <li>FoovX</li>
 <li>Foo_vX</li>
</ol>

<p>The choice should be made based on preventing current and future misleading
or ambiguous names. This means good care should be taken in making the right
choice for the package. Obviously the first two allow for some ambiguity (is
DB2 a package for IBM's DB2 or just the major version 2 of DB? - is IPv4 a
package for IPv4 or is it the 4th major release if IP?). They don't break
the idea of "_" mapping to directories (the class DB_NestedSet implies that
there is a nestedset.php in the DB dir). The last one prevents any ambiguity
but it's the least visually pleasing and also breaks the '_' to directory
mapping and is therefore the last choice.</p>

<p>We also came to the conclusion that the pear installer should not be clever
about the relationship between two major releases aside from printing out
notices about the fact that there is a newer major version when a user
installs an earlier one. However all major versions of a package will be
listed on one package home. This is especially important in order to not
break tutorials that cover older major releases (tutorial xyz for major
version 1 simply says 'pear install Foo' - if the system would then install
'Foo2' the user might be in for an unpleasant surprise).</p>

<p>Therefore, new major versions are for all intents and purposes new packages
with the above mentioned exceptions. The names of these new packages are
derived using one of the above mentioned naming conventions.</p>


<?php
response_footer();
?>
