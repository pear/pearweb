-- COLUMN          REFERENCES
--
-- uid             users(handle)
-- pid             packages(id)
-- rid             releases(id)
-- cid             categories(id)
-- nby             users(handle)

CREATE TABLE notes (
  id int(11) NOT NULL default '0',
  uid varchar(20) default NULL,
  pid int(11) default NULL,
  rid int(11) default NULL,
  cid int(11) default NULL,
  nby varchar(20) default NULL,
  ntime datetime default NULL,
  note text,
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY pid (pid)
);
