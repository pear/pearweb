-- COLUMN          REFERENCES
--
-- groupname       cvs_groups(name)
-- username        users(handle)
-- granted_by	     users(handle)    

CREATE TABLE cvs_group_membership (
  groupname varchar(20) NOT NULL default '',
  username varchar(20) NOT NULL default '',
  granted_when datetime default NULL,
  granted_by varchar(20) NOT NULL default '',
  UNIQUE KEY groupname (groupname,username)
);
