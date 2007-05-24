--TEST--
Damblan_Karma->remove() [granter cannot grant karma]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$karma->remove('foo', 'pear.dev');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Insufficient privileges')
), 'errors');
?>
===DONE===
--EXPECT--
===DONE===