<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_jobbank_status" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:job_bank/locallang_db.php:tx_t3consultancies_cat.tx_jobbank_status",		
		"config" => Array (
			"type" => "check",
		)
	),
);


t3lib_div::loadTCA("tx_t3consultancies_cat");
t3lib_extMgm::addTCAcolumns("tx_t3consultancies_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_t3consultancies_cat","tx_jobbank_status;;;;1-1-1");


t3lib_extMgm::allowTableOnStandardPages("tx_jobbank_list");


t3lib_extMgm::addToInsertRecords("tx_jobbank_list");

$TCA["tx_jobbank_list"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list",		
		"label" => "occupation",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jobbank_list.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, occupation, location, city, status, industry, clevel, sponsor_id, joboverview, qualification, position_filled, major_responsibilities",
	)
);

$TCA["tx_jobbank_status"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_status",		
		"label" => "status_name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jobbank_status.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, status_name",
	)
);

$TCA["tx_jobbank_career"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_career",		
		"label" => "career_name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jobbank_career.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, career_name",
	)
);

$TCA["tx_jobbank_qualification"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_qualification",		
		"label" => "qualification",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jobbank_qualification.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, qualification",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:job_bank/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Job Bank for Sponsors");
?>