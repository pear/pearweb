--TEST--
category::listAll() [database error]
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addFailingQuery("SELECT * FROM categories ORDER BY name", 'failed and stuff', 143);

// test
$all = category::listAll();
$phpt->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')
), 'error');
?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===
