<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_affiliatetracker_codes=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_affiliatetracker_visitor_tracking=1
');

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fe.php'] = t3lib_extMgm::extPath('affiliate_tracker').'classes/class.ux_tslib_fe.php';
?>