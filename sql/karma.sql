CREATE TABLE karma (
  id int(10) unsigned NOT NULL default '0',
  user varchar(20) NOT NULL default '',
  level varchar(20) NOT NULL default '',
  granted_by varchar(20) NOT NULL default '',
  granted_at datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY user (user),
  KEY level (level)
) TYPE=MyISAM;

