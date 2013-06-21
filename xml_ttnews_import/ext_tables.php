<?php
//$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	//Target PID: The page with which the incoming newslinks should be associated. 
	"tx_xmlttnewsimport_targetpid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:xml_ttnews_import/locallang_db.php:tx_ccrdfnewsimport.tx_xmlttnewsimport_targetpid",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
    //Category: Relates to table tx_ccrdfnewsimport_tx_xmlttnewsimport_category_mm to allow
	//setting of news category for the incoming RSS entry.
	"tx_xmlttnewsimport_category" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:xml_ttnews_import/locallang_db.php:tx_ccrdfnewsimport.tx_xmlttnewsimport_category",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "tt_news_cat",    
            "size" => 5,    
            "minitems" => 0,
            "maxitems" => 23,    
            "MM" => "tx_ccrdfnewsimport_tx_xmlttnewsimport_category_mm",
        )
    ),	
);


t3lib_div::loadTCA("tx_ccrdfnewsimport");
t3lib_extMgm::addTCAcolumns("tx_ccrdfnewsimport",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_ccrdfnewsimport","tx_xmlttnewsimport_targetpid;;;;1-1-1, tx_xmlttnewsimport_category");

$tempColumns = Array (
	"tx_xmlttnewsimport_xmlunid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:xml_ttnews_import/locallang_db.php:tt_news.tx_xmlttnewsimport_xmlunid",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_xmlttnewsimport_xmlunid;;;;1-1-1");


if (TYPO3_MODE=="BE")	{
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_xmlttnewsimport_cm1",
		"path" => t3lib_extMgm::extPath($_EXTKEY)."class.tx_xmlttnewsimport_cm1.php"
	);
}
?>