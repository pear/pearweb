--TEST--
PEAR_REST->saveAllCategoriesREST() [database failure]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addFailingQuery("SELECT * FROM categories ORDER BY name", "stupid person");

// ===== test ======
$rest->saveAllCategoriesREST();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')), 'after');
$phpunit->assertFileNotExists($rdir . '/c/categories.xml', 'info');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===