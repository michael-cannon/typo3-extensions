<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_jmquote_quotation"] = Array (
	"ctrl" => $TCA["tx_jmquote_quotation"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,text,author"
	),
	"feInterface" => $TCA["tx_jmquote_quotation"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:jm_quote/locallang_db.php:tx_jmquote_quotation.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:jm_quote/locallang_db.php:tx_jmquote_quotation.author",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, text, author")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>