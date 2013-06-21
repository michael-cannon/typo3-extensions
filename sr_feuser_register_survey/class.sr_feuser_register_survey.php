<?php

require_once( t3lib_extMgm::extPath('sr_feuser_register_survey').'class.sr_feuser_register_survey_user.php');
require_once( PATH_site.'tslib/class.tslib_content.php');
require_once( PATH_t3lib.'class.t3lib_befunc.php');


class sr_feuser_register_survey extends tslib_cObj {

	/**
	 * Object to `tx_mssurvey_user` class
	 * @var class
	 */
	var $objUser;

	/**
	 * Table containing survey records
	 * @var string
	 */
	var $tbSurveyItems;

	/**
	 * Table containing survey records
	 * @var string
	 */
	var $tbSurveyItemsGroupID;

	/**
	 * Table containing survey results
	 * @var string
	 */
	var $tbSurveyResults;

    /**
     * Table containing archived survey results
     * @var string
     */
    var $tbArchivedSurveyResults;

	/**
	 * Survey usergroup relationship table
	 * @var string
	 */
	var $tbSurveyDomainUsergroups;

	/**
	 * ID of the page were survey plugin is installed
	 * @var class
	 */
	var $surveyPID;

	/**
	 * ID of the page were survey items are stored
	 * @var class
	 */
	var $storagePID;

	/**
	 * ID of the page were survey results will be stored
	 * @var class
	 */
	var $resultsPID;

	/**
	 * Variable containing user groups of current domain and survey
	 * @var array
	 */
	var $surveyDomainGroups;

	/**
	 * Flag containing error status of user entered survey
	 * @var boolean
	 */
	var $surveyStatus;
	
	/**
	 * Error message
	 * @var string
	 */
	var $errorMessages;

	/**
	 * Template file to be used for Survey
	 * @var class
	 */
	var $templateFile;

	/**
	 * Variable containing content of template file
	 * @var string
	 */
	var $template;

	/**
	 * Array containing user response to survey.
	 * @var array
	 */
	var $userSurveyResponse;

	/**
	 * List of page ids to by-pass for survey check
	 * @var array
	 */
	var $excludePIDList;

	/**
	 * List of pages to check for survey. This will 
	 * override the pages listed in $excludePIDList
	 *
	 * @access private
	 * @var array
	 */
	var $includePIDList;

	/**
	 * List of user groups to not check for survey
	 * @var array
	 */
	var $includeUserGroups;

	/**
	 * Debug output is on or off
	 * @var debug
	 */
	var $debug;

	/**
	 * Survey configuration typoscript
	 * @var array
	 */
	var $surveyTSconf;

	/**
	 * Type of survey i.e. join/upgrade
	 * @var string
	 */
	var $surveyType;

	/**
	 * Flag to check for profile update or not
	 *
	 * @access private
	 * @var boolean
	 */
	var $checkForProfileUpdate;

    /**
     * Current domain identifier
     *
     * This variable holds the usergroup value for the current domain/website
     *
     * @access private
     * @var integer
     */
     var $currDomain;

	/**
	 * Function to to set the location of survey items
	 * @access public
	 * @var integer $pid - Page-ID where survey items are stored
	 */
	function setStoragePID( $pid) {
		$this->storagePID = $pid;
	}

	/**
	 * Function to to set the location of survey results
	 * @access public
	 * @var integer $pid - Page-ID where survey items are stored
	 */
	function setResultsPID( $pid) {
		$this->resultsPID = $pid;
	}

	/**
	 * Function to get the id of page where survey plugin is installed
	 *
	 * @access public
	 * @var integer $pid - Page-ID where survey items are stored
	 */
	function getSurveyPID() {
		return $this->surveyPID;
	}

	/**
	 * Function to get the excludePIDList class variable
	 *
	 * @access public
	 * @return array	List of pids to be by-passed for survey check
	 */
	function getExcludePIDList() {
		return $this->excludePIDList;
	}

	/**
	 * Function to get the includePIDList class variable
	 *
	 * @access public
	 * @return array	List of pids to be checked for survey
	 */
	function getIncludePIDList() {
		return $this->includePIDList;
	}
	
	/**
	 * Function to get the user groups to be by-passed for survey check
	 *
	 * @access public
	 * @return array	List of user groups
	 */
	function getIncludeUserGroups() {
		return $this->includeUserGroups;
	}

	/**
	 * This function returns the corresponding user group of user in present domain
	 *
	 * @access public
	 * @return string
	 */
	function getCorrSurveyDomainGroup() {
		return $this->surveyDomainGroups;
	}

	/**
	 * Function to access profile update flag
	 *
	 * @access public
	 * @return boolean
	 */
	function getProfileUpdateFlag() {
		return $this->checkForProfileUpdate;
	}

	/**
	 * Function to set the location of survey template
	 * @access public
	 * @var string $filePath - Page-ID where survey items are stored
	 */
	function setTemplateFile( $filePath) {
		$this->templateFile = $filePath;
		$this->template['page'] = file_get_contents( $this->templateFile);
	}

	/**
	 * Function to set the user responce to survey
	 * @access public
	 * @var string $surveyResponse - Array constaining user responce to survey questions
	 */
	function setUserSurveyResponse( $surveyResponse) {
		if ( is_array( $surveyResponse) && !empty( $surveyResponse)) {
			$this->userSurveyResponse = $surveyResponse;
		}
	}

	/**
	 * Function to set the type of survey
	 * @access public
	 * @var string $surveyType     Type of survey i.e. join/upgrade
	 */
	function setSurveyType( $surveyType) {

	    $this->surveyType = ( $surveyType === 'upgrade')
	                        ? $surveyType
	                        : 'join';
	}

	/**
	 * Function to get the error message
	 * @access public
	 * @return string
	 */
	function getErrorMessages() {
		$errorMessages = '';
		
		if ( (count( $this->errorMessages) > 0) && (strcmp( $this->template['page'], ''))) {
			$this->template['SUB_REQUIRED_SURVEY_FIELDS'] = $this->getSubpart( $this->template['page'], 'SUB_REQUIRED_SURVEY_FIELDS');

			if ( strcmp( $this->template['SUB_REQUIRED_SURVEY_FIELDS'], '')) {
				foreach ( $this->errorMessages as $value) {
					$tmp = $this->template['SUB_REQUIRED_SURVEY_FIELDS'];
					$replace = array(
					'MISSING_SURVEY_FIELDS' => $value
					);
					$errorMessages .= $this->substituteMarkerArray( $tmp, $replace, '###|###', 0);
				}
			}
		}
		
		return $errorMessages;
	}

	
	/**
    * Turn on debug status conditionally.
    * Put your IP here to see debug information.
    * @return void
    * @author js
    */
    function setDebugStatus(){
        //change the true below to false to always not show debug info
		if ( true && ('59.94.211.80' == $_SERVER[ 'REMOTE_ADDR' ] 
			|| '70.84.110.68' == $_SERVER[ 'REMOTE_ADDR' ]
			|| '24.61.220.205' == $_SERVER[ 'REMOTE_ADDR' ]
			)
		) {
            $this->debug = true;
        }
    }
	
