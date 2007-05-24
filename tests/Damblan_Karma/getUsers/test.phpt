--TEST--
Damblan_Karma->getUsers()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE level = 'pear.admin'",
array (
  0 => 
  array (
    'id' => '863',
    'user' => 'cox',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  1 => 
  array (
    'id' => '865',
    'user' => 'jmcastagnetto',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  2 => 
  array (
    'id' => '867',
    'user' => 'jon',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  3 => 
  array (
    'id' => '870',
    'user' => 'mj',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  4 => 
  array (
    'id' => '875',
    'user' => 'ssb',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  5 => 
  array (
    'id' => '879',
    'user' => 'andi',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  6 => 
  array (
    'id' => '881',
    'user' => 'rasmus',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  7 => 
  array (
    'id' => '883',
    'user' => 'zeev',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  8 => 
  array (
    'id' => '886',
    'user' => 'thies',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  9 => 
  array (
    'id' => '888',
    'user' => 'shane',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  10 => 
  array (
    'id' => '981',
    'user' => 'sterling',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  11 => 
  array (
    'id' => '1020',
    'user' => 'derick',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  12 => 
  array (
    'id' => '1054',
    'user' => 'wez',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  13 => 
  array (
    'id' => '1101',
    'user' => 'richard',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  14 => 
  array (
    'id' => '1133',
    'user' => 'alan_k',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  15 => 
  array (
    'id' => '1274',
    'user' => 'imajes',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  16 => 
  array (
    'id' => '1278',
    'user' => 'pajoye',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  17 => 
  array (
    'id' => '1932',
    'user' => 'arnaud',
    'level' => 'pear.admin',
    'granted_by' => 'pajoye',
    'granted_at' => '2006-09-27 17:11:17',
  ),
  18 => 
  array (
    'id' => '2029',
    'user' => 'cellog',
    'level' => 'pear.admin',
    'granted_by' => 'cellog',
    'granted_at' => '2007-01-07 19:47:08',
  ),
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'id' => '863',
    'user' => 'cox',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  1 => 
  array (
    'id' => '865',
    'user' => 'jmcastagnetto',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  2 => 
  array (
    'id' => '867',
    'user' => 'jon',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  3 => 
  array (
    'id' => '870',
    'user' => 'mj',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  4 => 
  array (
    'id' => '875',
    'user' => 'ssb',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  5 => 
  array (
    'id' => '879',
    'user' => 'andi',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  6 => 
  array (
    'id' => '881',
    'user' => 'rasmus',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  7 => 
  array (
    'id' => '883',
    'user' => 'zeev',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  8 => 
  array (
    'id' => '886',
    'user' => 'thies',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  9 => 
  array (
    'id' => '888',
    'user' => 'shane',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  10 => 
  array (
    'id' => '981',
    'user' => 'sterling',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  11 => 
  array (
    'id' => '1020',
    'user' => 'derick',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  12 => 
  array (
    'id' => '1054',
    'user' => 'wez',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:38',
  ),
  13 => 
  array (
    'id' => '1101',
    'user' => 'richard',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  14 => 
  array (
    'id' => '1133',
    'user' => 'alan_k',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  15 => 
  array (
    'id' => '1274',
    'user' => 'imajes',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  16 => 
  array (
    'id' => '1278',
    'user' => 'pajoye',
    'level' => 'pear.admin',
    'granted_by' => 'mj',
    'granted_at' => '2003-09-26 15:27:39',
  ),
  17 => 
  array (
    'id' => '1932',
    'user' => 'arnaud',
    'level' => 'pear.admin',
    'granted_by' => 'pajoye',
    'granted_at' => '2006-09-27 17:11:17',
  ),
  18 => 
  array (
    'id' => '2029',
    'user' => 'cellog',
    'level' => 'pear.admin',
    'granted_by' => 'cellog',
    'granted_at' => '2007-01-07 19:47:08',
  ),
), $karma->getUsers('pear.admin'), 'users');
?>
===DONE===
--EXPECT--
===DONE===