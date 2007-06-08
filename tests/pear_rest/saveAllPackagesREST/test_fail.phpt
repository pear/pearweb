--TEST--
PEAR_REST->saveAllPackagesREST() [database failure]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addFailingQuery("SELECT id, name FROM packages WHERE package_type = 'pear' AND approved = 1 ORDER BY name", "stupid person");

// ===== test ======
$rest->saveAllPackagesREST();
$phpt->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')), 'after');
$phpt->assertFileNotExists($rdir . '/c/categories.xml', 'info');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===