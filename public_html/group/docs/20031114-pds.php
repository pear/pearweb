<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
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
response_header("The PEAR Group: Package Directory Structure");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>&raquo; Package Directory Structure</h2>

<p>Published: 14th November 2003</p>

The goal is to unify the directory naming inside CVS and after installation.

<p>Let's assume we have a package &quot;The_Package_Name&quot; that contains one or more
sub-classes (eg. The_Package_Name_Module), with some documentation (perhaps
a README, copies of RFCs, etc.), a battery of test scripts (unit tests,
regression tests, etc.), and it uses some data files (localization strings,
etc.), the dir tree would look like:</p>

<pre>
        The_Package_Name
        |-- Name (contains Module.php)
        |-- data
        |-- docs
        |   `-- examples
        |-- misc
        |-- scripts
        `-- tests
</pre>

<p>&quot;Name&quot; refers to the last part of the &quot;The_Package_Name&quot;, 
all subclasses of the main class, should be put in there or subdirectories of it.
You can refer to http://cvs.php.net/cvs.php/pear/Cache_Lite/ - the directory
&quot;Lite&quot; as an example (this basically documents what we currently
do anyway). These types of dirs are optional.</p>

<p>The &quot;data&quot; and &quot;misc&quot; dirs should be optional, because
it will not make sense to have them for every single package in PEAR.</p>

<p>The directories that are required are &quot;docs/examples&quot; and 
&quot;tests&quot;. A package may have no extra documentation, but it should
have at least one example. There must also be some basics test to be able to 
verify that the package is working. The preferred type of testing script 
system to use is PHPUnit or .phpt. But for now we would be content with 
any sort of test script.</p>

<p>File in scripts will be installed into a directory available in $PATH,
such as /usr/local/bin.</p>

<p>Anything that does not fit any of the above categories is placed into the
&quot;misc&quot; dir.</p>

<p>Maintainers are expected to modify their existing packages to match
this new standard.</p>

<?php

echo make_link('/group/', 'Back');

response_footer();
