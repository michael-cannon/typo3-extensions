#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_xdspagetag_pagetag varchar(100) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_pagetag_stat'
#
CREATE TABLE tx_pagetag_stat (
  uid int(11) DEFAULT '0' NOT NULL auto_increment,
  searchstring varchar(100) NOT NULL DEFAULT '',
  tstamp int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid)
);

