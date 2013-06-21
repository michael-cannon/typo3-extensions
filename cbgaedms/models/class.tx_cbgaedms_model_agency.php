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
 * Class that implements the model for table tx_cbgaedms_agency.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_agency extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_agency($controller = null, $parameter = null) {
		parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {
		$fields = '*';
		$tables = 'tx_cbgaedms_agency a';
		$groupBy = null;
		$orderBy = null;
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND a.uid = ' . $uid;
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

	function loadList($parameters = null) {
		$fields = 'a.uid
			, a.agency
			, p.agency parent
			, a.parentagency
			, s.silo
			, a.agencysilo
			, c.cn_short_en
			, a.country
			, z.zn_name_local
			, a.address
			, a.state
			, a.city
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN tx_cbgaedms_agency p ON a.parentagency = p.uid
			LEFT JOIN tx_cbgaedms_silo s ON a.agencysilo = s.uid
			LEFT JOIN static_countries c ON a.country = c.uid
			LEFT JOIN static_country_zones z ON a.state = z.uid
		';
		$groupBy = null;
		$orderBy = 'a.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();
		$limit = (integer) $this->controller->parameters->get('offset');
		$limit .= ', ' . (integer) $this->controller->configurations->get('resultsPerView');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND a.uid = ' . $uid;
			elseif ( $agencyStr = $parameters->get('agencyStr') )
				$where .= ' AND a.agency LIKE "%' . $agencyStr . '%"';
			elseif ( $agency = $parameters->get('agency') )
				$where .= ' AND a.uid =' . $agency;
			if ( $parentagency = $parameters->get('parentagency') )
				$where .= ' AND a.parentagency = ' . $parentagency;
			if ( $cityStr = $parameters->get('cityStr') )
				$where .= ' AND a.city LIKE "%' . $cityStr . '%"';
			elseif ( $city = $parameters->get('city') )
				$where .= ' AND a.city LIKE "%' . $city . '%"';
			if ( $stateStr = $parameters->get('stateStr') )
				$where .= ' AND z.zn_name_local LIKE "%' . $stateStr . '%"';
			elseif ( $state = $parameters->get('state') )
				$where .= ' AND a.state = ' . $state;
			if ( $countryStr = $parameters->get('countryStr') )
				$where .= ' AND c.cn_short_en LIKE "%' . $countryStr . '%"';
			elseif ( $country = $parameters->get('country') )
				$where .= ' AND a.country = ' . $country;
			if ( $agencysiloStr = $parameters->get('agencysiloStr') )
				$where .= ' AND s.silo LIKE "%' . $agencysiloStr . '%"';
			elseif ( $agencysilo = $parameters->get('agencysilo') )
				$where .= ' AND a.agencysilo = ' . $agencysilo;
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

		// query total results
		$query = $GLOBALS['TYPO3_DB']->SELECTquery('count(*)', $tables, $where, $groupBy, $orderBy);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
		if($result) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
			// We use the controllers register to store this special value.
			$this->controller->set($this->controller->totalResultCountKey, current($row));
		}   
	}

	function loadView($parameters = null) {
		$fields = 'a.uid
			, a.agency
			, p.agency parent
			, a.parentagency
			, s.silo
			, a.agencysilo
			, c.cn_short_en
			, a.country
			, z.zn_name_local
			, a.state
			, a.city
			, a.address
			, a.address2
			, a.postalcode
			, a.numberofemployees
			, a.officephone
			, a.officefax
			, a.administrator
			, a.documents
			, a.incidentmanager
			, a.alternateincidentmanagers
			, a.buildingpoc
			, a.buildingpocphone
			, a.buildingpocphoneafterhours
			, a.buildingalternatepoc
			, a.buildingalternatepocphone
			, a.buildingalternatepocphoneafterhours
			, a.emergencycall
			, a.emergencybridgeline
			, a.passcode
			, a.chairpasscode
			, a.securityphone
			, a.receptionphone
			, a.phone247us
			, a.phone247nonus
			, a.viewers
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN tx_cbgaedms_agency p ON a.parentagency = p.uid
			LEFT JOIN tx_cbgaedms_silo s ON a.agencysilo = s.uid
			LEFT JOIN static_countries c ON a.country = c.uid
			LEFT JOIN static_country_zones z ON a.state = z.uid
		';
		$groupBy = null;
		$orderBy = 'a.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND a.uid = ' . $uid;
			if ( $agencyStr = $parameters->get('agencyStr') )
				$where .= ' AND a.agency LIKE "%' . $agencyStr .'%"';
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);

				// get incidentmanager object
				$incidentmanager = $this->loadFE_UserEntry($row['incidentmanager']);
				$entry->set('incidentmanagerEntry', $incidentmanager);

				// alternateincidentmanagers object
				$alternateincidentmanagers = $this->loadFE_UserEntries($row['alternateincidentmanagers']);
				$entry->set('alternateincidentmanagerEntries', $alternateincidentmanagers);

				$docs = $this->loadDocumentEntries($row['uid']);
				$entry->set('docs', $docs);

				$this->append($entry);
			}
		}
	}

	function preparedParameters ( $insert = false ) {
		$parameters = $this->controller->parameters;
		$parameterArray = array();
		$fields = 'hidden,agency,agencysilo,country,address,address2,city,state,postalcode,numberofemployees,officephone,incidentmanager,alternateincidentmanagers,buildingpoc,buildingalternatepoc,emergencycall,emergencybridgeline,passcode,chairpasscode,securityphone,receptionphone,phone247us,phone247nonus,buildingpocphone,buildingpocphoneafterhours,buildingalternatepocphone,buildingalternatepocphoneafterhours,fe_group,officefax,viewers';
		foreach($parameters->selectHashArray($fields) as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		$parameterArray['hidden'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		$parameterArray['tstamp'] = time();
		$parameterArray['alternateincidentmanagers'] = ( $parameterArray['alternateincidentmanagers'] ) ? $parameterArray['alternateincidentmanagers'] : $parameters->get('newalternateincidentmanagersleft');
		$parameterArray['viewers'] = ( $parameterArray['viewers'] ) ? $parameterArray['viewers'] : $parameters->get('newviewersleft');

		if ( ! $parameterArray['phone247us'] )
			$parameterArray['phone247us'] = $this->controller->configurations['phone247us'];
		if ( ! $parameterArray['phone247nonus'] )
			$parameterArray['phone247nonus'] = $this->controller->configurations['phone247nonus'];

		if ( $insert ) {
			$parameterArray['crdate'] = time();
			$parameterArray['pid'] = $this->controller->configurations['storagePid'];
			$parameterArray['feuser'] = $this->controller->getUserId();
			if ( ! $parameterArray['parentagency'] )
				$parameterArray['parentagency'] = $this->controller->configurations->get('masterAgency');
		}

		return $parameterArray;
	}

	function update() {
		$parameters = $this->controller->parameters;
		$updateArray = $this->preparedParameters();
		$where = 'uid = ' . $parameters->get('uid');
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_agency', $where, $updateArray);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	cbDebug( 'query', $query );	
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function insert() {
		$insertArray = $this->preparedParameters(true);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_agency', $insertArray);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	cbDebug( 'query', $query );	
		$GLOBALS['TYPO3_DB']->sql_query($query);
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	function loadAgencySilos($parameters = null) {
		$fields = 'DISTINCT s.uid optionvalue
			, s.silo optionname
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN tx_cbgaedms_silo s ON a.agencysilo = s.uid
		';
		$groupBy = null;
		$orderBy = 's.silo ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= 'AND a.agencysilo != 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgencyCountries($parameters = null) {
		$fields = 'DISTINCT c.uid optionvalue
			, c.cn_short_en optionname
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN static_countries c ON a.country = c.uid
		';
		$groupBy = null;
		$orderBy = 'c.cn_short_en ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= 'AND a.country != 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgencyStates($parameters = null) {
		$fields = 'DISTINCT z.uid optionvalue
			, z.zn_name_local optionname
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN static_country_zones z ON a.state = z.uid
		';
		$groupBy = null;
		$orderBy = 'z.zn_name_local ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= 'AND a.state != 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgencyCities($parameters = null) {
		$fields = 'DISTINCT a.city optionvalue
			, a.city optionname
		';
		$tables = 'tx_cbgaedms_agency a
		';
		$groupBy = null;
		$orderBy = 'a.city ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= 'AND a.city NOT LIKE "" ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgenciesAsOptions($parameters = null) {
		$fields = 'a.uid optionvalue
			, a.agency optionname
		';
		$tables = 'tx_cbgaedms_agency a
		';
		$groupBy = null;
		$orderBy = 'a.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgencyParentsAsOptions($parameters = null) {
		$fields = 'p.uid optionvalue
			, p.agency optionname
		';
		$tables = 'tx_cbgaedms_agency a
			LEFT JOIN tx_cbgaedms_agency p ON a.parentagency = p.uid
		';
		$groupBy = 'a.parentagency';
		$orderBy = 'p.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= ' AND p.hidden = 0 AND p.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $exclude = $parameters->get('exclude') )
				$where .= ' AND a.uid != ' . $exclude;
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

	function loadAgenciesAsDualSelect($parameters = null) {
		$fields = 'a.uid optionvalue
			, a.uid
			, a.agency optionname
			, a.agency
		';
		$tables = 'tx_cbgaedms_agency a
		';
		$groupBy = null;
		$orderBy = 'a.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			$fieldName = $parameters->get('fieldName');
			$uid = $parameters->get('uid');

			switch ( $fieldName ) {
				case 'userlocations' :
					break;

				case 'alternateincidentmanagers' :
					if ( ! $parameters->get('exclude') )
						$where .= " AND FIND_IN_SET({$uid}, a.{$fieldName})";
					else
						$where .= " AND NOT FIND_IN_SET({$uid}, a.{$fieldName})";
					break;

				case 'viewers' :
					if ( ! $parameters->get('exclude') )
						$where .= " AND FIND_IN_SET({$uid}, a.{$fieldName})";
					else
						$where .= " AND NOT FIND_IN_SET({$uid}, a.{$fieldName})";
					break;

				default:
					if ( ! $parameters->get('exclude') )
						$where .= " AND a.{$fieldName} = {$uid}";
					else
						$where .= " AND a.{$fieldName} != {$uid}";
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				if ( $parameters->get('loadAgencyEntries') ) {
					$agencies = $this->loadAgencyEntries($row['uid']);
					$entry->set('agencies', $agencies);
				}
				$this->append($entry);
			}
		}
	}

	function accessUpdate() {
		$parameters = $this->controller->parameters;
cbDebug( 'parameters', $parameters );	
		$fieldNames = array('incidentmanager', 'alternateincidentmanagers', 'viewers');
		$uid = $parameters->get('uid');

		foreach ( $fieldNames as $fieldName ) {
			$added = $parameters->get('added' . $fieldName . 'left');
			$removed = $parameters->get('added' . $fieldName . 'right');

			switch ( $fieldName ) {
				case 'alternateincidentmanagers' :
					if ( $added ) {
						// add permission
						$query = <<<EOD
							UPDATE tx_cbgaedms_agency
							SET alternateincidentmanagers = CONCAT_WS(',', IF(alternateincidentmanagers = '', NULL, alternateincidentmanagers), {$uid})
							WHERE uid IN ({$added})
EOD;
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}

					if ( $removed ) {
						// remove permission
						$query = <<<EOD
							UPDATE tx_cbgaedms_agency
							SET alternateincidentmanagers = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', alternateincidentmanagers, ','), CONCAT(',', {$uid}, ','), ','))
							WHERE uid IN ({$removed})
EOD;
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}
					break;

				case 'viewers' :
					if ( $added ) {
						// add permission
						$query = <<<EOD
							UPDATE tx_cbgaedms_agency
							SET viewers = CONCAT_WS(',', IF(viewers = '', NULL, viewers), {$uid})
							WHERE uid IN ({$added})
EOD;
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}

					if ( $removed ) {
						// remove permission
						$query = <<<EOD
							UPDATE tx_cbgaedms_agency
							SET viewers = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', viewers, ','), CONCAT(',', {$uid}, ','), ','))
							WHERE uid IN ({$removed})
EOD;
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}
					break;

				default:
					if ( $added ) {
						// add permission
						$updateArray = array($fieldName => $uid);
						$where = 'uid IN (' . $added . ')';
						$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_agency', $where, $updateArray);
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}

					if ( $removed ) {
						// remove permission
						$updateArray = array($fieldName => '' );
						$where = 'uid IN (' . $removed . ')';
						$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_agency', $where, $updateArray);
						// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}
			}

		}
	}

	function loadLocationAccess($parameters = null) {
		$fields = 'a.uid optionvalue
			, a.agency optionname
		';
		$tables = 'tx_cbgaedms_agency a
		';
		$groupBy = null;
		$orderBy = 'a.agency ASC';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';
		// $where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $fieldName = $parameters->get('fieldName') ) {
				$uid = $parameters->get('uid');
				$where .= " AND FIND_IN_SET({$uid}, a.{$fieldName})";
			}
		}

		// query
		$query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}

	function loadCountryZones($parameters = null) {
		$fields = 'z.uid
			, z.zn_name_local
		';
		$tables = '
			static_country_zones z 
			LEFT JOIN static_countries c ON z.zn_country_iso_nr = c.cn_iso_nr
		';
		$groupBy = null;
		$orderBy = 'z.zn_name_local';
		$where = '';
		$where .= ' z.uid != 100000';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('countryId') )
				$where .= ' AND c.uid = ' . $uid;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_agency.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_agency.php']);
}

?>
