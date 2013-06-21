
#
# Table structure for table 'tx_ccrdfnewsimport'
#$Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:03:15 peimic.comprock Exp $
CREATE TABLE tx_ccrdfnewsimport (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  fe_group int(11) DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  url text DEFAULT '' NOT NULL,
  intervall int(11) unsigned DEFAULT '0' NOT NULL,
  bodytext mediumtext NOT NULL,
  errors int(11) unsigned DEFAULT '0' NOT NULL,
  lastError tinytext NOT NULL,
  banlist text DEFAULT '' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);