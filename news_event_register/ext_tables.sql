# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_newseventregister_webexlink tinytext NOT NULL,
	tx_newseventregister_eventlink tinytext NOT NULL,
	tx_newseventregister_eventinformation text NOT NULL,
	tx_newseventregister_sendregistrationthankyou tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_usetxnewssponsormessage tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_useeventinformationregistered tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_sendfirstreminder tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_sendsecondreminder tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_sendthirdreminder tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_eventaccessinformation text NOT NULL,
	tx_newseventregister_eventinformationregistered text NOT NULL,
	tx_newseventregister_sendaccessinformation tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_followupmessage text NOT NULL,
	tx_newseventregister_followuplink tinytext NOT NULL,
	tx_newseventregister_sendfollowup tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_eventon tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_startdateandtime int(11) DEFAULT '0' NOT NULL,
	tx_newseventregister_enddateandtime int(11) DEFAULT '0' NOT NULL,
	tx_newseventregister_pointofcontact int(11) DEFAULT '0' NOT NULL,
	tx_newseventregister_surveyon tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_surveyrequired tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_newseventregister_surveyquestions int(11) DEFAULT '0' NOT NULL,
	tx_newseventregister_canned tinyint(3) unsigned DEFAULT '0' NOT NULL,
);



#
# Table structure for table 'tx_newseventregister_participants'
#
CREATE TABLE tx_newseventregister_participants (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	registrationdate int(11) DEFAULT '0' NOT NULL,
	news_id int(11) DEFAULT '0' NOT NULL,
	fe_user_id int(11) DEFAULT '0' NOT NULL,
	thankyousent int(11) DEFAULT '0' NOT NULL,
	firstremindersent int(11) DEFAULT '0' NOT NULL,
	secondremindersent int(11) DEFAULT '0' NOT NULL,
	thirdremindersent int(11) DEFAULT '0' NOT NULL,
	accessinformationsent int(11) DEFAULT '0' NOT NULL,
	followupsent int(11) DEFAULT '0' NOT NULL,
	unregistered int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY news_id (news_id),
	KEY fe_user_id (fe_user_id)
);

#
# Table structure for table 'tt_news_tx_mssurvey_items_mm'
# 
#
CREATE TABLE tt_news_tx_mssurvey_items_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
