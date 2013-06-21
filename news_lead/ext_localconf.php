<?php

if ( !defined ( 'TYPO3_MODE' ) )
{
	die ( 'Access denied.' );
}

if ( TYPO3_MODE != 'BE' )	
{
	require_once( t3lib_extMgm::extPath( 'news_lead' )
		. 'class.tx_newslead.php'
	);
}

$TYPO3_CONF_VARS[ 'EXTCONF' ][ 'tt_news' ][ 'extraItemMarkerHook' ][] =
								'tx_newslead'; 

t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_newslead_leadperiod=1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_newslead_leads=1');

?>
