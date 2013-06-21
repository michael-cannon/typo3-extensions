<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:37 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:
if ($_EXTCONF['setPageTSconfig'])	{
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:icmedia/pageTSconfig.txt">');
}

$TYPO3_CONF_VARS['EXT']['extConf']['rgmediaimages'] = 'a:1:{s:6:"rename";s:1:"0";}';
$TYPO3_CONF_VARS['EXT']['extConf']['movies'] = 'a:4:{s:3:"pi1";s:1:"1";s:3:"pi2";s:1:"1";s:3:"pi3";s:1:"1";s:3:"pi4";s:1:"1";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['EXT']['extConf']['rtelightbox'] = 'a:1:{s:11:"lightboxExt";s:10:"individual";}';	//  Modified or inserted by TYPO3 Extension Manager.

?>
