--TEST--
PEAR_REST->saveCategoryREST() [database failure]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addFailingQuery("SELECT * FROM categories WHERE name = 'Testing/Stuff'", "stupid person");

// ===== test ======
$rest->saveCategoryREST('Testing/Stuff');
$phpt->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')), 'after');
$phpt->assertFileNotExists($rdir . '/c/categories.xml', 'info');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===