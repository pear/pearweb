--TEST--
category::getRecent()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT p.id AS id, p.name AS name, p.summary AS summary, r.version AS version, r.releasedate AS releasedate, r.releasenotes AS releasenotes, r.doneby AS doneby, r.state AS state FROM packages p, releases r, categories c WHERE p.package_type = 'pear' AND p.id = r.package AND p.category = c.id AND c.name = 'test'ORDER BY r.releasedate DESC LIMIT 0, 1", array(), array());
$mock->addDataQuery("SELECT p.id AS id, p.name AS name, p.summary AS summary, r.version AS version, r.releasedate AS releasedate, r.releasenotes AS releasenotes, r.doneby AS doneby, r.state AS state FROM packages p, releases r, categories c WHERE p.package_type = 'pear' AND p.id = r.package AND p.category = c.id AND c.name = 'test2'ORDER BY r.releasedate DESC LIMIT 0, 1", array(
                    array(
                        'id' => 2,
                        'name' => 'Bar',
                        'summary' => 'hi there',
                        'version' => '0.1.2',
                        'releasedate' => '2007-03-20 13:25:25',
                        'releasenotes' => 'blah blah blah',
                        'doneby' => 'cellog',
                        'state' => 'alpha',
                        ),
                ), array('id', 'name', 'summary', 'version', 'releasedate', 'releasenotes',
                         'doneby', 'state'));
$mock->addDataQuery("SELECT p.id AS id, p.name AS name, p.summary AS summary, r.version AS version, r.releasedate AS releasedate, r.releasenotes AS releasenotes, r.doneby AS doneby, r.state AS state FROM packages p, releases r, categories c WHERE p.package_type = 'pear' AND p.id = r.package AND p.category = c.id AND c.name = 'test2'ORDER BY r.releasedate DESC LIMIT 0, 2", array(
                    array(
                        'id' => 2,
                        'name' => 'Bar',
                        'summary' => 'hi there',
                        'version' => '0.1.2',
                        'releasedate' => '2007-03-20 13:25:25',
                        'releasenotes' => 'blah blah blah',
                        'doneby' => 'cellog',
                        'state' => 'alpha',
                        ),
                    array(
                        'id' => 3,
                        'name' => 'Foo',
                        'summary' => 'hi there',
                        'version' => '0.1.2',
                        'releasedate' => '2007-03-20 13:25:25',
                        'releasenotes' => 'blah blah blah',
                        'doneby' => 'cellog',
                        'state' => 'alpha',
                        ),
                ), array('id', 'name', 'summary', 'version', 'releasedate', 'releasenotes',
                         'doneby', 'state'));

// test
$packages = category::getRecent(1, 'test');
$phpunit->assertEquals(array(), $packages, 'test 1');
$packages = category::getRecent(1, 'test2');
$phpunit->assertEquals(array (
  0 => 
  array (
    'id' => 2,
    'name' => 'Bar',
    'summary' => 'hi there',
    'version' => '0.1.2',
    'releasedate' => '2007-03-20 13:25:25',
    'releasenotes' => 'blah blah blah',
    'doneby' => 'cellog',
    'state' => 'alpha',
  ),
), $packages, 'test 2');
$packages = category::getRecent(2, 'test2');
$phpunit->assertEquals(array (
  0 => 
  array (
    'id' => 2,
    'name' => 'Bar',
    'summary' => 'hi there',
    'version' => '0.1.2',
    'releasedate' => '2007-03-20 13:25:25',
    'releasenotes' => 'blah blah blah',
    'doneby' => 'cellog',
    'state' => 'alpha',
  ),
  1 =>
    array(
        'id' => 3,
        'name' => 'Foo',
        'summary' => 'hi there',
        'version' => '0.1.2',
        'releasedate' => '2007-03-20 13:25:25',
        'releasenotes' => 'blah blah blah',
        'doneby' => 'cellog',
        'state' => 'alpha',
        ),
), $packages, 'test 3');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===