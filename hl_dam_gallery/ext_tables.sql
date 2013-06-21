#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
        tx_hldamgallery_usepage tinyint(3) DEFAULT '0' NOT NULL,
        tx_hldamgallery_hidemeta tinyint(3) DEFAULT '0' NOT NULL,
        tx_hldamgallery_hidenav tinyint(3) DEFAULT '0' NOT NULL,
        tx_hldamgallery_displaypage int(11) DEFAULT '-1' NOT NULL,
        tx_hldamgallery_squarethumbs tinyint(3) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_dam (
        tx_hldamgallery_viewcount int(11) DEFAULT '0'
);