#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_mcpodcast_access int(11) DEFAULT '0' NOT NULL,
	tx_mcpodcast_infotxt tinytext NOT NULL,
	tx_mcpodcast_infocache blob NOT NULL,
	tx_mcpodcast_mp3 blob NOT NULL
);
