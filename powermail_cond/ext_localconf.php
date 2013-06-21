<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_powermailcond_rules=1
');

$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_powermailcond_conditions'][0] = array(
	'fList' => 'title',
	'icon' => TRUE,
);
$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_powermailcond_rules'][0] = array(
	'fList' => 'title',
	'icon' => TRUE,
);

##### Hook Section #####

// Hook PM_FieldWrapMarkerArrayHook: Manipulate each field with eventhandler if needed
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FieldWrapMarkerArrayHook'][] = 'EXT:powermail_cond/lib/class.tx_powermailcond_fields.php:tx_powermail_cond_fields';

// Hook PM_FieldWrapMarkerArrayHookInner: Manipulate the innercontent of each field
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FieldWrapMarkerArrayHookInner'][] = 'EXT:powermail_cond/lib/class.tx_powermailcond_fields.php:tx_powermail_cond_fields';

// Hook PM_FormWrapMarkerHookInner: Manipulate fieldsets if needed
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FormWrapMarkerHookInner'][] = 'EXT:powermail_cond/lib/class.tx_powermailcond_fieldsets.php:tx_powermail_cond_fieldsets';

// MH: Hook PM_FormWrapMarkerHookInner: Manipulate fieldsets if needed
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_MarkerArrayHook'][] = 'EXT:powermail_cond/lib/class.tx_powermailcond_confirmation.php:tx_powermail_cond_confirmation';
?>