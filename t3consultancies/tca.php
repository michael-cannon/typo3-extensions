<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_t3consultancies"] = Array (
	"ctrl" => $TCA["tx_t3consultancies"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" =>
		"hidden,starttime,endtime,fe_group,title,description,url,contact_email,contact_name,services,selected,weight,fe_owner_user,logo,coupon,cntry"
	),
	"feInterface" => $TCA["tx_t3consultancies"]["feInterface"],
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
		"fe_group" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
			)
		),
		"url" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"real_url" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.real_url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"map_url" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.map_url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"contact_email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.contact_email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"contact_phone" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.contact_phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"contact_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.contact_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"services" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.services",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_t3consultancies_cat",	
				"foreign_table_where" => "AND tx_t3consultancies_cat.pid=###STORAGE_PID### ORDER BY tx_t3consultancies_cat.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_t3consultancies_services_mm",
			)
		),
		"selected" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.selected",		
			"config" => Array (
				"type" => "check",
			)
		),
		"weight" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.weight",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.weight.I.normal", 0),
					Array("LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.weight.I.high", 100),
					Array("LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.weight.I.low", -100)
				),
			)
		),
		"fe_owner_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.fe_owner_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"logo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.logo",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 512,	
				"uploadfolder" => "uploads/tx_t3consultancies",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"coupon" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.coupon",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 512,	
				"uploadfolder" => "uploads/tx_t3consultancies",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"featured_logo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.featured_logo",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 512,	
				"uploadfolder" => "uploads/tx_t3consultancies",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		'address' => Array (
			'label' => 'LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'city' => Array (
			'label' => 'LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.city',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'state' => Array (
			'label' => 'LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.state',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '2',
				'max' => '2'
			)
		),
		'zip' => Array (
			'label' => 'LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.zip',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '10',
				'max' => '20'
			)
		),
		"cntry" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies.cntry",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "static_countries",	
				"foreign_table_where" => "ORDER BY static_countries.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,
		description;;;;3-3-3, url, real_url, map_url, contact_name,
		contact_phone, contact_email, address, city, state, zip, cntry,
		services, logo, selected, featured_logo, coupon, weight, fe_owner_user")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_t3consultancies_cat"] = Array (
	"ctrl" => $TCA["tx_t3consultancies_cat"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "title"
	),
	"feInterface" => $TCA["tx_t3consultancies_cat"]["feInterface"],
	"columns" => Array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies_cat.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies_cat.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 512,	
				"uploadfolder" => "uploads/tx_t3consultancies",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies_cat.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "title;;;;2-2-2,description,image")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
