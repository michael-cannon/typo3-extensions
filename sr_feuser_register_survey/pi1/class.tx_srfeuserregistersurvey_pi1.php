<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Chetan Thapliyal (chetan@srijan.in)
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
* Plugin 'Registration survey' for the 'sr_feuser_register_survey' extension.
*
* @author	Chetan Thapliyal <chetan@srijan.in>
*/

require_once(PATH_tslib."class.tslib_pibase.php");

// For integrating survey
require_once(t3lib_extMgm::extPath('sr_feuser_register_survey').'class.sr_feuser_register_survey.php');
require_once(t3lib_extMgm::extPath('sr_feuser_register_survey').'class.sr_feuser_register_survey_user.php');


class tx_srfeuserregistersurvey_pi1 extends tslib_pibase {
	var $prefixId = "tx_srfeuserregistersurvey_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_srfeuserregistersurvey_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "sr_feuser_register_survey";	// The extension key.
	
	/**
	* [Put your description here]
	*/

	/**
	 * Object to `tx_mssurvey_user` class
	 * @var class
	 */
	var $objUser;
	
	/**
	* Object to sr_feuser_register_survey class
	*
	* @access private
	* @var class
	*/
	var $survey;
	
	/**
	* Object to user_survey_check class
	*
	* @access private
	* @var class
	*/
	var $surveyConfig;
	
	/**
	 * Array to store survey results
	 *
	 * @access private
	 * @var array
	 */
	var $surveyResults;
	
	/**
	 * Page to redirect to after submitting survey
	 *
	 * @access private
	 * @var string
	 */
	var $redirect;
	
	
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		// Initialize class variables
		$this->init();
		
		$content = $this->getContent();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Function to initialize class variables
	 *
	 * @access private
	 */
	function init() {
		$this->survey = t3lib_div::makeInstance('sr_feuser_register_survey');
		$this->objUser = t3lib_div::makeInstance('sr_feuser_register_survey_user');		
		$this->survey->setTemplateFile( PATH_site.$GLOBALS['TSFE']->tmpl->getFileName( $this->conf['templateFile']));
		$tmp = t3lib_div::_GP('FE');
		$this->surveyResults = $tmp['fe_users']['tx_mssurvey_pi1'];
		$this->survey->setUserSurveyResponse( $this->surveyResults);
		$this->redirect = ( t3lib_div::_GP('redirect') !== '')
		                  ? t3lib_div::_GP('redirect')
		                  : 'index.php';
	}
	
	/**
	 * Function to get plugin content
	 *
	 * @access private
	 */
	function getContent() {
		$content = '';
		
		switch( $this->piVars['cmd']) {
			case 'save':
				$content = $this->updateSurvey();
			break;
			default:
				$content = $this->getSurvey();
		}
		
		return $content;
	}
	
	/**
	 * Function to get the content of survey
	 *
	 * @access private
	 * @return string
	 */
	function getSurvey() {
		$content = '';
		$template['page'] = $this->cObj->fileResource( $this->conf['templateFile']);
		$template['SURVEY_TEMPLATE'] = $this->cObj->getSubpart( $template['page'], 'SURVEY_TEMPLATE');
		$replace = array();
		
		// Add survey error messages
		$replace['MISSING_SURVEY_FIELDS'] = ( $this->survey->hasErrors())
											? $this->survey->getErrorMessages()
											: '';
		$template['SURVEY_TEMPLATE'] = $this->cObj->substituteSubpart($template['SURVEY_TEMPLATE'], 
		                                                              '###SUB_REQUIRED_SURVEY_FIELDS###', 
                                                                       $replace['MISSING_SURVEY_FIELDS']);
		
		$replace['REDIRECT']   = $this->redirect;
        $replace['PAGE_TITLE'] = $GLOBALS['TSFE']->page['title'];
		$replace['FIRST_NAME'] = ( $GLOBALS['TSFE']->fe_user->user['first_name'])
			                     ? $GLOBALS['TSFE']->fe_user->user['first_name']
			                     : 'Member';
		$replace['FORM_NAME']  = $this->prefixId.'_form';
		$replace['FORM_URL']   = '';
		$replace['SURVEY']     = $this->survey->getSurveyForm();
		$replace['HIDDENFIELDS']  = '<input type="hidden" name="'.$this->prefixId.'[cmd]" value="save" />';
		$replace['HIDDENFIELDS'] .= '<input type="hidden" name="redirect" value="'.$this->redirect.'" />';
		$replace['SUBMIT_BUTTON_LABEL'] = $this->pi_getLL('submit_button_label');
		
		$content = $this->cObj->substituteMarkerArray($template['SURVEY_TEMPLATE'], $replace, '###|###', 1);
		
		return $content;
	}
	
