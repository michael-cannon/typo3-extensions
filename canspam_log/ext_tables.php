<?php
//$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:07 peimic.comprock Exp $
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$TCA["tx_canspamlog_main"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_canspamlog_main.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fe_userid, pageid, action_time, site, url, action, subaction, client_ip, tx_bpmprofile_newsletter1,tx_bpmprofile_newsletter2,tx_bpmprofile_newsletter3,tx_bpmprofile_newsletter4,tx_bpmprofile_newsletter5,tx_bpmprofile_newsletter6,tx_bpmprofile_newsletter7,tx_bpmprofile_newsletter8,tx_bpmprofile_newsletter9, tx_bpmprofile_newsletter10, currentArr",
	)
);
?>