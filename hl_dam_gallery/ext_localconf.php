<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_hldamgallery_pi1 = < plugin.tx_hldamgallery_pi1.CSS_editor
',43);

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath('hl_dam_gallery') . 'class.ux_tslib_content.php';

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_hldamgallery_pi1.php','_pi1','list_type',1);
?>