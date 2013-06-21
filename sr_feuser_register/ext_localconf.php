<?php
	if (!defined ('TYPO3_MODE')) die ('Access denied.');
		 
	t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', '
		tt_content.CSS_editor.ch.tx_srfeuserregister_pi1 = < plugin.tx_srfeuserregister_pi1.CSS_editor
		', 43);
	 
	t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_srfeuserregister_pi1.php', '_pi1', 'list_type', 0);
	 
	 
	t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', '
		plugin.tx_srfeuserregister_pi1 {
		fe_userOwnSelf = 1
		fe_userEditSelf = 1
		delete = 1
		}', 43);
	 
$extPath = t3lib_extMgm::siteRelPath('sr_feuser_register');

/*if ( TYPO3_MODE == 'FE') {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$extPath.'pi1/class.tx_srfeuserregister_pi1.php'] = t3lib_extMgm::extPath('sr_feuser_register').'pi1/ext/class.tx_srfeuserregister_pi1_extended.php';
}
*/

?>
