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

class wtdirectory_markers extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_detail.php';	// Path to any script in pi1 for locallang
	
	
	// Function makeMarkers() makes markers from row (uid => ###WTDIRECTORY_UID###)
	// $what should contains 'detail' or 'list' to load the right html template
	// $conf contains TYPO3 conf array
	// $row contains db values as an array
	// $allowedArray contains allowed fields (from flexform)
	function makeMarkers($what = '', $conf = array(), $row = array(), $allowedArray = array(), $piVars = array() ) {
	
		// config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->pi_initPIflexForm();
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->tmpl = array(); $subpartArray = array(); $markerArray = array(); $markerArrayAll = array(); $wrappedSubpartArray = array(); // init
        $this->tmpl['all']['all'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['ALLmarker']),'###WTDIRECTORY_ALL_'.strtoupper($what).'###'); // Load HTML Template: ALL (works on subpart ###WTDIRECTORY_ALL###)
		$this->tmpl['all']['item'] = $this->cObj->getSubpart($this->tmpl['all']['all'],"###ITEM###"); // Load HTML Template: ALL (works on subpart ###ITEM###)
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		
		// 1. Fill marker "all": ###WTDIRECTORY_SPECIAL_ALL###
		if(!empty($allowedArray)) { // If some fields where added to show in backend
			foreach ($allowedArray as $key => $value) { // one loop for every db field
				if(($row[$value] && $this->conf['enable.']['hideDescription'] == 1) || $this->conf['enable.']['hideDescription'] == 0) { // Only if not empty when hide description activated
					$markerArrayAll['###WTDIRECTORY_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_'.$value, ucfirst($value)); // label from locallang or take key if locallang empty
					$markerArrayAll['###WTDIRECTORY_KEY###'] = $this->div->clearName($row[$value]); // Add key for CSS
					
					// value2marker
					$this->conf[$what.'.']['field.'][$value.'.']['value'] = ($this->conf['enable.']['autoChange'] ? $this->div->linker($row[$value]) : $row[$value]); // set value
					$this->conf[$what.'.']['field.'][$value.'.']['file'] = $this->conf['path.']['ttaddress_pictures'].$row[$value]; // set value
					if ($this->cObj->cObjGetSingle($this->conf[$what.'.']['field.'][$value], $this->conf[$what.'.']['field.'][$value.'.'])) { // if ts for current field available
						$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->cObj->cObjGetSingle($this->conf[$what.'.']['field.'][$value], $this->conf[$what.'.']['field.'][$value.'.']); // value
					} else { // no ts for current field
						$markerArrayAll['###WTDIRECTORY_VALUE###'] = ($this->conf['enable.']['autoChange'] ? $this->div->linker($row[$value]) : $row[$value]); // value
					}
					// MLC 20080520 dining listings
					$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->div->relationValues( $conf, $row, $value);
					
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['item'], $markerArrayAll); // Add
				}
			}
			$subpartArray['###CONTENT###'] = $content_item; // ###WTDIRECTORY_SPECIAL_ALL###
			
			// Global markers
			$OuterMarkerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
			$OuterMarkerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label'); // "more..."
			$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id), "useCacheHash" => 1) ); // Link to same page without GET params (Listview)
			$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'list') ? $this->pi_getFFvalue($this->conf, 'target', 'list') : $GLOBALS["TSFE"]->id), 'additionalParams' => '&'.$this->prefixId.'[show]='.$row['ttaddress_uid'] . ($this->conf['enable.']['googlemapOnDetail'] == 1 && t3lib_extMgm::isLoaded('rggooglemap',0) ? '&tx_rggooglemap_pi1[poi]='.$row['ttaddress_uid'] : ''), "useCacheHash" => 1) ); // Link to same page with uid (Singleview)
			if (($this->conf['enable.']['vCardForList'] == 1 && $what == 'list') || ($this->conf['enable.']['vCardForDetail'] == 1 && $what == 'detail')) { // only if vcard enabled in constants
				$OuterMarkerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
				$wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, 'additionalParams' => '&'.$this->prefixId.'[vCard]='.($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with ?tx_wtdirectory_pi1[vCard]=uid
			}
			if ($this->pi_getFFvalue($this->conf, 'enable', 'googlemap') == 1 && t3lib_extMgm::isLoaded('rggooglemap',0)) { // only if googlemap enabled in flexform && rggooglemap is installed
				$OuterMarkerArray['###WTDIRECTORY_GOOGLEMAP_LABEL###'] = $this->pi_getLL('wtdirectory_googlemaplink_label', 'Show in map'); // "Show in map"
				$wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap ( array ( "parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => $this->div->addFilterParams($this->piVars) . ($this->piVars['show'] ? '&'.$this->prefixId.'[show]='.$this->piVars['show'] : '') . '&tx_rggooglemap_pi1[poi]='.($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1 ) );  // Link to target page with tt_address uid for googlmaps
			}
			
			$markerArray['###WTDIRECTORY_SPECIAL_ALL###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['all'], $OuterMarkerArray, $subpartArray, $wrappedSubpartArray); // Fill ###WTDIRECTORY_SPECIAL_ALL###
		
		} else { // No fields to show where added, so show all
			if(!empty($row)) {
				foreach ($row as $key => $value) { // one loop for every db field
					if(($value && $this->conf['enable.']['hideDescription'] == 1) || $this->conf['enable.']['hideDescription'] == 0) { // Only if not empty when hide description activated
						$markerArrayAll['###WTDIRECTORY_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_'.$key, ucfirst($key)); // label from locallang or take key if locallang empty
						$markerArrayAll['###WTDIRECTORY_KEY###'] = $this->div->clearName($value); // Add key for CSS
						
						// value2marker
						$this->conf[$what.'.']['field.'][$value.'.']['value'] = ($this->conf['enable.']['autoChange'] ? $this->div->linker($value) : $value); // value
						$this->conf[$what.'.']['field.'][$value.'.']['file'] = $this->conf['path.']['ttaddress_pictures'].$value; // value
						if ($this->cObj->cObjGetSingle($this->conf[$what.'.']['field.'][$value], $this->conf[$what.'.']['field.'][$value.'.'])) { // if ts for current field available
							$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->cObj->cObjGetSingle($this->conf[$what.'.']['field.'][$value], $this->conf[$what.'.']['field.'][$value.'.']); // value
						} else { // no ts for current field
							$markerArrayAll['###WTDIRECTORY_VALUE###'] = ($this->conf['enable.']['autoChange'] ? $this->div->linker($value) : $value); // value
						}
						// MLC 20080520 dining listings
						$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->div->relationValues( $conf, $row, $value);
						
						$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['item'], $markerArrayAll); // Add
					}
				}
				$subpartArray['###CONTENT###'] = $content_item; // ###WTDIRECTORY_SPECIAL_ALL###
			
				// Global markers outer
				$OuterMarkerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
				$OuterMarkerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label'); // "more..."
				$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id), "useCacheHash" => 1) ); // Link to same page without GET params (Listview)
				$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'list') ? $this->pi_getFFvalue($this->conf, 'target', 'list') : $GLOBALS["TSFE"]->id), 'additionalParams' => '&'.$this->prefixId.'[show]='.$row['ttaddress_uid'] . ($this->conf['enable.']['googlemapOnDetail'] == 1 && t3lib_extMgm::isLoaded('rggooglemap',0) ? '&tx_rggooglemap_pi1[poi]='.$row['ttaddress_uid'] : ''), "useCacheHash" => 1) ); // Link to same page with uid (Singleview)
				if (($this->conf['enable.']['vCardForList'] == 1 && $what == 'list') || ($this->conf['enable.']['vCardForDetail'] == 1 && $what == 'detail')) { // only if vcard enabled in constants
					$OuterMarkerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
					$wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, 'additionalParams' => '&'.$this->prefixId.'[vCard]='.($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with ?tx_wtdirectory_pi1[vCard]=uid
				}
				if ($this->pi_getFFvalue($this->conf, 'enable', 'googlemap') == 1 && t3lib_extMgm::isLoaded('rggooglemap',0)) { // only if googlemap enabled in flexform && rggooglemap is installed
					$OuterMarkerArray['###WTDIRECTORY_GOOGLEMAP_LABEL###'] = $this->pi_getLL('wtdirectory_googlemaplink_label', 'Show in map'); // "Show in map"
					$wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap ( array ( "parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => $this->div->addFilterParams($this->piVars) . ($this->piVars['show'] ? '&'.$this->prefixId.'[show]='.$this->piVars['show'] : '') . '&tx_rggooglemap_pi1[poi]='.($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1 ) );  // Link to target page with tt_address uid for googlmaps
				}
				
				$markerArray['###WTDIRECTORY_SPECIAL_ALL###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['all'], $OuterMarkerArray, $subpartArray, $wrappedSubpartArray); // Fill ###WTDIRECTORY_SPECIAL_ALL###
			}
		}
		
		
		// 2. Fill individual marker
		if(!empty($row)) { // If row is set
			foreach ($row as $key => $value) { // one loop for every db field
				// MLC 20080520 dining listings
				$markerArray['###WTDIRECTORY_'.strtoupper($key).'###'] = $this->div->relationValues( $conf, $key, $value, $uid );

				if ( $this->conf[$what.'.']['field.'][$key] ) {
					$this->conf[$what.'.']['field.'][$key.'.']['value'] = ($this->conf['enable.']['autoChange'] ? $this->div->linker($row[$key]) : $row[$key]); // set value
					$this->conf[$what.'.']['field.'][$key.'.']['file'] = $this->conf['path.']['ttaddress_pictures'].$row[$key]; // set value
					$markerArray['###WTDIRECTORY_'.strtoupper($key).'###'] = $this->cObj->cObjGetSingle($this->conf[$what.'.']['field.'][$key], $this->conf[$what.'.']['field.'][$key.'.']); // value
				}
			}
		}
		
		// 3. Fill global markers
		$markerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
		$markerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label'); // "more..."
		
		if(!empty($markerArray)) return $markerArray;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_markers.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_markers.php']);
}

?>
