--TEST--
release-upload.php [upload success (releaser is lead)]
--COOKIE--
PEAR_USER=cellog;PEAR_PW=hi
--POST--
verify=1&distfile=Archive_Tar-1.3.2.tgz
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
$_ENV['PEAR_TARBALL_DIR'] = dirname(__FILE__) . '/tarballs';
mkdir($_ENV['PEAR_TARBALL_DIR']);
$moresetup = dirname(__FILE__) . '/test_upload_success1.php.inc';
require dirname(__FILE__) . '/setup.php.inc';
@unlink(PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']));
copy(dirname(__FILE__) . '/test_upload_step1/Archive_Tar-1.3.2.tgz',
    PEAR_UPLOAD_TMPDIR . '/Archive_Tar-1.3.2.tgz');
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/release-upload.php';
$phpt->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'cellog\' AND registered = \'1\'',
  1 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  2 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  3 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  4 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  5 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  6 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
  7 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  8 => 'SELECT role FROM maintains WHERE handle = \'cellog\' AND package = 1 AND role = \'lead\'',
  9 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  10 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  11 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
  12 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  13 => 'SELECT role FROM maintains WHERE handle = \'cellog\' AND package = 1',
  14 => 'SELECT name FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  15 => 'SELECT id, name FROM packages WHERE package_type = \'pear\' AND approved = 1 ORDER BY name',
  16 => 'SELECT p.id AS packageid, p.name AS name, p.package_type AS type, c.id AS categoryid, c.name AS category, p.stablerelease AS stable, p.license AS license, p.summary AS summary, p.homepage AS homepage, p.description AS description, p.cvs_link AS cvs_link, p.doc_link as doc_link, p.unmaintained AS unmaintained,p.newpk_id AS newpk_id,
              p.newpackagename as new_package,
              p.newchannel as new_channel,
              p.blocktrackbacks FROM packages p, categories c WHERE p.package_type = \'pear\' AND p.approved = 1 AND  c.id = p.category AND p.name = \'Archive_Tar\'',
  17 => 'SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 1 ORDER BY releasedate DESC',
  18 => 'SELECT id, nby, ntime, note FROM notes WHERE pid = 1',
  19 => 'SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 1 ORDER BY optional ASC',
  20 => 'SELECT c.name FROM categories c, packages p WHERE c.id = p.category AND p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  21 => 'SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = \'Hungry Hungry Hippo\'',
  22 => 'UPDATE packages SET license = \'PHP License\', summary = \'Tar file management class\', description = \'This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.\' WHERE id=1',
  23 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  24 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
  25 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  26 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
  27 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  28 => 'SELECT role FROM maintains WHERE handle = \'cellog\' AND package = 1 AND role = \'lead\'',
  29 => 'SELECT name FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  30 => 'SELECT handle, role, active FROM maintains WHERE package = 1 ORDER BY active DESC',
  31 => 'SELECT handle FROM users WHERE handle=\'ssb\'',
  32 => 'INSERT INTO maintains (handle, package, role, active) VALUES (\'ssb\', 1, \'helper\', 0)',
  33 => 'SELECT name FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.id = 1',
  34 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  35 => 'SELECT * FROM maintains WHERE package = 1',
  36 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  37 => 'SELECT * FROM maintains WHERE package = 1',
  38 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  39 => 'SELECT role FROM maintains WHERE handle = \'cellog\' AND package = 1',
  40 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  41 => 'SELECT role FROM maintains WHERE handle = \'cellog\' AND package = 1',
  42 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  43 => 'SELECT version FROM releases WHERE package = 1 AND version = \'1.3.2\'',
  44 => 'INSERT INTO releases (id,package,version,state,doneby,releasedate,releasenotes) VALUES(1,1,\'1.3.2\',\'stable\',\'cellog\',NOW(),\'Correct Bug #4016
Remove duplicate remove error display with \\\'@\\\'
Correct Bug #3909 : Check existence of OS_WINDOWS constant
Correct Bug #5452 fix for "lone zero block" when untarring packages
Change filemode (from pear-core/Archive/Tar.php v.1.21)
Correct Bug #6486 Can not extract symlinks
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal	
Correct Bug #8114 Files added on-the-fly not storing date
Correct Bug #9352 Bug on _dirCheck function over nfs path\')',
  45 => 'INSERT INTO files (id,package,release,md5sum,basename,fullpath,packagexml) VALUES(1,1,1,\'17d49e837b64df4e8f9124f829b22cd1\',\'Archive_Tar-1.3.2.tgz\',\'' . dirname(__FILE__) .
  DIRECTORY_SEPARATOR . 'tarballs' . DIRECTORY_SEPARATOR . 'Archive_Tar-1.3.2.tgz\',\'<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.5.0RC2" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
 <name>Archive_Tar</name>
 <channel>pear.php.net</channel>
 <summary>Tar file management class</summary>
 <description>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</description>
 <lead>
  <name>Gregory Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Vincent Blavet</name>
  <user>vblavet</user>
  <email>vincent@phpconcept.net</email>
  <active>no</active>
 </lead>
 <helper>
  <name>Stig Bakken</name>
  <user>ssb</user>
  <email>stig@php.net</email>
  <active>no</active>
 </helper>
 <date>2007-01-03</date>
 <time>15:31:40</time>
 <version>
  <release>1.3.2</release>
  <api>1.3.2</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license uri="http://www.php.net/license">PHP License</license>
 <notes>Correct Bug #4016
Remove duplicate remove error display with &apos;@&apos;
Correct Bug #3909 : Check existence of OS_WINDOWS constant
Correct Bug #5452 fix for &quot;lone zero block&quot; when untarring packages
Change filemode (from pear-core/Archive/Tar.php v.1.21)
Correct Bug #6486 Can not extract symlinks
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal	
Correct Bug #8114 Files added on-the-fly not storing date
Correct Bug #9352 Bug on _dirCheck function over nfs path</notes>
 <contents>
  <dir name="/">
   <file baseinstalldir="/" md5sum="06409d39f4268a9aa9e2924c7f397a38" name="Archive/Tar.php" role="php" />
   <file baseinstalldir="/" md5sum="29b03715377b18b1fafcff98a99cc9a7" name="docs/Archive_Tar.txt" role="doc" />
  </dir>
 </contents>
 <compatible>
  <name>PEAR</name>
  <channel>pear.php.net</channel>
  <min>1.4.0</min>
  <max>1.5.0RC2</max>
 </compatible>
 <dependencies>
  <required>
   <php>
    <min>4.0.0</min>
   </php>
   <pearinstaller>
    <min>1.4.0b1</min>
   </pearinstaller>
  </required>
 </dependencies>
 <phprelease />
 <changelog>
  <release>
   <version>
    <release>1.3.1</release>
    <api>1.3.1</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2005-03-17</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Correct Bug #3855</notes>
  </release>
  <release>
   <version>
    <release>1.3.0</release>
    <api>1.3.0</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2005-03-06</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Bugs correction (2475, 2488, 2135, 2176)</notes>
  </release>
  <release>
   <version>
    <release>1.2</release>
    <api>1.2</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2004-05-08</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Add support for other separator than the space char and bug
	correction</notes>
  </release>
  <release>
   <version>
    <release>1.1</release>
    <api>1.1</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2003-05-28</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>* Add support for BZ2 compression
* Add support for add and extract without using temporary files : methods addString() and extractInString()</notes>
  </release>
  <release>
   <version>
    <release>1.0</release>
    <api>1.0</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2003-01-24</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Change status to stable</notes>
  </release>
  <release>
   <version>
    <release>0.10-b1</release>
    <api>0.10-b1</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2003-01-08</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Add support for long filenames (greater than 99 characters)</notes>
  </release>
  <release>
   <version>
    <release>0.9</release>
    <api>0.9</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2002-05-27</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Auto-detect gzip&apos;ed files</notes>
  </release>
  <release>
   <version>
    <release>0.4</release>
    <api>0.4</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2002-05-20</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Windows bugfix: use forward slashes inside archives</notes>
  </release>
  <release>
   <version>
    <release>0.2</release>
    <api>0.2</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2002-02-18</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>From initial commit to stable</notes>
  </release>
  <release>
   <version>
    <release>0.3</release>
    <api>0.3</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2002-04-13</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>Windows bugfix: used wrong directory separators</notes>
  </release>
 </changelog>
</package>
\')',
  46 => 'INSERT INTO deps (package, `release`, type, relation, version, name, optional) VALUES (1,1,\'php\',\'ge\',\'4.0.0\',\'PHP\',0)',
  47 => 'SELECT name FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  48 => 'INSERT INTO apidoc_queue (filename, queued) VALUES (\'/home/cellog/workspace/pearweb/tests/site/release-upload.php/tarballs/Archive_Tar-1.3.2.tgz\', NOW())',
  49 => 'SELECT id FROM packages p WHERE p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  50 => 'SELECT * FROM releases WHERE package = 1 ORDER BY releasedate DESC',
  51 => 'SELECT release, version FROM deps WHERE package = 1 AND type="php" and relation="ge"',
  52 => 'SELECT packagexml FROM files WHERE package = 1 AND
                release = \'1\'',
  53 => 'SELECT releasedate FROM releases WHERE id = 1',
  54 => 'SELECT c.name FROM categories c, packages p WHERE c.id = p.category AND p.package_type = \'pear\' AND p.approved = 1 AND  p.name = \'Archive_Tar\'',
  55 => 'SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = \'Hungry Hungry Hippo\'',
  56 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  57 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
  58 => 'SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = \'cellog\' AND
                 p.id = m.package AND
                 m.role = \'lead\'',
), $mock->queries, 'queries');

