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
	* Conference class contains the attributes and methods for conferences in the
	* chc_forum extension.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_conference extends tx_chcforum_pi1 {
		 
		var $uid;
		var $pid;
		var $tstamp;
		var $crdate;
		var $cruser_id;
		var $sorting;
		var $deleted;
		var $hidden;
		var $cat_id;
		var $conference_name;
		var $conference_desc;
		var $conference_public_r;
		var $conference_public_w;
		var $auth_forumgroup_rw;
		var $auth_feuser_mod;
		var $auth_forumgroup_attach;
	 
	 /**
	  * Conference object constructor. Constructs the object from a DB record.
		*
		* @param integer  $conf_id: the conference uid.
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return boolean  true if the DB query returned anything. 
		*/
		function tx_chcforum_conference ($conf_id, $cObj) {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;

			// bring in the user object.
			$this->user = $this->fconf['user'];

			// check if user can see hidden
			if (is_object($this->user) && $this->user->can_mod_conf($conf_id)) {
				$this->user->show_hidden = 1;	
			}


			 
			$this->internal['results_at_a_time'] = 1000;
			if (!$conf_id) {
				return;
			}

			$addWhere = "uid=$conf_id";
			$table = 'tx_chcforum_conference';
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
		 
		function return_title_option_tag($type) {
			if ($type == 'uid') {
				$val = $this->uid;
				return '<option value="conf_'.$val.'">'.htmlspecialchars('>>').' '.$this->conference_name.'</option>';			
			}
		}

	 /**
	  * Returns an array with info needed to create a conference row in cat view.
		* If this function is called with $null_conf set to true, it means that we're just
		* asking for an empty conf row to be returned (which is done when there aren't any
		* conferences in a categroy -- in this case, it gets called as a static method
		*
		* @param boolean $null_conf: set this to true if you want an empty conference row returned.
		* @return array array containing keys conf_desc, conf_new, conf_thread_count, conf_post_count, conf_last_post_data.
		*/
		function return_conf_row_data ($null_conf = false) {
			if ($null_conf == false) {
				if ($this->new_cnt > 0) {
					if ($this->new_cnt > 1) {
						$label = tx_chcforum_shared::lang('return_thread_data_new_plrl');
					} else {
						$label = tx_chcforum_shared::lang('return_thread_data_new_sing');
					}
					$new_posts = '['.$this->new_cnt.' '.$label.']';
				}
				$output_array = array ('conf_name' => $this->conference_link(),
					'conf_desc' => $this->conference_desc,
					'conf_new' => $new_posts,
					'conf_thread_count' => $this->return_thread_count(),
					'conf_post_count' => $this->return_post_count(),
					'conf_last_post_data' => $this->return_last_post_data()
				);
			} else {
				$output_array = array ('conf_name' => tx_chcforum_shared::lang('single_conf_null'),
					'conf_thread_count' => '-',
					'conf_post_count' => '-',
					'conf_last_post_data' => '<div style="text-align: center">'.tx_chcforum_shared::lang('na').'</div>');
			}
			return $output_array;
		}
		 
	 /**
		* Returns the name of this conference wrapped in a link that allows user to view this conference.
		*
		* @return string  conference named wrapped in link to conference (self).
		*/
		function conference_link() {
			$params = array ('view' => 'single_conf',
				'cat_uid' => $this->cat_id,
				'conf_uid' => $this->uid);
			$title = $this->conference_name;
			$out = tx_chcforum_shared::makeLink($params, $title);
			return $out;
		}
		 
   /**
	  *Returns a string containing information on the last post posted to this conference
		*
		*@return array  if there is a last post, returns post info according to return_post_info function. If there is not a last post, returns text to that effect.
		*/
		function return_last_post_data () {
			$uid = $this->return_most_recent_thread_id();
			if ($uid) {
				$tx_chcforum_thread = t3lib_div::makeInstanceClassName("tx_chcforum_thread");
				$thread = new $tx_chcforum_thread($uid, $this->cObj);
				$recent_post_id = $thread->return_most_recent_post();
				$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
				$post = new $tx_chcforum_post($recent_post_id, $this->cObj);
				return $post->return_post_info();
			} else {
				return '<div style="text-align: center;">'.tx_chcforum_shared::lang('return_last_post_data_none').'</div>';
			}
		}
		 
 	 /**
		* Returns the id of the thread most recently updated in this conference.
		*
		* @return integer  uid of most recently updated thread in this conference.
		*/		
		function return_most_recent_thread_id () {
			if ($this->uid) {
				$addWhere = "tx_chcforum_post.conference_id=$this->uid";
				$table = 'tx_chcforum_post';
				$fields = 'thread_id, tx_chcforum_thread.hidden';
				$where = tx_chcforum_shared::buildWhere($table,$addWhere);
				$table = 'tx_chcforum_post LEFT JOIN tx_chcforum_thread on tx_chcforum_post.thread_id = tx_chcforum_thread.uid';
				$addWhere.= ' AND tx_chcforum_thread.hidden=0';
				// this prevents mods from seeing info for hidden last threads, which
				// shouldn't be the case. The problem here is that when a thread is hidden,
				// all of it's posts are not necessarily hidden. Ugh. Gotta think about
				// this one...
				
				$order_by = 'tx_chcforum_post.crdate DESC';
				$limit = '1';
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);

				// Row equals the first row, which should be the most recent if I'm sorting correctly	
				$row = mysql_fetch_assoc($results);
				if ($row) {
					$uid = $row['thread_id'];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($results);

			}
			return $uid;
		}
		 
	 /**
	  * Returns the total number of threads in a conference.
		*
		* @return integer  thread count for this conference.
		*/
		function return_thread_count () {
			// Get number of records:
			$thread_ids = $this->return_all_thread_ids();
			if (is_array($thread_ids)) {
				$count = count($thread_ids);
			} else {
				$count = 0;		
			}
			return $count;
		}
		 
	 /**
	  * Returns the total number of posts in a conference.
		*
		* @return integer  post count for this conference.
		*/
		function return_post_count () {
			$table = 'tx_chcforum_post';
			$fields = 'COUNT(*)';
			if ($this->user->show_hidden == 1) {
				$addWhere = 'tx_chcforum_post.conference_id='.$this->uid;
				$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			} else {
				$addWhere = 'tx_chcforum_post.conference_id='.$this->uid.' AND tx_chcforum_thread.hidden=0';				
				$where = tx_chcforum_shared::buildWhere($table,$addWhere);
				$table = 'tx_chcforum_post LEFT JOIN tx_chcforum_thread ON tx_chcforum_thread.uid = tx_chcforum_post.thread_id';
			}
			$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
			$GLOBALS['TYPO3_DB']->sql_free_result($qresults);

			$count_new = $row['COUNT(*)'];
			return $count_new;
		}
		 
		function return_post_ids_since ($tstamp, $show_hidden = false, $conf_id_arr = false, $return_thread_ids = false) {
			$fields = 'tx_chcforum_post.uid as uid, tx_chcforum_post.hidden';
			if ($return_thread_ids) $fields.=', tx_chcforum_post.thread_id';
			$table = 'tx_chcforum_post';
			if ($conf_id_arr == true && is_array($conf_id_arr)) {
				$ids_str = implode(',',$conf_id_arr);
				$ids_str = '('.$ids_str.')';
				$addWhere = "tx_chcforum_post.conference_id IN $ids_str";
			} else {
				$addWhere = "tx_chcforum_post.conference_id=$this->uid";				
			}
			$addWhere.= " AND crdate > $tstamp";
			$where = tx_chcforum_shared::buildWhere($table,$addWhere,$show_hidden);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			if ($results) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
					if ($this->user->show_hidden == true or $show_hidden = true) {
						if ($return_thread_ids) {
							$tmp = array();
							$tmp['thread_uid'] = $row['thread_id'];
							$tmp['post_uid'] = $row['uid'];		
							$all_post_uids[] = $tmp;			
						} else {
							$all_post_uids[] = $row['uid'];					
						}
					} else {
						if ($row['hidden'] == false) {	
							if ($return_thread_ids) {
								$tmp = array();
								$tmp['thread_uid'] = $row['thread_id'];
								$tmp['post_uid'] = $row['uid'];					
								$all_post_uids[] = $tmp;			
							} else {
								$all_post_uids[] = $row['uid'];					
							}
						}
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
			}
			if (!empty($all_post_uids)) {
				return $all_post_uids;
			} else {
				return false;
			}						
		}
		
		function return_all_post_ids ($show_hidden = false,$conf_id_arr = false) {
			if ($conf_id_arr == true && is_array($conf_id_arr)) {
				$ids_str = implode(',',$conf_id_arr);
				$ids_str = '('.$ids_str.')';
				$addWhere = "tx_chcforum_post.conference_id IN $ids_str";
			} else {
				$addWhere = "tx_chcforum_post.conference_id=$this->uid";				
			}
			$fields = 'tx_chcforum_post.uid as uid, tx_chcforum_post.hidden';
			$table = 'tx_chcforum_post';
			$where = tx_chcforum_shared::buildWhere($table,$addWhere,$show_hidden);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			if ($results) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
					if ($this->user->show_hidden == true or $show_hidden = true) {
						$all_post_uids[] = $row['uid'];					
					} else {
						if ($row['hidden'] == false) {	
							$all_post_uids[] = $row['uid'];
						}
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
			}
			if (!empty($all_post_uids)) {
				return $all_post_uids;
			} else {
				return false;
			}			
		}
 	   /**
 	    * Returns all thread uids for this conference.
		*
		* @return array  array containing all thread uids for this conference.
		*/		
		function return_all_thread_ids ($show_hidden = false) {
				$order_by = 'thread_attribute DESC, ';
				switch ($this->fconf['thread_sorting']) {
					case "crdate_desc":
						$order_by .= 'tx_chcforum_thread.crdate DESC';
					break;
					case "crdate_asc":
						$order_by .= 'tx_chcforum_thread.crdate DESC';
					break;
					case "asc":
						$order_by .= 'tx_chcforum_thread.tstamp ASC';
					break;
					default:
					case "desc":
						$order_by .= 'tx_chcforum_thread.tstamp DESC';
					break;
				}
			$now = time();
			$fields = 'DISTINCT tx_chcforum_thread.uid as uid, tx_chcforum_post.hidden as posthidden, tx_chcforum_thread.crdate, thread_attribute';
			$addWhere = "tx_chcforum_thread.conference_id=$this->uid AND (tx_chcforum_thread.endtime >= $now OR tx_chcforum_thread.endtime <= 0)";

			$table = 'tx_chcforum_thread';
			$where = tx_chcforum_shared::buildWhere($table,$addWhere,$show_hidden);

			$table = 'tx_chcforum_thread LEFT JOIN tx_chcforum_post on tx_chcforum_post.thread_id = tx_chcforum_thread.uid';

			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			if ($results) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
					if ($this->user->show_hidden == true) {
						$thread_uid[] = $row['uid'];					
					} else {
						if ($row['posthidden'] == false) $thread_uid[] = $row['uid'];
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
			}
			if (!empty($thread_uid)) {
				// remove double IDs -- thanks to AK for catching this.
				// it would be nice to remove these in the query, but distinct
				// returns distinct rows, not distinct fields, and we actually need
				// to get both the hidden and unhidden post/row. Use array_values
				// to preserve the consecutive keys.
				$tmp = array_unique($thread_uid);
   			$thread_uid = array_values($tmp);
				return $thread_uid;
			} else {
				return false;
			}
		}
		 
		/**
		* Returns the category name for this conference
		*
		* @return string  category name
		*/
		function return_cat_name () {
			$tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
			$cat = new $tx_chcforum_category($this->cat_id);
			return $cat->cat_title;
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_conference.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_conference.php']);
	}
	 
?>
