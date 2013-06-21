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
* Class that implements the model for table tx_cbgaedms_useraccess.
*
* @author	Michael Cannon <michael@peimic.com>
* @package	TYPO3
* @subpackage	tx_cbgaedms
*/

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_useraccess extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_useraccess($controller = null, $parameter = null) {
		parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {
		// fix settings
		$fields = 'f.uid
			, CONCAT(f.last_name, ", ", f.first_name) name
			, f.first_name
			, f.last_name
			, f.title
			, f.email
			, f.telephone officephone
			, f.tx_cbgaedms_mobilephone mobilephone
			, f.tx_cbgaedms_homephone homephone
		';
		$tables = 'fe_users f';
		$groupBy = null;
		$orderBy = 'f.last_name ASC, f.first_name ASC';
		$limit = null;
		$where = 'f.disable = 0 AND f.deleted = 0 ';
		// $where .= ' AND (f.first_name != "" OR f.last_name != "")';
		$where .= ' AND f.pid = ' .  $this->controller->configurations->get('usersStoragePid');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND f.uid = ' . $uid;
			if ( $userId = $parameters->get('userId') )
				$where .= ' AND f.uid = ' . $userId;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'query', $query );	cbDebug( 'File ' . __FILE__, 'Line ' .  __LINE__ );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_useraccess.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_useraccess.php']);
}

?>