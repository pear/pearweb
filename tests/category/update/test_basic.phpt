--TEST--
category::update()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addUpdateQuery("UPDATE categories SET name = 'rename', description = 'new desc' WHERE id = 1", array(), 1);
$mock->addDataQuery("SELECT * FROM categories WHERE name = 'rename'", array(
    array('id' => 1, 'parent' => 0, 'name' => 'rename', 'summary' => 'hi', 'description' => 'new desc',
    'npackages' => 0, 'pkg_left' => 0, 'pkg_right' => 0, 'cat_left' => 0, 'cat_right' => 0)
), array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
    'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'rename' AND p.approved = 1", array(
    array('name' => 'hi'),
), array('name'));
$mock->addDataQuery("SELECT * FROM categories ORDER BY name", array(
    array('id' => 1, 'parent' => 0, 'name' => 'rename', 'summary' => 'hi', 'description' => 'new desc',
    'npackages' => 0, 'pkg_left' => 0, 'pkg_right' => 0, 'cat_left' => 0, 'cat_right' => 0)
), array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
    'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'rename'", array(
                    array('id' => 1, 'name' => 'Blah1'),
                    array('id' => 2, 'name' => 'Blah2'),
                ), array('id', 'name'));
$mock->addDataQuery("SELECT name FROM categories WHERE id = 1", array(array('name' => 'test')),
    array('name'));
// necessary setup for savePackagesCategoryREST()
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
    ), array('version', 'id', 'doneby', 'license', 'summary', 'description', 'releasedate',
    'releasenotes', 'state'));
$mock->addDataQuery("SELECT id, nby, ntime, note FROM notes WHERE pid = 1",
    array(), array('id', 'nby', 'ntime', 'note'));
$mock->addDataQuery("SELECT type, relation, version, name, `release` as `release`, optional
                     FROM deps
                     WHERE package = 1 ORDER BY optional ASC", array(),
                     array('type', 'relation', 'version', 'name', 'release', 'optional'));
$pear_rest->savePackageREST('Blah1');
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
                     array('type', 'relation', 'versino', 'name', 'release', 'optional'));
$pear_rest->savePackageREST('Blah2');

// to make sure old files are deleted
$mock->addInsertQuery("INSERT INTO categories (id, name, description, parent)VALUES (1, 'test', 'none', NULL)", array(), array());
$mock->addDataQuery("select max(cat_right) + 1 from categories
                              where parent is null", array(array('m' => 1)), array('m'));
$mock->addUpdateQuery("update categories
                        set cat_left = 1, cat_right = 2
                        where id = 1", array(), array());
$mock->addDataQuery("SELECT * FROM categories WHERE name = 'test'", array(
    array('id' => 1, 'parent' => 0, 'name' => 'test', 'summary' => 'hi', 'description' => 'old desc',
    'npackages' => 0, 'pkg_left' => 0, 'pkg_right' => 0, 'cat_left' => 0, 'cat_right' => 0)
), array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
    'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = 'pear' AND p.category = c.id AND c.name = 'test' AND p.approved = 1", array(
    array('name' => 'hi'),
), array('name'));
$mock->addDataQuery("SELECT * FROM categories ORDER BY name", array(
    array('id' => 1, 'parent' => 0, 'name' => 'test', 'summary' => 'hi', 'description' => 'old desc',
    'npackages' => 0, 'pkg_left' => 0, 'pkg_right' => 0, 'cat_left' => 0, 'cat_right' => 0)
), array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left',
    'pkg_right', 'cat_left', 'cat_right'));
$mock->addDataQuery("SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = 'test'", array(
                ), array('id', 'name'));    
category::add(array('name' => 'test'));


// ============= test =============
$mock->queries = array(); // start over
$id = category::update(1, 'rename', 'new desc');
$phpt->assertEquals(1, $id, 'id');
$phpt->assertEquals(array (
  0 => 'SELECT name FROM categories WHERE id = 1',
  1 => 'UPDATE categories SET name = \'rename\', description = \'new desc\' WHERE id = 1',
  2 => 'SELECT * FROM categories WHERE name = \'rename\'',
  3 => 'SELECT p.name AS name FROM packages p, categories c WHERE p.package_type = \'pear\' AND p.category = c.id AND c.name = \'rename\' AND p.approved = 1',
  4 => 'SELECT * FROM categories ORDER BY name',
  5 => 'SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = \'rename\'',
), $mock->queries, 'queries');
$phpt->assertFileNotExists($restdir . '/c/test/info.xml', 'info.xml');
$phpt->assertFileExists($restdir . '/c/rename/info.xml', 'r info.xml');
$phpt->assertFileNotExists($restdir . '/c/test/packages.xml', 'packages.xml');
$phpt->assertFileExists($restdir . '/c/rename/packages.xml', 'r packages.xml');
$phpt->assertFileNotExists($restdir . '/c/test/packagesinfo.xml', 'packagesinfo.xml');
$phpt->assertFileExists($restdir . '/c/rename/packagesinfo.xml', 'r packagesinfo.xml');
$phpt->assertFileExists($restdir . '/c/categories.xml', 'categories.xml');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===