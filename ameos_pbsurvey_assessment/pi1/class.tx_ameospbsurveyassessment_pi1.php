<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Luc Muller <typo3dev@ameos.com>
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
 * Plugin 'Ameos pbsurvey assessment' for the 'ameos_pbsurvey_assessment' extension.
 *
 * @author	Luc Muller <typo3dev@ameos.com>
 * @package	TYPO3
 * @subpackage	tx_ameospbsurveyassessment
 */
class tx_ameospbsurveyassessment_pi1 extends tslib_pibase {
	var $prefixId = 'tx_ameospbsurveyassessment_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_ameospbsurveyassessment_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'ameos_pbsurvey_assessment';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */

	 var $aFlexConf = array();
	 var $iPid = null; //int for pi
	 var $iRid = null; // int for result id

	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->_initFlex();

		if($GLOBALS["TSFE"]->tmpl->setup["config."]["language"]){
			$sUseLang = "v".strtoupper($GLOBALS["TSFE"]->tmpl->setup["config."]["language"]);
		}else{
			$sUseLang = "vDEF";
		}

		$this->iPid = $GLOBALS["TSFE"]->page["pid"];

		$this->iResId = $_COOKIE["pbsurvey"][$this->iPid]["rid"];

		if($this->iResId == ""){
			$sContent = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:error');
			return $this->pi_wrapInBaseClass($sContent);
			exit();
		}

		$scoretype = $this->conf['scoretype'];
		switch ($scoretype) {
			case 'points':
				$results = $this->_pointScoring();
				$sContent .= $results;
				break;

			default:
				$results = $this->_modScoring($scoretype);
				$sContent .= $results;
				break;
		}

		// show or hide mail form and send mail
		$mailform = $this->conf['mailform'];

		if($mailform == 'show') {
			$sContent .= '<a name="mailTop"></a>';

			if(isset($_REQUEST['survey_email'])) { 
				$sent = false;
				$email = $_REQUEST['survey_email'];

				// validate survey_email
				if ( t3lib_div::validEmail($email) ) {
					$message = $this->pi_getLL('emailResultsMessage');
					$linkText = $this->pi_getLL('emailResultsSurvey');
					$pageLink = $this->pi_getPageLink($GLOBALS["TSFE"]->page["pid"]);
					$message .= sprintf($linkText, $pageLink);
					$message .= $results;
					$sent = $this->_sendMail($email, $this->pi_getLL('emailResultsSubject'), $message);
				}

				if ( $sent ) {
					// show mail sent message
					$sContent .= $this->_mailSent();
				} else {
					$sContent .= $this->_mailNotSent();
				}
			}

			// always show mail form in case of sequentil mailings
			$sContent .= $this->_mailForm(); // return a mail form
		}