	/**
	 * Function to initialize class variables
	 * @access private
	 */
	function sr_feuser_register_survey() {
		$this->setDebugStatus();
		$this->objUser = t3lib_div::makeInstance('sr_feuser_register_survey_user');
		$this->tbSurveyItems = 'tx_mssurvey_items';
		$this->tbSurveyItemsGroupID = 'tx_mssurvey_items_item_groups_mm';
		$this->tbSurveyResults = 'tx_mssurvey_results';
        $this->tbArchivedSurveyResults = 'tx_srfeuserregistersurvey_results_archive';
		$this->tbSurveyDomainUsergroups = 'tt_content_tx_srfeuserregistersurvey_survey_usergroups_mm';
		$this->surveyStatus = false;
		$this->errorMessages = array();
		$this->templateFile = '';
		$this->template = array();
		$this->userSurveyResponse = array();
		$this->surveyType = 'join';

		$this->surveyTSconf = $this->getSurveyTSconf();
		// cbDebug( __FILE__, __LINE__ );	
		// cbDebug( 'this->surveyTSconf', $this->surveyTSconf );	
		$this->surveyDomainGroups = $this->getSurveyDomainGroups( $this->surveyTSconf);
		
		$this->currDomain = ( isset( $this->surveyTSconf['domainUsergroup']))
                            ? $this->surveyTSconf['domainUsergroup']
                            : 0;
        
        $this->surveyPID = ( isset( $this->surveyTSconf['surveyPID']))
		                   ? intval( trim( $this->surveyTSconf['surveyPID']))
		                   : $GLOBALS['TSFE']->id;
		                   
		$this->storagePID = ( isset( $this->surveyTSconf['questionsPID']))
		                    ? intval( trim( $this->surveyTSconf['questionsPID'], ','))
		                    : $GLOBALS['TSFE']->id;
		                   
		$this->resultsPID = ( isset( $this->surveyTSconf['resultsPID']))
		                    ? intval( trim( $this->surveyTSconf['resultsPID']))
		                    : $GLOBALS['TSFE']->id;
		
		$this->includeUserGroups = ( isset( $this->surveyTSconf['includeUserGroups']) && ($this->surveyTSconf['includeUserGroups'] !== ''))
		                           ? array_map( 'trim', explode( ',', $this->surveyTSconf['includeUserGroups']))
		                           : array();
		                           
		$this->excludePIDList = ( isset( $this->surveyTSconf['excludePIDList']) && ($this->surveyTSconf['excludePIDList'] !== ''))
		                        ? array_map( 'trim', explode( ',', $this->surveyTSconf['excludePIDList']))
		                        : array();

		$this->includePIDList = ( isset( $this->surveyTSconf['includePIDList']) && ($this->surveyTSconf['includePIDList'] !== ''))
		                        ? array_map( 'trim', explode( ',', $this->surveyTSconf['includePIDList']))
		                        : array();
		
		$this->checkForProfileUpdate = ( isset( $this->surveyTSconf['profileUpdate']) && ( (intval( $this->surveyTSconf['profileUpdate'])) != 0))
		                               ? true
		                               : false;
	}

	/**
	 * Function to check the error status of user entered survey values
	 * @access public
	 * @return boolean
	 */
	function hasErrors() {
		return ( $this->surveyStatus)
			   ? false
			   : true;
	}

