CREATE TABLE cvs_groups (
  groupname varchar(20) NOT NULL default '',
  description varchar(250) NOT NULL default '',
  UNIQUE KEY groupname (groupname)
);
