-- COLUMN          REFERENCES
--
-- handle          users(handle)
-- package	       packages(id)

CREATE TABLE package_acl (
  handle varchar(20) NOT NULL default '',
  package varchar(80) NOT NULL default '',
  access int(11) default NULL,
  UNIQUE KEY handle (handle,package)
);
