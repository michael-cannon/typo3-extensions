<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::allowTableOnStandardPages("tx_newssearch_result");

$TCA["tx_newssearch_result"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_result",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_newssearch_result.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fe_group, title, search_text, category, style, user_id",
	)
);

$TCA["tx_newssearch_log"] = Array (
        "ctrl" => Array (
                "title" => "LLL:EXT:news_search/locallang_db.php:tx_newssearch_log",
                "label" => "uid",
                "tstamp" => "tstamp",
                "crdate" => "crdate",
                "cruser_id" => "cruser_id",
                "default_sortby" => "ORDER BY crdate",
                "delete" => "deleted",
                "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
                "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_newssearch_log.gif",
        ),
        "feInterface" => Array (
                "fe_admin_fieldList" => "user, search_string",
        )
);


$tempColumns = Array (
    "tx_newssearch_backlink_to_page" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:news_search/locallang_db.php:tt_content.tx_newssearch_backlink_to_page",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
);

t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_newssearch_backlink_to_page;;;;1-1-1");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";

$tempColumns = Array (
    "tx_newssearch_bpm_landing_page" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:news_search/locallang_db.php:tt_news_cat.tx_newssearch_bpm_landing_page",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
    "tx_newssearch_soa_landing_page" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:news_search/locallang_db.php:tt_news_cat.tx_newssearch_soa_landing_page",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
);


t3lib_div::loadTCA("tt_news_cat");
t3lib_extMgm::addTCAcolumns("tt_news_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news_cat","tx_newssearch_bpm_landing_page;;;;1-1-1, tx_newssearch_soa_landing_page");

t3lib_extMgm::addPlugin(Array("LLL:EXT:news_search/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","News Search");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_newssearch_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_newssearch_pi1_wizicon.php";
?>
