<?php

########################################################################
# Extension Manager/Repository config file for ext: "pmk_rssnewsexport"
# 
# Auto generated 27-01-2006 06:10
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'RSS Newsfeed Export',
	'description' => 'Exports tt_news items into RSS 0.91 and RSS 2.0
Requires tt_news < 2.x - 
Based on  Christoph Möllers RDF Newsfeed Export  (cm_rdfexport) which only exports to RSS(RDF) 1.0',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,tt_news',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'TYPO3_version' => '3.5.1-3.7.1',
	'PHP_version' => '0.0.4-0.0.4',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Michael Keukert',
	'author_email' => 'pmk@naklar.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'private' => 0,
	'download_password' => '',
	'version' => '0.1.4',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:16:{s:12:"ext_icon.gif";s:4:"09ba";s:17:"ext_localconf.php";s:4:"d305";s:14:"ext_tables.php";s:4:"7b3b";s:14:"ext_tables.sql";s:4:"a907";s:13:"locallang.php";s:4:"cc47";s:16:"locallang_db.php";s:4:"7573";s:10:"rss091.png";s:4:"fd6d";s:18:"rssnewsexport.tmpl";s:4:"c639";s:14:"doc/manual.sxw";s:4:"b592";s:19:"doc/wizard_form.dat";s:4:"9945";s:20:"doc/wizard_form.html";s:4:"52a0";s:14:"pi1/ce_wiz.gif";s:4:"09ba";s:37:"pi1/class.tx_pmkrssnewsexport_pi1.php";s:4:"ea81";s:45:"pi1/class.tx_pmkrssnewsexport_pi1_wizicon.php";s:4:"2f34";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.php";s:4:"3320";}',
);

?>