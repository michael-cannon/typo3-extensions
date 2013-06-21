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
* Class that implements the model for table tx_cbgaedms_docversion.
*
* @author	Michael Cannon <michael@peimic.com>
* @package	TYPO3
* @subpackage	tx_cbgaedms
*/

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_docversion extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_docversion($controller = null, $parameter = null) {
		parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {
		$fields = 'v.uid
			, v.tstamp
			, v.docversion
			, v.file
			, v.filename
			, v.description
			, CONCAT(f.first_name, " ", f.last_name) feuser
		';
		$tables = 'tx_cbgaedms_docversion v
			LEFT JOIN tx_cbgaedms_doc_version_mm dvmm ON v.uid = dvmm.uid_foreign
			LEFT JOIN tx_cbgaedms_doc d ON d.uid = dvmm.uid_local
			LEFT JOIN fe_users f ON v.feuser = f.uid
		';
		$groupBy = null;
		$orderBy = 'v.uid DESC';
		$groupBy = null;
		$where = 'v.hidden = 0 AND v.deleted = 0 ';
		$limit = null;

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $parameters->get('docview') && $uid = $parameters->get('uid') ) {
				$where .= ' AND d.uid = ' . $uid;
			} elseif ( $uid = $parameters->get('uid') ) {
				$where .= ' AND v.uid = ' . $uid;
			}
			if ( $limitC = $parameters->get('limit') )
				$limit = $limitC;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}

	function loadLatestDocDetails($parameters = null) {
		$fields = '
			a.uid
			, a.agency
			, d.uid docuid
			, d.doc
			, d.description
			, t.doctype
			, v.uid versionuid
			, v.docversion
			, v.tstamp
			, v.description versiondescription
			, v.filename
			, v.feuser
		';
		$tables = '
			tx_cbgaedms_agency a
			LEFT JOIN tx_cbgaedms_agency_documents_mm admm ON a.uid = admm.uid_local
			LEFT JOIN tx_cbgaedms_doc d ON d.uid = admm.uid_foreign
			LEFT JOIN tx_cbgaedms_doc_version_mm dvmm ON d.uid = dvmm.uid_local
			LEFT JOIN tx_cbgaedms_docversion v ON v.uid = dvmm.uid_foreign
			LEFT JOIN tx_cbgaedms_doctype t ON d.doctype = t.uid
		';
		$groupBy = null;
		$orderBy = 'v.uid DESC';
		$groupBy = null;
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= 'AND d.hidden = 0 AND d.deleted = 0 ';
		$where .= 'AND v.hidden = 0 AND v.deleted = 0 ';
		$limit = null;

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $parameters->get('docview') && $uid = $parameters->get('uid') ) {
				$where .= ' AND d.uid = ' . $uid;
			} elseif ( $uid = $parameters->get('uid') ) {
				$where .= ' AND v.uid = ' . $uid;
			}
			if ( $limitC = $parameters->get('limit') )
				$limit = $limitC;
			if ( $period = $parameters->get('period') )
				$where .= ' AND v.tstamp >= ' . strtotime('-1 ' . $period);
			if ( $agencyStr = $parameters->get('agencyStr') )
				$where .= ' AND a.agency LIKE "%' . $agencyStr .'%"';
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_docversion.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_docversion.php']);
}

?>