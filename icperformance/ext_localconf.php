<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:37 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:
if ($_EXTCONF['setPageTSconfig'])	{
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:icperformance/pageTSconfig.txt">');
}

$TYPO3_CONF_VARS['BE']['compressionLevel'] = '3';
$TYPO3_CONF_VARS['BE']['trackBeUser'] = '0';
$TYPO3_CONF_VARS['FE']['compressionLevel'] = '3';
$TYPO3_CONF_VARS['GFX']['enable_typo3temp_db_tracking'] = '0';
$TYPO3_CONF_VARS['SYS']['no_pconnect'] = 1;
$TYPO3_CONF_VARS['SYS']['systemLog'] = '';
$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = '4';
$TYPO3_CONF_VARS['EXT']['extConf']['nc_staticfilecache'] = 'a:5:{s:23:"clearCacheForAllDomains";s:1:"1";s:22:"sendCacheControlHeader";s:1:"1";s:23:"showGenerationSignature";s:1:"0";s:8:"strftime";s:24:"cached statically on: %c";s:5:"debug";s:1:"0";}';	//  Modified or inserted by TYPO3 Extension Manager.

//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_icperformance_pi1.php','_pi1','list_type',1);
?>
