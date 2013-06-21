#
# Table structure for table 'tx_thmailformplus_main'
#
CREATE TABLE tx_thmailformplus_main (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	email_to tinytext NOT NULL,
	email_subject tinytext NOT NULL,
	email_sender tinytext NOT NULL,
	email_redirect blob NOT NULL,
	email_requiredfields tinytext NOT NULL,
	email_htmltemplate blob NOT NULL,
	email_replyto tinytext,
	email_subject_user tinytext,
	email_sendtouser tinytext,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_thmailformplus_log (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    submittedfields text,
    date datetime DEFAULT '0000-00-00 00:00:00',
    downloaded char(1) DEFAULT 'n' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);