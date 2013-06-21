<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Alexander Kellner <alexander.kellner@einpraegsam.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class tx_powermail_cond_fieldsets extends tslib_pibase {

	var $prefixId = 'tx_powermailcond_pi1'; // Prefix
	var $scriptRelPath = 'lib/class.tx_powermailcond_fieldsets.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'powermail_cond';	// The extension key.

	// Function PM_MainContentAfterHook() to manipulate content from powermail
	function PM_FormWrapMarkerHookInner(&$InnerMarkerArray, $conf, $obj) {
		// config
		$this->InnerMarkerArray = &$InnerMarkerArray;
		
		// Let's go
		// Target Fieldset
		$this->InnerMarkerArray['###POWERMAIL_JS###'] = ''; // clear marker (maybe it is already filled)
		if ($this->activateConditionsTarget($InnerMarkerArray['###POWERMAIL_FIELDSET_UID###'])) { // Check if there are conditions on a target fieldset to make some changes (disable, etc...)
			$this->manipulateFieldsetTarget($InnerMarkerArray['###POWERMAIL_FIELDSET_UID###']); // start function to manipulate fieldsets (hide, unhide: <fieldset /> => <fieldst style="display: none;" />)
		}
	}
	
	
	// Function manipulateFieldsetTarget() to add styles or other html code to prepare target fieldsets
	function manipulateFieldsetTarget($uid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_rules.actions',
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermailcond_conditions.pid = ' . $GLOBALS['TSFE']->id . ' AND tx_powermailcond_rules.fieldsetname = ' . $uid . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules'),
			$groupBy = 'tx_powermailcond_rules.uid',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		
		if ($row['actions'] == 0) { // fieldset should be hide - so show before
			$this->InnerMarkerArray['###POWERMAIL_JS###'] .= ' style="display: block;"'; // add style to fieldset
		}
		elseif ($row['actions'] == 1) { // fieldset should be unhide - so hide before
			$this->InnerMarkerArray['###POWERMAIL_JS###'] .= ' style="display: none;"'; //  add style to fieldset
		}
	}
	
	
	// Function activateConditionsTarget() search if the current fieldset is a target field (if than TRUE)
	function activateConditionsTarget($uid) {
		
		// check if current field has min. 1 condition
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_conditions.uid',
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermailcond_conditions.pid = ' . $GLOBALS['TSFE']->id . ' AND tx_powermailcond_rules.fieldsetname = ' . $uid . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules'),
			$groupBy = 'tx_powermailcond_conditions.uid',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if ($row['uid'] > 0) return true;
		else return false;
	
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_fieldsets.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_fieldsets.php']);
}
?>