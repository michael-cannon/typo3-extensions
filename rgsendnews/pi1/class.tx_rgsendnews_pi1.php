<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Georg Ringer (just2b) <http://www.ringer.it/>
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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Send news' for the 'rgsendnews' extension.
 *
 * @author	Georg Ringer (just2b) <http://www.ringer.it/>
 * @package	TYPO3
 * @subpackage	tx_rgsendnews
 */
class tx_rgsendnews_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_rgsendnews_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_rgsendnews_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'rgsendnews';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function xmlFunc ($content, $conf) {
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->conf=$conf;
		
		// get the post data
		$vars = t3lib_div::_POST('rgsn');
		$vars['newsid'] = intval($vars['newsid']);
		
		// make some checks
		$error = $this->getErrors($vars);
 
		// if there are some errors, show them
		if (count($error)>0) {
			$error = '<div class="error">'.implode('<br />',$error).'</div>';
			$content = $error;
		
		// everything seems ok, send the mail
		} else {
			// include a better html mail class
			require_once(t3lib_extMgm::siteRelpath('rgsendnews').'res/mail/htmlMimeMail.php');
			$mail = new htmlMimeMail();
			
			// get the news record
			$ceConf =  array('tables' => 'tt_news','source' => $vars['newsid'],'dontCheckPid' => 1);
			$newsRecord = $this->cObj->RECORDS($ceConf);
			
			// get some more details of the news record
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_news','uid = '.$vars['newsid']);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); 

			if ($GLOBALS['TSFE']->sys_language_content) {
				# $OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
			}			
					
			// template for plain mail
			foreach ($row as $key=>$value) {
  			$markerArray['###NEWS_'.strtoupper($key).'###'] = $this->cObj->stdWrap($value, $conf[$key.'.']);
  		}
			foreach ($vars as $key=>$value) {
  			$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value, $conf[$key.'.']);
  		}
			$template = $this->cObj->getSubpart($this->cObj->fileResource($conf['templateFile']), '###TEMPLATE_PLAINMAIL###');
			$plainMailText= $this->cObj->substituteMarkerArrayCached($template,$markerArray, array(),array());	


			// some default settings which apply to plain & html
			$mail->setTextCharset($GLOBALS['TSFE']->metaCharset);
			$mail->setHTMLCharset($GLOBALS['TSFE']->metaCharset);
			$mail->setHeadCharset($GLOBALS['TSFE']->metaCharset);
			// set the from
			$mail->setFrom($vars['sender'].' <'.$vars['sendmail'].'>');
			 
			// subject out of the template
			$subject = $this->cObj->getSubpart($this->cObj->fileResource($conf['templateFile']), '###TEMPLATE_SUBJECT###');
			$mail->setSubject(trim($this->cObj->substituteMarkerArrayCached($subject,$markerArray)));

			$result = false;
			
			// send it as html mail
			if ($vars['html']==1) {
				// Load the CSS as inline css
				$inlineCSS = t3lib_div::getURL($conf['stylesheet']);
				$stylesheet = ($inlineCSS) ? '<style type="text/css">'.$inlineCSS.'</style>' : '';
				
				$htmlMailText = '<html>
														<head>
															<title>'.$mail->subject.'</title>
															'.$stylesheet.'
														</head>
														<body>
															'.$newsRecord.'
														</body>
													</html>';
	
				$mail->setHtml($htmlMailText, $plainMailText);
			
			// send it as plain mail
			} else {
				$mail->setText($plainMailText);				
			}

			// send the mail now, really
			$result = $mail->send(array('"'.$vars['receiver'].'" <'.$vars['recmail'].'>')); 
			
			
			// save details for statistics & spam control, but just if mail was sent
			if ($result) {
				$insertArray = array();
				$insertArray['pid'] = (intval($conf['savePid']>0)) ? intval($conf['savePid']) : $GLOBALS['TSFE']->id;
				$insertArray['tstamp'] = time();
				$insertArray['crdate'] = time();			
				$insertArray['sender'] = $vars['sender'];
				$insertArray['sendmail'] = $vars['sendmail'];
				$insertArray['receiver'] = $vars['receiver'];
				$insertArray['recmail'] = $vars['recmail'];
				$insertArray['newsid'] = $vars['newsid'];
				$insertArray['comment'] = $vars['comment'];
				$insertArray['htmlmail'] = $vars['html'];			
				$insertArray['ip'] = $this->getCurrentIp();
				
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_rgsendnews_stat',$insertArray);
				
				$content = '<div class="success">'.sprintf($this->pi_getLL('mailsent'),$vars['receiver'], $vars['recmail']).'</div>';
			// error msg if mail didn't work
			} else {
				$content = '<div class="error">'.sprintf($this->pi_getLL('mailsenterror'),$vars['receiver'], $vars['recmail']).'</div>';
			}
			
		}

		return $content;
	}

	function getErrors($vars) {
		$error = array();
	#	$error[] = t3lib_div::view_array($vars);
		// sender name
		if ($vars['sender']=='') {
			$error[] =  $this->pi_getLL('error-sendname');
		}
		// sender email
		if ($vars['sendmail']=='') {
			$error[] = $this->pi_getLL('error-sendemail');
		} elseif (!t3lib_div::validEmail($vars['sendmail'])) {
			$error[] = $this->pi_getLL('error-sendemailwrong');
		}
		// receiver text
		if ($vars['receiver']=='') {
			$error[] = $this->pi_getLL('error-recname');
		}
		// receiver email
		if ($vars['recmail']=='') {
			$error[] = $this->pi_getLL('error-recemail');
		} elseif (!t3lib_div::validEmail($vars['recmail'])) {
			$error[] = $this->pi_getLL('error-recemailwrong');
		}
		// check "referrer"
		$hostLength = strlen(t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST'));
		if (substr($vars['url'],0,$hostLength) != t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST')) {
					$error[] = 'host' ;
		}		
		// check for SPAM
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_rgsendnews_stat', 'hidden=0 AND deleted=0 AND ip="'.$this->getCurrentIp().'"', '', 'uid DESC', 1);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// if same fields, need to be at least 10 min after sending again
		$timeToFriend = 60*5;
		$timeToGeneral = 30;
		if ($row['newsid']==$vars['newsid'] && $row['sendmail'] == $vars['sendmail'] && $row['recmail'] == $vars['recmail'] && ($row['crdate']>time()-$timeToFriend) ) {
			$error[] = $this->pi_getLL('error-spamdouble');
		// mail from same IP, no matter where
		} elseif ($row['crdate']>time()-$timeToGeneral){
			$error[] = $this->pi_getLL('error-spamdoublegeneral');
		}
		// check captcha response 
		if (t3lib_extMgm::isLoaded('captcha') && $this->conf['useCaptcha']==1)	{
			session_start();
			if ($vars['captcha'] != $_SESSION['tx_captcha_string']) {
				$error[] = $this->pi_getLL('error-captcha');    
			}
		}		
		
		return $error;	
	}

		/**
		* Retrieves current IP address
		*
		* @return    string        Current IP address
		*/
	function getCurrentIp() {
		if (preg_match('/^\d{2,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsendnews/pi1/class.tx_rgsendnews_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsendnews/pi1/class.tx_rgsendnews_pi1.php']);
}

?>