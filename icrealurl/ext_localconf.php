<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:37 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

include(t3lib_extMgm::extPath('icrealurl')."realurl.php");

$TYPO3_CONF_VARS['EXT']['extConf']['realurl'] = 'a:4:{s:10:"configFile";s:26:"typo3conf/realurl_conf.php";s:14:"enableAutoConf";s:1:"0";s:14:"autoConfFormat";s:1:"0";s:12:"enableDevLog";s:1:"0";}';

// include customizations outside of this script
$customFile						= PATH_typo3conf .'realurl-custom.php';

if ( file_exists( $customFile ) ) {
	include( $customFile );
}

?>
