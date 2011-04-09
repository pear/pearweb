CREATE TABLE channels (
  name varchar(255) NOT NULL default '', 
  is_active tinyint(1),
  project_label varchar(255) NOT NULL default '',
  project_link varchar(255) NOT NULL default '',
  contact_name varchar(255) NOT NULL default '',
  contact_email varchar(255) NOT NULL default '',
  PRIMARY KEY  (name),
);

INSERT INTO channels VALUES ('pear.php.net', 1, 'PEAR', 'http://pear.php.net/', 'PEAR Webmaster', 'pear-webmaster@lists.php.net');

