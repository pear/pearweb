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
response_header("The PEAR Group: Version Naming");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>&raquo; Version Naming</h2>

<p>Published: 26th Februray 2004</p>

<p>The PEAR Group would like to take this opportunity to define the 
following version naming convention, which will be mandatory for all 
packages from now on. All developers should attempt to move to 
this new version naming scheme as soon as possible.</p>

<p>Vote Results: +1 (6), -1 (0), +0 (2)</p>

<h3>&raquo; Current Versioning Method</h3>

<p>The current status quo is that version names should follow the 
progression defined by the version_compare() function.</p>

<h3>&raquo; New Versioning Method</h3>

<p>Note that the version number is not the same as the version name. The 
version name is the final name of a version which consists of the 
version number and an optional suffix. See the following for details.</p>

<p>To determine the version name of a release use the following rule set:</p>

<ul>
<li>A version number must include a major, a minor and a patch level 
version number. Please note that all are version numbers are mandatory.</li>

<li>A package version has a &quot;state&quot; (as indicated in the package.xml file), 
which describes the maturity. The state may be one of &quot;dev&quot;, &quot;alpha&quot;, 
&quot;beta&quot;, &quot;RC&quot; or &quot;stable&quot; (listed in the order of code maturity). Please 
note that the state &quot;RC&quot; is achieved by using the state &quot;beta&quot; and 
appending the version number with &quot;RC&quot; followed by an integer</li>

<li>A backwards-compatability break may include feature additions</li>

<li>A feature addition may include bug fixes</li>

<li>The version name is always computed on the version name of the 
release, on which the new release is based, if one exists.</li>

<li>The version number must be greater or equal than 0.1.0</li>

<li>All initial releases of a package with states &quot;dev&quot;, &quot;alpha&quot;, or 
&quot;beta&quot; prior to the first stable release should have a version number 
less than &quot;1.0.0&quot;.</li>

<li>The first release with state &quot;RC&quot; or &quot;stable&quot; must have a version 
number of &quot;1.0.0&quot;.</li>

<li>There may not be a stable release unless there has been at least 
one release before with the same major version.</li>

<li>BC may only be broken in releases that have a version number of 
&quot;x.0.0&quot; with a state lower than stable or that have a version number 
below &quot;1.0.0&quot;. As a converse only releases that break BC or that have a 
version number of &quot;1.0.0&quot; may increase the major version number compared 
to the previous release.</li>

<li>Features may only be added in releases that have a version number 
of &quot;x.y.0&quot; (where &quot;y > 0&quot;). As a converse the minor version may only be 
increased in releases that add features.</li>

<li>For releases that only fix bugs the version number should be 
&quot;x.y.z&quot; (where &quot;z > 0&quot;) unless the maturity state is increased. As a 
converse the patch level number should only be used (as in non zero) in 
releases that only fix bugs.</li>

<li>The state should always be added as a suffix unless the state is 
&quot;stable&quot; (please note that as stated above the state &quot;beta&quot; is used for 
beta releases and for release candidates). The suffix consists of the 
state followed by a number which is incremented with every subsequent 
release with the same state.</li>

<li>In the lifecycle of a package each major version increase it is 
only once (once from major version number 0 to 1, from 1 to 2 etc.).</li>

</ul>

<h4>Example: Lifecycle of a package</h4>

<table border="1" cellpadding="2" cellspacing="0">
<tr>
<th>Release type</th><th>Changes</th><th>Version</th><th>Notes</th>
</tr>
<tr>
<td>development release     </td><td>   initial release     </td><td>   0.1.0dev1   </td><td>   initial version</td>
</tr>
<tr>
<td>development release     </td><td>   features added      </td><td>   0.2.0dev1   </td><td>   BC break allowed</td>
</tr>
<tr>
<td>alpha release           </td><td>   features added      </td><td>   0.9.0alpha1 </td><td>   BC break allowed - but discouraged</td>
</tr>
<tr>
<td>beta release            </td><td>   bug fixes           </td><td>   0.9.0beta1  </td><td>   BC break allowed - but discouraged</td>
</tr>
<tr>
<td>beta release            </td><td>   bug fixes           </td><td>   0.9.0beta2  </td><td>   BC break allowed - but discouraged</td>
</tr>
<tr>
<td>RC release              </td><td>   bug fixes           </td><td>   1.0.0RC1    </td><td>   BC break allowed - but heavily discouraged</td>
</tr>
<tr>
<td>stable release          </td><td>   no changes          </td><td>   1.0.0       </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>stable release          </td><td>   bug fixes           </td><td>   1.0.1       </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>development release     </td><td>   features added      </td><td>   1.1.0dev1   </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>beta release            </td><td>   bug fixes           </td><td>   1.1.0beta1  </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>stable release          </td><td>   bug fixes           </td><td>   1.1.0       </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>stable release          </td><td>   features added      </td><td>   1.2.0       </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>development release     </td><td>   major changes       </td><td>   2.0.0dev1   </td><td>   BC break is allowed</td>
</tr>
<tr>
<td>alpha release           </td><td>   major changes       </td><td>   2.0.0alpha1 </td><td>   BC break is allowed - but discouraged</td>
</tr>
<tr>
<td>beta release            </td><td>   bug fixes           </td><td>   2.0.0beta1  </td><td>   BC break is allowed - but discouraged</td>
</tr>
<tr>
<td>RC release              </td><td>   features added      </td><td>   2.0.0RC1    </td><td>   BC break is allowed - but heavily discouraged</td>
</tr>
<tr>
<td>RC release              </td><td>   bug fixes           </td><td>   2.0.0RC2    </td><td>   BC break is allowed - but heavily discouraged</td>
</tr>
<tr>
<td>stable release          </td><td>   bug fixes           </td><td>   2.0.0       </td><td>   BC break is not allowed</td>
</tr>
<tr>
<td>stable release          </td><td>   bug fixes           </td><td>   2.0.1       </td><td>   BC break is not allowed</td>
</tr>
</table>

<h4>Automation</h4>

<p>It should be possible to turn this rule set into a little tool which can 
compute the next version for you based on questions you answer (like is 
this the first release, did this release break BC?, what state should 
this release have etc.)</p>

<p>This should make it possible to generate correct version numbers without 
going through this rather lengthy list.</p>

<p>A sample implementation can be found at 
<?php print_link("http://www.backendmedia.com/PEAR/version_generator.phps"); ?>.</p>

<p>Please note that this implementation currently does not enforce the 
usage of the patch level version number and still uses the state &quot;RC&quot; 
instead of &quot;beta&quot; for release candidates.</p>

<p>Future versions of the PEAR packager will hopefully include 
functionality validate the version name based on relevant metadata set 
in the package.xml and by comparing the package API with prior versions.</p>

<?php

echo make_link('/group/', 'Back');

response_footer();
