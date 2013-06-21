<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Peimic.com (http://peimic.com)
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
* Base class 'tx_affiliatetracker_base' for the 'affiliate_tracker' extension.
*
* @author	Suman Debnath <suman@srijan.in>
* @author	Michael Cannon <michael@peimic.com>
*/

error_reporting(E_ALL^E_NOTICE);

class tx_affiliatetracker_base {
	var $extKey = 'affiliate_tracker';	// The extension key.

	var $tb_visitor_tracking = 'tx_affiliatetracker_visitor_tracking'; //visitor tracking data table

	var $storage_pid; //storage page id
	var $code_parts = array();

	/**
	* Constructor
	* @access public
	*/
	function tx_affiliatetracker_base() {
		$this->init();
	}

	/**
	* Initialization and checks
	* @access private
	* @return void
	*/
	function init() {
		//Checking for the Typo3 data object
		if (!is_object($GLOBALS['TYPO3_DB'])) {
			die('No database connection available!');
		}

		//Fetching the sysfolder page id as set in the extension manager
		$data = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"][$this->extKey]);
		$this->storage_pid = intval($data['sysfolder_pid']);

		//Checking the value of sysfolder pid for validity. Checking for 0 is necessary since the root page has an id of 0.
		if ((1 > intval($this->storage_pid)) || ($this->checkValidPID($this->storage_pid) === false)) {
			die('Invalid or no Storage PID! Please set a valid value in the extension manager.');
		}

		//$GLOBALS['TYPO3_DB']->debugOutput	= true;
	}

	/**
	* Records tracking data
	* @param The affiliate code
	* @param FE User ID
	* @access public
	* @return mixed
	*/
	function recordAffiliateData($affiliate_code, $feuser_id) {
		//At least one parameter must have a value
		if (($affiliate_code == '') && (1 > $feuser_id)) {
			return false;
		}

		if ($affiliate_code == '') {
			if (0 < $feuser_id) {
				//Any previous Data for FE User ID 0 i.e. Unlogged user ?
				if ($this->ifFEUserHistoryExists(0)) {
					//Update previous data (both in DB and session) for FE User ID 0 to current user ID
					$this->updateFEUserHistory(0, $feuser_id);
				}
			} else {
				//Do Nothing. This stage will never be reached since such data is culled in the beginning. Here for clarity.
			}
		} else {
			//Checking if affiliate code is valid
			if ($this->checkAffiliateCode($affiliate_code) !== false) {
				//Fetching the sponsor id
				$affiliate_id = intval($this->getAffiliateID($this->code_parts['affiliate_code']));
			}
			$currentURL = $this->getCurrentURL();

			if (0 < $feuser_id) {
				//Any previous Data for FE User ID 0 i.e. Unlogged user ?
				if ($this->ifFEUserHistoryExists(0)) {
					//Update previous data (both in DB and session) for FE User ID 0 to current user ID
					$this->updateFEUserHistory(0, $feuser_id);
				}

				//Any previous Data for Current FE User ID i.e. current logged user ?
				if ($this->ifFEUserHistoryExists($feuser_id)) {
					//Checking for duplicates
					if ((!$this->checkDuplicateEntry($currentURL, $feuser_id)) || (1 > $affiliate_id)) {
						return false;
					}
				}

				//Insert Data
				$this->insertTrackingData($affiliate_id, $feuser_id);
			} else {
				//Any previous Data for FE User ID 0 i.e. Unlogged user ?
				if ($this->ifFEUserHistoryExists(0)) {
					//Checking for duplicates
					if ((!$this->checkDuplicateEntry($currentURL, 0)) || (1 > $affiliate_id)) {
						return false;
					}
				}

				//Insert Data
				$this->insertTrackingData($affiliate_id);
			}
		}
	}

	/**
	* Checks affiliate code
	* @param The affiliate code
	* @access private
	* @return mixed
	*/
	function checkAffiliateCode($affiliate_code) {
		$output = false;

		$regexp = '#(?P<affiliate_code>[1-9]\d*)(?P<affiliate_source_code>[A-Za-z]\a*)?(?P<affiliate_index_code>[1-9]\d*)?#';
		preg_match_all($regexp, $affiliate_code, $matches);

		if (trim($matches['affiliate_code'][0]) != '') {
			$this->code_parts['full_affiliate_code'] = trim($matches[0][0]);
			$this->code_parts['affiliate_code'] = trim($matches['affiliate_code'][0]);
			if (trim($matches['affiliate_source_code'][0]) != '') {
				$this->code_parts['affiliate_source_code'] = trim($matches['affiliate_source_code'][0]);
				if (trim($matches['affiliate_index_code'][0]) != '') {
					$this->code_parts['affiliate_index_code'] = trim($matches['affiliate_index_code'][0]);
				}
			}
		}

		if (0 < count($this->code_parts)) {
			$output = $this->getAffiliateID($this->code_parts['affiliate_code']);
		}

		return $output;
	}

	/**
	* Inserts tracking data
	* @param The affiliate code
	* @param FE User ID
	* @access private
	* @return mixed
	*/
	function insertTrackingData($affiliate_id, $feuser_id = 0) {
		$output = false;

		$currentURL = $this->getCurrentURL();
		if ((1 > $affiliate_id) || ($this->checkDuplicateEntry($currentURL, $feuser_id) === false)) {
			return $output;
		}

		$insertFields = array(
		'pid' => $this->storage_pid,
		'tstamp' => time(),
		'crdate' => time(),
		'affiliate_id' => $affiliate_id,
		'landing_url' => $currentURL,
		'referer_url' => t3lib_div::getIndpEnv('HTTP_REFERER'),
		'full_affiliate_code' => $this->code_parts['full_affiliate_code'],
		'affiliate_source_code' => $this->code_parts['affiliate_source_code'],
		'affiliate_index_code' => $this->code_parts['affiliate_index_code']
		);

		if (0 < $feuser_id) {
			$insertFields = array_merge($insertFields, array('feuser_id' => $feuser_id));
		}
		//debug($insertFields);

		if ($GLOBALS['TYPO3_DB']->exec_INSERTquery(
		$this->tb_visitor_tracking,
		$insertFields
		)) {
			$feuser_arr = $this->getSessionData($this->extKey);

			$this->addArrayElement($this->code_parts['full_affiliate_code'], $feuser_arr['full_affiliate_code']);
			$this->addArrayElement($currentURL, $feuser_arr['landing_url']);
			$this->addArrayElement($feuser_id, $feuser_arr['feuser_id']);
			$this->addArrayElement($GLOBALS['TYPO3_DB']->sql_insert_id(), $feuser_arr['uid']);
			//SD Is this array element useful ?
			$feuser_arr['sponsor-link'] = true;
			$feuser_arr['user-link'] = (0 < $feuser_id) ? true : false;

			//debug($feuser_arr);
			$this->setSessionData($feuser_arr, $this->extKey);
		}

		return $output;
	}

	/**
	* Updatess tracking data
	* @param integer FE User ID
	* @param array The uid of the data to be updated
	* @access private
	* @return mixed
	*/
	function updateTrackingData($feuser_id, $tracker_data_uid) {
		if ((1 > $feuser_id) || !is_array($tracker_data_uid) || (1 > count($tracker_data_uid))) {
			return false;
		}

		$updateFields = array(
		'feuser_id' => $feuser_id
		);
		$tracker_data_uid = implode(',', $tracker_data_uid);
		$where_clause = "FIND_IN_SET(uid, '$tracker_data_uid')";

		return $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
		$this->tb_visitor_tracking,
		$where_clause,
		$updateFields
		);
	}

	/**
	* Adds value to an array
	* @param mixed Value to be added
	* @param &$arr array The array
	* @access private
	*/
	function addArrayElement($value, &$arr) {
		if (!is_array($arr)) {
			$arr = array();
		}
		array_push($arr, $value);
	}

	/**
	* Checks the duplicate entry
	* @param string The current URL
	* @param integer The FE User ID
	* @access private
	* @return bool
	*/
	function checkDuplicateEntry($currentURL, $feuser_id) {
		$output = true;
		$feuser_arr = $this->getSessionData($this->extKey);
		if (!is_array($feuser_arr['feuser_id'])) {
			return $output;
		}

		foreach ($feuser_arr['feuser_id'] as $key => $value) {
			if (($feuser_arr['full_affiliate_code'][$key] == $this->code_parts['full_affiliate_code']) && ($feuser_arr['landing_url'][$key] == $currentURL) && ($feuser_arr['feuser_id'][$key] == $feuser_id)) {
				$output = false;
			}
		}

		return $output;
	}

	/**
	* Checks for history of a user
	* @param integer The FE User ID
	* @access private
	* @return bool
	*/
	function ifFEUserHistoryExists($feuser_id = 0) {
		$output = false;
		$feuser_arr = $this->getSessionData($this->extKey);
		if (is_array($feuser_arr['feuser_id'])) {
			if (in_array($feuser_id, $feuser_arr['feuser_id'])) {
				$output = true;
			}
		}

		return $output;
	}

	/**
	* Updates the session and DB history for a particular session
	* @param integer The old FE User ID of the page
	* @param integer The new FE User ID of the page
	* @access private
	*/
	function updateFEUserHistory($old_feuser_id, $new_feuser_id) {
		$output = false;

		//Only change should be from FE User ID 0 i.e. Unlogged user to a that of a logged user
		if ((1 > $new_feuser_id) || (0 < $old_feuser_id)) {
			return $output;
		}

		$temp_arr = array();
		$feuser_arr = $this->getSessionData($this->extKey);

		if (is_array($feuser_arr['feuser_id'])) {
			foreach ($feuser_arr['feuser_id'] as $index => $value) {
				if ($value == $old_feuser_id) {
					if (0 < $feuser_arr['uid'][$index]) {
						$temp_arr[] = $feuser_arr['uid'][$index];
					}
				}
			}
		}

		if (0 < count($temp_arr)) {
			if ($this->updateTrackingData($new_feuser_id, $temp_arr)) {
				foreach ($feuser_arr['feuser_id'] as $index => $value) {
					if ($value == $old_feuser_id) {
						$feuser_arr['feuser_id'][$index] = $new_feuser_id;
					}
				}
				$feuser_arr['user-link'] = true;
				$output = true;
				$this->setSessionData($feuser_arr, $this->extKey);
			}
		}

		return $output;
	}

	/**
	* Checks if the given page id is valid
	* @return bool Returns true if valid, false if invalid
	* @param integer The uid of the page
	* @access private
	*/
	function checkValidPID($pid) {
		$output = false;
		$pid = intval($pid);
		if (1 > $pid) {
			return $output;
		}

		$select_fields = 'uid';
		$table = 'pages';
		$where_clause = "(uid = $pid)
				AND (deleted = 0) 
				AND doktype NOT IN (255)";

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				$output = true;
			}
		}

		return $output;
	}

	/**
	* Gets the Affiliate ID. NOTE: Affiliate ID is actually uid of 'tx_t3consultancies' table
	* @return mixed Returns uid if valid, false if invalid
	* @param string The affiliate_code
	* @access private
	* @return mixed
	*/
	function getAffiliateID($code) {
		$output = false;
		if (trim($code) == '') {
			return $output;
		}

		$select_fields = 'tx_t3consultancies.uid';
		$table = 'tx_affiliatetracker_codes, tx_t3consultancies';
		$where_clause = "
			1 = 1
			AND tx_affiliatetracker_codes.affiliate_code = $code
			AND FIND_IN_SET(tx_affiliatetracker_codes.uid, 
				tx_affiliatetracker_affiliate_codes
			)
		";

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				$output = $row['uid'];
			}
		}

		return $output;
	}

	/**
	* Returns full url of the current page
	* @return string The returned URL
	* @access private
	*/
	function getCurrentURL() {
		$currentURL = 'http';
		$script_name = '';

		if(isset($_SERVER['REQUEST_URI'])) {
			$script_name = $_SERVER['REQUEST_URI'];
		} else {
			$script_name = $_SERVER['PHP_SELF'];
			if(trim($_SERVER['QUERY_STRING']) != '') {
				$script_name .=  '?'.$_SERVER['QUERY_STRING'];
			}
		}

		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$currentURL .=  's';
		}

		$currentURL .=  '://';
		if($_SERVER['SERVER_PORT'] != '80') {
			$currentURL .= $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$script_name;
		} else {
			$currentURL .= $_SERVER['HTTP_HOST'].$script_name;
		}

		return $currentURL;
	}

	/**
	* Sets a session variable
	* @access private
	* @return void
	*/
	function setSessionData($data, $name) {
		$GLOBALS['TSFE']->fe_user->setKey('ses', $name, $data);
	}

	/**
	* Gets a session variable
	* @access private
	* @return mixed
	*/
	function getSessionData($name) {
		return $GLOBALS['TSFE']->fe_user->getKey('ses', $name);
	}
}
?>
