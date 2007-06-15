<?php
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'cellog' AND registered = '1'", array (
  0 => 
  array (
    'handle' => 'cellog',
    'password' => md5('hi'),
    'name' => 'Greg Beaver',
    'email' => 'greg@chiaraquartet.net',
    'homepage' => 'http://greg.chiaraquartet.net',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '1',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => 'http://www.chiaraquartet.net',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.user','pear.pepr','pear.dev','pear.admin','pear.group','pear.voter','pear.bug')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.dev','pear.admin','pear.group')", array(
    array(
        'id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
        'granted_at' => '2007-05-28 17:16:00'
    )
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.qa','pear.admin','pear.group')", array(
), array('id', 'user', 'level', 'granted_by', 'granted_at'));

$mock->addDataQuery("SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = 'cellog' AND
                 p.id = m.package AND
                 m.role = 'lead'",
                 array(array('handle' => 'cellog')
                 ),
                 array('handle'
                 ));

$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.name = 'Archive_Tar'", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT id FROM packages p WHERE p.package_type = 'pear' AND p.approved = 1 AND  p.id = 1", array(array('id' => 1)), array('id'));
$mock->addDataQuery("SELECT role FROM maintains WHERE handle = 'cellog' AND package = 1 AND role = 'lead'", array(), array('role'));