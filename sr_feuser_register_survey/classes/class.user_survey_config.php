<?php

require_once( t3lib_extMgm::extPath('sr_feuser_register_survey').'class.sr_feuser_register_survey.php');

class user_survey_config {
	
	/**
	* Object to sr_feuser_register_survey class
	*
	* @access private
	* @var class
	*/
	var $survey;
	
	/**
	* Function to initialize class variables
	*
	* @access private
	* @var class
	*/
	function init() {
		$this->survey = t3lib_div::makeInstance('sr_feuser_register_survey');
	}
	
	/**
	* Function to redirect to survey page
	*
	* @access private
	* @param integer $pageID ID of the page in database table
	*/
	function redirectPage( $pageID) 	{
	    $currPageURL = rawurlencode( $this->survey->getCurrentURL());
		$url =  'index.php?id='.$pageID.'&type=0&redirect='.$currPageURL;
		$url = t3lib_div::locationHeaderUrl( $url);
		
		header('Location: '.$url);
	}
	
	/**
	* Function to redirect to survey page if user has pending survey questions
	*
	* @access public
	* @param array $param	Parameters passed to function by hook
	* @param class $parent	Object reference to tslib_fe class
	* @param integer $pageID ID of the page in database table
	*/
	function goForSurvey( $param, $parent) {
		$userID		= ( $GLOBALS['TSFE']->loginUser)
					  ? $GLOBALS['TSFE']->fe_user->user['uid']
					  : 0;

		// cookie name
		$cookieName	= 'surveybypass' . $userID;

		// MLC if no user, no processing
		// MLC if already determined to show survey skip
		if ( ! $userID || isset( $_COOKIE[ $cookieName ]) ) {
			return;
		}

		$this->init();
		$checkForProfileUpdate = $this->survey->getProfileUpdateFlag();
		
		if ( ! $checkForProfileUpdate) {
			return;
		}

		$surveyPID = $this->survey->getSurveyPID();
				  
		// Current page id
		$pid = intval( $GLOBALS['TSFE']->id);
		
		// Pages to include for survey check. This will override pages in $excludePIDList
		$includePIDList = $this->survey->getIncludePIDList();

		if ( intval( $GLOBALS['TSFE']->page['tx_srfeuserregistersurvey_survey_check']) > 0) {
            // If survey_check is enable in page header at back-end
            $validSurveyPage = true;
        } elseif ( 0 < count( $includePIDList)) {
            // MLC per client if include is set, then use that
			$validSurveyPage = ( in_array( $pid, $includePIDList) )
							   ? true
							   : false;
		} else {
			// Pages to by-pass for survey check
			$excludePIDList = $this->survey->getExcludePIDList();
			
			// include survey page in by-pass list so as to avoid checking for displaying survey
			$excludePIDList[] = $surveyPID;

			$validSurveyPage = ( in_array( $pid, $excludePIDList) )
							   ? false
							   : true;
		}
		
		// User groups to check for survey
		$includeUserGroups = $this->survey->getIncludeUserGroups();
		
		$userGroup = ( $userID > 0) 
					 ? array_map( 'trim', explode( ',', $GLOBALS['TSFE']->fe_user->user['usergroup']))
					 : array();
		
		$validSurveyUser = ( count( array_intersect(  $userGroup, $includeUserGroups)) > 0)
						   ? true
						   : false;
		
		if ( ( $validSurveyUser)  && ( $validSurveyPage) && ( !isset( $_COOKIE[ $cookieName ]))) {
			// Check for un-answered survey questions
			$userSurveyItems = $this->survey->getSurveyItems( $userID);
			
			if ( (is_array( $userSurveyItems)) && (count( $userSurveyItems) > 0)) {
				// Set cookie to track the user on next visit
				setcookie( $cookieName, true, null, '/');

				// redirect to survey page
				$this->redirectPage( $surveyPID);
			}
		} else {
			// new user, no need to redirect to survey page
		}
	}
	
	function debug_print( $var) {
		echo '<!--';
		print_r( $var);
		echo '-->';
	}
}

?>
