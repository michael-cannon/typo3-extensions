<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Evgeniy Beskonchin (inf2k@bcs-it.com)
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
 * Plugin 'Salesforce integration' for the 'salesforce_subscribe' extension.
 *
 * @author	Evgeniy Beskonchin <inf2k@bcs-it.com>
 * @version $Id: class.tx_salesforcesubscribe_pi1.php,v 1.1.1.1 2010/04/15 10:04:00 peimic.comprock Exp $
 */


require_once(PATH_tslib."class.tslib_pibase.php");
require_once(dirname(__FILE__).'/salesforce.php');
require_once( dirname(__FILE__)."/../../salesforce-access.php");

class tx_salesforcesubscribe_pi1 extends tslib_pibase {
	var $prefixId = "tx_salesforcesubscribe_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_salesforcesubscribe_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "salesforce_subscribe";	// The extension key.
	var $templateFile='EXT:salesforce_subscribe/pi1/profile.tmpl';
	var $username = SF_USERNAME;
	var $password = SF_PASSWORD;
	var $avaiableFields=array();
	var $template;
//	var $user;

	function main($content,$conf)	{
		global $TSFE;

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;

		$content='';

		if ($conf['templateFile']) $this->templateFile= $conf['templateFile'];

	    $this->template= $this->cObj->fileResource($this->templateFile);

		switch (t3lib_div::_GP('action')){
			case 'changeInfo':
				$content= $this->changeInfo();
				break;
			case 'showInfo':
				$content= $this->getSalesInfo();
				break;
			default:
				$content= $this->showWaitingPage();
		}

		return $this->pi_wrapInBaseClass($content);
	}

