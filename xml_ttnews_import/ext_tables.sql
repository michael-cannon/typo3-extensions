#$Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $

#
# Table structure for table 'tx_ccrdfnewsimport_tx_xmlttnewsimport_category_mm'
# Allows setting multiple news categories for import feed entries in tx_ccrdfnewsimport
# uid_local -corresponds to tx_ccrdfnewsimport.tx_xmlttnewsimport_category
# uid_foreign -corresponds to tt_news_cat.uid

CREATE TABLE tx_ccrdfnewsimport_tx_xmlttnewsimport_category_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_ccrdfnewsimport'
#
CREATE TABLE tx_ccrdfnewsimport (
	tx_xmlttnewsimport_targetpid blob NOT NULL,
    tx_xmlttnewsimport_category int(11) unsigned DEFAULT '0' NOT NULL
);


#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_xmlttnewsimport_xmlunid tinytext NOT NULL
);