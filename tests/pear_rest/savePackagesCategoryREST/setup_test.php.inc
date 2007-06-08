<?php
// this test has extensive setup because we need to create the
// REST for each package in the category plus releases
// as savePackagesCategoryREST groks data from files rather than the database
$mock->addDataQuery("SELECT p.id AS packageid, p.name AS name, p.package_type AS type, c.id AS categoryid, c.name AS category, p.stablerelease AS stable, p.license AS license, p.summary AS summary, p.homepage AS homepage, p.description AS description, p.cvs_link AS cvs_link, p.doc_link as doc_link, p.unmaintained AS unmaintained,p.newpk_id AS newpk_id,
              p.newpackagename as new_package,
              p.newchannel as new_channel,
              p.blocktrackbacks FROM packages p, categories c WHERE p.package_type = 'pear' AND p.approved = 1 AND  c.id = p.category AND p.name = 'Blah1'", array(
                array('packageid' => 1, 'name' => 'Blah1', 'type' => 'pear',
                'categoryid' => 1, 'category' => 'rename',
                'stable' => null, 'license' => 'BSD License',
                'summary' => 'Blah1', 'homepage' => null, 'description' => 'Hi Blah1',
                'cvs_link' => null,
                'doc_link' => null, 'unmaintained' => 0, 'newpk_id' => null,
                'new_package' => null, 'new_channel' => null)
              ),
              array('packageid', 'name', 'type', 'categoryid', 'category',
                'stable', 'license', 'summary', 'homepage', 'description', 'cvs_link',
                'doc_link', 'unmaintained', 'newpk_id', 'new_package', 'new_channel'));
$mock->addDataQuery("SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 1 ORDER BY releasedate DESC",
    array(
        array('version' => '1.0.0', 'id' => 1, 'doneby' => 'cellog',
        'license' => 'BSD License', 'summary' => 'hi Blah1', 'description' => 'whoa Blah1',
        'releasedate' => '2007-05-23 22:08:00', 'releasenotes' => '1halB', 'state' => 'stable')
    ), array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
    'releasenotes', 'state'));
$mock->addDataQuery("SELECT id, nby, ntime, note FROM notes WHERE pid = 1",
    array(), array('id', 'nby', 'ntime', 'note'));
$mock->addDataQuery("SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 1 ORDER BY optional ASC", array(
                array('type' => 'php', 'relation' => 'ge', 'version' => '5.2.2',
                'name' => '', 'release' => 1, 'optional' => 0),
                array('type' => 'pkg', 'relation' => 'ge', 'version' => '1.0.0',
                'name' => 'Blah2', 'release' => 1, 'optional' => 1),
                array('type' => 'pkg', 'relation' => 'ge', 'version' => '1.0.0',
                'name' => 'Blah3', 'release' => 1, 'optional' => 0),
                     ),
                     array('type', 'relation', 'version', 'name', 'release', 'optional'));
$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Blah1'", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT * FROM releases WHERE package = 1 ORDER BY releasedate DESC",
    array(
  array (
    'id' => '1',
    'package' => '1',
    'version' => '1.0.0',
    'state' => 'stable',
    'doneby' => 'cellog',
    'license' => 'BSD License',
    'summary' => 'hi',
    'description' => 'hi',
    'releasedate' => '2007-03-05 11:31:13',
    'releasenotes' => 'Major bugfix release
blah blah blah',
    'packagefile' => NULL,
  ),
    ), array('id', 'package', 'version', 'state', 'doneby', 'license', 'summary', 'description',
    'releasedate', 'releasenotes', 'packagefile'));
$mock->addDataQuery("SELECT packagexml FROM files WHERE package = 1 AND
                release = '1'", array(
                array('packagexml' => '<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.5.4" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0                              http://pear.php.net/dtd/tasks-1.0.xsd                              http://pear.php.net/dtd/package-2.0                              http://pear.php.net/dtd/package-2.0.xsd">
 <name>Blah1</name>
 <channel>pear.php.net</channel>
 <summary>hi</summary>
 <description>hi</description>
 <lead>
  <name>Greg Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <date>2007-05-08</date>
 <version>
  <release>1.0.0</release>
  <api>0.1.1</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license>BSD License</license>
 <notes>Major bugfix release
blah blah blah</notes>
 <contents>
  <dir baseinstalldir="/" name="/">
   <file name="TODO" role="web" />
  </dir> <!-- / -->
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.3.0</min>
   </php>
   <pearinstaller>
    <min>1.4.11</min>
   </pearinstaller>
   <package>
    <name>Blah3</name>
    <channel>pear.php.net</channel>
    <min>1.0.0</min>
   </package>
  </required>
  <optional>
   <package>
    <name>Blah2</name>
    <channel>pear.php.net</channel>
    <min>1.0.0</min>
   </package>
  </optional>
 </dependencies>
 <phprelease />
</package>
')
                ), array('packagexml'));
