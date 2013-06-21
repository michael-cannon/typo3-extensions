<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Administrator (admin@email.test)
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
* Module 'CHC Forum' for the 'chc_forum' extension.
*
* @author Administrator <admin@email.test>
*/



// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
include_once ('../pi1/class.tx_chcforum_tpower.php');

require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:chc_forum/mod1/locallang.php');

require_once (PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]

class tx_chcforum_module1 extends t3lib_SCbase {
	var $pageinfo;
	
	/**
	* @return [type]  ...
	*/
	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;	
		parent::init();
		/*
		if (t3lib_div::_GP('clear_all_cache')) {
		$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		} 
		*/
	}
	
	
	
	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	* Main function of the module. Write the content to $this->content
	*
	* @return [type]  ...
	*/
	function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		
		// Feel like doing some debugging? Uncomment this line for some help...
		#$this->debug  = 1; // uncomment for debugging;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user

		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);

		$access = 1; // forum doesn't use pids like the other web modules, so we don't need this access check here.

		if ($access)    {
			
			// Set the lang object
			$this->lang = $LANG;
			
			// Place post and get vars in an attribute -- easier to work with.

			$this->post = $GLOBALS['HTTP_POST_VARS'];
			$this->get = $GLOBALS['HTTP_GET_VARS'];
			
			// Debugging...
			if ($this->debug == 1) {
				$this->content .= 'POST:'.t3lib_div::view_array($this->post).'<br />'; // uncomment for debugging.
				$this->content .= 'GET:'.t3lib_div::view_array($this->get).'<br />'; // uncomment for debugging.
			}
						
			$this->post['stage'] = t3lib_div::_GP('stage');
			$this->post['fuid'] = t3lib_div::_GP('fuid');
			$this->post['selection'] = t3lib_div::_GP('selection');
			$this->post['submit'] = t3lib_div::_GP('submit');
			$this->post['cancel'] = t3lib_div::_GP('cancel');
			$this->post['table'] = t3lib_div::_GP('table');
			$this->post['fpid'] = t3lib_div::_GP('fpid');
			$this->post['spid'] = t3lib_div::_GP('spid');
			$this->post['action'] = t3lib_div::_GP('action');

			// Debugging...			
			if ($this->debug == 1) {
				$this->content .= 'COMBINED GET POST:'.t3lib_div::view_array($this->post).'<br />'; // uncomment for debugging.
			}

			// validate $this->post['fuid'] -- if it's not valid, it will be unset.
			$this->validateFuid();
			
			// set the uid of the forum that we'll be working with (from post, then from get).
			$this->fuid = $this->post['fuid'];
			if (empty($this->fuid)) $this->fuid = $this->get['fuid'];
			#if (empty($this->fuid)) $this->fuid = $this->id;
			
			// set the storage_pid ($this->spid) and the forum pid ($this->fpid) based on $this->uid
			if ($this->fuid) {
				$fields = 'tt_content.uid as fuid, tt_content.pid as fpid, tt_content.pi_flexform as flex, tt_content.pages AS sppid, pages.storage_pid as spid';
				$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
				if ($this->fuid) $where = 'tt_content.uid='.$this->fuid;
				$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', '');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
				$this->spid = $row['spid'];
				if ($row['sppid']) $this->spid = $row['sppid'];
				$this->fpid = $row['fpid'];
			}
			
			// get the forum conf from the xml. Store it in $this->fconf
			if ($row['flex']) {
				$flex_arr = t3lib_div::xml2array($row['flex']);
				foreach ($flex_arr['data'] as $sheet) {
	 				foreach ($sheet[lDEF] as $attr => $value) {
						$flex_parsed[$attr] = $value['vDEF'];
					}				
				}
				$this->fconf = $flex_parsed;
			}

			// Set stage -- we prefer a stage sent via post over one sent via get.
			$this->stage = $this->post['stage'];
			if (empty($this->stage)) $this->stage = $this->get['stage'];
			
			// _If_ the cancel button was pressed, at any point, we want to unset the post vars in $this->post. This
			// Gets rid of everything (so, cancelling just bumps you back to right after the forum selection menu). 
			// We'll set the stage to forum, too. Everything after this won't be set since $this->post has been emptied out.
			if (isset($this->post['cancel'])) {
				unset($this->post);
				$this->stage = 'forum';
			}
			
			// If a record has been selected to be modified, this rec_uid variable will be set.
			if ($this->post['rec_uid']) $this->rec_uid = $this->post['rec_uid'];
			// Once a selection has been made, an action is chosen -- this keeps track of the action,
			// which is generally add, delete, or modify.
			if ($this->post['action']) $this->action = $this->post['action'];
			// Selection tells us what table we're editing -- are we working with confs, cats, fgroups, etc..
			// If we know this, we can set some constants such as table names, icons, templates, etc.
			$this->selection = $this->post['selection'];
			if ($this->selection) {
				switch ($this->selection) {
					case 'mng_cats':
					$this->header['header'] = $this->lang->getLL('ad_cats');
					if ($this->action) $this->header['header'] .= ': '.$this->lang->getLL($this->action);
						$this->header['sH'] = true;
					$this->table = 'tx_chcforum_category';
					$this->form_tmpl = 'templates/add_cat.tpl';
					$this->icon = '../icons/icon_tx_chcforum_cat.gif';
					$this->icon_h = '../icons/icon_tx_chcforum_cat__h.gif';
					break;
					
					case 'mng_confs':
					$this->header['header'] = $this->lang->getLL('ad_confs');
					if ($this->action) $this->header['header'] .= ': '.$this->lang->getLL($this->action);
						$this->header['sH'] = true;
					$this->table = 'tx_chcforum_conference';
					$this->form_tmpl = 'templates/add_conf.tpl';
					$this->icon = '../icons/icon_tx_chcforum_cnf.gif';
					$this->icon_h = '../icons/icon_tx_chcforum_cnf__h.gif';
					
					break;
					case 'mng_fgroups':
					$this->header['header'] = $this->lang->getLL('ad_fgroups');
					if ($this->action) $this->header['header'] .= ': '.$this->lang->getLL($this->action);
						$this->header['sH'] = true;
					$this->table = 'tx_chcforum_forumgroup';
					$this->form_tmpl = 'templates/add_grp.tpl';
					$this->icon = '../icons/icon_tx_chcforum_fgrp.gif';
					$this->icon_h = '../icons/icon_tx_chcforum_fgrp__h.gif';
					break;
					
					case 'db_clean':
					$this->header['header'] = $this->lang->getLL('db_clean');
					if ($this->action) $this->header['header'] .= ': '.$this->lang->getLL($this->action);
						$this->header['sH'] = true;
					$this->action = 'db_clean';
					break;	
				}
			} else {
				$this->header['header'] = $this->lang->getLL('msg_selection_select');
			}
			
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="index.php" method="POST" enctype="application/x-www-form-urlencoded">';
			
			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL) {
				document.location = URL;
				}
				</script>
				';
			$this->doc->postCode = '
				<script language="javascript" type="text/javascript">
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
				';
			$this->doc->inDocStylesArray[] = '
				.genTable {border: 1px solid black; padding:0; margin:0;}
				.genRowLt {margin:0; padding: 0px; background-color: #e3dfdb;}
				.genRowDk {font-weight: bold; margin:0; padding: 3px; background-color: #9BA1A8};
				.genTable td  {margin:0; padding: 0px;}
				.listRowLt {margin:0; padding: 1px; background-color: #e3dfdb;}
				.listRowDk {margin:0; padding: 1px; background-color: #F6F2E6};
				';

			// sets the title tag for the page
			$this->content .= $this->doc->startPage($LANG->getLL('title'));

			// creates the pages header
			$this->content .= $this->doc->header($LANG->getLL('title'));
			
			// Output the forum selection menu and a divider
			$this->content .= $this->doc->section($this->lang->getLL('msg_forum_select'), $this->forumSelectMenu(), false, true, 0);

			$this->content .= $this->doc->divider(5);
			
			// Render content:
			if ($this->fuid != null) $this->content .= $this->doc->section($this->header['header'], $this->control(), true, $this->header['sH']);
			if ($this->message) if ($this->fuid != null) $this->content .= $this->doc->section('Results', $this->message, true, $this->header['sH']);
			
			
			// ShortCut
			if ($BE_USER->mayMakeShortcut()) {
				$this->content .= $this->doc->spacer(20).$this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}
			
			$this->content .= $this->doc->spacer(10);
		} else {
			// If no access or if ID == zero
			
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function validateFuid() {
		if ($this->post['fuid']) {
			$fields = 'tt_content.pid, tt_content.uid';
			$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
			$where = 'tt_content.list_type="chc_forum_pi1" AND tt_content.uid='.$this->post['fuid'];
			$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', '');
	 		if ($qresults) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
				if ($row) $valid = true;
			if (!$row) unset($this->post['fuid']); // if the query doesn't return anything, the fuid is bogus and we're unsetting it.
		}
	}


	function validatePost() {
		if ($this->post['stage'] = 'validate' && isset($this->post['submit'])) {
			// Setup the query
			switch ($this->action) {
				case 'addfgrp':
				case 'modfgrp':
				if (!empty($this->post['fgrp_title'])) {
					$data_arr['forumgroup_groups'] = '';
					$data_arr['forumgroup_users'] = '';
					
					$data_arr['hidden'] = $this->post['fgrp_hide'];
					$data_arr['forumgroup_title'] = $this->post['fgrp_title'];
					$data_arr['forumgroup_desc'] = $this->post['fgrp_desc'];
					if ($this->post['fgrp_groups']) $data_arr['forumgroup_groups'] = implode(',', $this->post['fgrp_groups']);
					if ($this->post['fgrp_users']) $data_arr['forumgroup_users'] = implode(',', $this->post['fgrp_users']);
					$valid = true;
				} else {
					if (!empty($this->post['fg_title'])) ; // missing title
					$valid = false;
				}
				if ($this->rec_uid) {
					$where = 'uid='.$this->rec_uid;
					$valid = true;
				}
				break;
				
				case 'addconf':
				case 'modconf':
				if (!empty($this->post['conf_title']) && !empty($this->post['conf_cat'])) {
					$data_arr['auth_forumgroup_rw'] = '';
					$data_arr['auth_feuser_mod'] = '';
					$data_arr['auth_forumgroup_attach'] = '';
					
					$data_arr['hidden'] = $this->post['conf_hide'];
					$data_arr['cat_id'] = $this->post['conf_cat'];
					$data_arr['conference_name'] = $this->post['conf_title'];
					$data_arr['conference_desc'] = $this->post['conf_desc'];
					$data_arr['conference_allow_user_edits'] = $this->post['conf_user_edits'];
					$data_arr['conference_public_r'] = $this->post['conf_publ_r'];
					$data_arr['conference_public_w'] = $this->post['conf_publ_w'];
					if ($this->post['conf_fgaccess']) $data_arr['auth_forumgroup_rw'] = implode(',', $this->post['conf_fgaccess']);
					if ($this->post['conf_users']) $data_arr['auth_feuser_mod'] = implode(',', $this->post['conf_users']);
					if ($this->post['conf_fgattach']) $data_arr['auth_forumgroup_attach'] = implode(',', $this->post['conf_fgattach']);
					$valid = true;
				} else {
					if (!empty($this->post['conf_cat'])) ; // missing category
					if (!empty($this->post['conf_title'])) ; // missing title
				}
				if ($this->rec_uid) {
					$where = 'uid='.$this->rec_uid;
					$valid = true;
				}
				break;
				
				case 'addcat':
				case 'modcat':
				if (!empty($this->post['cat_title'])) {
					$data_arr['hidden'] = $this->post['cat_hide'];
					$data_arr['cat_title'] = $this->post['cat_title'];
					$data_arr['cat_description'] = $this->post['cat_desc'];
					$data_arr['fe_group'] = $this->post['cat_grp'];
					if ($this->post['cat_fgaccess']) {
						$data_arr['auth_forumgroup_rw'] = implode(',', $this->post['cat_fgaccess']);
					} else {
						$data_arr['auth_forumgroup_rw'] = '';
					}
					$valid = true;
				} else {
					if (!empty($this->post['cat_title'])) ; // missing title
				}
				if ($this->rec_uid) {
					$where = 'uid='.$this->rec_uid;
					$valid = true;
				}
				break;
				
				case 'delcat':
				case 'delconf':
				case 'delfgrp':
				if ($this->rec_uid) {
					$where = 'uid='.$this->rec_uid;
					$valid = true;
				}
				break;
								
				case 'db_clean':
				$valid = true;
				break;
				
				default:
				return;
				break;
			}
			
			if ($this->debug == 1) {
				print '$where for validation query is: '.$where.'<br />';
				print '$data_arr for validation query is:<br />';
				debug($data_arr);
			}
			
			if ($data_arr) {
				foreach ($data_arr as $key => $value) {
					$data_arr[$key] = stripslashes ($value);
				}
			}
			
			// Execute the query
			if ($valid == true) {
				switch ($this->action) {
					case 'addcat':
					case 'addconf':
					case 'addfgrp':
						$data_arr['pid'] = $this->spid;
						$data_arr['tstamp'] = time();
						$data_arr['crdate'] = time();
						$data_arr['cruser_id'] = $this->be_user_uid;
						$data_arr['deleted'] = 0;
						$GLOBALS['TYPO3_DB']->debug($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table, $data_arr));
						$this->message .= $this->lang->getLL('message_add_success');
					break;
										
					case 'delcat':
					case 'delconf':
					case 'delfgrp':
						$GLOBALS['TYPO3_DB']->debug($GLOBALS['TYPO3_DB']->exec_DELETEquery($this->table, $where));
						$this->db_clean();
						$this->message .= $this->lang->getLL('message_del_success');
					break;
				
					case 'modcat':
					case 'modconf':
					case 'modfgrp':
						$GLOBALS['TYPO3_DB']->debug($GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table, $where, $data_arr));
						$this->db_clean();
						$this->message .= $this->lang->getLL('message_mod_success');					
					break;
									
					case 'db_clean':
						$this->db_clean();
						$content .= $this->selectionMenu();
						return $content;
					break;
				}
				// Might want to output some message saying that the submission was successful
			} else {
							// the submission was not validated. Print error messages
			}
		}
		#$content .= $this->selectForumForm();
		$content .= $this->selectAction();
		return $content;
	}
	
	/**
	* Prints out the module HTML
	*
	* @return [type]  ...
	*/
	function printContent() {
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}
	
	// Stage one -- select the forum that we'll be working with
	function forumSelectMenu() {
		$is_a_forum = false;

		$out = '<select name="fuid" onchange="jumpToUrl(\'index.php?stage=forum&fuid=\'+this.options[this.selectedIndex].value,this);">';
		if ($this->fuid == null) {
			$out .= '<option selected="selected" value="null">Select an instance of the forum plugin:</option>';
		} else {
			$out .= '<option value="null">Select an instance of the forum plugin:</option>';
		}
	
		$fields = 'tt_content.pid, tt_content.uid, tt_content.header, tt_content.pages, pages.storage_pid';
		$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
		$where = 'list_type="chc_forum_pi1" AND tt_content.hidden=0 AND tt_content.deleted=0';
		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', '');
		$GLOBALS['TYPO3_DB']->debug($GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', ''));
	
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
			$is_a_forum = true;
			if (!$row['storage_pid']) $row['storage_pid'] = $row['pages'];
			if (!$row['storage_pid']) $row['storage_pid'] = '0';
		
			if ($this->fuid == $row['uid']) {
				$out .= '<option selected="selected" value="'.$row['uid'].'">HEADER: '.$row['header'].' / STORAGE PID: '.$row['storage_pid'].' / PID: '.$row['pid'].'</option>';
			} else {
				$out .= '<option value="'.$row['uid'].'">HEADER: '.$row['header'].' / STORAGE PID: '.$row['storage_pid'].' / PID: '.$row['pid'].'</option>';
			}
		}

		if ($is_a_forum == false) {
			$out = $this->lang->getLL('instructions');
			return $out;
		}

	
		$out .= '</select>';

		return $out;
	}
	
	// Stage two -- select the table (where) we'll be working with / managing
	function SelectionMenu() {
		$option_array[] = array('uid' => 'mng_cats', 'title' => $this->lang->getLL('ad_cats'));
		$option_array[] = array('uid' => 'mng_confs', 'title' => $this->lang->getLL('ad_confs'));
		$option_array[] = array('uid' => 'mng_fgroups', 'title' => $this->lang->getLL('ad_fgroups'));
		$option_array[] = array('uid' => 'db_clean', 'title' => $this->lang->getLL('db_clean'));
		$selected_arr = array();
		$out = $this->selectInpt('selection', $option_array, $selected_arr, false, 1, false);
		$out .= $this->setHidden('fuid', $this->fuid);
		$out .= $this->setHidden('stage', 'selection');
	
		$out .= '<br /><br /><input type="submit" name="submit" value="'.$this->lang->getLL('submit').'"> <input type="submit" name="cancel" value="'.$this->lang->getLL('cancel').'">';
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function control() {
		if ($this->debug == 1) {
			print 'this->fuid: '.$this->fuid.'<br />';
			print 'this->fpid: '.$this->fpid.'<br />';
			print 'this->spid: '.$this->spid.'<br />';
			print 'this->stage: '.$this->stage.'<br />';
			print 'this->rec_uid: '.$this->rec_uid.'<br />';
			print 'this->selection: '.$this->selection.'<br />';
			print 'this->action: '.$this->action.'<br />';
			print 'this->table: '.$this->table.'<br />';
			print 'this->form_tmpl: '.$this->form_tmpl.'<br />';
			print 'this->icon: '.$this->icon.'<br />';
			print 'this->header: '.$this->header.'<br />';
			print 'this->table: '.$this->table.'<br />';
		}
	
		if ($this->message) $content .= $this->message;
	
		// figure out what to do
		if ($this->post['cancel'] or $this->fuid == 'null') {
			unset($this->post);
		} else {
			switch($this->stage) {
				case 'validate':
				$content .= $this->validatePost();
				break;
				case 'action':
				if (!empty($this->action)) {
					switch ($this->action) {
						case 'addcat':
						case 'addconf':
						case 'addfgrp':
							$content .= $this->listRecs();
						break;
						case 'delcat':
						case 'delconf':
						case 'delfgrp':
						case 'modcat':
						case 'modconf':
						case 'modfgrp':
						if ($this->post['rec_uid']) {
							$content .= $this->displayAddForm();
						} else {
							$content .= $this->listRecs();
						}
						break;
					}
				}
				break;
				case 'selection':
				if (!empty($this->fuid)) $content .= $this->selectAction();
				break;
	
				case 'forum':
				if (!empty($this->fuid)) $content .= $this->selectionMenu();
					break;
				default:
				break;
			}
		}
		return $content;
	}
	
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function db_clean() {
		$icon = $this->doc->icons(-1); 

		// if pruning is enabled, delete any posts that are past their time
		$fconf_age = $this->fconf['pruning_age']; // age in days
		if ($fconf_age > 0) {
			$fconf_age_s = $fconf_age * 86400; // age in secs
			$now = time(); // um...now
			$min_tstamp = $now - $fconf_age_s; // min tstamp value; anything less gets deleted
			$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, post_subject, category_id, conference_id, thread_id', 'tx_chcforum_post', "tstamp < $min_tstamp", '', '');
			while ($post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($p_res)) {
					$posts_prune_del[] = array( 'icon' => $icon , 'uid' => $post['uid'], 'subject' => '&nbsp;'.$post['post_subject'].'&nbsp;');
			}
		}

		// check the threads
		$t_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, thread_subject, category_id, conference_id', 'tx_chcforum_thread', '', '', '');
		while ($thread = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($t_res)) {
			// check if the parent conference still exists. If it doesn't add thread to delete array.
			$conf_uid = $thread['conference_id'];
			$c_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_conference', "uid=$conf_uid AND deleted=0", '', '', '');
			if (@$GLOBALS['TYPO3_DB']->sql_num_rows($c_res) == 0) {
				$threads_del[] = array( 'icon' => $icon, 'uid' => $thread['uid'], 'subject' => '&nbsp;'.$thread['thread_subject'].'&nbsp;');
			}
			// check if the thread is empty (no child posts) -- if it is, add it to delete array.
			$thread_uid = $thread['uid'];
			$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_post', "thread_id=$thread_uid AND deleted=0", '', '', '');
			if (@$GLOBALS['TYPO3_DB']->sql_num_rows($p_res) == 0) {
				$threads_del[] = array( 'icon' => $icon, 'uid' => $thread['uid'], 'subject' => '&nbsp;'.$thread['thread_subject'].'&nbsp;');
			}			
		}
		// delete any threads that are missing a parent conference
		if ($threads_del) {
			$t_where = 'uid='.$threads_del[0]['uid'];
			for ($key = 1, $size = count($threads_del); $key < $size; $key++) {
				$t_where .= ' OR uid='.$threads_del[$key]['uid'];
			}
			if ($t_where) $p_res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_thread', $t_where);
		}
		// Output a list of deleted threads.
		$this->message .= '<strong>'.$this->lang->getLL('message_threads_del').'</strong>';
		if ($threads_del) {
			$this->message .= $this->doc->table($threads_del); // figure out how to make this table display better.
		} else {
			$this->message .= '<br />'.$this->lang->getLL('message_none');		
		}
		$this->message .= '<br /><br />';
		
		// check the posts
		$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, post_subject, category_id, conference_id, thread_id', 'tx_chcforum_post', '', '', '');
		
		while ($post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($p_res)) {
			$thread_uid = $post['thread_id'];
			$conf_uid = $post['conference_id'];
			$t_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_thread', "uid=$thread_uid AND deleted=0", '', '', '');
			if (@$GLOBALS['TYPO3_DB']->sql_num_rows($t_res) == 0) {
				$posts_del[] = array( 'icon' => $icon , 'uid' => $post['uid'], 'subject' => '&nbsp;'.$post['post_subject'].'&nbsp;');
			} else {
				$c_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_conference', "uid=$conf_uid AND deleted=0", '', '', '');
				if (@$GLOBALS['TYPO3_DB']->sql_num_rows($c_res) == 0) {
					$posts_del[] = array( 'icon' => $icon , 'uid' => $post['uid'], 'subject' => '&nbsp;'.$post['post_subject'].'&nbsp;');
				}
			}
		}
		// delete any posts that are missing a parent thread or parent conference or pruned(?)
		if ($posts_del) {
			$p_where = 'uid='.$posts_del[0]['uid'];
			for ($key = 1, $size = count($posts_del); $key < $size; $key++) {
				$p_where .= ' OR uid='.$posts_del[$key]['uid'];
			}
			if ($p_where) $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_post', $p_where);
		}
		
		// Output a list of deleted posts.
		$this->message .= '<strong>'.$this->lang->getLL('message_posts_del').'</strong>';
		if ($posts_del) {
			$this->message .= $this->doc->table($posts_del);
		} else {
			$this->message .= '<br />'.$this->lang->getLL('message_none');		
		}
		$this->message .= '<br /><br />';

		// Output a list of pruned (also deleted) posts.
		$this->message .= '<strong>'.$this->lang->getLL('message_posts_prune_del').'</strong>';
		if ($posts_prune_del) {
			$this->message .= $this->doc->table($posts_prune_del);
		} else {
			$this->message .= '<br />'.$this->lang->getLL('message_none');		
		}
		$this->message .= '<br /><br />';
		
		// Delete everything where deleted = 0
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_forumgroup', 'deleted = 1');
		$del_count = $GLOBALS['TYPO3_DB']->sql_affected_rows();
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_category', 'deleted = 1');
		$del_count = $GLOBALS['TYPO3_DB']->sql_affected_rows() + $del_count;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_conference', 'deleted = 1');
		$del_count = $GLOBALS['TYPO3_DB']->sql_affected_rows() + $del_count;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_thread', 'deleted = 1');
		$del_count = $GLOBALS['TYPO3_DB']->sql_affected_rows() + $del_count;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_post', 'deleted = 1');
		$del_count = $GLOBALS['TYPO3_DB']->sql_affected_rows() + $del_count;
		// Output the count of deleted records.
		$this->message .= '<strong>'.$this->lang->getLL('message_del').'</strong><br />';
		$this->message .= $this->lang->getLL('message_del_lbl').' '.$del_count;
		$this->message .= '<br /><br />';
		
		// Clean up the posts_read table
		$pr_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('post_uid', 'tx_chcforum_posts_read', '', '', '');
		while ($pr = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pr_res)) {
			$post_uid = $pr['post_uid'];
			$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_post', "uid=$post_uid", '', '', '');
			$del_count = @$GLOBALS['TYPO3_DB']->sql_num_rows($p_res);
			if ($del_count == 0) {
				$pr_del[] = $pr['post_uid'];
			}
		}
		
		// Delete any post_read rows that refer to posts that no longer exist in the table.
		if ($pr_del) {
			$pr_where = 'post_uid='.$pr_del[0];
			for ($key = 1, $size = count($pr_del); $key < $size; $key++) {
				$pr_where .= ' OR post_uid='.$pr_del[$key];
			}
			if ($pr_where) $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_posts_read', $pr_where);
		}
		$this->message .= '<strong>'.$this->lang->getLL('message_pr_del').'</strong><br />';
		$this->message .= $this->lang->getLL('message_del_lbl').' '.$del_count;
		$this->message .= '<br /><br />';
	}
	
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function listRecs() {
		$tmpl = t3lib_div::makeInstance('tx_chcforum_tpower');
		$tmpl->tx_chcforum_tpower('templates/list.tpl');
		$tmpl->prepare();

	
		$tmpl->assign('new_record',$this->returnNewLink('create new record',$this->table));

		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, "pid=$this->spid AND deleted=0", '', '', '');
		if (@$GLOBALS['TYPO3_DB']->sql_num_rows($qresults) > 0) {
			$i = 0;
			while ($rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
				switch ($this->table) {
					case 'tx_chcforum_conference':
					$title = t3lib_div::formatForTextarea(stripslashes($rec['conference_name']));
					$select = $this->returnEditLink($rec['uid'],'','tx_chcforum_conference');
					#$delete = $this->returnDeleteLink($rec['uid'],'','tx_chcforum_conference');
					break;
					case 'tx_chcforum_category':
					$title = t3lib_div::formatForTextarea(stripslashes($rec['cat_title']));
					$select = $this->returnEditLink($rec['uid'],'','tx_chcforum_category');
					#$delete = $this->returnDeleteLink($rec['uid'],'','tx_chcforum_category');
					break;
					case 'tx_chcforum_forumgroup':
					$title = t3lib_div::formatForTextarea(stripslashes($rec['forumgroup_title']));
					$select = $this->returnEditLink($rec['uid'],'','tx_chcforum_forumgroup');
					#$delete = $this->returnDeleteLink($rec['uid'],'','tx_chcforum_forumgroup');
					break;
				}
				$i++;
				if (intval($i / 2) == ($i / 2)) {
					$style = 'listRowLt';
				} else {
					$style = 'listRowDk';
				}
				$tmpl->newblock('row');
				$tmpl->assign('select', $select);
				$tmpl->assign('delete', $delete);
				$tmpl->assign('style', $style);
				if ($rec['hidden'] == 1) {
					$tmpl->assign('img_file', $this->icon_h);
				} else {
					$tmpl->assign('img_file', $this->icon);
				}
				$tmpl->assign('title', $title);
			}
			$tmpl->newblock('submit');
			$tmpl->assign('submit', '<br /><br /><input type="submit" name="submit" value="'.$this->lang->getLL('submit').'"> <input type="submit" name="cancel" value="'.$this->lang->getLL('cancel').'">');
			
			$content = $tmpl->getOutputContent();
			if (($this->post['action'] == 'delcat') or ($this->post['action'] == 'delconf') or ($this->post['action'] == 'delfgrp')) {
				$content .= $this->setHidden('stage', 'validate');
			} else {
				$content .= $this->setHidden('stage', 'action');
			}
			$content .= $this->setHidden('selection', $this->selection);
			$content .= $this->setHidden('fuid', $this->fuid);
			$content .= $this->setHidden('action', $this->action);
		} else {
			$content .= $this->returnNewLink('create new record',$this->table);
			$content .= '<br /><br />';
			$content .= $this->lang->getLL('no_recs');
			$content .= '<br /><br /><input type="submit" name="cancel" value="'.$this->lang->getLL('cancel').'">';
		}
		return $content;
	}

	function MakeRequestURI() {
		$vars['fuid'] = $this->fuid;
		$vars['fpid'] = $this->fpid;
		$vars['spid'] = $this->spid;
		$vars['stage'] = $this->stage;
		$vars['selection'] = $this->selection;
		$vars['action'] = $this->action;
		$vars['table'] = $this->table;	
		$vars['submit'] = 'submit';	
		$base_url = $GLOBALS['REQUEST_URI'];
		foreach ($vars as $k => $v) {
			$params[] = $k.'='.$v; 
		}
		$params = implode ('&',$params);
		$url = $base_url.'?'.$params;
		return $url;
	}

	function returnDeleteLink($uid,$title,$tablename = false) {
		if (empty($tablename)) $tablename = $this->tconf['name'];
		$params = '&delete['.$tablename.']['.$uid.']=delete';
		$out .=	'<a href="#" onclick="'.
		t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'],$this->makeRequestURI()).
		'">';
		$out .= '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/delete_record.gif','width="11" height="12"').' title="Edit me" border="0" alt="" />';
		$out .= $title.'</a>';
		return $out;
	}

	function returnNewLink($title,$tablename = false) {
		if (empty($tablename)) $tablename = $this->tconf['name'];
		$params = '&edit['.$tablename.']['.$this->spid.']=new';

		$out .=	'<a href="#" onclick="'.
		t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'],$this->makeRequestURI()).
		'">';
		$out .= '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_record.gif','width="11" height="12"').' title="Edit me" border="0" alt="" /></a>';
		$out .=	' <a href="#" onclick="'.
		t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'],$this->makeRequestURI()).
		'">';
		$out .= $title.'</a>';
		return $out;
	}



	function returnEditLink($uid,$title,$tablename = false) {
		if (empty($tablename)) $tablename = $this->tconf['name'];
		$params = '&edit['.$tablename.']['.$uid.']=edit';
		$out .=	'<a href="#" onclick="'.
		t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'],$this->makeRequestURI()).
		'">';
		$out .= '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="Edit me" border="0" alt="" />';
		$out .= $title.'</a>';
		return $out;
	}

	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function setRec() {
	 if ($this->rec_uid && $this->table) {
		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, "uid=$this->rec_uid", '', '', '');
		$this->rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
	 } else {
		if ($this->selection == "f_conf" && isset($this->spid)) {
			$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_f_conf', "pid=$this->spid", '', '', '');
			if ($qresults) {
				$this->rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
				$this->uid = $this->rec['uid'];
				$this->action = 'fconf_update';
			}
		} else {
			return false;
		}
	 }
	}
	

	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function displayDb_cleanForm() {
		$out = $this->lang->getLL('db_clean_note');
		$out .= $this->setHidden('stage', 'validate');
		$out .= $this->setHidden('selection', $this->selection);
		$out .= $this->setHidden('action', $this->action);
		$out .= $this->setHidden('fuid', $this->fuid);
		$out .= '<br /><br /><input type="submit" name="submit" value="'.$this->lang->getLL('submit').'"> <input type="submit" name="cancel" value="'.$this->lang->getLL('cancel').'">';
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $field: ...
	* @return [type]  ...
	*/
	function explodeField ($field) {
		if (!empty($field)) {
			return explode(',', $field);
		} else {	
			return false;
		}
	}

	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function returnFGroups() {
		$where = 'pid = '.$this->spid.' AND deleted=0 AND hidden=0';
		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_forumgroup', $where, '', '', '');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
			$out[] = array('uid' => $row['uid'], 'title' => $row['forumgroup_title']);
		}
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function returnFeGroups() {
		$where = 'pid = '.$this->fconf[feusers_pid].' AND deleted=0 AND hidden=0';
		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_groups', $where, '', '', '');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
			$out[] = array('uid' => $row['uid'], 'title' => $row['title']);
		}
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function returnFCats() {
		$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_category', "pid=$this->spid AND deleted=0 AND hidden=0", '', '', '');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
			$out[] = array('uid' => $row['uid'], 'title' => $row['cat_title']);
		}
		return $out;
	}
	
	// Thanks to Frank StrŠter's comment on php.net -- http://www.php.net/manual/en/function.in-array.php
	function in_array_multi($needle, $haystack) {
		if (!is_array($haystack)) return false;
		while (list($key, $value) = each($haystack)) {
			if (is_array($value) && in_array_multi($needle, $value) || $value === $needle) {
				return true;
			}
		}
		return false;
	}
	
	// Option array is an array with key and value uid => title. $Selected_arr is an array with the uids that should be selected.
	function selectInpt($name, $option_arr, $selected_arr, $is_mult = false, $size = 4, $blank = false) {
		// Open the select field
		if ($size <= 1 && $is_mult == true) $size = 4;
		if ($size > 1) $size_attr = 'size='.$size.' ';
		if ($is_mult == true) {
			$out = '<select name="'.$name.'[]" '.$size_attr.'style="width: 200px;" multiple>';
		} else {
			$out = '<select name="'.$name.'" '.$size_attr.'style="width: 200px;">';
			if ($blank == true) $out .= '<option></option>'; //if it's not multiple, and blank equals true, add a blank option
		}
		if ($option_arr) {
			foreach ($option_arr as $arr) {
				if ($selected_arr && !empty($selected_arr)) {
					if (in_array($arr['uid'], $selected_arr)) {
						$out .= '<option selected value="'.$arr['uid'].'">'.t3lib_div::formatForTextarea(stripslashes($arr['title'])).'</option>';
					} else {
						$out .= '<option value="'.$arr['uid'].'">'.t3lib_div::formatForTextarea(stripslashes($arr['title'])).'</option>';
					}
				} else {
					$out .= '<option value="'.$arr['uid'].'">'.t3lib_div::formatForTextarea(stripslashes($arr['title'])).'</option>';
				}
			}
			$out .= '</select>';
		} else {
			$out = 'NONE';
		}
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $name: ...
	* @param [type]  $value: ...
	* @param [type]  $rows: ...
	* @param [type]  $cols: ...
	* @return [type]  ...
	*/
	function tboxInpt($name, $value, $rows = 7, $cols = 30) {
		$out = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" autowrap>'.t3lib_div::formatForTextarea(stripslashes($value)).'</textarea>';
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $name: ...
	* @param [type]  $value: ...
	* @param [type]  $maxlength: ...
	* @param [type]  $size: ...
	* @return [type]  ...
	*/
	function textInpt($name, $value = false, $maxlength = 255, $size = 30) {
		return '<input type="text" name="'.$name.'" maxlength='.$maxlength.' size='.$size.' value="'.t3lib_div::formatForTextarea(stripslashes($value)).'">';
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $name: ...
	* @param [type]  $checked: ...
	* @return [type]  ...
	*/
	function cboxInpt($name, $checked = false) {
		if ($checked == true) {
			$out = '<input type="checkbox" name="'.$name.'" value="1" checked>';
		} else {
			$out = '<input type="checkbox" name="'.$name.'" value="1">';
		}
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $start_lt: ...
	* @param [type]  $end_lt: ...
	* @param [type]  $name: ...
	* @param [type]  $grp_feusers: ...
	* @return [type]  ...
	*/
	function makeUserSelectMultiple ($start_lt, $end_lt, $name, $grp_feusers = array()) {
		// populate the select multiple box
		$alphabet = range($start_lt, $end_lt);
		foreach($alphabet as $letter) {
			$where = 'pid='.$this->fconf['feusers_pid'].' AND deleted=0 AND username LIKE \''.$letter.'%\'';
			$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,name', 'fe_users', $where, '', 'username ASC', '');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
				$names_array[] = array ('uid' => $row['uid'], 'name' => $row['name'], 'username' => $row['username'] );
			}
		}
		$out = '<select style="width: 200px;" size=8 name="'.$name.'" size="3" multiple>';
		if (!empty($names_array)) {
			foreach ($names_array as $a_name) {
				if (t3lib_div::inArray($grp_feusers, $a_name['uid']) == true) {
					$out .= '<option selected value="'.$a_name['uid'].'">'.$a_name['username'].' ('.$a_name['name'].')</option>'."\n";
				} else {
					$out .= '<option value="'.$a_name['uid'].'">'.$a_name['username'].' ('.$a_name['name'].')</option>'."\n";
				}
			}
		}
		$out .= '</select>';
		return $out;
	}
	
	/**
	* [Describe function...]
	*
	* @return [type]  ...
	*/
	function selectAction() {
		switch ($this->selection) {
			case 'mng_cats':
				$content .= $this->listRecs();
			break;

			case 'mng_confs':
				$content .= $this->listRecs();
			break;

			case 'mng_fgroups':
				$content .= $this->listRecs();
			break;
	
			case 'db_clean':
				$content .= $this->displayDb_cleanForm();
			break;
		}
		return $content;
	}
	
	/**
	* [Describe function...]
	*
	* @param [type]  $name: ...
	* @param [type]  $value: ...
	* @return [type]  ...
	*/
	function setHidden($name, $value) {
		return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/mod1/index.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/mod1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_chcforum_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>