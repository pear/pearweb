-- username REFERENCES users(handle)

CREATE TABLE cvs_acl (
  username varchar(20) default NULL,
  usertype enum('user','group') NOT NULL default 'user',
  path varchar(250) NOT NULL default '',
  access tinyint(1) default NULL,
  UNIQUE KEY username (username,path)
);
