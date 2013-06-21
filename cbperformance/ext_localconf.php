<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:10 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:
if ($_EXTCONF['setPageTSconfig'])	{
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:cbperformance/pageTSconfig.txt">');
}

$TYPO3_CONF_VARS['BE']['compressionLevel'] = '3';
$TYPO3_CONF_VARS['BE']['trackBeUser'] = '0';
$TYPO3_CONF_VARS['FE']['compressionLevel'] = '3';
$TYPO3_CONF_VARS['GFX']['enable_typo3temp_db_tracking'] = '0';
$TYPO3_CONF_VARS['SYS']['no_pconnect'] = 1;
$TYPO3_CONF_VARS['SYS']['systemLog'] = '';
$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = '4';

//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cbperformance_pi1.php','_pi1','list_type',1);
?>
