<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Mischa Heissmann <typo3.YYYY@heissmann.org>
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


class tx_powermail_cond_confirmation extends tslib_pibase {

	var $prefixId = 'tx_powermailcond_pi1'; // Prefix
	var $scriptRelPath = 'lib/class.tx_powermailcond_confirmation.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'powermail_cond';	// The extension key.
	var $eventHandler = 'onchange'; // eventhandler
	var $JSfunctionname = 'pmcond_main'; // function name of JavaScript
	
	
	// Function PM_FieldWrapMarkerHookInner() for manipulation of inner parts of fields (for checkboxes, etc...)
	function PM_MarkerArrayHook($what, $geoArray, &$markerArray, &$sessiondata, $tmpl, $obj) {
		// config
		$this->markerArray = &$markerArray;
		$this->sessiondata = &$sessiondata;
		
		// first we search for the "nosend"-Flag in the markerArray.
		foreach ($this->markerArray as $k => $v) {
			// if result
			if (preg_match('/nosend/', $k)) {
				// unset all markers with this key and their belongings
				unset($this->markerArray[$k]);
				$id = str_replace('_nosend', '', $k);
				unset($this->markerArray[$id]);
				$nmid = str_replace('###', '', $id);
				unset($this->markerArray['###LABEL_' . $nmid . '###']);
			}
		}
		// and now remove from session.
		foreach ($this->sessiondata as $k => $v) {
			if (is_array($v) && array_key_exists('nosend', (array) $v)) {
				unset($this->sessiondata[$k]);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_confirmation.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_confirmation.php']);
}
?>