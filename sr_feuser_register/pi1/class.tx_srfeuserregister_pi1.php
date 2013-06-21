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
 * @version $Id: class.tx_srfeuserregister_pi1.php,v 1.1.1.1 2010/04/15 10:04:04 peimic.comprock Exp $
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

// For integrating survey
require_once(t3lib_extMgm::extPath('sr_feuser_register_survey').'class.sr_feuser_register_survey.php');

//js for Linkpoint Credit Card API
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/LinkpointCreditCard.class.php');

//js for logging/debugging purposes
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/DebugLogger.class.php');

//js for security question functions
require_once(t3lib_extMgm::extPath('security_question').'pi1/SecurityQuestion.class.php');
require_once(t3lib_extMgm::extPath('security_question').'pi1/class.tx_securityquestion_pi1.php');

//js useful for HTML table output
require_once( CB_COGS_DIR . 'cb_html.php' );

// MLC 20081202 recaptcha
require_once(t3lib_extMgm::extPath('jm_recaptcha')."class.tx_jmrecaptcha.php");

//js for mail check and other functions
//this is just for reference, as cb_validation is currently being included from localconf.php
//require_once( CB_COGS_DIR . 'cb_validation.php' );

class tx_srfeuserregister_pi1 extends tslib_pibase {
	// MLC 20081202 recaptcha
	var $recaptcha				= null;
	var $recaptchaContent		= '';
	var $p						= null;

	var $cObj;
	// The backReference to the mother cObj object set at call time
	var $conf = array();
	var $site_url = '';
	var $theTable = 'fe_users';
	var $TCA = array();

	var $feUserData = array();

	/**
	 * Website (FE) user registration details
	 *
	 * This array holds the registration form data filled
	 * and submitted by user during registration process.
	 *
	 * @access private
	 * @var array
	 */
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
	var $failure = 0; // is set if data did not have the required fields set.
	var $error = '';
	var $saved = 0; // is set if data is saved
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
	// List of fields from fe_admin_fieldList
	var $fieldList;
	// List of required fields
	var $requiredArr;
	// list of formFields
	var $formFields;
	var $adminFieldList = 'name,disable,usergroup';
	var $fileFunc = ''; // Set to a basic_filefunc object for file uploads
	// MLC do preAuth
	var $doCreditcardPreauth	= false;
	// MLC ccExpiryValid
	var $ccExpiryValid			= false;
	// MLC credit card field name in dataArr
	var $ccField				= '';
	/** The transaction ID returned by the credit card gateway.  */ //js
    var $ccFinalOrderID;

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

	/**
	 * Object to sr_feuser_register_survey class.
	 * This object acts as a layer between sr_feuser_register,
	 * and ms_survey extension.
	 *
	 * @access private
	 * @var class
	 */
	var $survey;

	/**
	 * Flag to know whether to display survey with registration
	 * form or not.
	 *
	 * @access private
	 * @var boolean
	 */
	var $displaySurvey;

	// MLC tried to consolidate usergroup settings
	var $originalUsergroup		= '';
	var $corporateGroup 		= '';
	var $professionalGroup 		= '';
	var $professionalGuestGroup 	= '';
	var $complimentaryGroup 			= '';
	var $visitorGroups					= '';
	var $visitorGroupsArray			= array();
	var $corporateMembershipCharge		= '';
	var $professionalMembershipCharge	= '';
	var $corporateMembershipProductID	= '';
	var $professionalMembershipProductID	= '';
	var $redemptionPercentage	= 0;
	var $redemptionAmount		= 0;
	// MLC usergroup phone adjustmnet
	var $usergroupProPhoneAdj	= false;

	// MLC explode the usergroup array once to check via inarray versus multiple
	// preg_match and wondering which boundary to use
	var $usergroupArray			= array();

	// MLC ccInitialOrderID text holder
	var $ccInitialOrderID		= 'ccInitialOrderID';

	// MLC endtime set text holder
	var $isEndtimeSet			= 'isEndtimeSet';

	/**global database pointer*/
	var $db;

	/**Whether logging is on or off. js*/
	var $loggingOn				= false;

	/**An instance of DebugLogger. js*/
	var $logger					= null;

	// MLC countryChange idier
	var $countryChange 			= false;

	function initId() {
		$this->prefixId = 'tx_srfeuserregister_pi1';  // Same as class name
		$this->scriptRelPath = 'pi1/class.tx_srfeuserregister_pi1.php'; // Path to this script relative to the extension dir.
		$this->extKey = 'sr_feuser_register';  // The extension key.

		$this->theTable = 'fe_users';
		$this->adminFieldList = 'name,disable,usergroup';

		// Code for initialization of survey integration BEGINS
		$this->survey = t3lib_div::makeInstance('sr_feuser_register_survey');
		$this->survey->setTemplateFile( PATH_site.$GLOBALS['TSFE']->tmpl->getFileName( $this->conf['templateFile']));
		$this->displaySurvey = ( intval( $this->cObj->data['tx_srfeuserregistersurvey_display_survey']) > 0)
							   ? true
							   : false;

		$tmp = t3lib_div::_GP('FE');
		$this->survey->setUserSurveyResponse( $tmp['fe_users']['tx_mssurvey_pi1']);
		$this->survey->setSurveyType( $tmp['fe_users']['surveyType']);
		// Code for initialization of survey integration ENDS

		// MLC 20081202 recaptcha
		$this->recaptcha = new tx_jmrecaptcha();
		$this->p = t3lib_div::GPvar($this->prefixId);
	}

	function main($content, $conf) {
			// plugin initialization
		$this->conf = $conf;
		$this->initId();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;

		$this->db                = $GLOBALS['TYPO3_DB'];

		// Disable caching
		$this->pi_setPiVarDefaults();
		$this->site_url = t3lib_div::getIndpEnv('TYPO3_SITE_URL');

        //turn on debug information output if required (js)
        $this->setDebugStatus();

        if ( $this->debug )
        {
            echo '/Start $conf/';
			cbPrint2( '$conf', $conf );
			echo ' /id '. $GLOBALS['TSFE']->id;
			echo '/End $conf/';
            echo '/Start $GLOBALS["TSFE"]-> fe_user/';
			cbPrint2( '$GLOBALS["TSFE"]->fe_user', $GLOBALS['TSFE']->fe_user );
			echo '/End $GLOBALS["TSFE"]->fe_user/';
			//phpinfo();
        }

		// try to create a unique id for each logged in user
		$feUid					= ( isset(
									$GLOBALS["TSFE"]->fe_user->user[ 'uid' ] ) )
									? $GLOBALS["TSFE"]->fe_user->user[ 'uid' ]
									: '';

		// MLC need someway to create consistent independent session incase of
		// multiple users on same computer after a login
		// $this->ccInitialOrderID	.= $feUid;
		// $this->isEndtimeSet		.= $feUid;

		$this->corporateGroup 			= $this->conf[ 'corporateGroup' ];
		$this->professionalGroup 		= $this->conf[ 'professionalGroup' ];
		$this->professionalGuestGroup 		= $this->conf[ 'professionalGuestGroup' ];
		$this->complimentaryGroup 				= $this->conf[ 'complimentaryGroup' ];
		$this->visitorGroups						= $this->conf[ 'visitorGroups' ];
		$this->visitorGroupsArray					= explode( ',', $this->visitorGroups );
		$this->corporateMembershipCharge		= $this->conf[ 'corporateMembershipCharge' ];
		$this->professionalMembershipCharge		= $this->conf[ 'professionalMembershipCharge' ];
		$this->corporateMembershipProductID		= $this->conf[ 'corporateMembershipProductID' ]; //8;  //hardcode
		$this->professionalMembershipProductID	= $this->conf[ 'professionalMembershipProductID' ]; //13; //hardcode

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

		$this->pidRecord->sys_language_uid = ( trim( $GLOBALS['TSFE']->config['config']['sys_language_uid']))
		                                     ? trim($GLOBALS['TSFE']->config['config']['sys_language_uid'])
		                                     : 0;

		$this->thePid = intval($this->conf['pid'])
		                ? intval($this->conf['pid'])
		                : $GLOBALS['TSFE']->id;

		$row = $this->pidRecord->getPage($this->thePid);
		$this->thePidTitle = $row['title'];

		$this->registerPID = intval($this->conf['registerPID'])
		                     ? intval($this->conf['registerPID'])
		                     : $GLOBALS['TSFE']->id;

		$this->editPID = intval($this->conf['editPID'])
		                 ? intval($this->conf['editPID'])
		                 : $GLOBALS['TSFE']->id;

		$this->confirmPID = intval($this->conf['confirmPID'])
		                    ? intval($this->conf['confirmPID'])
		                    : $this->registerPID;

		$this->confirmType = intval($this->conf['confirmType'])
		                     ? intval($this->conf['confirmType'])
		                     : $GLOBALS['TSFE']->type;

		if ($this->conf['confirmType'] == 0 ) {
			$this->confirmType = 0;
		};

		$this->loginPID = intval($this->conf['loginPID'])
		                  ? intval($this->conf['loginPID'])
		                  : $GLOBALS['TSFE']->id;

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
		if ( isset( $this->dataArr['captcha'] ) ) {
			$this->dataArr['captcha'] = 1;
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

		$this->cmd = $this->feUserData['cmd']
					 ? $this->feUserData['cmd']
					 : strtolower( $this->cObj->data['select_key']);
		$this->cmd = $this->cmd
					 ? $this->cmd
					 : strtolower( $this->conf['defaultCODE']) ;

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

				// js require redemption code to be filled in if paying by such
				if ( 'paymentMethod_RedemptionCode' == $this->dataArr[ 'payment_method' ] )
				{
					$this->requiredArr[]	= 'redemptionCode';
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

				// MLC create psuedo username and pasword
				// form has been submitted
				if ( 'tempuser' == $this->cmd )
				{
					$this->createTempuser();
				}

				// MLC hack for looking up usergroups
				if ( '' != $this->dataArr[ 'usergroup' ] )
				{
					$this->originalUsergroup	= $this->dataArr[ 'usergroup' ];
					$usergroupArray				= explode( ',', $this->dataArr[
													'usergroup' ] );
					$ugNew		= '';

					switch( true )
					{
						// corporate
						case in_array( $this->corporateGroup
							, $usergroupArray ):
							$ugNew	= $this->corporateGroup
									. ','
									. $this->professionalGroup;
							break;

						// professional
						case in_array( $this->professionalGroup
							, $usergroupArray ):
							//if the user is paying by phone, give him complimentary for now
							//BSG will set user to professional manually upon getting his payment info
							if ( 'phone' == $this->dataArr[ 'payment_method' ] ) {
								$ugNew = $this->complimentaryGroup;
								$this->usergroupProPhoneAdj	= true;
							}
							//normal case: user requested professional, paid by credit card, and so,
							//gets professional membership
							else {
								$ugNew	= $this->professionalGroup;
							}
							break;

						// professional guest
						case in_array( $this->professionalGuestGroup
							, $usergroupArray ):
							$ugNew	= $this->professionalGuestGroup;
							break;

						// complimentary
						case in_array( $this->complimentaryGroup
							, $usergroupArray ):
							$ugNew	= $this->complimentaryGroup;
							break;

						// don't set default or else error reporting will be
						// wrong
					}

					$this->dataArr[ 'usergroup' ]	= $ugNew;
				}

				// MLC modifiy the usergroup with an override
				if ( '' != $this->conf[ 'userGroupUponRegistration' ] )
				{
					$this->dataArr[ 'usergroup' ]	=
									$this->conf[ 'userGroupUponRegistration' ];
				}

				// MLC conference usergroup append
				if ( '' != $this->conf[ 'conferenceUsergroupAppend' ] )
				{
					$this->dataArr[ 'usergroup' ]	.= ','
								. $this->conf[ 'conferenceUsergroupAppend' ];
				}

				// Add domain user group (BPM/SOA/..) to user access group (Free
				// Member/Professional Member/...)
				if ( isset( $this->conf[ 'domainUserGroup' ] )
					&& $this->conf[ 'domainUserGroup' ]
					&& '' != $this->dataArr[ 'usergroup' ]
					&& ! preg_match(
						'#\b' . $this->conf[ 'domainUserGroup' ] . '\b#'
						, $this->dataArr[ 'usergroup' ]
					)
				)
				{
					$this->dataArr[ 'usergroup' ]	.= ','
								. $this->conf[ 'domainUserGroup' ];
				}

				$this->usergroupArray	= explode( ','
											, $this->dataArr[ 'usergroup' ]
										);

				if ($this->debug) {
					echo "\n Is there a failure immediately before call to evalValues? ";
					var_dump( $this->failure );
				}

				// a button was clicked on
				$this->evalValues();

				if ($this->debug) {
					echo "\n Is there a failure immediately before call to setStartEndtime? ";
					var_dump( $this->failure );
				}
				// MLC set start/end time based upon create/edit and group
				// don't fire unless user submission is solid otherwsie endtime
				// keeps growing
				if ( ( 'create' == $this->cmd || 'edit' == $this->cmd  )
					&& ! $this->failure
//					&& 'true' != $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->isEndtimeSet)
				)
				{
					$this->setStartEndTime();
					$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->isEndtimeSet
						, 'true' );
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

			} else {
				//this is either a country change submitted through the onchange event! or a file deletion already processed by the parsing function
				// we are going to redisplay
				$this->countryChange 	= true;
				$this->evalValues();
				$this->failure = 1;
			}
			if (!$this->failure && !$this->feUserData['preview'] && !$this->feUserData['doNotSave'] ) {
				if ($this->debug) {
					$this->chargeCreditCard();
					$this->save();
				} else {
					$this->chargeCreditCard();
					$this->save();
                }
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

			if ($this->debug)  echo "Failure:";
			//log the error - js
			$this->logGeneralRegistrationErrors();

		} // No preview flag if a evaluation failure has occured
		$this->previewLabel = ($this->feUserData['preview'])
			? '_PREVIEW'
			: ''; // Setting preview template label suffix.

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
					// <td@krendls>
					if($this->conf['rssPartnerPid'] == $GLOBALS['TSFE']->page['uid'])
					{
					    $this->rssSendMail($this->dataArr['uid']);
					    $rURL = $this->pi_getPageLink($this->conf['rssPartnerThankyouPid']);
                        header('Location: '.t3lib_div::locationHeaderUrl($rURL));
                        exit;
					}
                    $key = 'CREATE'.$this->savedSuffix;
					// </td@krendls>
				}
				break;
			}

