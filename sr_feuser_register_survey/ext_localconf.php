<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_srfeuserregistersurvey_pi1 = < plugin.tx_srfeuserregistersurvey_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_srfeuserregistersurvey_pi1.php','_pi1','list_type',0);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_srfeuserregistersurvey_results_archive=1
');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'EXT:sr_feuser_register_survey/classes/class.user_survey_config.php:user_survey_config->goForSurvey';

?>
