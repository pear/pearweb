CREATE TABLE categories (
  id int(11) NOT NULL default '0',
  parent int(11) default NULL,
  name varchar(80) NOT NULL default '',
  summary text,
  description text,
  npackages int(11) default '0',
  pkg_left int(11) default NULL,
  pkg_right int(11) default NULL,
  cat_left int(11) default NULL,
  cat_right int(11) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
);

INSERT INTO categories VALUES (1,NULL,'Authentication',NULL,'none',9,NULL,NULL,135,136);
INSERT INTO categories VALUES (2,NULL,'Benchmarking',NULL,'none',1,NULL,NULL,3,4);
INSERT INTO categories VALUES (3,NULL,'Caching',NULL,'none',3,NULL,NULL,5,6);
INSERT INTO categories VALUES (4,NULL,'Configuration',NULL,'none',1,NULL,NULL,7,8);
INSERT INTO categories VALUES (5,NULL,'Console',NULL,'none',12,NULL,NULL,9,10);
INSERT INTO categories VALUES (6,NULL,'Encryption',NULL,'none',8,NULL,NULL,11,12);
INSERT INTO categories VALUES (7,NULL,'Database',NULL,'none',35,NULL,NULL,13,14);
INSERT INTO categories VALUES (8,NULL,'Date and Time',NULL,'none',4,NULL,NULL,15,16);
INSERT INTO categories VALUES (9,NULL,'File System',NULL,'none',10,NULL,NULL,17,18);
INSERT INTO categories VALUES (10,NULL,'HTML',NULL,'none',29,NULL,NULL,19,20);
INSERT INTO categories VALUES (11,NULL,'HTTP',NULL,'none',14,NULL,NULL,21,22);
INSERT INTO categories VALUES (12,NULL,'Images',NULL,'none',14,NULL,NULL,23,24);
INSERT INTO categories VALUES (13,NULL,'Logging',NULL,'none',2,NULL,NULL,25,26);
INSERT INTO categories VALUES (14,NULL,'Mail',NULL,'none',8,NULL,NULL,27,28);
INSERT INTO categories VALUES (15,NULL,'Math',NULL,'none',15,NULL,NULL,29,30);
INSERT INTO categories VALUES (16,NULL,'Networking',NULL,'none',55,NULL,NULL,31,32);
INSERT INTO categories VALUES (17,NULL,'Numbers',NULL,'none',2,NULL,NULL,33,34);
INSERT INTO categories VALUES (18,NULL,'Payment',NULL,'none',8,NULL,NULL,35,36);
INSERT INTO categories VALUES (19,NULL,'PEAR',NULL,'PEAR infrastructure',5,NULL,NULL,37,38);
INSERT INTO categories VALUES (20,NULL,'Scheduling',NULL,'none',0,NULL,NULL,39,40);
INSERT INTO categories VALUES (21,NULL,'Science',NULL,'none',1,NULL,NULL,41,42);
INSERT INTO categories VALUES (22,NULL,'XML',NULL,'none',30,NULL,NULL,43,44);
INSERT INTO categories VALUES (23,NULL,'Web Services',NULL,'none',10,NULL,NULL,45,46);
INSERT INTO categories VALUES (25,NULL,'PHP',NULL,'Classes related to the PHP language itself',24,NULL,NULL,47,48);
INSERT INTO categories VALUES (28,NULL,'Internationalization',NULL,'I18N related packages',7,NULL,NULL,51,52);
INSERT INTO categories VALUES (27,NULL,'Structures',NULL,'Structures and advanced data types',6,NULL,NULL,49,50);
INSERT INTO categories VALUES (29,NULL,'Tools and Utilities',NULL,'Tools and Utilities for PHP or written in PHP',12,NULL,NULL,53,60);
INSERT INTO categories VALUES (34,NULL,'Gtk Components',NULL,'Graphical components for php-gtk',5,NULL,NULL,65,66);
INSERT INTO categories VALUES (31,NULL,'Processing',NULL,'Foo',1,NULL,NULL,61,62);
INSERT INTO categories VALUES (33,NULL,'File Formats',NULL,'This category holds all sorts of packages reading/writing files of a certain format.',23,NULL,NULL,63,64);
INSERT INTO categories VALUES (35,NULL,'Streams',NULL,'PHP streams implementations and utilities',7,NULL,NULL,67,68);
INSERT INTO categories VALUES (36,NULL,'Text',NULL,'Creating and manipulating text.',16,NULL,NULL,69,70);
INSERT INTO categories VALUES (37,NULL,'System',NULL,'System Utilities',4,NULL,NULL,99,100);
INSERT INTO categories VALUES (40,29,'Version Control',NULL,'Packages that allow access to version control systems such as CVS or Subversion',0,NULL,NULL,56,57);
INSERT INTO categories VALUES (42,NULL,'Semantic Web',NULL,'The Semantic Web provides a common framework that allows data to be shared and reused across application, enterprise, and community boundaries',3,NULL,NULL,111,112);
INSERT INTO categories VALUES (43,29,'Testing',NULL,'Packages for creating test suites',0,NULL,NULL,58,59);
