#
# Table structure for table 'tx_t3consultancies_cat'
#
CREATE TABLE tx_t3consultancies_cat (
	tx_jobbank_status tinyint(3) unsigned DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_jobbank_list'
#
CREATE TABLE tx_jobbank_list (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	occupation tinytext NOT NULL,
	location tinytext NOT NULL,
	city tinytext NOT NULL,
	status int(11) DEFAULT '0' NOT NULL,
	industry tinytext NOT NULL,
	clevel tinytext NOT NULL,
	sponsor_id int(11) DEFAULT '0' NOT NULL,
	joboverview text NOT NULL,
	company_description text NOT NULL,
	additional_requirement text NOT NULL,
	major_responsibilities text NOT NULL,
	qualification tinytext NOT NULL,
	position_filled tinyint(4) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


# Table structure for table 'tx_jobbank_status'
#
CREATE TABLE tx_jobbank_status (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status_name tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_jobbank_career'
#
CREATE TABLE tx_jobbank_career (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	career_name tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_jobbank_qualification'
#
CREATE TABLE tx_jobbank_qualification (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	qualification tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
