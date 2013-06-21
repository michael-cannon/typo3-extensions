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

require_once(PATH_tslib.'class.tslib_pibase.php'); // include pibase
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_filter_search extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any pi1 script for locallang
	
	function main($conf, $piVars) {
		
		// Config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl = array(); $this->markerArray = array(); $content_item = ''; $this->outerArray = array(); $this->subpartArray = array(); $i=0; // init
		$this->tmpl['filter']['search'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']),'###WTDIRECTORY_FILTER_SEARCH###'); // Load HTML Template
		$this->tmpl['filter']['item'] = $this->cObj->getSubpart($this->tmpl['filter']['search'], '###ITEM###'); // work on subpart 1
		$this->searchfields = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'search', 'list'), 1); // take searchfieldlist as an array
		// MLC 20080520 dining listings
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		
		// Fill marker outside loop
		$this->outerArray['###WTDIRECTORY_SEARCH_SUBMITVALUE###'] = $this->pi_getLL('wtdirectory_search_submitbutton', 'go'); // value or submit button
		$this->outerArray['###WTDIRECTORY_SEARCH_TITLE###'] = $this->pi_getLL('wtdirectory_search_title', 'Search form'); // title
		$this->outerArray['###WTDIRECTORY_SEARCH_METHOD###'] = 'post'; // method
		$this->outerArray['###WTDIRECTORY_SEARCH_ACTION###'] = htmlentities($this->cObj->typolink('x', array("returnLast" => "url", "parameter" => $GLOBALS['TSFE']->id, "useCacheHash" => 1))); // target url for form
		
		// Fill markers within loop
		// MLC 20080520 dining listings
		$firstValue				= true;
		foreach ($this->searchfields as $value) { // one loop for every needed searchfield
			$this->markerArray['###WTDIRECTORY_SEARCH_TYPE###'] = 'text'; // only text fields
			$this->markerArray['###WTDIRECTORY_SEARCH_NAME###'] = $value; // Value of be field
			$this->markerArray['###WTDIRECTORY_SEARCH_VALUE###'] = ($this->piVars['filter'][$value] ? $this->piVars['filter'][$value] : ''); // method
			$this->markerArray['###WTDIRECTORY_SEARCH_VALUE###'] = $this->div->dropdownValues( $conf, $this->piVars['filter'], $value);
			// MLC 20080520 dining listings
			if ( $firstValue )
			{
				$this->markerArray['###WTDIRECTORY_SEARCH_VALUE###'] .= "&nbsp;&nbsp;<b>or</b>";
				$firstValue		= false;
			}
			$this->markerArray['###WTDIRECTORY_SEARCH_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_'.$value, ucfirst($value)); // Label for field
			$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray); // add all markers of this loop to the variable
			$i++; // increase counter
		}
		$this->subpartArray['###CONTENT###'] = $content_item; // work on subpart 2
		
		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['search'], $this->outerArray, $this->subpartArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers
		
		if (!empty($this->content) && $i) return $this->content;
		
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_search.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_search.php']);
}

?>