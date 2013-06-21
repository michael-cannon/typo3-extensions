<?php
/*
$typoScript='
plugin.tx_tfclifegroups_pi1._LOCAL_LANG.default.all_interests=All interests
plugin.tx_tfclifegroups_pi1._LOCAL_LANG.default.all_semesters=All semesters
plugin.tx_tfclifegroups_pi1._LOCAL_LANG.default.all_ages=All ages
plugin.tx_tfclifegroups_pi1._LOCAL_LANG.default.all_days=All days
plugin.tx_tfclifegroups_pi1._LOCAL_LANG.default.all_categories=All categories
';
t3lib_extMgm::addTypoScriptSetup($typoScript);
*/
#$TYPO3_CONF_VARS['SYS']['locallangXMLOverride']['EXT:tfc_lifegroups/pi1/locallang.php']['ext_tfc_lifegroups'] = 'EXT:ext_tfc_lifegroups/locallang.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tfc_lifegroups/pi1/class.tx_tfclifegroups_pi1.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_tfclifegroups_pi1.php';
?>