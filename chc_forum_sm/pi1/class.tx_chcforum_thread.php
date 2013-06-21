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
	* Thread class contains the attributes and methods for threads in the
	* chc_forum extension.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_thread extends tx_chcforum_pi1 {
		 
		var $uid;
		var $pid;
		var $tstamp;
		var $crdate;
		var $cruser_id;
		var $deleted;
		var $hidden;
		var $category_id;
		var $conference_id;
		var $thread_closed;
		var $thread_attribute;
		var $thread_subject;
		var $thread_author;
		var $thread_datetime;
		var $thread_views;
		var $thread_replies;
		var $thread_firstpostid;
		var $thread_lastpostid;
		var $cObj;
	
	 /**
	  * Thread object constructor. Constructs the object from a DB record.
		*
		* @param integer  $thread_id: the thread uid.
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return void
		*/	 
		function tx_chcforum_thread ($thread_id = false, $cObj) {
			$this->cObj = $cObj;
			$this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;
			#if (intval(phpversion()) < 5) unset($this->cObj->fconf);

			// bring in the user object.
			$this->user = $this->fconf['user'];

			$this->internal['results_at_a_time'] = 1000;
			if (!$thread_id) {
				return;
			}
			
			$addWhere = "uid=$thread_id";
			$table = 'tx_chcforum_thread';
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
			}
			
			// check if user can see hidden
			if (is_object($this->user) && $this->user->can_mod_conf($this->conference_id)) {
				$this->user->show_hidden = 1;	
			}

		}
		 
	 /**
	  * Returns the number of posts in this thread. Set Replies to 1 if you want to find out how 
	  * many replies there are (replies = total posts - 1) instead of how many posts there are.
	  *
	  * @param boolean  $replies: set to 1 if you want total replies, set to 0 if you want total posts.
		* @return integer  count of posts or replies in the thread.
	  */
		function return_post_count ($replies = 0) {

			$addWhere = "thread_id=$this->uid";
			$table = 'tx_chcforum_post';
			$fields = 'count(*) AS count';
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results);
			$GLOBALS['TYPO3_DB']->sql_free_result($results);
			$count = $row['count'];

			if ($replies == 1) $count = $count - 1;
			if ($count > 0) {
				return $count;
			} else {
				return 0;
			}
		}
		
		function return_all_post_ids_since ($tstamp, $show_hidden = false) {
			if (isset($this->cObj) && isset($this->uid)) {
				switch ($this->fconf['post_sorting']) {
					case "desc":
						$order_by = 'crdate DESC';
					break;
					case "asc":
					default:
						$order_by = 'crdate ASC';
					break;
				}			

				$addWhere = "thread_id=$this->uid AND crdate > $tstamp";
				$table = 'tx_chcforum_post';
				$fields = 'uid';

				$where = tx_chcforum_shared::buildWhere($table,$addWhere,$show_hidden);

				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
				$query = $GLOBALS['TYPO3_DB']->SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
				if ($results) {
					while ($row = mysql_fetch_assoc($results)) {
						$post_uids[] = $row['uid'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($results);
				}
				return $post_uids;
			} else {
				return false;
			}
		}
		
		 
	 /**
		* Used to get all the post ids for this thread.
		*
		* @return array  contains all post ids for this thread.
		*/
		function return_all_post_ids ($show_hidden = false) {
			if (isset($this->cObj) && isset($this->uid)) {
				switch ($this->fconf['post_sorting']) {
					case "desc":
						$order_by = 'crdate DESC';
					break;
					case "asc":
					default:
						$order_by = 'crdate ASC';
					break;
				}

				$addWhere = "thread_id=$this->uid";
				$table = 'tx_chcforum_post';
				$fields = 'uid';

				$where = tx_chcforum_shared::buildWhere($table,$addWhere,$show_hidden);

				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
				$query = $GLOBALS['TYPO3_DB']->SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
				if ($results) {
					while ($row = mysql_fetch_assoc($results)) {
						$post_uids[] = $row['uid'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($results);
				}
				return $post_uids;
			} else {
				return false;
			}
		}
		 
	 /**
	  * Returns the id of the most recent post in this thread.
		*
		* @return integer  uid of the most recent post in the thread.
		*/
		function return_most_recent_post () {
			if ($this->uid) {
				$table = 'tx_chcforum_post';
				$fields = 'uid';
				$order_by = 'crdate DESC';
				$addWhere = "thread_id=$this->uid";
				$where = tx_chcforum_shared::buildWhere($table,$addWhere);	
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

				if ($results) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results);
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
				return $row['uid'];
			}
		}
		 
	 /**
		* Returns the name of the author (starter) of this thread.
		*
		* @return string  name of the author (starter) of the thread.
		*/
		function return_thread_author_name() {
			// try to get the author from the post author (most up-to-date info)
			$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
			$post = new $tx_chcforum_post($this->thread_firstpostid, $this->cObj);
			if ($post->post_author) {
				$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
				$author = new $tx_chcforum_author($post->post_author, $this->cObj);
				return $author->return_name_link();
			// if the post author was deleted, use the stored author name
			} elseif ($post->post_author_name) {
				return $post->post_author_name;
			// if the first post no longer exists, just use the user record that was
			// stored when the thread was created.
			} else {
		  		// create author object
		  		$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
		  		$this->author = new $tx_chcforum_author($this->thread_author, $this->cObj);
				return $this->author->return_name_link();
			}
		}
		 
	 /**
		* Returns the data needed for a thread row to be displayed in the single conference view.
		*
		* @return array array containing the data needed to display this thread. Contains keys 'new_posts', 'thread_replies', 'thread_author', 'thread_last'.
		*/
		function return_thread_row_data () {
			$recent_post_id = $this->thread_lastpostid;
			if ($this->cached_last_post_id == $recent_post_id && !empty($this->cached_last_post_info)) {
				// display cached info.
				$output_array = unserialize($this->cached_last_post_info);				
				if (is_array($output_array)) {
					return $output_array;
				} else {
					// generate info and cache it -- this probably should not
					// ever happen here.
					$output_array = $this->generate_thread_row_data($recent_post_id);
					$this->cache_thread_row_data($output_array, $recent_post_id);
					return $output_array;				
				}
			} else {
				// generate info and cache it.		
				$output_array = $this->generate_thread_row_data($recent_post_id);
				$this->cache_thread_row_data($output_array, $recent_post_id);
				return $output_array;
			}
		}

		function generate_thread_row_data($recent_post_id) {
			$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
			$recent_post = new $tx_chcforum_post($recent_post_id, $this->cObj);
			 
			// Make the new $tx_chcforum_post message, if neccessary.
			if ($this->new_cnt > 0) {
				if ($this->new_cnt > 1) {
					$label = tx_chcforum_shared::lang('return_thread_data_new_plrl');
				} else {
					$label = tx_chcforum_shared::lang('return_thread_data_new_sing');
				}
				$new_posts = '['.$this->new_cnt.' '.$label.']';
			}
			$output_array = array (
				'thread_image' => $this->return_thread_image($this->new_cnt),	// code contributed by Ralf Sobbe
				'thread_subject' => $this->thread_link(),
				'new_posts' => $new_posts,
				'thread_replies' => $this->return_post_count(1),
				'thread_author' => $this->return_thread_author_name(),
				'thread_last' => $recent_post->return_post_info($view = 'single_conf'));
			return $output_array;			
		}
		
		function cache_thread_row_data($output_array, $post_id) {
			$data_arr['cached_last_post_info'] = serialize($output_array);
			$data_arr['cached_last_post_id'] = $post_id;
			$where = "uid = $this->uid";
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_chcforum_thread',$where,$data_arr);
			return true;
		}


		function return_thread_hide_btn() {
			if ($this->uid) {
				$params = array ('view' => 'single_conf',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->uid,
				);				
				if ($this->hidden == true) {
					$params['flag'] = 'unhide_thread';
					$img = 'unhide';
				} else {
					$params['flag'] = 'hide_thread';				
					$img = 'hide';
				}
				$img_path = $this->fconf['tmpl_img_path'].$img.'.'.$this->fconf['image_ext_type'];
				$title = '<img alt="'.tx_chcforum_shared::lang('btn_'.$img).'" border="0" src="'.$img_path.'">';
				$attr = 'title="'.tx_chcforum_shared::lang('btn_'.$img).'"';
				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);
				return $out;
			}	else {
				return false;
			}			
		}

		/**
		 * Show images in thread list. This depends on thread_closed and thread_attribute
		 */
		function return_thread_image($new_posts) {
  		// init
  		$file_name="thread.".$this->fconf['image_ext_type'];
  		  
  		//1. check thread_closed
  		if($this->thread_closed) {
  		  $file_name="thread_closed.".$this->fconf['image_ext_type'];
		  }
  		else {
    		//2. hot thread.
    		if($this->return_post_count(0) >= $this->fconf['hot_thread_cnt']) {
      		// thread with new post
      		if($new_posts > 0) 
      		  $file_name="thread_hot_new.".$this->fconf['image_ext_type'];
      		// only hot
      		else 
      		  $file_name="thread_hot.".$this->fconf['image_ext_type'];
    		}
    		else {
      		// 3. not hot but new post
          if($new_posts > 0)
  		      $file_name="thread_new.".$this->fconf['image_ext_type'];
    		}
		  }
			$this->tmpl_img_path = $this->fconf['tmpl_img_path'];
  		$image ="<img src=\"".$this->tmpl_img_path.$file_name."\">";
  		return $image;
	  }

		
	 /** 
		* Returns title of the thread wrapped in a link
		*
		* @return string  title of thread wrapped in link to view of this thread.
		*/		
		function thread_link($short = false) {
			$params = array ('view' => 'single_thread',
				'cat_uid' => $this->category_id,
				'conf_uid' => $this->conference_id,
				'thread_uid' => $this->uid);
			if ($short == true && $this->fconf['subject_trim']) {
				if (strlen($this->thread_subject) >= $this->fconf['subject_trim']) {
					$title = substr($this->thread_subject,0,$this->fconf['subject_trim']).'...';				
				} else {
					$title = $this->thread_subject;
				}
			} else {
				$title = $this->thread_subject;
			}
			// set the prefix var for special threads.
			$prefix = "";
			// get prefix for closed, sticky, and closed sticky.
			if($this->thread_attribute > 0) $prefix = tx_chcforum_shared::lang('thread_sticky');
			if($this->thread_closed) $prefix = tx_chcforum_shared::lang('thread_closed');
			if($this->thread_closed && $this->thread_attribute > 0) $prefix = tx_chcforum_shared::lang('thread_sticky');
			$out = $prefix.tx_chcforum_shared::makeLink($params, $title);
			return $out;
		}
		
		function update_self($data_arr) {
			if ($this->uid && is_array($data_arr)) {
				$where = "uid = $this->uid";
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_chcforum_thread',$where,$data_arr);
				foreach ($data_arr as $attr => $value) {
					$this->$attr = $value;
				}				
			}
		}

		function unhide() {
			if ($this->user->can_mod_conf($this->conference_id)) {
				$data_arr['hidden'] = 0;
				$this->update_self($data_arr);
			}
		}
		
		function hide() {
			if ($this->user->can_mod_conf($this->conference_id)) {
				$data_arr['hidden'] = 1;
				$this->update_self($data_arr);
			}			
		}


		function close_thread() {
			if ($this->uid) {
				if ($this->user->can_mod_conf($this->conference_id)) {
					$data_arr['thread_closed'] = 1;
					$this->update_self($data_arr);
				}
			}
		}
		
		function open_thread() {
			if ($this->uid) {
				if ($this->user->can_mod_conf($this->conference_id)) {
					$data_arr['thread_closed'] = 0;
					$this->update_self($data_arr);
				}
			}
		}


	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_thread.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_thread.php']);
	}
	
?>
