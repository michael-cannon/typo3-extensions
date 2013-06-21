<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_tfclifegroups_lifegroups"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_lifegroups"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,semesters,leader1_firstname,leader1_lastname,leader1_phone,leader1_email,leader2_firstname,leader2_lastname,leader2_phone,leader2_email,day,time,location,recurrence,category,ages,interests,url,descr,address,city,zone,zip,country"
	),
	"feInterface" => $TCA["tx_tfclifegroups_lifegroups"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"semesters" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.semesters",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_semesters",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_semesters.uid",	
				"size" => 5,	
				"minitems" => 1,
				"maxitems" => 50,
			)
		),
		"leader1_firstname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader1_firstname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"leader1_lastname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader1_lastname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"leader1_phone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader1_phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"leader1_email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader1_email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"leader2_firstname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader2_firstname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"leader2_lastname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader2_lastname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"leader2_phone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader2_phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"leader2_email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.leader2_email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"day" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.day",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_days",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_days.uid",	
				"size" => 8,	
				"minitems" => 1,
				"maxitems" => 7,
			)
		),
		"time" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.time",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "time",
			)
		),
		"location" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.location",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"recurrence" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.recurrence",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_recurrences",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_recurrences.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.category",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_categories",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_categories.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"ages" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.ages",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_ages",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_ages.uid",	
				"size" => 5,	
				"minitems" => 1,
				"maxitems" => 1000,
			)
		),
		"interests" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.interests",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_tfclifegroups_interests",	
				"foreign_table_where" => "ORDER BY tx_tfclifegroups_interests.uid",	
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
		"url" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.url",		
			"config" => Array (
				"type" => "input",
				"size" => "40",	
				"max" => "256",
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
			)
		),
		"descr" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups.descr",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		'address' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'city' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.city',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '255'
			)
		),
		'zone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.xml:fe_users.zone',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '255',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'zip' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.zip',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '10',
				'max' => '10'
			)
		),
		'country' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.country',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'eval' => 'trim',
				'max' => '3',
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, semesters;;;;3-3-3, leader1_firstname, leader1_lastname, leader1_phone, leader1_email, leader2_firstname, leader2_lastname, leader2_phone, leader2_email, day, time, location, recurrence, category, ages, interests, url, descr, address, city, zone, zip, country")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_categories"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_categories"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_tfclifegroups_categories"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_categories.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_interests"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_interests"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_tfclifegroups_interests"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_interests.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_recurrences"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_recurrences"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,descr"
	),
	"feInterface" => $TCA["tx_tfclifegroups_recurrences"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"descr" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_recurrences.descr",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, descr")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_days"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_days"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_tfclifegroups_days"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_days.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_semesters"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_semesters"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_tfclifegroups_semesters"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_semesters.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_tfclifegroups_ages"] = Array (
	"ctrl" => $TCA["tx_tfclifegroups_ages"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_tfclifegroups_ages"]["feInterface"],
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
			"label" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_ages.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
