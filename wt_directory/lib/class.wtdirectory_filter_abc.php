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
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions


class tx_wtdirectory_filter_abc extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'lib/class.wtdirectory_filter_abc.php';	// Path to this script relative to the extension dir.
	
	function main($conf, $piVars) {
		
		// Config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl = array(); $this->markerArray = array(); // init
		$this->tmpl['filter']['abc'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']),'###WTDIRECTORY_FILTER_ABC###'); // Load HTML Template
		$this->query_pid = ($this->pi_getFFvalue($this->conf, 'pid', 'mainconfig') > 0 ? ' AND tt_address.pid = '.$this->pi_getFFvalue($this->conf, 'pid', 'mainconfig') : ''); // where clause with pid
		$this->query_cat = ($this->pi_getFFvalue($this->conf, 'cat_join', 'mainconfig') > 0 ? ' AND tt_address_group.uid IN('.$this->pi_getFFvalue($this->conf, 'category', 'mainconfig').')' : ''); // where clause for tt_address_group
		
		// let's go
		if ($this->pi_getFFvalue($this->conf, 'abc', 'list') != '') { // if abc should be shown
			$this->markerArray['###WTDIRECTORY_ABC_ALL###'] = $this->show_all(); // Link all
			$this->markerArray['###WTDIRECTORY_ABC_ABC###'] = $this->show_abc(); // Link abc
			$this->markerArray['###WTDIRECTORY_ABC_0-9###'] = $this->show_numbers(); // Link numbers
			$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['abc'], $this->markerArray); // substitute Marker in Template
			$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
			$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers
		}
		
		if (!empty($this->content)) return $this->content;
		
    }
	
	
	// Function show_abc() to generate ABC list
	function show_abc() {
		$content = ''; // init
		
		for ($a=A; $a != AA; $a++) { // ABC loop
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'tt_address.uid uid',
				'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				$where_clause = 'tt_address.'.$this->pi_getFFvalue($this->conf, 'abc', 'list').' LIKE "'.$a.'%"'.$this->query_pid.$this->query_cat.$this->cObj->enableFields('tt_address'),
				$groupBy = 'tt_address.uid',
				$orderBy = '',
				$limit = '1'
			);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			// Generate Return string
			$content .= '<span class="wtdirectory_abc_letter">';
			if($row['uid']) { // If result (link with letter)
				$content .= $this->cObj->typolink($a, array('parameter'=>$GLOBALS['TSFE']->id, 'additionalParams'=>'&'.$this->prefixId.'[filter]['.$this->pi_getFFvalue($this->conf, 'abc', 'list').']='.htmlentities(strtolower($a).'%'), "useCacheHash" => 1));
			} else { // no result: letter only link
				$content .= $a; 
			} 
			$content .= '</span>'."\n";
			
		}
		if (!empty($content)) return $content;
	}
	
	
	// Function show_all() generates link same page without piVars
	function show_all() {
		$content = '<span class="fflocation_abc_letter_all">'.$this->cObj->typolink('All',array('parameter'=>$GLOBALS['TSFE']->id, "useCacheHash" => 1)).'</span>';
		
		if (!empty($content)) return $content;
	}
	
	
	// Function show_numbers() to generate numbers link
	function show_numbers() {
		$content = ''; $query_numbers = ''; // init
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			'tt_address.uid uid',
			'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
			$where_clause = 'tt_address.'.$this->pi_getFFvalue($this->conf, 'abc', 'list').' < "@%"'.$this->query_pid.$this->query_cat.$this->cObj->enableFields('tt_address'),
			$groupBy = 'tt_address.uid',
			$orderBy = '',
			$limit = '1'
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		// Generate Return string
		if($row['uid'] > 0) { // If result
			$content = $this->cObj->typolink("0-9", array('parameter'=>$GLOBALS['TSFE']->id, 'additionalParams'=>'&'.$this->prefixId.'[filter]['.$this->pi_getFFvalue($this->conf, 'abc', 'list').']='."@", "useCacheHash" => 1));
		} else { // if no result (no link)
			$content = "0-9\n"; 
		} 
			
		if (!empty($content)) return $content;
	}
	
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_abc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_abc.php']);
}

?>