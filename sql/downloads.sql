-- COLUMN          REFERENCES
--
-- file	           files(id)
-- package	       packages(id)
-- release	       releases(id)
-- author	         users(id)
-- category        categories(id)

CREATE TABLE downloads (
  id int(11) NOT NULL default '0',
  file int(11) NOT NULL default '0',
  package int(11) NOT NULL default '0',
  release int(11) NOT NULL default '0',
  author int(11) NOT NULL default '0',
  category int(11) NOT NULL default '0',
  dl_when datetime NOT NULL default '0000-00-00 00:00:00',
  dl_who varchar(20) default NULL,
  dl_host varchar(100) default NULL,
  PRIMARY KEY  (id),
  KEY release (release),
  KEY package (package)
);

CREATE TABLE downloads_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
);
