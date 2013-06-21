<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cabuniquenews_pi1.php','_pi1','',0);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cabuniquenews_pi1.php','_pi1','',0);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'EXT:cab_uniquenews/pi1/class.tx_cabuniquenews_pi1.php:tx_cabuniquenews_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'][] = 'EXT:cab_uniquenews/pi1/class.tx_cabuniquenews_pi1.php:tx_cabuniquenews_pi1';          

//$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][] = t3lib_div::makeInstance('tx_cabuniquenews_pi1');

//'tx_cabuniquenews_pi1';


?>