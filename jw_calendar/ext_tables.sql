#
# Table structure for table 'tx_jwcalendar_categories'
#
CREATE TABLE tx_jwcalendar_categories (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	color varchar(16) DEFAULT '' NOT NULL,
   	fe_group int(11) DEFAULT '0' NOT NULL,
   	fe_entry tinyint(4) unsigned DEFAULT '0' NOT NULL,
	comment varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_jwcalendar_exc_groups (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	color tinytext NOT NULL,
	bgcolor tinyint(4) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_jwcalendar_exc_events (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	begin int(11) DEFAULT '0' NOT NULL,
	end int(11) DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	priority int(11) DEFAULT '0' NOT NULL,
	exc_group int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_jwcalendar_events'
#
CREATE TABLE tx_jwcalendar_events (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	category int(11) unsigned DEFAULT '0' NOT NULL,
	begin int(11) DEFAULT '0' NOT NULL,
	end int(11) DEFAULT '0' NOT NULL,
	location varchar(128) DEFAULT '' NOT NULL,
	location_id int(11) unsigned DEFAULT '0' NOT NULL,
	organiser varchar(128) DEFAULT '' NOT NULL,
	organizer_id int(11) unsigned DEFAULT '0' NOT NULL,
	organizer_feuser int(11) unsigned DEFAULT '0' NOT NULL,
	email varchar(128) DEFAULT '' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	teaser tinytext NOT NULL,
	description text NOT NULL,
	link varchar(128) DEFAULT '' NOT NULL,
	image varchar(128) DEFAULT '' NOT NULL,
	directlink varchar(128) DEFAULT '' NOT NULL,
    fe_user int(11) DEFAULT '0' NOT NULL,
    event_type tinyint(5) DEFAULT '0' NOT NULL,
	exc_event varchar(128) DEFAULT '0' NOT NULL,
	exc_group varchar(128) DEFAULT '0' NOT NULL,

    rec_end_date int(11) unsigned DEFAULT '0' NOT NULL, 		
	rec_time_x tinyint(5) DEFAULT '0' NOT NULL,
	
	rec_daily_type int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_days int(11) unsigned DEFAULT '0' NOT NULL,

	
	rec_weekly_type int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_weeks int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_week_monday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_tuesday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_wednesday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_thursday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_friday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_saturday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	repeat_week_sunday tinyint(4) unsigned DEFAULT '0' NOT NULL,

	rec_monthly_type int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_months int(11) unsigned DEFAULT '0' NOT NULL,

	rec_yearly_type int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_years int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_year_month int(11) unsigned DEFAULT '0' NOT NULL,
	repeat_year_day int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_jwcalendar_organizer (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	description text NOT NULL,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	link varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);


CREATE TABLE tx_jwcalendar_location (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	location varchar(128) DEFAULT '' NOT NULL,
	description text NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	link varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);
