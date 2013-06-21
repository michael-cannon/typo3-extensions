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
	* Plugin 'CHC Forum' for the 'chc_forum' extension. This class acts as
	* the interface between the forum and typo3. Typo3 instatiates this class, which
	* really isn't much more than a gateway between Typo3 and the display class.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_search extends tx_chcforum_pi1 {

		function tx_chcforum_search ($s_query = false, $cObj) {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;
			#if (intval(phpversion()) < 5) unset($this->cObj->fconf);

			// bring in the user object.
			$this->user = $this->fconf['user'];


			$this->internal['results_at_a_time'] = 1000;
			$this->tmpl_path = tx_chcforum_shared::setTemplatePath();
			if ($s_query) $this->s_query = $s_query;
		}

		function display() {
			if ($this->s_query[submit]) {
				$this->html_out = $this->display_search_results();
			} else {
				$this->html_out.= $this->display_search_form();
			}
			return $this->html_out;		
		}

		function display_search_results() {
			// because there are multiple pages, we need to pass the search query...
			$original_query = serialize($this->s_query);
			if ($this->validate_s_query() == true) {
				$uid_list = $this->build_exec_search_query();
				if (is_array($uid_list['uids'])) {
					$uid_list['query'] = $original_query;
					return $uid_list;
				} else {
					$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
					$no_new_msg = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('search_no_results'), 'message');
					$out .= $no_new_msg->display();
					return $out;
				}
			}
		}

		function build_exec_search_query() {			
			// we'll only build this query if the s_query params have been validated.
			if ($this->s_query['valid'] == true) {
				$fields = 'uid,thread_id';
				
				// figure out where we're searching
				$build['fields'] = explode(',',$this->s_query['post_fields']);
				
				// build keyword where
				if ($this->s_query['keywords']['value']) {
					switch ($this->s_query['keywords']['exact']) {
						case true:
							// exact keyword phrase
							$this->s_query['keywords']['value'] = trim($this->s_query['keywords']['value']);
							// create where for all fields
							foreach($build['fields'] as $v) { // this must be an array...see l62
								$tmp_where[] = $v.' LIKE "%'.$this->s_query['keywords']['value'].'%"';
							}
							$where[] = '('.implode(' OR ',$tmp_where).')';
							unset($tmp_where);
	
						break;
						case false:
						default:
							// explode list by spaces into distinct words
							$a = explode(' ',$this->s_query['keywords']['value']);
							if (is_array($a)) {
								foreach ($a as $k => $v) {
									$a[$k] = trim($v);
								}
							}
							$this->s_query['keywords']['value'] = $a;
							// create where for all fields
							$kword_count = count($this->s_query['keywords']['value']);
							foreach($this->s_query['keywords']['value'] as $k_v) { // this must be an array...see l62
								foreach($build['fields'] as $f_v) { // we have field value and keyword value now
									$tmp_where[] = $f_v.' LIKE "%'.$k_v.'%"';
								}
							$where[] = '('.implode(' OR ',$tmp_where).')';
							unset($tmp_where);
							}							
						break;
					}
				}

				// build uname where
				if ($this->s_query['uname']['value']) {
					switch ($this->s_query['uname']['exact']) {
						case true:
							// exact keyword phrase
							$this->s_query['uname']['value'] = trim($this->s_query['uname']['value']);
							// create where for all fields
							$where[] = 'fe_users.username LIKE "%'.$this->s_query['uname']['value'].'%"';
						break;
						case false:
						default:
							// explode list by spaces into distinct words
							$a = explode(' ',$this->s_query['uname']['value']);
							if (is_array($a)) {
								foreach ($a as $k => $v) {
									$a[$k] = trim($v);
								}
							}
							$this->s_query['uname']['value'] = $a;
							foreach($this->s_query['uname']['value'] as $uname) { // we have field value and keyword value now
								$tmp_where[] = 'fe_users.username LIKE "%'.$uname.'%"';
							}
							$where[] = '('.implode(' OR ',$tmp_where).')';
							unset($tmp_where);
						break;					
					}
				}

				// build age where
				if ($this->s_query['post_age'] > 0) {
					$where[] = 'tx_chcforum_post.crdate > '.$this->s_query['post_age'];
				}
				
				// build conference / cats where
				if (is_array($this->s_query['where']['cats'])) {
					foreach ($this->s_query['where']['cats'] as $v) {
						$where[] = 'tx_chcforum_post.category_id = '.$v;
					}
				}
				if (is_array($this->s_query['where']['confs'])) {
					foreach ($this->s_query['where']['confs'] as $v) {
						$where[] = 'tx_chcforum_post.conference_id = '.$v;
					}
				}
											
				// got the where -- now set the fields, etc.
				switch ($this->s_query['display_results']) {
					case 'threads':
						$fields = 'DISTINCT tx_chcforum_post.thread_id AS uid, tx_chcforum_post.conference_id';						
						$where[] = 'tx_chcforum_thread.deleted != 1';
						$tables = 'tx_chcforum_post LEFT JOIN fe_users ON tx_chcforum_post.post_author = fe_users.uid LEFT JOIN tx_chcforum_thread ON tx_chcforum_post.thread_id = tx_chcforum_thread.uid';
					break;
					case 'posts':
					default:
						$fields = 'tx_chcforum_post.uid AS uid, conference_id';
						$where[] = 'tx_chcforum_post.deleted != 1';
						$tables = 'tx_chcforum_post LEFT JOIN fe_users ON tx_chcforum_post.post_author = fe_users.uid';
					break;
				}

				// where array is complete at this point -- let's make the 
				// SQL statement out of it now.
				if(is_array($where)) {
					$where = implode(' AND ',$where);
				}
				
				$order_by = 'tx_chcforum_post.crdate DESC';
		
				$query = $GLOBALS['TYPO3_DB']->SELECTquery($fields,$tables,$where,'',$order_by);				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tables,$where,'',$order_by);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					if ($this->user->can_read_conf($row[conference_id]) == true) {
						$s_results[uids][] = $row['uid'];
					}
				}
				if (is_array($s_results[uids])) $s_results[display_results] = $this->s_query['display_results'];
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				return $s_results;
			}
		}

		function validate_s_query() {
			// simplify submit...probably not necessary...
			if ($this->s_query['submit']) $this->s_query['submit'] = true;

			// handle keywords
			// max length of keywords seach is 200 chars
			$this->s_query['keywords'] = substr($this->s_query['keywords'], 0, 200);
			if ($this->s_query['keyword_exact']) {
				$keywords['value'] = $this->s_query['keywords'];
				$keywords['exact'] = true;
			} else {
				$keywords['value'] = $this->s_query['keywords'];
				$keywords['exact'] = false;		
			}
			$this->s_query['keywords'] = $keywords;
			unset ($this->s_query['keyword_exact']);
			
			// handle uname filter
			// max length of uname filter is 200 chars
			$this->s_query['uname'] = substr($this->s_query['uname'], 0, 200);
			if ($this->s_query['uname_exact']) {
				$uname['value'] = $this->s_query['uname'];
				$uname['exact'] = true;
			} else {
				$uname['value'] = $this->s_query['uname'];
				$uname['exact'] = false;		
			}
			$this->s_query['uname'] = $uname;
			unset ($this->s_query['uname_exact']);

			
			// make sure that the user has read access to all the conferences
			if (is_array($this->s_query['where'])) {
				foreach ($this->s_query['where'] as $k => $v) {				
					if ($v == '*') {
						unset($this->s_query['where']); // unset where if all cats and confs was selected.
						break;
					}
					$v = explode('_',$v);
					switch ($v[0]) {
						case 'cat':
							$cats[] = $v[1];
						break;
							
						case 'conf':
							$confs[] = $v[1];
						break;
						
						default:
						break;						
					}
				}				
			}
			if (is_array($cats)) {
				foreach ($cats as $k => $v) {
					if ($this->user->can_read_cat($v) != true) unset ($cats[$k]);
				}
			}
			if (is_array($confs)) {
				foreach ($confs as $k => $v) {
					if ($this->user->can_read_conf($v) != true) unset($confs[$k]);
				}		
			}
			unset($this->s_query['where']);
			// read access for cats and confs has been checked, so we can recreate
			// the where array at this point, so that it's more query friendly.
			$this->s_query['where']['cats'] = $cats;
			$this->s_query['where']['confs'] = $confs;

			// set age timestamp
			$allowed = array(1,7,30,60,90,180,356,0);
			if (in_array($this->s_query['post_age'],$allowed) == true) {
				if ($this->s_query['post_age'] != 0) {
					$this->s_query['post_age'] = time() - ($this->s_query['post_age'] * 24 * 60 * 60);
				}
			} else {
				$this->s_query['post_age'] = 0;
			}

			// set search fields
			switch ($this->s_query['post_fields']) {
				case '2':
					$this->s_query['post_fields'] = 'tx_chcforum_post.post_subject';
				break;				
				case '1':
				default:
					$this->s_query['post_fields'] = 'tx_chcforum_post.post_text,tx_chcforum_post.post_subject';
				break;
			}
			
			// set display type
			switch ($this->s_query['display_results']) {
				case '2':
					$this->s_query['display_results'] = 'threads';
				break;				
				case '1':
				default:
					$this->s_query['display_results'] = 'posts';
				break;
			}
			
			$this->s_query[valid] = true;
			return true;
		}

		function display_search_form() {
			// populate labels array
			$labels = $this->pop_search_labels();
			
			// create the search[where] html string
			$options_arr[] = '<option selected="selected" value="*">'.tx_chcforum_shared::lang('search_all_cats').'</option>';

			$cat_ids = tx_chcforum_category::get_all_cat_ids($this->cObj);
			if (is_array($cat_ids)) {
				foreach ($cat_ids as $cat_uid) {
					$tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
					$cat = new $tx_chcforum_category($cat_uid, $this->cObj);
					$cat_opt = '<option value="cat_'.$cat_uid.'">'.$cat->return_title().'</option>';
					$tree[$cat_opt] = $cat->return_conf_titles();
				}
				foreach ($tree as $k => $v) {
					if (is_array($v)) {
						$options_arr[] = $k;
						foreach ($v as $key => $value) {
							$options_arr[] = $value;
						}
					}
				}				
				if(is_array($options_arr)) {
					$where_options_str = implode("\n",$options_arr);
					unset($options_arr);
				}				
			}

			// create post_age html string.
			$options = array( '1,today',
												'7,7_days',
												'30,30_days',
												'60,60_days',
												'90,90_days',
												'180,180_days',
												'356,365_days',
												'0,any_day');
			foreach ($options as $v) {
				if (isset($selected)) unset($selected);
				$v = explode(',',$v);
				$val = $v[0];
				$lbl = $v[1];
				$lbl = tx_chcforum_shared::lang('search_'.$lbl);
			if ($v[0] == '90') $selected = ' selected="selected"';
				$age_options_str.= '<option value="'.$val.'"'.$selected.'>'.$lbl.'</option>';
			}						

			// setup the templates
			$tmpl = new tx_chcforum_tpower($this->tmpl_path.'search_form.tpl');
			$tmpl->prepare();

			// rg: changed to add external GETvars to the form url
			// used for ttnews / forum integration. Thanks Rupi!
			if ($this->conf['chcAddParams']) {
    			$paramArray = tx_chcforum_shared::getAddParams($this->conf['chcAddParams']);
			}

			// populate the template
			$tmpl->assign($labels);
			$tmpl->assign('action',htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id)));
			$tmpl->assign('where_options',$where_options_str);
			$tmpl->assign('age_options',$age_options_str);
			$this->html_out .= $tmpl->getOutputContent();
		}

		function pop_search_labels() {
			// list of labels
			$fields = array(
				'simple_search_hdr',
				'adv_search_hdr',
				'submit_search_hdr',
				'search_kw',
				'search_uname',
				'exact_match',
				'search_cats',
				'post_age',
				'post_fields',
				'display_results',
				'submit_inpt',
				'post_fields_all',
				'post_fields_titles',
				'display_posts',
				'display_threads',
			);
			foreach ($fields as $k => $v) {
				$string = 'search_'.$v;
				$labels[$v] = tx_chcforum_shared::lang($string);
			}
			return $labels;
		}
	}

	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_search.php']) {
		require_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_search.php']);
	}


	 
?>
