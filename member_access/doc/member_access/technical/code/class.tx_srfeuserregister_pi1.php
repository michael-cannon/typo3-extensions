<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2003 Kasper Skårhøj (kasper@typo3.com)
*  (c) 2004 Stanislas Rolland (stanislas.rolland@fructifor.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
*
* Front End creating/editing/deleting records authenticated by fe_user login.
*
* A variant restricted to front end user self-registration and profile maintenance, with a number of other changes (see below).
*
* @author Kasper Sk�rh�j <kasper@typo3.com>
* @coauthor Stanislas Rolland <stanislas.rolland@fructifor.com>
*/
/**
* Changes:
* 18.3.2004 Stanislas Rolland
* recast as extension of t3lib_pibase
* standardize naming conventions for extension and extension components
* introduce the use of the Typo3 translation facility
* eliminate all language-dependent labels from html template
* introduce html markers to delete from form the non-included fields as defined in configuration
* introduce the use of sr_static_info for country and country zone
* introduce the use of sr_static_info for preferred language
* fix the table to 'fe_users' because this is the one we are extending with properties linked to sr_static_info
* generate pibase-compliant url's
* setfixed method rewritten
* standardize naming conventions of post parameters other than FE[fe_users] and fD
* eliminate frontend user administration functions: only keep functions required by the frontend user self-registration and profile maintenance
* enable select_key on plugin insert with possible values of CREATE or EDIT
* add CSS support in HTML template and default TS setup
* add CSS file for htmlmail
* delete the infomail option as it is taken care of by the New Login Box extension
* add _HTML html template subparts so that the HTMLMail property may be set on/off without changing the html template file!
* add plain text version to html message
* eliminate use of base64 and locally extend htmlmail in order to avoid spam filtering
* add a number of setup properties to control emission of emails and notifications without modifying the html template
* allow to override form name and onchange attribute of the country selector
* apply config.metaCharset to emails when under Typo3 3.6.0
* add two new fields: first_name and last_name and method setName() in case name is not included in update field list
* 15.5.2004 Stanislas Rolland
* add an optional email file attachment to the HTML email on suggestion of Volker Graubaum <volker.graubaum(at)e-netconsulting.de>
* add maintenance of field module_sys_dmail_html
* rewrite the logic in order to allow the maintenance of a list of photographs by the user
* add invitation feature
* 6.6.2004 Stanislas Rolland
* add auto-login feature
* 31.8.2004 Stanislas Rolland
* add user Internet site url validation and class.tx_srfeuserregister_pi1_urlvalidator.php
* import and modify getUpdateJS to support various charsets
* 30.9.2004 Stanislas Rolland
* multiple changes to prepare for sr_email_subcribe extension
* hooks by David Worms
* compatibility with Direct Mail linkVars
*/
/**
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_srfeuserregister_pi1.php,v 1.1.1.1 2010/04/15 10:03:49 peimic.comprock Exp $
 */
 
require_once(PATH_tslib.'class.tslib_pibase.php');
	// To get the pid language overlay
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(t3lib_extMgm::extPath('sr_static_info').'pi1/class.tx_srstaticinfo_pi1.php');
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/class.tx_srfeuserregister_pi1_t3lib_htmlmail.php');
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/class.tx_srfeuserregister_pi1_urlvalidator.php');
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/class.tx_srfeuserregister_pi1_adodb_time.php');
	// For use with images:
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');

// MLC credit card validation
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/Cb_Validate_Credit_Card.class.php');
 
//js for upgradeMemberAccess()
require_once(t3lib_extMgm::extPath('member_access').'modfunc1/class.tx_memberaccess_modfunc1.php');

class tx_srfeuserregister_pi1 extends tslib_pibase {
	var $cObj;
	// The backReference to the mother cObj object set at call time
	var $conf = array();
	var $site_url = '';
	var $theTable = 'fe_users';
	var $TCA = array();
	 
	var $feUserData = array();
	var $dataArr = array();
	var $currentArr = array();
	var $failureMsg = array();
	var $thePid = 0;
	var $thePidTitle;
	var $markerArray = array();
	var $templateCode = '';
	 
	var $registerPID;
	var $editPID;
	var $confirmPID;
	var $confirmType;
	var $loginPID;
	var $cmd;
	var $setfixedEnabled = 1;
	var $HTMLMailEnabled = 1;
	var $preview;
	var $previewLabel = '';
	var $backURL;
	var $recUid;
	var $failure = 0;
	// is set if data did not have the required fields set.
	var $error = '';
	var $saved = 0;
	// is set if data is saved
	var $nc = '';
	// "&no_cache=1" if you want that parameter sent.
	var $additionalUpdateFields = '';
	var $emailMarkPrefix = 'EMAIL_TEMPLATE_';
	var $emailMarkAdminSuffix = '_ADMIN';
	var $savedSuffix = '_SAVED';
	var $setfixedPrefix = 'SETFIXED_';
	var $emailMarkHTMLSuffix = '_HTML';
	var $charset = 'iso-8859-1'; // charset to be used in emails and form conversions
	var $codeLength;
	var $cmdKey;
	var $fieldList;
	// List of fields from fe_admin_fieldList
	var $requiredArr;
	// List of required fields
	var $adminFieldList = 'name,disable,usergroup';
	var $fileFunc = ''; // Set to a basic_filefunc object for file uploads
	// MLC cc valid
	var $cc_valid				= false;
    
    //js
    var $debug = false;
	//whether to perform member expiry processing
	//set from the typoscript variable $this->conf['memberExpiryProcessing'];
	var $memberExpiryProcessing = false;
	//whether to perform member access processing
	//set from the typoscript variable $this->conf['memberAccessProcessing'];
	var $memberAccessProcessing = false;
	//whether to perform registration error processing (log errors to a table)
	//set from the typoscript variable $this->conf['registrationErrorLogging'];
	var $registrationErrorLogging = false;
	
	/**global database pointer*/
	var $db;

	function initId() {
		$this->prefixId = 'tx_srfeuserregister_pi1';  // Same as class name
		$this->scriptRelPath = 'pi1/class.tx_srfeuserregister_pi1.php'; // Path to this script relative to the extension dir.
		$this->extKey = 'sr_feuser_register';  // The extension key.

		$this->theTable = 'fe_users';
		$this->adminFieldList = 'name,disable,usergroup';
	}
	 
	function main($content, $conf) {

			// plugin initialization
		$this->initId();
		$this->conf = $conf;
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;
		
		$this->db                = $GLOBALS['TYPO3_DB'];
		
		// Disable caching
		$this->pi_setPiVarDefaults();
		$this->site_url = t3lib_div::getIndpEnv('TYPO3_SITE_URL');

        //turn on debug information output if required (js)
        $this->setDebugStatus();
		
        if ( $this->debug && true )
        {
            echo '/Start $conf/';
			cbPrint2( '$conf', $conf );
			echo '/End $conf/';
        }

		$this->memberExpiryInit(); //js

		// get the table definition
		$GLOBALS['TSFE']->includeTCA();
		t3lib_div::loadTCA($this->theTable);
		$this->TCA = $GLOBALS['TCA'][$this->theTable];
		 
		// prepare for character set settings and conversions
		$this->typoVersion = t3lib_div::int_from_ver($GLOBALS['TYPO_VERSION']);
		if ($this->typoVersion >= 3006000 ) {
			if (trim($GLOBALS['TSFE']->config['config']['metaCharset'])) {
				$this->charset = $GLOBALS['TSFE']->csConvObj->parse_charset($GLOBALS['TSFE']->config['config']['metaCharset']);
			}
		}

		// prepare for handling dates befor 1970
		$this->adodbTime = t3lib_div::makeInstance('tx_srfeuserregister_pi1_adodb_time');
		 
		// set the pid's and the title language overlay
		$this->pidRecord = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->pidRecord->init(0);
		$this->pidRecord->sys_language_uid = (trim($GLOBALS['TSFE']->config['config']['sys_language_uid'])) ? trim($GLOBALS['TSFE']->config['config']['sys_language_uid']) :
		0;
		$this->thePid = intval($this->conf['pid']) ? intval($this->conf['pid']) :
		$GLOBALS['TSFE']->id;
		$row = $this->pidRecord->getPage($this->thePid);
		$this->thePidTitle = $row['title'];
		$this->registerPID = intval($this->conf['registerPID']) ? intval($this->conf['registerPID']) :
		$GLOBALS['TSFE']->id;
		$this->editPID = intval($this->conf['editPID']) ? intval($this->conf['editPID']) :
		$GLOBALS['TSFE']->id;
		$this->confirmPID = intval($this->conf['confirmPID']) ? intval($this->conf['confirmPID']) :
		$this->registerPID;
		$this->confirmType = intval($this->conf['confirmType']) ? intval($this->conf['confirmType']) :
		$GLOBALS['TSFE']->type;
		if ($this->conf['confirmType'] == 0 ) {
			$this->confirmType = 0;
		};
		$this->loginPID = intval($this->conf['loginPID']) ? intval($this->conf['loginPID']) :
		$GLOBALS['TSFE']->id;
		 
		// Initialise static info library
		$this->staticInfo = t3lib_div::makeInstance('tx_srstaticinfo_pi1');
		$this->staticInfo->init();
		 
		// Initialise fileFunc object
		$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		 
		// Get post parameters
		if ($this->typoVersion >= 3006000 ) {
			$this->feUserData = t3lib_div::_GP($this->prefixId);
			$fe = t3lib_div::_GP('FE');
		} else {
			$this->feUserData = t3lib_div::slashArray(t3lib_div::GPvar($this->prefixId), 'strip');
			$fe = t3lib_div::GPvar('FE');
		};
		$this->dataArr = $fe[$this->theTable];
        if ( $this->debug )
        {
            cbPrint2( '$this->dataArr', $this->dataArr );
        }

			// Establishing compatibility with Direct Mail extension
		$this->feUserData['rU'] = t3lib_div::GPvar('rU') ? t3lib_div::GPvar('rU') : $this->feUserData['rU'];
		$this->feUserData['aC'] = t3lib_div::GPvar('aC') ? t3lib_div::GPvar('aC') : $this->feUserData['aC'];
		$this->feUserData['cmd'] = t3lib_div::GPvar('cmd') ? t3lib_div::GPvar('cmd') : $this->feUserData['cmd'];

		$this->backURL = $this->feUserData['backURL'];
		$this->recUid = intval($this->feUserData['rU']);
		$this->authCode = $this->feUserData['aC'];
		 
		// Setting cmd and various switches
		if ( $this->theTable == 'fe_users' && $this->feUserData['cmd'] == 'login' ) {
			unset($this->feUserData['cmd']);
		}
		$this->cmd = $this->feUserData['cmd'] ? $this->feUserData['cmd'] :
			strtolower($this->cObj->data['select_key']);
		$this->cmd = $this->cmd ? $this->cmd :
			strtolower($this->conf['defaultCODE']) ;
		if ($this->cmd == 'edit' ) {
			$this->cmdKey = 'edit';
		} else {
			$this->cmdKey = 'create';
		}
		if (!($this->conf['setfixed'] == 1) ) {
			$this->setfixedEnabled = 0;
		}
		if (!($this->conf['email.'][HTMLMail] == 1) ) {
			$this->HTMLMailEnabled = 0;
		}
		$this->preview = $this->feUserData['preview'];
		// Setting the list of fields allowed for editing and creation.
		$this->fieldList = implode(',', t3lib_div::trimExplode(',', $GLOBALS['TCA'][$this->theTable]['feInterface']['fe_admin_fieldList'], 1));
		$this->adminFieldList = implode(',', array_intersect( explode(',', $this->fieldList), t3lib_div::trimExplode(',', $this->adminFieldList, 1) ));
		 
		// Setting requiredArr to the fields in "required" intersected field the total field list in order to remove invalid fields.
		$this->requiredArr = array_intersect(t3lib_div::trimExplode(',', $this->conf[$this->cmdKey.'.']['required'], 1),
			t3lib_div::trimExplode(',', $this->conf[$this->cmdKey.'.']['fields'], 1)
		);
		 
		// Setting the authCode length
		$this->codeLength = intval($this->conf['authcodeFields.']['codeLength']) ? intval($this->conf['authcodeFields.']['codeLength']) :
		8;
		 
		// Setting the record uid if a frontend user is logged in and we are nor trying to send an invitation
		if ($this->theTable == 'fe_users' && $GLOBALS['TSFE']->loginUser && $this->cmd != 'invite') {
			$this->recUid = $GLOBALS['TSFE']->fe_user->user['uid'];
		}
		 
		// Fetching the template file
		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		 
		// Set globally substituted markers, fonts and colors.
		$splitMark = md5(microtime());
		list($this->markerArray['###GW1B###'], $this->markerArray['###GW1E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap1.']));
		list($this->markerArray['###GW2B###'], $this->markerArray['###GW2E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap2.']));
		list($this->markerArray['###GW3B###'], $this->markerArray['###GW3E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap3.']));
		$this->markerArray['###GC1###'] = $this->cObj->stdWrap($this->conf['color1'], $this->conf['color1.']);
		$this->markerArray['###GC2###'] = $this->cObj->stdWrap($this->conf['color2'], $this->conf['color2.']);
		$this->markerArray['###GC3###'] = $this->cObj->stdWrap($this->conf['color3'], $this->conf['color3.']);
		$this->markerArray['###CHARSET###'] = $this->charset;
		$this->markerArray['###PREFIXID###'] = $this->prefixId;
		 
		// Setting URL, HIDDENFIELDS and signature markers
		$this->markerArray = $this->addURLMarkers($this->markerArray);
		 
		// Setting CSS style markers if required
		if ($this->HTMLMailEnabled) {
			$this->markerArray = $this->addCSSStyleMarkers($this->markerArray);
		}
		 
		// *****************
		// If data is submitted, we take care of it here.
		// *******************
		if ($this->cmd == 'delete' && !$this->feUserData['preview'] && !$this->feUserData['doNotSave'] ) {
			// Delete record if delete command is sent + the preview flag is NOT set.

			$this->deleteRecord();
		}

		// Evaluate incoming data
		if (is_array($this->dataArr)) {
			$this->setName();
			$this->parseValues();
			$this->overrideValues();
			if ($this->feUserData['submit'] || $this->feUserData['doNotSave'] )
			{
				// MLC require credit card details
				if ( 'credit_card' == $this->dataArr[ 'payment_method' ] )
				{
					$this->requiredArr[]	= 'cc_type';
					$this->requiredArr[]	= 'cc_number';
					$this->requiredArr[]	= 'cc_expiry';
					$this->requiredArr[]	= 'cc_name';
				}

				if ($this->conf['evalFunc'] ) {
					$this->dataArr = $this->userProcess('evalFunc', $this->dataArr);
				}

				// MLC set referrer
				if ( '' == $this->dataArr[ 'referrer_uri' ] )
				{
					// MLC check that site_url is part of refrerre
					// else go home
					$siteClean	= preg_quote( $this->site_url );

					if ( preg_match( '#' . $siteClean . '#i'
						, $_SERVER[ 'HTTP_REFERER' ]
						)
					)
					{
						$this->dataArr[ 'referrer_uri' ] =
							$_SERVER[ 'HTTP_REFERER' ];
					}

					else
					{
						$this->dataArr[ 'referrer_uri' ] =
							$this->site_url;
					}
				}

				// MLC set endtime about x days later per timelimit
				if ( '' != $this->conf[ 'timelimit' ] )
				{
					$now		= time();
					$later		= $now
									+ ( $this->conf[ 'timelimit' ]
										* 24 * 60 * 60 
									);

					$this->dataArr[ 'endtime' ]	= $later;
				}

				// MLC create psuedo username and pasword
				// form has been submitted
				if ( 'tempuser' == $this->cmd )
				{
					$this->createTempuser();
				}

				// MLC set start/end time based upon create/edit and group
				if ( 'create' == $this->cmd || 'edit' == $this->cmd )
				{
					$this->setStartEndTime();
				}

				// MLC modifiy the usergroup with an override
				if ( '' != $this->conf[ 'userGroupUponRegistration' ] )
				{
					// lookup current, create array
//					$usergroup	= $GLOBALS['TSFE']->fe_user->user['usergroup'];
//					$usergroup	= explode( ',', $usergroup );
//
//					$ugNew		= explode( ','
//									, $this->conf[ 'userGroupUponRegistration' ]
//								);
//
//					$ugNew		= array_merge( $usergroup, $ugNew );
//					$ugNew		= array_unique( $ugNew );
//					$ugNew		= implode( ',', $ugNew );

					$this->dataArr[ 'usergroup' ]	=
									$this->conf[ 'userGroupUponRegistration' ];
				}

				// MLC append usergroup
				if ( '' != $this->dataArr[ 'usergroupaddon' ]
					&& ! preg_match(
						'#' . $this->dataArr[ 'usergroupaddon' ] . '#'
						, $this->dataArr[ 'usergroup' ]
					)
				)
				{
					$prepend		= '';

					if ( '' != $this->dataArr[ 'usergroup' ] )
					{
						$prepend	= ',';
					}

					$this->dataArr[ 'usergroup' ]	.= $prepend
								. $this->dataArr[ 'usergroupaddon' ];
				}

				// a button was clicked on
				$this->evalValues();

				if ( $this->debug )
				{
					cbPrint2( '$this->dataArr', $this->dataArr );
				}
			} else {
				//this is either a country change submitted through the onchange event! or a file deletion already processed by the parsing function
				// we are going to redisplay
				$this->evalValues();
				$this->failure = 1;
			}
			if (!$this->failure && !$this->feUserData['preview'] && !$this->feUserData['doNotSave'] ) {
				$this->save();
                
				if ( $this->debug )
				{
					cbPrint2( '$this->dataArr', $this->dataArr );
				}
			}
		} else {
			$this->defaultValues(); // If no incoming data, this will set the default values.
			$this->feUserData['preview'] = 0; // No preview if data is not received
		}
		if ($this->failure ) {
			$this->feUserData['preview'] = 0;
			
			if ($this->debug)  "Failure:";
			//log the error - js
			$this->logRegistrationErrors();
			
		} // No preview flag if a evaluation failure has occured
		$this->previewLabel = ($this->feUserData['preview']) ? '_PREVIEW' :
		''; // Setting preview template label suffix.
		 
		// Display forms
		if ($this->saved) {
			// Displaying the page here that says, the record has been saved. You're able to include the saved values by markers.
			switch($this->cmd) {
				case 'delete':
				$key = 'DELETE'.$this->savedSuffix;
				break;
				case 'edit':
				$key = 'EDIT'.$this->savedSuffix;
				break;
				case 'invite':
				$key = $this->setfixedPrefix.'INVITE';
				break;
				default:
				if ($this->setfixedEnabled ) {
					$key = $this->setfixedPrefix.'CREATE';
				} else {
					$key = 'CREATE'.$this->savedSuffix;
				}
				break;
			}

			// Display confirmation message
			$templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_'.$key.'###');
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $this->currentArr);
			$markerArray = $this->addStaticInfoMarkers($markerArray, $this->currentArr);
			$markerArray = $this->addLabelMarkers($markerArray, $this->currentArr);
			$content = $this->cObj->substituteMarkerArray($templateCode, $markerArray);
			 
			// MLC send mail prior to redirect or displaying form
			// Send email message(s)
			$this->compileMail($key, array($this->currentArr), $this->currentArr[$this->conf['email.']['field']], $this->conf['setfixed.']);
			 
			// MLC redirect saved tempuser
			if ( $key == 'CREATE_SAVED'
				&& $this->cmd == 'tempuser'
				&& $this->conf[ 'enableAutoLoginTempuser' ]
			) 
			{
				$loginVars = array();
				$loginVars['pid'] = $this->thePid;
				$loginVars['logintype'] = 'login';
				$loginVars['redirect_url'] = htmlspecialchars( trim(
					$this->dataArr[ 'referrer_uri' ] )
				);
				$loginVars['user'] = $this->dataArr['username'];
				$loginVars['pass'] = $this->dataArr['password'];

				// MLC review loginVars
				if ( $this->debug )
				{
					cbPrint2( 'loginVars', $loginVars );

					cbPrint2( 'location', 'Location: '.t3lib_div::locationHeaderUrl($this->site_url.$this->cObj->getTypoLink_URL($this->loginPID.','.$GLOBALS['TSFE']->type, $loginVars)));
					exit;
				}

				header('Location: '.t3lib_div::locationHeaderUrl($this->site_url.$this->cObj->getTypoLink_URL($this->loginPID.','.$GLOBALS['TSFE']->type, $loginVars)));
				exit;
			}
		} elseif ($this->error) {
			// If there was an error, we return the template-subpart with the error message
			$templateCode = $this->cObj->getSubpart($this->templateCode, $this->error);
			$this->setCObjects($templateCode);
			$content = $this->cObj->substituteMarkerArray($templateCode, $this->markerArray);
		} else {
			// Finally, if there has been no attempt to save. That is either preview or just displaying and empty or not correctly filled form:
			switch($this->cmd) {
				case 'setfixed':
				$content = $this->procesSetFixed();
				break;
				case 'infomail':
				$content = $this->sendInfoMail();
				break;
				case 'delete':
				$content = $this->displayDeleteScreen();
				break;
				case 'edit':
				$content = $this->displayEditScreen();
				break;
				case 'invite':
				$content = $this->displayCreateScreen($this->cmd);
				break;
				// MLC allow for tempuser creation
				case 'tempuser':
				case 'create':
				$content = $this->displayCreateScreen($this->cmd);
				break;
				default:
				if ($this->theTable == 'fe_users' && $GLOBALS['TSFE']->loginUser) {
					$content = $this->displayCreateScreen($this->cmd);
				} else {
					$content = $this->displayEditScreen();
				}
				break;
			}
		}

		// MLC delete the cc info from database as needed
		if ( $this->dataArr[ 'cc_number' ] 
			&& ! $this->conf[ 'enableCreditcardSave' ]
		)
		{
			$this->removeCreditcard( $this->dataArr );
		}
		
		return $this->pi_wrapInBaseClass($content);
	}
	 
	
    /**
	* Initialize values relating to member expiry.
	*/
	function memberExpiryInit() {
		
		if ($this->debug) {
			echo 'memberExpiryInit()';
			echo $this->conf;

			echo "<br>\n this->memberExpiryProcessing = " . $this->memberExpiryProcessing ."<br>\n";
			echo " this->memberAccessProcessing = " . $this->memberAccessProcessing ." <br>\n";
			echo "this->registrationErrorLogging = ". $this->registrationErrorLogging ."<br>\n";
			if ($this->registrationErrorLogging  == false ) {
				echo "this->registrationErrorLogging  turned off";
			}
			var_dump($this->registrationErrorLogging);
			
		}
		
		if(isset($this->conf['memberExpiryProcessing'])) {
			$this->memberExpiryProcessing = $this->conf['memberExpiryProcessing'];
		}
		if(isset($this->conf['memberAccessProcessing'])) {
			$this->memberAccessProcessing =  $this->conf['memberAccessProcessing'];
		}
		if(isset($this->conf['registrationErrorLogging'])) {
			$this->registrationErrorLogging = $this->conf['registrationErrorLogging'];
		}

		if ($this->debug) {
			echo "<br>\n this->memberExpiryProcessing = " . $this->memberExpiryProcessing ."<br>\n";
			echo " this->memberAccessProcessing = " . $this->memberAccessProcessing ." <br>\n";
			echo "this->registrationErrorLogging = ". $this->registrationErrorLogging ."<br>\n";
			if ($this->registrationErrorLogging  == false ) {
				echo "this->registrationErrorLogging  turned off";
			}
			if (1==$this->registrationErrorLogging){
			   echo  "logging RegistrationErrors on";
			}
		}
		
	}
	
	/**
    * Turn on debug status conditionally.
    * Put your IP here to see debug information.
    * @return void
    * @author js
    */
    function setDebugStatus(){
        //change the true below to false to always not show debug info
        if ( true && '59.94.209.67' == $_SERVER[ 'REMOTE_ADDR' ] ) {
            $this->debug = true;
            //echo 'Remote:59.94.208.61';
            //echo 'Join Now.';
            //echo $_SERVER[ 'REMOTE_ADDR' ];
        }
    }
    
    /**
	* Applies validation rules specified in TS setup
	*
	* @return void  on return, $this->failure is the list of fields which were not ok
	*/
	function evalValues() {
		// Check required, set failure if not ok.
		reset($this->requiredArr);
		$tempArr = array();
		while (list(, $theField) = each($this->requiredArr)) {
			if (!trim($this->dataArr[$theField])) {

				$tempArr[] = $theField;
			}
		}

		// Evaluate: This evaluates for more advanced things than "required" does. But it returns the same error code, so you must let the required-message tell, if further evaluation has failed!
		$recExist = 0;
		if (is_array($this->conf[$this->cmdKey.'.']['evalValues.'])) {
			switch($this->cmd) {
				case 'edit':
				if (isset($this->dataArr['pid'])) {
					 
					// This may be tricked if the input has the pid-field set but the edit-field list does NOT allow the pid to be edited. Then the pid may be false.
					$recordTestPid = intval($this->dataArr['pid']);
				} else {
					$tempRecArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->dataArr[uid]);
					$recordTestPid = intval($tempRecArr['pid']);
				}
				$recExist = 1;
				break;
				default:
				$recordTestPid = $this->thePid ? $this->thePid :
				t3lib_div::intval_positive($this->dataArr['pid']);
				break;
			}
			 
			reset($this->conf[$this->cmdKey.'.']['evalValues.']);
			while (list($theField, $theValue) = each($this->conf[$this->cmdKey.'.']['evalValues.'])) {
				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);
				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.

					$theCmd = trim($cmdParts[0]);

					switch($theCmd) {
						case 'uniqueGlobal':
						if (trim($this->dataArr[$theField]) && $DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable, $theField, $this->dataArr[$theField], 'LIMIT 1')) {
							if (!$recExist || $DBrows[0]['uid'] != $this->dataArr['uid']) {
								// Only issue an error if the record is not existing (if new...) and if the record with the false value selected was not our self.
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'The value exists already. Enter a new value.');
							}
						}
						break;
						case 'uniqueLocal':
						if (trim($this->dataArr[$theField]) && $DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable, $theField, $this->dataArr[$theField], "AND pid IN (".$recordTestPid.') LIMIT 1')) {
							if (!$recExist || $DBrows[0]['uid'] != $this->dataArr['uid']) {
								// Only issue an error if the record is not existing (if new...) and if the record with the false value selected was not our self.
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'The value exists already. Enter a new value.');
							}
						}
						break;
						case 'twice':
						if (strcmp($this->dataArr[$theField], $this->dataArr[$theField.'_again'])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter the same value twice.');
						}
						break;
						case 'email':
						if (trim($this->dataArr[$theField]) && !$this->cObj->checkEmail($this->dataArr[$theField])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter a valid email address.');
						}
						break;
						case 'required':
						if (!trim($this->dataArr[$theField])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter a value!');
						}
						break;
						case 'atLeast':
						$chars = intval($cmdParts[1]);
						if (strlen($this->dataArr[$theField]) < $chars) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'You must enter at least %s characters!'), $chars);
						}
						break;
						case 'atMost':
						$chars = intval($cmdParts[1]);
						if (strlen($this->dataArr[$theField]) > $chars) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'You must enter at most %s characters!'), $chars);
						}
						break;
						case 'inBranch':
						$pars = explode(';', $cmdParts[1]);
						if (intval($pars[0])) {
							$pid_list = $this->cObj->getTreeList(
							intval($pars[0]),
								intval($pars[1]) ? intval($pars[1]) :
							999,
								 
							intval($pars[2])
							);
							if (!$pid_list || !t3lib_div::inList($pid_list, $this->dataArr[$theField])) {
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'The value was not a valid value from this list: %s'), $pid_list);
							}
						}
						break;
						case 'unsetEmpty':
						if (!$this->dataArr[$theField]) {
							$hash = array_flip($tempArr);
							unset($hash[$theField]);
							$tempArr = array_keys($hash);
							unset($this->failureMsg[$theField]);
							unset($this->dataArr[$theField]); // This should prevent the field from entering the database.
						}
						break;
						case 'upload':
						if ($this->dataArr[$theField] && is_array($this->TCA['columns'][$theField]['config']) ) {
							if ($this->TCA['columns'][$theField]['config']['type'] == 'group' && $this->TCA['columns'][$theField]['config']['internal_type'] == 'file') {
								$uploadPath = $this->TCA['columns'][$theField]['config']['uploadfolder'];
								$allowedExtArray = t3lib_div::trimExplode(',', $this->TCA['columns'][$theField]['config']['allowed'], 1);
								$maxSize = $this->TCA['columns'][$theField]['config']['max_size'];
								$fileNameList = explode(',', $this->dataArr[$theField]);
								$newFileNameList = array();
								reset($fileNameList);
								while (list(, $filename) = each($fileNameList)) {
									$fI = pathinfo($filename);
									if (!count($allowedExtArray) || in_array(strtolower($fI['extension']), $allowedExtArray)) {
										if (@is_file(PATH_site.$uploadPath.'/'.$filename)) {
											if (!$maxSize || (filesize(PATH_site.$uploadPath.'/'.$filename) < ($maxSize * 1024))) {
												$newFileNameList[] = $filename;
											} else {
												$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, 'max_size', 'The file is larger than %s KB.'), $maxSize);
												unlink(PATH_site.$uploadPath.'/'.$filename);
											}
										}
									} else {
										$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, 'allowed', 'The file extension %s is not allowed.'), $fI['extension']);
										unlink(PATH_site.$uploadPath.'/'.$filename);
									}
								}
								$this->dataArr[$theField] = implode(',', $newFileNameList);
							}
						}
						break;
						case 'wwwURL':
						if ($this->dataArr[$theField]) {
							$wwwURLOptions = array (
							'AssumeProtocol' => 'http' ,
								'AllowBracks' => TRUE ,
								'AllowedProtocols' => array(0 => 'http', 1 => 'https', ) ,
								'Require' => array('Protocol' => FALSE , 'User' => FALSE , 'Password' => FALSE , 'Server' => TRUE , 'Resource' => FALSE , 'TLD' => TRUE , 'Port' => FALSE , 'QueryString' => FALSE , 'Anchor' => FALSE , ) ,
								'Forbid' => array('Protocol' => FALSE , 'User' => TRUE , 'Password' => TRUE , 'Server' => FALSE , 'Resource' => FALSE , 'TLD' => FALSE , 'Port' => TRUE , 'QueryString' => FALSE , 'Anchor' => FALSE , ) ,
								);
							$wwwURLResult = tx_srfeuserregister_pi1_urlvalidator::_ValURL($this->dataArr[$theField], $wwwURLOptions);
							if ($wwwURLResult['Result'] != 'EW_OK' ) {
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a valid Internet site address.');
							}
						}
						break;
						case 'date':
						if ($this->dataArr[$theField] && !$this->evalDate($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a valid date.');
						}
						break;
						case 'expiry':
						if ($this->dataArr[$theField] && !$this->evalExpiry($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a valid expiry MM/YYYY.');
						}
						break;
						case 'month':
						if ($this->dataArr[$theField] && !$this->evalMonth($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a valid month.');
						}
						break;
						case 'year':
						if ($this->dataArr[$theField] && !$this->evalYear($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a valid year.');
						}
						break;
						case 'int':
						if ($this->dataArr[$theField] && !is_numeric($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a whole number.');
						}
						break;

						case 'creditcard':
						if ($this->dataArr[$theField])
						{
							if (!$this->evalCreditcard($this->dataArr[$theField]) )
							{
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, $this->ccErrorText );
							}
							// MLC since credit card is valid, update usergroup
							// for advanced access
							else
							{
								$this->cc_valid		= true;
							}
						}
						break;

						case 'routing':
						if ($this->dataArr[$theField] && !$this->cbIsAba($this->dataArr[$theField]) ){
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please re-enter the routing number.' );
						}
						break;

						case 'pco':
						// MLC if zone is in the create list and pco, error
						if ( preg_match(
								'#\b' . $this->dataArr[$theField] . '\b#'
								, $this->conf[ 'create.' ][ 'fields' ] ) 
							&& $this->dataArr[$theField] == 'PCO'
						)
						{
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please choose one.' );
						}
						break;
					}
				}
				$this->markerArray['###EVAL_ERROR_FIELD_'.$theField.'###'] = is_array($this->failureMsg[$theField])?implode($this->failureMsg[$theField], '<br />'):
				'<!--no error-->';
			}
		}
		$this->failure = implode($tempArr, ',');

		if ( $this->debug )
		{
			cbPrint2( '$this->failure', $this->failure );
			cbPrint2( '$this->failureMsg', $this->failureMsg );
		}
	}
	 
	/**
	* Gets the error message to be displayed
	*
	 
	* @param string  $theField: the name of the field being validated
	* @param string  $theCmd: the name of the validation rule being evaluated
	* @param string  $label: a default error message provided by the invoking function

	* @return string  the error message to be displayed
	*/
	function getFailure($theField, $theCmd, $label) {
		$failureLabel = $this->pi_getLL('evalErrors_'.$theCmd.'_'.$theField);
		$failureLabel = $failureLabel ? $failureLabel :
		$this->pi_getLL('evalErrors_'.$theCmd);
		$failureLabel = $failureLabel ? $failureLabel :
		(isset($this->conf['evalErrors.'][$theField.'.'][$theCmd]) ? $this->conf['evalErrors.'][$theField.'.'][$theCmd] : $label);
		return $failureLabel;
	}
	 
	/**
	* Invokes a user process
	*
	* @param array  $mConfKey: the configuration array of the user process
	* @param array  $passVar: the array of variables to be passed to the user process
	* @return array  the updated array of passed variables
	*/
	function userProcess($mConfKey, $passVar) {
		if ($this->conf[$mConfKey]) {
			$funcConf = $this->conf[$mConfKey.'.'];
			$funcConf['parentObj'] = &$this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	}
	 
	/**
	* Invokes a user process
	*
	* @param string  $confVal: the name of the process to be invoked
	* @param array  $mConfKey: the configuration array of the user process
	* @param array  $passVar: the array of variables to be passed to the user process
	* @return array  the updated array of passed variables
	*/
	function userProcess_alt($confVal, $confArr, $passVar) {
		if ($confVal) {
			$funcConf = $confArr;
			$funcConf['parentObj'] = &$this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($confVal, $funcConf, $passVar);
		}
		return $passVar;
	}
	 
	/**
	* Transforms fields into certain things...
	*
	* @return void  all parsing done directly on input array $this->dataArr
	*/
	function parseValues() {
		if (is_array($this->conf['parseValues.'])) {
			reset($this->conf['parseValues.']);
			while (list($theField, $theValue) = each($this->conf['parseValues.'])) {
				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);
				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd) {
						case 'int':
						$this->dataArr[$theField] = intval($this->dataArr[$theField]);
						break;
						case 'lower':
						case 'upper':
						$this->dataArr[$theField] = $this->cObj->caseshift($this->dataArr[$theField], $theCmd);
						break;
						case 'nospace':
						$this->dataArr[$theField] = str_replace(' ', '', $this->dataArr[$theField]);
						break;
						case 'alpha':
						$this->dataArr[$theField] = ereg_replace('[^a-zA-Z]', '', $this->dataArr[$theField]);
						break;
						case 'num':
						$this->dataArr[$theField] = ereg_replace('[^0-9]', '', $this->dataArr[$theField]);
						break;
						case 'alphanum':
						$this->dataArr[$theField] = ereg_replace('[^a-zA-Z0-9]', '', $this->dataArr[$theField]);
						break;
						case 'alphanum_x':
						$this->dataArr[$theField] = ereg_replace('[^a-zA-Z0-9_-]', '', $this->dataArr[$theField]);
						break;
						case 'trim':
						$this->dataArr[$theField] = trim($this->dataArr[$theField]);
						break;
						case 'random':
						$this->dataArr[$theField] = substr(md5(uniqid(microtime(), 1)), 0, intval($cmdParts[1]));
						break;
						case 'files':
						if (is_string($this->dataArr[$theField]) && $this->dataArr[$theField]) {
							$this->dataArr[$theField] = explode(',', $this->dataArr[$theField]);
						}
						$this->processFiles($theField);
						break;
						case 'setEmptyIfAbsent':
						if (!isset($this->dataArr[$theField])) {
							$this->dataArr[$theField] = '';
						}
						break;
						case 'multiple':
						if (is_array($this->dataArr[$theField])) {
							$this->dataArr[$theField] = implode(',', $this->dataArr[$theField]);
						}
						break;
						case 'checkArray':
						if (is_array($this->dataArr[$theField])) {
							reset($this->dataArr[$theField]);
							$val = 0;
							while (list($kk, $vv) = each($this->dataArr[$theField])) {
								$kk = t3lib_div::intInRange($kk, 0);
								if ($kk <= 30) {
									if ($vv) {
										$val|= pow(2, $kk);
									}
								}
							}
							$this->dataArr[$theField] = $val;
						}
						break;
						case 'uniqueHashInt':
						$otherFields = t3lib_div::trimExplode(';', $cmdParts[1], 1);
						$hashArray = array();
						while (list(, $fN) = each($otherFields)) {
							$vv = $this->dataArr[$fN];
							$vv = ereg_replace('[[:space:]]', '', $vv);
							$vv = ereg_replace('[^[:alnum:]]', '', $vv);
							$vv = strtolower($vv);
							$hashArray[] = $vv;
						}
						$this->dataArr[$theField] = hexdec(substr(md5(serialize($hashArray)), 0, 8));
						break;
						case 'wwwURL':
						if ($this->dataArr[$theField]) {
							$wwwURLOptions = array (
							'AssumeProtocol' => 'http' ,
								'AllowBracks' => TRUE ,
								'AllowedProtocols' => array(0 => 'http', 1 => 'https', ) ,
								'Require' => array('Protocol' => FALSE , 'User' => FALSE , 'Password' => FALSE , 'Server' => TRUE , 'Resource' => FALSE , 'TLD' => TRUE , 'Port' => FALSE , 'QueryString' => FALSE , 'Anchor' => FALSE , ) ,
								'Forbid' => array('Protocol' => FALSE , 'User' => TRUE , 'Password' => TRUE , 'Server' => FALSE , 'Resource' => FALSE , 'TLD' => FALSE , 'Port' => TRUE , 'QueryString' => FALSE , 'Anchor' => FALSE , ) ,
								);
							$wwwURLResult = tx_srfeuserregister_pi1_urlvalidator::_ValURL($this->dataArr[$theField], $wwwURLOptions);
							if ($wwwURLResult['Result'] = 'EW_OK' ) {
								$this->dataArr[$theField] = $wwwURLResult['Value'];
							}
						}
						break;
						case 'date':
						if($this->dataArr[$theField] && $this->evalDate($this->dataArr[$theField]) && strlen($this->dataArr[$theField]) == 8) { 
								$this->dataArr[$theField] = substr($this->dataArr[$theField],0,4).'-'.substr($this->dataArr[$theField],4,2).'-'.substr($this->dataArr[$theField],6,2);
						}
						break;
					}
				}
			}
		}
	}
	 
	/**
	* Processes uploaded files
	*
	* @param string  $theField: the name of the field
	* @return void
	*/
	function processFiles($theField) {
		if (is_array($this->TCA['columns'][$theField])) {
			 
			$uploadPath = $this->TCA['columns'][$theField]['config']['uploadfolder'];
		}




		$fileNameList = array();
		if (is_array($this->dataArr[$theField]) && count($this->dataArr[$theField])) {
			while (list($i, $file) = each($this->dataArr[$theField])) {
				if (is_array($file)) {
					if ($uploadPath && $file['submit_delete']) {
						unlink(PATH_site.$uploadPath.'/'.$file['name']);
					} else {
						$fileNameList[] = $file['name'];
					}
				} else {
					$fileNameList[] = $file;
				}
			}
		}
		if ($uploadPath && is_array($_FILES['FE']['name'][$this->theTable][$theField]) && $this->evalFileError($_FILES['FE']['error'])) {
			reset($_FILES['FE']['name'][$this->theTable][$theField]);
			while (list($i, $filename) = each($_FILES['FE']['name'][$this->theTable][$theField])) {
				if ($filename) {
					$fI = pathinfo($filename);
					if (t3lib_div::verifyFilenameAgainstDenyPattern($fI['name'])) {
						$tmpFilename = (($GLOBALS['TSFE']->loginUser)?($GLOBALS['TSFE']->fe_user->user['username'].'_'):'').basename($filename, '.'.$fI['extension']).'_'.t3lib_div::shortmd5(uniqid($filename)).'.'.$fI['extension'];
						$theDestFile = $this->fileFunc->getUniqueName($this->fileFunc->cleanFileName($tmpFilename), PATH_site.$uploadPath.'/');
						t3lib_div::upload_copy_move($_FILES['FE']['tmp_name'][$this->theTable][$theField][$i], $theDestFile);
						$fI2 = pathinfo($theDestFile);
						$fileNameList[] = $fI2['basename'];
					}
				}
			}
		}
		$this->dataArr[$theField] = (count($fileNameList))?implode(',', $fileNameList):
		'';
	}
	 
	/**
	* Overrides field values as specified by TS setup
	*
	* @return void  all overriding done directly on array $this->dataArr
	*/
	function overrideValues() {
		// Addition of overriding values
		if (is_array($this->conf[$this->cmdKey.'.']['overrideValues.'])) {
			reset($this->conf[$this->cmdKey.'.']['overrideValues.']);
			while (list($theField, $theValue) = each($this->conf[$this->cmdKey.'.']['overrideValues.'])) {
				$this->dataArr[$theField] = $theValue;
			}
		}
	}
	 
	/**
	* Sets default field values as specified by TS setup
	*
	* @return void  all initialization done directly on array $this->dataArr
	*/
	function defaultValues() {
		// Addition of default values
		if (is_array($this->conf[$this->cmdKey.'.']['defaultValues.'])) {
			reset($this->conf[$this->cmdKey.'.']['defaultValues.']);
			while (list($theField, $theValue) = each($this->conf[$this->cmdKey.'.']['defaultValues.'])) {
				$this->dataArr[$theField] = $theValue;
			}
		}
		if (is_array($this->conf[$this->cmdKey.'.']['evalValues.'])) {
			reset($this->conf[$this->cmdKey.'.']['evalValues.']);
			while (list($theField, $theValue) = each($this->conf[$this->cmdKey.'.']['evalValues.'])) {
				$this->markerArray['###EVAL_ERROR_FIELD_'.$theField.'###'] = '<!--no error-->';
			}
		}
	}
	 
	/**
	* Moves first and last name into name
	*
	* @return void  done directly on array $this->dataArr
	*/
	function setName() {
		if (in_array('name', explode(',', $this->fieldList)) && !in_array('name', t3lib_div::trimExplode(',', $this->conf[$this->cmdKey.'.']['fields'], 1)) ) {
			$this->dataArr['name'] = trim(trim($this->dataArr['first_name']).' '.trim($this->dataArr['last_name']));
		}
	}
	 
	/**
	* Saves the data into the database
	*
	* @return void  sets $this->saved
	*/
	function save() {
		if($this->debug) echo "save()<br>\n";
		
		$dropFields				= explode( ','
									, $this->conf[ 'dropFields' ]
								);

		switch($this->cmd) {
			case 'edit':
			if ($this->debug) echo "case edit <br>\n";
			$theUid = $this->dataArr['uid'];
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $theUid);
			// Fetches the original record to check permissions
			if ($this->conf['edit'] && ($GLOBALS['TSFE']->loginUser || $this->aCAuth($origArr))) {
				// Must be logged in in order to edit  (OR be validated by email)
				$newFieldList = implode(',', array_intersect(explode(',', $this->fieldList), t3lib_div::trimExplode(',', $this->conf['edit.']['fields'], 1)));
				$newFieldList  = implode(',', array_unique( array_merge (explode(',', $newFieldList), explode(',', $this->adminFieldList))));
				if ($this->aCAuth($origArr) || $this->cObj->DBmayFEUserEdit($this->theTable, $origArr, $GLOBALS['TSFE']->fe_user->user, $this->conf['allowedGroups'], $this->conf['fe_userEditSelf'])) {
	
					if ( $this->debug )
					{
						 //cbPrint2( 'newFieldList', $newFieldList );
					}

					$newFieldList	= implode( ',', array_diff( 
											explode( ',', $newFieldList )
											, $dropFields
										)
									);
	
					if ( $this->debug )
					{
						 cbPrint2( 'newFieldList', $newFieldList );
						 //exit();
					}

					$this->noArrayArray( $this->dataArr );
					$this->rectifyUserGroup();
					$query = $this->cObj->DBgetUpdate($this->theTable, $theUid, $this->parseOutgoingDates( $this->dataArr ), $newFieldList);

					if ( $this->debug )
					{
						 cbPrint2( 'query', $query );
						 cbPrint2( 'dataArr["usergroup"]', $this->dataArr["usergroup"] );
						 exit();
					}
					$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
					echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
					$this->currentArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $theUid);
					$this->userProcess_alt($this->conf['edit.']['userFunc_afterSave'], $this->conf['edit.']['userFunc_afterSave.'], array('rec' => $this->currentArr, 'origRec' => $origArr));
					$this->unsetExpiredField($theUid);  //set expired flag to 0 if this is a prof/comp.prof. member - js
					$this->saved = 1;
				} else {
					 
					$this->error = '###TEMPLATE_NO_PERMISSIONS###';
				}
			}
			 
			break;
			default:
			if ($this->debug) echo "case default <br>\n";
			if ($this->conf['create']) {
				$newFieldList = implode(array_intersect(explode(',', $this->fieldList), t3lib_div::trimExplode(',', $this->conf['create.']['fields'], 1)), ',');
				$newFieldList  = implode( array_unique( array_merge (explode(',', $newFieldList), explode(',', $this->adminFieldList))), ',');

				$newFieldList	= implode( ',', array_diff( 
										explode( ',', $newFieldList )
										, $dropFields
									)
								);

				$this->noArrayArray( $this->dataArr );
				$query = $this->cObj->DBgetInsert($this->theTable, $this->thePid, $this->parseOutgoingDates( $this->dataArr ), $newFieldList);

				if ( $this->debug )
				{
					 cbPrint2( 'query', $query );
					 exit();
				}

				$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
				echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
				$newId = $GLOBALS[ 'TYPO3_DB' ]->sql_insert_id();
				
				$this->upgradeMemberAccess($newId); // - js
				
				if ($this->theTable == "fe_users" && $this->conf['fe_userOwnSelf']) {
					// enables users, creating logins, to own them self.
					$extraList = '';
					$dataArr = array();
					if ($GLOBALS['TCA'][$this->theTable]['ctrl']['fe_cruser_id']) {
						$field = $GLOBALS['TCA'][$this->theTable]['ctrl']['fe_cruser_id'];
						$dataArr[$field] = $newId;
						$extraList .= ','.$field;
					}
					if ($GLOBALS['TCA'][$this->theTable]['ctrl']['fe_crgroup_id']) {
						$field = $GLOBALS['TCA'][$this->theTable]['ctrl']['fe_crgroup_id'];
						list($dataArr[$field]) = explode(',', $this->dataArr['usergroup']);
						$dataArr[$field] = intval($dataArr[$field]);
						$extraList .= ','.$field;
					}
					if (count($dataArr)) {
						$query = $this->cObj->DBgetUpdate($this->theTable, $newId, $dataArr, $extraList);
						 
						if ($this->conf['debug']) debug('Own-self query: '.$query, 1);
							$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
						echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
					}
					 
				}
				 
				$this->currentArr = $this->parseIncomingTimestamps( $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $newId));
				$this->userProcess_alt($this->conf['create.']['userFunc_afterSave'], $this->conf['create.']['userFunc_afterSave.'], array('rec' => $this->currentArr));
				$this->saved = 1;
			}
			break;
		}
	}
	 
	/**
	* Removes required parts
	*
	* Works like this:
	* - Insert subparts like this ###SUB_REQUIRED_FIELD_".$theField."### that tells that the field is requires, if it's not correctly filled in.
	* - These subparts are all removed, except if the field is listed in $failure string!
	* and remove also the parts of non-included fields, using a similar scheme!
	*
	* @param string  $templateCode: the content of the HTML template
	* @param string  $failure: the list of fiels with errors
	* @return string  the template with susbstituted parts
	*/
	function removeRequired($templateCode, $failure) {
		reset($this->requiredArr);
		$includedFields = t3lib_div::trimExplode(',', $this->conf[$this->cmdKey.'.']['fields'], 1);
		$infoFields = explode(',', $this->fieldList);
		while (list(, $fName) = each($infoFields) ) {
			if (in_array(trim($fName), $this->requiredArr) ) {
				if (!t3lib_div::inList($failure, $fName)) {
					$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_FIELD_'.$fName.'###', '');
				}
			} else {
				$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_FIELD_'.$fName.'###', '');
				if (!in_array(trim($fName), $includedFields) ) {
					$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_INCLUDED_FIELD_'.$fName.'###', '');
				} else {
					if (is_array($this->conf['parseValues.']) && strstr($this->conf['parseValues.'][$fName],'checkArray')) {
						$listOfCommands = t3lib_div::trimExplode(',', $this->conf['parseValues.'][$fName], 1);
							while (list(, $cmd) = each($listOfCommands)) {
								$cmdParts = split('\[|\]', $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
								$theCmd = trim($cmdParts[0]);
								switch($theCmd) {
									case 'checkArray':
										$positions = t3lib_div::trimExplode(';', $cmdParts[1]);
										for($i=0; $i<10; $i++) {
											if(!in_array($i, $positions)) {
												$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_INCLUDED_FIELD_'.$fName.'_'.$i.'###', '');
											}
										}
									break;
								}
							}
					}
				}
			}
		}
		return $templateCode;
	}
	 
	/**
	* Initializes a template, filling values for data and labels
	*
	* @param string  $key: the template key
	* @param array  $r: the data array, if any
	* @return string  the template with substituted parts and markers
	*/
	function getPlainTemplate($key, $r = '') {
		$templateCode = $this->cObj->getSubpart($this->templateCode, $key);
		$markerArray = is_array($r) ? $this->cObj->fillInMarkerArray($this->markerArray, $r) :
		$this->markerArray;
		$markerArray = $this->addStaticInfoMarkers($markerArray, $r);
		$markerArray = $this->addLabelMarkers($markerArray, $r);
		$templateCode = $this->removeStaticInfoSubparts($templateCode, $markerArray);
		return $this->cObj->substituteMarkerArray($templateCode, $markerArray);
	}
	 
	/**
	* Displays the record update form
	*
	* @param array  $origArr: the array coming from the database
	* @return string  the template with substituted markers
	*/
	function displayEditForm($origArr) {
		// MLC mod merging
		$currentArr			= isset( $this->dataArr[ 'uid' ] )
								? array_merge( $origArr[ 0 ], $this->dataArr )
								: $origArr[ 0 ];
		$templateCode = $this->cObj->getSubpart($this->templateCode, "###TEMPLATE_EDIT".$this->previewLabel.'###');
		$failure = t3lib_div::GPvar('noWarnings')?"":
		$this->failure;
		if (!$failure) {
			$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_FIELDS_WARNING###', '');
		}
		$templateCode = $this->removeRequired($templateCode, $failure);
		$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $currentArr);
		$markerArray = $this->addStaticInfoMarkers($markerArray, $currentArr);
		$markerArray = $this->addLabelMarkers($markerArray, $currentArr);
		$markerArray = $this->addFileUploadMarkers('image', $markerArray, $currentArr);
		$templateCode = $this->removeStaticInfoSubparts($templateCode, $markerArray);
		$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="FE['.$this->theTable.'][uid]" value="'.$currentArr['uid'].'" />';
		if ( $this->theTable != 'fe_users' ) {
			$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[aC]" value="'.$this->authCode($origArr).'" />';
			$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[cmd]" value="edit" />';
		}
		if ($this->conf['edit.']['preview'] && !$this->previewLabel) {
			$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[preview]" value="1">';
		}
		$content = $this->cObj->substituteMarkerArray($templateCode, $markerArray);
		// $content .= $this->cObj->getUpdateJS($this->modifyDataArrForFormUpdate($currentArr), $this->theTable."_form", "FE[".$this->theTable."]", $this->fieldList.$this->additionalUpdateFields);
		$content .= $this->getUpdateJS($this->modifyDataArrForFormUpdate($currentArr), $this->theTable."_form", "FE[".$this->theTable."]", $this->fieldList.$this->additionalUpdateFields);
		return $content;
	}
	 
	/**
	* Checks if the edit form may be displayed; if not, a link to login
	*
	* @return string  the template with substituted markers
	*/
	function displayEditScreen() {
		if ($this->conf['edit']) {
			// If editing is enabled
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->dataArr['uid']?$this->dataArr['uid']:$this->recUid);
			// MLC merge dataArr with origArr to capture inline edits
			$origArr			= array( $origArr, $this->dataArr );

			// MLC removes the silly PCO
			$origArr[ 'zone' ]	= ( 'USA' != $origArr[ 'static_country_info' ]
									&& 'PCO' == $origArr[ 'zone' ]
								)
									? ''
									: $origArr[ 'zone' ];

			if( $this->theTable != 'fe_users' && $this->conf['setfixed.']['edit.']['_FIELDLIST']) {
				$fD = t3lib_div::GPvar('fD', 1);
				$fieldArr = array();
				if (is_array($fD)) {
					reset($fD);
					while (list($field, $value) = each($fD)) {
						$origArr[$field] = rawurldecode($value);
						$fieldArr[] = $field;
					}
				}
				$theCode = $this->setfixedHash($origArr, $origArr['_FIELDLIST']);
			}
			$origArr = $this->parseIncomingTimestamps($origArr);

			if ( is_array($origArr) && ( ($this->theTable == 'fe_users' && $GLOBALS['TSFE']->loginUser) || $this->aCAuth($origArr) || !strcmp($this->authCode, $theCode) ) ) {
				// Must be logged in OR be authenticated by the aC code in order to edit
				// If the recUid selects a record.... (no check here)
				if (is_array($origArr)) {
					if ( !strcmp($this->authCode, $this->theCode) || $this->aCAuth($origArr) || $this->cObj->DBmayFEUserEdit($this->theTable, $origArr, $GLOBALS['TSFE']->fe_user->user, $this->conf['allowedGroups'], $this->conf['fe_userEditSelf'])) {
						// Display the form, if access granted.
						$content = $this->displayEditForm($origArr);
					} else {
						// Else display error, that you could not edit that particular record...
						$content = $this->getPlainTemplate('###TEMPLATE_NO_PERMISSIONS###');
					}
				}
			} else {
				// This is if there is no login user. This must tell that you must login. Perhaps link to a page with create-user or login information.
				$content = $this->getPlainTemplate('###TEMPLATE_AUTH###');
			}
			 
		} else {
			$content .= 'Edit-option is not set in TypoScript';
		}
		return $content;
		 
	}
	 
	/**
	* Processes a record deletion request
	*
	* @return void  sets $this->saved
	*/
	function deleteRecord() {
		if ($this->conf['delete']) {
			// If deleting is enabled
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->recUid);
			if ($GLOBALS['TSFE']->loginUser || $this->aCAuth($origArr)) {
				 
				// Must be logged in OR be authenticated by the aC code in order to delete
				// If the recUid selects a record.... (no check here)
				if (is_array($origArr)) {
					if ($this->aCAuth($origArr) || $this->cObj->DBmayFEUserEdit($this->theTable, $origArr, $GLOBALS['TSFE']->fe_user->user, $this->conf['allowedGroups'], $this->conf['fe_userEditSelf'])) {
						// Display the form, if access granted.
						if (!$this->TCA['ctrl']['delete'] || $this->conf['forceFileDelete']) {
							// If the record is fully deleted... then remove the image attached.
							$this->deleteFilesFromRecord($this->recUid);
						}
						 
						$query = $this->cObj->DBgetDelete($this->theTable, $this->recUid);
						$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
						echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
						$this->currentArr = $origArr;
						$this->saved = 1;
					} else {
						$this->error = '###TEMPLATE_NO_PERMISSIONS###';
					}
				}
			}
		}
	}
	 
	/**
	* Deletes files associated with a deleted record
	*
	* @param string  $uid: record id
	* @return void
	*/
	function deleteFilesFromRecord($uid) {
		// Deletes the files attached to a record and updates the record.
		$rec = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $uid);
		 
		reset($this->TCA['columns']);
		$iFields = array();
		while (list($field, $conf) = each($this->TCA['columns'])) {
			if ($conf['config']['type'] == "group" && $conf['config']['internal_type'] == 'file') {
				$query = 'UPDATE '.$this->theTable.' SET '.$field."='' WHERE uid=".$uid;
				$res = $GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
				echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
				 
				$delFileArr = explode(',', $rec[$field]);
				reset($delFileArr);
				while (list(, $n) = each($delFileArr)) {
					if ($n) {
						$fpath = $conf['config']['uploadfolder'].'/'.$n;
						unlink($fpath);
					}
				}
			}
		}
	}
	 
	/**
	* This is basically the preview display of delete
	*
	* @return string  the template with substituted markers
	*/
	function displayDeleteScreen() {
		if ($this->conf['delete']) {
			// If deleting is enabled
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->recUid);
			if ( ($this->theTable == 'fe_users' && $GLOBALS['TSFE']->loginUser) || $this->aCAuth($origArr)) {
				// Must be logged in OR be authenticated by the aC code in order to delete
				// If the recUid selects a record.... (no check here)
				if (is_array($origArr)) {
					if ($this->aCAuth($origArr) || $this->cObj->DBmayFEUserEdit($this->theTable, $origArr, $GLOBALS['TSFE']->fe_user->user, $this->conf['allowedGroups'], $this->conf['fe_userEditSelf'])) {
						// Display the form, if access granted.
						$this->markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="rU" value="'.$this->recUid.'" />';
						if ( $this->theTable != 'fe_users' ) {
							$this->markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[aC]" value="'.$this->authCode($origArr).'" />';
							$this->markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[cmd]" value="delete" />';
						}
						$content = $this->getPlainTemplate('###TEMPLATE_DELETE_PREVIEW###', $origArr);
					} else {
						// Else display error, that you could not edit that particular record...
						$content = $this->getPlainTemplate('###TEMPLATE_NO_PERMISSIONS###');
					}
				}
			} else {
				// Finally this is if there is no login user. This must tell that you must login. Perhaps link to a page with create-user or login information.
				if ( $this->theTable == 'fe_users' ) {
					$content = $this->getPlainTemplate('###TEMPLATE_AUTH###');
				} else {
					$content = $this->getPlainTemplate('###TEMPLATE_NO_PERMISSIONS###');
				}
			}
		} else {
			$content .= 'Delete-option is not set in TypoScript';
		}
		return $content;
	}
	 
	/**
	* Generates the record creation form
	*
	* @return string  the template with substituted markers
	*/
	function displayCreateScreen($cmd = 'create') {
		if ($this->conf['create']) {
			$key				= ($cmd == 'invite')
									? 'INVITE'
									: 'CREATE';
			$templateCode = $this->cObj->getSubpart($this->templateCode, ((!$GLOBALS['TSFE']->loginUser || $cmd == 'invite')?'###TEMPLATE_'.$key.$this->previewLabel.'###':'###TEMPLATE_CREATE_LOGIN###'));

			// MLC csv of failed fields
			$failure			= t3lib_div::GPvar('noWarnings')
									? ''
									: $this->failure;
			if ( !$failure )
			{
				$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_FIELDS_WARNING###', '');
			}

			// MLC removes the silly PCO
			$this->dataArr[ 'zone' ]	= (
								'USA' != $this->dataArr[ 'static_country_info' ]
									&& 'PCO' == $this->dataArr[ 'zone' ]
								)
									? ''
									: $this->dataArr[ 'zone' ];
			$templateCode = $this->removeRequired($templateCode, $failure);
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $this->dataArr);
			$markerArray = $this->addStaticInfoMarkers($markerArray, $this->dataArr);
			$markerArray = $this->addFileUploadMarkers('image', $markerArray, $this->dataArr);
			$markerArray = $this->addLabelMarkers($markerArray, $this->dataArr);
			$templateCode = $this->removeStaticInfoSubparts($templateCode, $markerArray);
			if ($this->conf['create.']['preview'] && !$this->previewLabel) {
				$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[preview]" value="1">';
			}
			$content = $this->cObj->substituteMarkerArray($templateCode, $markerArray);
			// $content .= $this->cObj->getUpdateJS($this->modifyDataArrForFormUpdate($this->dataArr), $this->theTable."_form", "FE[".$this->theTable."]", $this->fieldList.$this->additionalUpdateFields);
			$content .= $this->getUpdateJS($this->modifyDataArrForFormUpdate($this->dataArr), $this->theTable."_form", "FE[".$this->theTable."]", $this->fieldList.$this->additionalUpdateFields);
		}
		return $content;
	}

	/**
	 * Sends info mail to subscriber
	 *
	 * @return	string		HTML content message
	 * @see init(),compileMail(), sendMail()
	 */
	function sendInfoMail()	{
		if ($this->conf['infomail'] && $this->conf['email.']['field'])	{
			$fetch = $this->feUserData['fetch'];
			if (isset($fetch))	{
				$pidLock=' AND pid IN ('.$this->thePid.') ';

					// Getting records
				if ( $this->theTable == 'fe_users' && t3lib_div::testInt($fetch) )	{
					$DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,'uid',$fetch,$pidLock,'','','1');
				} elseif ($fetch) {	// $this->conf['email.']['field'] must be a valid field in the table!
					$DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,$this->conf['email.']['field'],$fetch,$pidLock,'','','100');
				}
					// Processing records
				if (is_array($DBrows))	{
					$recipient = $DBrows[0][$this->conf['email.']['field']];
					$this->compileMail('INFOMAIL', $DBrows, trim($recipient), $this->conf['setfixed.']);
				} elseif ($this->cObj->checkEmail($fetch)) {
					$fetchArray = array( '0' => array( 'email' => $fetch));
					$this->compileMail('INFOMAIL_NORECORD', $fetchArray, $fetch);
				}

				$content = $this->getPlainTemplate('###TEMPLATE_'.$this->infomailPrefix.'SENT###', (is_array($DBrows)?$DBrows[0]:''));
			} else {
				$content = $this->getPlainTemplate('###TEMPLATE_INFOMAIL###');
			}
		} else {
			$content='Configuration error: infomail option is not available or emailField is not setup in TypoScript';
		}
		return $content;
	}
	 
	/**
	* Updates the input array from preview
	*
	* @param array  $inputArr: new values
	* @return array  updated array
	*/
	function modifyDataArrForFormUpdate($inputArr) {
		if (is_array($this->conf[$this->cmdKey.'.']['evalValues.'])) {
			reset($this->conf[$this->cmdKey.'.']['evalValues.']);
			 
			while (list($theField, $theValue) = each($this->conf[$this->cmdKey.'.']['evalValues.'])) {

				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);
				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd) {
						case 'twice':
						if (isset($inputArr[$theField])) {
							if (!isset($inputArr[$theField.'_again'])) {
								$inputArr[$theField.'_again'] = $inputArr[$theField];
							}
							$this->additionalUpdateFields .= ','.$theField.'_again';
						}
						break;
					}
				}
			}
		}
		 
		if (is_array($this->conf['parseValues.'])) {
			reset($this->conf['parseValues.']);
			while (list($theField, $theValue) = each($this->conf['parseValues.'])) {
				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);
				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd) {
						case 'multiple':
						if (isset($inputArr[$theField]) && !$this->isPreview()) {
							$inputArr[$theField] = explode(',', $inputArr[$theField]);
						}
						break;
						case 'checkArray':
						if ($inputArr[$theField] && !$this->isPreview()) {
							for($a = 0; $a <= 30; $a++) {
								if ($inputArr[$theField] & pow(2, $a)) {
									$alt_theField = $theField.']['.$a;
									$inputArr[$alt_theField] = 1;
									$this->additionalUpdateFields .= ','.$alt_theField;
								}
							}
						}
						break;
					}
				}
			}
		}
		$inputArr = $this->userProcess_alt($this->conf['userFunc_updateArray'], $this->conf['userFunc_updateArray.'], $inputArr );
		return $inputArr;
	}
	 
	/**
	* Process the front end user reply to the confirmation request
	*
	* @return string  the template with substituted markers
	*/
	function procesSetFixed() {
		if ($this->setfixedEnabled) {
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->recUid);
			$origUsergroup = $origArr['usergroup'];
			$fD = t3lib_div::GPvar('fD', 1);
			$fieldArr = array();
			if (is_array($fD)) {
				reset($fD);
				while (list($field, $value) = each($fD)) {
					$origArr[$field] = rawurldecode($value);
					$fieldArr[] = $field;
				}
			}
			 
			$theCode = $this->setfixedHash($origArr, $origArr['_FIELDLIST']);
			if (!strcmp($this->authCode, $theCode)) {
				if ($this->feUserData['sFK'] == 'DELETE') {
					if (!$this->TCA['ctrl']['delete'] || $this->conf['forceFileDelete']) {
						// If the record is fully deleted... then remove the image attached.
						$this->deleteFilesFromRecord($this->recUid);
					}
					$query = $this->cObj->DBgetDelete($this->theTable, $this->recUid);
					$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
					echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
				} else {
					if ($this->theTable == 'fe_users' && $origUsergroup != $this->conf['create.']['overrideValues.']['usergroup'] ) {
						$origArr['usergroup'] = $origUsergroup;
					}
						// Hook: first we initialize the hooks
					$hookObjectsArr = array();
					if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['confirmRegistrationClass'])) {
						foreach  ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['confirmRegistrationClass'] as $classRef) {
							$hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
						}
					}
						// Hook: confirmRegistrationClass_preProcess
					foreach($hookObjectsArr as $hookObj)    {
						if (method_exists($hookObj, 'confirmRegistrationClass_preProcess')) {
							$hookObj->confirmRegistrationClass_preProcess($origArr, $this);
						}
					}
					$newFieldList = implode(array_intersect(t3lib_div::trimExplode(',', $this->fieldList), t3lib_div::trimExplode(',', implode($fieldArr, ','), 1)), ',');
					$query = $this->cObj->DBgetUpdate($this->theTable, $this->recUid, $origArr, $newFieldList);
					$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
					echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
						// Hook: confirmRegistrationClass_postProcess
					foreach($hookObjectsArr as $hookObj)    {
						if (method_exists($hookObj, 'confirmRegistrationClass_postProcess')) {
							$hookObj->confirmRegistrationClass_preProcess($origArr, $this);
						}
					} 
				}
				 
				// Outputting template
				$content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_OK_'.$this->feUserData['sFK'].'###', $origArr);
				if (!$content) {
					$content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_OK###', $origArr);
				}
				 
				// Compiling email
				$this->dataArr = $origArr;
				$this->compileMail(
					$this->setfixedPrefix.$this->feUserData['sFK'],
					array($origArr),
					$origArr[$this->conf['email.']['field']],
					$this->conf['setfixed.']
				);
				 
				// Auto-login on confirmation
				if ($this->theTable == 'fe_users' && $this->feUserData['sFK'] == 'APPROVE' && $this->conf['enableAutoLoginOnConfirmation']) {
					$loginVars = array();
					$loginVars['user'] = $origArr['username'];
					$loginVars['pass'] = $origArr['password'];
					$loginVars['pid'] = $this->thePid;
					$loginVars['logintype'] = 'login';
					$loginVars['redirect_url'] = htmlspecialchars(trim($this->conf['autoLoginRedirect_url']));
					header('Location: '.t3lib_div::locationHeaderUrl($this->site_url.$this->cObj->getTypoLink_URL($this->loginPID.','.$GLOBALS['TSFE']->type, $loginVars)));
					exit;
				}
			} else {
				$content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_FAILED###');
			}
		}
		return $content;
	}
	 
	/**
	* Prepares an email message
	*
	* @param string  $key: template key
	* @param array  $DBrows: invoked with just one row of fe_users!!
	* @param string  $recipient: an email or the id of a front user
	* @param array  $setFixedConfig: a setfixed TS config array
	* @return void
	*/
	function compileMail($key, $DBrows, $recipient, $setFixedConfig = array())
	{
		// MLC translate values to English
		$DBrows					= $this->dataArrTranslate( $DBrows );

		$mailContent = '';
		$userContent['all'] = '';
		$HTMLContent['all'] = '';
		$adminContent['all'] = '';
		if (($this->conf['email.'][$key] ) || ($key == 'SETFIXED_CREATE' && $this->setfixedEnabled) || ($key == 'SETFIXED_INVITE' && $this->setfixedEnabled) ) {
			$userContent['all'] = trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.'###'));
			$HTMLContent['all'] = ($this->HTMLMailEnabled && $this->dataArr['module_sys_dmail_html']) ? trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.$this->emailMarkHTMLSuffix.'###')):
			'';
		}
		if ($this->conf['notify.'][$key] ) {
			$adminContent['all'] = trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.$this->emailMarkAdminSuffix.'###'));
		}
		$userContent['rec'] = $this->cObj->getSubpart($userContent['all'], '###SUB_RECORD###');
		$HTMLContent['rec'] = $this->cObj->getSubpart($HTMLContent['all'], '###SUB_RECORD###');
		$adminContent['rec'] = $this->cObj->getSubpart($adminContent['all'], '###SUB_RECORD###');
		 
		reset($DBrows);
		while (list(, $r) = each($DBrows)) {
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $r, '', 0);
			$markerArray['###SYS_AUTHCODE###'] = $this->authCode($r);
			$markerArray = $this->setfixed($markerArray, $setFixedConfig, $r);
			// MLC don't forget to translate
			$markerArray = $this->addStaticInfoMarkers($markerArray, $r);
			$markerArray = $this->addLabelMarkers($markerArray, $r);
			if ($userContent['rec']) {
				$userContent['accum'] .= $this->cObj->substituteMarkerArray($userContent['rec'], $markerArray);
			}
			if ($HTMLContent['rec']) {
				$HTMLContent['accum'] .= $this->cObj->substituteMarkerArray($HTMLContent['rec'], $markerArray);
			}
			if ($adminContent['rec']) {
				$adminContent['accum'] .= $this->cObj->substituteMarkerArray($adminContent['rec'], $markerArray);
			}
		}
		if ($userContent['all']) {
			$userContent['final'] .= strip_tags($this->cObj->substituteSubpart($userContent['all'], '###SUB_RECORD###', $userContent['accum']));
		}
		if ($HTMLContent['all']) {
			$HTMLContent['final'] .= $this->cObj->substituteSubpart($HTMLContent['all'], '###SUB_RECORD###', $this->pi_wrapInBaseClass($HTMLContent['accum']));
			$HTMLContent['final'] = $this->cObj->substituteMarkerArray($HTMLContent['final'], $markerArray);			 
		}
		if ($adminContent['all']) {
			$adminContent['final'] .= $this->cObj->substituteSubpart($adminContent['all'], '###SUB_RECORD###', $adminContent['accum']);
			 
		}
		 
		if (t3lib_div::testInt($recipient)) {
			$fe_userRec = $GLOBALS['TSFE']->sys_page->getRawRecord('fe_users', $recipient);
			$recipient = $fe_userRec['email'];
		}
		 
		// Check if we need to add an attachment
		if ($this->conf['addAttachment'] && $this->conf['addAttachment.']['cmd'] == $this->cmd && $this->conf['addAttachment.']['sFK'] == $this->feUserData['sFK']) {
			$file = ($this->conf['addAttachment.']['file']) ? $GLOBALS['TSFE']->tmpl->getFileName($this->conf['addAttachment.']['file']):
			'';
		}
		$this->sendMail($recipient, $this->conf['email.']['admin'], $userContent['final'], $adminContent['final'], $HTMLContent['final'], $file);
	}
	 
	/**
	* Dispatches the email messsage
	*
	* @param string  $recipient: email address
	* @param string  $admin: email address
	* @param string  $content: plain content for the recipient
	* @param string  $adminContent: plain content for admin
	* @param string  $HTMLContent: HTML content for the recipient
	* @param string  $fileAttachment: file name
	* @return void
	*/
	function sendMail($recipient, $admin, $content = '', $adminContent = '', $HTMLContent = '', $fileAttachment = '') {
		// Send mail to admin
		if ($admin && $adminContent) {
			$this->cObj->sendNotifyEmail($adminContent, $admin, '', $this->conf['email.']['from'], $this->conf['email.']['fromName'], $recipient);
		}
		// Send mail to front end user
		if ($this->HTMLMailEnabled && $HTMLContent && $this->dataArr['module_sys_dmail_html']) {
			$this->sendHTMLMail($HTMLContent, $content, $recipient, '', $this->conf['email.']['from'], $this->conf['email.']['fromName'], '', $fileAttachment);
		} else {
			$this->cObj->sendNotifyEmail($content, $recipient, '', $this->conf['email.']['from'], $this->conf['email.']['fromName']);
		}
	}
	 
	/**
	* Invokes the HTML mailing class
	*
	* @param string  $HTMLContent: HTML version of the message
	* @param string  $PLAINContent: plain version of the message
	* @param string  $recipient: email address
	* @param string  $dummy: ''
	* @param string  $fromEmail: email address
	* @param string  $fromName: name
	* @param string  $replyTo: email address
	* @param string  $fileAttachment: file name
	* @return void
	*/
	function sendHTMLMail($HTMLContent, $PLAINContent, $recipient, $dummy, $fromEmail, $fromName, $replyTo = '', $fileAttachment = '') {
		// HTML
		if (trim($recipient)) {
			$parts = spliti('<title>|</title>', $HTMLContent, 3);
			$subject = trim($parts[1]) ? strip_tags(trim($parts[1])) :
			'Front end user registration message';
			 
			$Typo3_htmlmail = t3lib_div::makeInstance('tx_srfeuserregister_pi1_t3lib_htmlmail');
			$Typo3_htmlmail->charset = 'iso-8859-1';
			$Typo3_htmlmail->start();
			$Typo3_htmlmail->messageid = md5(microtime());
			$Typo3_htmlmail->mailer = 'Typo3 HTMLMail';		 
			$Typo3_htmlmail->subject = $Typo3_htmlmail->convertName($subject);
			$Typo3_htmlmail->from_email = $fromEmail;
			$Typo3_htmlmail->from_name = $fromName;
			$Typo3_htmlmail->from_name = implode(' ' , t3lib_div::trimExplode(',', $Typo3_htmlmail->from_name));
			$Typo3_htmlmail->replyto_email = $replyTo ? $replyTo :$fromEmail;
			$Typo3_htmlmail->replyto_name = $replyTo ? '' : $fromName;
			$Typo3_htmlmail->replyto_name = implode(' ' , t3lib_div::trimExplode(',', $Typo3_htmlmail->replyto_name));
			$Typo3_htmlmail->organisation = '';
			$Typo3_htmlmail->priority = 3;
			 
			// ATTACHMENT
			if ($fileAttachment && file_exists($fileAttachment)) {
				$Typo3_htmlmail->addAttachment($fileAttachment);
			}
			 
			// HTML
			if (trim($HTMLContent)) {
				$Typo3_htmlmail->theParts['html']['content'] = $HTMLContent;
				$Typo3_htmlmail->theParts['html']['path'] = '';
				// MLC don't include media in HTML emails
				// $Typo3_htmlmail->extractMediaLinks();
				// $Typo3_htmlmail->extractHyperLinks();
				// $Typo3_htmlmail->fetchHTMLMedia();
				$Typo3_htmlmail->substMediaNamesInHTML(0); // 0 = relative
				$Typo3_htmlmail->substHREFsInHTML();
				 
				$Typo3_htmlmail->setHTML($Typo3_htmlmail->encodeMsg($Typo3_htmlmail->theParts['html']['content']));
			}
			 
			// PLAIN
			$Typo3_htmlmail->addPlain($PLAINContent);
			 
			// SET Headers and Content
			$Typo3_htmlmail->setHeaders();
			$Typo3_htmlmail->setContent();
			$Typo3_htmlmail->setRecipient($recipient);
			$Typo3_htmlmail->sendTheMail();
		}
	}
	 
	/**
	* Computes the authentication code
	*
	* @param array  $r: the data array
	* @param string  $extra: some extra mixture
	* @return string  the code
	*/
	function authCode($r, $extra = '') {
		$l = $this->codeLength;
		if ($this->conf['authcodeFields']) {
			$fieldArr = t3lib_div::trimExplode(',', $this->conf['authcodeFields'], 1);
			$value = '';
			 
			while (list(, $field) = each($fieldArr)) {
				$value .= $r[$field].'|';
			}
			$value .= $extra.'|'.$this->conf['authcodeFields.']['addKey'];
			if ($this->conf['authcodeFields.']['addDate']) {
				$value .= '|'.date($this->conf['authcodeFields.']['addDate']);
			}
			 
			$value .= $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
			return substr(md5($value), 0, $l);
		}
	}
	 
	/**
	* Authenticates a record
	*
	* @param array  $r: the record
	* @return boolean  true if the record is authenticated
	*/
	function aCAuth($r) {
		if ($this->authCode && !strcmp($this->authCode, $this->authCode($r))) {
			 
			 
			return true;
		}
	}
	 
	/**
	* Computes the setfixed url's
	*
	* @param array  $markerArray: the input marker array
	* @param array  $setfixed: the TS setup setfixed configuration
	* @param array  $r: the record
	* @return array  the output marker array
	*/
	function setfixed($markerArray, $setfixed, $r) {
		if ($this->setfixedEnabled && is_array($setfixed) ) {
			$setfixedpiVars = array();
			 
			reset($setfixed);
			while (list($theKey, $data) = each($setfixed)) {
				if (strstr($theKey, '.') ) {
					$theKey = substr($theKey, 0, -1);
				}
				unset($setfixedpiVars);
				 
				$recCopy = $r;
				$setfixedpiVars[$this->prefixId.'[rU]'] = $r[uid];

				if ( $this->theTable != 'fe_users' && $theKey == 'EDIT' ) {
					$setfixedpiVars[$this->prefixId.'[cmd]'] = 'edit';
					if (is_array($data) ) {
						reset($data);
						while (list($fieldName, $fieldValue) = each($data)) {
							$setfixedpiVars['fD['.$fieldName.']'] = rawurlencode($fieldValue);
							$recCopy[$fieldName] = $fieldValue;
						}
					}
					if( $this->conf['edit.']['setfixed']) {
						$setfixedpiVars[$this->prefixId.'[aC]'] = $this->setfixedHash($recCopy, $data['_FIELDLIST']);
					} else {
						$setfixedpiVars[$this->prefixId.'[aC]'] = $this->authCode($r);
					}
					$linkPID = $this->editPID;
				} else {
					$setfixedpiVars[$this->prefixId.'[cmd]'] = 'setfixed';
					$setfixedpiVars[$this->prefixId.'[sFK]'] = $theKey;
					if (is_array($data) ) {
						reset($data);
						while (list($fieldName, $fieldValue) = each($data)) {
							$setfixedpiVars['fD['.$fieldName.']'] = rawurlencode($fieldValue);
							$recCopy[$fieldName] = $fieldValue;
						}
					}
					$setfixedpiVars[$this->prefixId.'[aC]'] = $this->setfixedHash($recCopy, $data['_FIELDLIST']);
					$linkPID = $this->confirmPID;
				}

				if (t3lib_div::GPvar('L') ) {
					$setfixedpiVars['L'] = t3lib_div::GPvar('L');
				}

				$markerArray['###SETFIXED_'.strtoupper($theKey).'_URL###'] = $this->site_url.$this->cObj->getTypoLink_URL($linkPID.','.$this->confirmType, $setfixedpiVars);

			}
		}
		return $markerArray;
	}
	 
	/**
	 
	* Computes the setfixed hash
	*
	* @param array  $recCopy: copy of the record
	* @param string  $fields: the list of fields to include in the hash computation
	* @return string  the hash value
	*/
	function setfixedHash($recCopy, $fields = '') {
		if ($fields) {
			$fieldArr = t3lib_div::trimExplode(',', $fields, 1);
			reset($fieldArr);
			while (list($k, $v) = each($fieldArr)) {
				$recCopy_temp[$k] = $recCopy[$v];
				 
			}

		} else {
			$recCopy_temp = $recCopy;
		}
		$encStr = implode('|', $recCopy_temp).'|'.$this->conf['authcodeFields.']['addKey'].'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		$hash = substr(md5($encStr), 0, $this->codeLength);
		return $hash;
	}
	 
	/**
	* Checks if preview display is on.
	*
	* @return boolean  true if preview display is on
	*/
	 
	 
	function isPreview() {
		return ($this->conf[$this->cmdKey.'.']['preview'] && $this->feUserData['preview']);
	}
	 
	/**
	* Instantiate the file creation function
	*
	* @return void
	*/
	function createFileFuncObj() {
		if (!$this->fileFunc) {
			$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			 
		}
		 
	}
	 
	/**
	* Adds language-dependent label markers
	*
	* @param array  $markerArray: the input marker array
	* @param array  $dataArray: the record array
	* @return array  the output marker array
	*/
	function addLabelMarkers($markerArray, $dataArray) {
		 
		// Data field labels
		$infoFields = explode(',', $this->fieldList);
		while (list(, $fName) = each($infoFields) ) {
			$markerArray['###LABEL_'.strtoupper($fName).'###'] = $this->pi_getLL($fName);
			$markerArray['###FIELD_'.$fName.'_CHECKED###'] = ($dataArray[$fName])?'checked':
			'';
			$markerArray['###LABEL_'.$fName.'_CHECKED###'] = ($dataArray[$fName])?$this->pi_getLL('yes'):
			$this->pi_getLL('no');
			if (in_array(trim($fName), $this->requiredArr) ) {
				$markerArray['###REQUIRED_'.strtoupper($fName).'###'] = '*';
				$markerArray['###MISSING_'.strtoupper($fName).'###'] = $this->pi_getLL('missing_'.$fName);
				$markerArray['###MISSING_INVITATION_'.strtoupper($fName).'###'] = $this->pi_getLL('missing_invitation_'.$fName);
			} else {
				$markerArray['###REQUIRED_'.strtoupper($fName).'###'] = '';
			}
		}
		// Button labels
		$buttonLabelsList = 'register,confirm_register,back_to_form,update,confirm_update,enter,confirm_delete,cancel_delete,cancel_update';
		$buttonLabels = t3lib_div::trimExplode(',', $buttonLabelsList);
		while (list(, $labelName) = each($buttonLabels) ) {
			$markerArray['###LABEL_BUTTON_'.strtoupper($labelName).'###'] = $this->pi_getLL('button_'.$labelName);
		}
		// Labels possibly with variables
		$otherLabelsList = 'yes,no,password_repeat,click_here_to_register,click_here_to_edit,click_here_to_delete,'. ',copy_paste_link,enter_account_info,enter_invitation_account_info,required_info_notice,excuse_us'. ',registration_problem,registration_sorry,registration_clicked_twice,registration_help,kind_regards'. ',v_verify_before_create,v_verify_invitation_before_create,v_verify_before_update,v_really_wish_to_delete,v_edit_your_account'. ',v_dear,v_now_enter_your_username,v_notification'. ',v_registration_created,v_registration_created_subject,v_registration_created_message1,v_registration_created_message2'. ',v_please_confirm,v_your_account_was_created,v_follow_instructions1,v_follow_instructions2'. ',v_invitation_confirm,v_invitation_account_was_created,v_invitation_instructions1'. ',v_registration_initiated,v_registration_initiated_subject,v_registration_initiated_message1,v_registration_initiated_message2'. ',v_registration_invited,v_registration_invited_subject,v_registration_invited_message1,v_registration_invited_message2'. ',v_registration_confirmed,v_registration_confirmed_subject,v_registration_confirmed_message1,v_registration_confirmed_message2'. ',v_registration_cancelled,v_registration_cancelled_subject,v_registration_cancelled_message1,v_registration_cancelled_message2'. ',v_registration_updated,v_registration_updated_subject,v_registration_updated_message1'. ',v_registration_deleted,v_registration_deleted_subject,v_registration_deleted_message1,v_registration_deleted_message2';
		$otherLabels = t3lib_div::trimExplode(',', $otherLabelsList);
		while (list(, $labelName) = each($otherLabels) ) {
			$markerArray['###LABEL_'.strtoupper($labelName).'###'] = sprintf($this->pi_getLL($labelName), $this->thePidTitle, $dataArray['username'], $dataArray['name'], $dataArray['email'], $dataArray['password']);
			 
		}
		return $markerArray;
	}
	 
	/**
	* Adds URL markers to a $markerArray
	*
	* @param array  $markerArray: the input marker array
	* @return array  the output marker array
	*/
	function addURLMarkers($markerArray) {
		$vars = array();
		$unsetVarsList = 'backURL,submit,rU,aC,sFK,doNotSave,preview';
		$unsetVars = t3lib_div::trimExplode(',', $unsetVarsList);

		$unsetVars['cmd'] = 'cmd';
		$markerArray['###FORM_URL###'] = $this->get_url('', $GLOBALS['TSFE']->id.','.$GLOBALS['TSFE']->type, $vars, $unsetVars);
		$markerArray['###FORM_NAME###'] = $this->conf['formName'];
		$unsetVars['cmd'] = '';

		$vars['cmd'] = 'delete';
		$vars['backURL'] = rawurlencode($markerArray['###FORM_URL###']);
		$vars['rU'] = $this->recUid;
		$vars['preview'] = '1';
		$markerArray['###DELETE_URL###'] = $this->get_url('', $this->editPID.','.$GLOBALS['TSFE']->type, $vars);
		 
		$vars['cmd'] = 'create';
		$markerArray['###REGISTER_URL###'] = $this->get_url('', $this->registerPID.','.$GLOBALS['TSFE']->type, $vars, $unsetVars);
		$vars['cmd'] = 'edit';
		$markerArray['###EDIT_URL###'] = $this->get_url('', $this->editPID.','.$GLOBALS['TSFE']->type, $vars, $unsetVars);
		$vars['cmd'] = 'login';
		$markerArray['###LOGIN_FORM###'] = $this->get_url('', $this->loginPID.','.$GLOBALS['TSFE']->type, $vars, $unsetVars);
		$vars['cmd'] = 'infomail';
		$markerArray['###INFOMAIL_URL###'] = $this->get_url('', $this->registerPID.','.$GLOBALS['TSFE']->type, $vars, $unsetVars);
		 
		$markerArray['###THE_PID###'] = $this->thePid;
		$markerArray['###THE_PID_TITLE###'] = $this->thePidTitle;
		// MLC check that referrer is on current site, otherwise leave alone
		$markerArray['###REDIRECT###'] = $this->dataArr[ 'referrer_uri' ];
		$markerArray['###TEMPUSER###'] = $this->dataArr[ 'username' ];
		$markerArray['###TEMPPASS###'] = $this->dataArr[ 'password' ];
		$markerArray['###STARTTIME###'] = $this->dataArr[ 'starttime' ];
		$markerArray['###ENDTIME###'] = $this->dataArr[ 'endtime' ];
		$markerArray['###BACK_URL###'] = $this->backURL;
		$markerArray['###SITE_NAME###'] = $this->conf['email.']['fromName'];
		$markerArray['###SITE_URL###'] = $this->site_url;
		$markerArray['###SITE_WWW###'] = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
		$markerArray['###SITE_EMAIL###'] = $this->conf['email.']['from'];

		$markerArray['###HIDDENFIELDS###'] = '';
		if( $this->theTable == 'fe_users' ) $markerArray['###HIDDENFIELDS###'] = ($this->cmd?'<input type="hidden" name="'.$this->prefixId.'[cmd]" value="'.$this->cmd.'">':''). ($this->authCode?'<input type="hidden" name="'.$this->prefixId.'[aC]" value="'.$this->authCode.'">':''). ($this->backURL?'<input type="hidden" name="'.$this->prefixId.'[backURL]" value="'.htmlspecialchars($this->backURL).'">':'');
		return $markerArray;
	}
	 
	/**
	* Adds Static Info markers to a marker array

	*
	* @param array  $markerArray: the input marker array
	* @param array  $dataArray: the record array

	* @return array  the output marker array
	*/
	function addStaticInfoMarkers($markerArray, $dataArray = '') {
		 
		 // MLC always provide translation
		$markerArray['###FIELD_static_info_country###'] = $this->staticInfo->getStaticInfoName('COUNTRIES', is_array($dataArray)?$dataArray['static_info_country']:'');
		$markerArray['###FIELD_zone###'] = $this->staticInfo->getStaticInfoName('SUBDIVISIONS', is_array($dataArray)?$dataArray['zone']:'', is_array($dataArray)?$dataArray['static_info_country']:'');
		if (!$markerArray['###FIELD_zone###'] ) {
			$markerArray['###FIELD_zone###'] = is_array($dataArray)?$dataArray['zone']:'';
		}
		$markerArray['###FIELD_language###'] = $this->staticInfo->getStaticInfoName('LANGUAGES', is_array($dataArray)?$dataArray['language']:'');

		if (! $this->previewLabel ) {
			$markerArray['###SELECTOR_STATIC_INFO_COUNTRY###'] = $this->staticInfo->buildStaticInfoSelector('COUNTRIES', 'FE['.$this->theTable.']'.'[static_info_country]', '', is_array($dataArray)?$dataArray['static_info_country']:'', '', $this->conf['onChangeCountryAttribute']);
			$markerArray['###SELECTOR_ZONE###'] = $this->staticInfo->buildStaticInfoSelector('SUBDIVISIONS', 'FE['.$this->theTable.']'.'[zone]', '', is_array($dataArray)?$dataArray['zone']:'', is_array($dataArray)?$dataArray['static_info_country']:'');
			if (!$markerArray['###SELECTOR_ZONE###'] ) {
				$markerArray['###SELECTOR_ZONE###'] = '<input type="text" name="FE['.$this->theTable.'][zone]" size="20" />';
				// MLC offer blank instead
				// $markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="FE['.$this->theTable.'][zone]" value="">';
			}
			$markerArray['###SELECTOR_LANGUAGE###'] = $this->staticInfo->buildStaticInfoSelector('LANGUAGES', 'FE['.$this->theTable.']'.'[language]', '', is_array($dataArray)?$dataArray['language']:'');
		}
		return $markerArray;
	}
	 
	/**
	* Removes irrelevant Static Info subparts (zone selection when the country has no zone)
	*
	* @param string  $templateCode: the input template
	* @param array  $markerArray: the marker array
	* @return string  the output template
	*/
	function removeStaticInfoSubparts($templateCode, $markerArray) {
		 
		if ($this->previewLabel ) {
			if (!$markerArray['###FIELD_zone###'] ) {
				$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_INCLUDED_FIELD_'.zone.'###', '');
			}
		} else {
			if (!$markerArray['###SELECTOR_ZONE###'] ) {
				$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_INCLUDED_FIELD_'.zone.'###', '');
				 
			}
		}
		return $templateCode;
	}
	 
	/**
	* Adds CSS styles marker to a marker array for substitution in an HTML email message
	*
	* @param array  $markerArray: the input marker array
	* @return array  the output marker array
	*/
	function addCSSStyleMarkers($markerArray) {
		 
		if ($this->HTMLMailEnabled ) {
			$markerArray['###CSS_STYLES###'] = $this->cObj->fileResource($this->conf['email.']['HTMLMailCSS']);
		}
		return $markerArray;
	}
	 
	/**
	* Checks the error value from the upload $_FILES array.
	*
	* @param string  $error_code: the error code
	* @return boolean  true if ok
	*/
	function evalFileError($error_code) {
		if ($error_code == "0") {
			return true;
			// File upload okay
		} elseif ($error_code == '1') {
			return false; // filesize exceeds upload_max_filesize in php.ini
		} elseif ($error_code == '3') {
			return false; // The file was uploaded partially
		} elseif ($error_code == '4') {
			return true;
			// No file was uploaded
		} else {
			return true;
		}
	}
	 
	/**
	* Adds uploading markers to a marker array
	*
	* @param string  $theField: the field name
	* @param array  $markerArray: the input marker array
	* @param array  $dataArray: the record array
	* @return array  the output marker array
	*/
	function addFileUploadMarkers($theField, $markerArray, $dataArr = array()) {
		$filenames = array();
		if ($dataArr[$theField]) {
			$filenames = explode(',', $dataArr[$theField]);
		}
		if ($this->previewLabel ) {
			$markerArray['###UPLOAD_PREVIEW_' . $theField . '###'] = $this->buildFileUploader($theField, $this->TCA['columns'][$theField]['config'], $filenames, 'FE['.$this->theTable.']');
		} else {
			$markerArray['###UPLOAD_' . $theField . '###'] = $this->buildFileUploader($theField, $this->TCA['columns'][$theField]['config'], $filenames, 'FE['.$this->theTable.']');
		}
		return $markerArray;
	}
	 
	/**
	* Builds a file uploader
	 
	*
	* @param string  $fName: the field name
	* @param array  $config: the field TCA config
	* @param array  $filenames: array of uploaded file names
	* @param string  $prefix: the field name prefix

	* @return string  generated HTML uploading tags
	*/
	function buildFileUploader($fName, $config, $filenames = array(), $prefix) {
		$HTMLContent = '';
		$size = $config['maxitems'];
		$number = $size - sizeof($filenames);
		$dir = $config['uploadfolder'];
		 
		if ($this->previewLabel ) {
			for($i = 0; $i < sizeof($filenames); $i++) {
				$HTMLContent .= $filenames[$i] . '&nbsp;&nbsp;<small><a href="' . $dir.'/' . $filenames[$i] . '" target="_blank">' . $this->pi_getLL('file_view') . '</a></small><br />';
			}
		} else {
			for($i = 0; $i < sizeof($filenames); $i++) {
				$HTMLContent .= $filenames[$i] . '&nbsp;&nbsp;<input type="image" src="' . $GLOBALS['TSFE']->tmpl->getFileName($this->conf['icon_delete']) . '" name="'.$prefix.'['.$fName.']['.$i.'][submit_delete]" value="1" title="'.$this->pi_getLL('icon_delete').'" alt="'.$this->pi_getLL('icon_delete').'"'.$this->pi_classParam('icon').' />&nbsp;&nbsp;<small><a href="' . $dir.'/' . $filenames[$i] . '" target="_blank">' . $this->pi_getLL('file_view') . '</a></small><br />';
				$HTMLContent .= '<input type="hidden" name="' . $prefix . '[' . $fName . '][' . $i . '][name]' . '" value="' . $filenames[$i] . '" />';
			}
			for($i = sizeof($filenames); $i < $number + sizeof($filenames); $i++) {
				$HTMLContent .= '<input name="'.$prefix.'['.$fName.']['.$i.']'.'" type="file" '.$this->pi_classParam('uploader').' /><br />';
			}
		}
		 
		return $HTMLContent;
	}
	 
	/**
	* Generates a pibase-compliant typolink

	*
	* @param string  $tag: string to include within <a>-tags; if empty, only the url is returned
	* @param string  $id: page id (could of the form id,type )
	* @param array  $vars: extension variables to add to the url ($key, $value)
	* @param array  $unsetVars: extension variables (piVars to unset)
	* @param boolean  $usePiVars: if set, input vars and incoming piVars arrays are merge
	* @return string  generated link or url
	*/
	function get_url($tag = '', $id, $vars = array(), $unsetVars = array(), $usePiVars = true) {
		 
		$vars = (array) $vars;
		$unsetVars = (array) $unsetVars;
		if ($usePiVars) {
			$vars = array_merge($this->piVars, $vars); //vars override pivars
			while (list(, $key) = each($unsetVars)) {
				// unsetvars override anything
				unset($vars[$key]);
			}
		}
		while (list($key, $val) = each($vars)) {
			$piVars[$this->prefixId . '['. $key . ']'] = $val;
		}

		// MLC update to use typolink
		$piVars[ 'parameter' ]	= $id;

		// if news item, use it
		$newsVar				= 'tx_ttnews';
		$newsArray				= t3lib_div::_GP( $newsVar );

		if ( $newsArray )
		{
			$piVars[ 'additionalParams' ]	= "&tx_ttnews[tt_news]={$newsArray[ 'tt_news' ]}";
			$piVars[ 'useCacheHash' ]	= 1;
		}

		if ($tag) {
			return $this->site_url . $this->cObj->typolink( $tag, $piVars );
		} else {
			$piVars[ 'returnLast' ]	= 'url';
			return $this->site_url . $this->cObj->typolink( $tag, $piVars );
		}
	}

	/* evalDate($value)
	 *
	 *  Check if the value is a correct date in format yyyy-mm-dd
	*/
	function evalDate($value) {
		if( !$value) {  
			return false; 
		}
		$checkValue = trim($value);
		if( strlen($checkValue) == 8 ) {
			$checkValue = substr($checkValue,0,4).'-'.substr($checkValue,4,2).'-'.substr($checkValue,6,2) ;


		}
		list($year,$month,$day) = split('-', $checkValue, 3);
		if(is_numeric($year) && is_numeric($month) && is_numeric($day)) {
			return checkdate($month, $day, $year);
		} else {
			return false; 
		}
	}

	/**
	 * Check if the value is a valid month value.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param integer month value
	 * @return boolean, true if valid, false otherwise
	 */
	function evalMonth( $value )
	{
		if( !$value )
		{  
			return false; 
		}

		$value				= trim( $value );

		if( is_numeric( $value ) )
		{
			return checkdate( $value, 1, 2004 );
		}
		
		else
		{
			return false; 
		}
	}

	/**
	 * Check if the value is a valid year value.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param integer year value
	 * @return boolean, true if valid, false otherwise
	 */
	function evalYear( $value )
	{
		if( !$value )
		{  
			return false; 
		}

		$value				= trim( $value );

		if( is_numeric( $value ) )
		{
			return checkdate( 1, 1, $value );
		}
		
		else
		{
			return false; 
		}
	}

	/**
	 * Check if the value is a valid expiry value.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param string month/year value
	 * @return boolean, true if valid, false otherwise
	 */
	function evalExpiry( $value )
	{
		if( !$value )
		{  
			return false; 
		}

		$value				= trim( $value );
		$value				= explode( '/', $value );
		$month				= isset( $value[ 0 ] )
								? $value[ 0 ]
								: false;
		$year				= isset( $value[ 1 ] )
								? $value[ 1 ]
								: false;

		if( is_numeric( $month ) && is_numeric( $year) )
		{
			return checkdate( $month, 1, $year );
		}
		
		else
		{
			return false; 
		}
	}

	/**
	 * Check if the value is a valid credit card value.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param string credit card number
	 * @return boolean, true if valid, false otherwise
	 */
	function evalCreditcard( $value )
	{
		if( !$value )
		{  
			return false; 
		}

		$checkValue			= trim( $value );
		$cc					= new Cb_Validate_Credit_Card();

		$ccTypes			= explode( ','
								, $this->conf[ 'ccAllowedTypes' ]
							);
		$ccAllowedTypes		= array();

		foreach ( $ccTypes as $key => $constant )
		{
			$ccAllowedTypes[]	= constant( $constant );
		}

		$expiry				= explode( '/', $this->dataArr[ 'cc_expiry' ] );
		$result				= $cc->is_valid_card( $value
								, $expiry[ 0 ]
								, $expiry[ 1 ]
								, $ccAllowedTypes
							);
		$this->ccErrorText	= $cc->get_error_text( $result );

		if( ! $result )
		{
			return true;
		}
		
		else
		{
			return false; 
		}
	}

	/**
	 * Returns boolean whether ABA routing number or not.
	 *
	 * @link http://www.brainjar.com/js/validation/default.asp
	 * @link http://www.cflib.org/udf.cfm?ID=552
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param integer ABA routing number
	 * @return boolean
	 */
	function cbIsAba($routing)
	{
		// run through each digit and calculate the total.
		$n							= 0;

		$routingLength				= strlen($routing);

		// ABA routing numbers are always 9 digits long
		if ( preg_match('/[^0-9]/', $routing) || 9 != $routingLength )
		{
			return false;
		}

		// multiply the first digit by 3, the second by 7, the third by 1, the
		// fourth by 3, the fifth by 7, the sixth by 1, etc., and add them all up.

		for ( $i = 0; $i < $routingLength; $i += 3 ) 
		{
			$n						+= $routing[$i] * 3;
			$n						+= $routing[$i + 1] * 7;
			$n						+= $routing[$i + 2];
		}

		// If this sum is an integer multiple of 10 (e.g., 10, 20, 30, 40, 50,...)
		// then the number is valid, as far as the checksum is concerned.
		// (but not zero),
		if ( 0 != $n && 0 == ( $n % 10 ) )
		{
			return true;
		}

		else
		{
			return false;
		}
	}

	/**
	* Transforms incoming timestamps into dates
	*
	* @return parsedArray
	*/
	function parseIncomingTimestamps($origArr = array()) {

		$parsedArr = array();
		$parsedArr = $origArr;
		if (is_array($this->conf['parseFromDBValues.'])) {
			reset($this->conf['parseFromDBValues.']);
			while (list($theField, $theValue) = each($this->conf['parseFromDBValues.'])) {
				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);
				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd) {
						case 'date':
						if($origArr[$theField]) {
							$parsedArr[$theField] = date( 'Y-m-d', $origArr[$theField]);
						}
						if (!$parsedArr[$theField]) {
							unset($parsedArr[$theField]);
						}
						break;
						case 'adodb_date':
						if($origArr[$theField]) {
							$parsedArr[$theField] = $this->adodbTime->adodb_date( 'Y-m-d', $origArr[$theField]);
						}
						if (!$parsedArr[$theField]) {
							unset($parsedArr[$theField]);
						}
						break;
					}

				}
			}
		}
		return $parsedArr;
	}

	/**
	 * Convert array of array components into CSV.
	 *
	 * @param & array
	 * @return array
	 */
	function noArrayArray( & $array )
	{
		foreach ( $array as $key => $value )
		{
			if ( is_array( $value ) )
			{
				$array[ $key ]	= implode( '; ', $value );
			}
		}
	}

	/**
	* Transforms outgoing dates into timestamps
	*
	* @return parsedArray
	*/
	function parseOutgoingDates($origArr = array()) {

		$parsedArr = array();
		$parsedArr = $origArr;
		if (is_array($this->conf['parseToDBValues.'])) {
			reset($this->conf['parseToDBValues.']);
			while (list($theField, $theValue) = each($this->conf['parseToDBValues.'])) {
				$listOfCommands = t3lib_div::trimExplode(',', $theValue, 1);

				while (list(, $cmd) = each($listOfCommands)) {
					$cmdParts = split("\[|\]", $cmd); // Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd) {
						case 'date':
						if($origArr[$theField]) {
							if(strlen($origArr[$theField]) == 8) { 
								$parsedArr[$theField] = substr($origArr[$theField],0,4).'-'.substr($origArr[$theField],4,2).'-'.substr($origArr[$theField],6,2);
							} else {
								$parsedArr[$theField] = $origArr[$theField];
							}
							list($year,$month,$day) = split('-', $parsedArr[$theField], 3);
							$parsedArr[$theField] = mktime(0,0,0,$month,$day,$year);
						}
						break;
						case 'adodb_date':
						if($origArr[$theField]) {
							if(strlen($origArr[$theField]) == 8) { 
								$parsedArr[$theField] = substr($origArr[$theField],0,4).'-'.substr($origArr[$theField],4,2).'-'.substr($origArr[$theField],6,2);
							} else {
								$parsedArr[$theField] = $origArr[$theField];
							}
							list($year,$month,$day) = split('-', $parsedArr[$theField], 3);
							$parsedArr[$theField] = $this->adodbTime->adodb_mktime(0,0,0,$month,$day,$year);
						}
						break;
					}
				}
			}
		}
		return $parsedArr;
	}

	/**
	* Function imported from class.tslib_content.php
	*  unescape replaced by decodeURIComponent
	*
	* Returns a JavaScript <script> section with some function calls to JavaScript functions from "t3lib/jsfunc.updateform.js" (which is also included by setting a reference in $GLOBALS['TSFE']->additionalHeaderData['JSincludeFormupdate'])
	* The JavaScript codes simply transfers content into form fields of a form which is probably used for editing information by frontend users. Used by fe_adminLib.inc.
	*
	* @param       array           Data array which values to load into the form fields from $formName (only field names found in $fieldList)
	* @param       string          The form name
	* @param       string          A prefix for the data array
	* @param       string          The list of fields which are loaded
	* @return      string
	* @access private
	* @see user_feAdmin::displayCreateScreen()
	*/
	function getUpdateJS($dataArray, $formName, $arrPrefix, $fieldList) {
		$JSPart = '';
		$updateValues = t3lib_div::trimExplode(',', $fieldList);
		$mbstring_is_available = in_array('mbstring', get_loaded_extensions());
		while (list(, $fKey) = each($updateValues)) {
			$value = $dataArray[$fKey];
			if (is_array($value)) {

				reset($value);
				while (list(, $Nvalue) = each($value)) {
					if ($this->typoVersion >= 3006000 ) {
						$convValue = $GLOBALS['TSFE']->csConvObj->conv($Nvalue, $this->charset, 'utf-8');
					} elseif ($mbstring_is_available) {
						$convValue = mb_convert_encoding ( $Nvalue, 'utf-8', $this->charset);
					} elseif ($this->charset == 'iso-8859-1') {
						$convValue = utf8_encode($Nvalue);
					} else {
						$convValue = $Nvalue;   // giving up!
					}
					$JSPart .= "
						if (window.decodeURIComponent) { unesc = decodeURIComponent('".rawurlencode($convValue)."') } else { unesc = unescape('".rawurlencode($Nvalue)."') };
						updateForm('".$formName."','".$arrPrefix."[".$fKey."][]',unesc);";
				}
				 
			} else {
				if ($this->typoVersion >= 3006000 ) {
					$convValue = $GLOBALS['TSFE']->csConvObj->conv($value, $this->charset, 'utf-8');
				} elseif ($mbstring_is_available) {
					$convValue = mb_convert_encoding ( $value, 'utf-8', $this->charset);
				} elseif ($this->charset == 'iso-8859-1') {
					$convValue = utf8_encode($value);
				} else {
					$convValue = $value;  // giving up!
				}
				$JSPart .= "
					if (window.decodeURIComponent) { unesc = decodeURIComponent('".rawurlencode($convValue)."') } else { unesc = unescape('".rawurlencode($value)."') };
					updateForm('".$formName."','".$arrPrefix."[".$fKey."]',unesc);";
			}
		}
		$JSPart = '<script type="text/javascript">
			/*<![CDATA[*/ '.$JSPart.'
			/*]]>*/
			</script>
			';
		$GLOBALS['TSFE']->additionalHeaderData['JSincludeFormupdate'] = '<script type="text/javascript" src="'.$GLOBALS['TSFE']->absRefPrefix.'t3lib/jsfunc.updateform.js"></script>';
		return $JSPart;
	}
					
	/**
	 * Setup pseudo random username and password and stop periods for a
	 * temporary user.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @return void
	 */
	function createTempuser()
	{
		// check dataArr for current username, password, password_again
		if ( ! trim( $this->dataArr[ 'username' ] ) )
		{
			// microsecond difference
			$tempuser			= uniqid( 'visitor_' );
			$temppass			= uniqid( '' );

			$this->dataArr[ 'username' ]		=  $tempuser;

			// create password from unique id, 6 char
			// create unique username from email
			$this->dataArr[ 'password' ]		= $temppass;
			$this->dataArr[ 'password_again' ]	= $temppass;
		}

		if ( ! trim( $this->dataArr[ 'endtime' ] ) )
		{
			// set endtime about x days later per
			// $this->conf[ 'tempuserTimelimit' ]
			$now				= time();
			$later				= $now
									+ ( $this->conf[ 'tempuserTimelimit' ]
										* 24 * 60 * 60 
									);

			$this->dataArr[ 'endtime' ]	= $later;
		}
	}

	/**
	 * Remove credit card components from array
	 *
	 * @author Michael Cannon, michael@peimic.com
	 *
	 * @param array data array
	 * @return array 
	 */
	function removeCreditcard( $dataArr )
	{
		// Don't store credit card information as it's not encrypted or the like
		$update				= "
			UPDATE fe_users
			SET cc_number = ''
				, cc_type = ''
				, cc_name = ''
				, cc_expiry = ''
			WHERE uid = {$this->dataArr[ 'uid' ]}
		";

		$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $update);
		echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
	}

	/**
	 * Sets start and end time based upon create/edit status and cc_number
	 *
	 * @return void
	 */
	function setStartEndTime()
	{
		// bail if note create/edit cmd
		if ( 'create' != $this->cmd && 'edit' != $this->cmd )
		{
			return;
		}

		// set default start and end time
		$now						= time();

		if ( ! trim( $this->dataArr[ 'starttime' ] ) )
		{
			$this->dataArr[ 'starttime' ]	= $now;
		}

		$start						= ( 'create' == $this->cmd )
										? $now
										: $this->dataArr[ 'starttime' ];
		$end						= ( $this->dataArr[ 'endtime' ] )
										? $this->dataArr[ 'endtime' ]
										: 0;
        
										
		// set a flag for complimentary professional group.
		$complimentaryProfessionalGroupID = 4; //from the fe_groups table.
		$isComplimentaryProfessionalUserGroup = $this->dataArr['usergroup'] 
									== $complimentaryProfessionalGroupID; 
		// states
		// prof below includes complimentary professional group - js 
			// create no - new start, no end
			// create prof - new start, new end
			// edit no - keep start, no end
			// edit prof - new start, extend end
		// check that there's a payment_method
		if (( preg_match( '#\b' .$this->conf[ 'paidUsergroup' ] . '\b#'
				, $this->dataArr[ 'usergroup' ] )
			&& '' != $this->dataArr[ 'payment_method' ]
		) || $isComplimentaryProfessionalUserGroup )
		{
			$year					= ( 365 * 24 * 60 * 60 );
			$end					= $now + $year;
			$start					= $now;
		}

		$this->dataArr[ 'starttime' ]	= $start;
		$this->dataArr[ 'endtime' ]		= $end;
	}

	/**
	 * Translate some dataArr components into English for clarity.
	 *
	 * @param array data key:value pairs
	 * @param boolean true decode, false encode
	 * @return array
	 */
	function dataArrTranslate( $origArr, $decode = true )
	{
		$dataArr				= $origArr[ 0 ];

		$yesNoArray				= array(
									'0'		=> 'No'
									, '1'	=> 'Yes'
								);

		// create assoc array of items to translate
		$translator				= array(
			'usergroup'				=> array(
				'1'						=> 'Member'
				, '2'					=> 'Professional Member'
				, '4'					=> 'Complimentary'
				, '5'					=> 'Visitor, White Paper'
				, '6'					=> 'Visitor, Round Table'
				, '7'					=> 'Visitor, Presentation'
				, '8'					=> 'Editor Preview'
				, '9'					=> 'BPM - Chicago 2005'
				, '10'					=> 'BPM - San Francisco 2005'
				, '11'					=> 'BPM - Washington 2005'
			)
			, 'payment_method'		=> array(
				'credit_card'			=> 'Credit card'
				, 'phone'				=> 'Please call'
			)
			, 'module_sys_dmail_html'	=> $yesNoArray
			, 'tx_bpmprofile_newsletter1' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter2' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter3' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter4' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter5' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter6' 	=> $yesNoArray
			, 'paid'				=> $yesNoArray
			, 'processed'			=> $yesNoArray
		);

		$translatorKeys			= array_values( array_keys( $translator ) );

		// decode
		// key to value
		// a [ 1 ] => yes

		// encode
		// value to key
		// a [ yes ] => 1

		foreach ( $translatorKeys as $key )
		{
			$values				= $translator[ $key ];
			$origValue			= $dataArr[ $key ];

			if ( ! $decode )
			{
				// invert $values
				$values			= array_flip( $values );
			}

			switch( $key )
			{
				case 'usergroup':
					// convert 2,9
					// to Professional, BPM - Chicago

					// translate the newsletter code 1 to some, 2 thing, etc.
					foreach ( $values as $lkey => $value )
					{
						$origValue	= preg_replace( "#\b$lkey\b#"
										, $value
										, $origValue
									);
					}

					// save as string instead of original array
					$dataArr[ $key ]	= $origValue;
					break;

				default:
					$dataArr[ $key ]	= $values[ $origValue ];
					break;
			}
		}

		$origArr[ 0 ]			= $dataArr;

		return $origArr;
	}


	/**
	 * Upgrade member access if necessary.
	 * Upgrades access if there is an entry matching the email in the fe_users
	 * row with this $uid in the tx_memberaccess_acl table.
	 * Does this only if the conf item memberAccessProcessing is set. 
	 * @see class tx_memberaccess_modfunc1 
	 * @author Jaspreet Singh
	 * @param the uid of the row in fe_users for which to check/upgrade access
	 */
	 function upgradeMemberAccess( $uid )
	 {
		 if ($this->debug) echo "upgradeMemberAccess( $uid )";
		 
		 if (1 == $this->memberAccessProcessing) {
			 
			 if ($this->debug) echo "Upgrading access";
			 
			 $memberaccess = new tx_memberaccess_modfunc1; 
			 $memberaccess->db = $GLOBALS['TYPO3_DB'];
			 if ($this->debug) {
				 $memberaccess->debug = true;
				 
			 }
			 $memberaccess->updateFeUserGroups($uid);
			 
		 }
	 }

	/**
	 * Unset the expired flag field in table fe_users if necessary.
	 * This is done when a member signs up for a renewal on possibly lapsed
	 * membership.  If  a professional membership lapsed, the tx_memberexpiry_expired
	 * field will have been set to 1 (in member_expiry extension),
	 * and we set it to 0 here when member renews.
	 * @author Jaspreet Singh
	 * @param the uid of the field for which to unset the flag.
	 */
	function unsetExpiredField( $uid )
	{
		// set a flag for complimentary professional group.
		$complimentaryProfessionalGroupID = 4; //from the fe_groups table.
		$isComplimentaryProfessionalUserGroup = $this->dataArr['usergroup'] 
									== $complimentaryProfessionalGroupID; 

		//if memberexpiry processing is set on
		//and if the member is renewing a professional or complimentary
		//professional group membership,
		if ((1==$this->memberExpiryProcessing)
			&& (isset($uid))
			&& (( preg_match( '#\b' .$this->conf[ 'paidUsergroup' ] . '\b#'
					, $this->dataArr[ 'usergroup' ] )
				&& '' != $this->dataArr[ 'payment_method' ]
			) || $isComplimentaryProfessionalUserGroup )
			) {

			$update				= "
				UPDATE fe_users
				SET tx_memberexpiry_expired = 0
				WHERE uid = $uid
			";
			$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $update);
			if ($this->debug) { 
				echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
				echo "unsetExpiredField( $uid )";
				echo $update;
				echo '<br>';
			}
		}
		
	}
		
	/**
	 * Log registration errors in table tx_memberaccess_registrationerrors.
	 * Does this only if the conf item registrationErrorLogging is set.
	 * No parameters - gets parameters from class variables. 
	 * @author Jaspreet Singh
	 * @return void
	 */
	function logRegistrationErrors()
	{

		/* Query looks like this:
		INSERT INTO tx_memberaccess_registrationerrors( userid, errortime, email, errors )
		VALUES (6464, 1121928502, 'asf@qwerty.com', 'Email, First Name, Last Name, Question1' );
		*/
		if ($this->debug) echo "logRegistrationErrors()";
		
		//if logging is set on
		if (1==$this->registrationErrorLogging){
			if ($this->debug)  "logging RegistrationErrors";
			
			$uid = is_null($this->dataArr[uid]) || ''==$this->dataArr[uid] ? '0' : $this->dataArr[uid] ;
			$email = is_null($this->dataArr[email]) ? '' : $this->dataArr[email] ;
			$errortime = time();
			$errors = preg_replace( "/,/", ', ', $this->failure); //add spaces between the commas

			$query				= "
				INSERT INTO tx_memberaccess_registrationerrors( userid, errortime, email, errors )
				VALUES ('$uid', $errortime, '$email', '$errors' )
			";
			
			if ($this->debug) echo $query;
			
			$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
			echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
		}

	}

	/**
	Changes the usergroup being saved so as not to override existing usergroups.
	The problem: Suppose a user is changing his user group (say, from free to 
	paid professional).  When in case 'edit' in save(), the new usergroup
	(which is in $this->dataArr['usergroup']) will be 2.  If the user is a member
	of any other groups in addition to free, which is 1, his existing groups will
	get wiped out.
	The solution: Append the existing groups to the new group string.  However, 
	we ignore the free/professional/complimentary groups because those are the 
	groups that we want to change, not ones that we want to keep.
	@author Jaspreet Singh
	@param None. Gets its parameters from the class variable $this->dataArr['usergroup']
	@return void.  Saves its changes in the class variable  $this->dataArr['usergroup']
	*/
	function rectifyUserGroup()
	{
		$currentUserGroupString = $this->getCurrentUserGroupString();
		
		$memberaccess = new tx_memberaccess_modfunc1; 
		$memberaccess->db = $GLOBALS['TYPO3_DB'];
		if ($this->debug) {
			$memberaccess->debug = true;
		}
		//Ignore the free/professional/complimentary groups because those are the 
		//groups that we want to change, not ones that we want to keep.
		$ignoredLevels = '1,2,3,4';

		$desiredAccessLevel = $this->dataArr['usergroup']; 
		$newUserGroupString = $memberaccess->appendAccessLevel($currentUserGroupString, $desiredAccessLevel, $ignoredLevels);

		if($this->debug) {
			echo "\$currentUserGroupString = $currentUserGroupString \n<br>";
			echo "\$newUserGroupString = $newUserGroupString \n<br>";
		}
		
		$this->dataArr['usergroup'] = $newUserGroupString;

	}

	/**
	(Test) Changes the usergroup being saved so as not to override existing usergroups.
	The problem: Suppose a user is changing his user group (say, from free to 
	paid professional).  When in case 'edit' in save(), the new usergroup
	(which is in $this->dataArr['usergroup']) will be 2.  If the user is a member
	of any other groups in addition to free, which is 1, his existing groups will
	get wiped out.
	The solution: Append the existing groups to the new group string.
	@author Jaspreet Singh
	@param None. Gets its parameters from the class variable $this->dataArr['usergroup']
	@return void.  Saves its changes in the class variable  $this->dataArr['usergroup']
	*/
	function testRectifyUserGroup()
	{
		$currentUserGroupString = $this->getCurrentUserGroupString();
		
		//function appendAccessLevel($currentAccessLevel, $desiredAccessLevel)
		$memberaccess = new tx_memberaccess_modfunc1; 
		$memberaccess->db = $GLOBALS['TYPO3_DB'];
		if ($this->debug) {
			$memberaccess->debug = true;
		}
		$desiredAccessLevel = $this->dataArr['usergroup']; 
		$newUserGroupString = $memberaccess->appendAccessLevel($currentUserGroupString, $desiredAccessLevel);

		if($this->debug) {
			echo "\$currentUserGroupString = $currentUserGroupString \n<br>";
			echo "\$newUserGroupString = $newUserGroupString \n<br>";
		}
		
		$ignoredLevels = '1,2,3,4';
		$newUserGroupString = $memberaccess->appendAccessLevel('1,9,10', $desiredAccessLevel, $ignoredLevels);

		if($this->debug) {
			echo "\$currentUserGroupString = $currentUserGroupString \n<br>";
			echo "\$newUserGroupString = $newUserGroupString \n<br>";
		}
	}

	/**
	Returns the usergroup string from the database of the record associated with
	the current uid.
	@author Jaspreet Singh
	@param implicit Gets parameter from the uid of the current record from
		$this->dataArr['uid']
	@return string. The current usergroup string.  Returns null if error/not found.
	*/
	function getCurrentUserGroupString()
	{
		if ($this->debug) { echo "getCurrentUserGroupString();\n<br>"; }
		
		$uid = $this->dataArr['uid'];
		$usergroup = null;
		
		if (isset($uid) && $uid!='') {
			$columns = 'usergroup';
			$from = 'fe_users';
			$where = "uid = $uid";
			$rows = $this->db->exec_SELECTgetRows($columns, $from, $where );

			if (!is_array($rows)) {
				if ($this->debug) {
					echo ' $rows not an array';
				}
				$rows=array();
			}
			
			$usergroup = $rows[0]['usergroup'];
		}
		
		return $usergroup;
	}



}
 
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register/pi1/class.tx_srfeuserregister_pi1.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register/pi1/class.tx_srfeuserregister_pi1.php"]);
}
?>
