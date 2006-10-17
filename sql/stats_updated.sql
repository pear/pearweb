-- COLUMN          REFERENCES
--
-- release_id      releases(id)

-- this table is used by the stats updating cron job to harvest from the downloads table
-- and will only be used until the downloads table is defunct

CREATE TABLE stats_updated (
  release_id int(11) NOT NULL default '0',
  lastupdate datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (release_id)
)