	/**
	 * Fetch survey items corresponding to a particular user group
	 *
	 * @access public
	 * @var integer $userID - Username of survey user
	 * @var string $type      Type of survey items to return i.e. for join/upgrade
	 * @return array
	 */
	function getSurveyItems( $userID, $type = 'JOIN') {
		$surveyItems = array();
		$userID = intval( $userID);
		
		// Get ids of all the survey item that user has answered
		$answeredSurveyItemIds = ( $userID > 0)
								 ? $this->getUserAnsweredItemIds( $userID)
								 : array();
                                 
		$userGroup = $this->objUser->userGroup;
        $surveyType = trim( $this->surveyTSconf['type']);
        
        if ( $surveyType === '') {
            $surveyType = 'JOIN';
        }
        
		$surveyDomainGroups = ( $surveyType === 'UPGRADE')
		                      ? $this->getSurveyDomainGroups( $this->surveyTSconf, $surveyType)
		                      : $this->surveyDomainGroups;
		                      
		$currDomainSurveyGroups = ( $userGroup !== '')
		                          ? array_intersect( explode( ',', $userGroup), $surveyDomainGroups)
		                          : $surveyDomainGroups;
		
		$domainSurveyGroup = ( !empty( $currDomainSurveyGroups))
							 ? $currDomainSurveyGroups
							 : $surveyDomainGroups;
							 
		// Get ids of all the mandatory survey item that belong to current user's group
		$mandatorySurveyItemIds = $this->getMandatorySurveyItemIds( $domainSurveyGroup);
		$remainingMandatorySurveyItems = array_diff( $mandatorySurveyItemIds, $answeredSurveyItemIds);
        
		if ( ( count( $remainingMandatorySurveyItems) > 0) || ( strtoupper($this->surveyTSconf['type']) === 'RENEW')) {
			$select = 'DISTINCT *';
			$from   = $this->tbSurveyItems.' AS si';
			$join   = ( !empty( $domainSurveyGroup))
					  ? ' LEFT JOIN '.$this->tbSurveyItemsGroupID.' AS si_gid ON si.uid = si_gid.uid_local'
					  : '';
			$where  = '1';
			$where .= ( $this->storagePID > 0)
					  ? ' AND si.pid IN ('.$this->storagePID.')'
					  : '';
			$where .= ( !empty( $domainSurveyGroup))
					  ? ' AND uid_foreign IN ('.implode( ',', $domainSurveyGroup).' )'
					  : '';
			$where .= ( (count( $answeredSurveyItemIds) > 0) && ( strtoupper( $this->surveyTSconf['type']) !== 'RENEW'))
					  ? ' AND uid NOT IN ('.implode( ',', $answeredSurveyItemIds).')'
					  : '';
			$where .= ' AND deleted = 0 AND hidden = 0';
			$orderBy = 'si.sorting';

            // Uncomment to debug
			// cbDebug( __FILE__, __LINE__ );	
            $this->debug_print( $GLOBALS['TYPO3_DB']->SELECTquery( $select, $from.$join, $where, '', $orderBy));
            
            if ( $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $from.$join, $where, '', $orderBy)) {
				if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
					while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
						$tmp[$row['uid']][] = $row['uid_foreign'];
						$surveyItems[$row['uid']] = $row;
					}
				
                    if ( !empty( $tmp)) {
    				    foreach ( $tmp as $key => $value) {
    				        if ( is_array( $value) && !empty( $value)) {
    				            if ( count( array_diff( $domainSurveyGroup, $value)) > 0) {
    				                unset( $surveyItems[$key]);
    				            }
    				        }
    				    }
    				}
				}
			}
		}

		return $surveyItems;
	}

	/**
	 * Function to fetch the ids of all the mandatory survey items
	 *
	 * @access public
	 * @var integer $domainSurveyGroup	User group of the current domain against
	 *									which mandatory survey items are to fetch.
	 * @return array
	 */
	function getMandatorySurveyItemIds( $domainSurveyGroup) {
		$mandatorySurveyItemIds = array();

		$select = 'si.uid, si_gid.uid_foreign';
		$table  = $this->tbSurveyItems.' AS si';
		$join   = ( !empty( $domainSurveyGroup))
				  ? ' LEFT JOIN '.$this->tbSurveyItemsGroupID.' AS si_gid ON si.uid = si_gid. uid_local'
				  : '';
		$where  = '1';
		$where .= ' AND optional = 0';
		$where .= ( $this->storagePID > 0)
				  ? ' AND si.pid IN ('.$this->storagePID.')'
				  : '';
		$where .= ( !empty( $domainSurveyGroup))
				  ? ' AND si_gid.uid_foreign IN ('.implode( ',', $domainSurveyGroup).' )'
				  : '';
		$where .= ' AND deleted = 0 AND hidden = 0';
		$orderBy = 'si.uid';

		// uncomment for debugging
		// cbDebug( __FILE__, __LINE__ );	
		$this->debug_print($GLOBALS['TYPO3_DB']->SELECTquery( $select, $table.$join, $where, '', $orderBy));

		if ( $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table.$join, $where, '', $orderBy)) {
			if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
			    $tmp = array();
			    
				while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
			    	$tmp[$row['uid']][] = $row['uid_foreign'];
				}
				
				if ( !empty( $tmp)) {
				    foreach ( $tmp as $key => $value) {
				        if ( is_array( $value) && !empty( $value)) {
				            if ( count( array_diff( $domainSurveyGroup, $value)) < 1) {
				                $mandatorySurveyItemIds[] = $key;
				            }
				        }
				    }
				}
			}
		}

		return $mandatorySurveyItemIds;
	}

	/**
	 * Function to save survey results
	 *
	 * @access public
	 * @var array $survey - Array containing survey result
	 * @return boolean
	 */
	function saveSurvey( $survey) {
		$this->setDebugStatus();
		$status = false;
        
        // Save/Update new record to database table
		$results = $this->createCSV( $this->cleanVars( $survey['results']));
		$tstamp  = time();
		$table   = $this->tbSurveyResults;
        
		$insertFields = array(
			'pid'             => $this->resultsPID,
			'tstamp'          => $tstamp,
			'crdate'          => $tstamp,
			'fe_cruser_id'    => $survey['userID'],
            'domain_group_id' => $this->currDomain,
			'surveyid'        => $this->storagePID,
			'results'         => $results
			, 'remoteaddress'   => ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
									? $_SERVER[ 'REMOTE_ADDR' ]
									: 0
		);
			// cbDebug( __FILE__, __LINE__ );
			// cbDebug( 'insertFields', $insertFields );	

		// uncomment for debugging
		if ($this->debug) {
			echo "saveSurvey( $survey) {";
			// phpinfo();
			echo $results;
			// cbDebug( __FILE__, __LINE__ );	
			$this->debug_print( $GLOBALS['TYPO3_DB']->INSERTquery( $this->tbSurveyResults, $insertFields));
		}
		
		// cbDebug( __FILE__, __LINE__ );
		// cbDebug( 'insertFields', $insertFields );
		// exit();
		$GLOBALS['TYPO3_DB']->exec_INSERTquery( $this->tbSurveyResults, $insertFields);

		if ( !$GLOBALS['TYPO3_DB']->sql_error()) {
			$status = true;
		}
		else
		{
			cbDebug( __FILE__, __LINE__ );
			$this->debug_print( $GLOBALS['TYPO3_DB']->INSERTquery( $this->tbSurveyResults, $insertFields));
		}

		return $status;
	}

    /**
     * Function to archive current survery result of user
     *
     * @access private
     * @param  array $user_survey_record      User survey details to be archived
     *
     * @return integer                        ID of currently archived survey record (in archive table)
     */
    function archiveSurvey( $user_survey_record) {
     
        $archived_survey_id = 0;

        if ( is_array( $user_survey_record) && !empty( $user_survey_record)) {

            $time = time();
            
            $insert_fields = array(
                'tstamp'           => $time,
                'crdate'           => $time,
                'pid'				=> $user_survey_record['pid'],
                'survey_user_id'   => $user_survey_record['fe_cruser_id'],
                'survey_result_id' => $user_survey_record['uid'],
                'survey_crdate'    => $user_survey_record['crdate'],
                'survey_tstamp'    => $user_survey_record['tstamp'],
                'survey_result'    => $user_survey_record['results'], 
                'domain_group_id'  => $user_survey_record['domain_group_id']
                , 'remoteaddress'		=> $user_survey_record['remoteaddress']
            );
			// cbDebug( __FILE__, __LINE__ );
			// cbDebug( 'insert_fields', $insert_fields );	

            // Uncomment to debug it
            // echo $GLOBALS['TYPO3_DB']->INSERTquery( $this->tbArchivedSurveyResults, $insert_fields); 

            if ( $GLOBALS['TYPO3_DB']->exec_INSERTquery( $this->tbArchivedSurveyResults, $insert_fields)) {
                $archived_survey_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
            }

			else
			{
				cbDebug( __FILE__, __LINE__ );
				cbDebug( 'archive insert', $GLOBALS['TYPO3_DB']->INSERTquery( $this->tbArchivedSurveyResults, $insert_fields) ); 
			}
        }

        return $archived_survey_id;
     }

     /**
      * Function to get user record from survey results table
      *
      * @access private
      * @param integer $userID  User-ID corresponds to which we have to retrieve survey results
      *
      * @return array           User survey record from main survey table
      */
     function getUserSurveyRecord( $userID) {
     
        $userSurverRecord = array();

        $select = '*';
        $table  = $this->tbSurveyResults;
        $where  = ' fe_cruser_id = '.$userID;
        $where .= ' AND domain_group_id = '.$this->currDomain;
        $where .= ' AND pid = '.$this->resultsPID;

        // Uncomment to debug it
		// cbDebug( __FILE__, __LINE__ );	
        // cbDebug( 'getUserSurveyRecord', $GLOBALS['TYPO3_DB']->SELECTquery( $select, $table, $where) );

        $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table, $where);

        if ( $rs) {
        
            if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
            
                $userSurveyRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs);
            }
        }

        return $userSurveyRecord;
     }

	/**
	 * Function to update survey results
	 *
	 * @access public
	 * @var array $survey - Array containing survey result
	 * @return boolean
	 */
	function updateSurvey( $survey) {
		$status = false;
	
        $new_results = $this->createCSV( $this->cleanVars( $survey['results']));
        $time = time();
        $update_fields = array();
		
        $prev_survey_record = $this->getUserSurveyRecord( $survey['userID']);
				// cbDebug( __FILE__, __LINE__ );	
				// cbDebug( 'prev_survey_record', $prev_survey_record );	

        // If membership renew then move the existing survey result (if exists any) to archive table
				// cbDebug( __FILE__, __LINE__ );	
		if ( $this->surveyTSconf['type'] == 'RENEW') {
				// cbDebug( __FILE__, __LINE__ );	
        
            if ( is_array( $prev_survey_record) && !empty( $prev_survey_record)) {
				// cbDebug( __FILE__, __LINE__ );	
            
                // If record has archived then go for updation of survey results
                if ( $this->archiveSurvey( $prev_survey_record) > 0) {

                    $update_fields = array(
                        'crdate'          => $time,
                        'tstamp'          => $time,
			            'fe_cruser_id'    => $survey['userID'],
                        'domain_group_id' => $this->currDomain,
			            'surveyid'        => $this->storagePID,
			            'results'         => $new_results
						, 'remoteaddress'   => ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
												? $_SERVER[ 'REMOTE_ADDR' ]
												: 0
					);

				// cbDebug( __FILE__, __LINE__ );	
				// cbDebug( 'update_fields', $update_fields );	
        
                }
            }
        } else {

             $update_fields = array(
                'tstamp'    => $time,
			    'results'   => $prev_survey_record['results'].','.$new_results
             );
				// cbDebug( __FILE__, __LINE__ );	
				// cbDebug( 'update_fields', $update_fields );	
        }
				// cbDebug( __FILE__, __LINE__ );	
      
        if ( !empty ( $update_fields)) {
        
            $table  = $this->tbSurveyResults;
            $where  = ' uid = '.$prev_survey_record['uid'];
                
            // Uncomment to debug
			// cbDebug( __FILE__, __LINE__ );	
			// cbDebug( 'update', $GLOBALS['TYPO3_DB']->UPDATEquery( $table, $where, $update_fields) );
            $exec_status = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( $table, $where, $update_fields);
		    
            if ( !$GLOBALS['TYPO3_DB']->sql_error()) {
			    $status = true;
            }

			else
			{
				cbDebug( __FILE__, __LINE__ );
				cbDebug( 'update', $GLOBALS['TYPO3_DB']->UPDATEquery( $table, $where, $update_fields) );
			}
		}

		// MLC for whatever reason there was no prior record
		else
		{
			$status				= $this->saveSurvey( $survey );
		}

		return $status;
	}

	/**
	 * Function to check and validate the survey results
	 *
	 * @access public
	 * @var array $surveyResultItems   Array containing survey result
	 * @return boolean
	 */
	function isValidSurvey( $surveyResultItems) {
		$status = false;
		$this->surveyStatus = false;
		unset( $this->errorMessages);

		$surveyItems = $this->getSurveyItems( $GLOBALS['TSFE']->fe_user->user['uid'], $this->surveyType);
		$questionNo = 1;

		foreach ( $surveyItems as $item) {
			if ( intval( $item['optional']) < 1) {
		        $userNotAns = !isset( $surveyResultItems[$item['title']]);
		        
			    switch ( $item['type']) {
			        case 2:      // Radio survey item
			        $radioTextNotFilled = false;
			        
			        if ( isset( $surveyResultItems[$item['title'].'_hidden']) && !empty( $surveyResultItems[$item['title'].'_hidden'])) {
			            $radioTextboxItems = array_map( 'trim', explode( '|', $surveyResultItems[$item['title'].'_hidden']));
			            
			            if ( in_array( $surveyResultItems[$item['title']], $radioTextboxItems)) {
			                if ( $surveyResultItems[$item['title'].'_'.$surveyResultItems[$item['title']]] === '') {
                				$radioTextNotFilled = true;
			                }
			            }
			        }
			        
			        if ( $userNotAns || $radioTextNotFilled) {
        				$this->errorMessages[$item['title']] = 'Please answer question '.$questionNo;
			        }
			        break;
			        case 3:      // checked survey item
			        if ( $userNotAns) {
        				$this->errorMessages[$item['title']] = 'Please answer question '.$questionNo;
			        } else {
			            // Check whether textboxes with checked values are filled or not
			            if ( ( is_array( $surveyResultItems[$item['title']]) && !empty( $surveyResultItems[$item['title']]))) {
			                foreach ( $surveyResultItems[$item['title']] as $key => $value) {
			                    $value = trim( $value);
			                    
			                    // Textbox corresponding to checkbox exists and not blank
			                    if ( isset( $surveyResultItems[$item['title'].'_'.$value.'_value'])) {
			                        if ( $surveyResultItems[$item['title'].'_'.$value.'_value'] === '') {
                        				$this->errorMessages[$item['title']] = 'Please answer question '.$questionNo;
			                        }
			                    }
			                }
			            } else {
            				$this->errorMessages[$item['title']] = 'Please answer question '.$questionNo;
			            }
			        }
			        break;
			        default:	 // String, Text item, and others
			        if ( $userNotAns || $surveyResultItems[$item['title']] === '') {
        				$this->errorMessages[$item['title']] = 'Please answer question '.$questionNo;
			        }
			    }
			}

			$questionNo++;
		}

		if ( count( $this->errorMessages) < 1) {
			$status = true;
			$this->surveyStatus = true;
		}
		
		return $status;
	}

	/**
	 * Function to fetch survey results for a particular user
	 *
	 * @access public
	 * @var integer $userID ID of front-end user in database table
	 * @return array
	 */
	function getUserAnswers( $userID) {
		$userAnswers = array();
		$userID = intval( $userID);

		if ( $userID > 0) {

            // We will select questions from all the domains becuase 
            // some of the questions are common for all the domains
			$select = '*';
			$from = $this->tbSurveyResults;
			$where  = ' fe_cruser_id = '.$userID;
			$where .= ' AND pid = '.$this->resultsPID;
        	$where .= ' AND domain_group_id = '.$this->currDomain;
			$where .= ' AND deleted = 0 AND hidden = 0';

			// uncomment to debug
			// cbDebug( __FILE__, __LINE__ );	
			$this->debug_print($GLOBALS['TYPO3_DB']->SELECTquery( $select, $from, $where));

			$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $from, $where);

			if ( $rs) {
				if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {

                    while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
                    
					    $results = $row['results'];
					    $results = array_map( 'trim', explode( ',', $results));

					    foreach ( $results as $value) {
						    list( $itemName, $answer) = explode( ':', $value);
						    $itemName = trim( $itemName, '"');
						    $answer = trim( $answer, '"');
						    $userAnswers[$itemName] = $answer;
					    }
                    
                    }
				}
			}
		}

		return $userAnswers;
	}

	/**
	 * Function to fetch the IDS of all the survey items answered by user
	 *
	 * @access public
	 * @var string $userID - ID of front-end user in database table
	 * @return array
	 */
	function getUserAnsweredItemIds( $userID) {
		$userAnsweredItemIds = array();
		$userAnswers = $this->getUserAnswers( $userID);

		if ( count( $userAnswers) > 0) {
			$tmp = array_keys( $userAnswers);

			foreach ( $tmp as $value) {
				list($itemName, $itemValue) = explode( '_', $value);
				$surveyItemNames[] = "'". mysql_escape_string($itemName) ."'";
			}

			$surveyItemNames = array_unique( $surveyItemNames);
			$surveyItemNames = implode( ',', $surveyItemNames);

			$select = 'uid';
			$from   = $this->tbSurveyItems;
			$where  = 'title IN ('.$surveyItemNames.')';

			// uncomment it to debug
			// cbDebug( __FILE__, __LINE__ );	
			$this->debug_print($GLOBALS['TYPO3_DB']->SELECTquery( $select, $from, $where));

			$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $from, $where);

			if ( $rs) {
				if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
					while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
						$userAnsweredItemIds[] = $row['uid'];
					}
				}
			}
		}

		return $userAnsweredItemIds;
	}

	/**
	 * Function to fetch the survey form
	 *
	 * @access public
	 * @param string $type     Type of survey form i.e. join/upgrade
	 * @return string
	 */
	function getSurveyForm( $type = 'join') {
		$surveyForm = '';

		if ( false === $this->template) {
			return  $surveyForm;
		}

		$surveyItems = $this->getSurveyItems( $GLOBALS['TSFE']->fe_user->user['uid'], $type);

		if ( count( $surveyItems) < 1) {
			return '';
		}

		$questionNo = 1;

		foreach ( $surveyItems as $item) {
			$item['question'] = $questionNo.'. '.$item['question'];
			$surveyForm .= $this->process_var( $item);
			$questionNo++;
		}

		// Add information regarding what sort of survey items to display i.e. for join, or for upgrade
	    $surveyForm .= '<input type="hidden" name="FE[fe_users][surveyType]" value="'.$type.'">';
	    
		return $surveyForm;
	}


	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $row: ...
	 * @param   [type]      $name: ...
	 * @return   [type]      ...
	 */
	function process_var( $row, $name = '') {
		$row['title'] = ($name)
						? trim($name).'_'.trim($row['title'])
						: trim($row['title']);
		$row['missing_value'] = ( isset( $this->errorMessages[$row['title']]))
								? $this->errorMessages[$row['title']]
								: '';

		switch($row['type']){
			case 0:
			$this->variables[] = $row['title'];
			$row['default_value'] = ( !empty( $this->userSurveyResponse) && strcmp( $this->userSurveyResponse[$row['title']], ''))
									? $this->userSurveyResponse[$row['title']]
									:'';
			$out .= ($name)
					? $this->substituteMarkerArray($this->getSubpart($this->template['page'],'SHORTSTRINGITEM'),$row,'###|###',1)
					: $this->substituteMarkerArray($this->getSubpart($this->template['page'],'STRINGITEM'),$row,'###|###',1);
			break;
			case 1:
			$this->variables[] = $row['title'];
			$row['default_value'] = ( !empty( $this->userSurveyResponse) && strcmp( $this->userSurveyResponse[$row['title']], ''))
									? $this->userSurveyResponse[$row['title']]
									:'';
			$out .= ($name)
					? $this->substituteMarkerArray($this->getSubpart($this->template['page'],'SHORTTEXTITEM'),$row,'###|###',1)
					: $this->substituteMarkerArray($this->getSubpart($this->template['page'],'TEXTITEM'),$row,'###|###',1);
			break;
			case 2:
			$this->variables[] = $row['title'];
			$out  = ($name)
					? $this->itemOutput($row,'RADIO','SHORT')
					: $this->itemOutput($row,'RADIO');
			break;
			case 3:
			if ($row['itemvalues']){
				$vars = explode("\n",$row['itemvalues']);
				$vars = array_map('trim', $vars);

				foreach ($vars as $item){
					$this->variables[] = trim(trim($row['title'].'_'.$item),'@');
				}

				$out = ($name)
				? $this->itemOutput($row,'CHECKBOX','SHORT')
				: $this->itemOutput($row,'CHECKBOX');
			}

			break;
			case 4:
			$this->variables[] = $row['title'];
			$row['multi'] = '';
			$row['arr'] = '';
			$row['height'] = '1';

			$out  = ($name)
					? $this->itemOutput($row,'SELECT','SHORT')
					: $this->itemOutput($row,'SELECT');

			break;
			case 5:
			if ( $row['itemvalues']){
				$row['multiple'] = 'multiple = "multiple"';
				$row['arr'] = '[]';
				$row['height'] = '3';
				$vars = explode("\n",$row['itemvalues']);
				$vars = array_map('trim', $vars);
				
				foreach ($vars as $item) {
					$this->variables[] = trim($row['title'].'_'.$item);
				}
				
				$out  = ($name)
						? $this->itemOutput($row,'SELECT','SHORT')
						: $this->itemOutput($row,'SELECT');
			}
			break;
			case 6:
			if ($row['itemrows']){
				$out = $row['description'].'<br>';
				$out .='<table border="1">';
				$itemrows = explode("\n",$row['itemrows']);
				$itemrows = array_map('trim', $itemrows);
				$items = array_map('trim',explode(',',$row['items']));

				if ($row['exclude']){
					$userthere = array_search(strtolower($this->userdata['username']),array_map('strtolower', $itemrows));

					if ($userthere){
						unset($itemrows[$userthere]);
					} elseif ($userthere === 0){
						array_shift($itemrows);
					}
				}

				foreach ( $items as $item){
					$marker['columns'] .= '<th>'.$this->multitems[$item]['question'].'</th>';
				}
				
				foreach ( $itemrows as $itemrow) {
					$marker['rows'] .= '<tr><td>'.$itemrow.'</td>';
					foreach ( $items as $item) {
						$marker['rows'] .= '<td>'.$this->process_var($this->multitems[$item],$itemrow).'</td>';
					}
					$marker['rows'] .= '</tr>';
				}
				$out .= '</table>';
				$marker['missing_value'] = $row['missing_value'];
				$marker['description'] = $row['description'];
				$out = $this->substituteMarkerArray($this->getSubpart($this->template['page'],'MULTITEM'),$marker,'###|###',1);
			}
			break;
		}
		return $out;

	}

	/**
	 * Function to return the html output corresponding to each survey item based upon its configuration
	 *
	 * @param array $surveyItem - Array containing configuration details for a particular survey item
	 * @param string $tmplIdt - (sub)template identifier for the survey item in HTML template
	 * @param string $short - Prefix to template identifier. Identifies type of particular survey item
	 						  (single/multi-dimensional).
	 * @return string
	 */
	function itemOutput( $surveyItem, $tmplIdt, $short='') {
		$output = '';
		
		if ( strtoupper( $tmplIdt) === 'RADIO') {
		    // Special processing for radio button items
		    $output = $this->getRadioSurveyItem( $surveyItem);
		} elseif (  strtoupper( $tmplIdt) === 'CHECKBOX') {
			$output = $this->getCheckedSurveyItem( $surveyItem);
		} else {
    		$template['value'] = $this->getSubpart( $this->template['page'], $tmplIdt.'VALUE');
    		$template['item'] = $this->getSubpart( $this->template['page'], $short.$tmplIdt.'ITEM');
    		$surveyItemValues = explode("\n", $surveyItem['itemvalues']);
    		$surveyItemValues = array_map('trim', $surveyItemValues);
    
    		if ( array_key_exists( $surveyItem['title'], $this->userSurveyResponse)) {
    			// Go for user entered values
    			$response = $this->userSurveyResponse[$surveyItem['title']];
    
    			foreach ( $surveyItemValues as $key => $value) {
    				$value = trim( $value, '@');
    				$valueModified = ( is_array( $response))
    								 ? in_array( $value, $response)
    								 : !strcmp( $value, $response);
    
    				if ( $valueModified) {
    					$surveyItem['checked'] = 'checked';
    					$surveyItem['selected'] = 'selected';
    				} else {
    					$surveyItem['checked'] = '';
    					$surveyItem['selected'] = '';
    				}
    
    				$surveyItem['value'] = $value;
    				$surveyItem['values'] .=  $this->substituteMarkerArray( $template['value'],$surveyItem,'###|###',1);
    			}
    		} else {
    			// Go for default values
    			foreach ( $surveyItemValues as $key => $value) {
    				$isDefaultValue = ( $value{strlen( $value)-1} == '@')
    								  ? true
    								  : false;
    
    				if ( $isDefaultValue) {
    					$surveyItem['checked'] = 'checked';
    					$surveyItem['selected'] = 'selected';
    				} else {
    					$surveyItem['checked'] = '';
    					$surveyItem['selected'] = '';
    				}
    
    				$surveyItem['value'] = trim( $value, '@');
    				$surveyItem['values'] .=  $this->substituteMarkerArray( $template['value'], $surveyItem, '###|###', 1);
    			}
    		}
    
    		$output = $this->substituteMarkerArray( $template['item'], $surveyItem, '###|###', 1);
		}
		
		return $output;
	}

	/**
	 * Function to get radio survey items
	 *
	 * @access pubilc
	 * @param array $radioItemDesc	Array containing radio survey item
	 * @return string
	 */
	function getRadioSurveyItem( $radioItemDesc) {
	    $radioSurveyItem = '';
	    $template = array();
	    
	    if ( is_array( $radioItemDesc) && !empty( $radioItemDesc)) {
    		$radioSurveyItemValues = explode("\n", $radioItemDesc['itemvalues']);
    		$radioSurveyItemValues = array_map('trim', $radioSurveyItemValues);
    	    $hidden = array();
        		
    	    if ( is_array( $radioSurveyItemValues) && !empty( $radioSurveyItemValues)) {
    	        // Get radio survey item template
        		$template['item'] = $this->getSubpart( $this->template['page'], 'RADIOITEM');
        		$itemValues = '';
        		
    	        foreach ( $radioSurveyItemValues as $key => $itemValue) {
    	            // Check for optional textbox with radio button
    	            list( $radioLabel, $conf) = explode( '|', $itemValue);
    	            $radioLabel = trim( $radioLabel);
    	            $conf = trim( $conf);
    	            
    	            // Check whether value is checked or not
    	            $isDefaultValue = ( $radioLabel{strlen( $radioLabel)-1} == '@');
    	            $itemAnsByUser = array_key_exists( $radioItemDesc['title'], $this->userSurveyResponse);
    	            
    	            $radioLabel = trim( trim( $radioLabel, '@'));
    	            $currValueSelected = trim( $this->userSurveyResponse[$radioItemDesc['title']]) === $radioLabel;
    	            
    		        $replace = array(
    		        'checked' => ( $isDefaultValue || ( $itemAnsByUser && $currValueSelected))
								 ? 'checked'
								 : '',
    		        'title' => $radioItemDesc['title'],
    		        'value' => $radioLabel
    		        );
    		        
    	            // Select radio item value template based on configuration (either radio, or radio with textbox)
    	            if ( strtoupper( $conf) === 'TEXT') {
    	                // Radio button with textbox
        		        $template['value'] = $this->getSubpart( $this->template['page'], 'RADIO_VALUE_WITH_TEXTBOX');
        		        $replace['default_value'] = ( $itemAnsByUser && $currValueSelected)
        		                                    ? trim( $this->userSurveyResponse[$radioItemDesc['title'].'_'.$radioLabel])
        		                                    : '';
        		        $hidden[] =  $radioLabel;
    	            } else {
    	                // Normal radio item value
        		        $template['value'] = $this->getSubpart( $this->template['page'], 'RADIOVALUE');
    	            }
    	            
    	            $itemValues .= $this->substituteMarkerArray( $template['value'], $replace, '###|###', 1);
    	        }
    	        
    	        unset( $replace);
    	        
    	        // Populate all the radio values in radio item template
    	        $replace = array(
    	        'missing_value' => $radioItemDesc['missing_value'],
    	        'description' => $radioItemDesc['description'],
    	        'question' => $radioItemDesc['question'],
    	        'values' => $itemValues,
    	        'hidden' => ( count( $hidden) > 0)
    	                    ? '<input type="hidden" name="FE[fe_users][tx_mssurvey_pi1]['.$radioItemDesc['title'].'_hidden]" value="'.implode( ':', $hidden).'">'
    	                    : ''
    	        );
    	        
    	        $radioSurveyItem = $this->substituteMarkerArray( $template['item'], $replace, '###|###', 1);
    	    }
	    }
	    
	    return $radioSurveyItem;
	}

	function getCheckedSurveyItem( $checkedItemDesc) {
	    $checkedSurveyItem = '';
	    $template = array();
	    
	    if ( is_array( $checkedItemDesc) && !empty( $checkedItemDesc)) {
    		$checkedSurveyItemValues = explode("\n", $checkedItemDesc['itemvalues']);
    		$checkedSurveyItemValues = array_map('trim', $checkedSurveyItemValues);
    	    $hidden = array();
        		
    	    if ( is_array( $checkedSurveyItemValues) && !empty( $checkedSurveyItemValues)) {
    	        // Get radio survey item template
        		$template['item'] = $this->getSubpart( $this->template['page'], 'CHECKBOXITEM');
        		$itemValues = '';
        		
    	        foreach ( $checkedSurveyItemValues as $key => $itemValue) {
    	            // Check for optional textbox with checkbox
    	            list( $checkboxLabel, $conf) = explode( '|', $itemValue);
    	            $checkboxLabel = trim( $checkboxLabel);
    	            $conf = trim( $conf);
    	            
    	            // Check whether value is checked or not
    	            $isDefaultValue = ( $checkboxLabel{strlen( $checkboxLabel)-1} == '@');
    	            $itemAnsByUser = array_key_exists( $checkedItemDesc['title'], $this->userSurveyResponse);
    	            
    	            $checkboxLabel = trim( trim( $checkboxLabel, '@'));
    	            $currValueSelected = ( $itemAnsByUser)
										 ? in_array( $checkboxLabel, $this->userSurveyResponse[$checkedItemDesc['title']])
										 : false;
    	            
    		        $replace = array(
    		        'checked' => ( $isDefaultValue || ( $itemAnsByUser && $currValueSelected))
								 ? 'checked'
								 : '',
    		        'title' => $checkedItemDesc['title'],
    		        'value' => $checkboxLabel
    		        );
    		        
    	            // Select radio item value template based on configuration (either radio, or radio with textbox)
    	            if ( strtoupper( $conf) === 'TEXT') {
    	                // Radio button with textbox
        		        $template['value'] = $this->getSubpart( $this->template['page'], 'CHECKBOX_VALUE_WITH_TEXTBOX');
        		        $replace['default_value'] = ( $itemAnsByUser && $currValueSelected)
        		                                    ? trim( $this->userSurveyResponse[$checkedItemDesc['title'].'_'.$checkboxLabel.'_value'])
        		                                    : '';
        		        $hidden[] =  $checkboxLabel;
    	            } else {
    	                // Normal radio item value
        		        $template['value'] = $this->getSubpart( $this->template['page'], 'CHECKBOXVALUE');
    	            }
    	            
    	            $itemValues .= $this->substituteMarkerArray( $template['value'], $replace, '###|###', 1);
    	        }
    	        
    	        unset( $replace);
    	        
    	        // Populate all the radio values in radio item template
    	        $replace = array(
    	        'missing_value' => $checkedItemDesc['missing_value'],
    	        'description' => $checkedItemDesc['description'],
    	        'question' => $checkedItemDesc['question'],
    	        'values' => $itemValues,
    	        'hidden' => ( count( $hidden) > 0)
    	                    ? '<input type="hidden" name="FE[fe_users][tx_mssurvey_pi1]['.$checkedItemDesc['title'].'_hidden]" value="'.implode( ':', $hidden).'">'
    	                    : ''
    	        );
    	        
    	        $checkedSurveyItem = $this->substituteMarkerArray( $template['item'], $replace, '###|###', 1);
    	    }
	    }
	    
	    return $checkedSurveyItem;
	}

	/**
	 * Function to get the user groups associated with the current domain and survey
	 *
	 * @access public
	 * @param array $surveyGroups	Array containing survey user groups
	 * @return array
	 */
	function setSurveyDomainGroups( $surveyGroups) {
		if ( (is_array( $surveyGroups)) && (count( $surveyGroups) > 0)) {
			foreach ( $surveyGroups as $key => $group) {
				if ( is_integer( $group)) {
					$this->surveyDomainGroups[] = intval( $group);
				}
			}
		}
	}

	/**
	 * Function to create a comma separated record for survey results
	 *
	 * @access private
	 * @param array $surveyResult	Array containing survey results
	 * @return string
	 */
	function createCSV( $surveyResult) {
		$csv = '';

		if ( count( $surveyResult) > 0) {
			foreach ( $surveyResult as $item => $value) {
			    if ( !preg_match( '/(.)*_hidden$/i', $item)) {
    				$csv .= ( strcmp( trim( $value), ''))
    						? '"'.trim( $item).'":"'.trim( $value).'",'
    						: '';
			    }
			}

			$csv = trim( $csv, ',');
		}

		return $csv;
	}

	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $array: ...
	 * @return   [type]      ...
	 */
	function storevars($array){
		foreach ($array as $variable => $value){
			$this->userdata['storedvars'][trim($variable)] = $value;
		}
		return 0;
	}

	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $array: ...
	 * @return   [type]      ...
	 */
	function cleanvars( $array) {
		if ( ! is_array( $array ) )
		{
			return $array;
		}

		foreach ( $array as $varname => $var) {
			if ( is_array( $var)) {
				foreach ( $var as $name) {
					$array[$varname.'_'.$name] = 'X';
				}
				
				unset( $array[$varname]);
			}
		}
		
		return $array;
	}

	/**
	 * Function to check the existence of user survey
	 *
	 * @access public
	 * @param integer $userID	User-ID against which survey existence has to check
	 * @return boolean
	 */
	function userSurveyExists( $userID) {
		$status = false;
		
		$select = '*';
		$table  = $this->tbSurveyResults;
		$where  = ' fe_cruser_id = '.$userID;
        $where .= ' AND domain_group_id = '.$this->currDomain; 
		$where .= ' AND pid = '.$this->resultsPID;
		$where .= ' AND deleted = 0 AND hidden = 0';
		
		// Uncomment to debug
		// cbDebug( __FILE__, __LINE__ );	
        $this->debug_print( $GLOBALS['TYPO3_DB']->SELECTquery( $select, $table, $where));

        $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table, $where);
		
		if ( $rs) {
			if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
				$status = true;
			}
		}
		
		return $status;
	}
	
	/**
	* Function to get the survey configuration array.
	*
	* This function builts and return the survey configuration array
	* from the typoscript set in TSconfig of root page.
	*
	* @access public
	* @return array
	*/
	function getSurveyTSconf() {
		$surveyTSconf = array();
		$pageID = $GLOBALS['TSFE']->id;
		
		// Fetch page TSconfig from rootline
		$rootLine = t3lib_BEfunc::BEgetRootLine( $pageID);
		$rootPageTS = t3lib_BEfunc::getPagesTSconfig( $pageID, $rootLine);
		$surveyTSconf = $rootPageTS['survey.'];
		
		return $surveyTSconf;
	}
	
	/**
	* Function to fetch survey domain groups from configuration set via Page TS in root page
	*
	* @access public
	* @param array $surveyTSconf	Array containing survey configuration details
	* @param string $type			Flag to determine whether same level groups has to return, or next level (in case of upgrade)
	* @return array
	*/
	function getSurveyDomainGroups( $surveyTSconf, $type = 'JOIN') {
		$surveyDomainGroups = array();
		
		if ( is_array( $surveyTSconf) && ( count( $surveyTSconf) > 0)) {
			// Obtain curent id & group of current user
			$userID = ( $GLOBALS['TSFE']->loginUser)
					  ? intval( $GLOBALS['TSFE']->fe_user->user['uid'])
					  : 0;
					  
			$groupLevel = 0;
			$surveyUserGroupsLevels = $surveyTSconf['usergroupLevels.'];
			
			if ( $userID > 0) {
				$usergroups = array_map( 'trim', explode( ',', $GLOBALS['TSFE']->fe_user->user['usergroup']));
				
    			// Find and set the corresponding usergroup in this domain
				if ( is_array( $usergroups) && ( count( $usergroups) > 0)) {
					foreach ( $surveyUserGroupsLevels as $level => $levelGroups) {
						$levelGroups = array_map( 'trim', explode( ',', $levelGroups));
						$levelGroups = array_map( 'trim', $levelGroups, array('*'));
						$levelGroups = array_map( 'trim', $levelGroups);
						
						if ( count( array_intersect( $usergroups, $levelGroups)) > 0) {
							$groupLevel = $level;
							break;
						}
					}
				}
			}
			
			if ( $groupLevel > 0) {
			    $groupLevel = ( strtoupper( $type) === 'UPGRADE')
			                  ? $groupLevel + 1
			                  : $groupLevel;
			} else {
			    // New user, return first level survey user group
			    $groupLevel = 1;
			}
			
			// Corresponding user groups in different domains like SOA-Member/BPM-Member, SOA-Professional/BPM-Professional
			$corrSurveyUserGroups = array_map('trim', explode( ',', $surveyUserGroupsLevels[$groupLevel]));
			
			foreach ( $corrSurveyUserGroups as $key => $group) {
				if ( $group{strlen( $group)-1} == '*') {
					$surveyDomainGroups[] = intval( trim( $group, '*'));
					//break;
				}
			}
		}
		
		return $surveyDomainGroups;
	}

	/**
	 * Function to check the existence of user survey
	 *
	 * @access public
	 * @param integer $userID	User-ID against which survey results has to fetch
	 * @return boolean
	 */
	function getUserSurveyResults( $userID) {
	    $userSurveyResults = '';
	    $userID = intval( $userID);
	    
	    if ( $userID > 0) {
	        $itemNames = array_keys( $this->userSurveyResponse);
	        $itemNames = implode( '\', \'', $itemNames);
	        $itemNames = '\''.$itemNames.'\'';
	        
    	    $select = 'title, question, type';
			$from = $this->tbSurveyItems;
			$where .= '1';
			$where .= ' AND title IN ('.$itemNames.')';
			$where .= ( $this->storagePID > 0)
					  ? ' AND pid IN ('.$this->storagePID.')'
					  : '';
			$where .= ' AND deleted = 0 AND hidden = 0';
    		$orderBy = 'sorting ASC';
			
			// uncomment for debugging
			// cbDebug( __FILE__, __LINE__ );	
			$this->debug_print($GLOBALS['TYPO3_DB']->SELECTquery( $select, $from.$join, $where, '', $orderBy));

			$surveyItems = array();
			
			if ( $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $from, $where, '', $orderBy)) {
				if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
					while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
						$surveyItems[] = $row;
					}
				}
			}
			
			$userAnswers = $this->userSurveyResponse;
			$questionNo = 1;
			
			foreach ( $surveyItems as $surveyItem) {
				$question	= $surveyItem['question'];
				$title		= $surveyItem['title'];
				$type		= $surveyItem['type'];
				$answer	= '';
				
				switch ( $type) {
				    case 2:    // Radio survey item
				       $answer = ( isset( $userAnswers[$title.'_'.$userAnswers[$title]]) && $userAnswers[$title.'_'.$userAnswers[$title]] !== '')
				                 ? $userAnswers[$title].': '.$userAnswers[$title.'_'.$userAnswers[$title]]
				                 : $userAnswers[$title];
				       
				       break;
				    case 3:    // Checked survey item
						$checkedItems = $userAnswers[$title];
						
						if ( is_array( $checkedItems) && !empty( $checkedItems)) {
    						foreach ( $checkedItems as $key => $value) {
    					        $answer[] = ( isset( $userAnswers[$title.'_'.$value.'_value']) && $userAnswers[$title.'_'.$value.'_value'] !== '')
    					                    ? $value.': '.$userAnswers[$title.'_'.$value.'_value']
    					                    : $value;
    						}
    						
    						$answer	= implode( "\n", $answer );
						}
						
					    break;
				    default:
    						$answer	= $userAnswers[$title];
				}

			    $userSurveyResults .= $questionNo
					. '. '
					. $question
					. "\n"
					. $answer
					. "\n"
					. "\n"
					;

			    $questionNo++;
			}
	    }
    	    
	    return $userSurveyResults;
    }
	    
    /**	
     * Returns full url of the current page	
     *
     * @access public	
     * @return string The returned URL	
     */
    function getCurrentURL() {
        $currentURL = 'http';
        $script_name = '';
        
        if ( isset( $_SERVER['REQUEST_URI'])) {
            $script_name = $_SERVER['REQUEST_URI'];
        } else {
            $script_name = $_SERVER['PHP_SELF'];
            
            if( trim( $_SERVER['QUERY_STRING']) !== '') {
                $script_name .=  '?'.$_SERVER['QUERY_STRING'];
            }
        }
    
        if ( isset( $_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $currentURL .=  's';
        }
    
        $currentURL .=  '://';
        
        if ( $_SERVER['SERVER_PORT'] !== '80') {
            $currentURL .= $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$script_name;
        } else {
            $currentURL .= $_SERVER['HTTP_HOST'].$script_name;
        }
    
        return $currentURL;
    }
	    
	function debug_print( $var) {
		// echo '<!-- ';
		// print_r( $var);
		// echo ' -->';
		// cbDebug( 'debug_print', $var );
	}

}

?>