	function getSalesInfo(){
// Getting salesforce information
		$sfdc = new salesforce(dirname(__FILE__).'/partner.wsdl');
		$loginResult = $sfdc->login($this->username, $this->password);

		$batchSize = new soapval('batchSize', null, 2);
		$sfdc->setHeader('QueryOptions', array($batchSize));

		$userID= t3lib_div::_GP('userID');
		$userCode=  t3lib_div::_GP('code');

		// cbDebug( 'describeGlobal', $sfdc->describeGlobal() );	
		// cbDebug( 'describeSObject Membership__c', $sfdc->describeSObject( 'Membership__c' ) );	
		// cbDebug( 'describeSObject Membership_Link__c', $sfdc->describeSObject( 'Membership_Link__c' ) );	

// Getting User information
	    $queryResult = $sfdc->query("select id, firstname, lastname, title,
		email, accountid, mailingcity, mailingstate, mailingpostalcode,
		mailingcountry, phone, mailingstreet,uid__c,HasOptedOutOfEmail from contact where id='$userID'");
		$record= $queryResult['records'];
		$code = sprintf ( '%.0f', $record->values['uid__c'] );
	    if ($queryResult['size']==0 || $userID==0 || $code!=$userCode){
			header('Location: /'.$this->pi_getPageLink($this->conf['loginPage']));
			exit;
	    }
	    $id= $record->id;

		if (!$this->user['tx_salesforcesubscribe_id']) {
			$GLOBALS["TYPO3_DB"]->exec_UPDATEquery('fe_users', 'uid='.$this->user['uid'], array('tx_salesforcesubscribe_id'=>$id));
		}

// Getting User Company information
	    $accountQuery = $sfdc->query("select id, name from account where id='".$record->values['AccountId']."'");
	    $account= $accountQuery['records'];
	    $record->values['company']= $account->values['Name'];

// Getting Subscribes information
	    $subscriptionsQuery = $sfdc->query("select id, name, type__c from subscription__c");
	    $subscriptions= $subscriptionsQuery['records'];

// Getting current subscribes information
	    $cursubscriptionsQuery = $sfdc->query("select id, subscription__c, active__c from subscription_link__c where contact__c='$id'");
		$cursubscriptions= $cursubscriptionsQuery['records'];
		if ($cursubscriptionsQuery['size']<=1) 
			$cursubscriptions= array($cursubscriptions);
		
	    $checksubs=array();
	    $subsids= array();
	    foreach ($cursubscriptions as $s){
			$checksubs[$s->values['Subscription__c']]= $s->values;
			$checksubs[$s->values['Subscription__c']]['subId']= $s->id;
			$subsids[]= $s->id;
	    }

		// MLC events not used
		if ( false )
		{
// Getting Events information
        $eventsQuery = $sfdc->query("select id, name, type__c from
		brainstorm_event__c where name like '%06'");
        $events= $eventsQuery['records'];

// Getting current events information
        $cureventsQuery = $sfdc->query("select id,
			brainstorm_event__c from brainstorm_event_link__c
			where contact__c='$id'");
        $curevents= $cureventsQuery['records'];
		if ($cureventsQuery['size']<=1) $curevents= array($curevents);

        $checkevents=array();
        $eventsids= array();
        foreach ($curevents as $e){
            $checkevents[$e->values['Brainstorm_Event__c']]=
			$e->values;
            $eventsids[]= $e->id;
        }
		}

// Show info
	    $template= $this->cObj->getSubpart($this->template, '###EDIT_INFO###');
		$markerArray= array();
		$markerArray['###ID###']= $id;
		$markerArray['###PRID###']= $this->prefixId;
		$markerArray['###SUBSCRIBES_LIST###']= implode('::', $subsids);
		$markerArray['###ADDRESS_CONT###']='';
		$record->values[ 'HasOptedOutOfEmail' ] = ( 'true' == $record->values[
			'HasOptedOutOfEmail' ] )
			? 'checked="checked"'
			: '';
		foreach ($record->values as $key=>$value) {
			$markerArray['###'.strtoupper($key).'###']= $value;
		}

		$subscribesTemplate= $this->cObj->getSubpart($template, '###SUBSCRIBES###');
		$subscribes= $subscribesTemplate;
		foreach ($subscriptions as $subscription){
			$subsMarker=array(
				'###' . strtoupper( $subscription->id ) . '###' => ($checksubs[$subscription->id]&&$checksubs[$subscription->id]['Active__c']=='true'
					?'checked="checked"'
					:''
				),
				'###' . strtoupper( $subscription->id ) . '_VALUE###' => $checksubs[$subscription->id]['subId']
			);
			$subscribes = $this->cObj->substituteMarkerArrayCached($subscribes, $subsMarker, array());
		}

		if ( false )
		{
		$eventsTemplate= $this->cObj->getSubpart($template,'###EVENTS###');
        $eventes= '';
        foreach ($events as $event){
            $eventsMarker=array(
               '###EVENT###' => $event->values['Name'],
               '###ID###' => $event->id,
               '###CHECKED###' => ($checkevents[$event->id]?'checked':''),
            );
            $eventes.= $this->cObj->substituteMarkerArrayCached($eventsTemplate,
				$eventsMarker, array());
        }
        }
		return $this->cObj->substituteMarkerArrayCached($template, $markerArray,
		array('###SUBSCRIBES###' => $subscribes, '###EVENTS###'=> $eventes));
	}

	function changeInfo()
	{
		global $TSFE;

		$content='';
		$id= t3lib_div::_GP('userID');

		$sfdc = new salesforce(dirname(__FILE__).'/partner.wsdl');
		$loginResult = $sfdc->login($this->username, $this->password);

		$batchSize = new soapval('batchSize', null, 2);
		$sfdc->setHeader('QueryOptions', array($batchSize));

// Update User information
	    $accountQuery = $sfdc->query("select id from account where name='".$this->piVars['Company']."'");
		if ($accountQuery->size<1){
			$accountObject= new SObject('Account', null, array('Name'=>$this->piVars['Company']));
			$createResult= $sfdc->create($accountObject);
			$this->piVars['AccountId']= $createResult['id'];
		}else{
			$account= $accountQuery['records'];
			$this->piVars['AccountId']= $account->id;
		}

	    unset($this->piVars['Company']);
	    if(!isset($this->piVars['HasOptedOutOfEmail']))
		{
			$this->piVars['HasOptedOutOfEmail']=false;
		}
		else
		{
			$this->piVars['HasOptedOutOfEmail']=true;
		}
		$contact= new SObject('Contact', $id, array_merge($this->piVars, array('Id'=>$id)));
		$updateResult= $sfdc->update($contact);

// Update subscriptions
		// _GP:curSubscribes is subscriptions already in place
		$curSubscribes			= t3lib_div::_GP('subscribes');
		$curSubscribes			= explode( '::', $curSubscribes );

		// _GP:subscribe is subscriptions being kept or added
		$subscribei				= t3lib_div::_GP('subscribe');

		$subscribeList			= array();

		foreach ( $subscribei as $sid => $value )
		{
			// no value entry, then needs subscription
			if ( '' == $value )
			{
				$subscribe_link[]	= new SObject('subscription_link__c'
										, null
										, array(
											'subscription__c'	=> $sid
											, 'active__c'		=> true
											, 'contact__c'		=> $id
										)
									);

				// $sid is subscription
				$subscribeList[]	= $sid;
			}
		}

		$createResult= $sfdc->create($subscribe_link);

		$unSubscribe			= array();

		foreach ( $curSubscribes as $key => $sid )
		{
			// if curSub has entry, but sub doesn't, then delete
			if ( ! in_array( $sid, $subscribei ) )
			{
				// $value is the sub link
				$unSubscribe[]		= $sid;
			}
		}

		$subIp					= $_SERVER['REMOTE_ADDR'];
		$subDate				= date( 'F j, Y g:i:s A' );
		$subDetail				= " on $subDate ($subIp)";
		$subBody				= '';

		// use sublist to query db for list title
		// create text to body
		// Subscribed to “XBulletin” n Month day, Year HH:MM AMAM from IP ...\
		if ( count( $subscribeList ) > 0 )
		{
			$ids				= array();

			foreach ( $subscribeList as $key => $sid )
			{
				$ids[]			= "id = '$sid'";
			}

			$idsOr				= implode( ' OR ', $ids );

			$subQuery			= "select name from subscription__c where $idsOr";
			$subResult			= $sfdc->query( $subQuery );
			$subRecord			= $subResult['records'];

			foreach( $subRecord as $key => $subName )
			{
				$subBody		.= 'Subscribed to "'
									. $subName->values['Name']
									. '"'
									. $subDetail . "\n";
			}
		}

		// use unsublist to query db for sub list id
		// use sub list id to query db for list title
		// create text to body
		// Unsubscribed to “XBulletin” n Month day, Year HH:MM AMAM from IP .
		if ( count( $unSubscribe ) > 0 )
		{
			// takes current Subscription_Link__c ids and grab Subscription__c
			$ids				= array();

			foreach ( $unSubscribe as $key => $sid )
			{
				$ids[]			= "id = '$sid'";
			}

			$idsOr				= implode( ' OR ', $ids );

			$subQuery			= "select Subscription__c from Subscription_Link__c where $idsOr";

			$subResult			= $sfdc->query( $subQuery );
			$subRecord			= $subResult['records'];

			$unsubscribeList	= array();

			foreach( $subRecord as $key => $subSubid )
			{
				$unsubscribeList[]	= $subSubid->values['Subscription__c'];
			}

			$ids				= array();

			foreach ( $unsubscribeList as $key => $sid )
			{
				$ids[]			= "id = '$sid'";
			}

			$idsOr				= implode( ' OR ', $ids );

			$subQuery			= "select name from subscription__c where $idsOr";
			$subResult			= $sfdc->query( $subQuery );
			$subRecord			= $subResult['records'];


			foreach( $subRecord as $key => $subName )
			{
				$subBody		.= 'Unsubscribed to "'
									. $subName->values['Name']
									. '"'
									. $subDetail . "\n";
			}
		}

	    if ( 'true' == $this->piVars['HasOptedOutOfEmail'] )
		{
				$subBody		.= "Global unsubscribe" . $subDetail . "\n";
		}

	    else
		{
				$subBody		.= "Global subscribe" . $subDetail . "\n";
		}

		$sfdc->delete( $unSubscribe );

		$noteEntry				= new SObject( 'note'
									, null
									, array(
										'title'		=> 'Subscription change'
										, 'body'	=> $subBody
										, 'ParentId'	=> $id
									)
								);

		$noteEntryResult		= $sfdc->create( $noteEntry );

		return $this->cObj->getSubpart($this->template, '###THANKYOU_PAGE###');
	}

	function showWaitingPage(){
		global $TSFE;
		$oAmeosHeader = t3lib_div::makeInstance("tx_ameoshtmlheader_pi1");
		$oAmeosHeader->initialize($this);    // this refers to the cuurent extension
		$oAmeosHeader->addHeader('<META HTTP-EQUIV="REFRESH" CONTENT="0;
		URL=/'.$this->pi_getPageLink($TSFE->id,'',array('action'=>'showInfo',
		'userID'=>t3lib_div::_GP('userID')?t3lib_div::_GP('userID'):'0',
		'code'=>t3lib_div::_GP('code')?t3lib_div::_GP('code'):'0')).'">', AMEOS_HTMLHEADER_TYPERAW);
		return $this->cObj->getSubpart($this->template, '###WAITING_PAGE###');
	}

	function showNotLoggedPage(){
		return $this->cObj->getSubpart($this->template, '###NOT_LOGGED###');
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/salesforce_subscribe/pi1/class.tx_salesforcesubscribe_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/salesforce_subscribe/pi1/class.tx_salesforcesubscribe_pi1.php"]);
}

?>
