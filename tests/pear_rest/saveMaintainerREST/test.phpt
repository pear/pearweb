--TEST--
PEAR_REST->saveMaintainerREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM users WHERE handle = 'cellog'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog'", array (
  0 =>
  array (
    'handle' => 'cellog',
    'password' => 'as if!',
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
// ===== test ======
$rest->saveMaintainerREST('cellog');
$phpt->assertNoErrors('after');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/m/') & 0777, 'folder permissions');
}
$phpt->assertFileExists($rdir . '/m/cellog/info.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/m/cellog/info.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.maintainer"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.maintainer
    http://pear.php.net/dtd/rest.maintainer.xsd">
 <h>cellog</h>
 <n>Greg Beaver</n>
 <u>http://greg.chiaraquartet.net</u>
</m>',
file_get_contents($rdir . '/m/cellog/info.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===