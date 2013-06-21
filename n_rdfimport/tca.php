<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_nrdfimport_feeds"] = Array (
	"ctrl" => $TCA["tx_nrdfimport_feeds"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,name,url,poll_interval,cached_data"
	),
	"feInterface" => $TCA["tx_nrdfimport_feeds"]["feInterface"],
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
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tx_nrdfimport_feeds.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"url" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tx_nrdfimport_feeds.url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"poll_interval" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tx_nrdfimport_feeds.poll_interval",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"cached_data" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:n_rdfimport/locallang_db.php:tx_nrdfimport_feeds.cached_data",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, name, url, poll_interval, cached_data")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);
?>