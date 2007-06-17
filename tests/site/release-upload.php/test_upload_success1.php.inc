<?php
$restdir = dirname(__FILE__) . '/rest';
mkdir($restdir);
mkdir($restdir . '/c');
require_once 'pear-rest.php';
$pear_rest = new pear_rest($restdir);

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog' AND registered = '1'", array (
  0 => 
  array (
    'handle' => 'cellog',
    'password' => md5('hi'),
    'name' => 'Greg Beaver',
    'email' => 'greg@chiaraquartet.net',
    'homepage' => 'http://greg.chiaraquartet.net',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '1',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => 'http://www.chiaraquartet.net',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.qa','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = 'cellog' AND
                 p.id = m.package AND
                 m.role = 'lead'",
                 array(array('handle' => 'cellog')
                 ),
                 array('handle'
                 ));

$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Archive_Tar'", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.id = 1", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT role FROM maintains WHERE handle = 'cellog' AND package = 1 AND role = 'lead'", array(array('role' => 'lead')), array('role'));
$mock->addDataQuery("SELECT role FROM maintains WHERE handle = 'cellog' AND package = 1",
    array(array('role' => 'lead')), array('role'));
// rest generation
$mock->addDataQuery("SELECT name FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.id = 1",
    array(array('name' => 'Archive_Tar')), array('name'));
$mock->addDataQuery("SELECT id, name FROM packages WHERE package_type = 'pear' AND approved = 1 ORDER BY name",
    array(array('id' => 1, 'name' => 'Archive_Tar')), array('id', 'name'));
$mock->addDataQuery("SELECT p.id AS packageid, p.name AS name, p.package_type AS type, c.id AS categoryid, c.name AS category, p.stablerelease AS stable, p.license AS license, p.summary AS summary, p.homepage AS homepage, p.description AS description, p.cvs_link AS cvs_link, p.doc_link as doc_link, p.unmaintained AS unmaintained,p.newpk_id AS newpk_id,
              p.newpackagename as new_package,
              p.newchannel as new_channel,
              p.blocktrackbacks FROM packages p, categories c WHERE p.package_type = 'pear' AND p.approved = 1 AND  c.id = p.category AND p.name = 'Archive_Tar'",
    array(array(
        'packageid' => 1,
        'name' => 'Archive_Tar',
        'type' => 'pear',
        'categoryid' => 1,
        'category' => 'Hungry Hungry Hippo',
        'stable' => null, 
        'license' => 'PHP License',
        'summary' => 'hi',
        'homepage' => null,
        'description' => 'hi',
        'cvs_link' => null,
        'doc_link' => null,
        'unmaintained' => 0,
        'newpkd_id' => null,
        'new_package' => null,
        'new_channel' => null
    )),
    array('packageid', 'name', 'type', 'categoryid', 'category', 'stable', 'license',
    'summary', 'homepage', 'description', 'cvs_link', 'doc_link', 'unmaintained',
    'newpkd_id', 'new_package', 'new_channel'));

