<?php

if ( !defined ( 'TYPO3_MODE' ) )
{
	die ( 'Access denied.' );
}

if ( TYPO3_MODE != 'BE' )	
{
	require_once( t3lib_extMgm::extPath( 'news_related' )
		. 'class.tx_newsrelated.php'
	);
}

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraCodesHook' ][] =
								'tx_newsrelated'; 

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'selectConfHook' ][] =
								'tx_newsrelated'; 

?>
