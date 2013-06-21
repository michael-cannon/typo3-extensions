<?php

class sr_feuser_register_survey_user {

	/**
	 * Table containing user details
	 * @var boolean
	 */
	var $tbFeUsers;

	/**
	 * Flag to know whether current user is logged-n or not
	 * @var boolean
	 */
	var $isLoggedIn;

	/**
	 * User-ID for currently logged in user
	 * @var integer
	 */
	var $userID;

	/**
	 * Group-ID for currently logged in user
	 * @var integer
	 */
	var $userGroup;


	/**
	 * Function to initialize class variables
	 * @access private
	 */
	function sr_feuser_register_survey_user() {
		$this->tbFeUsers = 'fe_users';
		$this->isLoggedIn = ($GLOBALS['TSFE']->loginUser) ? true : false;
		$this->userID = ($this->isLoggedIn) ? $GLOBALS['TSFE']->fe_user->user['uid'] : 0;
		$this->userGroup = ($this->isLoggedIn) ? $GLOBALS['TSFE']->fe_user->user['usergroup']: 0;
	}


	/**
	 * Function to fetch details of a particular user from database
	 * @access public
	 * @var array $filters - ID of user in database table
	 * @return array
	 */
	function getUserDetails( $filters) {
		$userDetails = array();
		
		$select = '*';
		$from   = $this->tbFeUsers;
		$where  = '1';
		$where .= ' AND deleted = 0 AND disable = 0';
		
		if ( is_array( $filters) && !empty( $filters)) {
			foreach ( $filters as $key => $value) {
			    $where .= ' AND '.$key.' = '.$value;
			}
		}
		
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $from, $where);
		
		if ( $rs) {
			if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
				$userDetails = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs);
			}
		}
		
		return $userDetails;
	}
	
	/**
	 * Function to update the FE user record
	 *
	 * @access private
	 * @var integer $userID     ID of user against which detail has to update
	 * @var array $updateFields     Array containing user details for update in field/value format
	 * @return string
	 */
	function updateUserDetails( $userID, $updateFields) {
	    $status = false;

	    if ( $userID > 0 
			&& is_array( $updateFields) 
			&& !empty( $updateFields)
		)
		{
			$where				= "uid = $userID";

			// Uncomment this line for debugging
			// cbDebug( 'query', $GLOBALS['TYPO3_DB']->UPDATEquery( $this->tbFeUsers, $where, $updateFields));
		   
		   $GLOBALS['TYPO3_DB']->exec_UPDATEquery( $this->tbFeUsers, $where, $updateFields);
		   
		   if ( !$GLOBALS['TYPO3_DB']->sql_error()) {
			   $status = true;
		   }
	    }
		else
		{
			mail( 'michael@peimic.com'
				, 'BPM: Survey update failure'
				, 'file ' . __FILE__
					. "\nline: " . __LINE__ 
					. "\nuserID" . cbPrintString( $userID )
					. "\nthis" . cbPrintString( $this )
			);
		}
	    	    
	    return $status;
	}
	
	function debug_print( $var) {
        echo '<!-- ';
        print_r( $var);
        echo ' -->';
	}
}
?>
