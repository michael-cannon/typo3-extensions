<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cabnewsmultipleimages_pi1.php','_pi1','',0);


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'EXT:cab_newsmultipleimages/pi1/class.tx_cabnewsmultipleimages_pi1.php:tx_cabnewsmultipleimages_pi1';       
?>