#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_nrdfimport_mode varchar(9) DEFAULT '' NOT NULL,
	tx_nrdfimport_feed int(11) unsigned DEFAULT '' NOT NULL,
	tx_nrdfimport_count int(11) unsigned DEFAULT '10' NOT NULL,
	tx_nrdfimport_template blob NOT NULL
);



#
# Table structure for table 'tx_nrdfimport_feeds'
#
CREATE TABLE tx_nrdfimport_feeds (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	name tinytext NOT NULL,
	url tinytext NOT NULL,
	poll_interval tinytext NOT NULL,
	cached_data longtext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
