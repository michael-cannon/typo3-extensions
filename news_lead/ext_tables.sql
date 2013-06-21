#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_newslead_leadon tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newslead_timeframes int(11) unsigned DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tt_news_tx_newslead_timeframes_mm'
# 
#
CREATE TABLE tt_news_tx_newslead_timeframes_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_newslead_leads'
#
CREATE TABLE tx_newslead_leads (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	date int(11) DEFAULT '0' NOT NULL,
	news_id int(11) DEFAULT '0' NOT NULL,
	fe_user_id int(11) DEFAULT '0' NOT NULL,
	filename tinytext NOT NULL,
	referrer tinytext NOT NULL,
	leadsent tinyint(3) unsigned DEFAULT '0' NOT NULL,
	leadtimeframe int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_newslead_leadperiod'
#
CREATE TABLE tx_newslead_leadperiod (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	description tinytext NOT NULL,
	startdate int(11) DEFAULT '0' NOT NULL,
	enddate int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
