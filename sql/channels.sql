CREATE TABLE channels (
  name varchar(30) NOT NULL default '',
  server varchar(255) NOT NULL default '',
  PRIMARY KEY  (name),
  KEY server (server)
);

INSERT INTO channels VALUES ('pear','pear.php.net');
