-- this file is not intended to be used by development servers and as such is NOT
-- synchronized with the MDB2_Schema xml file

CREATE TABLE pear_mirrors (
  mirrorserver varchar(50) NOT NULL PRIMARY KEY,
  last_sync CHAR(20) DEFAULT '0'
);