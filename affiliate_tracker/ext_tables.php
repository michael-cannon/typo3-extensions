<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_affiliatetracker_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_affiliatetracker_modfunc1.php",
		"LLL:EXT:affiliate_tracker/locallang_db.php:moduleFunction.tx_affiliatetracker_modfunc1",
		"wiz"	
	);
}

$tempColumns = Array (
	"tx_affiliatetracker_affiliate_codes" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_t3consultancies.tx_affiliatetracker_codes",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_affiliatetracker_codes",	
			"foreign_table" => "tx_affiliatetracker_codes",	
			"foreign_table_where" => "AND tx_affiliatetracker_codes.pid=###CURRENT_PID### ORDER BY tx_affiliatetracker_codes.uid",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 99,	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_affiliatetracker_codes",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"list" => Array(
					"type" => "script",
					"title" => "List",
					"icon" => "list.gif",
					"params" => Array(
						"table"=>"tx_affiliatetracker_codes",
						"pid" => "###CURRENT_PID###",
					),
					"script" => "wizard_list.php",
				),
				"edit" => Array(
					"type" => "popup",
					"title" => "Edit",
					"script" => "wizard_edit.php",
					"popup_onlyOpenIfSelected" => 1,
					"icon" => "edit2.gif",
					"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
				),
			),
		)
	),
);


t3lib_div::loadTCA("tx_t3consultancies");
t3lib_extMgm::addTCAcolumns("tx_t3consultancies",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_t3consultancies","tx_affiliatetracker_affiliate_codes;;;;1-1-1");


t3lib_extMgm::allowTableOnStandardPages("tx_affiliatetracker_codes");


t3lib_extMgm::addToInsertRecords("tx_affiliatetracker_codes");

$TCA["tx_affiliatetracker_codes"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_codes",		
		"label" => "affiliate_code",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_affiliatetracker_codes.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "affiliate_code",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_affiliatetracker_visitor_tracking");


t3lib_extMgm::addToInsertRecords("tx_affiliatetracker_visitor_tracking");

$TCA["tx_affiliatetracker_visitor_tracking"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking",		
		"label" => "feuser_id",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_affiliatetracker_visitor_tracking.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "affiliate_id, landing_url, referer_url, feuser_id, full_affiliate_code, affiliate_source_code, affiliate_index_code",
	)
);
?>
