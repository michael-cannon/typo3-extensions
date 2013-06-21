<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Michael Cannon <michael@peimic.com>
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




/**
 * Class that implements the model for table tx_cbgaedms_static_country_zones.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_static_country_zones extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_static_country_zones($controller = null, $parameter = null) {
			parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {

			// fix settings
			$fields = '*';
			$tables = 'tx_cbgaedms_static_country_zones';
			$groupBy = null;
			$orderBy = null;
			$where = 'hidden = 0 AND deleted = 0 ';

			// variable settings
			if($parameters) {
				// do query modifications according to incoming parameters here.
			}

			// query
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
			if($result) {
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
							$entry = new tx_lib_object($row);
							$this->append($entry);
					}
			}
	}

	function loadStatesAsOptions($parameters = null) {
		$fields = 'uid optionvalue
			, zn_name_local optionname
		';
		$tables = 'static_country_zones';
		$groupBy = null;
		$orderBy = 'zn_name_local ASC';
		$where = '';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND uid != ' . $exclude;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_static_country_zones.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_static_country_zones.php']);
}

?>