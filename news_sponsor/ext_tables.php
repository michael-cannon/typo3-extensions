<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_newssponsor_sponsor" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_sponsor/locallang_db.php:tt_news.tx_newssponsor_sponsor",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_t3consultancies",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_newssponsor_message" => Array (
		"exclude" => 1,
		"label" =>"LLL:EXT:news_sponsor/locallang_db.php:tt_news.tx_newssponsor_message",
		"config" => Array (
			"type"=> "text",
			"columns"=> "40",
			"rows"=> "5"
		)
	),
);
t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);

// cbPrint(  $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ] );

// put sponsor tx_newssponsor_sponsor after author on News general tab
if ( preg_match( '#(author;;3;;)#'
		, $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]
		)
	)
{
	$TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]	=
								preg_replace( '#(author;;3;;)#'
									, '\1,tx_newssponsor_sponsor;;;;1-1-1,tx_newssponsor_message'
									, $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]
								);
}

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('SPONSOR', 'SPONSOR');
}
?>
