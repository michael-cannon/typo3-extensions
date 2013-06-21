<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_newsreadedcount_readedcounter" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:newsreadedcount/locallang_db.php:tt_news.tx_newsreadedcount_readedcounter",		
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "6",
			"eval" => "int",
			"checkbox" => "0",
			"default" => 0
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_newsreadedcount_readedcounter;;;;1-1-1");
?>