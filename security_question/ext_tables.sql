#
# Table structure for table 'fe_users'
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
CREATE TABLE fe_users (
    tx_securityquestion_question int(11) unsigned DEFAULT '0' NOT NULL,
    tx_securityquestion_answer varchar(255) DEFAULT '' NOT NULL,
);



#
# Table structure for table 'tx_securityquestion_questions'
#
CREATE TABLE tx_securityquestion_questions (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	question varchar(255) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);