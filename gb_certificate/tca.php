<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_gbcertificate_courses"] = Array (
	"ctrl" => $TCA["tx_gbcertificate_courses"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,code,title,detail_pid,show_certificate,course_prerequisites,hours"
	),
	"feInterface" => $TCA["tx_gbcertificate_courses"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"detail_pid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.detail_pid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "pages",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"show_certificate" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.show_certificate",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"course_prerequisites" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.course_prerequisites",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_gbcertificate_courses",	
				"foreign_table_where" => "ORDER BY tx_gbcertificate_courses.uid",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 20,
			)
		),
		"hours" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses.hours",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, code, title;;;;2-2-2, detail_pid;;;;3-3-3, show_certificate, course_prerequisites, hours")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_gbcertificate_course_users"] = Array (
	"ctrl" => $TCA["tx_gbcertificate_course_users"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,username,name,number,dates,code"
	),
	"feInterface" => $TCA["tx_gbcertificate_course_users"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"username" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users.username",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"number" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users.number",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"dates" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users.dates",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, username, name, number, dates, code")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>