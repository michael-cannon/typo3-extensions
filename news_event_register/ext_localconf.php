<?php

if ( !defined ( 'TYPO3_MODE' ) )
{
	die ( 'Access denied.' );
}

if ( TYPO3_MODE != 'BE' )	
{
	require_once( t3lib_extMgm::extPath( 'news_event_register' )
		. 'class.tx_newseventregister.php'
	);
}

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraItemMarkerHook' ][] =
								'tx_newseventregister'; 

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraCodesHook' ][] =
								'tx_newseventregister'; 

t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_newseventregister_Participants=1');

?>