$phpt->assertFileExists(dirname(__FILE__) . '/tarballs/Archive_Tar-1.3.2.tgz', 'tgz');
$phpt->assertEquals(17150, filesize(dirname(__FILE__) . '/tarballs/Archive_Tar-1.3.2.tgz'), 'tgz size');
$phpt->assertFileExists(dirname(__FILE__) . '/tarballs/Archive_Tar-1.3.2.tar', 'tar');
$phpt->assertEquals(93184, filesize(dirname(__FILE__) . '/tarballs/Archive_Tar-1.3.2.tar'), 'tar size');

$phpt->assertFileExists($restdir . '/p/packages.xml', 'REST p/packages.xml');
$phpt->assertFileExists($restdir . '/p/archive_tar/info.xml', 'REST p/archive_tar/info.xml');
$phpt->assertFileExists($restdir . '/p/archive_tar/maintainers.xml', 'REST p/archive_tar/maintainers.xml');
$phpt->assertFileExists($restdir . '/p/archive_tar/maintainers2.xml', 'REST p/archive_tar/maintainers2.xml');
$phpt->assertFileExists($restdir . '/r/archive_tar/1.3.2.xml', 'REST r/archive_tar/1.3.2.xml');
$phpt->assertFileExists($restdir . '/r/archive_tar/allreleases.xml', 'REST r/archive_tar/allreleases.xml');
$phpt->assertFileExists($restdir . '/r/archive_tar/allreleases2.xml', 'REST r/archive_tar/allreleases2.xml');
$phpt->assertFileExists($restdir . '/r/archive_tar/deps.1.3.2.txt', 'REST r/archive_tar/deps.13.2.txt');
$phpt->assertFileExists($restdir . '/r/archive_tar/latest.txt', 'REST r/archive_tar/latest.txt');
$phpt->assertFileExists($restdir . '/r/archive_tar/package.1.3.2.xml', 'REST r/archive_tar/package.1.3.2.xml');
$phpt->assertFileExists($restdir . '/r/archive_tar/stable.txt', 'REST r/archive_tar/stable.txt');
$phpt->assertFileExists($restdir . '/r/archive_tar/v2.1.3.2.xml', 'REST r/archive_tar/v2.1.3.2.xml');
$phpt->assertFileExists($restdir . '/c/Hungry+Hungry+Hippo/packagesinfo.xml', 'REST c/Hungry+Hungry+Hippo/packagesinfo.xml');
__halt_compiler();
?>
===DONE===
--CLEAN--
<?php
include dirname(__FILE__) . '/teardown.php.inc';
unlinkdir(dirname(__FILE__) . '/tarballs');
rmdir(dirname(__FILE__) . '/tarballs');
unlinkdir(dirname(__FILE__) . '/rest');
rmdir(dirname(__FILE__) . '/rest');
?>
--EXPECTF--
%s
 <title>PEAR :: Upload New Release</title>
%s
<!-- START MAIN CONTENT -->

  <td class="content">

    <h1>Upload New Release</h1>
<div class="success">Version 1.3.2 of Archive_Tar has been successfully released, and its promotion cycle has started.</div>
</p></div><p>
Upload a new package distribution file built using &quot;<code>pear
package</code>&quot; here.  The information from your package.xml file will
be displayed on the next screen for verification. The maximum file size
is 16 MB.
</p>

<p>
Uploading new releases is restricted to each package's lead developer(s).
</p><form action="release-upload.php" method="post" enctype="multipart/form-data" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Upload
 </caption>
 <tr>
  <th class="form-label_left"><label for="f" accesskey="i">D<span class="accesskey">i</span>stribution File</label></th>
  <td class="form-input">
   <input type="hidden" name="MAX_FILE_SIZE" value="16777216" />
   <input type="file" name="distfile" size="40" id="f"/>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="upload" value="Upload!" />
  </td>
 </tr>
</table>
<input type="hidden" name="_fields" value="distfile:upload" />
</form>


  </td>

<!-- END MAIN CONTENT -->
%s
</html>