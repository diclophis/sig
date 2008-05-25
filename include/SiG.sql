CREATE TABLE %1$snode_data (
   id int(4) unsigned zerofill NOT NULL auto_increment,
   user_id int(4),
   group_id int(4),
   timestamp int(11),
   perms int(4),
   umask int(4),
   KEY (id)
);

CREATE TABLE %1$snode_rel (
   id int(4) unsigned zerofill NOT NULL auto_increment,
   site_id int(4),
   node_id int(4),
   parent_id int(4),
   orderby int(4),
   KEY (id)
); 

CREATE TABLE %1$sproperty_types (
   id int(4) unsigned zerofill NOT NULL auto_increment,
   name text,
   KEY (id)
);

CREATE TABLE %1$sproperty_data (
   id int(4) unsigned zerofill NOT NULL auto_increment,
   type_id int(4),
   node_id int(4),
   value BLOB,
   KEY (id)
);
