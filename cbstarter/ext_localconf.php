<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:12 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:
if ($_EXTCONF['setPageTSconfig'])	{
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:cbstarter/pageTSconfig.txt">');
}

if ($_EXTCONF['setUserTSconfig']) {
	t3lib_extMgm::addUserTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:cbstarter/userTSconfig.txt">');
}

// MLC comment out the localconf.php include if you're running a pre-existing
// website and don't like cbstarter's default localconf.php settings
include(t3lib_extMgm::extPath('cbstarter')."scripts/localconf.php");

//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cbstarter_pi1.php','_pi1','list_type',1);
?>
