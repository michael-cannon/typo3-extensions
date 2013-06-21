<?php
session_start();
/***************************************************************
*  Copyright notice
*
*  (c) 2008  <>
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
require_once(t3lib_extMgm::extPath('bsg_regsteps')."/ccvalidator.php");
require_once(t3lib_extMgm::extPath('bsg_regsteps')."/class.validator.php");
require_once(t3lib_extMgm::extPath('bsg_regsteps')."/linkpoints/lphp.php");

// MLC 20080221 salesforce integration
require_once(t3lib_extMgm::extPath('salesforce_subscribe')."/pi1/salesforce.php");
require_once(t3lib_extMgm::extPath('salesforce_subscribe')."/pi1/nusoap.php");
require_once( dirname(__FILE__)."/../../salesforce-access.php");

// MLC 20081202 recaptcha
require_once(t3lib_extMgm::extPath('jm_recaptcha')."class.tx_jmrecaptcha.php");

/**
 * Plugin 'BSG Registration Plugin' for the 'bsg_regsteps' extension.
 *
 * @author	Eugene Lamskoy <e.lamskoy@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_bsgregsteps
 */

class tx_bsgregsteps_pi1 extends bsg_controller  {

    var $prefixId      = 'tx_bsgregsteps_pi1';
    var $scriptRelPath = 'pi1/class.tx_bsgregsteps_pi1.php';
    var $extKey        = 'bsg_regsteps';
    var $view;
    var $maxSteps = 4;
    var $stepNumber;
    var $stepIndex = 'step';
    var $fldRules;
	// salesforce user id
    var $sfuid;
	// salesforce data connection
    var $sfdc;
	// salesforce owner id
	var $sfoid					= '00530000000l2bhAAA';
	var $sfUsername				= SF_USERNAME;
	var $sfPassword				= SF_PASSWORD;
	var $conferenceCourses		= array();
	var $eventCourses			= array();
	var $trainingCourses		= array();
	var $confSeries				= array();
	var $proPrice				= 0;
	var $cbDebug				= FALSE;
	var $coursesPicked			= array();

	// MLC 20081202 recaptcha
	var $recaptcha				= null;
	var $p						= null;

    function main($content,$conf)	{
        $this->conf=$conf;
		$confCode				= isset( $conf['confCode'] )
									? $conf['confCode'] 
									: 'CH';
		define( 'BSG_REG_CONF_CODE', $confCode );

		$regformTemplate		= isset( $conf['regformTemplate'] )
									? $conf['regformTemplate'] 
									: 'regform.php';

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj=1;

        $this->lp = $this->getService("LinkpointsService");
        $this->lp->testing = false;
		// cbDebug testing off
		if ( $this->cbDebug ) {
        	$this->lp->testing = TRUE;
		}
        $this->lp->setKeyfile("/home/bpm/public_html/972837.pem");
        $this->lp->setHost("secure.linkpt.net");
        $this->lp->setMerchantID("972837");
        $this->lp->setPort("1129");

        if ( isset($_GET['reset'] ) )  {
            $this->clearSessionData();
        }

        $this->view = $this->getView("RegformView");

        if(isset($_GET['cmd'])) {
            if($_GET['cmd'] == 'prices') {
                return $this->ajaxPrices();
            }
            if($_GET['cmd'] == 'zones') {
                return $this->ajaxZones();
            }
            if($_GET['cmd'] == 'buypro') {
                return $this->ajaxAutoBuypro();
            }
        } else {
			// MLC 20081202 recaptcha
			$this->recaptcha = new tx_jmrecaptcha();
			$this->p = t3lib_div::GPvar($this->prefixId);

            $this->view->setTemplate($regformTemplate);
            $this->regForm();
        }

        return $this->pi_wrapInBaseClass($this->view->fetch());
    }

    function commaListToArray($str, $unique = false) {
        $arrOut = array();
        $arr = explode(",",$str);
        foreach ($arr as $a) {
            if( preg_match("#[a-zA-Z0-9]+#", $a) )
			{
                $arrOut[] = $a;
            }
        }
        return $unique ? array_unique($arrOut) : $arrOut;
    }

    function arrayToCommaList($arr, $unique = false) {
        if($unique) {
            $arr = array_unique($arr);
        }
        return implode(",", $arr);
    }

    function ajaxZones() {
        if(false && !$this->isPostMethod()) {
            return $this->_ajaxResponse("<error>Invalid request type</error>");
        }
        if(!isset($_REQUEST['c'])) {
            return $this->_ajaxResponse("<error>Invalid request c field</error>");
        }
        $id = addslashes(strtoupper(trim(strval($_REQUEST['c']))));

        $country = $this->getService("CountryService");
        $zones = $country->getCountryZones($id);
        $this->view->setTemplate("ajaxzones.php");
        $this->view->assign('zones', $zones);
        $data = $this->view->fetch();
        return $this->_ajaxResponse($data);
    }

    function ajaxPrices() {
		if(!$this->isPostMethod()) {
			return $this->_ajaxResponse("<error>Invalid request type</error>");
		}

		// test helper
		// $_POST					= $_GET;

		if(!isset($_POST['ids'], $_POST['pc'], $_POST['bp'])) {
			return $this->_ajaxResponse("<error>Invalid request ids-pc-bp</error>");
		}

        $this->stub = $stub = $this->getService("StubService");
        $this->codes = $stub->priorityCodes;
        $user = $this->getService("UserService");
        $userCode = strtoupper(trim(strval($_POST['pc'])));
        if(isset($this->codes[$userCode])) {
            $pCode = $userCode;
        } else {
            $pCode = $user->getPcodeByCurrentUser();
        }

        $ids = $this->commaListToArray($_POST['ids'], true);

        // MLC 20080129 only purchase item once
        $buyPro = intval($_POST['bp']);
        $courses = $stub->getCoursesByUid($ids, $pCode);

        $this->view->setTemplate('ajaxprices.php');
        $this->view->assign('proprice', $stub->getProPrice($pCode, $buyPro));
        $this->view->assign('courses', $courses);
        $this->view->assign('buypro', $buyPro);
        $this->view->assign("logged", $user->isFeUserLogged());
        $data = $this->view->fetch();
        return $this->_ajaxResponse($data);
    }

