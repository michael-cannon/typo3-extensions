<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("mc_googlesitemap")."class.tx_mcgooglesitemap_tt_content_tx_mcgooglesitemap_objective.php");

$tempColumns = Array (
	"tx_mcgooglesitemap_objective" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_objective",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_objective.I.0", "0"),
			),
			"itemsProcFunc" => "tx_mcgooglesitemap_tt_content_tx_mcgooglesitemap_objective->main",	
			"size" => 1,	
			"maxitems" => 1,
		)
	),
	"tx_mcgooglesitemap_lastmod" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod.I.0", "0"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod.I.1", "1"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
	"tx_mcgooglesitemap_pageuid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_pageuid",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_mcgooglesitemap_url" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_url",		
		"config" => Array (
			"type" => "text",
			"cols" => "30",	
			"rows" => "6",
		)
	),
	"tx_mcgooglesitemap_changefreq" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("","0"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.0", "1"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.1", "2"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.2", "3"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.3", "4"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.4", "5"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.5", "6"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_changefreq.I.6", "7"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
	"tx_mcgooglesitemap_priority" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_priority",		
		"config" => Array (
			"type" => "input",	
			"size" => "5",	
			"range" => Array ("lower"=>0,"upper"=>1),	
			"eval" => "nospace",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);

$tempColumns = Array (
	"tx_mcgooglesitemap_priority" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_priority",		
		"config" => Array (
			"type" => "input",	
			"size" => "5",	
                        "range" => Array ("lower"=>0,"upper"=>1),
			"eval" => "nospace",
		)
	),
	"tx_mcgooglesitemap_changefreq" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.7", "0"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.0", "1"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.1", "2"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.2", "3"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.3", "4"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.4", "5"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.5", "6"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:pages.tx_mcgooglesitemap_changefreq.I.6", "7"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_mcgooglesitemap_priority;;;;1-1-1, tx_mcgooglesitemap_changefreq");

$tempColumns = Array (
	"tx_mcgooglesitemap_lastmod" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod.I.0", "0"),
				Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.tx_mcgooglesitemap_lastmod.I.1", "1"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["menu"]["subtype_value_field"]="menu_type";
$TCA["tt_content"]["types"]["menu"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_mcgooglesitemap_objective;;;;1-1-1, tx_mcgooglesitemap_lastmod, tx_mcgooglesitemap_pageuid, tx_mcgooglesitemap_url, tx_mcgooglesitemap_changefreq, tx_mcgooglesitemap_priority";


t3lib_extMgm::addPlugin(Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.menu_type_pi1", $_EXTKEY."_pi1"),"menu_type");


t3lib_extMgm::addPlugin(Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.menu_type_pi2", $_EXTKEY."_pi2"),"menu_type");


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["menu"]["subtype_value_field"]="menu_type";
$TCA["tt_content"]["types"]["menu"]["subtypes_addlist"][$_EXTKEY."_pi3"]="tx_mcgooglesitemap_lastmod;;;;1-1-1";


t3lib_extMgm::addPlugin(Array("LLL:EXT:mc_googlesitemap/locallang_db.php:tt_content.menu_type_pi3", $_EXTKEY."_pi3"),"menu_type");
?>