$mock->addDataQuery("SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 1 ORDER BY releasedate DESC",
array (
  0 => 
  array (
    'version' => '2.1.6',
    'id' => '2246',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2005-04-23 00:00:04',
    'releasenotes' => '- Fixed bug #4047.
     - Fixed backward compatibility with PHP 4.x
     - Added PHP_AUTH_DIGEST support.',
    'state' => 'stable',
  ),
  1 => 
  array (
    'version' => '2.1.6RC1',
    'id' => '2198',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2005-04-04 17:10:18',
    'releasenotes' => '- Fixed bug #4047.
     - Fixed backward compatibility with PHP 4.x
     - Added PHP_AUTH_DIGEST support.',
    'state' => 'beta',
  ),
  2 => 
  array (
    'version' => '2.1.4',
    'id' => '1966',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2005-01-21 19:34:11',
    'releasenotes' => '- Fixed a bug #2380: constractor couldn\'t handle non-array option.
- The first stable release with HTTP Digest Authenthication support.',
    'state' => 'stable',
  ),
  3 => 
  array (
    'version' => '2.1.3rc1',
    'id' => '1641',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2004-08-22 09:04:44',
    'releasenotes' => '- fixed incompatibility problem with Auth 1.2.x.',
    'state' => 'beta',
  ),
  4 => 
  array (
    'version' => '2.1.1',
    'id' => '1559',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2004-07-13 09:51:06',
    'releasenotes' => '- Fixed a bug #1634 (URI parameter was handled incorrectry.)',
    'state' => 'beta',
  ),
  5 => 
  array (
    'version' => '2.1.0',
    'id' => '1436',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2004-05-30 05:39:36',
    'releasenotes' => '- Added _sessionName which fixes a major issue with realm sharing.
- Added sessionSharing option to use unique session id.
  Currently, this option is set to true by default to maintain
  backward compatibility.
- Added setOption and getOption to set/get option value.
- Starting with this release, HTTP Digest Authentication (RFC2617) is
  experimentally supported. The code for HTTP Digest Authentication is 
  originally developed by Tom Pike.',
    'state' => 'beta',
  ),
  6 => 
  array (
    'version' => '2.1.0RC2',
    'id' => '1296',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2004-04-17 02:58:43',
    'releasenotes' => '- Added _sessionName which fixes a major issue with realm sharing.
- Added sessionSharing option to use unique session id.
  Currently, this option is set to true by default to maintain
  backward compatibility.
- Added setOption and getOption to set/get option value.
- Starting with this release, HTTP Digest Authentication (RFC2617) is
  experimentally supported. The code for HTTP Digest Authentication is 
  originally developed by Tom Pike.',
    'state' => 'beta',
  ),
  7 => 
  array (
    'version' => '2.1RC1',
    'id' => '1287',
    'doneby' => 'hirokawa',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2004-04-13 19:04:30',
    'releasenotes' => '- Added sessionSharing option to use unique session id.
  Currently, this option is set to true by default to maintain
  backward compatibility.
- Added setOption and getOption to set/get option value.
- Starting with this release, HTTP Digest Authentication (RFC2617) is
  experimentally supported. The code for HTTP Digest Authentication is 
  originally developed by Tom Pike.',
    'state' => 'beta',
  ),
  8 => 
  array (
    'version' => '2.0',
    'id' => '916',
    'doneby' => 'mj',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2003-10-16 19:45:00',
    'releasenotes' => 'Starting with this release, the code will not be placed in
Auth_HTTP/Auth_HTTP.php anymore. Instead Auth/HTTP.php is used, which
conforms to the PEAR standards.

In order to make use of the new version, you will need to change your
scripts to include the file at the new location! The old version in
Auth_HTTP/Auth_HTTP.php will not be removed when upgrading.

Other changes:

* If it is possible, the session ID will be chosen based on the user\'s
  authentication credentials. (Patch by: Marko Karppinen)
* Cleaned up internal variable usage. (Patch by: Brad Bulger)',
    'state' => 'stable',
  ),
  9 => 
  array (
    'version' => '1.0.1',
    'id' => '134',
    'doneby' => 'mj',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2002-07-01 20:27:00',
    'releasenotes' => 'This release makes Auth_HTTP compatible to the new PEAR installation system.',
    'state' => 'stable',
  ),
  10 => 
  array (
    'version' => '1.0',
    'id' => '7',
    'doneby' => 'mj',
    'license' => NULL,
    'summary' => NULL,
    'description' => NULL,
    'releasedate' => '2002-01-21 11:49:00',
    'releasenotes' => 'This is the initial independent release of the Auth_HTTP package.',
    'state' => 'stable',
  ),
), array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
    'releasenotes', 'state'));
$mock->addDataQuery("SELECT id, nby, ntime, note FROM notes WHERE pid = 1", array(),
    array('id', 'nby', 'ntime', 'note'));
$mock->addDataQuery("SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 1 ORDER BY optional ASC", array(), array('type',
                    'relation', 'version', 'name', 'release', 'optional'));
$mock->addDataQuery("SELECT c.name FROM categories c, packages p WHERE c.id = p.category AND p.package_type = 'pear' AND p.approved = 1 AND  p.id = 1", array(array('name' => 'Hungry Hungry Hippo')),
    array('name'));
// release saving queries
$mock->addUpdateQuery("UPDATE packages SET license = 'PHP License', summary = 'Tar file management class', description = 'This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.' WHERE id=1", array(), 1);
$mock->addDataQuery("SELECT handle, role, active FROM maintains WHERE package = 1 ORDER BY active DESC",
    array(
        array('handle' => 'cellog', 'role' => 'lead', 'active' => 1),
        array('handle' => 'vblavet', 'role' => 'lead', 'active' => 0),
        array('handle' => 'deleteduser', 'role' => 'helper', 'active' => 0),
    ),
    array('handle', 'role', 'active')
);
$mock->addDataQuery("SELECT handle FROM users WHERE handle='ssb'", array(array('handle' => 'ssb')), array('handle'));
$mock->addInsertQuery("INSERT INTO maintains (handle, package, role, active) VALUES ('ssb', 1, 'helper', 0)", array(), 1);
$mock->addDeleteQuery("DELETE FROM maintains WHERE package = 1 AND handle = 'deleteduser'",
    array(), 1);
// more REST setup
$mock->addDataQuery("SELECT * FROM maintains WHERE package = 1",
    array(
        array('handle' => 'cellog', 'role' => 'lead', 'active' => 1),
        array('handle' => 'vblavet', 'role' => 'lead', 'active' => 0),
        array('handle' => 'ssb', 'role' => 'helper', 'active' => 0),
    ),
    array('handle', 'role', 'active')
);
// release version test - is this release already out there?
$mock->addDataQuery("SELECT version FROM releases WHERE package = 1 AND version = '1.3.2'",
    array(), array('version'));
