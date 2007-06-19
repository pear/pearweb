--TEST--
release-upload.php [upload success, no releases, trying to release stable, version < 1.0.0]
--COOKIE--
PEAR_USER=cellog;PEAR_PW=hi
--POST--
upload=1
--UPLOAD--
distfile=test_upload_step2/Archive_Tar-0.1.0.tgz
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = '/release-upload.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';
$_COOKIE['PEAR_USER'] = 'cellog';
$_COOKIE['PEAR_PW'] = 'hi';
$moresetup = dirname(__FILE__) . '/test_upload_noreleases.php.inc';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/release-upload.php';
$phpt->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'cellog\' AND registered = \'1\'',
  1 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  2 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  3 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  4 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  5 => 'SELECT name FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  6 => 'SELECT p.id FROM packages p WHERE ((p.package_type = \'pear\' AND p.approved = 1) OR p.package_type = \'pecl\') AND  p.name = \'Archive_Tar\'',
  7 => 'SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 1 ORDER BY releasedate DESC',
  8 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  9 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--CLEAN--
<?php
include dirname(__FILE__) . '/teardown.php.inc';
unlinkdir(dirname(__FILE__) . '/rest');
rmdir(dirname(__FILE__) . '/rest');
?>
--EXPECTF--
%s
 <title>PEAR :: Upload New Release :: Verify</title>
%s
<!-- START MAIN CONTENT -->

  <td class="content">

    <div class="errors">ERRORS:<br />You must correct your package.xml file:<ul><li>The first release of a package must be 'alpha' or 'beta', not 'stable'.  Try releasing version 1.0.0RC1, state 'beta'</li>
<li>Versions &lt; 1.0.0 may not be 'stable'</li>
</ul></div>
<form action="release-upload.php" method="post" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Please verify that the following release information is correct:
 </caption>
 <tr>
  <th class="form-label_left">Package:</th>
  <td class="form-input">
  Archive_Tar
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Version:</th>
  <td class="form-input">
  0.1.0
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Summary:</th>
  <td class="form-input">
  Tar file management class
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Description:</th>
  <td class="form-input">
  This class provides handling of tar files in PHP.<br />
It supports creating, listing, extracting and adding to tar files.<br />
Gzip support is available if PHP has the zlib extension built-in or<br />
loaded. Bz2 compression is also supported with the bz2 extension loaded.
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release State:</th>
  <td class="form-input">
  stable
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release Date:</th>
  <td class="form-input">
  2007-06-18
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release Notes:</th>
  <td class="form-input">
  Correct Bug #4016<br />
Remove duplicate remove error display with '@'<br />
Correct Bug #3909 : Check existence of OS_WINDOWS constant<br />
Correct Bug #5452 fix for &quot;lone zero block&quot; when untarring packages<br />
Change filemode (from pear-core/Archive/Tar.php v.1.21)<br />
Correct Bug #6486 Can not extract symlinks<br />
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal	<br />
Correct Bug #8114 Files added on-the-fly not storing date<br />
Correct Bug #9352 Bug on _dirCheck function over nfs path
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Package Type:</th>
  <td class="form-input">
  PHP package
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="cancel" value="Cancel" />
  </td>
 </tr>
</table>
<input type="hidden" name="distfile" value="pear-%s.tgz" />
<input type="hidden" name="_fields" value="verify:cancel:distfile" />
</form>


  </td>

<!-- END MAIN CONTENT -->
%s
</html>