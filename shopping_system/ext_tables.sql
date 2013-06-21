#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_shoppingsystem_related_product blob NOT NULL,
	tx_shoppingsystem_product_store tinytext NOT NULL,
	tx_shoppingsystem_product_brand tinytext NOT NULL,
	tx_shoppingsystem_product_merchant_url tinytext NOT NULL,
	tx_shoppingsystem_product_fetch_url tinytext NOT NULL,
	tx_shoppingsystem_product_image blob NOT NULL,
	tx_shoppingsystem_product_image_small blob NOT NULL,
	tx_shoppingsystem_product_price float(10,2) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tt_news_cat'
#
CREATE TABLE tt_news_cat (
	tx_shoppingsystem_featured_product blob NOT NULL
);

#
# Table structure for table 'tx_shoppingsystem_txtimages'
#
CREATE TABLE tx_shoppingsystem_txtimages (
	uid int(11) NOT NULL auto_increment,
	type int(11) DEFAULT '0' NOT NULL,
	name varchar(32) NOT NULL,
	img_path varchar(255) NOT NULL,
	
	PRIMARY KEY (uid),
);