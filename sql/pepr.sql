CREATE TABLE package_proposal_changelog (
  pkg_prop_id int(11) NOT NULL default '0',
  timestamp int(14) NOT NULL default '0',
  user_handle varchar(20) NOT NULL default '',
  comment text,
  PRIMARY KEY  (pkg_prop_id,timestamp,user_handle)
);

CREATE TABLE package_proposal_comments (
  user_handle varchar(20) NOT NULL default '',
  pkg_prop_id int(11) NOT NULL default '0',
  timestamp int(14) NOT NULL default '0',
  comment text NOT NULL,
  PRIMARY KEY  (user_handle,pkg_prop_id,timestamp)
);

CREATE TABLE package_proposal_links (
  pkg_prop_id int(11) NOT NULL default '0',
  type enum('pkg_file','pkg_source','pkg_example','pkg_example_source','pkg_doc','Package Related') NOT NULL default 'pkg_file',
  url varchar(255) NOT NULL default ''
);

CREATE TABLE package_proposal_votes (
  pkg_prop_id int(11) NOT NULL default '0',
  user_handle varchar(255) NOT NULL default '',
  value tinyint(1) NOT NULL default '1',
  reviews text NOT NULL,
  is_conditional tinyint(1) NOT NULL default '0',
  comment text NOT NULL,
  timestamp timestamp(14) NOT NULL,
  PRIMARY KEY  (pkg_prop_id,user_handle)
);

CREATE TABLE package_proposals (
  id int(11) NOT NULL auto_increment,
  pkg_category varchar(80) NOT NULL default '',
  pkg_name varchar(80) NOT NULL default '',
  pkg_license varchar(100) NOT NULL default '',
  pkg_describtion text NOT NULL,
  pkg_deps text NOT NULL,
  draft_date datetime NOT NULL default '0000-00-00 00:00:00',
  proposal_date datetime NOT NULL default '0000-00-00 00:00:00',
  vote_date datetime NOT NULL default '0000-00-00 00:00:00',
  longened_date datetime NOT NULL default '0000-00-00 00:00:00',
  status enum('draft','proposal','vote','finished') NOT NULL default 'draft',
  user_handle varchar(255) NOT NULL default '',
  markup enum('bbcode','wiki') NOT NULL default 'bbcode',
  PRIMARY KEY  (id),
  UNIQUE KEY cat_name (pkg_category,pkg_name)
);
