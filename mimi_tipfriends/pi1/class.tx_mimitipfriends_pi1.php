<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Courcy Michael (michael.courcy@intuiteo.com)
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
 * Plugin 'Tip many friends' for the 'mimi_tipfriends' extension.
 *
 * @author	Courcy Michael <michael.courcy@intuiteo.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");


class tx_mimitipfriends_pi1 extends tslib_pibase {
	var $prefixId = "tx_mimitipfriends_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_mimitipfriends_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "mimi_tipfriends";	// The extension key.
	var $defTemplate = 'tip_many_friends.tmpl';			// Default template file.
	var $error_mess = ""; //the error message if an email is wrong formatted or a field fogotten
	var $submit_vars = array(); //the var comming from the form
	var $tip_form = ""; //template for the form
	var $tip_thankyou = ""; //template for the thankyou message
	var $charset = 'iso-8859-1'; // charset to be used in emails and form conversions
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->submit_vars = $this->piVars; //the var wich are transferred to the plugin
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
				
		
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		
		
		// Get the template settings.
		$this->config['templateFile'] = trim($this->conf['templateFile']);
		
		// **************************
		// *** Get the HTML template:
		// **************************	
		if(strlen($this->config['templateFile']) < 1) $this->config['templateFile'] = t3lib_extMgm::siteRelPath($this->extKey).'pi1/'.$this->defTemplate;
		$this->templateCode = $this->cObj->fileResource($this->config['templateFile']);
		// Get the subparts from the HTML template.
		if($this->templateCode) {
			// Get the 2 templates.			
			$this->tip_form = $this->cObj->getSubpart($this->templateCode, '###TIP_FORM###');
			$this->tip_thankyou = $this->cObj->getSubpart($this->templateCode, '###TIP_THANKYOU###');			
		} else {
		// ### For dubug purposes only ###: 
		debug('No template code found!');
		}
		
		$CMD = $this->submit_vars['CMD'];
		//debug($CMD);
		
		if ($CMD==$this->pi_getLL('submit_input','Envoyer')) {
			if ($this->check_vars()) {
				return  $this->make_thankyou();
			}				
		}
		
