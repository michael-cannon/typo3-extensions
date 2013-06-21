<?php
if (!defined ("TYPO3_MODE"))	 die ("Access denied.");
$tempColumns = Array (
	"tx_newsrelated_dontshowinrelatednews" => Array (		
		"exclude" => 1,		
		"label" =>
		"LLL:EXT:news_related/locallang_db.php:tt_news_cat.tx_newsrelated_dontshowinrelatednews",		
		"config" => Array (
			"type" => "check",
		)
	),
);

t3lib_div::loadTCA("tt_news_cat");
t3lib_extMgm::addTCAcolumns("tt_news_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news_cat","tx_newsrelated_dontshowinrelatednews;;;;1-1-1");
if (TYPO3_MODE=='BE')	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('RELATED', 'RELATED');
}
?>
