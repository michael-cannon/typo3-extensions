#
# Table structure for table 'tx_bahagphotogallery_galleries'
#
CREATE TABLE tx_bahagphotogallery_galleries (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	path varchar(255) DEFAULT "" NOT NULL,
	name varchar(255) DEFAULT "" NOT NULL,
	comment text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_bahagphotogallery_images'
#
CREATE TABLE tx_bahagphotogallery_images (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	path varchar(255) DEFAULT "" NOT NULL,
	source varchar(255) DEFAULT "" NOT NULL,
	comment text NOT NULL,
	info_pid int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_bahagphotogallery_exif_data_items'
#
CREATE TABLE tx_bahagphotogallery_exif_data_items (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	item_name varchar(255) DEFAULT "" NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);