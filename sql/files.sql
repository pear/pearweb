-- COLUMN          REFERENCES
--
-- package         packages(id)
-- release         releases(id)

CREATE TABLE files (
  `id` int(11) NOT NULL default '0',
  `package` int(11) NOT NULL default '0',
  `release` int(11) NOT NULL default '0',
  `packagexml` LONGTEXT NOT NULL default '',
  `platform` varchar(50) default NULL,
  `format` varchar(50) default NULL,
  `md5sum` varchar(32) default NULL,
  `basename` varchar(100) default NULL,
  `fullpath` varchar(250) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY pkg_rel_plat (`package`,`release`,`platform`)
);
