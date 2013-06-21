#
# Table structure for table 'tx_newssearch_result'
#
CREATE TABLE tx_newssearch_result (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	search_text tinytext NOT NULL,
	category tinytext NOT NULL,
	style tinytext NOT NULL,
	user_id int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_newssearch_log'
#
CREATE TABLE tx_newssearch_log (
        uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
        pid int(11) unsigned DEFAULT '0' NOT NULL,
        tstamp int(11) unsigned DEFAULT '0' NOT NULL,
        crdate int(11) unsigned DEFAULT '0' NOT NULL,
        cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
        deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
        user blob NOT NULL,
        search_string tinytext NOT NULL,

        PRIMARY KEY (uid),
        KEY parent (pid)
);

#
# Table structure for table 'tt_news_cat'
#
CREATE TABLE tt_news_cat (
    tx_newssearch_bpm_landing_page blob NOT NULL,
    tx_newssearch_soa_landing_page blob NOT NULL
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_newssearch_backlink_to_page blob NOT NULL
);
