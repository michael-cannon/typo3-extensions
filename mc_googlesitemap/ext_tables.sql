#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_mcgooglesitemap_objective tinytext NOT NULL,
	tx_mcgooglesitemap_lastmod int(11) unsigned DEFAULT '0' NOT NULL,
	tx_mcgooglesitemap_pageuid blob NOT NULL,
	tx_mcgooglesitemap_url text NOT NULL,
	tx_mcgooglesitemap_changefreq int(11) unsigned DEFAULT '0' NOT NULL,
	tx_mcgooglesitemap_priority double DEFAULT '0' NOT NULL
);



#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_mcgooglesitemap_priority double DEFAULT '0' NOT NULL,
	tx_mcgooglesitemap_changefreq int(11) unsigned DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_mcgooglesitemap_lastmod int(11) unsigned DEFAULT '0' NOT NULL
);
