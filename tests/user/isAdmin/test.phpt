--TEST--
user::isQA()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.qa','pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-25 01:09:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-25 01:09:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'blah' AND level IN ('pear.qa','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'blah' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$phpunit->assertTrue(user::isQA('cellog'), 'test');
$phpunit->assertFalse(user::isQA('blah'), 'test 2');
$phpunit->assertTrue(user::isAdmin('cellog'), 'test3');
$phpunit->assertFalse(user::isAdmin('blah'), 'test 4');

$phpunit->assertEquals(array (
   0 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
   1 => 'SELECT * FROM karma WHERE user = \'blah\' AND level IN (\'pear.qa\',\'pear.admin\',\'pear.group\')',
   2 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
   3 => 'SELECT * FROM karma WHERE user = \'blah\' AND level IN (\'pear.admin\',\'pear.group\')',
), $mock->queries, 'queries');
?>
===DONE===
--EXPECT--
===DONE===