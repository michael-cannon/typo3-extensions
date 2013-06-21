<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_eusugarcrm_pi1 = < plugin.tx_eusugarcrm_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_eusugarcrm_pi1.php','_pi1','list_type',0);

$_EXTCONF = unserialize($_EXTCONF);
$TYPO3_CONF_VARS['SVCONF']['sugarCRM']['tx_eusugarcrm_sv1'] = $_EXTCONF;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['sendFormmail-PreProcClass'][] = 'EXT:eu_sugarcrm/class.tx_eusugarcrm_sendformmail.php:tx_eusugarcrm_sendformmail';
?>