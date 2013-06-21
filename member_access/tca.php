<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_memberaccess_acl"] = Array (
	"ctrl" => $TCA["tx_memberaccess_acl"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "email,name,company,accesslevel,endtimeExtension,endtime"
	),
	"feInterface" => $TCA["tx_memberaccess_acl"]["feInterface"],
	"columns" => Array (
		"email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"company" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.company",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "80",	
				"eval" => "trim",
			)
		),
		"accesslevel" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.accesslevel",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"endtimeExtension" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.endtimeExtension",		
			"config" => Array (
				"type" => "input",	
				"size" => "12",	
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl.endtime",		
			"config" => Array (
				"type" => "input",	
				"size" => "12",	
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "email;;;;1-1-1, name, company, accesslevel, endtimeExtension, endtime ")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_memberaccess_registrationerrors"] = Array (
	"ctrl" => $TCA["tx_memberaccess_registrationerrors"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,userid,errortime,email,errors"
	),
	"feInterface" => $TCA["tx_memberaccess_registrationerrors"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"userid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_registrationerrors.userid",		
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
		"errortime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_registrationerrors.errortime",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_registrationerrors.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
		"errors" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_registrationerrors.errors",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, userid, errortime, email, errors")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>