# $Id$
CREATE TABLE channels (
       name                   VARCHAR(30) NOT NULL,
       server                 VARCHAR(255) NOT NULL,
       PRIMARY KEY(name),
       INDEX(server)
);

INSERT INTO channels (name, server)
               VALUES('pear', 'pear.php.net');