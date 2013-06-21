<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_newslead_leadperiod"] = Array (
	"ctrl" => $TCA["tx_newslead_leadperiod"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,description,startdate,enddate"
	),
	"feInterface" => $TCA["tx_newslead_leadperiod"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leadperiod.description",		
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"startdate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leadperiod.startdate",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"enddate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leadperiod.enddate",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "description;;1;;1-1-1, startdate, enddate, hidden")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_newslead_leads"] = Array (
	"ctrl" => $TCA["tx_newslead_leads"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" =>
		"hidden,date,news_id,fe_user_id,leadsent,leadtimeframe,filename,referrer"
	),
	"feInterface" => $TCA["tx_newslead_leads"]["feInterface"],
	"columns" => Array (
		"leadsent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.leadsent",		
			"config" => Array (
				"type" => "check",
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.date",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"news_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.news_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_news",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"fe_user_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.fe_user_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"leadtimeframe" => Array (        
			"exclude" => 1,        
			"label" =>
			"LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.leadtimeframe",
			"config" => Array (
				"type" => "group",    
				"internal_type" => "db",    
				"allowed" => "tx_newslead_leadperiod",    
				"size" => 1,    
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"filename" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.filename",		
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "256",
			)
		),
		"referrer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_lead/locallang_db.php:tx_newslead_leads.referrer",		
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "256",
			)
		),
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "date;;1;;1-1-1, news_id, fe_user_id, filename, referrer, leadtimeframe, leadsent, hidden")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
