#
# Table structure for table 'tt_products_tx_ttproductsredemption_redemptioncodes_mm'
# 
#
CREATE TABLE tt_products_tx_ttproductsredemption_redemptioncodes_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tt_products'
#
CREATE TABLE tt_products (
	tx_ttproductsredemption_activateredemptioncodes tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_ttproductsredemption_redemptioncodes int(11) unsigned DEFAULT '0' NOT NULL,
	tx_ttproductsredemption_usergroups blob NOT NULL,
	tx_ttproductsredemption_ordering int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_ttproductsredemption_codes'
#
CREATE TABLE tx_ttproductsredemption_codes (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	code tinytext NOT NULL,
	percentage int(11) DEFAULT '0' NOT NULL,
	amount int(11) DEFAULT '0' NOT NULL,
	maximumquantity int(11) DEFAULT '0' NOT NULL,
	quantityused int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table
# 'fe_users_tx_ttproductsredemption_redemptioncodeusage_mm'
# 
#
CREATE TABLE fe_users_tx_ttproductsredemption_redemptioncodeusage_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_ttproductsredemption_redemptioncodeusage int(11) unsigned DEFAULT '0' NOT NULL
);
