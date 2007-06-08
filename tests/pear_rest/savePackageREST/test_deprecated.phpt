--TEST--
PEAR_REST->savePackageREST() [package is deprecated for other package]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT p.id AS packageid, p.name AS name, p.package_type AS type, c.id AS categoryid, c.name AS category, p.stablerelease AS stable, p.license AS license, p.summary AS summary, p.homepage AS homepage, p.description AS description, p.cvs_link AS cvs_link, p.doc_link as doc_link, p.unmaintained AS unmaintained,p.newpk_id AS newpk_id,
              p.newpackagename as new_package,
              p.newchannel as new_channel,
              p.blocktrackbacks FROM packages p, categories c WHERE p.package_type = 'pear' AND p.approved = 1 AND  c.id = p.category AND p.name = 'Test'",
            array(array(
                'packageid' => 1,
                'name' => 'Test',
                'type' => 'pear',
                'categoryid' => 1,
                'category' => 'test',
                'stable' => null,
                'license' => 'BSD License',
                'summary' => 'hi there',
                'homepage' => null,
                'description' => "Hi you rule\ndude",
                'cvs_link' => '1',
                'doc_link' => '2',
                'unmaintained' => 0,
                'newpk_id' => 2,
                'new_package' => 'Foo',
                'new_channel' => 'gronk.php.net',
                'blocktrackbacks' => 1,
            )),
            array('packageid', 'name', 'type', 'categoryid', 'category', 'stable',
                  'license', 'summary', 'homepage', 'description', 'cvs_link', 'doc_link',
                  'unmaintained', 'newpk_id', 'new_package', 'new_channel', 'blocktrackbacks'));
$mock->addDataQuery("SELECT version, id, doneby, license, summary, description, releasedate, releasenotes, state FROM releases WHERE package = 1 ORDER BY releasedate DESC",
        array(
        ),
        array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
        'releasenotes', 'state'));
$mock->addDataQuery("SELECT id, nby, ntime, note FROM notes WHERE pid = 1", array(), array(
    'id', 'nby', 'ntime', 'note'));
$mock->addDataQuery("SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 1 ORDER BY optional ASC",
        array(),
        array('type', 'relation', 'version', 'name', 'release', 'optional'));

// ======TEST=========== //
$rest->savePackageREST('Test');
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/p/test/info.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/p/test/info.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Test</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/test">test</ca>
 <l>BSD License</l>
 <s>hi there</s>
 <d>Hi you rule
dude</d>
 <r xlink:href="/rest/r/test"/>
 <dc>gronk.php.net</dc>
 <dp> Foo</dp>
</p>', file_get_contents($rdir . '/p/test/info.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===