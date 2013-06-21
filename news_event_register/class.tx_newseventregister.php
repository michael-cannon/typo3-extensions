<?php

/***************************************************************
*  Copyright notice
*  
*  (c) 2005 Michael Cannon <michael@peimic.com>
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
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Adds event display and registration capabilities to tt_news.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newseventregister.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

// used for html emails
require_once( t3lib_extMgm::extPath( 'sr_feuser_register' )
	. 'pi1/class.tx_srfeuserregister_pi1.php'
);
require_once( t3lib_extMgm::extPath( 'sr_feuser_register' )
	. 'pi1/class.tx_srfeuserregister_pi1_t3lib_htmlmail.php'
);

// used for multiple registration
require_once( t3lib_extMgm::extPath( 'news_related' )
	. 'class.tx_newsrelated.php'
);

class tx_newseventregister extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newseventregister';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newseventregister.php';
	// The extension key.
	var $extKey					= 'news_event_register';
	var $conf					= array();
	// template file
	var $templateFile			= 'EXT:news_event_register/news_event_register.tmpl';
	var $newsObject				= null;
	var $newsConf				= null;
	var $newsRow				= null;
	var $db						= null;
	var $hasEvent				= null;
	var $isRegistered			= null;
	var $user					= null;
	var $markerArray			= array();
	var $registerName			= 'tx_newseventregister_register';
	var $unregisterName			= 'tx_newseventregister_unregister';
	var $registerIdsName		= 'tx_newseventregister_registerids';
	var $unregisterIdsName		= 'tx_newseventregister_unregisterids';
	var $registerIds			= array();
	var $registeredIds			= array();
	var $unregisterIds			= array();
	var $registeredEmail		= '###TEMPLATE_REGISTERED_EMAIL###';
	var $registeredAdminEmail	= '###TEMPLATE_REGISTERED_ADMIN_EMAIL###';
	var $unregisteredEmail		= '###TEMPLATE_UNREGISTERED_EMAIL###';
	var $unregisteredAdminEmail	= '###TEMPLATE_UNREGISTERED_ADMIN_EMAIL###';
	var $pointOfContactPart		= '###TEMPLATE_POC###';
	var $htmlSuffix				= '_HTML';
	var $reminderArray			= array(
									1		=> 'first'
									, 2		=> 'second'
									, 3		=> 'third'
								);
	var $userUid				= null;
	var $sponsorUid				= null;
	var $adminEmail				= null;
	var $cObj					= null;
	var $timezoneOffset			= 0;
	var $timezoneTime			= 0;
	var $surveyOn				= false;
	var $survey					= null;
	var $surveyConf				= array();
	var $surveyContents			= '';
	var $surveyValid			= true;

	function main ( $parentObject )
	{
		// current database object
		$this->db				= $GLOBALS[ 'TYPO3_DB' ];
		$this->db->debugOutput	= true;

		// save our user
		$this->user				= ( isset( $GLOBALS[ 'TSFE' ]->fe_user->user ) )
									? $GLOBALS[ 'TSFE' ]->fe_user->user
									: array();

		$this->newsObject		= $parentObject;
		$this->cObj				= $this->newsObject->cObj;

		// grab news_event_register conf
		$this->conf				= $this->newsObject->conf[
									'news_event_register.' ];

		// timezoneOffset is in hours
		$this->timezoneOffset	= $this->conf[ 'timezoneOffset' ] * 60 * 60;
		$this->timezoneTime		= time() + $this->timezoneOffset;

		$this->newsConf			= $this->newsObject->conf[ 'displaySingle.' ];
		$this->newsRow			= ( isset(
										$this->newsObject->local_cObj->data[
											'uid' ] )
									)
									? $this->newsObject->local_cObj->data
									: $this->loadNewsRow();

		if ( isset( $this->newsObject->local_cObj->data[ 'uid' ] ) )
		{
			// MLC 20071016 apply time offset directly
			// already done in loadNewsRow
			$this->newsRow[ 'tx_newseventregister_startdateandtime' ]	+=
										$this->timezoneOffset;
			$this->newsRow[ 'tx_newseventregister_enddateandtime' ]		+=
										$this->timezoneOffset;
		}


		$this->adminEmail		= $this->conf[ 'adminEmail' ];

		//Initiate language
		// ~/www/tslib/class.tslib_pibase.php modified to use PATH_site
		$this->pi_loadLL();

		$this->hasEvent			= $this->newsRow[
									'tx_newseventregister_eventon' ];

		$this->surveyOn			= ( $this->newsRow[
									'tx_newseventregister_surveyon' ]
									&& $this->newsRow[
										'tx_newseventregister_surveyquestions' ]
									&& $this->hasEvent
								)
									? true
									: false;

		$templateflex_file		= $this->pi_getFFvalue(
									$this->newsObject->cObj->data['pi_flexform']
									, 'template_file'
									, 's_template'
								);
		$this->templateFile		= ( $templateflex_file )
									? 'uploads/tx_ttnews/' . $templateflex_file
									: $this->conf[ 'templateFile' ];

		// allow a template to be assign specifically for a news item
		if ( isset( $this->conf[ $this->newsRow[ 'uid' ] . '.' ][
			'templateFile' ]
			)
		)
		{
			$this->templateFile	= $this->conf[ $this->newsRow[ 'uid' ] . '.' ][
									'templateFile' ];
		}

		if ($this->newsRow[ 'tx_newseventregister_canned' ]) {
			$this->templateFile = $this->conf[ 'templateFileCanned' ];
		}
	
		$categories= $this->getRelatedCategories($this->newsRow[ 'uid' ]);
		foreach($categories as $cat){
			if ($cat['rt']){
				$this->templateFile = 'uploads/tx_ttnews/'.$cat['rt'];
				break;
			}elseif ($cat['ct'] && $this->newsRow[ 'tx_newseventregister_canned' ]){
				$this->templateFile = 'uploads/tx_ttnews/'.$cat['ct'];
				break;
			}
		}
	}

	function registrationOperations () {
		// grab event uids, 0 or more
		$registerIds			= t3lib_div::_GP( $this->registerIdsName );
		$this->registerIds		= ( is_array( $registerIds ) )
									? array_unique( $registerIds )
									: $this->registerIds;

		$unregisterIds			= t3lib_div::_GP( $this->unregisterIdsName );
		$this->unregisterIds		= ( is_array( $unregisterIds ) )
									? array_unique( $unregisterIds )
									: $this->unregisterIds;

		$this->isRegistered		= $this->isRegistered();

		// MLC 20090527 adapt for auto-login with submission for one-click
		// registration
		if ( ! $this->isRegistered 
			&& t3lib_div::_GP( $this->registerName )
			&& 0 == count( $this->registerIds )
			&& isset( $this->user[ 'uid' ] )
		) {
				$this->registerIds		= array( $this->newsRow[ 'uid' ] );
		}

		// handle special "visitor" or "temporary" user groups
		if ( ! $this->isRegistered && $this->conf[ 'tempGroupsUsed' ] )
		{
			$this->isRegistered	= $this->isTempRegistered();
		}

		// if survey questions, check that they're submitted
		if ( $this->surveyOn )
		{
			$this->survey		= t3lib_div::getUserObj( 'tx_mssurvey_pi1' );
			$this->survey->cObj	= $this->cObj;

			$this->surveyConf	= $GLOBALS[ 'T3_VAR' ][
									'callUserFunction_classPool' ][
									'tx_realurl' ]->pObj->tmpl->setup[ 'plugin.'
									][ 'tx_mssurvey_pi1.' ];

			$this->surveyConf[ 'pidList' ]		= $this->newsRow[ 'pid' ];
			$this->surveyConf[ 'newsUid' ]		= $this->newsRow[ 'uid' ];
			$this->surveyConf[ 'templateFile' ]	= $this->templateFile;
			$this->surveyConf[ 'submitted' ]	= t3lib_div::_GP(
													$this->registerName
												);
			$this->surveyConf[ 'registrantPid' ]	= $this->conf[
														'registrantPid' ];
			$this->surveyConf[ 'surveyrequired' ]	= $this->newsRow[
								'tx_newseventregister_surveyrequired' ];

			// generate survey content and check sent
			$this->surveyContents	= $this->survey->newsSurveyDisplay(
										$this->surveyConf
									);
			$this->surveyValid	= ! $this->survey->hasError;
		}

		// optional survey saving of results
		if ( $this->isRegistered && $this->surveyOn && $this->surveyValid )
		{
			// save survey results
			$this->survey->saveResults();
		}

		// Registration and survey is complete
		if ( ! $this->isRegistered
			&& t3lib_div::_GP( $this->registerName )
			&& 0 < count( $this->registerIds )
			&& $this->surveyValid
		)
		{
			$this->isRegistered	= $this->register();
			// $this->isRegistered	= true;

			// send thank you email
			$this->sendThankYou();

			if ( $this->surveyOn )
			{
				// save survey results
				$this->survey->saveResults();

				// redirect to thank you page
				if ( $this->conf[ 'thankyouPid' ] )
				{
	 				$pid		= $this->conf[ 'thankyouPid' ];
					$url		= $this->newsUrl( $pid );
					header( 'Location: ' . $url );
				}
			}
		}
		
		// Unregistration
		elseif ( t3lib_div::_GP( $this->unregisterName )
			&& $this->unregisterIds
		)
		{
			$this->isRegistered	= $this->unregister();
			// send unsubscribe email to admin
			$this->sendUnregistered();
		}
	}

	/**
	 * Send unregister message to participant for unsigning up. Notify admin
	 * also.
	 *
	 * @return void
	 */
	function sendUnregistered ()
	{
		$time					= $this->timezoneTime;
		$pid					= $this->conf[ 'registrantPid' ];
		$user					= $this->user[ 'uid' ];

		// remember current news item
		$currentNews			= $this->newsRow[ 'uid' ];

		$userEmail				= $this->user[ 'first_name' ]
									. ' '
									. $this->user[ 'last_name' ]
									. ' <'. $this->user[ 'email' ] . '>';

		foreach ( $this->unregisterIds as $key => $news )
		{
			// load $news item
			if ( $news != $currentNews )
			{
				$this->newsRow	= $this->loadNewsRow( $news );
			}
			
			$this->sendEmail( $this->unregisteredEmail, $userEmail );
			$this->sendEmail( $this->unregisteredAdminEmail
				, $this->adminEmail
			);
		}

		// recall current news item
		if ( $currentNews != $this->newsRow[ 'uid' ] )
		{
			$this->newsRow		= $this->loadNewsRow( $currentNews );
		}
	}

	/**
	 * Send thank you message for participant for signing up. Also, update db
	 * that thank you was sent.
	 *
	 * @return void
	 */
	function sendThankYou()
	{
		$time					= $this->timezoneTime;
		$pid					= $this->conf[ 'registrantPid' ];
		$user					= $this->user[ 'uid' ];

		// remember current news item
		$currentNews			= $this->newsRow[ 'uid' ];

		$userEmail				= $this->user[ 'first_name' ]
									. ' '
									. $this->user[ 'last_name' ]
									. ' <'. $this->user[ 'email' ] . '>';

		foreach ( $this->registerIds as $key => $news )
		{
			// load $news item
			if ( $news != $currentNews )
			{
				$this->newsRow	= $this->loadNewsRow( $news );
			}
			
			$this->sendEmail( $this->registeredEmail, $userEmail
				, $this->conf[ 'sendHtml' ]
			);

			$this->sendEmail( $this->registeredAdminEmail
				, $this->adminEmail
			);

			// update participant thank you date
			$where				= "
				1 = 1
				AND pid = $pid
				AND news_id = $news
				AND fe_user_id = $user
				AND unregistered = 0
			";
			$where				.= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants'
								);

			$fields				= array(
									'tstamp'				=> $time
									, 'thankyousent'		=> $time
								);

			// for the events not in registerIds but not in registeredIds
			// insert a participant record
			$success			= $this->db->exec_UPDATEquery(
									'tx_newseventregister_participants'
									, $where
									, $fields
								);
		}

		// recall current news item
		if ( $currentNews != $this->newsRow[ 'uid' ] )
		{
			$this->newsRow		= $this->loadNewsRow( $currentNews );
		}
	}

	/**
	 * Returns string containing point of contact user information
	 *
	 * @return string
	 */
	function getPoc ()
	{
		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab template
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, $this->pointOfContactPart
								);

		// call news_userinfo for parsing user information
		$userinfo				= t3lib_div::getUserObj( 'tx_newsuserinfo' );

		// set point of contact id so that it's loaded instead of logged in user
		$userinfo->userUid		= $this->newsRow[
									'tx_newseventregister_pointofcontact' ];

		$markerArray			= array();
		$markerArray			= $userinfo->extraItemMarkerProcessor(
									$markerArray
									, $this->newsRow
									, $this->newsConf
									, $this->newsObject
								);

		$poc					= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $markerArray
								);

		$poc					= trim( $poc );

		return $poc;
	}

	/**
	 * Sends email using given headers and template.
	 *
	 * @param string template name
	 * @param string to
	 * @param boolean true, use HTML template
	 * @return void
	 */
	function sendEmail ( $templatePart, $to = false, $sendHtml = false )
	{
		$sent					= false;

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// parse content
		$this->populateMarkerArray();

		if ( $sendHtml )
		{
			$templatePart		= preg_replace( '/(###)$/'
									, $this->htmlSuffix . '\1'
									, $templatePart
								);
		}

		// grab template
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, $templatePart
								);

		// call news_sponsorinfo for parsing sponsor information
		$sponsor				= t3lib_div::getUserObj( 'tx_newssponsor' );

		// allow alternate sponsor to be loaded
		// handy for cron'd or sent via backend emails
		$sponsor->sponsorUid	= $this->newsRow[ 'tx_newssponsor_sponsor' ];

		$this->markerArray		= $sponsor->extraItemMarkerProcessor(
									$this->markerArray
									, $this->newsRow
									, $this->newsConf
									, $this->newsObject
								);

		// call news_userinfo for parsing user information
		$userinfo				= t3lib_div::getUserObj( 'tx_newsuserinfo' );

		// allow alternate user to be loaded
		// handy for cron'd or sent via backend emails
		$userinfo->userUid		= ( $this->userUid )
									? $this->userUid
									: null;

		$this->markerArray		= $userinfo->extraItemMarkerProcessor(
									$this->markerArray
									, $this->newsRow
									, $this->newsConf
									, $this->newsObject
								);

		// names are usually on one line, ensure of such
		$this->markerArray[ '###FIRST_NAME###' ]	= strip_tags(
									$this->markerArray[ '###FIRST_NAME###' ]
								);
		$this->markerArray[ '###LAST_NAME###' ]	= strip_tags(
									$this->markerArray[ '###LAST_NAME###' ]
								);
		$this->markerArray[ '###EVENT_TITLE###' ]	= strip_tags(
									$this->markerArray[ '###EVENT_TITLE###' ]
								);

		$body					= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
								);

		// bail if no to
		if ( ! $to && ! $userinfo->user[ 'email' ] )
		{
			echo( 'failed to (uid)' );
			echo( $this->userUid );

			return $sent;
		}

		// build up full recipient address if none
		$to						= ( $to )
									? $to
									: $userinfo->user[ 'first_name' ]
										. ' '
										. $userinfo->user[ 'last_name' ]
										. ' <'
										. $userinfo->user[ 'email' ]
										. '>';

		// convert html to plain text string
		$plainBody				= $this->cbHtml2Str( $body );

		if ( $sendHtml && is_numeric( $this->conf[ 'removePlainLines' ] ) )
		{
			$plainBody			= trim( $plainBody );
			$plainBody			= explode( "\n", $plainBody );

			$linesToRemove		= $this->conf[ 'removePlainLines' ];

			for ( $i = 0; $i < $linesToRemove; $i++ )
			{
				unset( $plainBody[ $i ] );
			}

			$plainBody			= implode( "\n", $plainBody );
		}

		if ( $sendHtml )
		{
			// generate and send html email
			$sent				= tx_srfeuserregister_pi1::sendHTMLMail(
									$body
									, $plainBody
									, $to
									// cc
									, ''
									, $this->conf[ 'fromEmail' ]
									, $this->conf[ 'fromName' ]
									// reply to
									, $this->conf[ 'fromEmail' ]
								);
		}

		// send plain text email
		else
		{
			// email
			$sent				= $this->newsObject->cObj->sendNotifyEmail(
									$plainBody
									, $to
									// cc
									, ''
									, $this->conf[ 'fromEmail' ]
									, $this->conf[ 'fromName' ]
									// reply to
									, $this->conf[ 'fromEmail' ]
								);
		}

		return $sent;
	}

	/**
	 * Return boolean if unregister was success or not.
	 *
	 * @return boolean
	 */
	function unregister()
	{
		$return					= true;
		$time					= $this->timezoneTime;
		$pid					= $this->conf[ 'registrantPid' ];
		$user					= $this->user[ 'uid' ];

		foreach ( $this->unregisterIds as $key => $news )
		{
			$where				= "
				pid = $pid
				AND news_id = $news
				AND fe_user_id = $user
				AND unregistered = 0
			";
			$where				.= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants'
								);

			$fields				= array(
									'tstamp'				=> $time
									, 'unregistered'		=> $time
								);

			// for the events not in registerIds but not in registeredIds
			// insert a participant record
			$success			= $this->db->exec_UPDATEquery(
									'tx_newseventregister_participants'
									, $where
									, $fields
								);

			if ( ! $succes )
			{
				$return			= false;
			}
		}

		return $return;
	}

	/**
	 * Return boolean if register was success or not.
	 *
	 * @return boolean
	 */
	function register()
	{
		$return					= true;
		$time					= $this->timezoneTime;
		$pid					= $this->conf[ 'registrantPid' ];
		$user					= $this->user[ 'uid' ];

		foreach ( $this->registerIds as $key => $news )
		{
			$fields				= array(
									'pid'					=> $pid
									, 'tstamp'				=> $time
									, 'crdate'				=> $time
										+ $this->timezoneOffset
									, 'registrationdate'	=> $time
									, 'news_id'				=> $news
									, 'fe_user_id'			=> $user
								);

			// for the events not in registerIds but not in registeredIds
			// insert a participant record
			$success			= $this->db->exec_INSERTquery(
									'tx_newseventregister_participants'
									, $fields
								);

			if ( ! $success )
			{
				$return			= false;
			}
		}

		return $return;
	}

	/**
	 * Return boolean if temp user is registered or not.
	 *
	 * @return boolean
	 */
	function isTempRegistered()
	{
		// look into participants table for
		// user
		// news item
		// registered date
		// but no unregistered date
		// if so, user is registered
		$return					= false;

		// have a user?
		if ( ! isset( $this->user[ 'uid' ] )
			|| ! isset( $this->newsRow[ 'uid' ] )
		)
		{
			return $return;
		}

		$newsUid				= $this->newsRow[ 'uid' ];
		$newsUids				= ( 0 != count( $this->registerIds ) )
									? implode( ',', $this->registerIds )
									: $this->newsRow[ 'uid' ];

		$selectConf				= array();

		$selectConf[ 'selectFields' ]	=
								'tx_newseventregister_participants.news_id';
		$selectConf[ 'leftjoin' ]		= '
			fe_users
				ON tx_newseventregister_participants.fe_user_id = fe_users.uid
		';
		$selectConf[ 'pidInList' ]		= $this->conf[ 'registrantPid' ];

		$selectConf[ 'where' ] = "
			tx_newseventregister_participants.news_id IN ( $newsUids )
			AND tx_newseventregister_participants.registrationdate != 0
			AND tx_newseventregister_participants.unregistered = 0
			AND fe_users.email = '{$this->user[ 'email' ]}'
			AND fe_users.disable = 0
			AND fe_users.deleted = 0
		";
		$selectConf[ 'where' ] .= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants');

		$result					= $this->newsObject->cObj->exec_getQuery(
									'tx_newseventregister_participants'
									, $selectConf
								);

		// build up list of events already registered for
		while( $result && $value = $this->db->sql_fetch_assoc( $result ) )
		{
			$this->registeredIds[]	= $value[ 'news_id' ];
		}

		// since some events are already registered remove them from our
		// add list
		// diff registerIds with registeredIds to determine which events
		// need to be registered for
		$this->registerIds		= array_diff( $this->registerIds
									, $this->registeredIds
								);

		// is current news item in our registered list?
		if ( in_array( $newsUid, $this->registeredIds ) )
		{
			$return				= true;
		}

		return $return;
	}

	/**
	 * Return boolean if user is registered or not.
	 *
	 * @return boolean
	 */
	function isRegistered()
	{
		// look into participants table for
		// user
		// news item
		// registered date
		// but no unregistered date
		// if so, user is registered
		$return					= false;

		// have a user?
		if ( ! isset( $this->user[ 'uid' ] )
			|| ! isset( $this->newsRow[ 'uid' ] )
		)
		{
			return $return;
		}

		$newsUid				= $this->newsRow[ 'uid' ];
		$newsUids				= ( 0 != count( $this->registerIds ) )
									? implode( ',', $this->registerIds )
									: $this->newsRow[ 'uid' ];
		$userUid				= $this->user[ 'uid' ];

		$where					= '1 = 1';
		$where					.= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants');
		$where					.= ' AND news_id IN (' . $newsUids . ')';
		$where					.= ' AND fe_user_id = ' . $userUid;
		$where					.= ' AND registrationdate != 0';
		$where					.= ' AND unregistered = 0';

		$result				= $this->db->exec_SELECTgetRows(
									'news_id'
									, 'tx_newseventregister_participants'
									, $where
								);

		// build up list of events already registered for
		if ( 0 < count( $result ) )
		{
			foreach ( $result as $key => $value )
			{
				$this->registeredIds[]	= $value[ 'news_id' ];
			}

			// since some events are already registered remove them from our
			// add list
			// diff registerIds with registeredIds to determine which events
			// need to be registered for
			$this->registerIds	= array_diff( $this->registerIds
									, $this->registeredIds
								);
		}

		// is current news item in our registered list?
		if ( in_array( $newsUid, $this->registeredIds ) )
		{
			$return				= true;
		}

		return $return;
	}

	/**
	 * Return array containing current news item.
	 *
	 * @return array
	 */
	function loadNewsRow( $newsUid = false )
	{
		$newsUid				= ( $newsUid )
									? $newsUid
									: $this->newsObject->tt_news_uid;

		$where					= '1 = 1 AND uid = ' . $newsUid . ' ';
		$where					.= $this->newsObject->cObj->enableFields(
									'tt_news');

		// refer to for the database functions ~/www/t3lib/class.t3lib_db.php
		$newsItem				= $this->db->exec_SELECTgetRows(
									'*'
									, 'tt_news'
									, $where
								);

		// check that our newsItem array exists
		if ( empty( $newsItem ) )
		{
			return false;
		}

		$newsItem				= array_pop( $newsItem );

		if ( ! $newsItem[ 'tx_newseventregister_enddateandtime' ] )
		{
			$newsItem[ 'tx_newseventregister_enddateandtime' ]	=
				$newsItem[ 'tx_newseventregister_startdateandtime' ];
		}

		// MLC 20071018 apply time offset directly
		$newsItem[ 'tx_newseventregister_startdateandtime' ]	+=
									$this->timezoneOffset;
		$newsItem[ 'tx_newseventregister_enddateandtime' ]		+=
									$this->timezoneOffset;

		return $newsItem;
	}
	
	/**
	 * Return array containing current news item categories.
	 *
	 * @return array
	 */
	function getRelatedCategories ($newsUid = false)
	{
		$baseArray				= array();
		$relatedUid				= ( $newsUid )
									? $newsUid
									: $this->newsObject->tt_news_uid;


		if ( ! $relatedUid )
		{
			return $baseArray;
		}

		$where					= 'AND tt_news.uid = ' . $relatedUid;
		$where					.= ' AND tx_newsrelated_dontshowinrelatednews = 0 ';
		$where					.= $this->newsObject->cObj->enableFields(
									'tt_news');
		$where					.= $this->newsObject->cObj->enableFields(
									'tt_news_cat');

		$relatedResult			= $this->db->exec_SELECT_mm_query(
									'tt_news_cat.uid'
									. ', tt_news_cat.rounded_template'
									. ', tt_news_cat.canned_template'
									, 'tt_news'
									, 'tt_news_cat_mm'
									, 'tt_news_cat'
									, $where
								);

		$relateds				= $baseArray;
		$i=0;

		while ( $relatedResult
			&& $result = $this->db->sql_fetch_assoc( $relatedResult )
		)
		{
			// category uid
			$relateds[$i]['rt']			= $result[ 'rounded_template' ];
			$relateds[$i++]['ct']			= $result[ 'canned_template' ];
		}

		return $relateds;
	}
	
	function extraItemMarkerProcessor( $markerArray, $row, $lConf
		, $parentObject
	)
	{
		$this->main( $parentObject );
		$this->newsConf			= $lConf;
		$this->markerArray		= $markerArray;

		$this->populateMarkerArray();

		return $this->markerArray;
	}

	/**
	 * Populate markerArray based upon event existence.
	 *
	 * @return void
	 */
	function populateMarkerArray()
	{
		// check for event, if none, blank the markerArray entries
		if ( $this->hasEvent )
		{
			$this->populateEventLabels( $this->markerArray );
			$this->populateEventFields( $this->markerArray );
		}

		else
		{
			$this->blankEventFields( $this->markerArray );
		}
	}

	/**
	 * Create blanks for event fields.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function blankEventFields( & $markerArray )
	{
		$markerArray[ '###EVENT_REGISTER_MAGIC_TEXT###' ] = '';
		$markerArray[ '###EVENT_TITLE_REGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_TITLE_REGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_TITLE_UNREGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_FROM_TEXT###' ] = '';
		$markerArray[ '###EVENT_TO_TEXT###' ] = '';
		$markerArray[ '###EVENT_LINK_TEXT###' ] = '';
		$markerArray[ '###EVENT_INFORMATION_TEXT###' ] = '';
		$markerArray[ '###EVENT_INFORMATION_EMAILED_TEXT###' ] = '';
		$markerArray[ '###EVENT_REGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_UNREGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_REGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_UNREGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_NOTICES_TEXT###' ] = '';
		$markerArray[ '###EVENT_SUBJECT_REGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_BODY_REGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_SUBJECT_UNREGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_BODY_UNREGISTERED_TEXT###' ] = '';
		$markerArray[ '###EVENT_SUBJECT_REMINDER_TEXT###' ] = '';
		$markerArray[ '###EVENT_BODY_REMINDER_TEXT###' ] = '';
		$markerArray[ '###EVENT_SUBJECT_ACCESS_TEXT###' ] = '';
		$markerArray[ '###EVENT_BODY_ACCESS_TEXT###' ] = '';
		$markerArray[ '###EVENT_ACCESS_TEXT###' ] = '';
		$markerArray[ '###EVENT_FOLLOWUP_TEXT###' ] = '';
		$markerArray[ '###EVENT_FOLLOWUP_LINK_TEXT###' ] = '';
		$markerArray[ '###EVENT_SUBJECT_FOLLOWUP_TEXT###' ] = '';
		$markerArray[ '###EVENT_BODY_FOLLOWUP_TEXT###' ] = '';
		$markerArray[ '###EVENT_POC_TEXT###' ] = '';
		$markerArray[ '###EVENT_RESERVE_TEXT###' ] = '';

		$markerArray[ '###EVENT_REGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_UNREGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_TITLE###' ] = '';
		$markerArray[ '###EVENT_AUTHOR###' ] = '';
		$markerArray[ '###EVENT_FROM_DATE###' ] = '';
		$markerArray[ '###EVENT_FROM_TIME###' ] = '';
		$markerArray[ '###EVENT_TO_DATE###' ] = '';
		$markerArray[ '###EVENT_TO_TIME###' ] = '';
		$markerArray[ '###EVENT_INFORMATION###' ] = '';
 		$markerArray[ '###EVENT_INFORMATION_EMAILED###' ]	= '';
 		$markerArray[ '###EVENT_INFORMATION_REGISTERED###' ] = '';
 		$markerArray[ '###EVENT_INFORMATION_REGISTERED_EMAILED###' ] = '';
		$markerArray[ '###EVENT_LINK###' ] = '';
		$markerArray[ '###EVENT_WEBEXLINK###' ] = '';
		$markerArray[ '###EVENT_REGISTER_FORM###' ] = '';
		$markerArray[ '###EVENT_UNREGISTER_FORM###' ] = '';
		$markerArray[ '###EVENT_REGISTER_FORM_NAME###' ] = '';
		$markerArray[ '###EVENT_UNREGISTER_FORM_NAME###' ] = '';
		$markerArray[ '###EVENT_ACCESS###' ] = '';
		$markerArray[ '###EVENT_FOLLOWUP###' ] = '';
		$markerArray[ '###EVENT_FOLLOWUP_LINK###' ] = '';
		$markerArray[ '###EVENT_POC###' ] = '';
		$markerArray[ '###EVENT_BACK###' ] = '';
		$markerArray[ '###EVENT_URL###' ] = '';
		$markerArray[ '###EVENT_UID###' ] = '';
		$markerArray[ '###EVENT_REGISTERING_FOR_TEXT###' ] = '';
		$markerArray[ '###EVENT_MULTIPLE_REGISTER_TEXT###' ] = '';
		$markerArray[ '###EVENT_REGISTER_NOW_TEXT###' ] = '';
		$markerArray[ '###EVENT_TITLE_ENDED_TEXT###' ] = '';
		$markerArray[ '###EVENT_ENDED_TEXT###' ] = '';
		$markerArray[ '###EVENT_OPTIONAL_SURVEY###' ] = '';
		$markerArray[ '###EVENT_OPTIONAL_SURVEY_TEXT###' ] = '';
		$markerArray[ '###EVENT_SURVEY_ERROR###' ] = '';
	}

	function populateEventLabels( & $markerArray )
	{
		// pi_getLL entries come from locallang.php
		// these are labels
		$stdWrap			= $this->conf[ 'label.' ];

		$markerArray[ '###EVENT_DATE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'date' ), $stdWrap );
		$markerArray[ '###EVENT_FROM_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'from' ), $stdWrap );
		$markerArray[ '###EVENT_TO_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'to' ), $stdWrap[ 'to.' ] );
		$markerArray[ '###EVENT_LINK_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'link' ), $stdWrap[ 'link.' ] );
		$markerArray[ '###EVENT_RESERVE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'reserve' ), $stdWrap[ 'reserve.' ] );
		$markerArray[ '###EVENT_INFORMATION_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'information' ), $stdWrap );
		$markerArray[ '###EVENT_INFORMATION_EMAILED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'information' ), $stdWrap );
		$markerArray[ '###EVENT_REGISTER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'register' ), $this->user['first_name'] ), $stdWrap[ 'register.' ] );
		$markerArray[ '###EVENT_UNREGISTER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'unregister' ), $this->user['first_name'] ), $stdWrap[ 'register.' ] );
		$markerArray[ '###EVENT_REGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'registered' ), $this->user['first_name'] ), $stdWrap[ 'registered.' ] );
		$markerArray[ '###EVENT_UNREGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'unregistered' ), $this->user['first_name'] ), $stdWrap[ 'register.' ] );
		$markerArray[ '###EVENT_NOTICES_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'notices' ), $stdWrap[ 'notice.' ] );
		$markerArray[ '###EVENT_SUBJECT_REGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_registered' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_UNREGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_unregistered' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_REGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'body_registered' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_UNREGISTERED_TEXT###' ] =
			$this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL(
			'body_unregistered' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_REGISTERED_ADMIN_TEXT###' ] =
			$this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL(
			'body_registered_admin' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_UNREGISTERED_ADMIN_TEXT###' ] =
			$this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL(
			'body_unregistered_admin' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_REGISTERED_ADMIN_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_registered_admin' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_UNREGISTERED_ADMIN_TEXT###' ] =
			$this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL(
			'subject_unregistered_admin' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_REMINDER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_reminder' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_REMINDER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'body_reminder' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_ACCESS_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_access' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_ACCESS_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'body_access' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_ACCESS_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'access' ), $stdWrap );
		$markerArray[ '###EVENT_FOLLOWUP_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'followup' ), $stdWrap );
		$markerArray[ '###EVENT_FOLLOWUP_LINK_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'followup_link' ), $stdWrap );
		$markerArray[ '###EVENT_SUBJECT_FOLLOWUP_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'subject_followup' ), $this->conf[ 'sitename' ], $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_BODY_FOLLOWUP_TEXT###' ] =
		$this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL(
		'body_followup' ), $this->newsRow[ 'title' ] ), $stdWrap );
		$markerArray[ '###EVENT_POC_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'poc' ), $stdWrap );

		$referrer				= $_SERVER[ 'HTTP_REFERER' ];
		$markerArray[ '###EVENT_BACK###' ] = $this->newsObject->local_cObj->stdWrap( sprintf( $this->pi_getLL( 'back'), $referrer ), $stdWrap[ 'information.' ] );
		//not using no_cache because it caused problems - js
		$linkArray				= array(
									'parameter'			=>
										( $this->conf[ 'eventUrlPid' ] )
											? $this->conf[ 'eventUrlPid' ]
											: $GLOBALS[ 'TSFE' ]->id
									, 'returnLast'		=> 'url'
									, 'no_cache'		=> 1
									, 'useCacheHash'	=> 0
									, 'additionalParams'	=>
							"&tx_ttnews[tt_news]={$this->newsRow[ 'uid' ]}"
								);
		$url					= 'http://'
									. $_SERVER['HTTP_HOST']
									. '/'
									. $this->newsObject->cObj->typolink( ''
										, $linkArray
									);
		$url					= preg_replace( '#/nc/#', '/', $url );
		$url					= preg_replace( '#/no_cache/#', '/', $url );
		$markerArray[ '###EVENT_URL###' ] = $url;

		// special open
		$markerArray[ '###EVENT_TITLE_ENDED_TEXT###' ] =
			$this->newsObject->local_cObj->stdWrap( $this->pi_getLL(
				'title_ended' ), $stdWrap[ 'title.' ] );
		$markerArray[ '###EVENT_TITLE_REGISTER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title_register' ), $stdWrap[ 'title.' ] );
		$markerArray[ '###EVENT_TITLE_REGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title_registered' ), $stdWrap[ 'title.' ] );
		$markerArray[ '###EVENT_TITLE_UNREGISTERED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title_unregistered' ), $stdWrap[ 'title.' ] );
		$markerArray[ '###EVENT_REGISTERING_FOR_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'registering_for' ), $this->conf[ 'form.' ][ 'label.' ] );
		
		if ( ! $this->isRegistered
			&& ( $this->timezoneTime <
				$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
				|| $this->timezoneTime <
					$this->newsRow[ 'tx_newseventregister_enddateandtime' ]
			)
		)
		{
			$markerArray[ '###EVENT_REGISTER_NOW_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'register_now' ), $this->conf[ 'form.' ][ 'label.' ] );
		}
		elseif ( $this->isRegistered )
		{
			$markerArray[ '###EVENT_REGISTER_NOW_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title_registered' ), $this->conf[ 'form.' ][ 'label.' ] );
		}
		else 
		{
			$markerArray[ '###EVENT_REGISTER_NOW_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'ended' ), $this->conf[ 'form.' ][ 'label.' ] );
		}

		$markerArray[ '###EVENT_MULTIPLE_REGISTER_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'multiple_register' ), $this->conf[ 'form.' ][ 'label.' ] );
		$markerArray[ '###EVENT_ENDED_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'ended' ), $this->conf[ 'form.' ][ 'label.' ] );

		// this one combines alternates between reserve and registered
		if ( ! $this->isRegistered )
		{
			$markerArray[ '###EVENT_REGISTER_MAGIC_TEXT###' ] =
								$markerArray[ '###EVENT_RESERVE_TEXT###' ]
								. $markerArray[ '###EVENT_REGISTER_TEXT###' ];
		}

		else
		{
			$markerArray[ '###EVENT_REGISTER_MAGIC_TEXT###' ] =
								$markerArray[ '###EVENT_REGISTERED_TEXT###' ]
								. $markerArray[ '###EVENT_NOTICES_TEXT###' ];

			if ( $this->conf[ 'showUnregister' ] )
			{
				$markerArray[ '###EVENT_REGISTER_MAGIC_TEXT###' ] .=
								$markerArray[ '###EVENT_UNREGISTER_TEXT###' ];
			}
		}

		$this->markerArray[ '###EVENT_SURVEY_ERROR###' ]	=
								( $this->surveyValid )
									? ''
									: $this->pi_getLL( 'survey_error' );

		// Don't show canned RT materials till after start
		// allow materials up an hour beforehand
		if ( ( $this->timezoneTime + 3600  ) <=
				$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
			&& $this->newsRow[ 'tx_newseventregister_canned' ]
		)
		{
 			$markerArray['###TEXT_LINKS###'] = '';
  			$markerArray['###NEWS_LINKS###'] = '';
			$markerArray['###TEXT_FILES###'] = '';
			$markerArray['###FILE_LINK###'] = '';
		}
	}

	/**
	 * Fill markerArray event fields with localized translations.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function populateEventFields( & $markerArray )
 	{
 		$stdWrap				= $this->conf[ 'field.' ];
 
 		$this->newsConf['email_stdWrap.']['wrap'] = $stdWrap[ 'wrap' ];
 
 		$markerArray[ '###EVENT_UID###' ] = $this->newsRow['uid'];

 		$markerArray[ '###EVENT_TITLE###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['title'], $stdWrap[ 'title.' ] ); 
 
 		$markerArray[ '###EVENT_AUTHOR###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['author'], $stdWrap ); 

		// use sponsor message as it's 'offer' related
		$markerArray[ '###EVENT_INFORMATION###' ]	=
								$this->newsObject->local_cObj->stdWrap(
									$this->newsRow['tx_newssponsor_message']
									, $stdWrap[ 'information.' ]
								);

		if ( $this->isRegistered )
		{
 			$markerArray[ '###EVENT_INFORMATION_REGISTERED###' ] =
								$this->newsObject->local_cObj->stdWrap(
									$this->newsRow['tx_newseventregister_eventinformationregistered']
									, $stdWrap[ 'information.' ]
								);
		}

		else
		{
 			$markerArray[ '###EVENT_INFORMATION_REGISTERED###' ] = '';
		}
 
 		// Email event information fields
 		// per client, logic is if eventinformation ignore use*
		if ( $this->newsRow[ 'tx_newseventregister_usetxnewssponsormessage']
			&& ! $this->newsRow[ 'tx_newseventregister_eventinformation' ]
		)
		{
 			$markerArray[ '###EVENT_INFORMATION_EMAILED###' ] =
								$markerArray[ '###EVENT_INFORMATION###' ];
		}

		else
		{
 			$markerArray[ '###EVENT_INFORMATION_EMAILED###' ]	=
								$this->newsObject->local_cObj->stdWrap(
									$this->newsRow['tx_newseventregister_eventinformation']
									, $stdWrap[ 'information.' ]
								);
		}

		// if not registered and no message then blank heading
		// if registered and no fullfilment then blank heading
		// no information, no title
		if ( ( ! $this->isRegistered
			&& '' == $this->newsRow[ 'tx_newssponsor_message' ] )
			|| ( $this->isRegistered
			&& '' == $this->newsRow[
				'tx_newseventregister_useeventinformationregistered' ] )
			|| ! trim( strip_tags( $markerArray[ '###EVENT_INFORMATION###' ] ) )
		)
		{
			$markerArray[ '###EVENT_INFORMATION_TEXT###' ] ='';
		}

 		// per client, logic is if eventinformation ignore use*
		if ( '' == $this->newsRow[ 'tx_newseventregister_eventinformation']
			&& $this->newsRow[ 'tx_newseventregister_useeventinformationregistered' ]
 			&& $this->isRegistered
		)
		{
 			$markerArray[ '###EVENT_INFORMATION_REGISTERED_EMAILED###' ] = 
 				$markerArray[ '###EVENT_INFORMATION_REGISTERED###' ];
		}

		// contents for this are already in ###EVENT_INFORMATION_EMAILED###
		// so blank it
		else
		{
 			$markerArray[ '###EVENT_INFORMATION_REGISTERED_EMAILED###' ] = '';
		}
 
		// hide email special offer heading if no content for emailed markers
		$regEmailedStripped		= trim( strip_tags( $markerArray[
									'###EVENT_INFORMATION_REGISTERED_EMAILED###' ]
								) );

		$emailedStripped		= trim( strip_tags( $markerArray[
									'###EVENT_INFORMATION_EMAILED###' ]
								) );

		if ( '' == $regEmailedStripped && '' == $emailedStripped )
		{
			$markerArray[ '###EVENT_INFORMATION_EMAILED_TEXT###' ] ='';
		}
 
 		$markerArray[ '###EVENT_ACCESS###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['tx_newseventregister_eventaccessinformation'], $stdWrap[ 'information.' ] );
 
 		$markerArray[ '###EVENT_FOLLOWUP###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['tx_newseventregister_followupmessage'], $stdWrap[ 'information.' ] );
 
 		$markerArray[ '###EVENT_LINK###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['tx_newseventregister_eventlink'], $this->newsConf['email_stdWrap.'] ); 

		$webexLink				= ( $this->newsRow['tx_newseventregister_webexlink'] )
									?  $this->newsRow['tx_newseventregister_webexlink']
									: $this->conf[ 'webexLink' ];

 		$markerArray[ '###EVENT_WEBEXLINK###' ] = $webexLink; 
 
 		$markerArray[ '###EVENT_FOLLOWUP_LINK###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow['tx_newseventregister_followuplink'], $this->newsConf['email_stdWrap.'] ); 
 
 		$markerArray[ '###EVENT_FROM_DATE###' ] =
 			$this->newsObject->local_cObj->stdWrap(
 			$this->newsRow['tx_newseventregister_startdateandtime'],
 			$stdWrap['start.']['date.']
 		); 

 		$markerArray[ '###EVENT_FROM_TIME###' ] =
 			$this->newsObject->local_cObj->stdWrap(
 			$this->newsRow['tx_newseventregister_startdateandtime'],
 			$stdWrap['start.']['time.']
 		); 
 
 		$markerArray[ '###EVENT_TO_DATE###' ] =
 			$this->newsObject->local_cObj->stdWrap(
 			$this->newsRow['tx_newseventregister_enddateandtime'],
 			$stdWrap['end.']['date.']
 		); 
 
 		$markerArray[ '###EVENT_TO_TIME###' ] =
 			$this->newsObject->local_cObj->stdWrap(
 			$this->newsRow['tx_newseventregister_enddateandtime'],
 			$stdWrap['end.']['time.']
 		);
 
		// let the survey system forward to thank you once questions answered
		// well
		if ( $this->surveyOn || ! $this->conf[ 'thankyouPid' ] )
		{
	 		$pid				= $GLOBALS[ 'TSFE' ]->id;
		}

		else
		{
	 		$pid				= $this->conf[ 'thankyouPid' ];
		}
		
		$url					= $this->newsUrl( $pid );
 
 		// handle multiple events for signup
 		$stdWrap				= $this->conf[ 'form.' ][ 'label.' ];
 
 		$singleRegister			= $this->singleRegisterInput();

		// only show if multiple register enabled and there's no survey
		// questions
		// check that survey isn't optional and slated for thank you
		if ( $this->surveyOn
			&& ! ( $this->conf[ 'showSurveyOnThankYou' ]
				&& ! $this->newsRow[ 'tx_newseventregister_surveyrequired' ]
			)
		)
		{
			$multipleRegister	= ( $this->newsRow[
									'tx_newseventregister_surveyrequired' ]
								)
									? $this->pi_getLL( 'survey_title_required' )
									: $this->pi_getLL( 'survey_title' );

			$multipleRegister	.= '<p>&nbsp;</p>';
			$multipleRegister	.= $this->surveyContents;
		}

		elseif ( $this->conf[ 'multipleRegister' ] )
		{
	 		$multipleRegister	= $this->multipleRegisterInput();
		}

		else
		{
 			$multipleRegister	= '';
		}

 		$registerSubmit			= $this->pi_getLL( 'submit_register' );
 		$registerForm			= '
 			<form action="' . $url . '" method="post">'
 				. $singleRegister
 				. $multipleRegister
 				. '<input type="hidden" value="1" name="' . $this->registerName
 					. '" />
 				<br />
 				<input type="submit" value="' . $registerSubmit . '" />
 			</form>
 		';
 
 		$markerArray[ '###EVENT_REGISTER_FORM###' ] = ( $this->timezoneTime <
					$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
					|| $this->timezoneTime <
						$this->newsRow[ 'tx_newseventregister_enddateandtime' ]
				)
								? $registerForm
								: $markerArray[ '###EVENT_ENDED_TEXT###' ];
 
 		// handle multiple events for signup
 		$multipleUnregister		= $this->conf[ 'multipleUnregister' ]
 									? $this->multipleUnregisterInput()
 									: '';
 
 		// don't add label is no checkboxes
 		$multipleUnregister		= ( $multipleUnregister
 										&& $this->conf[ 'multipleUnregister' ]
 									)
 									? $this->newsObject->local_cObj->stdWrap(
 											$this->pi_getLL(
 												'multiple_unregister' )
 											, $stdWrap
 										)
 										. $multipleUnregister
 									: '';
 
 		$unregisterSubmit		= $this->pi_getLL( 'submit_unregister' );
 		$unregisterForm			= '
 			<form action="' . $url . '" method="post">
 				<input type="hidden" value="' . $this->newsRow[ "uid" ]
 					. '" name="' . $this->unregisterIdsName . '[]" />
 				' . $multipleUnregister . '
 				<input type="hidden" value="1" name="' . $this->unregisterName
 				. '" />
 				&nbsp;<input type="submit" value="' . $unregisterSubmit . '" />
 			</form>
 		';
 
 		$markerArray[ '###EVENT_UNREGISTER_FORM###' ] =( $this->timezoneTime <
					$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
					|| $this->timezoneTime <
						$this->newsRow[ 'tx_newseventregister_enddateandtime' ]
				)
								? $unregisterForm
								: $markerArray[ '###EVENT_ENDED_TEXT###' ];

 		$markerArray[ '###EVENT_POC###' ] = $this->newsObject->local_cObj->stdWrap( $this->getPoc(), $stdWrap );
 
 		// this one combines alternates between reserve and registered
 		if ( ! $this->isRegistered )
 		{
 			$markerArray[ '###EVENT_REGISTER_MAGIC###' ] =
 								$markerArray[ '###EVENT_RESERVE###' ]
 								. $markerArray[ '###EVENT_REGISTER_FORM###' ];
 		}
 
 		else
 		{
 			$markerArray[ '###EVENT_REGISTER_MAGIC###' ] =
 								$markerArray[ '###EVENT_REGISTERED###' ]
 								. $markerArray[ '###EVENT_NOTICES###' ];
 
 			if ( $this->conf[ 'showUnregister' ] )
 			{
 				$markerArray[ '###EVENT_REGISTER_MAGIC###' ] .=
 								$markerArray[ '###EVENT_UNREGISTER_FORM###' ];
 			}
 		}

		// check filled state
		if ( $this->surveyOn && $this->conf[ 'showSurveyOnThankYou' ]
			&& ! $this->newsRow[ 'tx_newseventregister_surveyrequired' ]
		)
		{
			$markerArray[ '###EVENT_OPTIONAL_SURVEY_TEXT###' ] =
								$this->pi_getLL( 'survey_title' );
			$markerArray[ '###EVENT_OPTIONAL_SURVEY###' ] =
								$this->optionalSurvey();
		}

		else
		{
			$markerArray[ '###EVENT_OPTIONAL_SURVEY_TEXT###' ] = '';
			$markerArray[ '###EVENT_OPTIONAL_SURVEY###' ] = '';
		}
 	}

	/**
	 * Returns string containing input checkbox for single registration.
	 *
	 * @return string
	 */
	function singleRegisterInput ()
	{
		$this->markerArray[ '###EVENT_REGISTER_FORM_NAME###' ] =
								$this->registerIdsName;
		$this->markerArray[ '###EVENT_TITLE###' ]	= strip_tags(
									$this->markerArray[ '###EVENT_TITLE###' ]
								);
		$this->markerArray[ '###EVENT_AUTHOR###' ]	= strip_tags(
									$this->markerArray[ '###EVENT_AUTHOR###' ]
								);

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab subpart
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, '###TEMPLATE_REGISTER_SINGLE###'
								);

		$content				= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
								);

		return $content;
	}

	/**
	 * Returns string containing input checkboxes for multiple registration
	 * selections.
	 *
	 * @return string
	 */
	function multipleRegisterInput ()
	{
		$string					= '';
		$this->markerArray[ '###EVENT_REGISTER_FORM_NAME###' ] =
								$this->registerIdsName;

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		$templatePart			= '###TEMPLATE_REGISTER_MULTIPLE###';

		if ( ! preg_match( "/$templatePart/", $template ) )
		{
			return $string;
		}

		// grab subpart
		$subpart				= $this->newsObject->getNewsSubpart(
									$template
									, $templatePart
								);

		$subpartEach			= $this->newsObject->getLayouts( $subpart
									, 1
									, 'EACH'
								);

		// instantiate news
		$newsRelated			= t3lib_div::getUserObj( 'tx_newsrelated' );

		// look up related events by category
		$newsRelated->main( $this->newsObject );

		// grab their uid and title
		$relatedNews			= $newsRelated->relatedNews;

		if ( ! $relatedNews )
		{
			return $string;
		}

		foreach ( $relatedNews as $key => $news )
		{
			// for each one, create an input checkbox followed by the title
			// one per line
			if ( $news[ 'tx_newseventregister_eventon' ]
				&& $this->timezoneTime >=
					$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
				&& $this->timezoneTime >=
					$this->newsRow[ 'tx_newseventregister_enddateandtime' ]
			)
			{
				$this->markerArray[ '###EVENT_UID###' ] =
								$news[ 'uid' ];
				$this->markerArray[ '###EVENT_TITLE###' ] =
								$news[ 'title' ];
				$this->markerArray[ '###EVENT_FROM_DATE###' ] =
					$this->newsObject->local_cObj->stdWrap(
						$this->newsRow['tx_newseventregister_startdateandtime']
						, $this->conf['field.']['start.']['date.']
					); 
				$this->markerArray[ '###EVENT_FROM_TIME###' ] =
					$this->newsObject->local_cObj->stdWrap(
						$this->newsRow['tx_newseventregister_startdateandtime']
						, $this->conf['field.']['start.']['time.']
					); 

				$string			.= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpartEach[ 0 ]
									, $this->markerArray
								);
			}
		}

		if ( '' == $string )
		{
			return $string;
		}

		$contentMarkerArray		= array();
		$contentMarkerArray[ '###CONTENT###' ] = $string;

		$content				= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, $contentMarkerArray
								);

		return $content;
	}

	/**
	 * Returns string containing input checkboxes for multiple unregistration
	 * selections.
	 *
	 * @return string
	 */
	function multipleUnregisterInput ()
	{
		$string					= '';

		if ( ! $this->user[ 'uid' ] )
		{
			return $string;
		}

		$registeredNews			= array();

		// look up currently registered events
		$selectConf				= $this->emailSelectConf();
		$selectConf[ 'selectFields' ]	= 'tt_news.uid, tt_news.title';
		$selectConf[ 'where' ]	.= "
			AND tt_news.uid != {$this->newsRow[ 'uid' ]}
			AND fe_user_id = {$this->user[ 'uid' ]}
			AND registrationdate != 0
			AND unregistered = 0
			{$this->timezoneTime} <=
				tt_news.tx_newseventregister_startdateandtime
		";

		$result					= $this->newsObject->cObj->exec_getQuery(
									'tt_news'
									, $selectConf
								);

		// build up list of events already registered for
		while( $result && $value = $this->db->sql_fetch_assoc( $result ) )
		{
			// grab their uid and title
			$registeredNews[]	= $value;
		}

		if ( 0 == count( $registeredNews ) )
		{
			return $string;
		}

		foreach ( $registeredNews as $key => $news )
		{
			// for each one, create an input checkbox followed by the title
			// one per line
			$string			.= '
				<input type="checkbox"
					value="' . $news[ 'uid' ] . '"
					name="' . $this->unregisterIdsName . '[]"
				/> '
				. $news[ 'title' ]
				. '<br />
			';
		}

		return $string;
	}

	function extraCodesProcessor( $parentObject )
	{
		$this->main( $parentObject );

		$content				= '';

		switch ( $this->newsObject->theCode )
		{
			case 'EVENT_REGISTER':
				$this->registrationOperations();
				$content		= $this->getParsedEventTemplate();
				break;

			case 'SEND_REMINDERS':
				$content		= $this->sendReminders();
				break;

			case 'SEND_ACCESS':
				$content		= $this->sendAccessInformation();
				break;

			case 'SEND_FOLLOW_UP':
				$content		= $this->sendFollowup();
				break;

			case 'SEND_MONITOR':
				$content		= $this->sendMonitor();
				break;

			default:
				break;
		}
		
		return $content;
	}

	/**
	 * Return string contain parsed event template.
	 *
	 * @return string
	 */
	function getParsedEventTemplate()
	{
		$this->populateMarkerArray();

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		switch ( true )
		{
			case ( $this->timezoneTime >=
					$this->newsRow[ 'tx_newseventregister_startdateandtime' ]
					&& $this->timezoneTime >=
						$this->newsRow[ 'tx_newseventregister_enddateandtime' ]
			):
				$subpartName	= '###TEMPLATE_ENDED###';
				break;

			case ( $this->isRegistered ):
				$subpartName	= '###TEMPLATE_REGISTERED###';
				break;

			case ( ! $this->isRegistered && $this->unregisterIds ):
				$subpartName	= '###TEMPLATE_UNREGISTERED###';
				break;

			default:
			case ( ! $this->isRegistered ):
				$subpartName	= '###TEMPLATE_REGISTER###';
				break;
		}

		// grab subpart
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, $subpartName
								);

		$content				= $this->newsObject->cObj->substituteMarkerArray(
									$subpart
									, $this->markerArray
								);

		return $content;
	}

	/**
	 * Runs the cron operations for sending out reminder, access, and follow-up
	 * emails.
	 *
	 * @return void
	 */
	function runcron ()
	{
        // order here matters to prevent getting three reminders and access
        // information for the person signing up an hour before the event

		// followup
		$this->sendFollowup();

		// access
		$this->sendAccessInformation();

		// reminder
		$this->sendReminders();

		return;
	}

	/**
	 * Send the event follow up emails.
	 *
	 * @return void
	 */
	function sendFollowup ()
	{
		set_time_limit( 3000 );

		$sendUids				= array();
		$pid					= $this->conf[ 'registrantPid' ];

		$selectConf				= $this->emailSelectConf();

		$dateField				= 'followupsent';

		$selectConf[ 'where' ] .= "
			AND tx_newseventregister_sendfollowup = 1
			AND tx_newseventregister_participants.{$dateField} = 0
			AND tx_newseventregister_followupmessage != ''
			AND unix_timestamp() >= tx_newseventregister_enddateandtime
			AND unix_timestamp() >= tx_newseventregister_startdateandtime
		";

		$result					= $this->newsObject->cObj->exec_getQuery(
									'tt_news'
									, $selectConf
								);

		// build up list of events already registered for
		while( $result && $value = $this->db->sql_fetch_assoc( $result ) )
		{
			$sendUids[]		= $value;
		}

		// email build up and sending
		$this->sendEmails( $sendUids
			, $dateField
			, '###TEMPLATE_FOLLOWUP_EMAIL###'
		);
	}

	/**
	 * Send the event access informaiton emails.
	 *
	 * @return void
	 */
	function sendAccessInformation ()
	{
		set_time_limit( 3000 );

		$sendUids				= array();
		$userUids				= array();
		$pid					= $this->conf[ 'registrantPid' ];

		$selectConf				= $this->emailSelectConf();

		// hours
		$period					= $this->conf[ 'accessInformationSend' ];

		$dateField				= 'accessinformationsent';

		$selectConf[ 'where' ] .= "
			AND tx_newseventregister_sendaccessinformation = 1
			AND tx_newseventregister_participants.{$dateField} = 0
			AND unix_timestamp() BETWEEN 
				( tx_newseventregister_startdateandtime
					- ( {$period} * 60 * 60 )
					+ {$this->timezoneOffset}
				)
				AND
				( tx_newseventregister_enddateandtime
					+ {$this->timezoneOffset}
				)
				/*
			AND tx_newseventregister_participants.fe_user_id = 10106
				*/
			AND tx_newseventregister_participants.followupsent = 0
		";

		$query					= $this->newsObject->cObj->getQuery(
									'tt_news'
									, $selectConf
								);

// cbDebug( 'query', $query );	
		$result					= $this->newsObject->cObj->exec_getQuery(
									'tt_news'
									, $selectConf
								);

		// build up list of events already registered for
		while( $result && $value = $this->db->sql_fetch_assoc( $result ) )
		{
			$sendUids[]		= $value;
 
            if($value['tx_newseventregister_canned']) $userUids[]= $value;
		}

		// activate visitors for canned events
		if (0 < count($userUids)){
			$this->activateVisitors($userUids);
		}
		
		// email build up and sending
		if ( 0 < count( $sendUids ) )
		{
			$this->sendEmails( $sendUids
				, $dateField
				, '###TEMPLATE_ACCESS_EMAIL###'
			);

			@$this->sendMonitor( true );
		}
	}

	/**
	 * Send the event reminder emails.
	 *
	 * @return void
	 */
	function sendReminders ()
	{
		set_time_limit( 3000 );

		$sendUids				= array();
		$pid					= $this->conf[ 'registrantPid' ];
		$delay					= $this->conf[ 'reminderDelay' ];

		$selectConf				= $this->emailSelectConf();
		$where					= $selectConf[ 'where' ];

		foreach( $this->reminderArray as $reminderNumber => $text )
		{
			// days
			$period				= $this->conf[ $text . 'ReminderSend' ];

			// is tx_newseventregister_sendfirstreminder active
			// is now between first reminder period in days and
			// tx_newseventregister_startdateandtime
			$dateField			= $text . 'remindersent';

			// rewrite where every time
			// don't select if previous reminder was sent less than x days ago
			$selectConf[ 'where' ] = "
				$where
				AND tx_newseventregister_send{$text}reminder = 1
				AND tx_newseventregister_participants.{$dateField} = 0
				AND unix_timestamp() BETWEEN 
					( tx_newseventregister_startdateandtime
						- ( {$period} * 60 * 60 * 24 )
						+ {$this->timezoneOffset}
					)
					AND
					( tx_newseventregister_startdateandtime
						+ {$this->timezoneOffset}
					)
				AND tx_newseventregister_participants.accessinformationsent = 0
				AND tx_newseventregister_participants.followupsent = 0
				AND unix_timestamp() >= (
					tx_newseventregister_participants.thankyousent
						+ ( $delay * 60 * 60 * 24 )
						+ {$this->timezoneOffset}
					)
				AND unix_timestamp() >= (
					tx_newseventregister_participants.firstremindersent
						+ ( $delay * 60 * 60 * 24 )
						+ {$this->timezoneOffset}
					)
				AND unix_timestamp() >= (
					tx_newseventregister_participants.secondremindersent
						+ ( $delay * 60 * 60 * 24 )
						+ {$this->timezoneOffset}
					)
				AND unix_timestamp() >= (
					tx_newseventregister_participants.thirdremindersent
						+ ( $delay * 60 * 60 * 24 )
						+ {$this->timezoneOffset}
					)
			";

			$result				= $this->newsObject->cObj->exec_getQuery(
									'tt_news'
									, $selectConf
								);

			// build up list of events already registered for
			while( $result && $value = $this->db->sql_fetch_assoc( $result ) )
			{
				$sendUids[]		= $value;
			}

			// email build up and sending
			$this->sendEmails( $sendUids
				, $dateField
				, '###TEMPLATE_REMINDER_EMAIL###'
			);

			// reset or else accidentally collect bad info
			$sendUids			= array();
		}
	}

	function activateVisitors($userUids){
		foreach($userUids as $user){
			$userStartTime= $user['tx_newseventregister_startdateandtime']
				- ( $this->conf[ 'accessInformationSend' ] * 60 * 60 );
			$userEndTime= $userStartTime + (24*60*60);
			$userUid= $user['fe_user_id'];

			$success= $this->db->exec_UPDATEquery('fe_users'
				, 'uid='.$userUid.' AND username LIKE "visitor_%"'
				, array('starttime'=>$userStartTime, 'endtime'=>$userEndTime)
			);
		}
	}

	/**
	 * Sends out reminder emails and updates partipant record of such.
	 *
	 * @param array news and user id
	 * @param string table field name being updated
	 * @param string email template subpart
	 * @return void
	 */
	function sendEmails( $sendUids, $field, $template )
	{
		// lie to the system
		$this->hasEvent			= 1;
		$templateOld			= $this->templateFile;

		// generate email for each and send
		$sendUidsCount			= count( $sendUids );

		// nobody found? fine, contine on with foreach
		if ( 1 > $sendUidsCount )
		{
			return;
		}

		$pid					= $this->conf[ 'registrantPid' ];
		$time					= $this->timezoneTime;

		for ( $i = 0; $i < $sendUidsCount; $i++ )
		{
			$newsUid			= $sendUids[ $i ][ 'uid' ];
			$userUid			= $sendUids[ $i ][ 'fe_user_id' ];

			if($sendUids[ $i ][ 'tx_newseventregister_canned' ])
				$this->templateFile = $this->conf['templateFileCanned'];
			else
				$this->templateFile = $templateOld;

			// for each news load it
			$this->newsRow		= $this->loadNewsRow( $newsUid );

			// for each person load them
			$this->userUid		= $userUid;

			// call sendEmail using template part name and to
			@$this->sendEmail( $template
				, false 
				, $this->conf[ 'sendHtml' ]
			);

			// update tstamp and period sent for participant
			$where				= "
					pid = $pid
					AND news_id = $newsUid
					AND fe_user_id IN ( $userUid )
					AND unregistered = 0
			";
			$where				.= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants'
								);

			$fields				= array(
									'tstamp'	=> $time
									, $field	=> $time
								);

			// for the events not in registerIds but not in registeredIds
			// insert a participant record
			$success			= $this->db->exec_UPDATEquery(
									'tx_newseventregister_participants'
									, $where
									, $fields
								);
		}
	}

	/**
	 * Return array contain select configuration for cron'd emails.
	 *
	 * @return array
	 */
	function emailSelectConf ()
	{
		$selectConf				= array();
		$pid					= $this->conf[ 'registrantPid' ];

		// return news and fe_user ids
		$selectConf[ 'selectFields' ]	= "
			tt_news.uid
			, tx_newseventregister_canned
			, tx_newseventregister_participants.fe_user_id
			, tx_newseventregister_startdateandtime
		";

		// are there any users signed up
		$selectConf[ 'leftjoin' ]		= '
			tx_newseventregister_participants
				ON tt_news.uid = tx_newseventregister_participants.news_id
		';

		// resolve news item locations
		$selectConf[ 'pidInList' ]		= $this->conf[ 'eventPid' ];

		// look in tt_news for tx_newseventregister_eventon 
		// user isn't unregistered
		// tx_newseventregister_participants.unregistered = 0
		$selectConf[ 'where' ] = "
			tx_newseventregister_participants.uid IS NOT NULL
			AND tx_newseventregister_eventon = 1
			AND tx_newseventregister_participants.pid = $pid
			AND tx_newseventregister_participants.unregistered = 0
		";
		$selectConf[ 'where' ] .= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants');

		return $selectConf;
	}

	/**
	 * Send the event registeration and emails sent email.
	 *
	 * @param boolean time frame, false - yesterday, true - now
	 * @return void
	 */
	function sendMonitor ( $now = false )
	{
		$markerArray			= array();

		$categories				= $this->newsObject->catExclusive;

		switch ($this->newsObject->config['categoryMode']){
			case 1:
				$catSql= 'AND tt_news_cat_mm.uid_foreign IN ('.$categories.')';
				break;
			case -1:
				$catSql= 'AND tt_news_cat_mm.uid_foreign NOT IN ('.$categories.')';
				break;
			case 0:
			default:
				$catSql='';
		}

		// month:day:year
		$timeFrame				= ( $now )
									? time()
									: strtotime( 'yesterday' );

		$dateFrame				= date( 'n:j:Y', $timeFrame );
		$dateFrame				= explode( ':', $dateFrame );

		$dayStart				= $this->timezoneOffset
									+ mktime( 0, 0, 0
										, $dateFrame[ 0 ]
										, $dateFrame[ 1 ]
										, $dateFrame[ 2 ]
									);

		$dayEnd					= $this->timezoneOffset
									+ mktime( 23, 59, 59
										, $dateFrame[ 0 ]
										, $dateFrame[ 1 ]
										, $dateFrame[ 2 ]
									);

		$enabled				= $this->newsObject->cObj->enableFields(
									'tt_news'
								);
		$enabled				.= $this->newsObject->cObj->enableFields(
									'tx_newseventregister_participants'
								);

		// select total, new, thank you, reminder, access counts based upon
		// dates for active round tables
		$query					= "
			SELECT 
				title
				, FROM_UNIXTIME( tx_newseventregister_startdateandtime
					+ {$this->timezoneOffset}
					, '%M %e, %Y %l:%i %p' ) date
				, SUM( CASE WHEN ( 0 != registrationdate ) THEN 1 ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					totalRegistrants
				, SUM( CASE WHEN ( registrationdate BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					newRegistrants
				, SUM( CASE WHEN ( thankyousent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					thankyousent
				, SUM( CASE WHEN ( firstremindersent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					firstremindersent
				, SUM( CASE WHEN ( secondremindersent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					secondremindersent
				, SUM( CASE WHEN ( thirdremindersent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					thirdremindersent
				, SUM( CASE WHEN ( accessinformationsent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					accessinformationsent
				, SUM( CASE WHEN ( followupsent BETWEEN $dayStart
						AND $dayEnd ) THEN 1
					ELSE 0 END )
					/ COUNT(DISTINCT tt_news_cat_mm.uid_foreign)
					followupsent
			FROM tt_news
				LEFT JOIN tx_newseventregister_participants
					ON tt_news.uid = tx_newseventregister_participants.news_id
				LEFT JOIN tt_news_cat_mm
					ON tt_news.uid = tt_news_cat_mm.uid_local
			WHERE
				1 = 1
				$enabled
				$catSql
				/* 
					include prior 24 hours for access emails or sending of
					follow ups.
				 */
				AND (
					( $dayStart <= tx_newseventregister_startdateandtime
						AND $dayStart <= tx_newseventregister_enddateandtime )
					OR $dayStart <= followupsent
				)
				GROUP BY tt_news.uid
		";

		$result					= $this->db->sql( TYPO3_db, $query );

		// build up list of events already registered for
		if ( ! $result )
		{
			return;
		}

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$this->markerArray[ '###EVENT_TODAYS_DATE###' ]				= 
									date( 'l, M j, Y' , $dayStart );
			$this->markerArray[ '###EVENT_NEWS_TITLE###' ]				=
									trim( preg_replace( '#\?#', ''
										, $data[ 'title' ] )
									);
			$this->markerArray[ '###EVENT_DATE###' ]					=
									$data[ 'date' ];
			$this->markerArray[ '###EVENT_TOTAL_REGISTRANTS###' ]		=
									number_format( $data[ 'totalRegistrants' ]
									);
			$this->markerArray[ '###EVENT_NEW_REGISTRANTS###' ]			=
									number_format( $data[ 'newRegistrants' ] );
			$this->markerArray[ '###EVENT_THANK_YOU_EMAILS_SENT###' ]	=
									number_format( $data[ 'thankyousent' ] );
			$this->markerArray[ '###EVENT_REMINDER_EMAILS_SENT###' ]	=
									number_format( $data[ 'firstremindersent' ]
									+ $data[ 'secondremindersent' ]
									+ $data[ 'thirdremindersent' ] );
			$this->markerArray[ '###EVENT_ACCESS_EMAILS_SENT###' ]		=
									number_format( $data[
									'accessinformationsent' ] );
			$this->markerArray[ '###EVENT_FOLLOWUP_EMAILS_SENT###' ]	=
									number_format( $data[ 'followupsent' ] );

			// try to help prevent sending meaningless fluff
			if ( ! $now
				|| $this->markerArray[ '###EVENT_NEW_REGISTRANTS###' ]
				|| $this->markerArray[ '###EVENT_THANK_YOU_EMAILS_SENT###' ]
				|| $this->markerArray[ '###EVENT_REMINDER_EMAILS_SENT###' ]
				|| $this->markerArray[ '###EVENT_ACCESS_EMAILS_SENT###' ]
				|| $this->markerArray[ '###EVENT_FOLLOWUP_EMAILS_SENT###' ]
			)
			{
				// email build up and sending
				$this->sendEmail( '###TEMPLATE_MONITOR###'
					, $this->conf[ 'monitorEmail' ]
				);
			}
		}
	}

	/**
	 * Return string of news URL
	 *
	 * @param int page id
	 * @return string
	 */
	function newsUrl( $pid )
	{
 		//not using no_cache because it caused problems - js
		$linkArray				= array(
 									'parameter'			=> $pid
 									, 'returnLast'		=> 'url'
 									, 'no_cache'		=> 0
 									, 'useCacheHash'	=> 1
 									, 'additionalParams'	=>
 							"&tx_ttnews[tt_news]={$this->newsRow[ 'uid' ]}"
 								);
 		$url					= '/'
 									. $this->newsObject->cObj->typolink( ''
 										, $linkArray
 									);

		return $url;
	}

	/**
	 * Returns string containing optional survey.
	 *
	 * @return string
	 */
	function optionalSurvey ()
	{
		$string					= '';

		$url					= $_SERVER[ 'HTTP_REFERER' ];
		$submit					= $this->pi_getLL( 'submit_survey' );

		$this->markerArray[ '###SURVEY_ITEMS###' ] = <<<EOD
 			<form action="{$url}" method="post">
				{$this->surveyContents}
 				<input type="submit" value="{$submit}" />
 			</form>
EOD;

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, '###TEMPLATE_OPTIONAL_SURVEY###'
								);

		$content				= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
								);

		return $content;
	}

	/**
	 * Returns string containing HTML converted to a plain text string.
	 *
	 * @param string
	 * @return string
	 */
	function cbHtml2Str ( $string ) {
		// remove CSS first, easier this way
		$string						= preg_replace(
										'#<style[^>]*>.+</style>#si'
										, ''
										, $string
									);
		// convert html entities to real characters
		$string						= html_entity_decode( $string );
		$string						= preg_replace( '/&bull;/', '*', $string );
		// convert quotes
		$string						= preg_replace( '/(&#8220;|&#8221;)/'
											, '"'
											, $string
										);
		// as the email is sent plain text, remove html
		$string						= strip_tags( $string );
		// double space to single
		$string						= preg_replace( '/  /', ' ', $string );
		// remove sentence beginning whitespace
		$string						= preg_replace( '/\s{2,}/'
											, "\n\n"
											, $string
										);
		$string						= trim( $string );

		return $string;
	}
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_event_register/class.tx_newseventregister.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_event_register/class.tx_newseventregister.php' ]
// 	);
// }

?>
