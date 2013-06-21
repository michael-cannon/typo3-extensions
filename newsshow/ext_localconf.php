<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}
	
	// Add plugin
	t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_newsshow_pi1.php','_pi1','list_type',0);
	
	// Get extension configuration
	$newsshow_extConf = unserialize($_EXTCONF);
	
	// Add constants
	t3lib_extMgm::addTypoScriptConstants('extension.newsshow.typeNum = ' . $newsshow_extConf['typeNum']);
	
	// Save & New button
	t3lib_extMgm::addUserTSConfig('
		options.saveDocNew.tx_newsshow_newsshows=1
	');
?>
