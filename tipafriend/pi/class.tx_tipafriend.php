<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Creates a Tip-a-Friend form.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   65: class tx_tipafriend extends tslib_pibase
 *   76:     function main_tipafriend($content,$conf)
 *  135:     function tipform()
 *  201:     function validate($tipData)
 *  217:     function getRecipients($emails)
 *  237:     function sendTip($tipData,$url)
 *  297:     function tiplink()
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */








require_once(PATH_tslib.'class.tslib_pibase.php');
/**
 * Creates a Tip-a-friend form based on an HTML template.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_tipafriend
 */
class tx_tipafriend extends tslib_pibase {

	var $cObj;		// The backReference to the parent cObj object set at call time

	/**
	 * Main news function.
	 *
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function main_tipafriend($content,$conf)	{
		$this->conf = $conf;
		$this->config['code'] = $this->cObj->stdWrap($this->conf['code'],$this->conf['code.']);

			// template is read.
		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);

			// globally substituted markers, fonts and colors.
		$splitMark = md5(microtime());
		$globalMarkerArray=array();
		list($globalMarkerArray['###GW1B###'],$globalMarkerArray['###GW1E###']) = explode($splitMark,$this->cObj->stdWrap($splitMark,$conf['wrap1.']));
		list($globalMarkerArray['###GW2B###'],$globalMarkerArray['###GW2E###']) = explode($splitMark,$this->cObj->stdWrap($splitMark,$conf['wrap2.']));
		$globalMarkerArray['###GC1###'] = $this->cObj->stdWrap($conf['color1'],$conf['color1.']);
		$globalMarkerArray['###GC2###'] = $this->cObj->stdWrap($conf['color2'],$conf['color2.']);
		$globalMarkerArray['###GC3###'] = $this->cObj->stdWrap($conf['color3'],$conf['color3.']);

			// Substitute Global Marker Array
		$this->templateCode= $this->cObj->substituteMarkerArray($this->templateCode, $globalMarkerArray);

			// TYpoLink
		$this->typolink_conf = $this->conf['typolink.'];
		$this->typolink_conf['additionalParams'] = $this->cObj->stdWrap($this->typolink_conf['additionalParams'],$this->typolink_conf['additionalParams.']);
		unset($this->typolink_conf['additionalParams.']);

		$codes=t3lib_div::trimExplode(',', $this->config['code']?$this->config['code']:$this->conf['defaultCode'],1);
		if (!count($codes))	$codes=array('');
		while(list(,$theCode)=each($codes))	{
			$theCode = (string)strtoupper(trim($theCode));
			$this->theCode = $theCode;
			switch($theCode)	{
				case 'TIPFORM':
					$content=$this->tipform();
				break;
				case 'TIPLINK':
					$content=$this->tiplink();
				break;
				default:
					$langKey = strtoupper($GLOBALS['TSFE']->config['config']['language']);
					$helpTemplate = $this->cObj->fileResource('EXT:tipafriend/pi/tipafriend_help.tmpl');

						// Get language version
					$helpTemplate_lang='';
					if ($langKey)	{$helpTemplate_lang = $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_'.$langKey.'###');}
					$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');

						// Markers and substitution:
					$markerArray['###CODE###'] = $this->theCode;
					$content.=$this->cObj->substituteMarkerArray($helpTemplate,$markerArray);
				break;
			}
		}
		return $content;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function tipform()	{
		global $TSFE;
		$GLOBALS['TSFE']->set_no_cache();

		$tipData = t3lib_div::_GP('TIPFORM');
		if($TSFE->loginUser){
			if (!$tipData['name']) $tipData['name']=$TSFE->fe_user->user['name'];
			if (!$tipData['email']) $tipData['email']=$TSFE->fe_user->user['email'];
		}
		$tipData['recipient'] = $this->getRecipients($tipData['recipient']);
		list($tipData['email']) = explode(',',$this->getRecipients($tipData['email']));
		$url = t3lib_div::_GP('tipUrl');

		// MLC grab referrer is nothing passed
		if ( ! $url )
		{
			$url				= $_SERVER[ 'HTTP_REFERER' ];
		}

			// Preparing markers
		$wrappedSubpartArray=array();
		$subpartArray=array();

		$markerArray=array();
		$markerArray['###FORM_URL###']=t3lib_div::getIndpEnv('REQUEST_URI');
		
// <<  YA; Delete the Get parameters from link which will be send; Jun 2007
		if (strpos($url,'/page-browse'))
		{
				$url = substr($url,0,strpos($url,'/page-browse'));
				$url = $url.".html";
		}
		elseif(strpos($url,'?'))
		{
			if (strpos($url,'?All')==FALSE)
				$url = substr($url,0,strpos($url,'?'));
		}
		
		
// >> YA;
		$markerArray['###URL###']=$url;
		$markerArray['###URL_ENCODED###']=rawurlencode($url);
		$markerArray['###URL_SPECIALCHARS###']=htmlspecialchars($url);
		$markerArray['###URL_DISPLAY###']=htmlspecialchars(strlen($url)>70 ? t3lib_div::fixed_lgd($url,30).t3lib_div::fixed_lgd($url,-30) : $url);

		$wrappedSubpartArray['###LINK###']=array('<a href="'.htmlspecialchars($url).'">','</a>');

			// validation
		$error=0;
		$sent=0;
		if (t3lib_div::_GP('sendTip'))	{

			if (t3lib_extMgm::isLoaded('captcha'))	{
				session_start();
				$captchaStr = $_SESSION['tx_captcha_string'];
				$_SESSION['tx_captcha_string'] = '';
			} else {
				$captchaStr = -1;
			}

			if ($this->validate($tipData,$captchaStr))	{
				$this->sendTip($tipData,$url);
				$sent=1;
			} else {
				$error=1;
			}
		}
			// Display form
		if ($sent)	{
			$subpart = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_TIPFORM_SENT###');

			$markerArray['###RECIPIENT###']=htmlspecialchars($tipData['recipient']);

			$content= $this->cObj->substituteMarkerArrayCached($subpart,$markerArray,$subpartArray,$wrappedSubpartArray);
		} else {

			$captchaHTMLoutput = t3lib_extMgm::isLoaded('captcha') ? '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" />' : '';

				// Generate Captcha data and store string in session:


			$subpart = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_TIPFORM###');

			$markerArray['###MESSAGE###']=htmlspecialchars($tipData['message']);
			$markerArray['###RECIPIENT###']=htmlspecialchars($tipData['recipient']);
			$markerArray['###YOUR_EMAIL###']=htmlspecialchars($tipData['email']);
			$markerArray['###YOUR_NAME###']=htmlspecialchars($tipData['name']);
			$markerArray['###HTML_MESSAGE###']=$tipData['html_message'] ? 'checked' : '';
			$markerArray['###CAPTCHA_HTML###']=$captchaHTMLoutput;

			if (!$error)	{
				$subpartArray['###ERROR_MSG###']='';
			}

//			debug($markerArray);

				// Substitute
			$content= $this->cObj->substituteMarkerArrayCached($subpart,$markerArray,$subpartArray,$wrappedSubpartArray);

#session_start();
#$content.=$_SESSION['tx_captcha_string'];
		}
		return $content;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$tipData: ...
	 * @return	[type]		...
	 */
	function validate($tipData,$captchaStr='')	{
		if (
			trim($tipData['name']) &&
			$tipData['email'] &&
			$tipData['recipient'] &&
			($captchaStr===-1 || ($captchaStr && $tipData['captchaResponse']===$captchaStr))
			) {
				return 1;
			}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$emails: ...
	 * @return	[type]		...
	 */
	function getRecipients($emails)	{
		$emailArr = split('[, ;]',$emails);
		reset($emailArr);
		$listArr=array();
		while(list(,$email)=each($emailArr))	{
			$email = trim($email);
			if ($email && t3lib_div::validEmail($email))	{
				$listArr[] = $email;
			}
		}
		return implode(',',$listArr);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$tipData: ...
	 * @param	[type]		$url: ...
	 * @return	[type]		...
	 */
	function sendTip($tipData,$url)	{
			// Get template
		$subpart = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_EMAIL###');

			// Set markers
		$markerArray['###MESSAGE###']=htmlspecialchars($tipData['message']);
		$markerArray['###RECIPIENT###']=htmlspecialchars($tipData['recipient']);
		$markerArray['###YOUR_EMAIL###']=htmlspecialchars($tipData['email']);
		$markerArray['###YOUR_NAME###']=htmlspecialchars($tipData['name']);
		$markerArray['###URL###']=$url;

			// Substitute in template
		$content= $this->cObj->substituteMarkerArrayCached($subpart,$markerArray,$subpartArray,$wrappedSubpartArray);

			// Set subject, conten and headers
		$headers=array();
		$headers[]='FROM: '.$tipData['name'].' <'.$tipData['email'].'>';
		list($subject,$plain_message) = explode(chr(10),trim($content),2);


			// HTML
		$cls=t3lib_div::makeInstanceClassName('t3lib_htmlmail');

		if (class_exists($cls) && $this->conf['htmlmail'] && $tipData['html_message'])	{	// If htmlmail lib is included, then generate a nice HTML-email
			$Typo3_htmlmail = t3lib_div::makeInstance('t3lib_htmlmail');
			$Typo3_htmlmail->start();
			$Typo3_htmlmail->useBase64();

			$Typo3_htmlmail->subject = $subject;
			$Typo3_htmlmail->from_email = $tipData['email'];
			$Typo3_htmlmail->from_name = $tipData['name'];
			$Typo3_htmlmail->replyto_email = $tipData['email'];
			$Typo3_htmlmail->replyto_name = $tipData['name'];
			$Typo3_htmlmail->organisation = '';
			$Typo3_htmlmail->priority = 3;

	//		debug($url);

				// this will fail if the url is password protected!
			$Typo3_htmlmail->addHTML($url);
			$Typo3_htmlmail->addPlain($plain_message);

			$Typo3_htmlmail->setHeaders();
			$Typo3_htmlmail->setContent();
			$Typo3_htmlmail->setRecipient($tipData['recipient']);

//			debug($Typo3_htmlmail->theParts);
			$Typo3_htmlmail->sendtheMail();
		} else { // Plain mail:
				// Sending mail:
#			$GLOBALS['TSFE']->plainMailEncoded(, $subject, , implode($headers,chr(10)));
			$this->cObj->sendNotifyEmail($plain_message, $tipData['recipient'], '', $tipData['email'], $tipData['name']);
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function tiplink()	{
		$url=t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');

		$subpart = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_TIPLINK###');

		$wrappedSubpartArray=array();
		$tConf = $this->typolink_conf;
		$tConf['additionalParams'].= '&tipUrl='.rawurlencode($url);
//		debug($tConf);
		$wrappedSubpartArray['###LINK###']= $this->cObj->typolinkWrap($tConf);

		$markerArray=array();
		$markerArray['###URL###']=$url;
		$markerArray['###URL_ENCODED###']=rawurlencode($url);
		$markerArray['###URL_SPECIALCHARS###']=htmlspecialchars($url);

			// Substitute
		$content= $this->cObj->substituteMarkerArrayCached($subpart,$markerArray,array(),$wrappedSubpartArray);
		return $content;
	}






	function getCaptchaElements()	{
		$code = substr(md5(uniqid()),0,10);

		return array($code,$code);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tipafriend/pi/class.tx_tipafriend.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tipafriend/pi/class.tx_tipafriend.php']);
}
?>
