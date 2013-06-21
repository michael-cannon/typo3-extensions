<?php

/***************************************************************
*  Copyright notice
*  
*  (c) 2009 Michael Cannon <michael@peimic.com>
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
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Modify tt_news register for META data
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_aqnewsmeta.php,v 1.1.1.1 2010/04/15 10:03:05 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

class tx_aqnewsmeta extends tslib_pibase {
	var $extKey					= 'aqnewsmeta';
	var $conf					= array();

	var $newsObject				= null;
	var $lConf					= null;
	var $row					= null;

	function main ( $parentObject ) {
		$this->newsObject		= $parentObject;
		// grab aqnewsmeta conf
		$this->conf				= $this->newsObject->conf[ 'aqnewsmeta.' ];
	}
	
	function extraItemMarkerProcessor( $markerArray, $row, $lConf , $parentObject) {
		$this->main( $parentObject );
		$this->row				= $row;
		$this->lConf			= $lConf;
		$this->modifyRegisters();

		return $markerArray;
	}

	function modifyRegisters() {
		// only fire off if in SINGLE mode
		if (!$this->newsObject->piVars[$this->newsObject->config['singleViewPointerName']]
			&& $this->newsObject->config['code'] == 'SINGLE'
			&& true // display displaySingle render only
			) {
			$this->modifyRegisterSubheader();
		}
	}

	function modifyRegisterSubheader() {
		$crop = $this->conf['crop']
			? $this->conf['crop']
			: '150||1';

		$lConf	= array(
			'crop' => $crop
			, 'stripHtml' => 1
			, 'trim' => 1
		);

		$this->newsObject->local_cObj->LOAD_REGISTER(array(
			'newsSubheader' => $this->row['short']
				? $this->row['short']
				: $this->newsObject->local_cObj->stdWrap($this->row['bodytext'], $lConf)
		), '');
	}
}

if ( defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aqnewsmeta/class.tx_aqnewsmeta.php']
) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aqnewsmeta/class.tx_aqnewsmeta.php']);
}

?>