			// MLC rerun to capture revised thank you message
			$this->usergroupArray	= explode( ','
										, $this->currentArr[ 'usergroup' ] );
			$this->markerArray = $this->addURLMarkers($this->markerArray);

			// Display confirmation message
			$templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_'.$key.'###');
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $this->currentArr);
			$markerArray = $this->addStaticInfoMarkers($markerArray, $this->currentArr);
			$markerArray = $this->addLabelMarkers($markerArray, $this->currentArr);
			$content = $this->cObj->substituteMarkerArray($templateCode, $markerArray);

			// MLC send mail prior to redirect or displaying form
			// Send email message(s)
			$this->compileMail($key, array($this->currentArr), $this->currentArr[$this->conf['email.']['field']], $this->conf['setfixed.']);

			// MLC delete the cc info from database as needed
			if ( $this->dataArr[ 'cc_number' ]
				&& ! $this->conf[ 'enableCreditcardSave' ]
			)
			{
				$this->removeCreditcard( $this->dataArr );
			}

			// MLC redirect saved tempuser
			if ( $key == 'CREATE_SAVED'
				&& (
					( $this->cmd == 'tempuser'
						&& $this->conf[ 'enableAutoLoginTempuser' ]
					)
					|| $this->conf[ 'enableAutoLogin' ]
				)
			)
			{
				$loginVars = array();
				$loginVars['pid'] = $this->thePid;
				$loginVars['logintype'] = 'login';
				$loginVars['user'] = $this->dataArr['username'];
				$loginVars['pass'] = $this->dataArr['password'];

				$loginVars['redirect_url'] = htmlspecialchars( trim(
					$this->dataArr[ 'referrer_uri' ] )
				);

				// MLC 20090223 check for round table registration
				if ( $this->cmd == 'tempuser'
						&& $this->conf[ 'enableAutoLoginTempuser' ]
						&& preg_match( '#\b6\b#', $this->dataArr[ 'usergroup' ] )
				) {
					// append rt registration clause
					$loginVars['redirect_url'] = htmlspecialchars( trim(
						str_replace('.html', '/vrtr/1.html', $this->dataArr[ 'referrer_uri' ])
				 	) );
				}

				if ( $this->conf[ 'autoLoginRedirect_url' ] )
				{
					$loginVars['redirect_url'] = htmlspecialchars( trim(
						$this->conf[ 'autoLoginRedirect_url' ] )
					);
				}

				// MLC review loginVars
				if ( $this->debug )
				{
					cbPrint2( 'loginVars', $loginVars );

					cbPrint2( 'location', 'Location: '.t3lib_div::locationHeaderUrl($this->site_url.$this->cObj->getTypoLink_URL($this->loginPID.','.$GLOBALS['TSFE']->type, $loginVars)));
					exit;
				}
				header('Location: '.t3lib_div::locationHeaderUrl($this->site_url.$this->cObj->getTypoLink_URL($this->loginPID.','.$GLOBALS['TSFE']->type, $loginVars)));
				exit;
			} elseif ( $key == 'EDIT_SAVED'
				&& $this->conf[ 'editsavedRedirect_url' ] ) {
				header('Location: '.$this->conf[ 'editsavedRedirect_url' ]);
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
				case 'thankyou':
				$content = $this->displayThankyouScreen();
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
				    // <td@krendls>
				    {
    					if($this->conf['rssPartnerPid'] == $GLOBALS['TSFE']->page['uid'])
    					{
    				        $feuser_id = $GLOBALS['TSFE']->fe_user->user['uid'];
    				        if($feuser_id == NULL)
    				        {
                				$content = $this->displayCreateScreen($this->cmd);
    				        }else{
    				            $usergroups = explode(',',$GLOBALS['TSFE']->fe_user->user['usergroup']);
    				            if(in_array($this->conf['rssPartnerUsergroup'], $usergroups))
    				            {
    				                //	User already in "BSG PARTNER" usergrpup
    				            }elseif(in_array($this->conf['rssPartnerWaitingUsergroup'], $usergroups)){
                    				$content = $this->displayRssThankyouScreen();
    				            }else{
    				                if(isset($this->feUserData['submit']))
    				                {
    				                    $content = $this->rssApproveCheck();
    				                }else{
                    				    $content = $this->displayRssAgreementScreen();
    				                }
    				            }
    				        }
    					}else{
    						$content = $this->displayCreateScreen($this->cmd);
    					}
				    }break;
				    // </td@krendls>
				default:
				if ($this->theTable == 'fe_users' && $GLOBALS['TSFE']->loginUser) {
					$content = $this->displayCreateScreen($this->cmd);
				} else {
					$content = $this->displayEditScreen();
				}
				break;
			}
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
        //echo  '$ _SERVER[ REMOTE_ADDR ]' . $_SERVER[ 'REMOTE_ADDR' ];
		if ( true && ('59.94.211.12' == $_SERVER[ 'REMOTE_ADDR' ]
			|| '70.84.110.68' == $_SERVER[ 'REMOTE_ADDR' ]
//			|| '71.255.125.178' == $_SERVER[ 'REMOTE_ADDR' ]
			)
		) {
            $this->debug = true;
            //echo 'Remote:59.94.208.61';
            //echo 'Join Now.';
            //echo $_SERVER[ 'REMOTE_ADDR' ];
			$today = mktime(0, 1, 0, date("m")  , date("d"), date("Y"));
			echo '$today ' . $today; //debug
			$dec4 = mktime(0, 1, 0, 12, 4, date("Y"));
			echo '$dec4 ' . $dec4; //debug
        }
		if (true) {
			$this->loggingOn 	= true;
			$this->logger		= new DebugLogger();
			if ($this->debug) {
				$this->logger->debug = true;
			}
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

		if ($this->debug) {
			echo "\n<hr> function evalValues() {";
			echo "\$tempArr before switch "; print_r($tempArr);
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

						// MLC ensure active records are really active
						case 'uniqueLocal':
						if ( trim($this->dataArr[$theField])
							&& $DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable
								, $theField
								, $this->dataArr[$theField]
								, "AND pid IN ($recordTestPid)"
									. ' LIMIT 1'
							)
						)
						{
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
						case 'emailValidMx':
						if ($this->debug) {echo "case emailValidMx";}
						// MLC don't check MX
						if (trim($this->dataArr[$theField]) && !cbIsEmail($this->dataArr[$theField], false)) {
							if ($this->debug) {echo "/case emailValidMx inner "
								. $this->dataArr[$theField]; $isEmail = cbIsEmail($this->dataArr[$theField], false); var_dump($isEmail); echo '/';}
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, "Though your email address might be valid, we're unable to  verify it at your mail server. As such, please try another.");
						}
						break;
						case 'required':
						if (!trim($this->dataArr[$theField])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter a value!');
						}
						break;
						case 'missing':
						if (!trim($this->dataArr[$theField])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, 'missing', 'You must enter a value!');
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
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please enter a current expiry MM/YYYY.');
						}
						else
						{
							$this->ccExpiryValid	= true;
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
							$ccCleanValue				= preg_replace( '#[^0-9]#', '', $this->dataArr[$theField] );
							if ($this->debug) {

								$ecoResult = $this->evalCreditcard($ccCleanValue);
								echo " \$this->evalCreditcard($ccCleanValue):  "; var_dump($ecoResult);
							}
							if (!$this->evalCreditcard($ccCleanValue) )
							{
								$tempArr[] = $theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, $this->ccErrorText );
							}
							else
							{
								// MLC since credit card appears valid mark it
								// for needing further processing
								$this->ccField				= $theField;
								$this->doCreditcardPreauth	= true;
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
								'#\b' . $theField . '\b#'
								, $this->conf[ 'create.' ][ 'fields' ] )
							&& $this->dataArr[$theField] == 'PCO'
						)
						{
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please choose one.' );
						}
						break;

						case 'redemptionCode':
						//if (trim($this->dataArr[$theField]) && !$this->evalRedemptionCode($this->dataArr[$theField])) {
						if (!$this->evalRedemptionCode($this->dataArr[$theField])) {
							$tempArr[] = $theField;
							$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'Please check or remove your redemption code.');
							if ($this->debug) {
								echo ' redemptionCode case: error ';
							}
						}
						break;

                        // MLC 20080116 easy anti-spam 
                        case 'noSpam':
                        $spamSign   = "#<a|href#";
                        if ($this->dataArr[$theField]
                            && preg_match( $spamSign
                                , $this->dataArr[$theField]
                            )
                        )
                        {
                            $tempArr[] = $theField;
                            $this->failureMsg[$theField][] =
                            $this->getFailure($theField, $theCmd
                                , 'Please double check your entry.'
                            );
                        }
                        break;

						// MLC 20081202 recaptcha anti-spam
                        case 'captcha':
                        if ($this->dataArr[$theField]) {
							$status = $this->recaptcha->validateReCaptcha();
							if (! $status['verified']) {
                            	$tempArr[] = $theField;
								$this->failureMsg[$theField][] =
								$this->getFailure($theField
									, $theCmd
									, 'Please reenter your captcha response'
								);
								$this->recaptchaContent = $this->recaptcha->getReCaptcha($status['error']);
							} else {
								$this->recaptchaContent = $this->recaptcha->getReCaptcha();
							}
                        }
                        break;
					}
				}
				$this->markerArray['###EVAL_ERROR_FIELD_'.$theField.'###'] = is_array($this->failureMsg[$theField])?implode($this->failureMsg[$theField], '<br />'):
				'<!--no error-->';
			}
		}

		if ($this->debug) {
			echo "\n ";
			echo "\$tempArr after switch "; print_r($tempArr);
		}

		//check for survey error moved into the current encompassing if block
		//by js
		// MLC don't process on simply onChange or countryChange events
		if ( ! $this->countryChange
			&& $this->displaySurvey
			&& ! $this->survey->isValidSurvey(
				$this->dataArr['tx_mssurvey_pi1']
			)
		)
		{
			$tempArr[] 			= 'tx_mssurvey_pi1';
			$this->failureMsg['tx_mssurvey_pi1'][] = "Please answer questions.";
		}

		if ($this->debug) {
			echo "\n ";
			echo "\$tempArr after survey "; print_r($tempArr);
		}

		// MLC do credit card check preAuth only if no failures and is needed
		if ( 0 == count( $tempArr )
			&& $this->ccExpiryValid
			&& $this->doCreditcardPreauth
		)
		{
			if ( ! $this->preauthCreditcard($this->dataArr[$this->ccField]) )
			{
				$tempArr[] 								= $this->ccField;
				$this->failureMsg[$this->ccField][] 	= "We are sorry but your credit card was declined during a pre-approval process. Please check that your card information is correct, try another card, or select 'Pay by phone' and we'll contact you.";
				//js: also add the error message for credit card declined
				//to the marker array, since 
				//we're already below the line above that sets marker array
				//bugfix
				$this->markerArray['###EVAL_ERROR_FIELD_'.$this->ccField.'###'] = implode($this->failureMsg[$this->ccField], '<br />');
				
			}
		}

		// MLC check for false positives
		// check for items in tempArr that aren't in formFields
		$this->formFields		= explode( ','
									, preg_replace( '#\s#', ''
										, $this->conf[$this->cmdKey.'.']['fields']
									)
								);

		foreach ( $tempArr as $key => $theField )
		{
			if ( ! in_array( $theField, $this->formFields ) )
			{
				unset( $tempArr[ $key ] );
			}
		}

		if ($this->debug) {
			echo "\n ";
			echo "\$tempArr after unset "; print_r($tempArr);
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

		$failureLabel = $failureLabel
						? $failureLabel
						: $this->pi_getLL('evalErrors_'.$theCmd);
		$failureLabel = $failureLabel
						? $failureLabel
						: (isset($this->conf['evalErrors.'][$theField.'.'][$theCmd]) ? $this->conf['evalErrors.'][$theField.'.'][$theCmd] : $label);

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
		if (in_array('name', explode(',', $this->fieldList)) && !in_array('name', t3lib_div::trimExplode(',', $this->conf[$this->cmdKey.'.']['eields'], 1)) ) {
			$this->dataArr['name'] = trim(trim($this->dataArr['first_name']).' '.trim($this->dataArr['last_name']));
		}
	}


	/**
	Set paid bit if user has already paid.  Otherwise, if user selected pay by phone or this is not a paid
	membership, paid will remain 0.
	@author Jaspreet Singh
	@param none Operates on class dataArr variable.
	@return none
	*/
	function setPaidBit() {
		$paymentMethod = $this->dataArr[ 'payment_method' ];
		if ('paymentMethod_RedemptionCode'==$paymentMethod ||  'credit_card' == $paymentMethod ) {
			$this->dataArr['paid']= 1;
		}
	}

	/**
	Saves redemption code usage, if any.
	@param int the UID of the associated row in fe_users
	*/
	function saveRedemptionCodeUsage($feuser_uid) {

		$redemptionCode = $this->dataArr['redemptionCode'];

		//No need to do anything if the redemption code is blank
		if (!$redemptionCode || ''==$redemptionCode) {
			return;
		}

		if ($this->debug) {
			echo "saveRedemptionCodeUsage()";
		}

		/* Query looks like this:
		INSERT INTO fe_users_tx_ttproductsredemption_redemptioncodeusage_mm( uid_local, uid_foreign, crdate )
		VALUES (2581, 1, 2343243434 );
		*/

		$feuser_uid = $feuser_uid; //is_null($this->dataArr[uid]) || ''==$this->dataArr[uid] ? '0' : $this->dataArr[uid] ;
		$redemptionCodeID = $this->getRedemptionCodeID($redemptionCode);
		$crdate = time();

		$query				= "
			INSERT INTO fe_users_tx_ttproductsredemption_redemptioncodeusage_mm( uid_local, uid_foreign, crdate )
			VALUES ('$feuser_uid', '$redemptionCodeID', '$crdate' )
		";

		if ($this->debug) echo $query;

		$this->db->sql(TYPO3_db, $query);
		if ($this->debug) {
			echo $this->db->sql_error();
		}

	}

	/**
	* Saves the data into the database
	*
	* @return void  sets $this->saved
	*/
	function save() {
		$survey = array();
		$survey['results'] = $this->dataArr['tx_mssurvey_pi1'];

		if($this->debug) echo "save()<br>\n";

		$dropFields				= explode( ','
									, $this->conf[ 'dropFields' ]
								);

		if($this->debug) {
			echo " \$dropFields $dropFields ";
			print_r ($dropFields);
		}

		// <td@krendls>
		$tdValue = 0;
		if(isset($this->dataArr['tx_bsgrsspartner_interested_topics_678e0591c9']))
		{
			foreach($this->dataArr['tx_bsgrsspartner_interested_topics_678e0591c9'] as $shift)$tdValue = $tdValue | 1 << $shift;
		}
		$this->dataArr['tx_bsgrsspartner_interested_topics_678e0591c9'] = $tdValue;
		// </td@krendls>

		$this->setPaidBit();

		switch($this->cmd) {
			case 'edit':
			if ($this->debug) echo "case edit <br>\n";
			$theUid = $this->dataArr['uid'];
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $theUid);
			// Fetches the original record to check permissions
			// MLC make sure there's a uid to prevent blantant updates
			if ($theUid && $this->conf['edit'] && ($GLOBALS['TSFE']->loginUser || $this->aCAuth($origArr))) {
				// Must be logged in in order to edit (OR be validated by email)
				$newFieldList = implode(',', array_intersect(explode(',', $this->fieldList), t3lib_div::trimExplode(',', $this->conf['edit.']['fields'], 1)));

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
						 exit();
					}

					$this->noArrayArray( $this->dataArr );
					$this->rectifyUserGroup(); // - js
					$this->saveRedemptionCodeUsage( $theUid ); // -js

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
					$this->saveCanspamInfo($theUid, 'save', 'edit'); // - js
					$this->saved = 1;
				} else {

					$this->error = '###TEMPLATE_NO_PERMISSIONS###';
				}
			}

			if ($this->loggingOn) { //js
				$this->logger->logEntry($theUid, 0, 0, 'save()', $this->currentArr);
			}

			// MLC don't fire update if none
			if ( $this->displaySurvey )
			{
				$survey['userID'] = $theUid;
				$updated = $this->survey->updateSurvey( $survey);

				// MLC have some warning of a failure to fix
				if ( false && ! $updated )
				{
					mail( 'michael@peimic.com'
						, 'BPM: Survey update failure'
						, 'file ' . __FILE__
							. "\nline: " . __LINE__
							. "\nsurvey" . cbPrintString( $survey )
							. "\nfe_user" . cbPrintString( $this->dataArr )
					);
				}
			}

			break;
			default:
			if ($this->debug) echo "case default <br>\n";
			if ($this->conf['create']) {
				$newFieldList =
				    implode(
				        array_intersect(
				            explode(',', $this->fieldList),
				            t3lib_div::trimExplode(',', $this->conf['create.']['fields'], 1)
				        ),
				    ',');
				$newFieldList =
				    implode(
				        array_unique(
				            array_merge(
				                explode(',', $newFieldList),
				                explode(',', $this->adminFieldList)
				            )
				        ),
				    ',');

				$newFieldList = implode( ',', array_diff(
										explode( ',', $newFieldList )
										, $dropFields
									)
								);

				$this->noArrayArray( $this->dataArr );
				if($this->conf['rssPartnerPid'] == $GLOBALS['TSFE']->page['uid'])
				    $this->dataArr['usergroup'] .= ','.$this->conf['rssPartnerWaitingUsergroup'];
				$query = $this->cObj->DBgetInsert($this->theTable, $this->thePid, $this->parseOutgoingDates( $this->dataArr ), $newFieldList);

				if ( $this->debug )
				{
					 cbPrint( __LINE__ );
					 cbPrint2( 'query', $query );
					 exit();
				}

				$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
				echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
				$newId = $GLOBALS[ 'TYPO3_DB' ]->sql_insert_id();
				$this->dataArr[ 'uid' ]	= $newId;

				$this->upgradeMemberAccess($newId); // - js
				$this->saveRedemptionCodeUsage($newId); // - js

				if ( $this->debug )
				{
					echo "\$newId: /$newId/";
				}

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
					// MLC ensure user uid
					if ($newId && count($dataArr)) {
						$query = $this->cObj->DBgetUpdate($this->theTable, $newId, $dataArr, $extraList);

						if ($this->conf['debug']) debug('Own-self query: '.$query, 1);
							$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
							echo $GLOBALS[ 'TYPO3_DB' ]->sql_error();
					}

				}

				$this->currentArr = $this->parseIncomingTimestamps( $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $newId));
				$this->userProcess_alt($this->conf['create.']['userFunc_afterSave'], $this->conf['create.']['userFunc_afterSave.'], array('rec' => $this->currentArr));
				$this->saved = 1;

				$this->saveCanspamInfo($newId, 'save', 'create'); // - js
				if ($this->loggingOn) { //js
					$this->logger->logEntry($newId, 0, 0, 'save()', $this->currentArr);
				}

				// Don't save survey results if no FE user creates, or some error occurs while retrieving new user's ID
    			if ( $newId > 0) {
    				$survey['userID'] = $newId;
        			$saved = $this->survey->saveSurvey( $survey );

					// MLC have some warning of a failure to fix
					if ( false && ! $saved )
					{
						mail( 'michael@peimic.com'
							, 'BPM: Survey update failure'
							, 'file ' . __FILE__
								. "\nline: " . __LINE__
								. "\nsurvey" . cbPrintString( $survey )
								. "\nfe_user" . cbPrintString( $this->currentArr )
						);
					}
    			}
			}
			break;
		}


		$this->currentArr = array_merge( $this->currentArr, array( 'survey' => $survey));
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
		$failure = t3lib_div::GPvar('noWarnings')
				   ? ''
				   : $this->failure;

		if (!$failure) {
			$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_FIELDS_WARNING###', '');
		}

		// Add survey error messages
		$markerArray['###MISSING_SURVEY_FIELDS###'] = ( $this->survey->hasErrors())
													  ? $this->survey->getErrorMessages()
													  : '';
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_SURVEY_FIELDS###', $markerArray['###MISSING_SURVEY_FIELDS###']);
		$templateCode = $this->removeRequired($templateCode, $failure);
		$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $currentArr);
		$markerArray = $this->addStaticInfoMarkers($markerArray, $currentArr);
		$markerArray = $this->addLabelMarkers($markerArray, $currentArr);
		if ($this->debug) {
			echo "\$this->displaySurvey $this->displaySurvey";
			echo "Displaying survey";
			/*

			*/
		}
		$markerArray['###SURVEY###'] = ( $this->displaySurvey)
									   ? $this->survey->getSurveyForm( 'upgrade')
									   : '';
		$markerArray['###SELECTBOX_tx_securityquestion_question###'] = $this->getSecurityQuestionDropdownListHTML(); //js for security question

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
		//$content .= $this->survey->getSurveyForm();
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

			// MLC cleanup usergroups to be the input
			$this->dataArr[ 'usergroup' ] 	= $this->originalUsergroup;

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
			$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord( $this->theTable, $this->recUid);
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
	* This is shows a simple thank you page
	*
	* @return string  the template with substituted markers
	*/
	function displayThankyouScreen()
	{
		// If deleting is enabled
		$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $this->recUid);

		// Must be logged in
		if ($this->theTable == 'fe_users'
			&& $GLOBALS['TSFE']->loginUser
			&& is_array($origArr)
		)
		{
			if ( ( $GLOBALS['TSFE']->fe_user->user[ 'tstamp' ] + ( 3600 * 5 ) )
				>= time()
			)
			{
				$content		= $this->getPlainTemplate('###TEMPLATE_THANKYOU###', $origArr);
			}

			else
			{
				$content		= $this->getPlainTemplate('###TEMPLATE_THANKYOU_PAST###', $origArr);
			}
		}

		else
		{
			// Finally this is if there is no login user. This must tell that you must login. Perhaps link to a page with create-user or login information.
			if ( $this->theTable == 'fe_users' ) {
				$content = $this->getPlainTemplate('###TEMPLATE_AUTH###');
			} else {
				$content = $this->getPlainTemplate('###TEMPLATE_NO_PERMISSIONS###');
			}
		}

		return $content;
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
			//templateCode is a string containing the portion of the template file that concerns us.
			//If there is a user logged in or if mode is invite, get TEMPLATE_INVITE or TEMPLATE_CREATE
			// possibly with a _PREVIEW at the end.
			//Otherwise get TEMPLATE_CREATE_LOGIN
			$templateCode = $this->cObj->getSubpart(
				$this->templateCode, ((!$GLOBALS['TSFE']->loginUser || $cmd == 'invite') ? '###TEMPLATE_'.$key.$this->previewLabel.'###' : '###TEMPLATE_CREATE_LOGIN###')
				);

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
			$this->markerArray['###PRODUCT_LIST###'] = $this->getProductListHTML(); //js the list of e-commerce products

			// Add survey error messages
			$markerArray['###MISSING_SURVEY_FIELDS###'] = ( $this->survey->hasErrors())
														  ? $this->survey->getErrorMessages()
														  : '';
			$templateCode = $this->cObj->substituteSubpart($templateCode, '###SUB_REQUIRED_SURVEY_FIELDS###', $markerArray['###MISSING_SURVEY_FIELDS###']);
			$templateCode = $this->removeRequired($templateCode, $failure);
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $this->dataArr);
			$markerArray = $this->addStaticInfoMarkers($markerArray, $this->dataArr);
			$markerArray = $this->addFileUploadMarkers('image', $markerArray, $this->dataArr);
			$markerArray = $this->addLabelMarkers($markerArray, $this->dataArr);

			if ($this->debug) {
				echo "\$this->displaySurvey $this->displaySurvey";
				echo "Displaying survey";
			}
			$markerArray['###SURVEY###'] = ( $this->displaySurvey)
										   ? $this->survey->getSurveyForm()
										   : '';
			$markerArray['###SELECTBOX_tx_securityquestion_question###'] = $this->getSecurityQuestionDropdownListHTML();

			$templateCode = $this->removeStaticInfoSubparts($templateCode, $markerArray);
			if ($this->conf['create.']['preview'] && !$this->previewLabel) {
				$markerArray['###HIDDENFIELDS###'] .= '<input type="hidden" name="'.$this->prefixId.'[preview]" value="1">';
			}
			$content = $this->cObj->substituteMarkerArray($templateCode, $markerArray);
			// $content .= $this->cObj->getUpdateJS($this->modifyDataArrForFormUpdate($this->dataArr), $this->theTable."_form", "FE[".$this->theTable."]", $this->fieldList.$this->additionalUpdateFields);

			// MLC reset usergroup for displaying
			if ( '' != $this->originalUsergroup )
			{
				$this->dataArr[ 'usergroup' ]	= $this->originalUsergroup;
			}

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
	Returns a string that is meant to be appended to the subject line in admin e-mails.
	@author Jaspreet Singh
	@return array. 0) the usergroup text 1) the call text, if any. (i.e., "Please call")
	*/
	function getSubjectLineSuffix() {
		$usergroupText		= '';
		$callText			= '';

		//usergroup text
		$comma = ',';
		$requestedUsergroupArray				= explode( $comma, $this->originalUsergroup );
		$isVisitor = 0 < count(array_intersect( $requestedUsergroupArray,  $this->visitorGroupsArray ));

		// MLC for cases when no usergroup is passed
		$isVisitor = ( $isVisitor )
						? $isVisitor
						: preg_match( '#^visitor_.+#'
							, $this->dataArr[ 'username' ]
						);

		switch( true )
		{
			// corporate
			case in_array( $this->corporateGroup, $requestedUsergroupArray ):
				$usergroupText = 'Corp';
				break;

			// professional
			case in_array( $this->professionalGroup , $requestedUsergroupArray ):
				//this check should normally be redundant
				if ('' != $this->dataArr[ 'payment_method' ]) {
					$usergroupText = 'Pro Member';
				}
				else { //this isn't supposed to happen, but just in case
					$usergroupText = 'Requested Pro Member';
				}
				break;

			// professional guest
			case in_array( $this->professionalGuestGroup, $requestedUsergroupArray ):
				$usergroupText = 'Pro Guest';
				break;

			// complimentary
			case in_array( $this->complimentaryGroup, $requestedUsergroupArray ):
				$usergroupText = 'Member';
				break;

			// visitor
			case $isVisitor:
				$usergroupText = 'Visitor';
				break;

			default:
				$usergroupText = 'Other';
				break;
		}

		//call text
		if ('phone' == $this->dataArr[ 'payment_method' ]) {
			$callText = '(Please call)';
		}

		return array($usergroupText, $callText);
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
		if ($this->debug) {
			echo "\n<hr>function compileMail($key, $DBrows, $recipient, $setFixedConfig = array())\n";
		}

		// MLC translate values to English
		$DBrows					= $this->dataArrTranslate( $DBrows );

		$mailContent = '';
		$userContent['all'] = '';
		$HTMLContent['all'] = '';
		$adminContent['all'] = '';
		if (($this->conf['email.'][$key] ) || ($key == 'SETFIXED_CREATE' && $this->setfixedEnabled) || ($key == 'SETFIXED_INVITE' && $this->setfixedEnabled) ) {
			if ($this->debug) {
				echo " Assigning 'all' marker: " . '###'.$this->emailMarkPrefix.$key.$this->emailMarkHTMLSuffix.'###' . " ";
			}

			$userContent['all'] = trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.'###'));
			$HTMLContent['all'] = ($this->HTMLMailEnabled && $this->dataArr['module_sys_dmail_html'])
				? trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.$this->emailMarkHTMLSuffix.'###'))
				:
			'';
		}
		if ($this->conf['notify.'][$key] ) {
			$adminContent['all'] = trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.$key.$this->emailMarkAdminSuffix.'###'));
		}
		$userContent['rec'] = $this->cObj->getSubpart($userContent['all'], '###SUB_RECORD###');
		$HTMLContent['rec'] = $this->cObj->getSubpart($HTMLContent['all'], '###SUB_RECORD###');
		$adminContent['rec'] = $this->cObj->getSubpart($adminContent['all'], '###SUB_RECORD###');

		if ($this->debug) {
			echo "\$HTMLContent['rec']" . $HTMLContent['rec'];
			echo "\$HTMLContent['all']" . $HTMLContent['all'];
		}

		reset($DBrows);
		while (list(, $r) = each($DBrows)) {
			$markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $r, '', 0);
			$markerArray['###SYS_AUTHCODE###'] = $this->authCode($r);

			//js payment info
			if ('' != $this->dataArr[ 'payment_method' ]) {
				$markerArray['###PURCHASE_INFO###'] = $this->getPaymentPurchaseInfoString(); //js
			} else {
				$markerArray['###PURCHASE_INFO###'] = "";
			}

			//js custom subject line
			list($usergroupText, $callText) = $this->getSubjectLineSuffix();
			$markerArray['###SUBJECT_LINE1###'] = $usergroupText;
			$markerArray['###SUBJECT_LINE2###'] = $callText;
			if ($this->debug) {
			//	echo "\$markerArray: "; print_r($markerArray);
			}

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

		$survey_info = $this->survey->getUserSurveyResults( $this->currentArr['survey']['userID']);
	    $replace = array( 'SURVEY_INFO' => $survey_info );

		// Add survey results to admin notification mail
		if ( $adminContent['final'] !== '') {
			$adminContent['final'] = $this->cObj->substituteMarkerArray( $adminContent['final'], $replace, '###|###', 1);
		}

		// MLC convert breaklines
		$adminContent[ 'final' ]	= preg_replace( '#<br( /)?>#', "\n"
										, $adminContent[ 'final' ]
									);

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

		if ($this->debug) {
			echo "<hr>\n function sendMail(\$recipient $recipient, \$admin $admin, \$content $content = '', \$adminContent $adminContent = '', \$HTMLContent $HTMLContent = '', \$fileAttachment $fileAttachment = '') {";
			echo " \$this->HTMLMailEnabled $this->HTMLMailEnabled \$this->dataArr['module_sys_dmail_html']  " . $this->dataArr['module_sys_dmail_html'];
		}

		$subjectSuffix = ' Registration';
		//js for debugging emails
		if ($this->debug) {
			$admin = 'osd_sd' . '@' . 'solutiondevelopment.biz';
		}
		// Send mail to admin
		if ($admin && $adminContent) {
			$this->cObj->sendNotifyEmail($adminContent, $admin, '', $this->conf['email.']['from'], $this->conf['email.']['fromName'], $recipient, '', $subjectSuffix);
		}
		// Send mail to front end user
		if ($this->HTMLMailEnabled && $HTMLContent && $this->dataArr['module_sys_dmail_html']) {
			$this->sendHTMLMail($HTMLContent, $content, $recipient, '', $this->conf['email.']['from'], $this->conf['email.']['fromName'], '', $fileAttachment);
			if ($this->debug) {
				echo " Sending HTML mail ";
			}

		} else {
			$this->cObj->sendNotifyEmail($content, $recipient, '', $this->conf['email.']['from'], $this->conf['email.']['fromName']);

			if ($this->debug) {
				echo " Sending Notify mail ";
			}
		}
	}

	/**
	 * Sending a notification email using $GLOBALS['TSFE']->plainMailEncoded()
	 * js note: This was copied over from class.tslib_content.php so it could be modified.
	 * Parameters relating to the subject line are added.
	 *
	 * @param	string		The message content. If blank, no email is sent.
	 * @param	string		Comma list of recipient email addresses
	 * @param	string		Email address of recipient of an extra mail. The same mail will be sent ONCE more; not using a CC header but sending twice.
	 * @param	string		"From" email address
	 * @param	string		Optional "From" name
	 * @param	string		Optional "Reply-To" header email address.
	 * @param 	string		Optional the subject line. If blank, gets subject from the first line of the content.
	 * @param   string  	Optional subjectSuffix. If set, this is appended to the subject with no intervening space (caller adds the space if desired).
	 * @return	boolean		Returns true if sent
	 */
	function sendNotifyEmail($msg, $recipients, $cc, $email_from, $email_fromName='', $replyTo='', $subject='', $subjectSuffix='')	{
			// Sends order emails:
		$headers=array();
		if ($email_from)	{$headers[]='From: '.$email_fromName.' <'.$email_from.'>';}
		if ($replyTo)		{$headers[]='Reply-To: '.$replyTo;}

		$recipients=implode(',',t3lib_div::trimExplode(',',$recipients,1));

		$emailContent = trim($msg);
		if ($emailContent)	{
			$parts = split(chr(10),$emailContent,2);		// First line is subject
			if (''==$subject) {
				$subject=trim($parts[0]);
			}
			$subject .= $subjectSuffix;
			$plain_message=trim($parts[1]);

			if ($recipients)	$GLOBALS['TSFE']->plainMailEncoded($recipients, $subject, $plain_message, implode(chr(10),$headers));
			if ($cc)	$GLOBALS['TSFE']->plainMailEncoded($cc, $subject, $plain_message, implode(chr(10),$headers));
			return true;
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
	* @param string  $subject: the subject line. If blank, gets subject from <title>
	* @return void
	*/
	function sendHTMLMail($HTMLContent, $PLAINContent, $recipient, $dummy, $fromEmail, $fromName, $replyTo = '', $fileAttachment = '', $subject = '') {
		// HTML
		if (trim($recipient)) {
			$parts = spliti('<title>|</title>', $HTMLContent, 3);
			if (''==$subject) {
				$subject = trim($parts[1]) ? strip_tags(trim($parts[1])) :
				'Front end user registration message';
			}
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

		//js add marker label for redemptionCode.  This isn't added in the loop above because redemptionCode isn't a
		// field saved in the fe_users table; rather it's used elsewhere.
		$fName = 'redemptionCode';
		$markerArray['###MISSING_'.strtoupper($fName).'###'] = $this->pi_getLL('missing_'.$fName);

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
			$markerArray['###LABEL_'.strtoupper($labelName).'###'] =
			sprintf($this->pi_getLL($labelName), $this->thePidTitle, $dataArray['username'], $dataArray['first_name'], $dataArray['email'], $dataArray['password']);

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

		// Thank you usergroup
		$ugArray				= ( 0 < sizeof( $this->usergroupArray ) )
									? $this->usergroupArray
									: explode( ',' , $GLOBALS[ 'TSFE' ]->fe_user->user[ 'usergroup' ] );

		$tyUg					= '_';

		switch ( true )
		{
			case ( in_array( $this->corporateGroup, $ugArray ) ):
				$tyUg			.= $this->corporateGroup;
				break;

			case ( in_array( $this->professionalGroup, $ugArray )
				|| $this->usergroupProPhoneAdj
			):
				$tyUg			.= $this->professionalGroup;
				break;

			case ( in_array( $this->professionalGuestGroup, $ugArray ) ):
				$tyUg			.= $this->professionalGuestGroup;
				break;

			case ( in_array( $this->complimentaryGroup, $ugArray ) ):
				$tyUg			.= $this->complimentaryGroup;
				break;

			default:
				$tyUg			= '';
				break;
		}

		$markerArray['###THANKYOU_TITLE###'] = $this->pi_getLL('thank_you_title'
			. $tyUg );
		$markerArray['###THANKYOU_BODY###'] = $this->pi_getLL('thank_you'
			. $tyUg );
		$markerArray['###THANKYOU_TITLE_PAST###'] = $this->pi_getLL('thank_you_title_past'
			. $tyUg );
		$markerArray['###THANKYOU_BODY_PAST###'] = $this->pi_getLL('thank_you_past'
			. $tyUg );
		$markerArray['###THANKYOU_EMAIL_BODY###'] = $this->pi_getLL(
			'thank_you_email' . $tyUg );

		$markerArray['###HIDDENFIELDS###'] = '';
		if( $this->theTable == 'fe_users' ) $markerArray['###HIDDENFIELDS###'] = ($this->cmd?'<input type="hidden" name="'.$this->prefixId.'[cmd]" value="'.$this->cmd.'">':''). ($this->authCode?'<input type="hidden" name="'.$this->prefixId.'[aC]" value="'.$this->authCode.'">':''). ($this->backURL?'<input type="hidden" name="'.$this->prefixId.'[backURL]" value="'.htmlspecialchars($this->backURL).'">':'');

		// MLC 20061121 Use Secure Form refresh
		$url					= '';

		if ( "on" != $_SERVER["HTTPS"] )
		{
			$url 				= 'https://'
									. $_SERVER["HTTP_HOST"]
									. user_loginRequestUri();
			$url				= '<p><a href="'
									. $url
									. '">Use Secure Form</a></p>';
		}

		$markerArray['###USE_SECURE_URL###'] = $url;

		// MLC 20081202 recaptcha
		$this->recaptchaContent = $this->recaptchaContent
			? $this->recaptchaContent
			: $this->recaptcha->getReCaptcha();
		$markerArray['###CAPTCHA###'] = $this->recaptchaContent;

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

	/** evalDate($value)
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

		// cureent
		$cMonth				= date( 'n' );
		$cYear				= date( 'Y' );

		if( is_numeric( $month )
			&& 1 <= $month && $month <= 12
			&& is_numeric( $year)
			&& $cYear <= $year && $year < ( $cYear + 11 )
			&& ( ( $year == $cYear ) ? ( $month >= $cMonth ) : true )
		)
		{
			return true;
		}

		else
		{
			return false;
		}
	}

	/**
	Check if the value is a valid redemption code.
	This function is called by main in the field checking loop.

	@author Jaspreet Singh

	@param string redemption code
	@return boolean, true if valid, false otherwise
	*/
	function evalRedemptionCode( $value )
	{
		//A blank redemption code is not an error
		if( ! $value )
		{
			return true;
		}

		$valid = false;

		$productID = $this->getRedeemableProductID();
		$valid = $this->isRedemptionCodeValidForProduct($value, $productID);

		if ($this->debug) {
			echo " $valid evalRedemptionCode( $value ) ";
		}

		return $valid;
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
		if ($this->debug) echo "evalCreditcard( $value )";

		if( ! $value )
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
								, $this->dataArr[ 'cc_type' ]
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
	Returns HTML for the product list
	*/
	function getProductListHTML() {
		if (!$this->debug) {
			return "";
		}

		$productUIDs_array 	= array(8,10,13);
		$productUIDsString 	= implode($productUIDs_array);
		$category 				= 4;
		$now					= time();
		$columns				= "uid, title, subtitle, note, price, price2";
		$from 					= "tt_products";
		$where 					= "category=$category AND deleted=0 "
									. " AND ( (starttime=0) OR ($now >= starttime) ) "
									. " AND ( (endtime=0) OR ($now <= endtime) ) "
									;

		$rows = $this->db->exec_SELECTgetRows($columns, $from, $where );

		if (0==count($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}

		//$usergroup = $rows[0]['usergroup'];
		return "<p>Products List</p>";
		return cbArr2Html( $rows, 'Products List' );
	}

	/**
	Returns a string detailing payment and purchase info, suitable for display in an e-mail to
	the user.

	@author Jaspreet Singh
	@param none Gets paramaters from Typo3 variables
	@return a string that looks something like this:
		Payment/Purchase Information:
		-------------------------------
		Credit Card No: (Last 4 digits)  3007
		Amount Charged: $329
		Items purchased: Professional Membership $249 , International Magazine Subscription $80

	*/
	function getPaymentPurchaseInfoString() {
		list($last4Chars, $amountCharged, $redemptionCode, $transactionID)
			= $this->getPaymentInfo();
		list(, $itemsPurchased) = $this->getPurchaseInfo();

		$info = 	"\nPayment/Purchase Information:";
		$info .= 	"\n-------------------------------";
		$info .= 	"\nCredit Card No: (Last 4 digits)	$last4Chars";
		$info .= 	"\nName on Card:		{$this->dataArr[ 'cc_name' ]}";
		$info .=	"\nAmount Charged:		\$$amountCharged";
		$info .=	"\n";
		$info .=	"\nItems Purchased:		$itemsPurchased";
//		$info .=	"\nRedemption Code: $redemptionCode";
		$info .=	"\n";
		$info .=	"\nCredit Card Transaction ID: $transactionID\n";
		$info .=	"\n";

		return $info;
	}

	/**
	Returns an array detailing payment info.

	@author Jaspreet Singh
	@param none Gets paramaters from Typo3 variables
	@return an array containing 1) the last 4 numbers of the credit card
		2) the amount to be charged.  This is a number,
		though it could be in the form of a string.
		3) redemption code, if any.  Is a string.
		4) the credit card transaction ID.  This is returned from the credit card
			gateway and looks something like 401A3D8B-4359375C-285-18D374
	*/
	function getPaymentInfo() {

		$cc_number = $this->dataArr['cc_number'];
		$numChars = strlen($cc_number);
		$last4Chars = substr( $cc_number, $numChars-4, $numChars-1);

		$amountCharged = $this->getChargeTotal();

		$redemptionCode = $this->dataArr['redemptionCode'];

		//This is supposed to be the final orderID, but use the initial one if final is not there
		$transactionID = (isset($this->ccFinalOrderID) || '' != $this->ccFinalOrderID)
						? $this->ccFinalOrderID
						: $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);

		if ($this->debug) {
			echo '/' . $last4Chars . '/' .$amountCharged . '.';
		}
		return array($last4Chars, $amountCharged, $redemptionCode, $transactionID);
	}

	/**
	Is the redemption code valid?  And for this particluar product? And is the code active?
	And are codes active for this product?
	@return true if valid; else false
	*/
	function isRedemptionCodeValidForProduct($redemptionCode, $productID) {

		// Do a check between products and redemption codes to make sure
		// redemption code of of product and product says that code is active
		$valid					= false;
		$now					= time();
		$columns				= "tx_ttproductsredemption_codes.percentage percentage, tx_ttproductsredemption_codes.amount amount";
		$from 					= "tx_ttproductsredemption_codes"
								. " , tt_products_tx_ttproductsredemption_redemptioncodes_mm mm"
								. " , tt_products products "
								;
		$where 					= " tx_ttproductsredemption_codes.uid = mm.uid_foreign "
								. " AND products.uid = mm.uid_local "
								. " AND tx_ttproductsredemption_codes.code='$redemptionCode'"
								. " AND products.uid ='$productID'"
								. " AND products.tx_ttproductsredemption_activateredemptioncodes = 1 "
								;
		//TODO add enable fields for other tables?  Are they needed?
 		$where					.= $this->cObj->enableFields(
 									'tx_ttproductsredemption_codes');

		$rows = $this->db->exec_SELECTgetRows($columns, $from, $where );

		if (0==count($rows)) {
			//$rows=array();
		} else {
			$this->redemptionPercentage	= $rows[ 0 ][ 'percentage' ];
			$this->redemptionAmount		= $rows[ 0 ][ 'amount' ];
			$valid = true;

			if ($this->debug) {
				//echo $where;
				print_r( $rows[0] );
			}
		}

		if ($this->debug) {
			echo ";; $valid function isRedemptionCodeValidForProduct($redemptionCode, $productID) {";
			echo $from;
			echo " ". $where;
			echo "count(\$rows) " . count($rows);
		}

		return $valid;
	}

	/**
	Returns the uid of the redemption code
	*/
	function getRedemptionCodeID($redemptionCode) {

		$now					= time();
		$columns				= "uid";
		$from 					= "tx_ttproductsredemption_codes";
		$where 					= "code='$redemptionCode'";
 		$where					.= $this->cObj->enableFields(
 									'tx_ttproductsredemption_codes');

		$rows = $this->db->exec_SELECTgetRows($columns, $from, $where );

		if (0==count($rows)) {
			//$rows=array();
		} else {
			$uid = $rows[0]['uid'];
			if ($this->debug) {
				print_r( $rows[0] );
			}
		}
		return $uid;
	}

	/**
	Return the UID of row from tt_products associated with the one selected
	product which the user could use a redemption code for.
	The reason for this is that we don't have a redemption code box for every
	product, but rather just 1.
	Depends on having class variables usergroupArray, corporateGroup
	set before this function is called.
	@author Jaspreet Singh
	@param none Gets paramaters from Typo3 variables
	@return productID
	*/
	function getRedeemableProductID() {

		$productID = '';

		if ( in_array( $this->corporateGroup, $this->usergroupArray ) ) {
			$productID = $this->corporateMembershipProductID;
			if ($this->debug) {
				echo "Corporate product";
			}
		}

		// MLC don't forget that users can pay by phone
		elseif ( in_array( $this->professionalGroup, $this->usergroupArray )
			|| $this->usergroupProPhoneAdj
		) {
			$productID = $this->professionalMembershipProductID;
			if ($this->debug) {
				echo "Professional product";
			}
		}

		if ($this->debug ) {

			echo " function getRedeemableProductID() {";
			echo " \$this->corporateGroup $this->corporateGroup ";
			echo " \$this->corporateMembershipProductID $this->corporateMembershipProductID;";
			echo " \$this->professionalGroup $this->professionalGroup";
			echo " \$this->usergroupArray"; print_r($this->usergroupArray);
			echo " \$this->professionalMembershipProductID $this->professionalMembershipProductID";
			echo " \$productID $productID";
		}

		return $productID;
	}


	/**
	Returns information on the items purchased, if any, plus payment info.
	This is based on the products the user chose.  The prices are hardcoded here.

	@author Jaspreet Singh
	@param none Gets paramaters from Typo3 variables
	@return an array containing 1) the amount to be charged.  This is a number,
		though it could be in the form of a string.
		2) the items purchased as a string.
	*/
	function getPurchaseInfo() {

		$chargeTotal = 0;
		$itemsPurchased = ''; // a string which will list what the user bought

		if ( in_array( $this->corporateGroup, $this->usergroupArray ) )
		{
			$chargeTotal = $this->corporateMembershipCharge;
			$itemsPurchased = "Corporate Membership \$$chargeTotal ";
		}

		elseif ( in_array( $this->professionalGroup, $this->usergroupArray )
			|| $this->usergroupProPhoneAdj
		)
		{
			$chargeTotal = $this->professionalMembershipCharge;
			$itemsPurchased .= "Professional Membership \$$chargeTotal ";
		}

		if ($this->debug) {
			//echo 'discount redemption code /' . $this->dataArr['redemptionCode'] . '/';
		}

		$redemptionCode = $this->dataArr['redemptionCode'];

		$productID = $this->getRedeemableProductID();

		if ($this->isRedemptionCodeValidForProduct($redemptionCode, $productID)) {
			if ($this->debug) {
				//echo 'discount redemption code';
			}

			if ( 0 != $this->redemptionAmount )
			{
				$redemptionCodeDiscount = 0 - $this->redemptionAmount;
			}

			else
			{
				$redemptionCodeDiscount = $chargeTotal
									* $this->redemptionPercentage
									/ 100
									* -1;
			}

			$chargeTotal += $redemptionCodeDiscount;

			$itemsPurchased .= "\n						Redemption Code $redemptionCode Applied \$$redemptionCodeDiscount";
		}

		if (($this->dataArr['static_info_country'] != 'USA')
			&& ( $this->dataArr['tt_products'] == '7'  ) )
		{
			$magazineCharge = 80;
			$chargeTotal += $magazineCharge;
			$itemsPurchased .= "\n						International Magazine Subscription \$$magazineCharge";
		}

		if ($this->debug) {
			echo "$ chargeTotal $chargeTotal (To be set to $ 1.05 for testing)";
			//echo $itemsPurchased;
			$chargeTotal = '1.05';
		}

		return array($chargeTotal, $itemsPurchased);
	}

	/**
	Returns the amount to be charged.

	This is based on the products the user chose.

	@author Jaspreet Singh
	@param none Gets paramaters from Typo3 variables
	@return the amount to be charged.  This is a number, though it could be in the
		form of a string.
	*/
	function getChargeTotal() {
		list($chargeTotal,) = $this->getPurchaseInfo();
		return $chargeTotal;
	}


	/**
	 * Check if the credit card is a valid credit card with amount chargeable
	 * actually available.
	 *
	 * Two things happen here.  First we perform the generic checks to see if the
	 * cc information is valid according to various algorithms.  This includes
	 * the cc number and expiry dates.  These checks are performed locally (right
	 * in this script).
	 *
	 * Secondly, we attempt to preauthorize a charge for the amount chargeable
	 * on this card by talking to the Linkpoint gateway.  This, of course,
	 * happens remotely on their computer.  If the gateway denies the card
	 * for whatever reason, this functions calls the card invalid.
	 *
	 * The preauthorization process gives us an order ID, which we save in a Typo3 session
	 * variable in order to re-use it to actually charge the card.
	 *
	 * This function is based on the orignial preauthCreditcard, which is renamed to
	 * evalCreditcard and called from here.
	 *
	 * @author Michael Cannon, michael@peimic.com
	 * @author Jaspreet Singh
	 *
	 * @param string credit card number
	 * @return boolean, true if valid, false otherwise
	 */
	function preauthCreditcard( $value )
	{
		if ($this->debug) echo "preauthCreditcard( $value )";

		if( !$value )
		{
			return false;
		}

		// An example of [cc_expiry] is 03/2005
		$value				= preg_replace( '#[^0-9]#', '', $value );
		$cc_number = $value;
		$expiry				= explode( '/', $this->dataArr[ 'cc_expiry' ] );
		$cc_month = $expiry[ 0 ];
		$cc_year = $expiry[ 1 ];
		$chargeTotal = $this->getChargeTotal();

		//Local tests (algorithmic)
		// MLC don't rerun if not needed
		if ( ! $this->ccExpiryValid || ! $this->doCreditcardPreauth )
		{
			if ( ! $this->evalCreditcard($value)
				|| ! $this->evalExpiry( $this->dataArr[ 'cc_expiry' ] )
			)
			{
				if (!$this->debug) return false;
			}
		}

		//Remote validation with with gateway
		$lp = new LinkpointCreditCard();
		$cc_year = substr( $cc_year, -2 );

		if ($this->debug) {
			echo ' getKey ccInitialOrderID/' . $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);
			echo ' getKey ccTest/' . $GLOBALS["TSFE"]->fe_user->getKey('ses', 'ccTest');
			$lp->debug = true;
		}

		//If this is the 2nd time around, there should be an orderID cached.
		//If it's good, we use it; or, if it's not good, or if this is the 1st
		//time around, we do a preAuth to get an orderID
		$cachedOrderID = $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);

		$result = array();  //this will hold the response from the gateway
		$orderID = false;
		if ( ! $cachedOrderID )
		{
			if ( 0 != $chargeTotal )
			{
				$userArr		= $this->dataArr;

				// MLC load user data from fe_users if missing
				if ( '' == $this->dataArr[ 'email' ] )
				{
					$userArr 	= $GLOBALS["TSFE"]->fe_user->user;
				}

				$result = $lp->preAuth($cc_number, $cc_month, $cc_year, $chargeTotal
					, $userArr
					, $this->getPurchaseInfo()
				);

				if ($result["r_approved"] == "APPROVED")
				{
					$orderID = $result['r_ordernum'];
				}
				if ($this->debug) {
					print_r ($result);
				}
			}

			else
			{
				$orderID = 'No-charge';
			}

			$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->ccInitialOrderID, $orderID);
		} else {
			$orderID = $cachedOrderID;
		}

		if ($this->debug) {
			echo "$ orderID"; var_dump($orderID);
			echo 'getKey ccInitialOrderID/' . $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);
			echo 'getKey ccTest/' . $GLOBALS["TSFE"]->fe_user->getKey('ses', 'ccTest');
			echo $this->getPaymentPurchaseInfoString();
		}

		//If the orderID is good, it should be something like 401A3D8B-4359375C-285-18D374
		//If the validation failed, Linkpoint returns a blank orderID.
		if( ! $orderID )
		{
			$this->logRegistrationCreditCardErrors($result);
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	Charges the credit card using the preauth OrderID stored as a session variable.

	@see preauthCreditcard( $value )
	@param none Gets parameters from session/Typo3 variables.
	@author Jaspreet Singh
	*/
	function chargeCreditCard(){
		// bail if not needed
		if ( 'credit_card' != $this->dataArr[ 'payment_method' ] )
		{
			return;
		}

		if ($this->debug) echo "function chargeCreditCard(){ \n";

		$cc_number = $this->dataArr['cc_number'];
		$expiry				= explode( '/', $this->dataArr[ 'cc_expiry' ] );
		$cc_month = $expiry[ 0 ];
		$cc_year = substr( $expiry[ 1 ], -2 );
		$chargeTotal = $this->getChargeTotal();
		if (0==$chargeTotal) { return; }
		$initialOrderID = $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);

		// MLC somehow info is gone, try to get anew
		if ( ! $initialOrderID )
		{
			if ( $this->preauthCreditcard( $this->dataArr[$this->ccField] ) )
			{
				$initialOrderID = $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->ccInitialOrderID);
			}

			// warn and bail
			elseif ( false )
			{
				$subjectLine 	= "BPM: Registration no order id failure";
				if ($this->debug) { $subjectLine .= " (\$debug is on)"; }
				mail( 'michael@peimic.com'
					, $subjectLine
					, 'file ' . __FILE__
						. "\nline: " . __LINE__
						. "\ndataArr" . cbPrintString( $this->dataArr )
						. "\nthis" . cbPrintString( $this )
				);

				return;
			}
		}

		$lp = new LinkpointCreditCard();
		$result = $lp->postAuth($cc_number, $cc_month, $cc_year, $chargeTotal, $initialOrderID
			, $this->dataArr
			, $this->getPurchaseInfo()
		);

		$finalOrderID = $result['r_ordernum'];

		//cache the final order ID as we will send it in an e-mail.
		//blank out the old initial order ID, as it's already been used for a postAuth
		//and is no longer valid
		$this->ccFinalOrderID = $finalOrderID;
		$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->ccInitialOrderID, '');

		if ($this->debug) { echo "$ orderID"; var_dump($finalOrderID); }
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
		// MLC 20080514
		// return '';

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
	 * (Modified by js to reflect new /renew page logic according to the
	 * page type (create/edit) and the previous and new member type (is a
	 * paid renewal being made?)
	 * @return void
	 */
	function setStartEndTime()
	{
		// set default start and end time
		$now						= time();

		if ( ! trim( $this->dataArr[ 'starttime' ] ) )
		{
			$this->dataArr[ 'starttime' ]	= $now;
		}

		if ( ! trim( $this->dataArr[ 'endtime' ] ) )
		{
			$this->dataArr[ 'endtime' ] 	= 0;
		}

		$userGroupIsProfessional 		= $this->isCurrentUserGroupProfessional();
		$userGroupUponRegIsProfessional = $this->isUserGroupUponRegProfessional();
		$start						= ( 'create' == $this->cmd )
										? $now
										: $this->dataArr[ 'starttime' ];

		// MLC blank usergroup is to help not lose the current endtime
		$end						= ( $this->dataArr[ 'endtime' ]
										&& ( $userGroupIsProfessional
											|| $userGroupUponRegIsProfessional
											|| '' == $this->dataArr['usergroup']
											|| $this->usergroupProPhoneAdj
										)
									)
										? $this->dataArr[ 'endtime' ]
										: 0;

		$year					= ( 365 * 24 * 60 * 60 );

		if ( $this->debug) {
			echo 'setStartEndTime(): usergroup:' . $this->dataArr['usergroup']
				. ' userGroupUponRegistration:' . $this->conf[ 'userGroupUponRegistration' ]
				. ' isUserGroupUponRegProfessional():'; var_dump($userGroupUponRegIsProfessional);
			echo ' starttime: ' . $this->dataArr['starttime']
				. 'endtime' . $this->dataArr['endtime']
				. ' getCurrentUserGroupString():' . $this->getCurrentUserGroupString()
				. "\n \$start $start \$end $end";
				;
		}

		//case create
		if ( 'create' == $this->cmd ) {
			//prof member start = now, end=now+1yr
			if ($userGroupUponRegIsProfessional) {
				$start 		= $now;
				$end 		= $now + $year;
				if ($this->debug) {echo "\n create: //prof member start = now, end=now+1yr";}
			} else {
				//free member start = now, end=0
				// no additional edit needed
				if ($this->debug) {echo "\n create: //free member start = now, end=0";}
			}
		}

		//case edit
		elseif ( 'edit' == $this->cmd ) {
			if ($userGroupUponRegIsProfessional) {
				if ($this->isCurrentUserGroupFree()) {
					//was free member, new usergroup is prof
					//start = now, end = now+1yr
					if ($this->debug) {echo "\n edit: // was free, new usergroup prof, start = now, end = now+1yr";}
					$start 	= $now;
					$end 	= $now + $year;
				}
				elseif ($this->isCurrentUserGroupProfessional()) {
					//was prof member, new usergroup is prof (increasing the term of prof. membership)
					//start = (currentvalue), end=(currentvalue)+1yr
					if ($this->debug) {echo "\n edit: // was prof member, new usergroup is prof (increasing term), //start = (currentvalue), end=(currentvalue)+1yr";}
					// $start = ; handled above
					// MLC don't add year unless paid for
					if (  '' != $this->dataArr[ 'payment_method' ] )
					{
						// end could be zero
						$end	+= ( $end )
									? $year
									: $now + $year;
					}
					if ($this->debug) {
						echo "\n After case edit prof-prof:\$start $start \$end $end";
					}
				}
			} else {
			//was free member, new usergroup is free too (just a profile edit, not upgrade)
			//start = (currentvalue), end=(currentvalue)
			//happens automatically
			if ($this->debug) {echo "\n edit: //was free member, new usergroup is free too (or prof/prof just a profile edit, not upgrade), //start = (currentvalue), end=(currentvalue)";}
			}
		}

		if ($this->debug) {
			echo "\n New Start/End Times: start:" . $start . date("r", $start)
			. ' end: ' . $end . date("r", $end)
			;
		}

		$this->dataArr[ 'starttime' ]	= $start;
		$this->dataArr[ 'endtime' ]		= $end;
	}

	/**
	Returns whether the current usergroup of the user (stored in the database)
	is not a professional usergroup.
	@author js
	@param none
	@return boolean
	*/
	function isCurrentUserGroupFree()
	{
		return !($this->isCurrentUserGroupProfessional());
	}

	/**
	Returns whether the current usergroup of the user (stored in the database)
	is a professional usergroup.
	@author js
	@param none
	@return boolean
	*/
	function isCurrentUserGroupProfessional()
	{
		$currentUserGroup = $this->getCurrentUserGroupString();
		return $this->isUserGroupProfessional($currentUserGroup);
	}

	/**
	Returns whether or not the usergroup the user is requesting is a professional
	user group. (I.e., the usergroup upon registration.)

	This includes group 2 (Professional Member) and
	group 4 (Professional Guest).
	@author Jaspreet Singh
	@param none. Implicit parameter of usergroup taken from class variable dataArr.
	@return boolean
	*/
	function isUserGroupUponRegProfessional()
	{
		// MLC check for payment method
		return '' != $this->dataArr[ 'payment_method' ] && $this->isUserGroupProfessional( $this->dataArr['usergroup'] );
	}

	/**
	Returns whether or not the passed usergroup is a professional user group.

	This includes group 2 (Professional Member) and
	group 4 (Professional Guest).

	If the group is 2, only returns true if a payment is being made.
	@author Jaspreet Singh
	@param int. The usergroup to test
	@return boolean
	*/
	function isUserGroupProfessional($usergroup)
	{
		$returnValue = false;

		// MLC use class vars and swap preg lookups
		// set a flag for professional guest group.
		$isProfessionalGuestUserGroup = preg_match(
											'#\b' . $this->professionalGuestGroup . '\b#'
											, $usergroup
										);

		if ( preg_match( '#\b' .$this->professionalGroup . '\b#'
				, $usergroup )
			|| $isProfessionalGuestUserGroup
		)
		{
			$returnValue = true;
		}

		return $returnValue;
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

		// MLC catch pending pro
		$pendingProText			= ( ! $this->usergroupProPhoneAdj )
									? 'Complimentary Member'
									: 'Pending Professional Member';

		// create assoc array of items to translate
		$translator				= array(
			// MLC TODO sorry, nasty hard coding
			// convert to labels via text tool?
			// grab usergroup title from usergroups table directly?
			'usergroup'				=> array(
				'1'						=> $pendingProText
				, '2'					=> 'Professional Member'
				, '3'					=> 'Complimentary Professional'
				, '4'					=> 'Professional Guest Member'
				, '5'					=> 'Visitor, White Paper'
				, '6'					=> 'Visitor, Round Table'
				, '7'					=> 'Visitor, Presentation'
				, '8'					=> 'Editor Preview'
				, '9'					=> 'Chicago conference'
				, '10'					=> 'San Francisco conference'
				, '11'					=> 'Washington DC conference'
				, '12'					=> 'New York conference'
				, '13'					=> ''	// SOA comp
				, '14'					=> ''	// SOA pro
				, '15'					=> ''	// SOA member
				, '16'					=> 'Corporate Membership'
				, '17'					=> 'SOA Institute'	// SOA domain
				, '18'					=> 'BPM Institute'	// BPM domain
				, '19'					=> ''	// Conference requesters
				, '20'					=> ''	// BSG only
				, '21'					=> ''	// Sales login
				, '22'					=> ''	// Sponsor login
				, '23'					=> ''	// TBD
				, '24'					=> ''	// TBD
				, '25'					=> ''	// TBD
			)
			, 'payment_method'		=> array(
				'credit_card'			=> 'Credit card'
				, 'phone'				=> 'Please call'
				, 'paymentMethod_RedemptionCode'	=> 'Redemption code'
			)
			, 'module_sys_dmail_html'	=> $yesNoArray
			, 'tx_bpmprofile_newsletter1' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter2' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter3' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter4' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter5' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter6' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter7' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter8' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter9' 	=> $yesNoArray
			, 'tx_bpmprofile_newsletter10' 	=> $yesNoArray
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
					// MLC ,, to ,
					$dataArr[ $key ]	= preg_replace( "#,\s?,#", ','
											, $origValue
										);
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

		if ( $uid && 1 == $this->memberAccessProcessing )
		{
			if ($this->debug) echo "Upgrading access";

			$memberaccess = new tx_memberaccess_modfunc1;
			$memberaccess->initialize();
			$memberaccess->db = $GLOBALS['TYPO3_DB'];
			if ($this->debug) {
				$memberaccess->debug = true;

			}

			$memberaccess->updateFeUserGroupsExtended($uid);
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
		// set a flag for professional guest group.
		$isProfessionalGuestUserGroup = in_array(
			$this->professionalGuestGroup, $this->usergroupArray );

		//if memberexpiry processing is set on
		//and if the member is renewing a professional or
		//professional guest group membership,
		if ((1==$this->memberExpiryProcessing)
			&& (isset($uid))
			&& ( ( in_array( $this->professionalGroup, $this->usergroupArray )
				&& '' != $this->dataArr[ 'payment_method' ]
			) || $isProfessionalGuestUserGroup )
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
	Logs credit card errors encountered in the process of registration
	@param array the array returned as a result by the Linkpoint PHP gateway functions.
	@author Jaspreet Singh
	*/
	function logRegistrationCreditCardErrors($result) {

		$uid = is_null($this->dataArr[uid]) || ''==$this->dataArr[uid] ? '0' : $this->dataArr[uid] ;
		$email = is_null($this->dataArr[email]) ? '' : $this->dataArr[email] ;
		$errortime = time();
		$errors = '';
		while (list($key, $value) = each($result))
		{
			$errors .=  "$key = $value\n";
		}

		if ($this->debug) {
			echo "\$ccerrors $errors";
		}

		$this->logRegistrationErrors($uid, $email, $errortime, $errors);
	}

	/**
	 * Log registration errors in table tx_memberaccess_registrationerrors.
	 * Does this only if the conf item registrationErrorLogging is set.
	 *
	 * @author Jaspreet Singh
	 * @return void
	 */
	function logRegistrationErrors($uid, $email, $errortime, $errors)
	{

		if ($this->debug) {
			echo "debug mode - entering logRegistrationErrors()";
			//return;
		}

		/* Query looks like this:
		INSERT INTO tx_memberaccess_registrationerrors( userid, errortime, email, errors )
		VALUES (6464, 1121928502, 'asf@qwerty.com', 'Email, First Name, Last Name, Question1' );
		*/
		if ($this->debug) echo "logRegistrationErrors()";

		//if logging is set on
		if (1==$this->registrationErrorLogging){
			if ($this->debug)  "logging RegistrationErrors";

			$query				= "
				INSERT INTO tx_memberaccess_registrationerrors( userid, errortime, email, errors )
				VALUES ('$uid', $errortime, '$email', '$errors' )
			";

			if ($this->debug) { echo $query; }

			$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
			if ($this->debug) { echo $GLOBALS[ 'TYPO3_DB' ]->sql_error(); }
		}

	}

	/**
	 * Log registration errors in table tx_memberaccess_registrationerrors.
	 * Does this only if the conf item registrationErrorLogging is set.
	 * No parameters - gets parameters from class variables.
	 * @author Jaspreet Singh
	 * @return void
	 */
	function logGeneralRegistrationErrors()
	{

		if ($this->debug) {
			echo "debug mode - entering logRegistrationErrors()";
			return;
		}

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

			if ($this->debug) { echo $query; }

			$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
			if ($this->debug) { echo $GLOBALS[ 'TYPO3_DB' ]->sql_error(); }
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
		//if there was not change in the usergroup requested, we don't want to do an update
		if (!($this->dataArr['usergroup'])) {
			return;
		}
		$currentUserGroupString = $this->getCurrentUserGroupString();

		$memberaccess = new tx_memberaccess_modfunc1;
		$memberaccess->db = $GLOBALS['TYPO3_DB'];
		if ($this->debug) {
			$memberaccess->debug = true;
		}
		//Ignore the free/professional/complimentary groups because those are the
		//groups that we want to change, not ones that we want to keep.
		$ignoredLevels			= $this->professionalGroup
									. ',' . $this->corporateGroup
									. ',' . $this->professionalGuestGroup
									. ',' . $this->complimentaryGroup
									;

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

		$ignoredLevels			= $this->professionalGroup
									. ',' . $this->professionalGuestGroup
									. ',' . $this->complimentaryGroup
									;
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
		if (false && $this->debug) { echo "getCurrentUserGroupString();\n<br>"; }

		$uid = $this->dataArr['uid'];
		$usergroup = null;

		if (isset($uid) && $uid!='') {
			$columns = 'usergroup';
			$from = 'fe_users';
			$where = "uid = $uid";
			$rows = $this->db->exec_SELECTgetRows($columns, $from, $where );

			if (0==count($rows)) {
				if ($this->debug) {
					echo ' $rows not an array';
				}
				$rows=array();
			}

			$usergroup = $rows[0]['usergroup'];
		}

		return $usergroup;
	}

	function debug_print( $var) {
		echo '<!-- ';
		print_r( $var);
		echo ' -->';
	}



	/**
	Saves info required for CANSPAM compliance.
	Gets the rest of the parameters from class members.
	Only takes action if TS conf enableCanspamLogging is 1.
	@author Jaspreet Singh
	@param int the UID of the associated row in fe_users
	@param string the action that occurred (e.g. 'save')
	@param string the sub-action that occurred (e.g. 'edit' or 'create')
	@return void
	*/
	function saveCanspamInfo($feuserID, $action, $subAction) {

		//The $newID shouldn't be null, but even if it is, it's OK, because we save
		//all the informaiton we have.
		if ($this->debug) {
			echo "saveCanspamInfo($feuserID)";
			cbPrint2('$this->currentArr', $this->currentArr);
			//echo "\n$this->currentArr"; var_dump($this->currentArr);
		}

		if ( $this->conf['enableCanspamLogging'] != 1 ) {
			if ($this->debug) {
				echo "enableCanspamLogging is off. returning.";
			}
			return;
		}

		/* Query looks like this:
		INSERT INTO tx_canspamlog_main( fe_userid, pageID, action_time, url, action, subaction, client_ip )
		VALUES (2581, 1, 2343243434, 'bpminstitute.org/member-login/join/secure-join.html', 'save', 'edit', '255.255.255.255' );
		*/

		$pageID 		= $GLOBALS['TSFE']->id;
		$actionTime 	= time();
		$site			= $_SERVER['SERVER_NAME'];
		$url 			= $_SERVER['REQUEST_URI'];
		$ip				= $_SERVER['REMOTE_ADDR'];
		$a				= $this->currentArr; //$a for array
		$tx_bpmprofile_newsletter1	= $a['tx_bpmprofile_newsletter1'];
		$tx_bpmprofile_newsletter2	= $a['tx_bpmprofile_newsletter2'];
		$tx_bpmprofile_newsletter3	= $a['tx_bpmprofile_newsletter3'];
		$tx_bpmprofile_newsletter4	= $a['tx_bpmprofile_newsletter4'];
		$tx_bpmprofile_newsletter5	= $a['tx_bpmprofile_newsletter5'];
		$tx_bpmprofile_newsletter6	= $a['tx_bpmprofile_newsletter6'];
		$tx_bpmprofile_newsletter7	= $a['tx_bpmprofile_newsletter7'];
		$tx_bpmprofile_newsletter8	= $a['tx_bpmprofile_newsletter8'];
		$tx_bpmprofile_newsletter9	= $a['tx_bpmprofile_newsletter9'];
		$tx_bpmprofile_newsletter10	= $a['tx_bpmprofile_newsletter10'];
		$currentArr					= mysql_escape_string(print_r( $a, true));

		$query				= "
			INSERT INTO tx_canspamlog_main( fe_userid, pageID, action_time, site
			, url, action, subaction, client_ip
			,tx_bpmprofile_newsletter1,tx_bpmprofile_newsletter2
			,tx_bpmprofile_newsletter3,tx_bpmprofile_newsletter4
			,tx_bpmprofile_newsletter5,tx_bpmprofile_newsletter6
			,tx_bpmprofile_newsletter7,tx_bpmprofile_newsletter8
			,tx_bpmprofile_newsletter9,tx_bpmprofile_newsletter10
			,currentArr)
			VALUES ('$feuserID', '$pageID', $actionTime, '$site'
			,'$url', '$action', '$subAction', '$ip'
			,'$tx_bpmprofile_newsletter1','$tx_bpmprofile_newsletter2'
			,'$tx_bpmprofile_newsletter3','$tx_bpmprofile_newsletter4'
			,'$tx_bpmprofile_newsletter5','$tx_bpmprofile_newsletter6'
			,'$tx_bpmprofile_newsletter7','$tx_bpmprofile_newsletter8'
			,'$tx_bpmprofile_newsletter9','$tx_bpmprofile_newsletter10'
			,'$currentArr'
			)
		";

		if ($this->debug) echo $query;

		$this->db->sql(TYPO3_db, $query);
		if ($this->debug) {
			echo $this->db->sql_error();
		}

	}

	/**
	Return HTML for the security question selectbox.
	Pass in the value the user has currently selected to have that have the "selected"
	attribute applied to it.
	@param The HTML name of select dropdown box.
	@param The id (from the db table) of the value which the user has currently selected.
	@author Jaspreet Singh
	@return string. The HTML.
	*/
	function getSecurityQuestionDropdownListHTML($name="FE[fe_users][tx_securityquestion_question]", $userSelectedKey=0) {

		if (0==$userSelectedKey) {
			$userSelectedKey = t3lib_div::_GP($name);
		}

		$securityQuestion = new SecurityQuestion($this->db, $this->cObj);
		if ($this->debug) {
			//phpinfo();
			echo "getSecurityQuestionDropdownListHTML() ";
			echo "\$userSelectedKey $userSelectedKey ";
			$securityQuestion->debug = 1;
		}

		$html = $securityQuestion->getSecurityQuestionDropdownListHTML();
		return $html;
	}

    // <td@krendls>
    function displayRssAgreementScreen()
    {
        $content = '';
        $markers = array();
        $templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RSS_AGREEMENT###');

        $markers = $this->addURLMarkers($markers);
        $markers['###LABEL_BUTTON_REGISTER###'] = $this->pi_getLL('button_register');
        $content = $this->cObj->substituteMarkerArrayCached($templateCode, $markers);
        return  $content;
    }
    function displayRssThankyouScreen()
    {/*
        $content = '';
        $markers = array();
        $templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RSS_THANK_YOU###');

        $markers['###LABEL_BUTTON_REGISTER###'] = $this->pi_getLL('button_register');
        $content = $this->cObj->substituteMarkerArrayCached($templateCode, $markers);
        return  $content;*/
	    $rURL = $this->pi_getPageLink($this->conf['rssPartnerThankyouPid']);
        header('Location: '.t3lib_div::locationHeaderUrl($rURL));
        exit;
    }
    function displayRssAgreementError()
    {
        $content = '';
        $markers = array();
        $templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RSS_NO_AGREE_CHECK###');

        $markers['###LABEL_BUTTON_REGISTER###'] = $this->pi_getLL('button_register');
        $content = $this->cObj->substituteMarkerArrayCached($templateCode, $markers);
        return $content;
    }
    function displayRssUpdateDBError()
    {
        $content = '';
        $markers = array();
        $templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RSS_UPDATE_ERROR###');

        $markers['###LABEL_BUTTON_REGISTER###'] = $this->pi_getLL('button_register');
        $content = $this->cObj->substituteMarkerArrayCached($templateCode, $markers);
        return $content;
    }
    function rssGetMailContent($userID)
    {
        $content = '';
        $markers = array();
        $templateCode = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RSS_SEND_MAIL###');


        $query = 'SELECT * FROM `fe_users` WHERE `uid`='.$userID;
        echo $query;
        $sqlResult = $this->db->sql_query($query);
        if($sqlResult != false)
        {
            $userinfo = $this->db->sql_fetch_assoc($sqlResult);

            $options = array(
                4 => 'Simple RSS Newsfeed',
                1 => 'Option 1: Do it yourself',
                2 => 'Option 2: Use our code to display on your site',
                3 => 'Option 3: View examples to store in your database');
            $intend_option = $options[(int)$userinfo['tx_bsgrsspartner_intend_option']];

            $topics = array(
                0 => 'Business Process Management',
                1 => 'Sarbanes-Oxley',
                2 => 'Service-Oriented Architecture',
                3 => 'Business Rules',
                4 => 'Organizational Performance',
                5 => 'Enterprise Architecture'
            );
            $interested_topics = $userinfo['tx_bsgrsspartner_interested_topics_678e0591c9'];
            $interested_topics_text = '';
            foreach($topics as $shift => $value)
            {
                if($interested_topics >> $shift == 1)$interested_topics_text .= $value."\n";
            }

            $markers['###USER_ACCOUNT###']               = empty($userinfo['username']) ? '' : $userinfo['username'];
            $markers['###USER_FNAME###']                 = empty($userinfo['first_name']) ? '' : $userinfo['first_name'];
            $markers['###USER_LNAME###']                 = empty($userinfo['last_name']) ? '' : $userinfo['last_name'];
            $markers['###USER_MAIL###']                  = empty($userinfo['email']) ? '' : $userinfo['email'];
            $markers['###USER_PASS###']                  = empty($userinfo['password']) ? '' : $userinfo['password'];
            $markers['###USER_TITLE###']                 = empty($userinfo['title']) ? '' : $userinfo['title'];
            $markers['###USER_COMPANY###']               = empty($userinfo['company']) ? '' : $userinfo['company'];
            $markers['###USER_ADDRESS###']               = empty($userinfo['address']) ? '' : $userinfo['address'];
            $markers['###USER_CITY###']                  = empty($userinfo['city']) ? '' : $userinfo['city'];
            $markers['###USER_ZIP###']                   = empty($userinfo['zip']) ? '' : $userinfo[''];
            $markers['###USER_WORK_PHONE###']            = empty($userinfo['telephone']) ? '' : $userinfo['telephone'];
            $markers['###USER_FAX###']                   = empty($userinfo['fax']) ? '' : $userinfo['fax'];
            $markers['###USER_COMPANY_WEBSITE###']       = empty($userinfo['www']) ? '' : $userinfo['www'];
            $markers['###USER_RSS_USE_INTEND###']        = empty($userinfo['tx_bsgrsspartner_intend_use']) ? '' : $userinfo['tx_bsgrsspartner_intend_use'];
            $markers['###USER_RSS_INTEND_OPTION###']     = empty($intend_option) ? '' : $intend_option;
            $markers['###USER_RSS_INTERESTED_TOPICS###'] = empty($interested_topics_text) ? '' : $interested_topics_text;
            $markers['###USER_RSS_COMMENTS###']          = empty($userinfo['tx_bsgrsspartner_comments']) ? '' : $userinfo['tx_bsgrsspartner_comments'];

            $content = $this->cObj->substituteMarkerArrayCached($templateCode, $markers);
        }else{
            $content = false;
        }
        return $content;
    }
    function rssSendMail($userUID)
    {
        $mailContent = $this->rssGetMailContent($userUID);
        t3lib_div::plainMailEncoded(
            $this->conf['rssPartnerApproveMail'],
            'Partnership request',
            $mailContent);
    }
    function rssApproveCheck()
    {
        $content = '';
        if(isset($this->dataArr['join_agree']) && $this->dataArr['join_agree'] == 'yes')
        {
            $this->rssSendMail($GLOBALS['TSFE']->fe_user->user['uid']);
            $sql = "UPDATE `fe_users` SET `usergroup` = CONCAT(`usergroup`, ',{$this->conf['rssPartnerWaitingUsergroup']}') WHERE `uid`=".$GLOBALS['TSFE']->fe_user->user['uid'];
            $sqlResult = $this->db->sql_query($sql);
            if($sqlResult != false)
            {
                $content .= $this->displayRssThankyouScreen();
            }else{
                $content .= $this->displayRssUpdateDBError();
            }
        }else{
            $content .= $this->displayRssAgreementError();
            $content .= $this->displayRssAgreementScreen();
        }
        return  $content;
    }
    // </td@krendls>

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register/pi1/class.tx_srfeuserregister_pi1.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register/pi1/class.tx_srfeuserregister_pi1.php"]);
}
?>
