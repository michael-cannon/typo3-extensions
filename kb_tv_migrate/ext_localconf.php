<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['foldOut'] = intval($_EXTCONF['foldOut']) ? true: false;

?>
