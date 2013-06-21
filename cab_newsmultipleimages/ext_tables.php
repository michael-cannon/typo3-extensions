<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_cabnewsmultipleimages_directimages" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cab_newsmultipleimages/locallang_db.xml:tt_news.tx_cabnewsmultipleimages_directimages",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => "gif,png,jpeg,jpg",	
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_cabnewsmultipleimages",
			"show_thumbs" => 1,	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 5,
		)
	),
	"tx_cabnewsmultipleimages_directimages_alttext" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cab_newsmultipleimages/locallang_db.xml:tt_news.tx_cabnewsmultipleimages_directimages_alttext",		
		"config" => Array (
			"type" => "text",
			"cols" => "30",	
			"rows" => "5",
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_cabnewsmultipleimages_directimages;;;;1-1-1, tx_cabnewsmultipleimages_directimages_alttext");
?>