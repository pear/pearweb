-- $Id$

CREATE TABLE apidoc_queue (
  filename varchar(255) NOT NULL default '',
  queued datetime NOT NULL default '0000-00-00 00:00:00',
  finished datetime NOT NULL default '0000-00-00 00:00:00',
  log longtext NOT NULL,
  UNIQUE KEY filename (filename)
) TYPE=MyISAM COMMENT='This is the queue table for the generation of API docs.';

