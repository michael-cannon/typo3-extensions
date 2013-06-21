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
 * Class that implements the model for table tx_cbgaedms_silo.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_silo extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_silo($controller = null, $parameter = null) {
			parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {

			// fix settings
			$fields = '*';
			$tables = 'tx_cbgaedms_silo';
			$groupBy = null;
			$orderBy = null;
			$where = 'hidden = 0 AND deleted = 0 ';
			$where .= ' AND agency = ' . $this->controller->configurations->get('masterAgency');

			// variable settings
			if($parameters) {
				// do query modifications according to incoming parameters here.
				if ( $uid = $parameters->get('uid') )
					$where .= ' AND uid = ' . $uid;
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

	function loadSilosAsOptions($parameters = null) {
		$fields = 'uid optionvalue
			, silo optionname
		';
		$tables = 'tx_cbgaedms_silo';
		$groupBy = null;
		$orderBy = 'silo ASC';
		$where = 'hidden = 0 AND deleted = 0 ';
		$where .= ' AND agency = ' . $this->controller->configurations->get('masterAgency');

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

	function preparedParameters ( $insert = false ) {
		$parameters = $this->controller->parameters;
		$parameterArray = array();
		foreach($parameters->selectHashArray('silo, description, hidden') as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		$parameterArray['hidden'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		$parameterArray['tstamp'] = time();

		if ( $insert ) {
			$parameterArray['crdate'] = time();
			$parameterArray['pid'] = $this->controller->configurations['storagePid'];
			$parameterArray['agency'] = $this->controller->configurations['masterAgency'];
		}

		return $parameterArray;
	}

	function update() {
		$parameters = $this->controller->parameters;
		$updateArray = $this->preparedParameters();
		$where = 'uid = ' . $parameters->get('uid');
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_silo', $where, $updateArray);
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function insert() {
		$insertArray = $this->preparedParameters(true);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_silo', $insertArray);
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_silo.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_silo.php']);
}

?>