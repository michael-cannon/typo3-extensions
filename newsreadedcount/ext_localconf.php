<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_newsreadedcount_pi1.php','_pi1','',1);

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraCodesHook'][] = 'EXT:newsreadedcount/pi1/class.tx_newsreadedcount_pi1.php:tx_newsreadedcount_pi1';
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'EXT:newsreadedcount/pi1/class.tx_newsreadedcount_pi1.php:tx_newsreadedcount_pi1';
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['selectConfHook'][] = 'EXT:newsreadedcount/pi1/class.tx_newsreadedcount_pi1.php:tx_newsreadedcount_pi1';

// TODO figure out language labels
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LLL:EXT:newsreadedcount/locallang_db.xml:tt_news.pi_flexform.MOST_READ_LATEST', 'MOST_READ_LATEST');
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('MOST_READ_LATEST', 'MOST_READ_LATEST');
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LLL:EXT:newsreadedcount/locallang_db.xml:tt_news.pi_flexform.MOST_READ_LIST', 'MOST_READ_LIST');
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('MOST_READ_LIST', 'MOST_READ_LIST');

?>