<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_emailscheduler_main"] = Array (
	"ctrl" => $TCA["tx_emailscheduler_main"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,duration,emailcontent"
	),
	"feInterface" => $TCA["tx_emailscheduler_main"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:emailscheduler/locallang_db.php:tx_emailscheduler_main.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"duration" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:emailscheduler/locallang_db.php:tx_emailscheduler_main.duration",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"emailcontent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:emailscheduler/locallang_db.php:tx_emailscheduler_main.emailcontent",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_emailscheduler",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, duration;;;;3-3-3, emailcontent")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>