		return $this->make_tipform();		
	}
	
	
	
	/**
	 * Check the vars if they are empty and if email are well formatted
	 * When the first mistake is met the errMess is intitialized 
	 * and the fuction return false.
	 *
	 * @return Boolean
	 */
	function check_vars(){
		if (!t3lib_div::validEmail($this->submit_vars['my_email']) ) {
			$this->error_mess = $this->pi_getLL('my_email_wrong','Il y a probablement une faute de syntaxe dans votre email');
			return 0;		
		}		
		if (!t3lib_div::validEmail($this->submit_vars['email1']) ) {
			$this->error_mess = $this->pi_getLL('email1_wrong','Il y a probablement une faute de syntaxe dans le premier email');
			return 0;		
		}
		if (strlen($this->submit_vars['my_name'])< 1) {
			$this->error_mess = $this->pi_getLL('my_name_wrong','Vous devez donner votre nom');
			return 0;
		}
		//we don't force second and third email neither the optional message
		if (strlen($this->submit_vars['email2'])>0 && !t3lib_div::validEmail($this->submit_vars['email2']) ) {
			$this->error_mess = $this->pi_getLL('email2_wrong','Il y a probablement une faute de syntaxe dans le deuxième email');
			return 0;		
		}
		if (strlen($this->submit_vars['email3'])>0 && !t3lib_div::validEmail($this->submit_vars['email3']) ) {
			$this->error_mess = $this->pi_getLL('email3_wrong','Il y a probablement une faute de syntaxe dans le troisième email');
			return 0;		
		}
		return 1;		
	}
	
	//generate the form for sending the tip
	/**
	 * Generate the form with eventual mistake message
	 *
	 * @return String the form for sending the link
	 */
	function make_tipform(){		
		//debug("2 " . $this->error_mess);
		//format the tipform		
		$markerArray = array();
		//first all the markers we can extract from the locallang 
		$markerArray['###TIP_MANY_FRIENDS###'] 	= $this->pi_getLL('tip_many_friends','Envoyer cette page à des amis');
		$markerArray['###ERROR_MESSAGE###'] 	= $this->error_mess;
		$markerArray['###MY_EMAIL###'] 			= $this->pi_getLL('my_email','Mon email');
		$markerArray['###MY_NAME###'] 			= $this->pi_getLL('my_name','Mon nom');
		$markerArray['###EMAIL1###'] 			= $this->pi_getLL('email1','Premier email');
		$markerArray['###EMAIL2###'] 			= $this->pi_getLL('email2','Deuxième email');
		$markerArray['###EMAIL3###'] 			= $this->pi_getLL('email3','troisième email');
		$markerArray['###MY_MESSAGE###'] 		= $this->pi_getLL('my_message','Mon message');
		$markerArray['###SUBMIT_MESSAGE###'] 	= $this->pi_getLL('submit_message','Les champs avec * sont obligatoires');
		//and the input field
	   	
		//if the referer doesn't exist in the variable we must save it in the form otherwise we repeat it
		if ($this->submit_vars['hidden_referer']) 
		{
		$markerArray['###HIDDEN_REFERER###']	= '<input type="hidden" name="'.$this->prefixId.'[hidden_referer]" value="'.$this->submit_vars['hidden_referer'].'" />';
		}

		else 
		{
			$markerArray['###HIDDEN_REFERER###']	= '<input type="hidden" name="'.$this->prefixId.'[hidden_referer]" value="'.$_SERVER['HTTP_REFERER'].'" />';
			// MLC grab current URL
			// $requestUri			= $_SERVER["REQUEST_URI"];
			// $httpHost			= $_SERVER["HTTP_HOST"];
			// $url				= 'http://' . $httpHost . $requestUri;
			// $markerArray['###HIDDEN_REFERER###']	= '<input type="hidden" name="'.$this->prefixId.'[hidden_referer]" value="'.$url.'" />';
		}
		
		$markerArray['###MY_EMAIL_INPUT###']	= '<input type="text" name="'.$this->prefixId.'[my_email]" value="'.$this->submit_vars['my_email'].'" />';
		$markerArray['###MY_NAME_INPUT###']  	= '<input type="text" name="'.$this->prefixId.'[my_name]" value="'.$this->submit_vars['my_name'].'" />';
		$markerArray['###EMAIL1_INPUT###'] 		= '<input type="text" name="'.$this->prefixId.'[email1]" value="'.$this->submit_vars['email1'].'" />';
		$markerArray['###EMAIL2_INPUT###']		= '<input type="text" name="'.$this->prefixId.'[email2]" value="'.$this->submit_vars['email2'].'" />';
		$markerArray['###EMAIL3_INPUT###'] 		= '<input type="text" name="'.$this->prefixId.'[email3]" value="'.$this->submit_vars['email3'].'" />';
		$markerArray['###MY_MESSAGE_INPUT###'] 	= '<textarea name="'.$this->prefixId.'[my_message]" >'.$this->submit_vars['my_message'].'</textarea>';
		$markerArray['###SUBMIT_INPUT###'] 		= '<input type="submit" name="'.$this->prefixId.'[CMD]" value="'.$this->pi_getLL('submit_input','Envoyer').'" />';
		return $this->cObj->substituteMarkerArrayCached($this->tip_form, $markerArray, array(), array());		
	}
	
	
	/**
	 * Send the link and 
	 * Generate the message that the link has been well sended
	 * with a back link to the previous page 
	 * @return String the message that the link has been well sended
	 */
	function make_thankyou(){
		//first all the markers we can extract from the locallang 
		$markerArray['###TIP_MANY_FRIENDS_THANKYOU###'] 				= $this->pi_getLL('tip_many_friends_thankyou','Envoyer cette page à des amis');
		$markerArray['###TIP_MANY_FRIENDS_THANKYOU_TEXT###'] 			= $this->pi_getLL('tip_many_friends_thankyou_text');	
		$markerArray['###BACK_LINK###'] 								= '<a href="'.$this->submit_vars['hidden_referer'].'">'.$this->pi_getLL('back').'</a>';
		$content = $this->cObj->substituteMarkerArrayCached($this->tip_thankyou, $markerArray, array(), array());
		//we prepare the text 
		$text_to_be_send = $this->pi_getLL('start_mail_message') . "\n\n";
		$text_to_be_send .= '<'.$this->submit_vars['hidden_referer'] .'>' .  "\n\n";
		$text_to_be_send .= $this->submit_vars['my_message']  . "\n\n";
		$text_to_be_send .= $this->pi_getLL('end_mail_message');		
		//we send the mail using the dmailer engine
		$subject = $this->pi_getLL('mail_subject');		 
		$headers = 'From: '.$this->submit_vars['my_name'].'<'.$this->submit_vars['my_email'].'>';
		mail($this->submit_vars['email1'], $subject, $text_to_be_send, $headers);
		if($this->submit_vars['email2']){
			mail($this->submit_vars['email2'], $subject, $text_to_be_send, $headers);
		}
		if($this->submit_vars['email3']){
			mail($this->submit_vars['email3'], $subject, $text_to_be_send, $headers);
		}
		return  $content;
	}
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/mimi_tipfriends/pi1/class.tx_mimitipfriends_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/mimi_tipfriends/pi1/class.tx_mimitipfriends_pi1.php"]);
}

?>