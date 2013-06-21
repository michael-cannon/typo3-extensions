<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_sponsorcontentscheduler_featured_weeks"] = Array (
	"ctrl" => $TCA["tx_sponsorcontentscheduler_featured_weeks"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "starttime,endtime,description"
	),
	"feInterface" => $TCA["tx_sponsorcontentscheduler_featured_weeks"]["feInterface"],
	"columns" => Array (
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_featured_weeks.description",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "starttime;;;;1-1-1, endtime, description")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_sponsorcontentscheduler_featured_weeks_mm"] = Array (
	"ctrl" => $TCA["tx_sponsorcontentscheduler_featured_weeks_mm"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "uid_local,uid_foreign"
	),
	"feInterface" => $TCA["tx_sponsorcontentscheduler_featured_weeks_mm"]["feInterface"],
	"columns" => Array (
		"uid_local" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_featured_weeks_mm.uid_local",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_news",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"uid_foreign" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_sponsorcontentscheduler_featured_weeks",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "uid_local;;;;1-1-1, uid_foreign")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_sponsorcontentscheduler_package"] = Array (
	"ctrl" => $TCA["tx_sponsorcontentscheduler_package"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,title,company_profile,bulletin,roundtable,whitepaper,fe_uid,sponsor_id"
	),
	"feInterface" => $TCA["tx_sponsorcontentscheduler_package"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"company_profile" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.company_profile",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.company_profile.I.0", "1"),
					Array("LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.company_profile.I.1", "0"),
				),
			)
		),
		"bulletin" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.bulletin",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"roundtable" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.roundtable",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"whitepaper" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.whitepaper",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"fe_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.fe_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"sponsor_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package.sponsor_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_t3consultancies",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, sponsor_id, fe_uid, company_profile;;;;3-3-3, bulletin, roundtable, whitepaper")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);
?>