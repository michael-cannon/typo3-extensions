#
# Table structure for table 'tx_powermailcond_conditions'
#
CREATE TABLE tx_powermailcond_conditions (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	line int(11) DEFAULT '0' NOT NULL,
	rules int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_powermailcond_rules'
#
CREATE TABLE tx_powermailcond_rules (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	conjunction int(11) DEFAULT '0' NOT NULL,
	ops int(11) DEFAULT '0' NOT NULL,
	conditions int(11) DEFAULT '0' NOT NULL,
	condstring text NOT NULL,
	actions int(11) DEFAULT '0' NOT NULL,
	fieldname int(11) DEFAULT '0' NOT NULL,
	fieldsetname int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_powermail_fieldsets'
#
CREATE TABLE tx_powermail_fieldsets (
	tx_powermailcond_conditions int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_powermail_fields'
#
CREATE TABLE tx_powermail_fields (
	tx_powermailcond_conditions int(11) DEFAULT '0' NOT NULL,
	tx_powermailcond_manualcode tinytext NOT NULL
);
