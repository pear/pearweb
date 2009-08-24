<?php /* vim: set noet ts=4 sw=4: : */
/**
 * User interface for viewing and editing bug details
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id: quick-fix-desc.php 252104 2008-02-02 21:23:23Z dufuz $
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';

response_header('How to retrieve the package version information');
?>
<h2>How to retrieve the version of your package</h2>
<p>
 When reporting a bug, it is very helpful if you tell us which version
 of the package you are using. You can obtain this number by opening
 a shell and typing:
</p>
<pre>pear list|grep $packagename</pre>
<p>
 Alternatively, type in <tt>pear list</tt> alone and search the
 output for your package.
</p>
<?php
response_footer();