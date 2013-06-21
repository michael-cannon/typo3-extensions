#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_t3consultancies_selected_only tinyint(3) unsigned DEFAULT '0' NOT NULL,
    tx_t3consultancies_sponsor int(11) unsigned DEFAULT '0' NOT NULL
);




#
# Table structure for table 'tx_t3consultancies_services_mm'
# 
#
CREATE TABLE tx_t3consultancies_services_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_t3consultancies'
#
CREATE TABLE tx_t3consultancies (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	description text NOT NULL,
	url tinytext NOT NULL,
	contact_email tinytext NOT NULL,
	contact_email2 tinytext NOT NULL,
	contact_name tinytext NOT NULL,
	services int(11) unsigned DEFAULT '0' NOT NULL,
	selected tinyint(3) unsigned DEFAULT '0' NOT NULL,
	weight int(11) DEFAULT '0' NOT NULL,
	weight_bpm int(11) DEFAULT '0' NOT NULL,
	weight_soa int(11) DEFAULT '0' NOT NULL,
	fe_owner_user int(11) DEFAULT '0' NOT NULL,
	logo blob NOT NULL,
	cntry int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_t3consultancies_cat'
#
CREATE TABLE tx_t3consultancies_cat (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	parent int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_t3consultancies_contact_requests'
#
CREATE TABLE tx_t3consultancies_contact_requests (
       uid int(11) NOT NULL auto_increment,
       pid int(11) DEFAULT '0' NOT NULL,
       tstamp int(11) DEFAULT '0' NOT NULL,
       crdate int(11) DEFAULT '0' NOT NULL,
       cruser_id int(11) DEFAULT '0' NOT NULL,
       deleted tinyint(4) DEFAULT '0' NOT NULL,
       hidden tinyint(4) DEFAULT '0' NOT NULL,
       first_name tinytext NOT NULL,
       last_name tinytext NOT NULL,
       company tinytext NOT NULL,
       phone tinytext NOT NULL,
       email tinytext NOT NULL,
       comment text NOT NULL,
       companies blob NOT NULL,

       PRIMARY KEY (uid),
       KEY parent (pid)
);

