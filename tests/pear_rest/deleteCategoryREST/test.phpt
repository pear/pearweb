--TEST--
PEAR_REST->deleteCategoryREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require_once 'System.php';
System::mkdir(array('-p', $rdir . '/c/Plonk+It'));
touch($rdir . '/c/Plonk+It/here');
// test
$rest->deleteCategoryREST('Plonk It');
$phpunit->assertNoErrors('after');
$phpunit->assertFileNotExists($rdir . '/c/Plonk+It', 'Plonk It');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===