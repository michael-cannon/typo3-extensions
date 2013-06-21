#
# Table structure for table 'tx_memberaccess_acl'
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:03:48 peimic.comprock Exp $
#Note: setting email to utf charset because that is what the charset/collation
#is for the email field in fe_users.  js
CREATE TABLE tx_memberaccess_acl (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	email varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL,
	name varchar(100)   DEFAULT '' NOT NULL,
	company varchar(80)  DEFAULT '' NOT NULL,
	accesslevel varchar(100)  DEFAULT '' NOT NULL,
	endtimeExtension int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	UNIQUE KEY `email` (`email`),
    KEY parent (pid)
);



#
# Table structure for table 'tx_memberaccess_registrationerrors'
#Note: setting email to utf charset because that is what the charset/collation
#is for the email field in fe_users.  js
#
CREATE TABLE tx_memberaccess_registrationerrors (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	userid int(11) DEFAULT '0' NOT NULL,
	errortime int(11) DEFAULT '0' NOT NULL,
	email varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL,
	errors text DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

