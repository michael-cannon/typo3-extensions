<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_nrdfimport_mode" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_mode",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_mode.I.0", "frontpage"),
				Array("LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_mode.I.1", "details"),
				// MLC
				Array("LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_mode.I.2", "listing"),
			),
		)
	),
	"tx_nrdfimport_count" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_count",		
		"config" => Array (
			"type" => "input",
			'size'	=> '3',
			'max'	=> '3',
			'eval'	=> 'trim,nospace,num',
			'default'	=> 10
		)
	),
	"tx_nrdfimport_feed" => Array (		
		"exclude" => 1,		
		"label" =>
		"LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_feed",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "db",
			"allowed" => 'tx_nrdfimport_feeds',
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_nrdfimport_template" => Array (		
		"exclude" => 1,		
		"label" =>
		"LLL:EXT:n_rdfimport/locallang_db.php:tt_content.tx_nrdfimport_template",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => 'html,htm,tmpl',
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_nrdfimport",
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_extMgm::allowTableOnStandardPages("tx_nrdfimport_feeds");


t3lib_extMgm::addToInsertRecords("tx_nrdfimport_feeds");

$TCA["tx_nrdfimport_feeds"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:n_rdfimport/locallang_db.php:tx_nrdfimport_feeds",		
		"label" => "name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_nrdfimport_feeds.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, name, url, poll_interval, cached_data",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_nrdfimport_mode;;;;1-1-1,tx_nrdfimport_count,tx_nrdfimport_feed,tx_nrdfimport_template";


t3lib_extMgm::addPlugin(Array("LLL:EXT:n_rdfimport/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_nrdfimport_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_nrdfimport_pi1_wizicon.php";
?>
