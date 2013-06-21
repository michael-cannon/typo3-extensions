#
# Table structure for table 'fe_users'
# $Id: ext_tables.sql,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $

CREATE TABLE fe_users (
    tx_memberexpiry_expiretime int(11) DEFAULT '0' NOT NULL,
    tx_memberexpiry_expired tinyint(3) unsigned DEFAULT '0' NOT NULL
    tx_memberexpiry_emailsenttime int(11) DEFAULT '0' NOT NULL,
);
