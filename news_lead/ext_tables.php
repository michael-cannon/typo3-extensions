<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_newslead_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_newslead_modfunc1.php",
		"LLL:EXT:news_lead/locallang_db.php:moduleFunction.tx_newslead_modfunc1",
		"wiz"	
	);
}

if (TYPO3_MODE=="BE") {
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_newslead_modfunc2",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc2/class.tx_newslead_modfunc2.php",
		"LLL:EXT:news_lead/locallang_db.php:moduleFunction.tx_newslead_modfunc2",
		"wiz"	
	);
}

$tempColumns = Array (
	"tx_newslead_leadon" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_lead/locallang_db.php:tt_news.tx_newslead_leadon",		
		"config" => Array (
			"type" => "check",
			"default" => 0,
		)
	),
	"tx_newslead_timeframes" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_lead/locallang_db.php:tt_news.tx_newslead_timeframes",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_newslead_leadperiod",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 99,	
			"MM" => "tt_news_tx_newslead_timeframes_mm",
			'wizards' => Array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'add' => Array(
					'type' => 'script',
					'title' => 'Create new time period',
					'icon' => 'add.gif',
					'params' => Array(
						'table'=>'tx_newslead_leadperiod',
						'pid' => '###STORAGE_PID###',
						'setValue' => 'append'
					),
					'script' => 'wizard_add.php',
				),
			),
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news",",--div--;Leads,tx_newslead_leadon;;;;1-1-1, tx_newslead_timeframes");

$TCA["tx_newslead_leadperiod"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leadperiod",		
		"label" => "description",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY startdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_newslead_leadperiod.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, startdate, enddate",
	)
);

$TCA["tx_newslead_leads"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads",		
		"label" => "news_id",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_newslead_leads.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, date, news_id, fe_user_id, leadsent, leadtimeframe",
	)
);
?>
