#
# Table structure for table 'tx_danewslettersubscription_cat'
#
CREATE TABLE tx_danewslettersubscription_cat (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title varchar(256) DEFAULT '' NOT NULL,
	descr text NOT NULL,
	editor int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_danewslettersubscription_newsletter (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title varchar(256) DEFAULT '' NOT NULL,
	link_file text NOT NULL,
	html_body text NOT NULL,
	category int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_danewslettersubscription_furels (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	fe_user int(11) DEFAULT '0' NOT NULL,
	newsletter_cat int(11) unsigned DEFAULT '0' NOT NULL,
	email varchar(60) DEFAULT '' NOT NULL,
	datacontent blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY fe_user (fe_user),
	KEY newsletter_cat (newsletter_cat),
);

CREATE TABLE tx_danewslettersubscription_presets (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(256) DEFAULT '' NOT NULL,
	presetcontent blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY pid (pid)
);

CREATE TABLE tt_news (
  newsletter int(11) unsigned DEFAULT '0' NOT NULL
);


