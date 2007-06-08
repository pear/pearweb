--TEST--
category::isValid()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT id FROM categories WHERE name = 'blah'",
    array(), array());
$mock->addDataQuery("SELECT id FROM categories WHERE name = 'blah2'",
    array(array('id' => 1)), array('id'));


// test
$phpt->assertNotTrue(category::isValid('blah'), '1');
$phpt->assertNotFalse(category::isValid('blah2'), '1');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===