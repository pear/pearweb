CREATE TABLE `users` (
  `handle` varchar(20) NOT NULL default ' ',
  `password` varchar(64) default NULL,
  `ppp_only` int(11) NOT NULL default '0',
  `name` varchar(100) default NULL,
  `email` varchar(100) default NULL,
  `homepage` varchar(255) default NULL,
  `created` datetime default NULL,
  `createdby` varchar(20) default NULL,
  `lastlogin` datetime default NULL,
  `showemail` tinyint(1) default NULL,
  `registered` tinyint(1) default NULL,
  `admin` tinyint(1) default NULL,
  `userinfo` longtext,
  `pgpkeyid` varchar(20) default NULL,
  `pgpkey` longtext,
  `wishlist` varchar(255) default NULL,
  `active` int(11) NOT NULL default '1',
  `longitude` varchar(25) default NULL,
  `latitude` varchar(25) default NULL,
  `from_site` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`handle`),
  UNIQUE KEY `email_u` (`email`),
  KEY `handle` (`handle`,`registered`),
  KEY `pgpkeyid` (`pgpkeyid`),
  KEY `email` (`email`(25))
);

-- Password is "admin"
INSERT INTO users(handle, password, ppp_only, name, email, homepage, created, createdby, lastlogin, showemail, registered, admin, userinfo, active)
          VALUES ('admin','21232f297a57a5a743894a0e4a801fc3',0,'Administrator','root@example.com','http://www.example.com/',NOW(),NULL,NULL,1,1,0, 'This is the super user.', 1);

-- Password is "helloworld"
INSERT INTO users(handle, password, ppp_only, name, email, homepage, created, createdby, lastlogin, showemail, registered, admin, userinfo, active)
          VALUES ('johndoe','fc5e038d38a57032085441e7fe7010b0',0,'John Doe','john@example.com','http://www.example.com/',NOW(),'root',NULL,1,1,0, 'Hi, I\'m John Doe.',1);

CREATE TABLE `lostpassword` (
  `handle` varchar(20) NOT NULL default ' ',
  `newpassword` varchar(64) NOT NULL default ' ',
  `salt` varchar(32) NOT NULL default ' ',
  `requested` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY  (`handle`)
);
