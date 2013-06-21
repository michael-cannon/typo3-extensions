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
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_markers.php'); // load markers class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_vcard.php'); // load vcard class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_pi1_detail extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_detail.php';	// Path to this script relative to the extension dir.
	
	function main($conf, $piVars) {
        
		// config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->piVars = $piVars; // make it global
		$this->conf = $conf; // make it global
		$this->pi_loadLL();
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->markers = t3lib_div::makeInstance('wtdirectory_markers'); // Create new instance for div class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl = array(); // init
		$this->tmpl['detail'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['detail']), '###WTDIRECTORY_DETAIL###'); // Load HTML Template
		
		if ($this->piVars['show'] > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'*',
				'tt_address',
				$where_clause = 'tt_address.uid = '.$this->piVars['show'].$this->cObj->enableFields('tt_address'),
				$groupBy = 'tt_address.uid',
				$orderBy = '',
				$limit = 1
			);
			if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
			
			if ($row['uid'] > 0) { // address found
				
				if ($this->conf['detail.']['title']) { // Page title
					$GLOBALS['TSFE']->page['title'] = $this->div->marker2value($this->conf['detail.']['title'], $row); // set pagetitle
					$GLOBALS['TSFE']->indexedDocTitle = $this->div->marker2value($this->conf['detail.']['title'], $row); // set pagetitle for indexed search
				}
				
				// Markers
				$this->markerArray = $this->markers->makeMarkers('detail', $this->conf, $row, t3lib_div::trimExplode(',',$this->pi_getFFvalue($this->conf, 'field', 'detail'),1), $this->piVars); // get markerArray
				$this->wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, 'additionalParams' => '&'.$this->prefixId.'[vCard]='.$row['uid'], "useCacheHash" => 1) ); // Link to same page with uid for vCard
				$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id), "useCacheHash" => 1) ); // Link to same page without GET params (Listview)
				if (t3lib_extMgm::isLoaded('rggooglemap',0)) $this->wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => ($this->piVars['show'] ? '&'.$this->prefixId.'[show]='.$this->piVars['show'] : '') . '&tx_rggooglemap_pi1[poi]='.$row['uid'], "useCacheHash" => 1) ); // Link to target page with tt_address uid for googlmaps
				
				$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['detail'],$this->markerArray,array(),$this->wrappedSubpartArray); // substitute Marker in Template
				$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
				$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers
			
			} else { // no address to uid found
				$this->content = $this->pi_getLL('wtdirectory_error_nodetail');
			}
			
		}
		
		if (!empty($this->content)) return $this->content;
		
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_detail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_detail.php']);
}

?>
