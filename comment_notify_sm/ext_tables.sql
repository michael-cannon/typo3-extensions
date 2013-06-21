#
# Table structure for table 'fe_users'
#
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:03:19 peimic.comprock Exp $
#
CREATE TABLE fe_users (
	tx_commentnotify_global_notify_enabled tinyint(3) DEFAULT '1' NOT NULL
);

CREATE TABLE tt_news (
	tx_commentnotify_internal_url text NOT NULL
);

CREATE TABLE tx_chcforum_thread (
	tx_commentnotify_internal_url text NOT NULL             
);


#
# Table structure for table 'tx_commentnotify_users_posts'
#
CREATE TABLE tx_commentnotify_users_posts (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_userid int(11) DEFAULT '0' NOT NULL,
	postid int(11) DEFAULT '0' NOT NULL,
	what int(11) DEFAULT '0' NOT NULL,
	notifyenabled tinyint(3) DEFAULT '1' NOT NULL,
	lastnotified int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_commentnotify_notifications'
#
CREATE TABLE tx_commentnotify_notifications (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	users_posts_id int(11) DEFAULT '0' NOT NULL,
	notificationtime int(11) DEFAULT '0' NOT NULL,
	eventtime int(11) DEFAULT '0' NOT NULL,
	notifystatus int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);