<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_shoppingsystem_pi1.php','_pi1','',1);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_shoppingsystem_pi2.php','_pi2','',1);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_shoppingsystem_pi3.php','_pi3','',1);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'][] = 'EXT:shopping_system/pi1/class.tx_shoppingsystem_pi1.php:tx_shoppingsystem_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'][] = 'EXT:shopping_system/pi2/class.tx_shoppingsystem_pi2.php:tx_shoppingsystem_pi2';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'][] = 'EXT:shopping_system/pi3/class.tx_shoppingsystem_pi3.php:tx_shoppingsystem_pi3';

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_shoppingsystem_pi4 = < plugin.tx_shoppingsystem_pi4.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi4/class.tx_shoppingsystem_pi4.php','_pi4','list_type',1);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_shoppingsystem_pi5 = < plugin.tx_shoppingsystem_pi5.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi5/class.tx_shoppingsystem_pi5.php','_pi5','list_type',1);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_shoppingsystem_pi6 = < plugin.tx_shoppingsystem_pi6.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi6/class.tx_shoppingsystem_pi6.php','_pi6','list_type',1);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_shoppingsystem_pi7 = < plugin.tx_shoppingsystem_pi7.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi7/class.tx_shoppingsystem_pi7.php','_pi7','list_type',1);

// Add by Eugene Lamskoy >>> 
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:shopping_system/class.shopping_tcemainprocdm.php:tx_shopping_tcemainprocdm';
// <<< Add by Eugene Lamskoy

?>