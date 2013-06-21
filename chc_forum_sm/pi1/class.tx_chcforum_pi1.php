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
	$ext_path = t3lib_extMgm::extPath('chc_forum');
	require_once(PATH_tslib.'class.tslib_pibase.php');
	 
	/**
	* TX_CHCFORUM_PI1 CLASS
	*
	* @author Administrator <admin@email.test>
	*/
	class tx_chcforum_pi1 extends tslib_pibase {
		var $prefixId = 'tx_chcforum_pi1'; // Same as class name
		var $scriptRelPath = 'pi1/class.tx_chcforum_pi1.php'; // Path to this script relative to the extension dir.
		var $extKey = 'chc_forum'; // The extension key.
		var $cObj; // The backReference to the mother cObj object set at call time
		var $gpvars;
		 
		/**
		* Main control function. This is the function that gets called by typo3.
		* @param array $content: not sure what this is...
		* @param array $conf: configuration for this module.
		* @return void
		*/
		function main($content, $conf) {
			$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
			$GLOBALS['TSFE']->set_no_cache(); // disable frontend caching on this page

			$this->conf = $conf; // Load the typoscript conf for the plugin
			// Local cObj.
			$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
			// Loading the LOCAL_LANG values
			$this->pi_loadLL();
	
			// Disable Caching
			$GLOBALS["TSFE"] -> set_no_cache();

			// add fconf to the cObj
			$this->cObj->conf = $this->conf; // fconf needs to see this->conf
			$tx_chcforum_fconf = t3lib_div::makeInstanceClassName("tx_chcforum_fconf");
			$fconf = new $tx_chcforum_fconf($this->cObj);
			foreach($fconf as $k => $v) {
			$fconf_arr[$k] = $v;
			}
			unset($fconf);
			$this->fconf = $fconf_arr;
			$this->cObj->fconf = $this->fconf;

			$this->forum_pid = $this->cObj->data['pid']; // the pid for the page containing this instance of the forum.
					 

			// set up some values for cwtCommunity...
			// is cwt_community integration on?
			// is the extension loaded?
			if ($this->fconf['enable_cwtcommunity'] == true && t3lib_extMgm::isLoaded('cwt_community') == true) {
					$this->conf['cwtCommunityIntegrated'] = true;
					if (!$this->conf['cwtCommunity.']['template_userlist']) $this->conf['cwtCommunity.']['template_userlist'] = $this->fconf['tmpl_path'].'cwtcommunity_userlist.tmpl';
					if (!$this->conf['cwtCommunity.']['template_messages']) $this->conf['cwtCommunity.']['template_messages'] = $this->fconf['tmpl_path'].'cwtcommunity_messages.tmpl';
					if (!$this->conf['cwtCommunity.']['template_profile']) $this->conf['cwtCommunity.']['template_profile'] = $this->fconf['tmpl_path'].'cwtcommunity_profile.tmpl';
					if (!$this->conf['cwtCommunity.']['template_buddylist']) $this->conf['cwtCommunity.']['template_buddylist'] = $this->fconf['tmpl_path'].'cwtcommunity_buddylist.tmpl';
					$this->conf['cwtCommunity.']['pid_profile'] = $this->forum_pid;
					$this->conf['cwtCommunity.']['pid_buddylist'] = $this->forum_pid;
					$this->conf['cwtCommunity.']['pid_messages'] = $this->forum_pid;
					if (!$this->cObj->fconf['alt_img_field']) $this->cObj->fconf['alt_img_field'] = 'tx_cwtcommunityuser_image'; 
					if (!$this->cObj->fconf['alt_img_path']) $this->cObj->fconf['alt_img_path'] = 'uploads/tx_cwtcommunityuser';
			} else {
				$this->conf['cwtCommunityIntegrated'] = false;
			}

			// pass conf via cObj
			$this->cObj->conf = $this->conf;
			$this->cObj->ux_language = $this->LOCAL_LANG;
			$this->cObj->ux_llkey = $this->LLkey;
			$this->cObj->ux_llcharset = $this->LOCAL_LANG_charset;
			
			
			// Organize and decrypt information sent via the URL
			$gpvars['action'] = htmlspecialchars(t3lib_div::_GP('action')); // for cwt_community integration
			$gpvars['recipient_uid'] = htmlspecialchars(t3lib_div::_GP('recipient_uid')); // for cwt_community integration
			$gpvars['view'] = htmlspecialchars(t3lib_div::_GP('view'));
			$gpvars['cat_uid'] = htmlspecialchars(t3lib_div::_GP('cat_uid'));
			$gpvars['conf_uid'] = htmlspecialchars(t3lib_div::_GP('conf_uid'));
			$gpvars['thread_uid'] = htmlspecialchars(t3lib_div::_GP('thread_uid'));
			$gpvars['post_uid'] = htmlspecialchars(t3lib_div::_GP('post_uid'));
			$gpvars['fe_user_uid'] = htmlspecialchars(t3lib_div::_GP('fe_user_uid'));
			$gpvars['where'] = htmlspecialchars(t3lib_div::_GP('where'));
			$gpvars['author'] = htmlspecialchars($this->decode_post_var(t3lib_div::_GP('author')));
			$gpvars['stage'] = htmlspecialchars(t3lib_div::_GP('stage'));
			$gpvars['preview'] = htmlspecialchars(t3lib_div::_GP('preview'));
			$gpvars['page'] = htmlspecialchars(t3lib_div::_GP('page'));
			$gpvars['submit'] = htmlspecialchars(t3lib_div::_GP('submit'));
			$gpvars['cancel'] = htmlspecialchars(t3lib_div::_GP('cancel'));
			$gpvars['flag'] = htmlspecialchars(t3lib_div::_GP('flag'));
			$profile = t3lib_div::_GP('profile');
			$profile_h = t3lib_div::GPvar('profile_h');
			$profile_c = t3lib_div::GPvar('profile_c');
			if (!$profile or !is_array($profile)) $profile = array();
			if (!$profile_h or !is_array($profile_h)) $profile_h = array();
			if (!$profile_c or !is_array($profile_c)) $profile_c = array();
			$gpvars['profile'] = array_merge($profile, $profile_h, $profile_c);
			
			if ($this->conf['cwtCommunityIntegrated'] == true && $gpvars['author']) {
				// if we have cwt_community integration, we need to set the
				// get value for "uid" to the author uid.
				t3lib_div::_GETset($gpvars['author'],'uid');
			}


			if ($gpvars['profile']) {
				$gpvars['profile']['yahoo'] = htmlspecialchars($gpvars['profile']['yahoo']);
				$gpvars['profile']['aim'] = htmlspecialchars($gpvars['profile']['aim']);
				$gpvars['profile']['msn'] = htmlspecialchars($gpvars['profile']['msn']);
				$gpvars['profile']['www'] = htmlspecialchars($gpvars['profile']['www']);
				$gpvars['profile']['email'] = htmlspecialchars($gpvars['profile']['email']);
				$gpvars['profile']['submit'] = htmlspecialchars($gpvars['profile']['submit']);
			}

			$gpvars['attach_file_name'] = htmlspecialchars(t3lib_div::GPvar('attach_file_name'));
			$gpvars['name'] = htmlentities(t3lib_div::_GP('name'));
			$gpvars['rawName'] = htmlspecialchars(t3lib_div::_GP('name'));
			$gpvars['email'] = htmlspecialchars(t3lib_div::_GP('email'));
			$gpvars['text'] = htmlentities(t3lib_div::_GP('text'));
			$gpvars['rawText'] = htmlspecialchars(t3lib_div::_GP('text'));
			$gpvars['subject'] = htmlentities(t3lib_div::_GP('subject'));
			$gpvars['rawSubject'] = htmlspecialchars(t3lib_div::_GP('subject'));
			$gpvars['search'] = t3lib_div::_GP('search');
			$gpvars['files'] = $_FILES;
			$gpvars['thread_endtime'] = htmlspecialchars(t3lib_div::_GP('thread_endtime'));

			$gpvars['rateSelect'] = htmlspecialchars(t3lib_div::_GP('rateSelect'));
			$gpvars['ratePostUID'] = htmlspecialchars(t3lib_div::_GP('ratePostUID'));

			if (is_array($this->conf['gpvars.'])) {
				foreach($this->conf['gpvars.'] as $k => $v) {
					if($gpvars[$k] == false) $gpvars[$k] = $v;
				}
			}

			// validate the gpvars!			

			// validate view and where!
			if (!in_array($gpvars['view'],array('all_cats','single_cat','single_conf',
					'single_thread','single_post','edit_post','profile','search','ulist','new','cwt_user_pm','cwt_buddylist'))) $gpvars['view'] = 'all_cats';
			if (!in_array($gpvars['where'],array('all_cats','single_cat','single_conf',
					'single_thread','single_post','edit_post','profile'))) unset($gpvars['where']);
			
			// validate uids
			if (!is_numeric($gpvars['cat_uid']) && $gpvars['cat_uid'] < 0) unset($gpvars['cat_uid']);
			if (!is_numeric($gpvars['conf_uid']) && $gpvars['cat_uid'] < 0) unset($gpvars['conf_uid']);
			if (!is_numeric($gpvars['thread_uid']) && $gpvars['cat_uid'] < 0) unset($gpvars['thread_uid']);
			if (!is_numeric($gpvars['post_uid']) && $gpvars['cat_uid'] < 0) unset($gpvars['post_uid']);
			if (!is_numeric($gpvars['fe_user_uid']) && $gpvars['cat_uid'] < 0) unset($gpvars['fe_user_uid']);
			if (!is_numeric($gpvars['author']) && $gpvars['cat_uid'] < 0) unset($gpvars['author']);
			if (!is_numeric($gpvars['ratePostUID']) && $gpvars['ratePostUID'] < 0) unset($gpvars['ratePostUID']);

			// validate rating (between 1 and 5)
			if ($gpvars['rateSelect'] < 1 or $gpvars['rateSelect'] > 5) $gpvars['rateSelect'] = 0;

			if ($gpvars['flag'] == 'rate') {
					if (!$gpvars['rateSelect'] or !$gpvars['ratePostUID']) $gpvars['flag'] = '';
			}
			
			// validate preview
			// if the preview button was pressed, set $gpvars['preview'] to true.
			// Have to do it this way because preview was originally done through a
			// checkbox... rather than change everything in the code, I'm just adding
			// this check here.
			if ($gpvars['preview']) $gpvars['preview']=1;
			
			// validate page
			if ($gpvars['page'] < 0 or !is_numeric($gpvars['page'])) {
				$gpvars['page'] = 0;
			}

			// validate email
			if ($gpvars['email'] && preg_match( "/^[-^!#$%&'*+\/=?`{|}~.\w]+@[-a-zA-Z0-9]+(\.[-a-zA-Z0-9]+)+$/", $gpvars['email'] ) != 1) {
				unset($gpvars['email']);
			}
			
			// do more thorough search vars validation in search class
			$serialized_s_query = t3lib_div::_GP('s_query');
			if($serialized_s_query) $s_query_arr = unserialize(urldecode($serialized_s_query));
			if ($s_query_arr) $gpvars['search'] = $s_query_arr;

			$gpvars['search']['keywords'] = htmlspecialchars($gpvars['search']['keywords']);
			$gpvars['search']['uname'] = htmlspecialchars($gpvars['search']['uname']);
			if (!is_numeric($gpvars['search']['post_age'])) $gpvars['search']['post_age'] = '30'; 
			if ($gpvars['search']['post_fields'] != '1' and $gpvars['search']['post_fields'] != '2') $gpvars['search']['post_fields'] = '1';
			if ($gpvars['search']['display_results'] != '1' and $gpvars['search']['display_results'] != '2') $gpvars['search']['display_results'] = '1';

			// Make a new $tx_chcforum_display object using the information in $gpvars array
#			xdebug_start_profiling();
			$tx_chcforum_display = t3lib_div::makeInstanceClassName("tx_chcforum_display");
			$display = new $tx_chcforum_display($gpvars, &$this->cObj);						
			$out .= '<div id="tx_chcforum-pi1">';
			$out .= $display->html_out;
			$out .= '</div>';
#			xdebug_start_profiling();
#			xdebug_dump_function_profile(3);

			return $out;
		}

		// Added by Max Mishyn. replace PHP's strftime function with typo3's strWrap (for correct handling of time zones		
		function strftime($format, $datetime) {
			$df['strftime'] = $format;
			return $this->cObj->stdWrap($datetime, $df); 
		}

		function decode_post_var($encoded) {
			if ($encoded == 'self') return 'self';
			$decoded =	base64_decode($encoded);
			$decoded_array = unserialize($decoded);
			if ($decoded_array['key'] == md5($decoded_array['value'].$this->fconf['forum_pw'])) {
				return $decoded_array['value'];
			} else {
				return false;
			}
		}
	}
	 
	include_once($ext_path.'pi1/class.tx_chcforum_tpower.php');
	include_once($ext_path.'pi1/kses_lib.php');
	include_once($ext_path.'pi1/class.tx_chcforum_user.php');
	include_once($ext_path.'pi1/class.tx_chcforum_author.php');
	include_once($ext_path.'pi1/class.tx_chcforum_category.php');
	include_once($ext_path.'pi1/class.tx_chcforum_conference.php');
	include_once($ext_path.'pi1/class.tx_chcforum_display.php');
	include_once($ext_path.'pi1/class.tx_chcforum_form.php');
	include_once($ext_path.'pi1/class.tx_chcforum_message.php');
	include_once($ext_path.'pi1/class.tx_chcforum_post.php');
	include_once($ext_path.'pi1/class.tx_chcforum_shared.php');
	include_once($ext_path.'pi1/class.tx_chcforum_thread.php');
	include_once($ext_path.'pi1/class.tx_chcforum_fconf.php');
	include_once($ext_path.'pi1/class.tx_chcforum_search.php');
		 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_pi1.php']) {
		require_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_pi1.php']);
	}


	 
?>
