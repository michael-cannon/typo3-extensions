#
# Table structure for table 'tx_sponsorcontentscheduler_featured_weeks'
#
CREATE TABLE tx_sponsorcontentscheduler_featured_weeks (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	description tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_sponsorcontentscheduler_featured_weeks_mm'
#
CREATE TABLE tx_sponsorcontentscheduler_featured_weeks_mm (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	uid_local blob NOT NULL,
	uid_foreign blob NOT NULL,
	selected int(11) DEFAULT '0' NOT NULL,
	site_id  int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_sponsorcontentscheduler_package'
#
CREATE TABLE tx_sponsorcontentscheduler_package (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	fe_uid int(11) NOT NULL default '0',
    sponsor_id int(11) NOT NULL default '0',
    rights tinytext NOT NULL,
 	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_sponsorcontentscheduler_highresimages'
#
CREATE TABLE tx_sponsorcontentscheduler_highresimages (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	pic blob NOT NULL
    sponsor_id int(11) NOT NULL default '0',
    fe_user_id int(11) NOT NULL default '0',
 	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_sponsorcontentscheduler_bulletin'
#
CREATE TABLE tx_sponsorcontentscheduler_bulletin (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	pic blob NOT NULL
    sponsor_id int(11) NOT NULL default '0',
    company_name tinytext NOT NULL,
    link_location tinytext NOT NULL,
    description text NOT NULL,
    default_logo varchar(255) NOT NULL,
    link_text tinytext NOT NULL,
 	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_sponsorcontentscheduler_sponsor_id blob NOT NULL,
	tx_sponsorcontentscheduler_max_featured_weeks varchar(255) NOT NULL,
	tx_sponsorcontentscheduler_author_id blob NOT NULL,
	tx_sponsorcontentscheduler_news_due_date int(11) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_due_reminder_14_days_sent TINYINT(3) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_due_reminder_7_days_sent TINYINT(3) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_due_reminder_1_days_sent TINYINT(3) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_due_reminder_0_days_sent TINYINT(3) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_package_id int(11) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_unused_leads int(11) DEFAULT '0' NOT NULL
	tt_news.tx_sponsorcontentscheduler_unsold_leads int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_sponsorcontentscheduler_sponsor_id blob NOT NULL,
	tx_sponsorcontentscheduler_package_id int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_t3consultancies'
#
CREATE TABLE tx_t3consultancies (
	tx_sponsorcontentscheduler_job_bank int(11) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_sponsor_page int(11) DEFAULT '0' NOT NULL,
	tx_sponsorcontentscheduler_owner_id int(11) DEFAULT '0' NOT NULL
);

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
    zone_location int(11) DEFAULT '0' NOT NULL,
	
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
