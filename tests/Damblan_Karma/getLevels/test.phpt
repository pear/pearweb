--TEST--
Damblan_Karma->getLevels()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT level, COUNT(level) AS sum FROM karma GROUP BY level",
array (
  0 => 
  array (
    'level' => 'pear.admin',
    'sum' => '19',
  ),
  1 => 
  array (
    'level' => 'pear.bug',
    'sum' => '9',
  ),
  2 => 
  array (
    'level' => 'pear.bug.admin',
    'sum' => '5',
  ),
  3 => 
  array (
    'level' => 'pear.dev',
    'sum' => '673',
  ),
  4 => 
  array (
    'level' => 'pear.doc.chm-upload',
    'sum' => '2',
  ),
  5 => 
  array (
    'level' => 'pear.election',
    'sum' => '1',
  ),
  6 => 
  array (
    'level' => 'pear.group',
    'sum' => '9',
  ),
  7 => 
  array (
    'level' => 'pear.pepr',
    'sum' => '392',
  ),
  8 => 
  array (
    'level' => 'pear.pepr.admin',
    'sum' => '1',
  ),
  9 => 
  array (
    'level' => 'pear.qa',
    'sum' => '7',
  ),
  10 => 
  array (
    'level' => 'pear.user',
    'sum' => '2',
  ),
  11 => 
  array (
    'level' => 'pear.voter',
    'sum' => '97',
  ),
  12 => 
  array (
    'level' => 'poo.foo',
    'sum' => '1',
  ),
), array('level', 'sum'));
$phpt->assertEquals(array (
  0 => 
  array (
    'level' => 'pear.admin',
    'sum' => '19',
  ),
  1 => 
  array (
    'level' => 'pear.bug',
    'sum' => '9',
  ),
  2 => 
  array (
    'level' => 'pear.bug.admin',
    'sum' => '5',
  ),
  3 => 
  array (
    'level' => 'pear.dev',
    'sum' => '673',
  ),
  4 => 
  array (
    'level' => 'pear.doc.chm-upload',
    'sum' => '2',
  ),
  5 => 
  array (
    'level' => 'pear.election',
    'sum' => '1',
  ),
  6 => 
  array (
    'level' => 'pear.group',
    'sum' => '9',
  ),
  7 => 
  array (
    'level' => 'pear.pepr',
    'sum' => '392',
  ),
  8 => 
  array (
    'level' => 'pear.pepr.admin',
    'sum' => '1',
  ),
  9 => 
  array (
    'level' => 'pear.qa',
    'sum' => '7',
  ),
  10 => 
  array (
    'level' => 'pear.user',
    'sum' => '2',
  ),
  11 => 
  array (
    'level' => 'pear.voter',
    'sum' => '97',
  ),
  12 => 
  array (
    'level' => 'poo.foo',
    'sum' => '1',
  ),
), $karma->getLevels(), 'levels');
?>
===DONE===
--EXPECT--
===DONE===