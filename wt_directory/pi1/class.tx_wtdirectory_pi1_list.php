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
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_filter_abc.php'); // load abc filter class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_filter_search.php'); // load search filter class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_filter_cat.php'); // load category filter class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_pagebrowser.php'); // load pagebrowser class
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_pi1_list extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_list.php';	// Path to this script relative to the extension dir.
	
	function main($conf, $piVars) {
		// Config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->tmpl = array(); $this->wrappedSubpartArray = array(); $this->content = ''; $result = 0; $this->query = array(); // init
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->markers = t3lib_div::makeInstance('wtdirectory_markers'); // Create new instance for div class
		$this->filter_abc = t3lib_div::makeInstance('tx_wtdirectory_filter_abc'); // Create new instance for abcfilter class
		$this->filter_search = t3lib_div::makeInstance('tx_wtdirectory_filter_search'); // Create new instance for searchfilter class
		$this->filter_cat = t3lib_div::makeInstance('tx_wtdirectory_filter_cat'); // Create new instance for catfilter class
		$this->pagebrowser = t3lib_div::makeInstance('tx_wtdirectory_pagebrowser'); // Create new instance for pagebrowser class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl['list']['all'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['list']),'###WTDIRECTORY_LIST###'); // Load HTML Template
		$this->tmpl['list']['item'] = $this->cObj->getSubpart($this->tmpl['list']['all'],'###ITEM###'); // work on subpart 2
		
		// Define WHERE clause for db query
		$this->query_pid = ($this->pi_getFFvalue($this->conf, 'pid', 'mainconfig') > 0 ? ' AND tt_address.pid = '.$this->pi_getFFvalue($this->conf, 'pid', 'mainconfig') : ''); // where clause with pid
		$this->query_cat = ($this->pi_getFFvalue($this->conf, 'cat_join', 'mainconfig') > 0 ? ' AND tt_address_group.uid IN('.$this->pi_getFFvalue($this->conf, 'category', 'mainconfig').')' : ''); // where clause for tt_address_group
		$this->setFilter();
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			$this->query['select'] = '*, tt_address.uid ttaddress_uid',
			// MLC 20080520 dining listings
			$this->query['from'] = 'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)
			LEFT JOIN tt_address_tx_cbdiningguide_cuisine_mm on (tt_address.uid = tt_address_tx_cbdiningguide_cuisine_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_specialty_mm on (tt_address.uid = tt_address_tx_cbdiningguide_specialty_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_meals_mm on (tt_address.uid = tt_address_tx_cbdiningguide_meals_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_neighborhood_mm on (tt_address.uid = tt_address_tx_cbdiningguide_neighborhood_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_price_mm on (tt_address.uid = tt_address_tx_cbdiningguide_price_mm.uid_local)',
			$this->query['where'] = $this->filter.$this->query_pid.$this->query_cat.$this->cObj->enableFields('tt_address'),
			$this->query['groupby'] = 'tt_address.uid',
			$this->query['orderby'] = addslashes($this->conf['list.']['orderby']),
			$this->query['limit'] = ($this->piVars['pointer'] > 0 ? $this->piVars['pointer'] : 0).','.$this->conf['list.']['perPage']
		);
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res); // numbers of all entries
		if ($res) { // If there is a result
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every tt_address entry
				$this->InnerMarkerArray = $this->markers->makeMarkers('list', $this->conf, $row, t3lib_div::trimExplode(',',$this->pi_getFFvalue($this->conf, 'field', 'list'),1), $this->piVars); // get markerArray
				$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'list') ? $this->pi_getFFvalue($this->conf, 'target', 'list') : $GLOBALS["TSFE"]->id), "additionalParams" => '&'.$this->prefixId.'[show]='.$row['ttaddress_uid'], "useCacheHash" => 1) ); // Link to same page without GET params (Listview)
				$this->wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, "additionalParams" => '&'.$this->prefixId.'[vCard]='.$row['ttaddress_uid'], "useCacheHash" => 1) ); // Link to same page without GET params (vCard)
				if (t3lib_extMgm::isLoaded('rggooglemap',0)) $this->wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => $this->div->addFilterParams($this->piVars).'&tx_rggooglemap_pi1[poi]='.$row['ttaddress_uid'], "useCacheHash" => 1) ); // Link to target page with tt_address uid for googlmaps
				
				$this->content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['list']['item'], $this->InnerMarkerArray, array(), $this->wrappedSubpartArray);
				$result = 1; // min 1 result
			}
		}
		$this->subpartArray = array('###CONTENT###' => $this->content_item); // work on subpart 3
		$this->OuterSubpartArray = $this->markers->makeMarkers('list', $this->conf, $row, t3lib_div::trimExplode(',',$this->pi_getFFvalue($this->conf, 'field', 'list'),1), $this->piVars); // get markerArray
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_ABC###'] = $this->filter_abc->main($this->conf, $this->piVars); // include ABC filter
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_SEARCH###'] = $this->filter_search->main($this->conf, $this->piVars); // include SEARCH filter
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_CAT###'] = $this->filter_cat->main($this->conf, $this->piVars); // include CAT filter
		$this->OuterSubpartArray['###WTDIRECTORY_PAGEBROWSER###'] = $this->pagebrowser->main( $this->conf, $this->piVars, $this->cObj, array('overall' => $this->overall(), 'overall_cur' => $num, 'pointer' => ($this->piVars['pointer'] > 0 ? $this->piVars['pointer'] : 0), 'perPage' => $this->conf['list.']['perPage']) );
		if (!$result) $this->OuterSubpartArray['###WTDIRECTORY_FILTER_NORESULTS###'] = '<span class="wtdirectory_noaddresses">'.$this->pi_getLL('wtdirectory_error_nolist').'</span>'; // no result message
			
		$this->content .= $this->cObj->substituteMarkerArrayCached($this->tmpl['list']['all'], $this->OuterSubpartArray, $this->subpartArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace("|###.*?###|i","",$this->content); // Finally clear not filled markers
		
		return $this->content; // return HTML
		
    }
	
	
	// Function overall() gives the number of all addresses
	function overall() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			$this->query['select'] = '*',
			// MLC 20080520 dining listings
			$this->query['from'] = 'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)
			LEFT JOIN tt_address_tx_cbdiningguide_cuisine_mm on (tt_address.uid = tt_address_tx_cbdiningguide_cuisine_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_specialty_mm on (tt_address.uid = tt_address_tx_cbdiningguide_specialty_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_meals_mm on (tt_address.uid = tt_address_tx_cbdiningguide_meals_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_neighborhood_mm on (tt_address.uid = tt_address_tx_cbdiningguide_neighborhood_mm.uid_local)
			LEFT JOIN tt_address_tx_cbdiningguide_price_mm on (tt_address.uid = tt_address_tx_cbdiningguide_price_mm.uid_local)',
			$this->query['where'] = $this->filter.$this->query_pid.$this->query_cat.$this->cObj->enableFields('tt_address'),
			$this->query['groupby'] = 'tt_address.uid',
			$this->query['orderby'] = '',
			$this->query['limit'] = ''
		);
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res); // numbers of all entries
		
		if (!empty($num)) return $num;
	}
	
	
	// Set filter for query if set per piVars
	function setFilter() {
		$this->filter = ''; $set = 0; // init
		
		// let's go
		if (empty($this->piVars['filter'])) { // no filter set
			if (isset($_REQUEST['tx_wtdirectory_pi1']['filter'])) {
				$this->piVars['filter'] = $_REQUEST['tx_wtdirectory_pi1']['filter'];
			}
		}

		// filter set
		if (is_array($this->piVars['filter'])) { // if is array
			foreach ($this->piVars['filter'] as $key => $value) { // one loop for every filter
				if (!empty($value)) {
					// MLC 20080520 dining listings
					if ( in_array( $key, $this->div->cbDining ) ) {
						$this->filter .= 'tt_address_'.$key.'_mm.uid_foreign= "'.$value.'" AND '; // add this filter to query
						$set = 1; // min 1 filter was set
					} elseif ($value == '@') { // 0-9
						$this->filter .= 'tt_address.'.$key.' < "@%" AND '; // add this filter to query
						$set = 1; // min 1 filter was set
					} elseif ($value == str_replace('%', '', $value)) { // without % like a word or a name
						$this->filter .= 'tt_address.'.$key.' LIKE "%'.$value.'%" AND '; // add this filter to query
						$set = 1; // min 1 filter was set
					} else { // value like a% or e%
						$this->filter .= 'tt_address.'.$key.' LIKE "'.$value.'" AND '; // add this filter to query
						$set = 1; // min 1 filter was set
					} 
				}
			}
		}
		if ($set == 0) $this->filter = '1 = 1'; // default value (WHERE 1=1)
		else $this->filter = substr(trim($this->filter), 0, -4); // delete last " AND " of whole query
		
		if (!empty($this->piVars['catfilter'])) $this->filter .= ' AND tt_address_group.uid = '.$this->piVars['catfilter']; // if catfilter set, add where clause
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_list.php']);
}

?>