	/**
	 * Function to save/update the user supplied survey data
	 *
	 * @access private
	 * @return string
	 */
	function updateSurvey() {
		$content = '';
		$userID = intval( $GLOBALS['TSFE']->fe_user->user['uid']);
		
		// Check if the user entered survey responce is valid or not
		$validSurvey = $this->survey->isValidSurvey( $this->surveyResults);
		
		if ( $validSurvey) {

			$survey = array(
			    'results' => $this->surveyResults,
			    'userID'  => $userID
			);
			
			$updated = false;
			
			// Save survey against user if it has no entry, else update it.
			if ( $this->survey->userSurveyExists( $userID)) {
            
				$updated = $this->survey->updateSurvey( $survey);
                
			} else {
            
				$updated = $this->survey->saveSurvey( $survey);
                
			}

			// Update user membership
			if ( $updated ) {
			    $this->updateUserMembership();
			}

			// MLC have some warning of a failure to fix
			else
			{
				mail( 'michael@peimic.com'
					, 'BPM: Survey update failure'
					, 'file ' . __FILE__
						. "\nline: " . __LINE__ 
						. "\nsurvey" . cbPrintString( $survey )
						. "\nfe_user" . cbPrintString( $GLOBALS['TSFE']->fe_user )
						. "\nthis" . cbPrintString( $this )
				);
			}
			
			// Redirect to user selected page
			$url = t3lib_div::locationHeaderUrl( $this->redirect);
			header('Location: '.$url);
		} else {
			$content = $this->getSurvey();
		}
		
		return $content;
	}
	
	/**
	 * Function to update the user membership after supplying survey data
	 *
	 * @access private
	 * @return boolean  Execution status
	 */
	function updateUserMembership() {
	    $status = false;
	    
	    $userID = $GLOBALS['TSFE']->fe_user->user['uid'];
	    $userDetails = $this->objUser->getUserDetails( array( 'uid' => $userID));
	    
	    if ( is_array( $userDetails) && !empty( $userDetails)) {
	        $corrUserGroup = $this->survey->getCorrSurveyDomainGroup();

	        if ( is_array( $corrUserGroup) && !empty( $corrUserGroup)) {
				// MLC turn both data sets into arrays, merge, then take
				// unique
				$usergroupArr	= explode( ',', $userDetails['usergroup'] );
				$usergroupArr	= array_merge( $usergroupArr, $corrUserGroup);
				$usergroupArr	= array_unique( $usergroupArr );
				$updateFields	= array(
									       'usergroup' => implode( ',', $usergroupArr)
								       );
    	        
				// MLC actual method created
				$status = $this->objUser->updateUserDetails( $userID, $updateFields);
	        }

			else
			{
				mail( 'michael@peimic.com'
					, 'BPM: Survey update failure A'
					, 'file ' . __FILE__
						. "\nline: " . __LINE__ 
						. "\ncorrUserGroup" . cbPrintString( $corrUserGroup )
						. "\nthis" . cbPrintString( $this )
				);
			}
	    }

		else
		{
			mail( 'michael@peimic.com'
				, 'BPM: Survey update failure B'
				, 'file ' . __FILE__
					. "\nline: " . __LINE__ 
					. "\nfe_user" . cbPrintString( $GLOBALS['TSFE']->fe_user )
					. "\nuserDetails" . cbPrintString( $userDetails )
					. "\nSERVER" . cbPrintString( $_SERVER )
					. "\nPOST" . cbPrintString( $_POST )
					. "\nGET" . cbPrintString( $_GET )
					. "\nthis" . cbPrintString( $this )
			);
		}
	    	    
	    return $status;
	}
	 
	function debug_print( $var) {
		echo '<!--';
		print_r( $var);
		echo '-->';
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register_survey/pi1/class.tx_srfeuserregistersurvey_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register_survey/pi1/class.tx_srfeuserregistersurvey_pi1.php"]);
}

?>
