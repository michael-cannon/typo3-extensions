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
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_vcard.php'); // load vcard class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_div.php'); // load div class

class tx_wtdirectory_pi1_vcard extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId      = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_vcard.php';	// Path to this script relative to the extension dir.
	
	function main($conf, $piVars) {
        
		// config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->piVars = $piVars; // make it global
		$this->conf = $conf; // make it global
		$this->pi_loadLL();
		$this->vcard = t3lib_div::makeInstance('tx_wtdirectory_pi1_vcardlib'); // Create new instance for vcard class
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for vcard class

		if ($this->conf['vCard.']['enable'] == 1) { // only if enabled via ts
			
			// Give me all datas of tt_address
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'*',
				'tt_address',
				$where_clause = 'tt_address.uid = '.$this->piVars['vCard'].$this->cObj->enableFields('tt_address'),
				$groupBy = '',
				$orderBy = '',
				$limit = 1
			);
			if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
			
			if ($row['uid'] > 0) { // address found
				
				if (isset($this->conf['vCard.']) && is_array($this->conf['vCard.'])) { // if set via ts
					foreach ($this->conf['vCard.'] as $key => $value) {
						if (isset($this->conf['vCard.'][$key])) $this->vcard->data[$key] = $this->div->marker2value($value, $row); //set value
					}
					
					$this->vcard->download(); // open download for vcard
				}
			
			} else { // no address to uid found
				$this->content = $this->extKey.'vCard error';
			}
		}
		
		if (!empty($this->content)) return $this->content;
		
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_vcard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_vcard.php']);
}

?>