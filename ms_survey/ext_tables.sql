#
# Table structure for table 'tx_mssurvey_items_item_groups_mm'
# 
#
CREATE TABLE tx_mssurvey_items_item_groups_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_mssurvey_items'
#
CREATE TABLE tx_mssurvey_items (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	optional tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	multitem tinyint(3) unsigned DEFAULT '0' NOT NULL,
	break tinyint(3) unsigned DEFAULT '0' NOT NULL,
	type int(11) unsigned DEFAULT '0' NOT NULL,
	question text NOT NULL,
	description text NOT NULL,
	itemvalues text NOT NULL,
	itemrows text NOT NULL,
	exclude tinyint(3) unsigned DEFAULT '0' NOT NULL,
	items blob NOT NULL,
	width int(11) DEFAULT '0' NOT NULL,
	height int(11) DEFAULT '0' NOT NULL,
	item_groups int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mssurvey_results'
#
CREATE TABLE tx_mssurvey_results (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	fe_cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	domain_group_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	surveyid int(11) DEFAULT '0' NOT NULL,
	results text NOT NULL,
	remoteaddress tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
