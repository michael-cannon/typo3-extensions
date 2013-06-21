#
# Table structure for table 'tt_content_tx_srfeuserregistersurvey_survey_usergroups_mm'
# 
#
CREATE TABLE tt_content_tx_srfeuserregistersurvey_survey_usergroups_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_srfeuserregistersurvey_display_survey tinyint(3) DEFAULT '0' NOT NULL,
	tx_srfeuserregistersurvey_survey_storage_pid blob NOT NULL,
	tx_srfeuserregistersurvey_survey_results_pid blob NOT NULL,
	tx_srfeuserregistersurvey_survey_usergroups int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_srfeuserregistersurvey_survey_check tinyint(3) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_srfeuserregistersurvey_results_archive'
#
CREATE TABLE tx_srfeuserregistersurvey_results_archive (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	survey_result_id blob NOT NULL,
	survey_user_id blob NOT NULL,
	survey_tstamp int(11) DEFAULT '0' NOT NULL,
	survey_crdate int(11) DEFAULT '0' NOT NULL,
	survey_result text NOT NULL,
	domain_group_id int(11) DEFAULT '0' NOT NULL,
	remoteaddress tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mssurvey_results'
#
CREATE TABLE tx_mssurvey_results (
	domain_group_id int(11) DEFAULT '0' NOT NULL
);