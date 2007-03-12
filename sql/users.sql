CREATE TABLE users (
  handle varchar(20) NOT NULL default '',
  password varchar(64) default NULL,
  name varchar(100) default NULL,
  email varchar(100) default NULL,
  homepage varchar(255) default NULL,
  created datetime default NULL,
  createdby varchar(20) default NULL,
  lastlogin datetime default NULL,
  showemail tinyint(1) default NULL,
  registered tinyint(1) default NULL,
  admin tinyint(1) default NULL,
  userinfo text default NULL,
  pgpkeyid varchar(20) default NULL,
  pgpkey text,
  wishlist varchar(255) NOT NULL default '',
  PRIMARY KEY  (handle),
  KEY handle (handle,registered),
  KEY pgpkeyid (pgpkeyid),
  KEY email (email(25)),
  UNIQUE KEY email_u (email)
);

-- Password is "admin"
INSERT INTO users VALUES ('admin','21232f297a57a5a743894a0e4a801fc3','Administrator','root@example.com','http://www.example.com/',NOW(),NULL,NULL,1,1,0, 'This is the super user.',NULL,NULL,'');

-- Password is "helloworld"
INSERT INTO users VALUES ('johndoe','fc5e038d38a57032085441e7fe7010b0','John Doe','john@example.com','http://www.example.com/',NOW(),'root',NULL,1,1,0, 'Hi, I\'m John Doe.',NULL,NULL,'');

CREATE TABLE lostpassword (
  handle varchar(20) NOT NULL,
  newpassword varchar(64) default NULL,
  salt CHAR(32) NOT NULL,
  requested datetime NOT NULL,
  PRIMARY KEY (handle)
);