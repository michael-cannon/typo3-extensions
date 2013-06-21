#
# Table structure for table 'tx_t3consultancies'
#
CREATE TABLE tx_t3consultancies (
	tx_affiliatetracker_affiliate_codes blob NOT NULL
);



#
# Table structure for table 'tx_affiliatetracker_codes'
#
CREATE TABLE tx_affiliatetracker_codes (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	affiliate_code varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_affiliatetracker_visitor_tracking'
#
CREATE TABLE tx_affiliatetracker_visitor_tracking (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	affiliate_id int(11) NOT NULL,
	landing_url varchar(255) DEFAULT '' NOT NULL,
	referer_url varchar(255) DEFAULT '' NOT NULL,
	feuser_id int(11) NOT NULL,
	full_affiliate_code varchar(255) DEFAULT '' NOT NULL,
	affiliate_source_code varchar(255) DEFAULT '' NOT NULL,
	affiliate_index_code varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
