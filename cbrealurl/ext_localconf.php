<?php
// TYPO3 CVS ID: $Id: ext_localconf.php,v 1.3 2012/02/06 14:19:47 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$path							= t3lib_extMgm::extPath('cbrealurl')."realurl.php";
$length							= strlen( $path );
// include( $path );
$TYPO3_CONF_VARS['EXT']['extConf']['realurl'] = 'a:5:{s:10:"configFile";s:'.$length.':'.$path.';s:14:"enableAutoConf";s:1:"0";s:14:"autoConfFormat";s:1:"0";s:12:"enableDevLog";s:1:"0";s:19:"enableChashUrlDebug";s:1:"0";}';

?>
