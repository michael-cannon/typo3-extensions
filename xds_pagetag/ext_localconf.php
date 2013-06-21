<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF']['indexed_search']['be_hooks']['additionalSearchStat'] = 'EXT:xds_pagetag/class.tx_xdspagetag.php:&tx_xdspagetag';
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_xdspagetag_pi1.php', '_pi1', 'list_type', 0);

?>