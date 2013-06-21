
# THESE create statements will NOT work if this file is piped into MySQL. 
# Rather they will be detected by the Typo3 Install Tool and through that 
# you should upgrade the tables to content these fields.

CREATE TABLE fe_users (
  static_info_country char(3) DEFAULT '' NOT NULL,
  zone varchar(45) DEFAULT '' NOT NULL,
  language char(2) DEFAULT '' NOT NULL,
  payment_method varchar(50) DEFAULT '' NOT NULL,
  first_name varchar(50) DEFAULT '' NOT NULL,
  last_name varchar(50) DEFAULT '' NOT NULL,
  name varchar(100) DEFAULT '' NOT NULL,
  country varchar(60) DEFAULT '' NOT NULL,
  zip varchar(20) DEFAULT '' NOT NULL,
  email varchar(255) DEFAULT '' NOT NULL,
  telephone varchar(25) DEFAULT '' NOT NULL,
  fax varchar(25) DEFAULT '' NOT NULL,
  date_of_birth int(11) DEFAULT '0' NOT NULL,
  module_sys_dmail_html tinyint(3) unsigned DEFAULT '0' NOT NULL,
	cc_type varchar(255) DEFAULT '' NOT NULL,
	cc_number varchar(255) DEFAULT '' NOT NULL,
	cc_expiry varchar(255) DEFAULT '' NOT NULL,
	cc_name varchar(255) DEFAULT '' NOT NULL,
	join_agree varchar(255) DEFAULT '' NOT NULL,
	referrer_uri varchar(255) DEFAULT '' NOT NULL,
	internal_note text NOT NULL,
	processed tinyint(3) unsigned DEFAULT '0' NOT NULL,
	paid tinyint(3) unsigned DEFAULT '0' NOT NULL,
	use_intend tinyint(3) unsigned DEFAULT '0' NOT NULL,
);