		return $this->pi_wrapInBaseClass($sContent);
	}

	function _modScoring($mod) {
		$sContent = '_customScoring ' . $mod;

		// check for submitted answers
		$db = $GLOBALS["TYPO3_DB"];

		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_results",
			"uid = ".$iResId." AND deleted != 1 AND hidden != 1",
			"",
			"uid ASC"
		);

		 while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {
			if($aItem["endtstamp"] == "0"){
				$sContent = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:mustfinish');
				return $this->pi_wrapInBaseClass($sContent);
			}
		}

		// cycle through answers grouping them by scoretype e.g. modulo math
		$db = $GLOBALS["TYPO3_DB"];

		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_answers",
			"result = ".$this->iResId." AND deleted != 1 AND hidden != 1",
			"",
			"question ASC"
		);

		$aAnswers = array();
		while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {
			$modMath = 	$aItem['row'] % $mod;
			// sum answer points by grouping via modulo math
			$aAnswers[ $modMath ] += $aItem['answer'];
		}
	 	// echo '<pre>'; print_r($aAnswers); echo '</pre>';

		// pull results options
		$aResults = $this->conf['results.'];
	 	// echo '<pre>aResults '; print_r($aResults); echo '</pre>';

		// match results with answers by key
		$aFinalAnswers = array();
		$aWeight = array();
		foreach ( $aResults AS $key => $value ) {
			$key = str_replace( '.', '', $key );
			$aFinalAnswers[ $key ]['title'] = $value['title'];
			$aFinalAnswers[ $key ]['description'] = $value['description'];
			$aFinalAnswers[ $key ]['weight'] = $aAnswers[ $key ];
			$aWeight[ $key ] = $aAnswers[ $key ];
		}
	 	// echo '<pre>aFinalAnswers '; print_r($aFinalAnswers); echo '</pre>';

		// order answers by resultordering via weight<br />';
		if ( $this->conf['resultordering'] == 'desc' ) {
			array_multisort($aWeight, SORT_DESC, $aFinalAnswers);
		} else {
			array_multisort($aWeight, SORT_ASC, $aFinalAnswers);
		}
	 	// echo '<pre>aFinalAnswers '; print_r($aFinalAnswers); echo '</pre>';

		// create marker structure fill with results & answers
		$aMarkers = array();
		$iResultslimit = $this->conf['resultslimit'];
	 	// echo '<pre>iResultslimit '; print_r($iResultslimit); echo '</pre>';

		$aResultsWrap = $this->conf['resultsWrap.'];
	 	// echo '<pre>aResultsWrap '; print_r($aResultsWrap); echo '</pre>';

		// limit results to resultslimit
		for ( $i = 0; $i < $iResultslimit; $i++ ) {
			$key = $aFinalAnswers[ $i ];
			
			// pull template components
			$sTitle = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:title');
			// replace template markers with result entries
			$sTitle = $this->cObj->stdWrap(str_replace("###TITLE###",$key['title'],$sTitle), $aResultsWrap['title.']);
			$aMarkers[] = $sTitle;

			$sWeight = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:weight');
			$sWeight = $this->cObj->stdWrap(str_replace("###WEIGHT###",$key['weight'],$sWeight), $aResultsWrap['weight.']);
			$aMarkers[] = $sWeight;

			$sDescription = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:description');
			$sDescription = $this->cObj->stdWrap(str_replace("###DESCRIPTION###",$key['description'],$sDescription), $aResultsWrap['description.']);
			$aMarkers[] = $sDescription;

		}
		// echo '<pre>aMarkers ' . ' : '; print_r($aMarkers); echo '</pre>';	

		// build up content to display 
		$sContent = implode("\n", $aMarkers);

		return $sContent;
	}

	function _pointScoring() {
		$iTotalScore = $this->_GetTotalScore($this->iPid,$this->iResId);

		if(!$iTotalScore){
			$sContent = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:mustfinish');
			return $this->pi_wrapInBaseClass($sContent);
			exit();
		}

		$iMyScore = $this->_GetMyScore($this->iResId);

		$iMyScore2 = intval(($iMyScore*intval($this->conf['questioncount']))/$iTotalScore);

		$aFLexConf = $this->aFlex;
		
		if ($aFLexConf["score"][$sUseLang] == "1") {
			$sScore = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:scoreis');
			$sScore = str_replace("###SCORE###",$iMyScore2,$sScore);
			$sContent = "<p>".$sScore."</p>";
		}

		if($aFLexConf["pourcent"][$sUseLang] == "1"){
			$iPourcent = intval(((intval($iMyScore)*100)/intval($iTotalScore)));
			$sScore = $GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:percent');
			$sScore = str_replace("###%###",$iPourcent,$sScore);
			$sContent.="<p>".$sScore."</p>
			";
		}

		$aLevelInfo = $aFLexConf["palliers"]["el"];

		$aLevel = $this->_GetLevelValue($aLevelInfo,"niveau",$sUseLang);

		$aTitre = $this->_GetLevelValue($aLevelInfo,"titre",$sUseLang);

		$aContenu = $this->_GetLevelValue($aLevelInfo,"contenu",$sUseLang);

		$sContent .= $this->_GetBilanInfos($aLevel,$aTitre,$aContenu,$iMyScore2);

		if ($aFLexConf["pageretour"][$sUseLang]) {
			$sPageRetourLink = $this->_GetPageRetourLink($aFLexConf,$sUseLang);
		}
		$sContent .= $sPageRetourLink;

		return $sContent;
	}

	function _Label($sPath) {
	if(TYPO3_MODE == "FE") {
			// front end
			return $GLOBALS["TSFE"]->sL($sPath);
		}
		else {
			// back end
			return $GLOBALS["LANG"]->sL($sPath);
		}
	}

		function _initFlex()
			{
				// on initialise la configuration du flexform du BE
				$this->pi_initPIflexForm();		// Init and get the flexform data of the plugin
				$this->aFlex = array();		// Setup our storage array...
				
				// Assign the flexform data to a local variable for easier access
				$piFlexForm = $this->cObj->data['pi_flexform'];


				if(is_array($piFlexForm) && array_key_exists("data", $piFlexForm))
				{
					while(list($sheet, $data) = each($piFlexForm['data']))	// Traverse the entire array based on the language ... and assign each configuration option to $this->lConf array...
					{
						foreach($data as $lang => $value)
						{
							foreach($value as $key => $val)
							{ $this->aFlex[$key] = $val;}
						}
					}
				}
			}
        

	function _GetTotalScore($iPid,$iResId) {
		$db = $GLOBALS["TYPO3_DB"];

		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_results",
			"uid = ".$iResId." AND deleted != 1 AND hidden != 1",
			"",
			"uid ASC"
		);

		 while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {
			if($aItem["endtstamp"] == "0" || $aItem['finished'] == 0){
				return false;
				exit();
			}
		 }

		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_item",
			"pid = ".$iPid." AND deleted != 1 AND hidden != 1",
			"",
			"uid ASC"
		);

		$aAnswers = array();

		 while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {

			if($aItem["answers"]){
				$aAnswers[] = $aItem["answers"];
			}
		 }

		$aExpAnswers = array();
		foreach	($aAnswers as $key=>$val){
			$aExpAnswers = array_merge($aExpAnswers,explode("\n",$val));
		}


		$aExpAnswers2 = array();
		foreach ($aExpAnswers as $key=>$val){
			$transtable = explode("|",$val);
			$aExpAnswers2 = array_merge($aExpAnswers2,$transtable["1"]);
		}


		foreach ($aExpAnswers2 as $key=>$val){
			if($val > 0){
				$iScoreTotal += intval($val);
			}
		}

		return $iScoreTotal;

	}

	function _GetMyScore($iResId) {
		
		$db = $GLOBALS["TYPO3_DB"];

		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_answers",
			"result = ".$iResId." AND deleted != 1 AND hidden != 1",
			"",
			"question ASC"
		);

		while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {

			$iScore += $this->_GetAnswerScore($aItem["question"],$aItem["answer"]);

		}

		if($iScore < '0'){
			$iScore = '0';
		}

		return $iScore;
	}

	function _GetAnswerScore($iQuestionId,$iAnswerLine) {

		$iAnswerLine = $iAnswerLine - 1;

		$db = $GLOBALS["TYPO3_DB"];
	
		$rSql = $db->exec_SELECTquery(
			"*",
			"tx_pbsurvey_item",
			"uid = ".$iQuestionId." AND deleted != 1 AND hidden != 1",
			"",
			"uid ASC"
		);

		$aAnswers = array();

		while(($aItem = $db->sql_fetch_assoc($rSql)) !== FALSE) {

			if($aItem["answers"]){
				$aAnswers[] = $aItem["answers"];
			}

		}

		$aAnsLine = explode("\n",$aAnswers["0"]);

		$aScore = explode("|",$aAnsLine[$iAnswerLine]);

		$iScore = $aScore["1"];

		return intval($iScore);

	}

	function _GetLevelValue($aFlexInfo,$sInfo,$sUseLang) {
	
		foreach ($aFlexInfo as $key=>$val){
			$aLevelValue = array_merge($aLevelValue,$val["pallier"]["el"][$sInfo][$sUseLang]);
		}

		return $aLevelValue;
	}

	function _GetBilanInfos($aLevel,$aTitre,$aContenu,$iMyScore){

		foreach($aLevel as $key=>$val){
			if($iMyScore < $val){
				$Level = $key;
				break;
			}
		}

		$sContent = "<h3>".$aTitre["$Level"]."</h3>
		<p>".nl2br(strip_tags($aContenu[$Level]))."</p>";

		return $sContent;

	}

	function _GetPageRetourLink($aConf,$sUseLang) {


		$iIdPage = $aConf["pageretour"][$sUseLang];

		$aTypoConf = Array();
		
		$aTypoConf["parameter"] = $iIdPage;
		$aTypoConf["ATagParams"] = "";

		$sContent = $this->cObj->typolink($GLOBALS["TSFE"]->sL('LLL:EXT:ameos_pbsurvey_assessment/pi1/locallang.xml:viewresults'),$aTypoConf);

		return $sContent;

	}

	/*
	 * Show mail NOT sent
	 */
	function _mailNotSent() {
		$sContent = $this->pi_getLL('emailNotSentHeader');
		$sContent .= $this->pi_getLL('emailNotSentBody');
		return $sContent;
	}

	/*
	 * Show mail sent
	 */
	function _mailSent() {
		$sContent = $this->pi_getLL('emailSentHeader');
		$sContent .= $this->pi_getLL('emailSentBody');
		return $sContent;
	}
	
	/*
	* Send survey result by email form
	*/
	function _mailForm() {
		$pageLink = $this->pi_getPageLink($this->cObj->data['pid']);
		// echo 'pageLink' . ' : '; print_r($pageLink); echo '<br />';	
		$sContent = $this->pi_getLL('emailSendHeader');
		$sContent .= $this->pi_getLL('emailSendBody');
		$sContent .= '<form name="mail_survey_result" method="post" action="'.$pageLink.'#mailTop">';
		$sContent .= '<input type="text" value="" name="survey_email" />';
		$sContent .= '<input type="submit" value="' . $this->pi_getLL('emailSendButton') . '" />';
		$sContent .= '</form>';

		return $sContent;
	}


	/**
	 * sample function for sending HTML via TYPO3 built in method.
	 */
	function _sendMail($recipients, $subject, $content) {
		// Get an instance of the TYPO3 mailer class
		$mailer = t3lib_div::makeInstance('t3lib_htmlmail');
		$mailer->start();

		// Set the encoding to base64
		// $mailer->useBase64();
		$mailer->use8Bit();

		// Get/set the recipient(s)
		if(!is_array($recipients)) {
			$recipients = t3lib_div::trimExplode(',', $recipients, 1);
		}
		$mailer->setRecipient($recipients);

		// Subject
		$mailer->subject = $subject;

		// Sender information
		$mailer->from_email = $this->conf['senderemail']; 
		$mailer->from_name = $this->conf['sendername'];

		// Add the plaintext version
		$mailer->addPlain( strip_tags( $content) );
				
		// Add the HTML version 
		$mailer->setHTML($mailer->encodeMsg($content));
				
		// Send the mail
		$mailer->setHeaders();
		$mailer->setContent();
		$mailWasSent = $mailer->sendTheMail();

		return $mailWasSent;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ameos_pbsurvey_assessment/pi1/class.tx_ameospbsurveyassessment_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ameos_pbsurvey_assessment/pi1/class.tx_ameospbsurveyassessment_pi1.php']);
}

?>
