<?php

if ( !defined ( 'TYPO3_MODE' ) )
{
	die ( 'Access denied.' );
}

if ( TYPO3_MODE != 'BE' )	
{
	require_once( t3lib_extMgm::extPath( 'news_userinfo' )
		. 'class.tx_newsuserinfo.php'
	);
}

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraItemMarkerHook' ][] =
								'tx_newsuserinfo'; 

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraCodesHook' ][] =
								'tx_newsuserinfo'; 

?>
