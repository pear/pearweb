CREATE TABLE tagnames (
   tagid int not null auto_increment primary key,
   tagname varchar(50) not null,
   tagdesc varchar(200) not null,
   adminkey tinyint not null default 0,
   UNIQUE KEY tagname_idx (tagname)
);

CREATE TABLE tag_package_link (
   package_id int not null,
   tagid int not null,
   PRIMARY KEY (package_id, tagid)
);