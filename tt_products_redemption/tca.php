<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_ttproductsredemption_codes"] = Array (
	"ctrl" => $TCA["tx_ttproductsredemption_codes"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,title,code,percentage,amount,maximumquantity,quantityused"
	),
	"feInterface" => $TCA["tx_ttproductsredemption_codes"]["feInterface"],
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
				"eval" => "datetime",
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
				"eval" => "datetime",
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
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",	
				"eval" => "required,alphanum,nospace,unique",
			)
		),
		"percentage" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.percentage",		
			"config" => Array (
				"type" => "input",	
				"size" => "6",	
				"default" => "0",	
			)
		),
		"amount" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.amount",		
			"config" => Array (
				"type" => "input",	
				"size" => "8",	
				"default" => "0",	
			)
		),
		"maximumquantity" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.maximumquantity",		
			"config" => Array (
				"type" => "input",	
				"size" => "8",	
				"eval" => "int,nospace",
				"default" => "0",	
			)
		),
		"quantityused" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes.quantityused",		
			"config" => Array (
				"type" => "input",	
				"size" => "8",	
				"eval" => "int,nospace",
				"default" => "0",	
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, code;;;;3-3-3, percentage, amount, maximumquantity, quantityused")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);
?>
