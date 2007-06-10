--TEST--
user::remove()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require_once 'pear-prepend.php';
$pear_rest = new pear_rest($restdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rest');
require_once 'pear-rest.php';
$rest = new PEAR_REST($rdir = dirname(__FILE__) . '/rest');

require_once 'System.php';
System::mkdir(array('-p', $rdir . '/m/dufuz'));
touch($rdir . '/m/dufuz/info.xml');

$mock->addDeleteQuery("DELETE FROM notes WHERE uid = 'dufuz'", array(), array());

$mock->addDataQuery("SELECT handle FROM users WHERE registered = 1 ORDER BY handle", array(
    array('handle' => 'boo'),
    array('handle' => 'hoo'),
    array('handle' => 'ya'),
    array('handle' => 'big'),
    array('handle' => 'baby'),
), array('handle'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'boo' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'boo', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'hoo' AND level IN ('pear.dev','pear.admin','pear.group')", array(), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'ya' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'ya', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'big' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'big', 'level' => 'pear.admin', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
    array('id' => 1, 'user' => 'big', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'baby' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'baby', 'level' => 'pear.dev', 'granted_by' => 'o', 'granted_at' => '2007-05-22 00:00:00'),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz'", array(), array());

$mock->addInsertQuery("
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                ('dufuz', 'Helgi Thormar', 'dufuz@php.net', 'http://www.helgi.ws/', 0, '5d8052a59cae407c50bf4056bc8c9014', 0, 'a:2:{i:0;s:14:\"do nifty tests\";i:1;s:6:\"hippie\";}', 'pear')",
                array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array (
    'handle' => 'dufuz',
    'password' => '5d8052a59cae407c50bf4056bc8c9014',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => 'a:2:{i:0;s:14:\"do nifty tests\";i:1;s:6:\"hippie\";}',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
          'cols' => array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active')
          )), 1);

$mock->addDeleteQuery("DELETE FROM users WHERE handle = 'dufuz'",
    array("SELECT * FROM users WHERE handle = 'dufuz'" => array(array(), 'cols' => array())), 1);

// ============= test =============
$phpt->assertFileExists($rdir . '/m/dufuz/info.xml', 'test 1');
$data = array(
    'handle'    => 'dufuz',
    'firstname' => 'Helgi',
    'lastname'  => 'Thormar',
    'email'     => 'dufuz@php.net',
    'purpose'   => 'do nifty tests',
    'password'  => 'PEARforThewin',
    'password2' => 'PEARforThewin',
    'moreinfo'  => 'hippie',
    'homepage'  => 'http://www.helgi.ws/',
);
$id = user::add($data, false, false);
$phpt->assertTrue($id, 'id');
$res = user::remove('dufuz');
$phpt->assertTrue($res, 'test 2');
$phpt->assertFileNotExists($rdir . '/m/dufuz/info.xml', 'test 3');
$phpt->assertFileExists($rdir . '/m/allmaintainers.xml', 'test 4');

$phpt->assertEquals(array (
    0 => 'SELECT * FROM users WHERE handle = \'dufuz\'',
    1 => '
            INSERT INTO users
                (handle, name, email, homepage, showemail, password, registered, userinfo, from_site)
            VALUES
                (\'dufuz\', \'Helgi Thormar\', \'dufuz@php.net\', \'http://www.helgi.ws/\', 0, \'5d8052a59cae407c50bf4056bc8c9014\', 0, \'a:2:{i:0;s:14:"do nifty tests";i:1;s:6:"hippie";}\', \'pear\')',
    2 => 'DELETE FROM notes WHERE uid = \'dufuz\'',
    3 => 'SELECT handle FROM users WHERE registered = 1 ORDER BY handle',
    4 => 'SELECT * FROM karma WHERE user = \'boo\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
    5 => 'SELECT * FROM karma WHERE user = \'hoo\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
    6 => 'SELECT * FROM karma WHERE user = \'ya\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
    7 => 'SELECT * FROM karma WHERE user = \'big\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
    8 => 'SELECT * FROM karma WHERE user = \'baby\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
    9 => 'DELETE FROM users WHERE handle = \'dufuz\'',
), $mock->queries, 'queries');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===