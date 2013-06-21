<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_thmailformplus_main"] = Array (
	"ctrl" => $TCA["tx_thmailformplus_main"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "email_to,email_subject,email_sender,email_redirect,email_requiredfields,email_htmltemplate"
	),
	"feInterface" => $TCA["tx_thmailformplus_main"]["feInterface"],
	"columns" => Array (
		"email_to" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_to",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
//				"eval" => "required,nospace",
			)
		),
		"email_subject" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_subject",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"email_sender" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_sender",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "nospace",
			)
		),
		"email_redirect" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_redirect",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "pages",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"email_requiredfields" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_requiredfields",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"email_htmltemplate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_htmltemplate",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_thmailformplus",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"email_replyto" => Array (
		        'exclude' => 1,
			'label' => 'LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_replyto',
		        'config' => Array (
			    'type' => 'input',
			    'size' => '30',
    		        )
		),
		"email_sendtouser" => Array(
		    "exclude" => 1,
		    "label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_sendtouser",
		    "config" => Array(
			"type" => "input",
			"size" => "30",
		    )
		),
		"email_subject_user" => Array(
		    "exclude" => 1,
		    "label" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main.email_subject_user",
		    "config" => Array(
			"type" => "input",
			"size" => "30",
		    )
		),
	),

	"types" => Array (
		"0" => Array("showitem" => "email_to;;;;1-1-1, email_subject, email_sender, email_redirect, email_requiredfields, email_htmltemplate,email_replyto,;;;;2-2-2,email_sendtouser,email_subject_user")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);

							    

?>