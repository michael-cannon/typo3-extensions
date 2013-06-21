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
 * Class that implements the model for table fe_users.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_fe_users extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_fe_users($controller = null, $parameter = null) {
		parent::tx_cbgaedms_model_common($controller, $parameter);
	}

function loadfullresults($parameters = null) {
		$fields = 'f.uid
			, CONCAT(f.last_name, ", ", f.first_name) name
			, f.name altname
			, f.first_name
			, f.last_name
			, f.title
			, f.email
			, f.telephone officephone
			, f.tx_cbgaedms_mobilephone mobilephone
			, f.tx_cbgaedms_homephone homephone
			, f.static_info_country
			, f.company
			, f.tx_ipggeosrfeuser_region region
			, f.zone
			, f.city
			, f.password
			, f.address
			, f.zip
			, f.fax
			, f.status
			, f.tx_ipgmetrorep_metrorep metrorep
		';
		$tables = 'fe_users f';
		$where = 'f.disable = 0 AND f.deleted = 0 ';
		// $where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');
		$groupBy = null;
		$orderBy = 'f.last_name ASC, f.first_name ASC';
		//$limit = (integer) $this->controller->parameters->get('offset');
		//$limit .= ', ' . (integer) $this->controller->configurations->get('resultsPerView');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND f.uid = ' . $uid;
			if ( $userStr = $parameters->get('userStr') ) {
				$where .= ' AND ( f.first_name LIKE "%' . $userStr .'%"';
				$where .= ' OR f.last_name LIKE "%' . $userStr .'%"';
			// LOU 8-14-09 Added ability to search by email, title, telephone, mobile, home, city, country, company
				$where .= ' OR f.email LIKE "%' . $userStr .'%"';
				$where .= ' OR f.title LIKE "%' . $userStr .'%"';
				$where .= ' OR f.telephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.tx_cbgaedms_mobilephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.tx_cbgaedms_homephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.static_info_country LIKE "%' . $userStr .'%"';
				$where .= ' OR f.company LIKE "%' . $userStr .'%"';
				$where .= ' OR f.zone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.city LIKE "%' . $userStr .'%"';
				$where .= ' OR f.password LIKE "%' . $userStr .'%"';
				$where .= ' OR f.address LIKE "%' . $userStr .'%"';
				$where .= ' OR f.fax LIKE "%' . $userStr .'%"';

				$viewCommon = new tx_cbgaedms_view_common();
				$keyUserStatusType = $viewCommon->keyUserStatusType( $userStr );

				$where .= $keyUserStatusType
					? ' OR f.status = "' . $keyUserStatusType .'"'
					: '';
				$keyRegionType = $viewCommon->keyRegionType( $userStr );

				$where .= $keyRegionType
					? ' OR f.tx_ipggeosrfeuser_region = "' . $keyRegionType .'"'
					: '';
				$where .= ' )';
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$row['name'] = ( ", " != $row['name'] ) ? $row['name'] : $row['altname'];
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}

		// query total results
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery('count(*)', $tables, $where, $groupBy, $orderBy);
		// $result = $GLOBALS['TYPO3_DB']->sql_query($query);
		if($result) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
			// We use the controllers register to store this special value.
			$this->controller->set($this->controller->totalResultCountKey, current($row));
		}   
	}
	
	function load($parameters = null) {
		$fields = 'f.uid
			, CONCAT(f.last_name, ", ", f.first_name) name
			, f.name altname
			, f.first_name
			, f.last_name
			, f.title
			, f.email
			, f.telephone officephone
			, f.tx_cbgaedms_mobilephone mobilephone
			, f.tx_cbgaedms_homephone homephone
			, f.static_info_country
			, f.company
			, f.tx_ipggeosrfeuser_region region
			, f.zone
			, f.city
			, f.password
			, f.address
			, f.zip
			, f.fax
			, f.status
			, f.tx_ipgmetrorep_metrorep metrorep
		';
		$tables = 'fe_users f';
		$where = 'f.disable = 0 AND f.deleted = 0 ';
		// $where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');
		$groupBy = null;
		$orderBy = 'f.last_name ASC, f.first_name ASC';
		$limit = (integer) $this->controller->parameters->get('offset');
		$limit .= ', ' . (integer) $this->controller->configurations->get('resultsPerView');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			// prevent loading all users if none given
			$uid = $parameters->get('uid');
			if ( ! $uid && $parameters->get('entries') )
				$where .= ' AND 1 = 0';
			elseif ( $uid )
				$where .= ' AND f.uid IN ( ' . $uid . ')';
			if ( $userStr = $parameters->get('userStr') ) {
				$where .= ' AND ( f.first_name LIKE "%' . $userStr .'%"';
				$where .= ' OR f.last_name LIKE "%' . $userStr .'%"';
			// LOU 8-14-09 Added ability to search by email, title, telephone, mobile, home, city, country, company
				$where .= ' OR f.email LIKE "%' . $userStr .'%"';
				$where .= ' OR f.title LIKE "%' . $userStr .'%"';
				$where .= ' OR f.telephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.tx_cbgaedms_mobilephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.tx_cbgaedms_homephone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.static_info_country LIKE "%' . $userStr .'%"';
				$where .= ' OR f.company LIKE "%' . $userStr .'%"';
				$where .= ' OR f.zone LIKE "%' . $userStr .'%"';
				$where .= ' OR f.city LIKE "%' . $userStr .'%"';
				$where .= ' OR f.password LIKE "%' . $userStr .'%"';
				$where .= ' OR f.address LIKE "%' . $userStr .'%"';
				$where .= ' OR f.fax LIKE "%' . $userStr .'%"';

				$viewCommon = new tx_cbgaedms_view_common();
				$keyUserStatusType = $viewCommon->keyUserStatusType( $userStr );

				$where .= $keyUserStatusType
					? ' OR f.status = "' . $keyUserStatusType .'"'
					: '';
				$keyRegionType = $viewCommon->keyRegionType( $userStr );

				$where .= $keyRegionType
					? ' OR f.tx_ipggeosrfeuser_region = "' . $keyRegionType .'"'
					: '';
				$where .= ' )';
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$row['name'] = ( ", " != $row['name'] ) ? $row['name'] : $row['altname'];
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
	
	
	function loadEmail($parameters = null) {
		$fields = 'CONCAT(f.first_name, " ", f.last_name, " <", f.email, ">") name';
		$tables = 'fe_users f';
		$where = 'f.disable = 0 AND f.deleted = 0 ';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');
		$groupBy = null;
		$orderBy = null;
		$limit = null;

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND f.uid = ' . $uid;
			if ( $userStr = $parameters->get('userStr') ) {
				$where .= ' AND ( f.first_name LIKE "%' . $userStr .'%"';
				$where .= ' OR f.last_name LIKE "%' . $userStr .'%"';
				$where .= ' )';
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}

	function loadUsersNoTitleAsOptions($parameters = null) {
		$fields = 'uid optionvalue
			, CONCAT(last_name, ", ", first_name) optionname
			, CONCAT(name) altname
		';
		$tables = 'fe_users f';
		$groupBy = null;
		$orderBy = 'last_name ASC, first_name ASC';
		$where = 'disable = 0 AND deleted = 0 ';
		// $where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');

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

	function loadUsersAsOptions($parameters = null) {
		$fields = 'uid optionvalue
			, CONCAT(last_name, ", ", first_name, "; ", title ) optionname
			, CONCAT(name, "; ", title ) altname
		';
		$tables = 'fe_users f';
		$groupBy = null;
		$orderBy = 'last_name ASC, first_name ASC';
		$where = 'disable = 0 AND deleted = 0 ';
		// $where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');

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
		$fields = 'hidden,metrorep,first_name,last_name,title,email,officephone,mobilephone,homephone';
		foreach($parameters->selectHashArray($fields) as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		$parameterArray['disable'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		unset( $parameterArray['hidden'] );
		$parameterArray['tx_ipgmetrorep_metrorep'] = ('' == $parameterArray['metrorep'])
									? 0
									: 1;
		unset( $parameterArray['metrorep'] );
		$parameterArray['tstamp'] = time();

		$parameterArray['telephone'] = $parameterArray['officephone'];
		unset( $parameterArray['officephone'] );

		$parameterArray['tx_cbgaedms_mobilephone'] = $parameterArray['mobilephone'];
		unset( $parameterArray['mobilephone'] );

		$parameterArray['tx_cbgaedms_homephone'] = $parameterArray['homephone'];
		unset( $parameterArray['homephone'] );

		$parameterArray['name'] = $parameterArray['first_name'] . ' ' . $parameterArray['last_name'];

		if ( $insert ) {
			$parameterArray['crdate'] = time();
			$parameterArray['pid'] = $this->controller->configurations['usersStoragePid'];
		}

		return $parameterArray;
	}

	function update() {
		$parameters = $this->controller->parameters;
		$updateArray = $this->preparedParameters();
		$where = 'uid = ' . $parameters->get('uid');
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', $where, $updateArray);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	exit();

		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function loadUserAsDualSelect($parameters = null) {
		$fields = 'f.uid optionvalue
			, f.uid
			, CONCAT(f.last_name, ", ", f.first_name) optionname
			, CONCAT(f.last_name, ", ", f.first_name) name
		';
		$tables = 'fe_users f
			, tx_cbgaedms_agency a
		';
		$where = 'f.disable = 0 AND f.deleted = 0 ';
		$where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' . $this->controller->configurations->get('usersStoragePid');
		if ( $uid = $parameters->get('uid') )
			$where .= " AND a.uid = {$uid}";
		else
			$where .= ' AND a.uid = ' . $this->controller->configurations->get('masterAgency');
		$where .= $this->getWhereAgencyAccess();
		// $where .= $this->getWhereMasterAgencyChildren();
		$groupBy = null;
		$orderBy = 'f.last_name ASC, f.first_name ASC';
		$limit = null;

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			$fieldName = $parameters->get('fieldName');
			if ( ! $parameters->get('exclude') ) {
				if ( $selected = $parameters->get('selected') )
					$where .= " AND f.uid IN ({$selected})";
				else
					$where .= " AND FIND_IN_SET(f.uid, a.{$fieldName})";
			} else {
				if ( $selected = $parameters->get('selected') )
					$where .= " AND f.uid NOT IN ({$selected})";
				else
					$where .= " AND NOT FIND_IN_SET(f.uid, a.{$fieldName})";
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_fe_users.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_fe_users.php']);
}

?>
