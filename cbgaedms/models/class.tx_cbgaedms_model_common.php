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
 * Common model class for extension cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

class tx_cbgaedms_model_common extends tx_lib_object {

	function tx_cbgaedms_model_common($controller = null, $parameter = null) {
		parent::tx_lib_object($controller, $parameter);
	}
	
	function loadModelEntry ( $modelName, $uid, $load = 'load' ) {
		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		if ( $uid ) {
			if ( ! is_array( $uid ) ) {
				$parameters->set('uid', $uid);
			} else {
				foreach ( $uid as $key => $value )
				{
					$parameters->set($key, $value);
				}
			}
		}
		$model->$load($parameters);
		$entry = new tx_cbgaedms_view_common($model->current(), $this->controller);
		return $entry;
	}

	function loadModelEntries ( $modelName, $uid, $load = 'load' )
	{
		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		if ( ! is_array( $uid ) ) {
			$parameters->set('uid', $uid);
		} else {
			foreach ( $uid as $key => $value )
			{
				$parameters->set($key, $value);
			}
		}
		$model->$load($parameters);
		$entry = new tx_cbgaedms_view_common($this->controller);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entries = new tx_cbgaedms_view_common($model->current(), $this->controller);
            $entry->append($entries);
        }
		return $entry;
	}

	function loadDocumentEntries ( $uid ) {
		$parameters = array( 'agencyId' => $uid );
		return $this->loadModelEntries( 'tx_cbgaedms_model_doc', $parameters, 'loadAgencyDocs' );
	}

	function loadDocument_VersionEntries ( $uid ) {
		$parameters = array( 'uid' => $uid, 'docview' => true );
		return $this->loadModelEntries( 'tx_cbgaedms_model_docversion', $parameters );
	}

	function loadDocument_VersionLatestEntry ( $uid ) {
		$parameters = array( 'uid' => $uid, 'docview' => true, 'limit' => 1 );
		return $this->loadModelEntry( 'tx_cbgaedms_model_docversion', $parameters );
	}

	function loadDocument_VersionEntry ( $uid ) {
		return $this->loadModelEntry( 'tx_cbgaedms_model_docversion', $uid );
	}

	function loadDocument_TypeEntry ( $uid ) {
		$parameters = array( 'uid' => $uid, 'doctype' => true, 'limit' => 1 );
		return $this->loadModelEntry( 'tx_cbgaedms_model_doctype', $parameters );
	}

	function loadFE_UserEntry ( $uid ) {
		return $this->loadModelEntry( 'tx_cbgaedms_model_fe_users', $uid );
	}

	function loadFE_UserEntries ( $uid ) {
		$parameters = array( 'uid' => $uid, 'entries' => true );
		return $this->loadModelEntries( 'tx_cbgaedms_model_fe_users', $parameters );
	}

	function loadAgencyEntries ( $uid ) {
		$parameters = array( 'uid' => $uid, 'fieldName' => 'userlocations' );
		return $this->loadModelEntries( 'tx_cbgaedms_model_agency', $parameters, 'loadAgenciesAsDualSelect' );
	}

	function setMasterAgencyAdmin() {
		$fields = 'a.administrator';
		$tables = 'tx_cbgaedms_agency a';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= ' AND a.uid = ' .  $this->controller->configurations->get('masterAgency');

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where);
		// cbDebug( 'query', $query );	cbDebug( 'File ' . __FILE__, 'Line ' .  __LINE__ );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where);
		if($result && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->controller->setMasterAgencyAdmin($row['administrator']);
		} else {
			$this->controller->setMasterAgencyAdmin(0);
		}
	}

	function getWhereAgencyAccess() {
		if ( $this->controller->adminOk() )
			return '';

		$user = $this->controller->getUserId();
		$where = " AND ( FIND_IN_SET($user, a.administrator)
			OR a.incidentmanager = $user
			OR FIND_IN_SET($user, a.alternateincidentmanagers)
			OR FIND_IN_SET($user, a.viewers))";
		
		return $where;
	}

	function getWhereMasterAgencyChildren() {
		$masterAgencyUid = $this->controller->configurations->get('masterAgency');
		$masterAgencyChildIds = $this->getMasterAgencyChildren($masterAgencyUid);
		$masterAgencyIn = ( $this->controller->adminOk() )
			? ',' . $masterAgencyUid
			: '';
		$where = ' AND a.uid IN ( ' . $masterAgencyChildIds . $masterAgencyIn . ' )';
		return $where;
	}

	function getMasterAgencyChildren($childlist, $cc = 0) {
		$parentAgencyArray = array();

		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'uid',
			'tx_cbgaedms_agency',
			'parentagency IN ('.$childlist.') AND hidden = 0 AND deleted = 0'
		);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$cc++;
			if ($cc > 1000) {
				$GLOBALS['TT']->setTSlogMessage('tx_cbgaedms_agency: one or more recursive agencies were found');
				return implode(',', $parentAgencyArray);
			}
			$subChildren = $this->getMasterAgencyChildren($row['uid'], $cc);
			$subChildren = $subChildren ? ','.$subChildren : '';
			$parentAgencyArray[] = $row['uid'].$subChildren;
		}
		$childlist = implode(',', $parentAgencyArray);
		return $childlist;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_common.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_common.php']);
}

?>