    function ajaxAutoBuypro() {
        if(!$this->isPostMethod()) {
            return $this->_ajaxResponse("<error>Invalid request type</error>");
        }
        if(!isset($_POST['pc'])) {
            return $this->_ajaxResponse("<error>Invalid request pc</error>");
        }

        $this->stub = $stub = $this->getService("StubService");
        $this->codes = $stub->priorityCodes;
        $user = $this->getService("UserService");
        $userCode = strtoupper(trim(strval($_POST['pc'])));

        if(isset($this->codes[$userCode])) {
            $pCode = $userCode;
        } else {
            $pCode = $user->getPcodeByCurrentUser();
        }

        $this->view->setTemplate('ajaxbuypro.php');
        $this->view->assign('autoBuypro', $stub->getAutoBuypro($pCode));
        $data = $this->view->fetch();
        return $this->_ajaxResponse($data);
    }

    function getPriorityCode() {
        if(!isset($_GET['pc'])) {
            return '';
        }
        $userCode = strtoupper(trim(strval($_GET['pc'])));
        if(isset($this->codes[$userCode])) {
            return $userCode;
        }
        return '';
    }

    function buildFieldRules($isLogged) {
        $arrFields = array();
        if(false && !$isLogged) {
            $arrFields['username'] = array('title'=>"Username", 'mandatory' => true, "type"=>"formstring");
            $arrFields['password'] = array('title'=>"Password", 'mandatory' => true, "type"=>"formstring");
            $arrFields['password1'] = array('title'=>"Password (confirm)", 'mandatory' => true, "type"=>"formstring");
        }

        $arrCourses = array();
        for($i=0,$iCount = count($this->courses); $i<$iCount; $i++) {
            $arrCourses['course_'.strval($i+1)] = array('title'=>$this->courses[$i]['title'], 'mandatory' => false, "type"=>"salesforceid" );
        }

        $arrFields = array_merge($arrFields, $arrCourses);
        $arrFields = array_merge($arrFields, array (
			'first_name' => array('title'=>"First Name", 'mandatory' => true, "type"=>"formstring"),
			'last_name' => array('title'=>"Last Name", 'mandatory' => true, "type"=>"formstring"),
			'title' => array('title'=>"Title", 'mandatory' => true, "type"=>"formstring"),
			'company' => array('title'=>"Company", 'mandatory' => true, "type"=>"formstring"),
			'department' => array('title'=>"Department", 'mandatory' => false, "type"=>""),
			'address' => array('title'=>"Address", 'mandatory' => true, "type"=>"formstring"),
			'country' => array('title'=>"Country", 'mandatory' => true, "type"=>"formstring"),
			'city' => array('title'=>"City", 'mandatory' => true, "type"=>"formstring"),
			'phone' => array('title'=>"Phone", 'mandatory' => true, "type"=>"formstring"),
			'zip' => array('title'=>"Zip", 'mandatory' => true, "type"=>"zip"),
			'fax' => array('title'=>"Fax", 'mandatory' => false, "type"=>""),
			'bmail' => array('title'=>"Business email", 'mandatory' => true, "type"=>"email"),
			'pmail' => array('title'=>"Preferred email", 'mandatory' => true, "type"=>"email"),
			'adminmail' => array('title'=>"Administrative email", 'mandatory' => false, "type"=>"email"),
			'ccnumber' => array('title'=>"Credit card number", 'mandatory' => true, "type"=>"ccnumber"),
			'cholder' => array('title'=>"Credit card holder", 'mandatory' => true, "type"=>"formstring"),
			'expmonth' => array('title'=>"Expiration month", 'mandatory' => false, "type"=>"intc"),
			'expyear' => array('title'=>"Expiration year", 'mandatory' => false, "type"=>"intc"),
			'buypro' => array('title'=>"Buy pro account", 'mandatory' => false, "type"=>""),
			'pc' => array('title'=>"Pcode", 'mandatory' => false, "type"=>"pcode"),
			'state' => array('title'=>"state", 'mandatory' => false, "type"=>"state"),
			'conf_series' => array('title'=>"Conference series", 'mandatory' => false, "type"=>"formstring"),
        )
        );

        $this->fldRules = $arrFields;
    }

    function gatherPriceData($formData) {
        $arrCodes = array();
        foreach (array_keys($formData) as $key) {
            if(preg_match('/course_[\d]+/i',$key)) {
                $arrCodes[] = $formData[$key];
            }
        }

		// MLC 20080305 prevent duplicate billing
        $arrCodes = array_unique( $arrCodes );

        $this->coursesPicked = $this->stub->getCoursesByUid($arrCodes, $formData['pc']);
        $totalPrice = 0.0;
        foreach ($this->coursesPicked as $c) {
            $totalPrice = bcadd($totalPrice, $c['price'], 3);
        }
        if($formData['buypro']) {
            $proPrice = $this->stub->getProPrice($formData['pc'], $formData['buypro']);
			$this->proPrice = $proPrice;
            $totalPrice = bcadd($totalPrice, $proPrice, 3);
        }
        $arrOut = array(
        'courses' => $this->coursesPicked,
        'pro' => $formData['pc'],
        'price' => $totalPrice,
        'userData' => $formData,
        );
        return $arrOut;
    }

