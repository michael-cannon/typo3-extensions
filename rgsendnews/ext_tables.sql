#
# Table structure for table 'tx_rgsendnews_stat'
#
CREATE TABLE tx_rgsendnews_stat (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	sender tinytext NOT NULL,
	receiver tinytext NOT NULL,
	newsid blob NOT NULL,
	comment text NOT NULL,
	ip tinytext NOT NULL,
	recmail tinytext NOT NULL,
	sendmail tinytext NOT NULL,
	htmlmail tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);