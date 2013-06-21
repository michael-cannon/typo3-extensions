<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_jobbank_list"] = Array (
	"ctrl" => $TCA["tx_jobbank_list"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,occupation,location,status,industry,clevel,sponsor_id,joboverview,,company_description,additional_requirement,qualification"
	),
	"feInterface" => $TCA["tx_jobbank_list"]["feInterface"],
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
		"occupation" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.occupation",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"location" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.location",		
			"config" => Array (
				"type" => "select",
				"items" => Array(
				),
				"foreign_table" => "static_country_zones",
			)
		),
		"city" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"industry" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.industry",		
			"config" => Array (
				"type" => "select",	
				"foreign_table"=>"tx_t3consultancies_cat",
				"foreign_table_where" => " ORDER BY tx_t3consultancies_cat.uid",
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
			)
		),
		"clevel" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.clevel",		
			"config" => Array (
				"type" => "select",
				"items" => Array (				
				),
				"foreign_table" => "tx_jobbank_career"
			)
		),
		"sponsor_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.sponsor_id",		
			"config" => Array (
				"type" => "select",
				"items" => Array (				
				),
				"foreign_table" => "tx_t3consultancies"
			)
		),
		"qualification" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.qualification",		
			"config" => Array (
				"type" => "select",
				"items" => Array (				
				),
				"foreign_table" => "tx_jobbank_qualification"
			)
		),
		"status" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.status",		
			"config" => Array (
				"type" => "select",
				"items" => Array (				
				),
				"foreign_table" => "tx_jobbank_status"
			)
		),
		"company_description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.company_description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"additional_requirement" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.additional_requirement",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"major_responsibilities" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.major_responsibilities",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"position_filled" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.position_filled",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"joboverview" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_list.joboverview",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, company_description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts],joboverview;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts],occupation, city, location,
		sponsor_id, major_responsibilities;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts],additional_requirement;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts],industry, status,clevel, qualification,position_filled")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_jobbank_status"] = Array (
	"ctrl" => $TCA["tx_jobbank_status"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,status_name"
	),
	"feInterface" => $TCA["tx_jobbank_status"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"status_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_status.status_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, status_name")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_jobbank_career"] = Array (
	"ctrl" => $TCA["tx_jobbank_career"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,career_name"
	),
	"feInterface" => $TCA["tx_jobbank_career"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"career_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_career.career_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, career_name")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_jobbank_qualification"] = Array (
	"ctrl" => $TCA["tx_jobbank_qualification"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,qualification"
	),
	"feInterface" => $TCA["tx_jobbank_qualification"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"qualification" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank/locallang_db.php:tx_jobbank_qualification.qualification",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, qualification")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
