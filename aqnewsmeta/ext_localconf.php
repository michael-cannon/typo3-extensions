<?php

if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

if (TYPO3_MODE !='BE')	{
	require_once(t3lib_extMgm::extPath('aqnewsmeta') .'class.tx_aqnewsmeta.php');
}

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'tx_aqnewsmeta'; 

?>