$mock->addInsertQuery("INSERT INTO releases (id,package,version,state,doneby,releasedate,releasenotes) VALUES(1,1,'1.3.2','stable','cellog',NOW(),'Correct Bug #4016
Remove duplicate remove error display with \'@\'
Correct Bug #3909 : Check existence of OS_WINDOWS constant
Correct Bug #5452 fix for \"lone zero block\" when untarring packages
Change filemode (from pear-core/Archive/Tar.php v.1.21)
Correct Bug #6486 Can not extract symlinks
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal	
Correct Bug #8114 Files added on-the-fly not storing date
Correct Bug #9352 Bug on _dirCheck function over nfs path')", array(), 1);
$mock->addInsertQuery("INSERT INTO files (id,package,release,md5sum,basename,fullpath,packagexml) VALUES(1,1,1,'17d49e837b64df4e8f9124f829b22cd1','Archive_Tar-1.3.2.tgz','/home/cellog/workspace/pearweb/tests/site/release-upload.php/tarballs/Archive_Tar-1.3.2.tgz','<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<package packagerversion=\"1.5.0RC2\" version=\"2.0\" xmlns=\"http://pear.php.net/dtd/package-2.0\" xmlns:tasks=\"http://pear.php.net/dtd/tasks-1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd\">
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
 <license uri=\"http://www.php.net/license\">PHP License</license>
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
  <dir name=\"/\">
   <file baseinstalldir=\"/\" md5sum=\"06409d39f4268a9aa9e2924c7f397a38\" name=\"Archive/Tar.php\" role=\"php\" />
   <file baseinstalldir=\"/\" md5sum=\"29b03715377b18b1fafcff98a99cc9a7\" name=\"docs/Archive_Tar.txt\" role=\"doc\" />
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
   <notes>Windows bugfix: used wrong directory separators</notes>
  </release>
 </changelog>
</package>
')", array(), 1);
$mock->addInsertQuery("INSERT INTO deps (package, `release`, type, relation, version, name, optional) VALUES (1,1,'php','ge','4.0.0','PHP',0)", array(), 1);
$mock->addDataQuery("SELECT name FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Archive_Tar'",
    array(
        array('name' => 'Archive_Tar')
    ), array('name'));
$mock->addInsertQuery("INSERT INTO apidoc_queue (filename, queued) VALUES ('" . dirname(__FILE__) .
    DIRECTORY_SEPARATOR . 'tarballs' . DIRECTORY_SEPARATOR . "Archive_Tar-1.3.2.tgz', NOW())",
        array(), 1);
$mock->addDataQuery("SELECT * FROM releases WHERE package = 1 ORDER BY releasedate DESC",
    array(
  9 => 
  array (
    'version' => '1.3.2',
    'id' => '1',
    'doneby' => 'cellog',
    'license' => 'PHP License',
    'summary' => 'Tar file management class',
    'description' => 'This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.',
    'releasedate' => '2002-07-01 20:27:00',
    'releasenotes' => 'Correct Bug #4016
Remove duplicate remove error display with \';@\'
Correct Bug #3909 : Check existence of OS_WINDOWS constant
Correct Bug #5452 fix for "lone zero block" when untarring packages
Change filemode (from pear-core/Archive/Tar.php v.1.21)
Correct Bug #6486 Can not extract symlinks
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal	
Correct Bug #8114 Files added on-the-fly not storing date
Correct Bug #9352 Bug on _dirCheck function over nfs path',
    'state' => 'stable',
  ),
    ),
    array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
    'releasenotes', 'state'));
$mock->addDataQuery('SELECT release, version FROM deps WHERE package = 1 AND type="php" and relation="ge"', array(array('release' => '1.3.2', 'version' => '4.0.0')), arraY('release', 'version'));
$mock->addDataQuery("SELECT packagexml FROM files WHERE package = 1 AND
                release = '1'", array(
    array('packagexml' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<package packagerversion=\"1.5.0RC2\" version=\"2.0\" xmlns=\"http://pear.php.net/dtd/package-2.0\" xmlns:tasks=\"http://pear.php.net/dtd/tasks-1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd\">
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
 <license uri=\"http://www.php.net/license\">PHP License</license>
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
  <dir name=\"/\">
   <file baseinstalldir=\"/\" md5sum=\"06409d39f4268a9aa9e2924c7f397a38\" name=\"Archive/Tar.php\" role=\"php\" />
   <file baseinstalldir=\"/\" md5sum=\"29b03715377b18b1fafcff98a99cc9a7\" name=\"docs/Archive_Tar.txt\" role=\"doc\" />
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
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
   <license uri=\"http://www.php.net/license\">PHP License</license>
   <notes>Windows bugfix: used wrong directory separators</notes>
  </release>
 </changelog>
</package>
")
                ), array('packagexml'));
$mock->addDataQuery("SELECT releasedate FROM releases WHERE id = 1",
    array(
        array('releasedate' => '2002-07-01 20:27:00')
    ), array('releasedate'));
$mock->addDataQuery("SELECT c.name FROM categories c, packages p WHERE c.id = p.category AND p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Archive_Tar'",
    array(
        array('name' => 'Hungry Hungry Hippo'),
    ), array('name'));
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'Hungry Hungry Hippo'",
array(array('id' => 1, 'name' => 'Archive_Tar')), array('id', 'name'));