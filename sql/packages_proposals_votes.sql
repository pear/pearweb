-- MySQL dump 8.22
--
-- Host: localhost    Database: pear
---------------------------------------------------------
-- Server version	3.23.51-log

--
-- Table structure for table 'packages_proposals_votes'
--

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

