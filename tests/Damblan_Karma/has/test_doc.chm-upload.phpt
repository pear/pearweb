--TEST--
Damblan_Karma->has() [doc.chm-upload karma levels]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.doc.chm-upload','pear.group')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.doc.chm-upload', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($karma->has('cellog', 'doc.chm-upload'), 'pear.admin');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.doc.chm-upload','pear.group')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.group', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertTrue($karma->has('cellog', 'doc.chm-upload'), 'pear.group');

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.doc.chm-upload','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpt->assertFalse($karma->has('cellog', 'doc.chm-upload'), 'none');
?>
===DONE===
--EXPECT--
===DONE===