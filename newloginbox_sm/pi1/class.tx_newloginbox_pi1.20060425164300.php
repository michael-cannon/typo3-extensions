<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Sk�rh�j (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'Better login-box' for the 'newloginbox' extension.
 *
 * $Id: class.tx_newloginbox_pi1.20060425164300.php,v 1.1.1.1 2010/04/15 10:03:54 peimic.comprock Exp $
 * XHTML compliant!
 *
 * @author	Kasper Sk�rh�j (kasper@typo3.com)
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   61: class tx_newloginbox_pi1 extends tslib_pibase 
 *   76:     function main($content,$conf)	
 *  243:     function getOutputLabel($key,$sheet,$field)	
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');







/**
 * Plugin 'Better login-box' for the 'newloginbox' extension.
 * 
 * @author	Kasper Sk�rh�j (kasper@typo3.com)
 * @package TYPO3
 * @subpackage tx_newloginbox
 */
class tx_newloginbox_pi1 extends tslib_pibase {

		// Default plugin variables:
	var $prefixId = 'tx_newloginbox_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_newloginbox_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'newloginbox';	// The extension key.
	
	
	/**
	 * Displays an alternative, more advanced / user friendly login form (than the default)
	 * 
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * @return	string		HTML for the plugin
	 */
	function main($content,$conf)	{
	
			// Loading TypoScript array into object variable:
		$this->conf=$conf;
		
			// Loading language-labels
		$this->pi_loadLL();
		
			// Init FlexForm configuration for plugin:
		$this->pi_initPIflexForm();

			// Get storage PIDs:
		$d=$GLOBALS['TSFE']->getStorageSiterootPids();

			// GPvars:		
		$logintype=t3lib_div::GPvar('logintype');
		$redirect_url=t3lib_div::GPvar('redirect_url');

			// Auto redirect.
			// Feature to redirect to the page where the user came from (HTTP_REFERER).
			// Allowed domains to redirect to, can be configured with plugin.tx_newloginbox_pi1.domains
			// Thanks to plan2.net / Martin Kutschker for implementing this feature.
		if (!$redirect_url && $this->conf['domains'])   {
			$redirect_url = t3lib_div::getIndpEnv('HTTP_REFERER');

				// is referring url allowed to redirect?
			if (ereg('^http://([[:alnum:]._-]+)/',$redirect_url,$match))    {
				$redirect_domain = $match[1];
				$found = false;
				foreach(split(',', $this->conf['domains']) as $d)       {
					if (ereg('(^|\.)'.$d.'$',$redirect_domain))     {
						$found = true;
						break;
					}
				}
				if (!$found)    $redirect_url='';
			}

				// avoid forced logout, when trying to login immediatly after a logout
			$redirect_url=ereg_replace("[&?]logintype=[a-z]+","",$redirect_url);
		}

		if ($this->piVars['forgot'])	{
			$content.='<h3>'.$this->pi_getLL('forgot_password','',1).'</h3>';

			if (trim($this->piVars['DATA']['forgot_email']) && t3lib_div::validEmail($this->piVars['DATA']['forgot_email']))	{

				$query="SELECT username,password FROM fe_users WHERE email='".addslashes(trim($this->piVars['DATA']['forgot_email']))."' 
					AND pid='".intval($d['_STORAGE_PID'])."'".
					$this->cObj->enableFields('fe_users');
				$res = mysql(TYPO3_db,$query);

				if ($row=mysql_fetch_assoc($res))	{
					$msg=sprintf($this->pi_getLL('forgot_password_pswmsg','',1),trim($this->piVars['DATA']['forgot_email']),$row['username'],$row['password']);
				} else {
					$msg=sprintf($this->pi_getLL('forgot_password_no_pswmsg','',1),trim($this->piVars['DATA']['forgot_email']));
				}

				// Hook (used by kb_md5fepw extension by Kraft Bernhard <kraftb@gmx.net>)
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['forgotEmail'])) {
					$_params = array (
						'msg' => &$msg,
					); 
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['forgotEmail'] as $funcRef) {
						t3lib_div::callUserFunction($funcRef, $_params, $this);
					} 
				}

				$this->cObj->sendNotifyEmail($msg, trim($this->piVars['DATA']['forgot_email']), '', $this->conf['email_from'], $this->conf['email_fromName'], $this->conf['replyTo']);
				
				$content.='<p>'.sprintf($this->pi_getLL('forgot_password_emailSent','',1), '<em>'.htmlspecialchars(trim($this->piVars['DATA']['forgot_email'])).'</em>').'</p>';
				$content.='<p'.$this->pi_classParam('back').'>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('forgot_password_backToLogin','',1),array('forgot'=>'')).'</p>';
			} else {
				$content.='<p>'.$this->pi_getLL('forgot_password_enterEmail','',1).'</p>';
				$content.='
				
					<!--
						"Send password" form
					-->
					<form action="'.htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')).'" method="post" style="margin: 0 0 0 0;">
					<table '.$this->conf['tableParams_details'].'>
						<tr>
							<td><p>'.$this->pi_getLL('your_email','',1).'</p></td>
							<td><input type="text" name="'.$this->prefixId.'[DATA][forgot_email]" id="'.$this->pi_getClassName('forgot_email').'" value="" /></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" name="submit"  id="'.$this->pi_getClassName('forgot_email_submit').'" value="'.$this->pi_getLL('send_password','',1).'"'.$this->pi_classParam('submit').' />
							</td>
						</tr>
					</table>
					</form>
				';
			}
		} else {
			if ($GLOBALS['TSFE']->loginUser)	{
				if ($logintype=='login')	{
					$outH = $this->getOutputLabel('header_success','s_success','header');
					$outC = str_replace('###USER###',$GLOBALS['TSFE']->fe_user->user['username'],$this->getOutputLabel('msg_success','s_success','message'));
	
					if ($outH)	$content.='<h3>'.$outH.'</h3>';
					if ($outC)	$content.='<p>'.$outC.'</p>';

						// Hook for general actions after after login has been confirmed (by Thomas Danzl <thomas@danzl.org>)
					if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['login_confirmed']) {
						$_params = array();
						foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['login_confirmed'] as $_funcRef)    {
							if ($_funcRef) {
								t3lib_div::callUserFunction($_funcRef,$_params,$this);
							}
						}
					}

					// Hook for dkd_redirect_at_login extension (by Ingmar Schlecht <ingmar@typo3.org>)
					if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['dkd_redirect_at_login'])	{
						$_params = array('redirect_url'=>$redirect_url);
						$redirect_url = t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['dkd_redirect_at_login'],$_params,$this);
					}

					if (!$GLOBALS['TSFE']->fe_user->cookieId)	{
						$content.='<p style="color:red; font-weight:bold;">'.$this->pi_getLL('cookie_warning','',1).'</p>';
					} elseif ($redirect_url)	{
						header('Location: '.t3lib_div::locationHeaderUrl($redirect_url));
						exit;
					}
				} else {
					$outH = $this->getOutputLabel('header_status','s_status','header');
					$outC = str_replace('###USER###',$GLOBALS['TSFE']->fe_user->user['username'],$this->getOutputLabel('msg_status','s_status','message'));
	
					if ($outH)	$content.='<h3>'.$outH.'</h3>';
					if ($outC)	$content.='<p>'.$outC.'</p>';
					
					$usernameInfo = '<strong>'.htmlspecialchars($GLOBALS['TSFE']->fe_user->user['username']).'</strong>, '.htmlspecialchars($GLOBALS['TSFE']->fe_user->user['name']);
					if ($this->conf['detailsPage'])	{
						$usernameInfo = $this->pi_linkToPage($usernameInfo,$this->conf['detailsPage'],'',array('tx_newloginbox_pi3[showUid]' => $GLOBALS['TSFE']->fe_user->user['uid'], 'tx_newloginbox_pi3[returnUrl]'=>t3lib_div::getIndpEnv('REQUEST_URI')));
					}
					
					$content.='
				
						<!--
							"Logout" form
						-->
						<form action="'.htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'_top')).'" target="_top" method="post" style="margin: 0 0 0 0;">
						<table '.$this->conf['tableParams_details'].'>
							<tr>
								<td><p'.$this->pi_classParam('username').'>'.$this->pi_getLL('username','',1).'</p></td>
								<td><p>'.$usernameInfo.'</p></td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" name="submit"   id="'.$this->pi_getClassName('logout_submit').'" value="'.$this->pi_getLL('logout','',1).'"'.$this->pi_classParam('submit').' />
									<input type="hidden" name="logintype" value="logout" />
									<input type="hidden" name="pid" value="'.intval($d['_STORAGE_PID']).'" />
								</td>
							</tr>
						</table>
						</form>
					';
				}
			} else {
				if ($logintype=='login')	{
					$outH = $this->getOutputLabel('header_error','s_error','header');
					$outC = $this->getOutputLabel('msg_error','s_error','message');
				} elseif ($logintype=='logout')	{
					$outH = $this->getOutputLabel('header_logout','s_logout','header');
					$outC = $this->getOutputLabel('msg_logout','s_logout','message');
				} else {	// No user currently logged in:
					$outH = $this->getOutputLabel('header_welcome','s_welcome','header');
					$outC = $this->getOutputLabel('msg_welcome','s_welcome','message');
				}
				if ($outH)	$content.='<h3>'.$outH.'</h3>';
				if ($outC)	$content.='<p>'.$outC.'</p>';


					// Hook (used by kb_md5fepw extension by Kraft Bernhard <kraftb@gmx.net>)
					// This hook allows to call User JS functions.
					// The methods should also set the required JS functions to get included
				$onSubmit = '';
				$extraHidden = '';
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['loginFormOnSubmitFuncs'])) {
					$_params = array (); 
					$onSubmitAr = array();
					$extraHiddenAr = array();
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['loginFormOnSubmitFuncs'] as $funcRef) {
						list($onSub, $hid) = t3lib_div::callUserFunction($funcRef, $_params, $this);
						$onSubmitAr[] = $onSub;
						$extraHiddenAr[] = $hid;
					} 
				} 
				if (count($onSubmitAr)) {
					$onSubmit = implode('; ', $onSubmitAr).'; return true;';
					$extraHidden = implode(chr(10), $extraHiddenAr);
				}

				$content.='
				
					<!--
						"Login" form, including login result if failure.
					-->
					<form action="'.htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'_top')).'" target="_top" method="post" style="margin: 0 0 0 0;"'.(strlen($onSubmit)?' onSubmit="'.$onSubmit.'"':'').'>
					<table '.$this->conf['tableParams_details'].'>
						<tr>
							<td><p>'.$this->pi_getLL('username','',1).'</p></td>
							<td><input type="text" name="user" value="" id="'.$this->pi_getClassName('user').'" /></td>
						</tr>
						<tr>
							<td><p>'.$this->pi_getLL('password','',1).'</p></td>
							<td><input type="password" name="pass" value="" id="'.$this->pi_getClassName('pass').'" /></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" name="submit" value="'.$this->pi_getLL('login','',1).'"'.$this->pi_classParam('submit').'   id="'.$this->pi_getClassName('login_submit').'" />
								<input type="hidden" name="logintype" value="login" />
								<input type="hidden" name="pid" value="'.intval($d['_STORAGE_PID']).'" />
								<input type="hidden" name="redirect_url" value="'.htmlspecialchars($redirect_url).'" />
								'.$extraHidden.'
							</td>
						</tr>
					</table>
					</form>
				';
				
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'show_forgot_password','sDEF'))	{
					$content.='<p'.$this->pi_classParam('forgotP').'>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('forgot_password','',1),array('forgot'=>1)).'</p>';
				}
			}
		}

		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Returns the headers/messages for the login/logout/status etc state of the login form. If a value is found int cObj->data[...] then that is used, otherwise the default from local_lang.
	 * 
	 * @param	string		The key used for labels in locallang
	 * @param	string		The sheet refering to T3FlexForm content from "pi_flexform"
	 * @param	string		The field from the sheet of T3FlexForm content from "pi_flexform"
	 * @return	string		The result string
	 */
	function getOutputLabel($key,$sheet,$field)	{
		$dataF = nl2br(trim(strip_tags($this->pi_getFFvalue($this->cObj->data['pi_flexform'],$field,$sheet),'<b><i><a>')));		// The possible entities from the flexform should theoretically be htmlspecialchars()'ed for XHTML compatibility - but this was not easy to just fix since SOME HTML should be allowed! Further, allowing HTML with strip_tags does not prevent wrong attributes and mixing of character case!
		return $dataF ? $dataF : nl2br(trim($this->pi_getLL('oLabel_'.$key,'',1)));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newloginbox/pi1/class.tx_newloginbox_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newloginbox/pi1/class.tx_newloginbox_pi1.php']);
}
?>