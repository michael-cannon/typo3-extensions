#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
	tx_extendedshop_vatcode varchar(255) DEFAULT '' NOT NULL,
	tx_extendedshop_state varchar(250) DEFAULT '' NOT NULL,
	tx_extendedshop_private int(11) unsigned DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_extendedshop_vatcode varchar(255) DEFAULT '' NOT NULL,
	tx_extendedshop_state varchar(250) DEFAULT '' NOT NULL,
	tx_extendedshop_mobile varchar(20) DEFAULT '' NOT NULL,
	tx_extendedshop_private int(11) unsigned DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'tx_extendedshop_status'
#
CREATE TABLE tx_extendedshop_status (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l18n_parent int(11) DEFAULT '0' NOT NULL,
    l18n_diffsource mediumblob NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    status varchar(20) DEFAULT "" NOT NULL,
    priority int(11) DEFAULT '0' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure for table 'tx_extendedshop_category'
#
CREATE TABLE tx_extendedshop_category (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	code varchar(245) DEFAULT '' NOT NULL,
	title tinytext NOT NULL,
	summary text NOT NULL,
	description text NOT NULL,
	image blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_extendedshop_orders'
#
CREATE TABLE tx_extendedshop_orders (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	code varchar(50) DEFAULT '' NOT NULL,
	customer blob NOT NULL,
	shippingcustomer blob NOT NULL,
	date int(11) DEFAULT '0' NOT NULL,
	shipping text NOT NULL,
	payment text NOT NULL,
	total float DEFAULT '0' NOT NULL,
	weight float DEFAULT '0' NOT NULL,
	volume float DEFAULT '0' NOT NULL,
	trackingcode varchar(50) DEFAULT '' NOT NULL,
	state varchar(15) DEFAULT '' NOT NULL,
	ip tinytext NOT NULL,
	note text NOT NULL,
	status int(11) DEFAULT '0' NOT NULL,
	deliverydate int(11) DEFAULT '0' NOT NULL,
	complete tinyint(3) DEFAULT '0' NOT NULL,
	ordernote text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_extendedshop_rows'
#
CREATE TABLE tx_extendedshop_rows (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	ordercode blob NOT NULL,
	productcode blob NOT NULL,
	quantity int(11) DEFAULT '0' NOT NULL,
	price float DEFAULT '0' NOT NULL,
	weight float DEFAULT '0' NOT NULL,
	volume float DEFAULT '0' NOT NULL,
	state varchar(255) DEFAULT '' NOT NULL,
	itemcode varchar(100) DEFAULT '' NOT NULL,
	options tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_extendedshop_products'
#
CREATE TABLE tx_extendedshop_products (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	l18n_parent int(11) NOT NULL default '0',
	sys_language_uid int(11) NOT NULL default '0',
	l18n_diffsource mediumblob NOT NULL,
	code varchar(100) DEFAULT '' NOT NULL,
	title varchar(100) DEFAULT '' NOT NULL,
	pagetitle varchar(100) DEFAULT '' NOT NULL,
	summary text NOT NULL,
	description text NOT NULL,
	image blob NOT NULL,
	price float DEFAULT '0' NOT NULL,
	pricenotax float DEFAULT '0' NOT NULL,
	instock int(11) DEFAULT '0' NOT NULL,
	category int(11) unsigned DEFAULT '0' NOT NULL,
	www varchar(150) DEFAULT '' NOT NULL,
	ordered tinytext NOT NULL,
	weight float DEFAULT '0' NOT NULL,
	volume float DEFAULT '0' NOT NULL,
	offertprice float DEFAULT '0' NOT NULL,
	offertpricenotax float DEFAULT '0' NOT NULL,
	discount char(2) DEFAULT '' NOT NULL,
	sizes varchar(245) DEFAULT '' NOT NULL,
	colors varchar(245) DEFAULT '' NOT NULL,
	correlatedproducts blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
