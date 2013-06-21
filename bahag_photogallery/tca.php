<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_bahagphotogallery_galleries"] = Array (
	"ctrl" => $TCA["tx_bahagphotogallery_galleries"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,path,name,comment"
	),
	"feInterface" => $TCA["tx_bahagphotogallery_galleries"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"path" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_galleries.path",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_galleries.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"comment" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_galleries.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, path, name, comment")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_bahagphotogallery_images"] = Array (
	"ctrl" => $TCA["tx_bahagphotogallery_images"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,path,source,comment,info_pid"
	),
	"feInterface" => $TCA["tx_bahagphotogallery_images"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"path" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_images.path",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"source" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_images.source",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"comment" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_images.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"info_pid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_images.info_pid",		
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
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, path, source, comment, info_pid")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_bahagphotogallery_exif_data_items"] = Array (
	"ctrl" => $TCA["tx_bahagphotogallery_exif_data_items"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,item_name"
	),
	"feInterface" => $TCA["tx_bahagphotogallery_exif_data_items"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"item_name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_exif_data_items.item_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, item_name")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>