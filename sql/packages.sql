-- COLUMN          REFERENCES
--
-- category        categories(id)

CREATE TABLE packages (
  id int(11) NOT NULL default '0',
  name varchar(80) NOT NULL default '',
  category int(11) default NULL,
  stablerelease varchar(20) default NULL,
  develrelease varchar(20) default NULL,
  license varchar(20) default NULL,
  summary text,
  description text,
  homepage varchar(255) default NULL,
  package_type enum('pear','pecl') NOT NULL default 'pear',
  doc_link varchar(255) default NULL,
  cvs_link varchar(255) default NULL,
  approved tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY name (name),
  KEY category (category)
);
