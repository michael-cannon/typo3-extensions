#
# Table structure for table 'tx_gbcertificate_courses'
#
CREATE TABLE tx_gbcertificate_courses (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	code varchar(255) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	detail_pid blob NOT NULL,
	show_certificate tinyint(3) DEFAULT '0' NOT NULL,
	course_prerequisites blob NOT NULL,
	hours tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_gbcertificate_course_users'
#
CREATE TABLE tx_gbcertificate_course_users (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	username varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	number tinytext NOT NULL,
	dates varchar(255) DEFAULT '' NOT NULL,
	code tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);