$pear_rest->savePackageREST('Blah1');
$mock->addDataQuery('SELECT release, version FROM deps WHERE package = 1 AND type="php" and relation="ge"', array(
    array(
        'release' => 1,
        'version' => '5.2.2',
    )
), array('release', 'version'));
$mock->addDataQuery('SELECT release, version FROM deps WHERE package = 2 AND type="php" and relation="ge"', array(
    array(
        'release' => 1,
        'version' => '5.2.2',
    )
), array('release', 'version'));
$pear_rest->saveAllReleasesREST('Blah1');
file_put_contents($rdir . '/r/blah1/deps.1.0.0.txt', serialize(array(
    array('type' => 'php', 'rel' => 'ge', 'version' => '5.2.2', 'optional' => 'no'),
    array('type' => 'pkg', 'rel' => 'ge', 'version' => '1.0.0', 'optional' => 'yes', 'name' => 'Blah2'),
    array('type' => 'pkg', 'rel' => 'ge', 'version' => '1.0.0', 'optional' => 'no', 'name' => 'Blah3'),
)));



$mock->addDataQuery("SELECT p.id AS packageid, p.name AS name, p.package_type AS type, c.id AS categoryid, c.name AS category, p.stablerelease AS stable, p.license AS license, p.summary AS summary, p.homepage AS homepage, p.description AS description, p.cvs_link AS cvs_link, p.doc_link as doc_link, p.unmaintained AS unmaintained,p.newpk_id AS newpk_id,
              p.newpackagename as new_package,
              p.newchannel as new_channel,
              p.blocktrackbacks FROM packages p, categories c WHERE p.package_type = 'pear' AND p.approved = 1 AND  c.id = p.category AND p.name = 'Blah2'", array(
                array('packageid' => 1, 'name' => 'Blah2', 'type' => 'pear',
                'categoryid' => 1, 'category' => 'rename',
                'stable' => null, 'license' => 'BSD License',
                'summary' => 'Blah2', 'homepage' => null, 'description' => 'Hi Blah2',
                'cvs_link' => null,
                'doc_link' => null, 'unmaintained' => 0, 'newpk_id' => null,
                'new_package' => null, 'new_channel' => null)
              ),
              array('packageid', 'name', 'type', 'categoryid', 'category',
                'stable', 'license', 'summary', 'homepage', 'description', 'cvs_link',
                'doc_link', 'unmaintained', 'newpk_id', 'new_package', 'new_channel'));
$mock->addDataQuery("SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 2 ORDER BY releasedate DESC",
    array(
    ), array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
    'releasenotes', 'state'));
$mock->addDataQuery("SELECT id, nby, ntime, note FROM notes WHERE pid = 2",
    array(), array('id', 'nby', 'ntime', 'note'));
$mock->addDataQuery("SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 2 ORDER BY optional ASC", array(),
                     array('type', 'relation', 'version', 'name', 'release', 'optional'));
$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Blah2'", array(array('id' => 2)), array('id'));
$mock->addDataQuery("SELECT * FROM releases WHERE package = 2 ORDER BY releasedate DESC",
    array(
  array (
    'id' => '2',
    'package' => '2',
    'version' => '1.0.0',
    'state' => 'stable',
    'doneby' => 'cellog',
    'license' => 'BSD License',
    'summary' => 'hi',
    'description' => 'hi',
    'releasedate' => '2007-03-05 11:31:13',
    'releasenotes' => 'Major bugfix release
blah blah blah',
    'packagefile' => NULL,
  ),
    ), array('id', 'package', 'version', 'state', 'doneby', 'license', 'summary', 'description',
    'releasedate', 'releasenotes', 'packagefile'));
$mock->addDataQuery("SELECT packagexml FROM files WHERE package = 2 AND
                release = '2'", array(
                array('packagexml' => '<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.5.4" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0                              http://pear.php.net/dtd/tasks-1.0.xsd                              http://pear.php.net/dtd/package-2.0                              http://pear.php.net/dtd/package-2.0.xsd">
 <name>Blah2</name>
 <channel>pear.php.net</channel>
 <summary>hi</summary>
 <description>hi</description>
 <lead>
  <name>Greg Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <date>2007-05-08</date>
 <version>
  <release>1.0.0</release>
  <api>0.1.1</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license>BSD License</license>
 <notes>Major bugfix release
blah blah blah</notes>
 <contents>
  <dir baseinstalldir="/" name="/">
   <file name="TODO" role="web" />
  </dir> <!-- / -->
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.3.0</min>
   </php>
   <pearinstaller>
    <min>1.4.11</min>
   </pearinstaller>
  </required>
 </dependencies>
 <phprelease />
</package>
')
                ), array('packagexml'));
$pear_rest->savePackageREST('Blah2');
$pear_rest->saveAllReleasesREST('Blah2');
file_put_contents($rdir . '/r/blah2/deps.1.0.0.txt', serialize(false));

$mock->addDataQuery("SELECT * FROM categories WHERE name = 'Halb'",
    array(array('id' => 1,
          'parent' => null,
          'name' => 'Halb',
          'summary' => null,
          'description' => 'hi there',
          'npackages' => 0,
          'pkg_left' => 0,
          'pkg_right' => 0,
          'cat_left' => 1,
          'cat_right' => 2)),
    array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
          'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'Halb' AND p.approved = 1",
    array(
        array('name' => 'Blah1'),
        array('name' => 'Blah2'),
    ), array('name'));
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'Halb'",
    array(
        array('id' => 1, 'name' => 'Blah1'),
        array('id' => 2, 'name' => 'Blah2'),
    ), array('id', 'name'));
$pear_rest->saveCategoryREST('Halb');