    function regForm() {
        $this->stub = $stub = $this->getService("StubService");
        $this->codes = $stub->priorityCodes;
		$this->confSeries = $this->stub->confSeries;
		$this->confSeriesTrainingOnly = $this->stub->confSeriesTrainingOnly;
        $country = $this->getService("CountryService");
        $this->user = $user = $this->getService("UserService");
        $this->courses = $stub->getCoursesWithPrices($this->getPriorityCode());

        $this->buildFieldRules($user->isFeUserLogged());

        $this->view->assign("pc", $user->getPcodeByCurrentUser());
        $this->view->assign("countries", $country->getCountries());
        $this->view->assign("courses", $this->courses);
        $this->view->assign("years", $stub->getYears());
        $this->view->assign("months", $stub->getMonths());

        $this->view->assign("logged", $user->isFeUserLogged());
        $this->view->assign("confSeries", $this->confSeries);
        $this->view->assign("confSeriesTrainingOnly", $this->confSeriesTrainingOnly);
        $this->view->assign("professional", $user->isCurrentFeUserProfessionalMember());
        $this->view->assign("firstRun", 0);

		// MLC 20081202 recaptcha
		if ((is_object($this->p) || is_array($this->p))
			&& array_key_exists('submitted', $this->p)) {
			$status = $this->recaptcha->validateReCaptcha();
			if ($status['verified']) {
				// no error
				$recaptcha = $this->recaptcha->getReCaptcha();
			} else {
				// error
				$recaptcha = $this->recaptcha->getReCaptcha($status['error']);
            	$this->view->addError('Please reenter the captcha field');
			}
		} else  {
			// not called yet
			$recaptcha = $this->recaptcha->getReCaptcha();
		}
    	
		$this->view->assign('recaptcha', $recaptcha);
		$this->view->assign('prefixId', $this->prefixId);

        if($this->hasStepInfo(1,"form")) {
            $data				= $this->getStepInfo(1,"form");
            $this->view->assign('form', $data);
        } else {
			$data				= array();
		}

        if($user->isFeUserLogged()) {
            $userData			= $user->getCurrentFeUserRecord();
            $this->view->assign("loggedUser", $userData);
			$data				= array_merge( $userData, $data );
		}

        if(!$this->isPostMethod()) {
        	// $this->view->assign("firstRun", 1);
            return;
        }

        $formName = 'bsg_step_1';
        if(!isset($_POST[$formName])) {
            $this->view->addError('Invalid POST data');
            return false;
        }

        $form = $this->trimPostData($formName);

        $res = $this->validate($form, $this->fldRules);
        $this->view->assign('form',$res['data']);

        $payData = $this->gatherPriceData($res['data']);

        if(!count($payData['courses']) || (count($payData['courses']) == 1 && in_array(0, $payData['courses']))) {
            $this->view->addError('Please select at least one course!');
        }

        // MLC 20080129 no cost, no credit card needed
        // EL 20080131 added bccomp for safety
        if ( 0 == bccomp($payData['price'], "0.0", 3))
        {
            // look up cc fields in errors_flds
            $cholderIndex	= array_search( 'cholder', $res[ 'errors_flds' ] );
            $ccnumberIndex	= array_search( 'ccnumber', $res[ 'errors_flds' ] );

            // remove cc errors from errors_flds
            unset( $res[ 'errors' ][ $cholderIndex ] );
            unset( $res[ 'errors' ][ $ccnumberIndex ] );
            unset( $res[ 'errors_flds' ][ $cholderIndex ] );
            unset( $res[ 'errors_flds' ][ $ccnumberIndex ] );

            // unset errors and errors_flds if empty
            if ( empty( $res[ 'errors' ] ) )            {
                unset( $res[ 'errors' ] );
            }

            if ( empty( $res[ 'errors_flds' ] ) )            {
                unset( $res[ 'errors_flds' ] );
            }
        }

        if($this->view->hasErrors()) {
            $this->view->assign('form',$res['data']);
            return;
        }
        
        /**
         * Error processing
         */
        if(isset($res['errors'])) {
            $this->view->assign('form',$res['data']);
            foreach ($res['errors'] as $err) {
                $this->view->addError($err);
            }
            if(isset($res['errors_flds'])) {
                $email = isset($res['data']['pmail']) ? $res['data']['pmail'] : '';
                $user->addUserErrors ($res['errors_flds'], $email);
            }
            return;
        }

        /**
         * Success
         */        
        $lpData = $this->processPayment($payData);

        if($this->view->hasErrors()) {
            $this->view->assign('form',$res['data']);
            return;
        }

        // MLC 20080129 add courses registered for to user
        $courses = $this->coursesAsString($payData['courses']);
        $res['data']['courses']	= $courses;
        $record['courses']	= $courses;

		// record is what'll be pushed to actual user record in database
		// key names are important to prevent sql errors
        $record = $this->formDataToUserRecord($res['data']);

		$nonLoggedInMember		= false;
		
		// MLC 20080318 look for related member record
		if ( ! $user->isFeUserLogged() ) {
			// look for active member record by email
            $userData			= $user->getFeUserByEmail( $record['email'] );

			// if found, load it into $data
			if ( $userData )
			{
				$nonLoggedInMember	= true;
				$data				= array_merge( $userData, $data );
			}
		}

        // MLC 20080129 update modify time
        $time					= time();
        $record['tstamp'] 		= $time;
		$coursesArray			= array();

		foreach ( $payData['courses'] as $key => $value )
		{
			$coursesArray[]	= $value['uid'];
		}
        $record['courses_list']	= $this->arrayToCommaList($coursesArray, true);

		// usergroup levels
		$proguest				= $user->getProfessionalGuestGroupId();
		$free					= $user->getMemberGroupId();
		$pro					= $user->getProfessionalGroupId();

		// grant new user complimentary access
		if ( ! $user->isFeUserLogged() && ! $nonLoggedInMember )
		{
			$record['usergroup']	= $free;
		}
		// load current usergroup if member
		else
		{
			$record['usergroup']	= $data['usergroup'];
		}

		// MLC 20080219 set pro membership endtime
		// $record is data pushed into fe_users directly
		if ( $res['data']['buypro'] ) {
			// set usergroup to pro as needed
			// non-member pro set
			if ( ! $user->isFeUserLogged() && ! $nonLoggedInMember )
			{
				// set baseline starttime
				$record['starttime']	= $time;

				// set year added endtime
				$record['endtime']		= $time;

				$record['usergroup']	= $pro;
			}
			// member pro set
    		else
			{
				// set baseline starttime
				$starttime			= $data['starttime'];
				$record['starttime']	+= $starttime
											? $starttime
											: $time;

				// set year added endtime
				$endtime			= $data['endtime'];
				$record['endtime']	= $endtime
										? $endtime
										: $time;

				$usergroup		= $data['usergroup'];
				$usergroup		= explode( ',', $usergroup );

				// usergroup upgrade handling
				foreach ( $usergroup as $key => $value )
				{
					if ( $comp == $value || $free == $value )
					{
						$usergroup[ $key ]	= $pro;
					}
				}

				$usergroup		= implode( ',', $usergroup );
				$record['usergroup']	= $usergroup;
			}

			$record['endtime']	+= ( 60 * 60 * 24 * 365 );
		}

		// immediate conference presentations access
		$record['usergroup']	.= ',' . BSG_REG_CONF_USERGROUP;

		// prevent usergroup duplicates
		$usergroup				= $record['usergroup'];
		$usergroup				= explode( ',', $usergroup );
		$usergroup				= array_unique( $usergroup );
		$usergroup				= implode( ',', $usergroup );
		$record['usergroup']	= $usergroup;

		$res['data']			= array_merge( $record, $res['data'] );

        if( $user->isFeUserLogged() ) {
            $result = $user->updateUser($user->getCurrentFeUserId(), $record);
			$record['username'] = $data['username'];
			$record['password'] = $data['password'];
			$record['uid'] = $user->getCurrentFeUserId();
        } elseif ( $nonLoggedInMember ) {
            $result = $user->updateUser($data['uid'], $record);
			$record['username'] = $data['username'];
			$record['password'] = $data['password'];
			$record['uid'] = $data['uid'];
        } else {
            $this->setStepInfo(1, "form", $form);

			$record['crdate'] = $time;
           	$record['pid'] 		= $user->getMemberPid();
			// $record['username'] = $user->generateUsername();
			$record['username'] = $res['data']['pmail'];
			$record['password'] = $user->generatePassword();
			$result = $user->addUser($record);
			$record['uid'] = $result;
        }

		$res['data']			= array_merge( $record, $res['data'] );
		
		$this->sendConfirmation($record, $payData);

		// MLC 20080221 push record data to salesforce
		$record[ 'totalPrice' ]	= $payData['price'];
		$this->sendSalesforce( $record );

		$this->sendRegistrationToAdmin(
			$res['data']
			, $payData
			, $lpData
			, ! $user->isFeUserLogged()
		);

        // MLC 20080130 show course agenda on thank you page
        $coursesAsHtml	= $record['courses'];
        $coursesAsHtml	= preg_replace( "# - .*#", '', $coursesAsHtml );
        $coursesAsHtml	= nl2br( $coursesAsHtml );
        $coursesAsHtml	= str_replace( "\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $coursesAsHtml );
        $this->view->setTemplate( 'success.php');
        $this->view->assign("courses", $coursesAsHtml);
        $this->view->assign("confCity", BSG_REG_CONF_CITY);
        $this->view->assign("isNewMember", ($time == $record['crdate']));
        $this->view->assign("user", $record['username']);
        $this->view->assign("pass", $record['password']);

        return;
    }

	function lookupConferenceSeries( $confId )
	{
		if ( $confId ) {
			$confSeries				= array_flip( $this->confSeries );
			return $confSeries[$confId];
		} else {
			return 'Training only';
		}
	}

	// MLC 20080221 push record data to salesforce
	function sendSalesforce( $record )
	{
		$sfdc					= $this->salesforceLogin( $record );
		// create account link
		$sfaid					= $this->salesforceAccount( $record );
		$sfuid					= $this->getSalesforceId( $record );

		$record['AccountId']	= $sfaid;
		$sfContact				= $this->convertToSfContact( $record );

		$this->sfuid			= $this->salesforceContact( $sfuid, $sfContact );

		$this->loadCoursesArray( $record['priority_code'] );
		$courses				= $this->salesforceCoursesArray( $this->coursesPicked );

		// create training links
		if ($this->cbDebug) {
			cbDebug( 'training', '' );
			cbDebug( 'courses', $courses );
			cbDebug( 'this->stub->courseTypeTraining', $this->stub->courseTypeTraining );
		}
		$this->salesforceTrainingLinks( $courses[$this->stub->courseTypeTraining] );

		// create attendee status label
		$record['Reg_Class']	= $this->salesforceRegClass( $courses );

		// figure out reg type
		$record['reg_type__c']	= 'Attendee';

		if ($this->cbDebug) cbDebug( 'record', $record );	

		if ($this->cbDebug) {
			cbDebug( 'events', '' );
			cbDebug( 'this->stub->courseTypeEven', $this->stub->courseTypeEven );
		}
		// create event links
		$this->salesforceEventLinks( $courses[$this->stub->courseTypeEvent]
			, $record
		);

		// create conference series link
		if ($this->cbDebug) cbDebug( 'conf link', '' );
		$this->salesforceConferenceLinks( $courses, $record );

		// create membership links
		$this->salesforceMembershipLinks( $record );

		return;
	}

	function salesforceMembershipLinks( $record ) {
		$expire				= $record['endtime']
								? date('Y-m-d', $record['endtime'])
								: '';

		$isPro				= $this->proPrice || $this->user->isCurrentFeUserProfessionalMember();
		if ($this->cbDebug) cbDebug( 'isPro', $isPro, true );	

		$memberArr			= array(
								'Contact__c' => $this->sfuid
								, 'Cost__c' => $this->proPrice . ''
								, 'Expiration_Date__c' => $expire
								, 'Membership__c' => 'a0730000000OIsg'
									/*
									default BPMInstitute.org a0730000000OIsg
									SOAInstitute.org a0730000000OIsl
									BrainStorm Group a0730000000OIsb
									*/
								, 'Type__c' => $isPro ? 'Pro' : 'Member'
									/*
									Pro
									Member
									*/
								, 'Typo_Uid__c' => $record['uid'] . ''
							);
		if ($this->cbDebug) cbDebug( 'memberArr', $memberArr );	

		$sql				= "select Id,Expiration_Date__c,Active__c,Cost__c,Membership__c,Type__c,Typo_Uid__c from Membership_Link__c where Contact__c = '{$this->sfuid}'";
		if ($this->cbDebug) cbDebug( 'sql', $sql );	
		$result				= $this->sfdc->query( $sql );

		if ($this->cbDebug) cbDebug( 'result', $result );	
		$linkId				= false;

		if ( isset( $result['records'] ) ) {
			$linkId = $result['records']->id;
		}
		if ($this->cbDebug) cbDebug( 'linkId', $linkId, true );	

		if ( $linkId ) {
			unset($memberArr['Contact__c']);

			$memberObject		= new SObject('Membership_Link__c', $linkId, $memberArr);
			$sResult		= $this->sfdc->update($memberObject);
		} else {
			$memberObject		= new SObject('Membership_Link__c', null, $memberArr);
			$sResult		= $this->sfdc->create($memberObject);
		}
		if ($this->cbDebug) cbDebug( 'sResult', $sResult );	

		if ( false && cbDoDebug() )
		{
			cbDebug( 'salesforceMembershipLinks', 'exit' );	
			exit();
		}
	}
	
	function salesforceRegClass( $courses )
	{
		$regClass				= '';
		$conferences			= $courses[$this->stub->courseTypeConference];
		$confType				= ( isset( $conferences[0]['uid'] ) )
									? preg_replace( "#-\w+#", '', $conferences[0]['uid'])
									: false;
		$confType1				= ( isset( $conferences[1]['uid'] ) )
									? preg_replace( "#-\w+#", '', $conferences[1]['uid'])
									: false;
		$twoDays				= ( 'DAY1' == $confType && 'DAY2' == $confType1 )
									? true
									: false;

		if ( $this->cbDebug ) {
			cbDebug( 'confType', $confType, true );
			cbDebug( 'confType1', $confType1, true );
			cbDebug( '$twoDays', $twoDays, true );
		}

		$hasConferences			= ( 0 < count( $conferences ) );
		$hasEvents				= ( 0 < count( $courses[$this->stub->courseTypeEvent] ) );
		$hasTraining			= ( 0 < count( $courses[$this->stub->courseTypeTraining] ) );

		switch( true )
		{
			case ! $hasConferences && ! $hasEvents && $hasTraining:
				$regClass	= 'TRAINING ONLY';
				break;

			case ! $hasConferences && $hasEvents && ! $hasTraining:
				$regClass	= 'SEMINAR ONLY';
				break;

			case ! $hasConferences && $hasEvents && $hasTraining:
				$regClass	= 'TRAINING & SEMINAR';
				break;

			case $hasConferences && ! $hasEvents && ! $hasTraining:
				if ( $twoDays || '2DAYS' == substr( $confType, 0, 5 ) )
					$regClass	= '2 DAYS';
				elseif ( 'HDAY1' == $confType && ! $confType1 )
					$regClass	= '1/2 DAY ONLY';
				elseif ( 'HDAY1' == $confType && $confType1 )
					$regClass	= '1-1/2 DAY';
				elseif ( 'DAY1' == $confType )
					$regClass	= 'DAY 1 ONLY';
				else
					$regClass	= 'DAY 2 ONLY';
				break;

			case $hasConferences && ! $hasEvents && $hasTraining:
				if ( $twoDays || '2DAYS' == substr( $confType, 0, 5 ) )
					$regClass	= '2 DAY & TRAINING';
				elseif ( 'HDAY1' == $confType && ! $confType1 )
					$regClass	= '1/2 DAY ONLY & TRAINING';
				elseif ( 'HDAY1' == $confType && $confType1 )
					$regClass	= '1-1/2 DAY & TRAINING';
				elseif ( 'DAY1' == $confType )
					$regClass	= 'DAY 1 & TRAINING';
				else
					$regClass	= 'DAY 2 & TRAINING';
				break;

			case $hasConferences && $hasEvents && ! $hasTraining:
				if ( $twoDays || '2DAYS' == substr( $confType, 0, 5 ) )
					$regClass	= '2 DAYS & SEMINAR';
				elseif ( 'HDAY1' == $confType && ! $confType1 )
					$regClass	= '1/2 DAY ONLY & SEMINAR';
				elseif ( 'HDAY1' == $confType && $confType1 )
					$regClass	= '1-1/2 DAY & SEMINAR';
				elseif ( 'DAY1' == $confType )
					$regClass	= 'DAY 1 & SEMINAR';
				else
					$regClass	= 'DAY 2 & SEMINAR';
				break;

			case $hasConferences && $hasEvents && $hasTraining:
				if ( $twoDays || '2DAYS' == substr( $confType, 0, 5 ) )
					$regClass	= '2 DAYS & TRAINING & SEMINAR';
				elseif ( 'HDAY1' == $confType && ! $confType1 )
					$regClass	= '1/2 DAY ONLY & TRAINING & SEMINAR';
				elseif ( 'HDAY1' == $confType && $confType1 )
					$regClass	= '1-1/2 DAY & TRAINING & SEMINAR';
				elseif ( 'DAY1' == $confType )
					$regClass	= 'DAY 1 & TRAINING & SEMINAR';
				else
					$regClass	= 'DAY 2 & TRAINING & SEMINAR';
				break;

			case false:
				$regClass	= 'RegClass';
				$regClass	= 'BOOTH STAFF';
				$regClass	= 'PANEL SPEAKER';
				$regClass	= 'PRESS';
				$regClass	= 'VIP';
				$regClass	= 'SPONSOR STAFF';
				$regClass	= 'SPEAKER';
			break;
		}

		return $regClass;
	}

	function salesforceConferenceLinks( $courses, $record )
	{
		$conferences			= $courses[$this->stub->courseTypeConference];
		$hasConferences			= ( 0 < count( $conferences ) );
		$hasEvents				= ( 0 < count( $courses[$this->stub->courseTypeEvent] ) );
		$hasTraining			= ( 0 < count( $courses[$this->stub->courseTypeTraining] ) );

		if ( $this->cbDebug ) {
			cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			cbDebug( 'courses', $courses );
			cbDebug( 'hasConferences', $hasConferences, true );
			cbDebug( 'hasEvents', $hasEvents, true );
			cbDebug( 'hasTraining', $hasTraining, true );
		}

		if ( ! $hasConferences && $hasEvents && ! $hasTraining ) {
			return;
		}

		// figure out reg type
		$Reg_Class__c			= $record['Reg_Class'];
		if ( $this->cbDebug ) {
			cbDebug( 'Reg_Class__c', $Reg_Class__c, true );
		}

		$baseEventLink			=  array(
									'Brainstorm_Event__c' => null
									, 'Contact__c' => $this->sfuid
									, 'Reg_Class__c' => $Reg_Class__c
									, 'reg_type__c' => $record['reg_type__c']
									, 'payment_status__c' => 'Registered'
									, 'Source_Code__c' => $record['priority_code']
								);

		foreach ( $conferences as $conference ) {
			if ( 'TRAINING ONLY' != $Reg_Class__c ) {
				// $conf_series	= $record['conf_series'];
				$conf_series	=  preg_replace( "#\w+-#", '', $conference['uid']);
				$price			= $conference['price'];
			} else {
				$conf_series	= $this->confSeriesTrainingOnly;
				$price			= '';
			}

			// create event links
			$baseEventLink['Brainstorm_Event__c'] = $conf_series;
			$baseEventLink['Amount__c'] = $price . '';
			$baseEventLink['Total_Amount__c'] = $record[ 'totalPrice' ].'';
			if ($this->cbDebug) cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			if ($this->cbDebug) cbDebug( 'baseEventLink', $baseEventLink );	

			$sobject				= new SObject('Brainstorm_Event_Link__c'
										, null
										, $baseEventLink
									);
			$result					= $this->sfdc->create($sobject);
			if ($this->cbDebug) cbDebug( 'result', $result );	
		}

		if ( $hasTraining && 'TRAINING ONLY' != $Reg_Class__c )
		{
			$conf_series		= $this->confSeriesTrainingOnly;
			$price				= '';

			// create training course links
			$baseEventLink['Brainstorm_Event__c'] = $conf_series;
			$baseEventLink['Amount__c'] = '';
			$baseEventLink['Total_Amount__c'] = '';
			if ($this->cbDebug) cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			if ($this->cbDebug) cbDebug( 'baseEventLink', $baseEventLink );	

			$sobject			= new SObject('Brainstorm_Event_Link__c'
									, null
									, $baseEventLink
								);
			$result				= $this->sfdc->create($sobject);
			if ($this->cbDebug) cbDebug( 'result', $result );	
		}
	}

	function salesforceContact( $sfuid, $sfContact )
	{
		// found user, update sf record
		if ( $sfuid )
		{
			$contact			= new SObject('Contact', $sfuid, $sfContact );
			$contactResult		= $this->sfdc->update($contact);
		}

		// not found user, create sf user
		else
		{
			$contact			= new SObject('Contact', null, $sfContact );
			$contactResult		= $this->sfdc->create($contact);
			$sfuid				= $contactResult['id'];
		}

		return $sfuid;
	}

	function salesforceCoursesArray( $courses ) {
		$courseBreakdown		= array(
									$this->stub->courseTypeTraining => array()
									, $this->stub->courseTypeEvent => array()
									, $this->stub->courseTypeConference => array()
								);

		if ( 0 == count( $courses ) ) {
			return $courseBreakdown;
		}

		// cycle through courses, figure out which belongs to which category
		$trainingKeys			= array_keys( $this->trainingCourses );
		$conferenceKeys			= array_keys( $this->conferenceCourses );

		foreach ( $courses as $course ) {
			if ( in_array( $course['uid'], $trainingKeys ) ) {
				$courseBreakdown[$this->stub->courseTypeTraining][]	= $course;
			} elseif ( in_array( $course['uid'], $conferenceKeys ) ) {
				$courseBreakdown[$this->stub->courseTypeConference][]	= $course;
			} else {
				$courseBreakdown[$this->stub->courseTypeEvent][]	= $course;
			}
		}
		
		return $courseBreakdown;
	}
	
	function loadCoursesArray( $pc )
	{
		// load from config or service
        $courses				= $this->stub->getCoursesBreakdown( $pc );
		$this->conferenceCourses	= $courses[$this->stub->courseTypeConference];
		$this->eventCourses		= $courses[$this->stub->courseTypeEvent];
		$this->trainingCourses	= $courses[$this->stub->courseTypeTraining];
	}

	function salesforceEventLinks( $courses, $record )
	{
		if ( 0 == count( $courses ) )
		{
			return;
		}

		$baseEventLink			=  array(
									'Brainstorm_Event__c' => null
									, 'Contact__c' => $this->sfuid
									, 'Reg_Class__c' => $record['Reg_Class']
									, 'reg_type__c' => $record['reg_type__c']
									, 'payment_status__c' => 'Registered'
									, 'Source_Code__c' => $record['priority_code']
								);

		// create training course links
		foreach ( $courses as $key => $course )
		{
			$baseEventLink['Brainstorm_Event__c'] = $course['uid'];
			$baseEventLink['Amount__c'] = $course['price'] . '';
			if ($this->cbDebug) cbDebug( 'baseEventLink', $baseEventLink );	

			$sobject			= new SObject('Brainstorm_Event_Link__c'
									, null
									, $baseEventLink
								);
			$result				= $this->sfdc->create($sobject);
			if ($this->cbDebug) cbDebug( 'result', $result );	
		}
	}

	function salesforceTrainingLinks( $courses )
	{
		if ( 0 == count( $courses ) )
		{
			return;
		}

		// figure out reg type
		$Reg_Type__c			= 'Attendee';

		$baseTrainingLink		=  array(
									'Reg_Type__c' => $Reg_Type__c
									, 'Contact__c' => $this->sfuid
									, 'Brainstorm_Training__c' => ''
								);

		// create training course links
		foreach ( $courses as $key => $course)
		{
			$baseTrainingLink['Brainstorm_Training__c'] = $course['uid'];
			$baseTrainingLink['Amount__c'] = $course['price'] . '';
			if ($this->cbDebug) cbDebug( 'baseTrainingLink', $baseTrainingLink );	
			$sobject			= new SObject('TrainingContact_Link__c'
									, null
									, $baseTrainingLink
								);
			$result				= $this->sfdc->create($sobject);
			if ($this->cbDebug) cbDebug( 'result', $result );	
		}
	}

	function salesforceAccount( $record )
	{
	    $sql					= "select Id from Account where Name='{$record['company']}' order by CreatedDate asc limit 1";
	    $accountQuery			= $this->sfdc->query( $sql );

		if ( isset( $accountQuery['records'] ) )
		{
			$AccountId			= $accountQuery['records']->id;
		}
		else
		{
			$accountObject		= new SObject('Account', null, array(
									'Name' => $record['company']
									, 'OwnerId' => $this->sfoid
								));
			$createResult		= $this->sfdc->create($accountObject);
			$AccountId			= $createResult['id'];
		}

		return $AccountId;
	}
	
	function convertToSfContact( $data )
	{
		$arrSubstitutes			= array(
			'FirstName' => 'first_name'
			, 'LastName' => 'last_name'
			, 'Title' => 'title'
			, 'Department' => 'department'
			, 'MailingCountry' => 'country'
			, 'MailingStreet' => 'address'
			, 'MailingCity' => 'city'
			, 'MailingState' => 'zone'
			, 'MailingPostalCode' => 'zip'
			, 'Phone' => 'telephone'
			, 'Fax' => 'fax'
			, 'Email' => 'email'
			, 'AssistantPhone' => 'adminmail'
			, 'Secondary_E_Mail__c' => 'bmail'
			, 'Dear__c' => 'dear'
			, 'AccountId' => 'AccountId'
			, 'Typo_Uid__c' => 'uid'
		);

        $arrOut					= array();
        foreach ($arrSubstitutes as  $dbRecord => $fldRecord)
		{
            if ( isset( $data[$fldRecord] ) )
			{
                $arrOut[$dbRecord] = '' . $data[$fldRecord];
            }
        }

		$arrOut['OwnerId']		= $this->sfoid;

		// TODO
		// , 'ID_Status__c' => 'status logic'
		$arrOut['NewsLetter__c']	= true;

        return $arrOut;
	}

	function getSalesforceId( $record )
	{
		$sfuid					= false;
		$t3uid					= false;

		// check to see if sf id already exists
        if ( $this->user->isFeUserLogged() )
		{
            $userData			= $this->user->getCurrentFeUserRecord();
			$sfuid				= ( '' != $userData[ 'tx_cbsalesforce_salesforceid' ] )
									? $userData[ 'tx_cbsalesforce_salesforceid' ]
									: false;
			$t3uid				= $userData['uid'];
		}

		// try typo3 fe_users.uid first to prevent duplicate error on create
		if ( $t3uid )
		{
			$sql				= "select Id from Contact where Typo_Uid__c = '{$t3uid}'";
			$result				= $this->sfdc->query( $sql );

			if ( isset( $result['records']->id ) )
			{
				return $result['records']->id;
			}
		}

		// check that salesforce user actually exists and accessible
		if ( $sfuid )
		{
			$sql				= "select Id from Contact where id = '{$sfuid}'";
			$result				= $this->sfdc->query( $sql );

			if ( isset( $result['records']->id ) )
			{
				return $result['records']->id;
			}
		}

		// look for current user
		// use user email to find record
		$where					= "email = '{$record['email']}'";
		$sql					= "select Id, Name from Contact where {$where} order by CreatedDate asc limit 1";
		$result					= $this->sfdc->query( $sql );

		// use typo3 salesforce user id
		$sfuid					= ( isset( $result['records'] ) )
									? $result['records']->id
									: false;

		return $sfuid;
	}

	function salesforceLogin( $userData )
	{
		// get salesfoces instance
		$this->sfdc				= new salesforce(t3lib_extMgm::extPath('salesforce_subscribe').'/pi1/partner.wsdl');
		$loginResult			= $this->sfdc->login($this->sfUsername, $this->sfPassword);
		if ( 'true' == $loginResult['passwordExpired'] ) {
			$msg = 'Please send the new Salesforce Password for user ' . SF_USERNAME . ' to support@peimic.com.';
			$msg .= "\n\n" . 'Currently Salesforce and TYPO3 are not connecting.  Error follows.';
			$msg .= "\n\n" . print_r($loginResult, true);
			$msg .= "\n\n" . 'Missing Salesforce record follows.';
			$msg .= "\n\n" . print_r($userData, true);
			$header = 'From: support+bsg@peimic.com';
			mail($loginResult['userInfo']['userEmail'], 'BSG: Salesforce Password Expired', $msg, $header);
			mail('support+bsg@peimic.com', 'BSG: Salesforce Password Expired', $msg, $header);
			// echo cbMail2html($loginResult['userInfo']['userEmail'], 'BSG: Salesforce Password Expired', $msg, $header);
		}

		$batchSize = new soapval('batchSize', null, 2);
		$this->sfdc->setHeader('QueryOptions', array($batchSize));

		return $this->sfdc;
	}

    function processPayment($payData) {
        // MLC 20080129 don't process no charge transactions
        // EL 20080131 added bccomp for safety
        if ( 0 == bccomp($payData['price'], "0.0", 3))
        {
            return 'FREE';
        }

        $result = $this->lp->preAuth(floatval($payData['price']), $payData['userData']);
        if(!is_array($result)) {
            $this->view->addError('Connection error: '.$result);
            return false;
        } elseif(!isset($result['r_approved']) || $result['r_approved'] !== 'APPROVED') {
            $this->view->addError('Please contact your credit card company to verbally authorize this transaction and then resubmit.');
            return false;
        }
        $paymentCode = $result['r_ordernum'];


        $result = $this->lp->postAuth(floatval($payData['price']), $paymentCode, $payData['userData']);

        if(!is_array($result)) {
            $this->view->addError('Connection error: '.$result);
            return false;
        } elseif(!isset($result['r_approved']) || $result['r_approved'] !== 'APPROVED') {
            $this->view->addError($result['r_error']);
            return false;
        }

        $result['transaction_code_1'] = $paymentCode;
        return $result;
    }

    function formDataToUserRecord($data) {
        $arrSubstitutes = array(
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'address' => 'address',
        'telephone' => 'phone',
        'fax' => 'fax',
        'email'  => 'pmail',
        'adminmail'  => 'adminmail',
        'zip' => 'zip',
        'city' => 'city',
        'country' => 'country',
        'username' => 'username',
        'password' => 'password',
        'bmail' => 'bmail',
        'department' => 'department',
        'title' => 'title',
        'company' => 'company',
        /*        'expyear' => 'expyear',
        'expmonth' => 'expmonth',*/
        'zone' => 'state',
        'courses' => 'courses',
        'priority_code' => 'pc',
        'conf_series' => 'conf_series',
        );

        $arrOut = array();
        foreach ($arrSubstitutes as  $dbRecord => $fldRecord) {
            if(isset($data[$fldRecord])) {
                $arrOut[$dbRecord] = $data[$fldRecord];
            }
        }
        
        if(isset($data['expyear'], $data['expmonth'])) {
            $arrOut['cc_expiry'] = $data['expmonth'] . "/". $data['expyear'];            
        }        
        
        return $arrOut;
    }

    function getStep() {
        $index = $this->stepIndex;
        if(isset($_REQUEST[$this->prefixId][$index])) {
            $step = intval($_REQUEST[$this->prefixId][$index]);
        } elseif (isset($_SESSION[$this->prefixId][$index])) {
            $step = $_SESSION[$this->prefixId][$index];
        } else {
            $step = 1;
        }
        return $this->correctStepNumber($step);
    }

    function correctStepNumber($stepNo) {
        $stepNo = intval($stepNo);

        // Negative check
        if($stepNo <= 0) {
            $stepNo = 1;
        }
        // MaxValue check
        if($stepNo > $this->maxSteps) {
            $stepNo = $this->maxSteps;
        }

        // If = 1 nothing to check
        if($stepNo == 1) {
            return $stepNo;
        }

        for($i=1 ; $i<$stepNo; $i++) {
            if(!$this->hasStep($i)) {
                return $i;
            }
        }

        return $stepNo;
    }

    function customPrice($number) {
        return number_format($number, 2, '.', ',');
    }

    function setStepInfo($stepNo, $index, $data) {
        $_SESSION[__CLASS__]['step'.$stepNo][$index] = $data;
    }

    function hasStep($stepNo) {
        return isset($_SESSION[__CLASS__]['step'.$stepNo]);
    }

    function hasStepInfo($stepNo, $index) {
        return isset($_SESSION[__CLASS__]['step'.$stepNo][$index]);
    }

    function getStepInfo($stepNo, $index) {
        if(!$this->hasStepInfo($stepNo, $index)) {
            return false;
        }
        return $_SESSION[__CLASS__]['step'.$stepNo][$index];
    }

    function sendRegistrationToAdmin($arrData, $payData, $lpData, $autoRegister = false) {
        $this->adminEmail		= $this->conf['adminmail'];

        $msg					= array();

        $user	= $this->getService("UserService");
        $memberStatus			= ( ! $autoRegister )
									? $user->getMemberStatusByCurrentUser()
									: ! $arrData['buypro']
										? 'Member'
										: 'Professional'
								;

        if($autoRegister) {
            $msg[] = "Registrant account was automatically registered";
        }

        $msg[] = "Member status: {$memberStatus}";
		$msg[] = "Username: {$arrData['username']}";
		$msg[] = "Password: {$arrData['password']}";
        $msg[] = "";
		// MLC link to sf user record
		$msg[] = "Salesforce Link: https://na3.salesforce.com/{$this->sfuid}";

        $msg[] = "First Name: {$arrData['first_name']}";
        $msg[] = "Last Name: {$arrData['last_name']}";
        $msg[] = "Title: {$arrData['title']}";
        $msg[] = "Company: {$arrData['company']}";
        $msg[] = "Department: {$arrData['department']}";
        $msg[] = "Address: {$arrData['address']}";
        $msg[] = "City: {$arrData['city']}";
        if(!empty($arrData['state'])) {
            $msg[] = "State: {$arrData['state']}";
        }
        $msg[] = "Zip: {$arrData['zip']}";
        $msg[] = "Country: {$arrData['country']}";
        $msg[] = "Phone: {$arrData['phone']}";
        $msg[] = "Fax: {$arrData['fax']}";
        $msg[] = "Business email: {$arrData['bmail']}";
        $msg[] = "Preferred email: {$arrData['pmail']}";
        $msg[] = "Administrative email: {$arrData['adminmail']}";
        $msg[] = "";
		$conf_series	= $this->lookupConferenceSeries( $arrData['conf_series'] );
        $msg[] = "Conference series: {$conf_series}";
        $msg[] = "Selected courses:\n {$arrData['courses']}";
		if ( ! $arrData['buypro'] )
		{
        	$msg[] = "Buy pro: No";
		}
		elseif ( 2 == $arrData['buypro'] )
		{
        	$msg[] = "Buy pro renewal: Yes";
            $msg[] = "Expires " . date( 'F j, Y', $arrData['endtime'] );
		}
		else
		{
        	$msg[] = "Buy pro: Yes\n";
            $msg[] = "Expires " . date( 'F j, Y', $arrData['endtime'] );
		}
		$msg[] = '';
        $msg[] = "Priority code: {$arrData['pc']}";
        $msg[] = "Total price: $".$this->customPrice($payData['price']);
        $msg[] = "";
		// MLC 20080209 sanitize credit card
		$ccNumber				= $arrData['ccnumber'];
		$ccNumber				= substr_replace($ccNumber, 'XXXXXXXX', 4, -4);
        $msg[] = "Credit card: {$ccNumber}";
        $msg[] = "Credit card holder: {$arrData['cholder']}";
        $msg[] = "Expiration date(month/year): "
        . $payData['userData']['expmonth']
        . '/' . ( $payData['userData']['expyear'] + 2000 );

        $lpDataStr	= '';
		$lpData					= is_array( $lpData )
									? $lpData
									: array();
        foreach( $lpData as $key => $value )
        {
            $lpDataStr	.= $key . ' : '. $value . "\n";
        }
        $msg[] = "Linkpoint response: {$lpDataStr}";

        $header = null;
        if(isset($this->conf['fromEmail'])) {
            $header = 'From: '. $this->conf['fromEmail']. "\r\n" .
            'Reply-To: '. $this->conf['fromEmail'] . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        }

        @mail($this->adminEmail, "BSG: " . BSG_REG_CONF_CITY . " Registration", implode("\n", $msg), $header);
		if ( $this->cbDebug ) {
        	echo cbMail2html($this->adminEmail, "BSG: " . BSG_REG_CONF_CITY . " Registration", implode("\n", $msg), $header);
		}
    }

    function sendConfirmation($arrData, $payData) {
        $courses = $arrData['courses'];

        $buypro = isset($payData['userData']['buypro']) ? intval($payData['userData']['buypro']) : 0;
        $priorityCode = $arrData['priority_code'];
        $proAccount = '';
        if($buypro) {
            $proPrice = $this->stub->getProPrice($payData['userData']['pc'], $buypro);
			$renewal			= ( 2 != $buypro ) ? '' : 'renewal';
            $proAccount =  "Professional membership $renewal: $ ". $this->customPrice($proPrice);
            $proAccount .=  "\nExpires " . date( 'F j, Y', $arrData['endtime'] );
        }
		$login				= <<<EOD
BrainStorm BPMInstitute.org and SOAInstitute.org Access Information
Username: {$arrData['username']}
Password: {$arrData['password']}
EOD;
        $total = $this->customPrice($payData['price']);
		$conf_series	= $this->lookupConferenceSeries( $arrData['conf_series'] );

        $header = null;

        if(isset($this->conf['fromEmail'])) {
            $header .= 'From: '. $this->conf['fromEmail']. "\r\n" .
            'Reply-To: '. $this->conf['fromEmail'] . "\r\n";
        }

		$header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

		$to = $arrData['email'];

		if ( $arrData['adminmail'] ) {
			$to .=  ', ' . $arrData['adminmail'];
		}
		
        require_once(t3lib_extMgm::extPath('bsg_regsteps')."/config/" . BSG_REG_CONF_CODE . ".mail.confirm.php");
        mail($to, "BrainStorm " .  BSG_REG_CONF_CITY . ": Receipt & Confirmation", $strData, $header);
		if ( $this->cbDebug ) {
        	echo cbMail2html($to, "BrainStorm " .  BSG_REG_CONF_CITY . ": Receipt & Confirmation", $strData, $header);
		}
    }

    function coursesAsString( $coursesIn )  {
        $courses = array();
        foreach ($coursesIn as $c) {
            $courses[] = strip_tags( $c['thankyoutitle'] )
            . " "
            . strip_tags( $c['subtitle'] )
            ;
            $courses[] = "\t"
            . $c['name']
            . ": $ "
            . $this->customPrice($c['price'])
            ;
        }
        $courses = implode("\n", $courses);

        return $courses;
    }

    function clearSessionData() {
        unset($_SESSION[get_class($this)]);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bsg_regsteps/pi1/class.tx_bsgregsteps_pi1.php'])	{
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bsg_regsteps/pi1/class.tx_bsgregsteps_pi1.php']);
}

?>