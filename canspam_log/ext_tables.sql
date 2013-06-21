#
# Table structure for table 'tx_canspamlog_main'
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:03:07 peimic.comprock Exp $
CREATE TABLE tx_canspamlog_main (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fe_userid int(11) DEFAULT '0' NOT NULL,
	pageid int(11) DEFAULT '0' NOT NULL,
	action_time int(11) DEFAULT '0' NOT NULL,
	site varchar(255) DEFAULT '' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	action varchar(255) DEFAULT '' NOT NULL,
	subaction varchar(255) DEFAULT '' NOT NULL,
	client_ip varchar(15) DEFAULT '' NOT NULL,
	tx_bpmprofile_newsletter1 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter2 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter3 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter4 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter5 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter6 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter7 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter8 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter9 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_bpmprofile_newsletter10 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	currentArr text,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY `fe_userid` (`fe_userid`)
);