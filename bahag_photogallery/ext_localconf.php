<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_bahagphotogallery_pi1 = < plugin.tx_bahagphotogallery_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_bahagphotogallery_pi1.php','_pi1','list_type',0);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_bahagphotogallery_galleries=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_bahagphotogallery_images=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_bahagphotogallery_exif_data_items=1
');


//Using the hooks provided in t3lib/class.t3lib_tcemain.php.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:bahag_photogallery/classes/class.tx_bahag_photogallery_cache_control.php:tx_bahag_photogallery_cache_control';

?>
