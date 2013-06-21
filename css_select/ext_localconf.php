<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}
	
	// FE plugins
	t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cssselect_pi1.php','_pi1','',1);
	
	// Add CSS file(s) to root line
	$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',tx_cssselect_stylesheets,tx_cssselect_stop';
?>
