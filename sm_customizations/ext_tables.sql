#
# Table structure for table 'fe_users'
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:04:03 peimic.comprock Exp $
CREATE TABLE fe_users (
	tx_smcustomizations_terms_agree tinyint(3) DEFAULT '0' NOT NULL,
	tx_smcustomizations_income_range tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_education_level tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_shopping_frequency tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_online_shopping tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_how_found tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_how_found_text tinytext DEFAULT '' NOT NULL,
	tx_smcustomizations_age int(11) unsigned DEFAULT '0' NOT NULL
);