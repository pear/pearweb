<?php
require dirname(dirname(dirname(__FILE__))) . '/mockDB/DB.php';
set_include_path(dirname(dirname(dirname(dirname(__FILE__)))) . '/include' . PATH_SEPARATOR .
    get_include_path());
require_once 'DB.php';
$_ENV['PEAR_DATABASE_DSN'] = 'mock://pear:pear@localhost/pear';
// so we do local
$mock = new mockDB_core;
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
    'mock' => $mock,
);
$dbh = DB::connect($_ENV['PEAR_DATABASE_DSN'], $options);

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

require_once 'pear-prepend.php';


require dirname(dirname(dirname(__FILE__))) . '/phpt_test.php5.inc';
$phpt = new PEAR_PHPTest;

//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll('
//            SELECT
//                packages.name, releases.version, downloads,
//                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as d
//                FROM releases, packages, aggregated_package_stats a
//                WHERE
//                    packages.name <> "pearweb" AND
//                    packages.name <> "pearweb_phars" AND
//                    packages.id = releases.package AND
//                    packages.package_type = \'pear\' AND
//                    a.release_id = releases.id AND
//                    a.package_id = packages.id AND
//                    packages.newpk_id IS NULL AND
//                    packages.unmaintained = 0 AND
//                    a.yearmonth = "2006-01-01 00:00:00"
//                ORDER BY d DESC LIMIT 0, 5', array(), DB_FETCHMODE_ASSOC));

$mock->addDataQuery("SELECT packages.id AS id, packages.name AS name, packages.summary AS summary, releases.version AS version, releases.releasedate AS releasedate, releases.releasenotes AS releasenotes, releases.doneby AS doneby, releases.state AS state FROM packages, releases WHERE packages.id = releases.package AND packages.approved = 1 AND packages.package_type = 'pear' ORDER BY releases.releasedate DESC LIMIT 0, 5",
array (
  0 => 
  array (
    'id' => '14',
    'name' => 'PEAR',
    'summary' => 'PEAR All Your Base All Belong To Me System',
    'version' => '1.5.1',
    'releasedate' => '2007-03-20 13:25:25',
    'releasenotes' => '* fix Bug #10286: No output on failed tests [cellog]
* fix Bug #10358: placeholders not replaced in PackageFile validator [cellog]
* fix Bug #10367: E_WARNING on list-all for empty channel [timj]
* fix Bug #10400: Fatal Error when creating a bundled package with PFM2 [berdir]
* fix Bug #10401: Funny \'pear info\' output [cellog]',
    'doneby' => 'cellog',
    'state' => 'stable',
  ),
  1 => 
  array (
    'id' => '14',
    'name' => 'PEAR',
    'summary' => 'PEAR All Your Base All Belong To Me System',
    'version' => '1.5.0RC2',
    'releasedate' => '2007-02-02 17:18:12',
    'releasenotes' => '* fully fix Bug #9355: Bug in GLIBC detection
* fix Bug #9551: listing packages in pear.geeklog.net fails',
    'doneby' => 'cellog',
    'state' => 'beta',
  ),
  2 => 
  array (
    'id' => '14',
    'name' => 'PEAR',
    'summary' => 'PEAR All Your Base All Belong To Me System',
    'version' => '1.5.0RC1',
    'releasedate' => '2007-02-02 17:13:30',
    'releasenotes' => 'Minor feature addition, freeze API and prepare for 1.5.0 stable
* fix Bug #8553: PEAR_Installer_Role_Common::processInstallation() does not honor layer
* fix Bug #8794: upgrade-all including Auth fails with fatal error
* fix Bug #9101: PEAR_Exception::getCauseMessage() failure with a
                 nested Exception (Adam Harvey)
* fix Bug #9135: pear bundle fails if php_dir is not writeable
* fix Bug #9182: can\'t add versioning to conflicting deps in PEAR_PF_v2_rw
* fix Bug #9206: PEAR package info is not retrieved correctly for package
                 with single release
* fix Bug #9208: Typo error on abstract PEAR_Task_Common::validXml() method
* fix Bug #9280: cannot install with --packagingroot package from non-php.net channel
* fix Bug #9307: Validator generates warning on empty <contents> tag
* fix Bug #9355: Bug in GLIBC detection (OS/Guess.php), patch proposal
* fix shell-test command with invalid usage (noticed by Christian Weiske)
* fix notice when running "pear sign" (noticed by Christian Weiske)
* implement Request #8827: add a yesno type handling for post-install scripts',
    'doneby' => 'cellog',
    'state' => 'beta',
  ),
  3 => 
  array (
    'id' => '582',
    'name' => 'Math_Derivative',
    'summary' => 'Calculate the derivative of a mathematical expression',
    'version' => '0.1.0',
    'releasedate' => '2006-11-18 10:00:14',
    'releasenotes' => 'Beta release after 8 months of tests.
Included predefined functions like sqrt(), atan(), acos(), asin().',
    'doneby' => 'colder',
    'state' => 'beta',
  ),
  4 => 
  array (
    'id' => '261',
    'name' => 'Games_Chess',
    'summary' => 'Construct and validate a logical chess game, does not display',
    'version' => '1.0.0RC1',
    'releasedate' => '2006-11-17 19:13:08',
    'releasenotes' => 'fix bugs in Crazyhouse discovered in unit testing',
    'doneby' => 'cellog',
    'state' => 'beta',
  ),
),
    array('id', 'name', 'summary', 'version', 'releasedate', 'releasenotes', 'doneby',
          'state')
);
$mock->addDataQuery('
            SELECT
                packages.name, releases.version, downloads,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as d
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = \'pear\' AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "' . date('Y-m-01') . ' 00:00:00"
                ORDER BY d DESC LIMIT 0, 5',array (
  0 => 
  array (
    'name' => 'XML_RPC',
    'version' => '1.4.4',
    'downloads' => '72342',
    'd' => '120.37',
  ),
  1 => 
  array (
    'name' => 'XML_RPC',
    'version' => '1.4.5',
    'downloads' => '46438',
    'd' => '91.05',
  ),
  2 => 
  array (
    'name' => 'Mail',
    'version' => '1.1.9',
    'downloads' => '35739',
    'd' => '56.46',
  ),
  3 => 
  array (
    'name' => 'XML_Parser',
    'version' => '1.2.7',
    'downloads' => '32885',
    'd' => '52.87',
  ),
  4 => 
  array (
    'name' => 'Net_Socket',
    'version' => '1.0.6',
    'downloads' => '30925',
    'd' => '37.17',
  ),
), array('name', 'version', 'downloads', 'd'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.dev','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
