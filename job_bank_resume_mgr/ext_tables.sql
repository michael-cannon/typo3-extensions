#
# Table structure for table 'tx_jobbankresumemgr_info'
#
CREATE TABLE tx_jobbankresumemgr_info (
  uid int(11) NOT NULL auto_increment,
  pid int(11) NOT NULL default '0',
  tstamp int(11) unsigned NOT NULL default '0',
  crdate int(11) unsigned NOT NULL default '0',
  cruser_id int(11) unsigned NOT NULL default '0',
  deleted tinyint(4) unsigned NOT NULL default '0',
  hidden tinyint(4) unsigned NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  job_id int(11) NOT NULL default '0',
  resume_file blob NOT NULL,
  resume_file_name tinytext NOT NULL,
  job_bank_comments text NOT NULL,
  PRIMARY KEY  (uid),
  KEY parent (pid)
) TYPE=MyISAM;


#
# Table structure for table 'tx_t3consultancies'
#
CREATE TABLE tx_t3consultancies (
    tx_jobbankresumemgr_resumecontactname tinytext NOT NULL,
    tx_jobbankresumemgr_resumecontactemail tinytext NOT NULL
);