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
 * Class that implements the model for table tx_cbgaedms_reports.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_reports extends tx_cbgaedms_model_common {
	function tx_cbgaedms_model_reports($controller = null, $parameter = null) {
			parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {
		$fields = '*, recipients newrecipientsleft';
		$tables = 'tx_cbgaedms_reports t';
		$groupBy = null;
		$orderBy = 'uid DESC';
		$limit = null;
		$where = 't.hidden = 0 AND t.deleted = 0 ';
		$where .= ' AND t.parentagency = ' . $this->controller->configurations->get('masterAgency');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			// don't allow for result
			if ( true === $parameters->get('reports') && ! $parameters->get('uid') )
				$where .= ' AND 1 = 0';
			elseif ( $uid = $parameters->get('uid') )
				$where .= ' AND t.uid = ' . $uid;
			if ( $limitCount = $parameters->get('limit') )
				$limit = $limitCount;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}

	function cronLoad($parameters = null) {
		$fields = 't.report, t.frequency, t.recipients, t.messagebody';
		$tables = 'tx_cbgaedms_reports t';
		$groupBy = null;
		$orderBy = 'uid DESC';
		$limit = null;
		$where = 't.hidden = 0 AND t.deleted = 0 ';
		$where .= ' AND t.parentagency = ' . $this->controller->configurations->get('masterAgency');
		$where .= ' AND t.reporton = 1';
		$where .= ' AND NOT MOD(DAYOFYEAR(NOW()), t.frequency)';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
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
		foreach($parameters->selectHashArray('report, frequency, recipients, messagebody, reporton, hidden') as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		$parameterArray['recipients'] = ( $parameterArray['recipients'] ) ?  $parameterArray['recipients'] : $parameters->get('newrecipientsleft');
		$parameterArray['reporton'] = ('' == $parameterArray['reporton'])
									? 0
									: 1;
		$parameterArray['hidden'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		$parameterArray['tstamp'] = time();

		if ( $insert ) {
			$parameterArray['crdate'] = time();
			$parameterArray['pid'] = $this->controller->configurations['storagePid'];
			$parameterArray['parentagency'] = $this->controller->configurations['masterAgency'];
		}

		return $parameterArray;
	}

	function update() {
		$parameters = $this->controller->parameters;
		$updateArray = $this->preparedParameters();
		$where = 'uid = ' . $parameters->get('uid');
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_reports', $where, $updateArray);
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function insert() {
		$insertArray = $this->preparedParameters(true);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_reports', $insertArray);
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_reports.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_reports.php']);
}

?>