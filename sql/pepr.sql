-- MySQL dump 8.22
--
-- Host: localhost    Database: pear
---------------------------------------------------------
-- Server version	3.23.51-log

--
-- Table structure for table 'packages_proposals'
--

CREATE TABLE packages_proposals (
  id int(10) unsigned NOT NULL default '0',
  name varchar(80) NOT NULL default '',
  category int(11) NOT NULL default '0',
  user varchar(255) NOT NULL default '',
  summary text NOT NULL,
  description text NOT NULL,
  homepage varchar(255) NOT NULL default '',
  source_links text NOT NULL,
  date_start datetime NOT NULL default '0000-00-00 00:00:00',
  date_end datetime NOT NULL default '0000-00-00 00:00:00',
  status set('open','finished','rejected') NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE packages_proposals_votes (
  id int(10) unsigned NOT NULL default '0',
  proposal int(10) unsigned NOT NULL default '0',
  handle varchar(50) NOT NULL default '',
  vote_when datetime NOT NULL default '0000-00-00 00:00:00',
  vote_value tinyint(4) NOT NULL default '0',
  comment text NOT NULL,
  PRIMARY KEY  (id),
  KEY proposal (proposal)
) TYPE=MyISAM;