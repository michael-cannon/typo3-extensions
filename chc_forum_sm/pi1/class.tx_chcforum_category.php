<?php
	 
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2004 Zach Davis (zach@crito.org)
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
	* Category class contains the attributes and methods for categories in the
	* chc_forum extension.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_category extends tx_chcforum_pi1 {
		 
		var $uid;
		var $pid;
		var $tstamp;
		var $crdate;
		var $cruser_id;
		var $sorting;
		var $deleted;
		var $hidden;
		var $fe_group;
		var $cat_title;
		var $cat_description;
		 
	 /**
		* Category object constructor. Create object from data in table row.
		*
		* @param integer  $cat_id: the category uid.
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return boolean  true if the DB query returned anything.
		*/
		function tx_chcforum_category ($cat_id = false, $cObj) {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;

			// bring in the user object.
			$this->user = $this->fconf['user'];
			
			$this->internal['results_at_a_time'] = 1000;
			if (!$cat_id) {
				return;
			} else {

				$addWhere = "uid=$cat_id";
				$table = 'tx_chcforum_category';
				$fields = '*';
				$limit = '1';
				$where = tx_chcforum_shared::buildWhere($table,$addWhere,1);
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
				$row_array = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results);
				$GLOBALS['TYPO3_DB']->sql_free_result($results);

				if ($row_array) {
					foreach ($row_array as $attr => $value) {
						$this->$attr = $value;
					}
					return true;
				}
			}
		}
		 
	 /**
		* Return data for outputing a category row in the category view
		*
		* @return array  array containing the data needed to populate a category row. Currently, this array only contains the title, but this could change later.
		*/
		function cat_header_row () {
			$output_array = array ('cat_title' => $this->cat_title);
			return $output_array;
		}
		 
	 /**
	  * Get the uids of all the confs that belong to this category
	  *
	  * @return array  array in the shape of 0 => conf_uid, 1 => another_conf_uid, etc.
	  */
		function get_confs () {
			if (!$this->user) {
				$tx_chcforum_user = t3lib_div::makeInstanceClassName("tx_chcforum_user");
        		$this->user = new $tx_chcforum_user($this->cObj);
			}
			
			$out = '';
		
			if ($this->uid) {

				switch ($this->fconf['conf_sorting']) {
					case 'sort':
						$order_by = 'sorting ASC';
					break;
					case 'alpha_desc':
						$order_by = 'conference_name DESC';					
					break;
					case 'alpha_asc':
					default:
						$order_by = 'conference_name ASC';
					break;					
				}

				$addWhere = "cat_id=$this->uid";
				$where = tx_chcforum_shared::buildWhere('tx_chcforum_conference',$addWhere);
				$fields = 'uid';
				$table = 'tx_chcforum_conference';
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

				if ($results) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
						if ($this->user->can_read_conf($row['uid'])) {
							$out[] = $row['uid'];
						}
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
			}
			return $out;
		}

		function return_conf_titles() {
			$confs = $this->get_confs();
			if (is_array($confs)) {
				foreach ($confs as $k => $uid) {
					$tx_chcforum_conference = t3lib_div::makeInstanceClassName("tx_chcforum_conference");
					$conf = new $tx_chcforum_conference($uid, $this->cObj);
					$conf_arr[] = $conf->return_title_option_tag('uid');
				}
				return $conf_arr;
			}
		}
		
		function return_title() {
			return $this->cat_title;
		}
		 
	 /**
		* Returns the IDs of all categories in the forum. This should not reveal categories that 
		* cannot be accessed by the user, Since it uses the typo3 DB query function. This is 
		* generally called as a static method,since it does not require a category object to function 
		* correctly.
		*
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return array  array in the shape of 0 => cat_uid, 1 => another_cat_uid, etc.
		*/
		function get_all_cat_ids ($cObj) {
      
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;
			
			// figure out how to sort the conferences...based on fconf value.
			switch ($this->fconf['cat_sorting']) {
				case 'sort':
					$order_by = 'sorting ASC';
				break;
				case 'alpha_desc':
					$order_by = 'cat_title DESC';					
				break;
				case 'alpha_asc':
				default:
					$order_by = 'cat_title ASC';
				break;					
			}

			$where = tx_chcforum_shared::buildWhere('tx_chcforum_category');
			$fields = 'uid';
			$table = 'tx_chcforum_category';
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				if ($this->user->can_read_cat($row['uid']) == true) $cat_uid[] = $row['uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($results);
			return $cat_uid;
		}
	}

	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_category.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_category.php']);
	}
	 
?>
