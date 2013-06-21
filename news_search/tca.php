<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$arrAllSelect=array('All',0);
$TCA["tx_newssearch_result"] = Array (
	"ctrl" => $TCA["tx_newssearch_result"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_group,title,search_text,category,style,user_id"
	),
	"feInterface" => $TCA["tx_newssearch_result"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
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
			"label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				'eval' => 'trim,required'
			)
		),
		"search_text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result.search_text",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result.category",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
				$arrAllSelect,
				),
				"foreign_table"=>"tx_t3consultancies_cat",
				"foreign_table_where" => " ORDER BY tx_t3consultancies_cat.title",
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"style" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result.style",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
				Array("Normal","normal"),
				Array("Curriculum","curriculum"),
				)
			)
		),
		"user_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result.user_id",		
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
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, search_text;;;;3-3-3, category, style, user_id")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "fe_group")
	)
);

$TCA["tx_newssearch_log"] = Array (
        "ctrl" => $TCA["tx_newssearch_log"]["ctrl"],
        "interface" => Array (
                "showRecordFieldList" => "user,search_string"
        ),
        "feInterface" => $TCA["tx_newssearch_log"]["feInterface"],
        "columns" => Array (
                "user" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_log.user",
                        "config" => Array (
                                "type" => "group",
                                "internal_type" => "db",
                                "allowed" => "fe_users",
                                "size" => 1,
                                "minitems" => 0,
                                "maxitems" => 1,
                        )
                ),
                "search_string" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_log.search_string",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                        )
                ),
        ),
        "types" => Array (
                "0" => Array("showitem" => "user;;;;1-1-1, search_string")
        ),
        "palettes" => Array (
                "1" => Array("showitem" => "")
        )
);

?>
