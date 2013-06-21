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
	* The fconf (forum configuration) class is used to get the configuration
	* variables from the tx_chcforum_f_conf table. These variables are set in
	* the backend module, and are needed at various points throughout the extension.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_fconf extends tx_chcforum_pi1 {
		 
	 /**
	  * fconf object constructor. Constructs the object from a DB record.
		*
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return void
		*/ 
		function tx_chcforum_fconf ($cObj) {

			#t3lib_div::debug(debug_backtrace());
			// unset this object -- we want it lean and mean.
			foreach ($this as $k => $v) {
				unset($this->$k);
			}

			
			$this->cObj = $cObj; 
			$this->conf = $this->cObj->conf;
	

			// Converting flexform data into array:
			$this->cObj->data['pi_flexform'] = t3lib_div::xml2array($this->cObj->data['pi_flexform']);
			
			// transfer XML data to attributes
			if (is_array($this->cObj->data['pi_flexform']['data'])) {
				foreach ($this->cObj->data['pi_flexform']['data'] as $sheet) {
					if (is_array($sheet[lDEF])) {
						foreach ($sheet[lDEF] as $attr => $value) {
							$this->$attr = $value['vDEF']; 
						}
					}
				}				
			}

			// set some static values based on fconf

			// set fconf default values
			if (!$this->posts_per_page) $this->posts_per_page = 10;
			if (!$this->threads_per_page) $this->threads_per_page = 30;
			if (!$this->date_format) $this->date_format = '%b %d %Y';
			if (!$this->thread_sorting) $this->conf_sorting = 'asc';
			if (!$this->conf_sorting) $this->conf_sorting = 'alpha_asc';
			if (!$this->cat_sorting) $this->cat_sorting = 'alpha_asc';
			if (!$this->max_user_img) $this->max_user_img = 100;
			if (!$this->max_attach) $this->max_attach = 100;
			if (!$this->allowed_file_types) $this->allowed_file_types = 'png,doc,gif,jpg,xls,ppt';
			if (!$this->allowed_mime_types) $this->allowed_mime_types = 'application/msword,image/gif,image/jpeg,image/pjpeg,application/excel,application/powerpoint';
			if (!$this->tmpl_path) $this->tmpl_path = t3lib_extMgm::siterelpath('chc_forum').'pi1/templates/';
			$this->tmpl_img_path = $this->tmpl_path.'img/';
 			if (!$this->emoticons_path) $this->emoticons_path = $this->tmpl_img_path.'emoticons/';
			if (!$this->hot_thread_cnt) $this->hot_thread_cnt = 20;
			if (!$this->forum_pw) $this->forum_pw = 'lycidas';
			if (!$this->image_ext_type) $this->image_ext_type = 'png';

			// kses html parsing allowed tags...
			$this->allowed = array('b' => array(),
											 'em' => array(),
											 'i' => array(),
											 'strong' => array(),
											 'b' => array(),
											 'span' => array('style' => array(),
																			 'class' => array(),
																			 'id' => array()),
			                 'a' => array('href'  => array('minlen' => 3, 'maxlen' => 300),
			                              'title' => array('valueless' => 'n')),
			                 'p' => array('align' => 1,
			                              'dummy' => array('valueless' => 'y')),
			                 'img' => array('src' => 1),
			                 'font' => array(),
											 'br' => array(),
											 'div' => array('style' => array(),
											 								 'class' => array(),
											 								 'id' => array()),
								  );
								
			

			// change fconf if cwt_community integration is on
			// the cwt_community form handles email, website, and img,
			// so we'll make the forum think these things are disabled.
			if ($this->conf['cwtCommunityIntegrated'] == true) {
				$this->disable_email = true;
				$this->disable_website = true;
				$this->disable_img_edit = true;
			}

			// unset cObj and conf; no longer needed.
			unset($this->cObj);
			unset($this->conf);
			
			$this->is_valid();
		}
		 
		/**
		* Makes sure that the fconf object is valid. Called on construction.
		*
		* @return void
		*/
		function is_valid () {
			$this->is_valid = false;
			if (empty($this->feusers_pid)) $error[] = tx_chcforum_shared::lang('fconf_no_feusers_pid');
				if (!$error) {
				$this->is_valid = true;
			} else {
				foreach ($error as $text) {
					$this->message .= $text.'<br />';
				}
				$this->message .= '<br />';
				$this->message .= tx_chcforum_shared::lang('fconf_setup');
			}
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_fconf.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_fconf.php']);
	}
	 
?>
