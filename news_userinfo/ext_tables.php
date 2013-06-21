<?php

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('USER_INFO', 'USER_INFO');
}

?>
