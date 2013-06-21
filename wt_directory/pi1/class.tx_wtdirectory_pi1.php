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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory').'pi1/class.tx_wtdirectory_pi1_list.php'); // load listview class
require_once(t3lib_extMgm::extPath('wt_directory').'pi1/class.tx_wtdirectory_pi1_detail.php'); // load detailview class
require_once(t3lib_extMgm::extPath('wt_directory').'pi1/class.tx_wtdirectory_pi1_vcard.php'); // load vcard class


/**
 * Plugin 'wt_directory (tt_address list and detail view)' for the 'wt_directory' extension.
 *
 * @author	Alexander Kellner <alexander.kellner@einpraegsam.net>
 * @package	TYPO3
 * @subpackage	tx_wtdirectory
 */
class tx_wtdirectory_pi1 extends tslib_pibase {

	var $extKey        = 'wt_directory';	// The extension key.
	var $prefixId      = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to this script relative to the extension dir.
	
	function main($content,$conf) {
		// Config
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->pi_USER_INT_obj = 1;	// USER_INT
		$this->conf = array_merge($this->conf,$this->cObj->data['pi_flexform']); // add flexform arry to conf array
		
		// Instances and security function
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->piVars = $this->div->sec($this->piVars); // Security options for piVars

		// Main part
		if (empty($this->piVars['vCard'])) { // no vcard export
			switch (empty($this->piVars['show'])) { // piVar show not set?
				case 1: // Not set: list view
					$this->listView = t3lib_div::makeInstance('tx_wtdirectory_pi1_list'); // Create new instance for list class
					$this->content = $this->listView->main($this->conf, $this->piVars); // List view
					break;
				
				default: // piVars set: detail view
					$this->detailView = t3lib_div::makeInstance('tx_wtdirectory_pi1_detail'); // Create new instance for detail class
					$this->content = $this->detailView->main($this->conf, $this->piVars); // Detail view
					break;
			}
			
		} else { // vcard export
			$this->vCard = t3lib_div::makeInstance('tx_wtdirectory_pi1_vcard'); // Create new instance for vCard class
			$this->content = $this->vCard->main($this->conf, $this->piVars); // vCard Download
		}
	
		if (!empty($this->content)) return $this->pi_wrapInBaseClass($this->content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php']);
}

?>