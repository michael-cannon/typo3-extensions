<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_piapappnote_notes=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_piapappnote_categories=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_piapappnote_devices=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_piapappnote_versions=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_piapappnote_pi1 = < plugin.tx_piapappnote_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_piapappnote_pi1.php','_pi1','list_type',0);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:piap_appnote/class.tx_piapappnote_category_save.php:tx_piapappnote_category_save';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:piap_appnote/class.tx_piapappnote_related_notes_save.php:tx_piapappnote_related_notes_save';

include(t3lib_extMgm::extPath('piap_appnote')."realurl.php");
?>
