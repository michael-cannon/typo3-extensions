#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_chcforum_aim tinytext NOT NULL,
  tx_chcforum_yahoo tinytext NOT NULL,
  tx_chcforum_msn tinytext NOT NULL
  tx_chcforum_customim tinytext NOT NULL
);


#
# Table structure for table 'tx_chcforum_category'
#
CREATE TABLE tx_chcforum_category (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	cat_title tinytext NOT NULL,
	cat_description text NOT NULL,
	auth_forumgroup_r blob NOT NULL,	
	auth_forumgroup_w blob NOT NULL,	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY allowed_group (fe_group)
);



#
# Table structure for table 'tx_chcforum_conference'
#
CREATE TABLE tx_chcforum_conference (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	cat_id int(11) unsigned DEFAULT '0' NOT NULL,
	conference_name tinytext NOT NULL,
	conference_desc text NOT NULL,
	conference_allow_user_edits tinyint(3) unsigned DEFAULT '0' NOT NULL,
	conference_public_r tinyint(3) unsigned DEFAULT '0' NOT NULL,
	conference_public_w tinyint(3) unsigned DEFAULT '0' NOT NULL,
	auth_forumgroup_r blob NOT NULL,
	auth_forumgroup_w blob NOT NULL,
	auth_feuser_mod blob NOT NULL,
	auth_forumgroup_attach blob NOT NULL,
	hide_new tinyint(3) unsigned DEFAULT '0' NOT NULL,
		
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_cat (cat_id)
);



#
# Table structure for table 'tx_chcforum_forumgroup'
#
CREATE TABLE tx_chcforum_forumgroup (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	forumgroup_title tinytext NOT NULL,
	forumgroup_desc text NOT NULL,
	forumgroup_users blob NOT NULL,
	forumgroup_groups blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_chcforum_post'
#
CREATE TABLE tx_chcforum_post (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	post_sent_flag tinyint(4) unsigned DEFAULT '0' NOT NULL,
	category_id int(11) DEFAULT '0' NOT NULL,
	conference_id int(11) DEFAULT '0' NOT NULL,
	thread_id int(11) DEFAULT '0' NOT NULL,
	post_author int(11) DEFAULT '0' NOT NULL,
	post_author_name tinytext NOT NULL,
	post_author_email tinytext NOT NULL,
	post_subject tinytext NOT NULL,
	post_author_ip tinytext NOT NULL,
	post_edit_tstamp int(11) DEFAULT '0' NOT NULL,
	post_edit_count int(11) DEFAULT '0' NOT NULL,
	post_attached blob NOT NULL,
	post_text text NOT NULL,
	cache_parsed_text text NOT NULL
	cache_tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_cat (category_id),
	KEY parent_conf (conference_id),
	KEY parent_thread (thread_id),
	KEY author (post_author),
	KEY date (crdate)
);



#
# Table structure for table 'tx_chcforum_thread'
#
CREATE TABLE tx_chcforum_thread (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	category_id int(11) DEFAULT '0' NOT NULL,
	conference_id int(11) unsigned DEFAULT '0' NOT NULL,
	thread_closed tinyint(4) unsigned DEFAULT '0' NOT NULL,
	thread_attribute tinyint(4) DEFAULT '0' NOT NULL,
	thread_subject tinytext NOT NULL,
	thread_author int(11) DEFAULT '0' NOT NULL,
	thread_datetime int(11) DEFAULT '0' NOT NULL,
	thread_views int(11) DEFAULT '0' NOT NULL,
	thread_replies int(11) DEFAULT '0' NOT NULL,
	thread_firstpostid int(11) DEFAULT '0' NOT NULL,
	thread_lastpostid int(11) DEFAULT '0' NOT NULL,
	cached_last_post_info tinytext NOT NULL,
	cached_last_post_id int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
	KEY parent_cat (category_id),
	KEY parent_conf (conference_id),
	KEY created (thread_datetime)
);

#
# Table structure for table 'tx_chcforum_mail_log'
#
CREATE TABLE tx_chcforum_mail_log (
	recipient_uid int(11) unsigned DEFAULT '0' NOT NULL,
	message_uid int(11) unsigned DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'tx_chcforum_user_conf'
#
CREATE TABLE tx_chcforum_user_conf (
	user_uid int(11) unsigned DEFAULT '0' NOT NULL,
	mailer_confs int(11) unsigned DEFAULT '0' NOT NULL,
	forum_uid int(11) unsigned DEFAULT '0' NOT NULL,
  KEY parent (mailer_confs),
  KEY user (user_uid)
);

#
# Table structure for table 'tx_chcforum_user_thread'
#
CREATE TABLE tx_chcforum_user_thread (
	user_uid int(11) unsigned DEFAULT '0' NOT NULL,
	mailer_threads int(11) unsigned DEFAULT '0' NOT NULL,
	forum_uid int(11) unsigned DEFAULT '0' NOT NULL,
  KEY parent (mailer_threads)
);


#
# Table structure for table 'tx_chcforum_f_conf'
#
CREATE TABLE tx_chcforum_f_conf (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	feusers_pid int(11) unsigned DEFAULT '0' NOT NULL,
	posts_per_page int(11) unsigned DEFAULT '0' NOT NULL,
	threads_per_page int(11) unsigned DEFAULT '0' NOT NULL,
	max_user_img int(11) unsigned DEFAULT '0' NOT NULL,
	max_attach int(11) unsigned DEFAULT '0' NOT NULL,
	allowed_file_types text NOT NULL,
	allowed_mime_types text NOT NULL,
	mailer_forum_url text NOT NULL,
	mailer_disable tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_aim tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_yahoo tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_msn tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_img tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_email tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_website tinyint(3) unsigned DEFAULT '0' NOT NULL,
	disable_profile tinyint(3) unsigned DEFAULT '0' NOT NULL,
	req_email tinyint(3) unsigned DEFAULT '0' NOT NULL,
	custom_im text NOT NULL,
	tmpl_path text NOT NULL,
	date_format text NOT NULL,
	time_format text NOT NULL,
	pruning_auto tinyint(3) unsigned DEFAULT '0' NOT NULL,
	pruning_age int(11) unsigned DEFAULT '0' NOT NULL,
			
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_chcforum_mail_queue'
#
CREATE TABLE tx_chcforum_mail_queue (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	conf_uid int(11) unsigned DEFAULT '0' NOT NULL, 
	thread_uid int(11) unsigned DEFAULT '0' NOT NULL,
	post_uid int(11) unsigned DEFAULT '0' NOT NULL,
	post_author tinytext NOT NULL,
	post_subject tinytext NOT NULL,
	post_text text NOT NULL,
	post_tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	sent_flag tinyint(4) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_chcforum_posts_read'
#
CREATE TABLE tx_chcforum_posts_read (
	uid int(11) unsigned NOT NULL auto_increment,
	feuser_uid int(11) unsigned DEFAULT '0' NOT NULL,
	post_uid text NOT NULL,	

	PRIMARY KEY (uid),
	KEY user (feuser_uid)	
);

CREATE TABLE tx_chcforum_visits (
	uid int(11) unsigned DEFAULT '0' NOT NULL,
	feuser_uid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY user_visit (feuser_uid)	
);

#
# Table structure for table 'tx_chcforum_rating'
#
CREATE TABLE tx_chcforum_ratings (
	post_uid int(11) unsigned DEFAULT '0' NOT NULL,
	rating int(1) unsigned DEFAULT '0' NOT NULL,
	rater_ip tinytext NOT NULL,
	rater_uid int(11) unsigned DEFAULT '0' NOT NULL,

	KEY parent (post_uid)
);