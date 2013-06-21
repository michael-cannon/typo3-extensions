<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_mssurvey_items"] = Array (
	"ctrl" => $TCA["tx_mssurvey_items"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,multitem,break,type,question,description,itemvalues,itemrows,exclude,items,width,height,item_groups"
	),
	"feInterface" => $TCA["tx_mssurvey_items"]["feInterface"],
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
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,lower,alphanum_x,uniqueInPid",
			)
		),
		"multitem" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.multitem",		
			"config" => Array (
				"type" => "check",
			)
		),
		"break" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.break",		
			"config" => Array (
				"type" => "check",
			)
		),
		"type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.0", "0"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.1", "1"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.2", "2"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.3", "3"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.4", "4"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.5", "5"),
					Array("LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.type.I.6", "6"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"question" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.question",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
			, 'defaultExtra'	=> 'richtext[*]'
		),
		"itemvalues" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.itemvalues",		
			"config" => Array (
				"type" => "text",
				"wrap" => "OFF",
				"cols" => "20",	
				"rows" => "5",
			)
		),
		"itemrows" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.itemrows",		
			"config" => Array (
				"type" => "text",
				"wrap" => "OFF",
				"cols" => "20",	
				"rows" => "5",
			)
		),
		"exclude" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.exclude",		
			"config" => Array (
				"type" => "check",
			)
		),
		"items" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.items",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_mssurvey_items",	
				"foreign_table_where" => "AND tx_mssurvey_items.pid=###CURRENT_PID### AND tx_mssurvey_items.multitem=1 ORDER BY tx_mssurvey_items.uid ASC",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
		"width" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.width",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"eval" => "int",
				"default" => "30",
			)
		),
		"height" => Array (        
			"exclude" => 1,        
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.height",        
			"config" => Array (
			"type" => "input",    
			"size" => "5",
			"default" => "3",
			"eval" => "int",
	               )
	       ),
		"item_groups" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.item_groups",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "fe_groups",	
				"foreign_table_where" => "ORDER BY fe_groups.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 25,	
				"MM" => "tx_mssurvey_items_item_groups_mm",
			)
		),
		"optional" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items.optional",        
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, width, description, item_groups;;;richtext[*];4-4-4"),
		"1" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, width, height,description, item_groups;;;richtext[*];4-4-4"),
		"2" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, itemvalues, description, item_groups;;;richtext[*];4-4-4"),
		"3" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, itemvalues, description, item_groups;;;richtext[*];4-4-4"),
		"4" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, itemvalues, description, item_groups;;;richtext[*];4-4-4"),
		"5" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, question, itemvalues, height,description, item_groups;;;richtext[*];4-4-4"),
		"6" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2,optional, multitem, break, type;;;;3-3-3, items,itemrows, exclude,description, item_groups;;;richtext[*];4-4-4"),

),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_mssurvey_results"] = Array (
	"ctrl" => $TCA["tx_mssurvey_results"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,surveyid,fe_cruser_id,results,remoteaddress"
	),
	"feInterface" => $TCA["tx_mssurvey_results"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"surveyid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results.surveyid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "pages,tt_news",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		
		"fe_cruser_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results.fe_cruser_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"results" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results.results",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
        "remoteaddress" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results.remoteaddress",        
            "config" => Array (
                "type" => "none",    
                "size" => "15",    
                "max" => "15",
            )
        ),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, surveyid, fe_cruser_id, results, remoteaddress")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
