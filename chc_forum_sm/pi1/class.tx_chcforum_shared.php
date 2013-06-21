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
	* Shared class never gets instantiated (like t3lib_div). Instead, the methods get
	* called as tx_chcforum_shared::methodname.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_shared extends tx_chcforum_pi1 {
		

		function encode($string) {
			// encode the uid with the secret word to ensure that this is coming from the
			// forum. -- this isn't perfect; needs to be thought about a bit more.
			$tmp = array('key' => md5($string.$this->fconf['forum_pw']), 'value' => $string);
			$tmp = serialize($tmp);
			$tmp = base64_encode($tmp);
			return $tmp;
		}

	 /**
		* Makes a hash out of string -- used to obscure variable data passed through URL.
		* Not really doing much at this point, but might be in the future.
		*
		* @param string $string:  string that gets hashed.
		* @return string hashed $string.
		*/
		function makeHash($string) {
			#$hash = base64_encode(serialize($string));
			#return $hash;
			return $string;
		}
		
		function buildWhere($table, $addWhere = false, $show_hidden = false) {

			// kind of a hack -- the idea is that there needs
			// to be a way to globally show hidden records (for
			// mods to see hidden posts and threads, mainly).
			if (!$show_hidden) {
				$show_hidden = $this->user->show_hidden;
			}

			// kinda constants
			$pid = $this->conf['pidList'];
			$pid = $table.'.pid IN ('.$pid.')';

			$enable_fields = $this->cObj->enableFields($table,$show_hidden);

			$where = $enable_fields;

			// someday...
#			$now = time();
#			$start_stop_where = "$table.endtime >= $now AND $table.starttime <= $now";

			if ($addWhere) $where = $where.' AND ('.$addWhere.')';
			$where = $pid.$where;
			
#			$where = '';
#			if ($show_hidden == false) $where[] = $table.'.hidden = 0';
#			$where[] = $table.'.deleted = 0';
#			if ($check_access == true) 
#			if ($addWhere) $where[] = '('.$addWhere.')';
#			$where = implode(' AND ', $where);

			return $where;
			
		}
		
		
	 /** 
		* Unmakes hash created by previous function -- used to decypher hashed variables passed through URL.
		* Not really doing much at this point, but might be in the future.
		*
		* @param string $hash:  string that gets de-hashed.
		* @return string de-hashed $hash.
		*/
		function unmakeHash($hash) {
			#$string = unserialize(base64_decode($hash));
			#return $string;
			return $hash;
		}


	 /**		 
		* Wrapper for typo3 pi_getLL function. Returns value from local_lang file corresponding to $key.
		*
		* @param string $key:  the key in the locallang file to look up.
		* @return string the text from the local_lang file.
		*/
		function lang($key) {
			if (!$this->LOCAL_LANG_charset) $this->LOCAL_LANG_charset = $this->cObj->ux_llcharset;
			if (!$this->LOCAL_LANG) $this->LOCAL_LANG = $this->cObj->ux_language;
			$this->LLkey = $this->cObj->ux_llkey;			
			// slows things down:
			// if ($this->LOCAL_LANG_loaded == false) $this->pi_loadLL();
			// Loading the LOCAL_LANG values
			return $this->pi_getLL("$key");
		}

	 /**
		* Makes a link to the current page with the post vars $params and text $title.
		*
		* @param array  $params:  an array of parameters for the link. eg. 'view' => 'single_conf', 'conf_uid' => 3, etc.
		* @param string  $title:  the link text.
		* @param string  $attr:  any attributes that should be inserted into the link HTML.
		* @return string the HTML for the link
		*/
		function makeLink($params = false, $title = false, $attr = false, $url_only = false) {
			if ($this->conf) {
				$pid = $this->cObj->data[pid];
			} else {
				$this->conf = $this->cObj->conf;
				$pid = $this->cObj->data[pid];
			}

			// add URL parameters sent via typoscript. Used for forum / tt_news
			// integration. Thanks Rupi!
			if ($this->conf['chcAddParams']) {
    			$params = array_merge($params,tx_chcforum_shared::getAddParams($this->conf['chcAddParams']));
			}

			$url = htmlspecialchars($this->cObj->getTypoLink_URL($pid,$params)); // run it through special chars for XHTML compliancy
			$out = '<a href="'.$url.'" '.$attr.'>'.$title.'</a>' ;
			if ($url_only == true) {
				return $url;
			} else {
				return $out;
			}
		}
		 
		/**
		* Returns the HTML needed for a linked image.
		*
		* @param array  $params: an array of parameters for the link. eg. 'view' => 'single_conf', 'conf_uid' => 3, etc.
		* @param string  $img_file_path: the path to the image -- relative or absolute.
		* @param string  $attr: any attributes that should be inserted into the link HTML.
		* @return string the HTML for the image link
		*/
		function makeImageLink($params, $img_file_path, $attr = false, $alt = false) {
			$img_html = '<img alt="'.$alt.'" title="imagelink" class="tx-chcforum-pi1-buttonPadding" border="0" src="'.$img_file_path.'" />';
			$link = tx_chcforum_shared::makeLink($params, $img_html, $attr);
			return $link;
		}
		 
		/**
		* Used to get the template path from fconf table. Should be called before accessing
		* any template via tpower -- put it in an if statemet, so that if $this->tmpl_path is
		* alread set, this won't get called again.
		* eg.: if (!this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
		*
		* @return string  correct path to the template file
		*/
		function setTemplatePath() {
			if ($this->fconf['tmpl_path'] && t3lib_div::validPathStr($this->fconf['tmpl_path'])) {
				$tmpl_path = t3lib_div::getFileAbsFileName(t3lib_div::fixWindowsFilePath($this->fconf['tmpl_path']));
				if (!file_exists($tmpl_path)) $tmpl_path = t3lib_extMgm::extPath('chc_forum').'pi1/templates/';
			} else {
				$tmpl_path = t3lib_extMgm::extPath('chc_forum').'pi1/templates/';
			}
			return $tmpl_path;
		}	
			
		/**
	 	* Returns an array with additional Link parameters
		* 
		* @param string  $addParamsList: comma-seperated list of parameters (from TS-setup) that will be added to all forum links.
		* @return array additional link parameters in an array
		*/
		 function getAddParams($addParamsList){
		 	$queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET'])) ;
			if ($queryString) {
				while (list(, $val) = each($queryString)) {
					$tmp = explode('=', $val); 
					$paramArray[$tmp[0]] = $tmp[1];
				} 
				while (list($pk, $pv) = each($paramArray)) {
					if (t3lib_div::inList($addParamsList, $pk)) {
						$addParamArray[$pk]=$pv ;
					} 
				} 
			}
			return $addParamArray;
		 }

			function getCWTcommunity($view) {
				// build the cwt community object.
				$cwt_path = t3lib_extMgm::extPath('cwt_community');
				include_once($cwt_path.'pi1/class.tx_cwtcommunity_pi1.php');
				$cwt_obj = t3lib_div::makeInstanceClassName("tx_cwtcommunity_pi1");
				$cwt = new $cwt_obj();
				
				// tell it what to do and where to look.
				$cwt_conf = $this->conf['cwtCommunity.'];
				$cwt_conf['tsFlex']['data']['sDEF']['lDEF']['field_code']['vDEF'] = $view;
				$cwt->cObj->data['pages'] = $this->fconf['feusers_pid'];
				return $cwt->main($content,$cwt_conf);				
				
			}

	}
	
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_shared.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_shared.php']);
	}
	 
?>
