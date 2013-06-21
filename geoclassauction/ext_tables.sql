#
# Table structure for table 'tx_geoclassauction_auctionsites'
#
CREATE TABLE tx_geoclassauction_auctionsites (
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
	fe_group int(11) DEFAULT '0' NOT NULL,
	sitename tinytext NOT NULL,
	siteurl tinytext NOT NULL,
	codes int(11) DEFAULT '0' NOT NULL,
	fe_user blob NOT NULL,
	description text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_geoclassauction_leads'
#
CREATE TABLE tx_geoclassauction_leads (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	auction blob NOT NULL,
	fe_user blob NOT NULL,
	attendanceday int(11) DEFAULT '0' NOT NULL,
	attendancetime int(11) DEFAULT '0' NOT NULL,
	vehicle text NOT NULL,
	pleasecall tinyint(3) DEFAULT '0' NOT NULL,
	note text NOT NULL,
	howheard int(11) DEFAULT '0' NOT NULL,
	eventcode tinytext NOT NULL,
	isdealer tinyint(3) DEFAULT '0' NOT NULL,
	contacted int(11) DEFAULT '0' NOT NULL,
	internalnotes text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_geoclassauction_homephone tinytext NOT NULL
);