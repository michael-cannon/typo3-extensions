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
 * Adds sponsored lead generation to tt_news
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newslead.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

class tx_newslead extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newslead';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newslead.php';
	// template file
	var $templateFile			= 'EXT:news_lead/news_lead.tmpl';
	// The extension key.
	var $extKey					= 'news_lead';
	var $conf					= array();

	// database object
	var $db						= null;

	// fe_user
	var $user					= null;

	// news row
	var $newsRow				= null;
	var $newsConf				= null;
	var $newsObject				= null;
	var $sponsor				= null;
	var $timeFrameUid			= null;
	var $alreadySent			= null;
	var $saveLead				= false;
	var $requestFile			= null;
	var $leadSent				= false;
	var $filepath				= 'uploads/media/';
	var $requestFilePrefix		= 'rfi';
	
	function main ( $parentObject )
	{
		// set our news row internally
		$this->newsObject		= $parentObject;

		// current database object
		$this->db				= $GLOBALS[ 'TYPO3_DB' ];
		$this->db->debugOutput	= true;

		// save our user
		$this->user				= $GLOBALS[ 'TSFE' ]->fe_user->user;

		// grab news_lead conf
		$this->conf				= $this->newsObject->conf[ 'news_lead.' ];

		//Initiate language
		$this->pi_loadLL();

		// use TS defined or default template
		$templateflex_file		= $this->pi_getFFvalue(
									$this->newsObject->cObj->data['pi_flexform']
									, 'template_file'
									, 's_template'
								);
		$this->templateFile		= ( $templateflex_file )
									? 'uploads/tx_ttnews/' . $templateflex_file
									: $this->conf[ 'templateFile' ];
	}

	function extraItemMarkerProcessor( $markerArray, $row, $lConf
		, $parentObject
	)
	{
		$this->main( $parentObject );
		$this->newsRow			= $row;
		$this->newsConf			= $lConf;
		$this->markerArray		= $markerArray;

 		$sponsorUid				= ( $this->newsRow[ 'tx_newssponsor_sponsor' ] )
									? $this->newsRow[ 'tx_newssponsor_sponsor' ]
									: 0;

 		$where					= '1 = 1 AND uid = ' . $sponsorUid . ' ';
 		$where					.= $this->newsObject->cObj->enableFields(
 									'tx_t3consultancies');
 
		$sponsor				= $this->db->exec_SELECTgetRows(
 									'*'
 									, 'tx_t3consultancies'
 									, $where
 								);

 		// check that our sponsor array exists
		$this->sponsor			= ( 0 < count( $sponsor ) )
 									? array_pop( $sponsor )
 									: false;

		$this->timeFrameUid		= $this->currentTimeFrameUid();

		// no user, no download either
		if ( $this->user)
		{
			$this->alertSponsor( $markerArray[ '###FILE_LINK###' ] );
			$this->alertSponsor( $markerArray[ '###NEWS_LINKS###' ], true );
		}

		return $markerArray;
	}

	/**
	 * Replaces current filelinks with sponsorAlert parameters as needed. If
	 * coming through with requestFile, don't replace links, but do send
	 * sponsorAlert.
	 *
	 * @param & string filelinks
	 * @param boolean newslinks tranform
	 * @return void
	 */
	function alertSponsor ( & $filelinks, $newslinkTransform = false )
	{
		// check for requestFile tag
		$this->requestFile		= t3lib_div::_GP( 'requestFile' );

		// external file is array key
		$newslinkRequestFile	= preg_match( '#'
									. $this->requestFilePrefix
									. '[0-9]+#'
									, $this->requestFile );

		if ( $newslinkRequestFile )
		{
			// get link count
			$linkCount			= preg_replace( '#' . $this->requestFilePrefix
									. '#'
									, ''
									, $this->requestFile
								);

			// break newslinks into array
			$filelinksArr		= explode( "\n", $this->newsRow[ 'links' ] );

			// grab array indice
			$filelink			= ( isset( $filelinksArr[ $linkCount ] ) )
									? $filelinksArr[ $linkCount ]
									: false;

			// pull url
			// set to be redirect
			if ( $filelink )
			{
				if ( preg_match( '/^(https?|ftp|rstp)/', $filelink ) )
				{
					$this->requestFile	= $filelink;
				}

				elseif ( preg_match( '/<link/', $filelink ) )
				{
					$this->requestFile	= preg_replace( '/(.*<link )([^>]+)(>.*)/i'
									, '\2'
									, $filelink
								);
				}

				else
				{
					$this->requestFile	= preg_replace( '/.*(href=")([^"]+)("[^>]*>.*)/i'
									, '\2'
									, $filelink
								);
				}
			}

			else
			{
				$this->requestFile  = false;
			}
		}

		// if found and file is of the current news item
		// if two news items have same named file, then issue
		// general news lead
		if ( $this->requestFile
			&& ( preg_match( "#{$this->requestFile}#", $filelinks )
				|| $newslinkRequestFile
			)
		)
		{
			// if leads isn't enabled for either global or news items then bail
			// no email, no lead...
			// check date range
			if ( $this->conf[ 'send' ]
				&& $this->newsRow[ 'tx_newslead_leadon' ]
				&& $this->timeFrameUid
				&& $this->sponsor 
				&& '' != $this->sponsor[ 'contact_email' ]
			)
			{
				$this->alreadySent	= $this->isLeadSent();

				// send email only if not sent already for the current period
				if ( ! $this->alreadySent )
				{
					$this->sendLead();
					$this->saveLead		= true;
				}
			}

			if ( $this->saveLead
				|| $this->conf[ 'trackAll' ]
			)
			{
				$this->saveLead();
			}

			// redirect to requestFile
			if ( ! $newslinkRequestFile )
			{
				$filename		= $GLOBALS[ 'TSFE' ]->baseUrl
									. $this->filepath
									. $this->requestFile;
			}

			else
			{
				$filename		= $this->requestFile;
			}

			header( 'Location: ' . $filename );
		}

		elseif ( ! $newslinkTransform )
		{
			// MLC move file to become an attribute of current item url
			$url				= $_SERVER[ 'REQUEST_URI' ];

			// remove previous requestFile
			$url				= preg_replace( '#(\?|&)?requestFile=.*#i'
									, ''
									, $url
								);
			$url				.= ( preg_match( '/\?/', $url ) )
									? '&'
									: '?';
			$link				= '\1' . $url . 'requestFile=' 
									. '\2\3';

			// rewrite urls to include this page link plus requestFile parameter
			$filelinks			= preg_replace( '/(href=")([^"]+)("[^>]*>)/i'
									, $link
									, $filelinks
								);
			$filelinks			= preg_replace( "#{$this->filepath}#i"
									, ''
									, $filelinks
								);
		}

		else
		{
			// break newslink into array
			$filelinksArr		= explode( "<br />", $filelinks );

			// MLC move file to become an attribute of current item url
			$url				= $_SERVER[ 'REQUEST_URI' ];

			// remove previous requestFile
			$url				= preg_replace( '#(\?|&)?requestFile=.*#i'
									, ''
									, $url
								);
			$url				.= ( preg_match( '/\?/', $url ) )
									? '&'
									: '?';

			foreach( $filelinksArr as $key => $value )
			{
				// rewrite urls to include this page link plus requestFile
				// parameter
				// use counter has as requestfile
				$link			= '\1' . $url . 'requestFile='
									. $this->requestFilePrefix
									. $key
									. '\3';

				$value			= preg_replace( '/(href=")([^"]+)("[^>]*>)/i'
									, $link
									, $value
								);

				$filelinksArr[ $key ]	= $value;
			}

			$filelinks			= implode( "<br />", $filelinksArr );
		}
	}

	/**
	 * Return timeframe uid if in current lead time period otherwise false.
	 *
	 * @return mixed integer uid, boolean false
	 */
	function currentTimeFrameUid ()
	{
		$return					= false;

		$newsUid				= $this->newsRow[ 'uid' ];

		$where					= $this->newsObject->cObj->enableFields(
									'tt_news');
		$where					.= 'AND tt_news.uid = ' 
									. $newsUid . ' ';
		$where					.= 'AND tx_newslead_leadperiod.pid = ' 
									. $this->conf[ 'timeFramePid' ]
									. ' ';
		$where					.= 'AND unix_timestamp() BETWEEN
										tx_newslead_leadperiod.startdate
										AND
										( tx_newslead_leadperiod.enddate
											+ 86399
										)
								';

		$result					= $this->db->exec_SELECT_mm_query(
									'tx_newslead_leadperiod.uid'
									, 'tt_news'
									, 'tt_news_tx_newslead_timeframes_mm'
									, 'tx_newslead_leadperiod'
									, $where
									, ''	// group by
									// order by
									, 'tx_newslead_leadperiod.startdate'
									, 1	// limit
								);

		if ( $result
			&& $return	= $this->db->sql_fetch_assoc( $result )
		)
		{
			$return				= $return[ 'uid' ];
		}

		return $return;
	}

	/**
	 * Returns true if lead has already been sent for this current user, news,
	 * and time frame. Otherwise returns false.
	 *
	 * @return boolean
	 */
	function isLeadSent()
	{
		// look in leads table to see if leadsent is checked for the current
		// user, news, and time frame
		$where					= '1 = 1
									AND leadsent = 1
									AND news_id = ' . $this->newsRow[ 'uid' ]
									. ' AND fe_user_id = '
										. $this->user[ 'uid' ]
									. ' AND leadtimeframe = '
										. $this->timeFrameUid
									. " AND filename = '"
										. $this->requestFile . "'";
 		$where					.= $this->newsObject->cObj->enableFields(
 									'tx_newslead_leads');
 
		$leadUid				= $this->db->exec_SELECTgetRows(
 									'uid'
 									, 'tx_newslead_leads'
 									, $where
 								);

		$leadSent				= ( 0 < count( $leadUid ) )
 									? true
 									: false;

		return $leadSent;
	}

	/**
	 * Send lead via email to sponsor contact.
	 *
	 * @ref http://typo3api.ueckermann.de/class_8tslib__content_8php-source.html#l05147
	 * 
	 * return void
	 */
	function sendLead()
	{
		// generate sponsor alert email
		$user					= $this->user;

		$to						= $this->sponsor[ 'contact_name' ];
		$to						.= ' <'. $this->sponsor[ 'contact_email' ]
									. '>';

		// grab template
		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab subpart
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, '###TEMPLATE_EMAIL###'
								);

		// create subpart marker array
		$subpartArray			= array(
			// locallang text parts
			'###ARTICLE_TEXT###'		=> $this->pi_getLL( 'article_text' )
			, '###LEAD_TEXT###'			=> sprintf(
												$this->pi_getLL( 'lead_text' )
												, $this->conf[ 'sitename' ]
											)
			, '###ARTICLE_TITLE_TEXT###'	=> $this->pi_getLL( 'article_title' )
			, '###FILE_NAME_TEXT###'	=> $this->pi_getLL( 'file_name' )
			, '###REFERRER_TEXT###'	=> $this->pi_getLL( 'referrer' )
			, '###REQUESTERS_TEXT###'	=> $this->pi_getLL( 'requesters_text' )

			// sponsor
			, '###SPONSOR_CONTACT_NAME###'	=> $this->sponsor[ 'contact_name' ]
			// news
			, '###ARTICLE_TITLE###'		=> $this->newsRow[ 'title' ]
			, '###FILE_NAME###'			=> basename( $this->requestFile )
		);

		// call news_userinfo for parsing user information
		$userinfo				= t3lib_div::getUserObj( 'tx_newsuserinfo' );
		$subpartArray			= $userinfo->extraItemMarkerProcessor(
									$subpartArray
									, $this->newsRow
									, $this->newsConf
									, $this->newsObject
								);

		// parse
		$body					= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $subpartArray
									, array()
									, array()
								);

		// as the email is sent plain text, remove html
		$body					= strip_tags( $body );
		$body					= trim( $body );

		$subject				= sprintf( $this->pi_getLL( 'subject' )
									, $this->user[ 'first_name' ]
									, $this->user[ 'last_name' ]
								);

		// email
		$this->newsObject->cObj->sendNotifyEmail( $subject . "\n" . $body
			, $to
			, $this->conf[ 'bcc' ]
			, $this->conf[ 'fromEmail' ]
			, $this->conf[ 'fromName' ]
			, $this->conf[ 'fromEmail' ]
		);

		$this->leadSent			= true;
	}

	/**
	 * Save lead to database
	 *
	 * @return void
	 */
	function saveLead()
	{
		$time					= time();

		$fields					= array(
									'pid'			=> $this->conf[ 'pid' ]
									, 'news_id'		=> $this->newsRow[ 'uid' ]
									, 'fe_user_id'	=> $this->user[ 'uid' ]
									, 'leadsent'		=> intval(
															$this->leadSent )
									, 'tstamp'			=> $time
									, 'crdate'			=> $time
									, 'date'			=> $time
									, 'leadtimeframe'	=> $this->timeFrameUid
									, 'filename'		=> $this->requestFile
									, 'referrer'		=> $_SERVER[ 'HTTP_REFERER' ]
								);

		$this->db->exec_INSERTquery( 'tx_newslead_leads', $fields );
        $this->sendLeads($this->newsRow['uid']);
	}

    // THIS IS A COPY OF THE SAME METHOD IN sponsor_content_scheduler/pi1/class.tx_sponsorcontentscheduler_pi1.php
    /**
     * Send outstanding leads.
     * Send means: set leadsent = 1 for it so that it shows up in the leads download
     * outstanding means: leads that have happened already but that the client
     *                    just now has paid for
     * This function should be called whenever the client buys leads.
     * It can probably also be called when a lead happens
     */
    function sendLeads($news_id)
    {
        if (!is_numeric($news_id))
            return;
        global $TYPO3_DB;

        // if unused_leads == 0: return
        $res = $TYPO3_DB->sql_query(sprintf(
            'SELECT tx_sponsorcontentscheduler_unused_leads FROM tt_news WHERE uid = %s',
            $TYPO3_DB->fullQuoteStr($news_id, '')));
        if ($TYPO3_DB->sql_num_rows($res) == 0)
            return;
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        $unused_leads = $row['tx_sponsorcontentscheduler_unused_leads'];
        if ($unused_leads == 0)
            return;

        // if unsent_leads == 0: return
        $res = $TYPO3_DB->sql_query(sprintf(
            'SELECT COUNT(1) AS unsent_leads FROM tx_newslead_leads WHERE news_id = %s AND leadsent = 0 AND hidden = 0 and deleted = 0',
                $TYPO3_DB->fullQuoteStr($news_id)));
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        $unsent_leads = $row['unsent_leads'];
        if ($unsent_leads == 0)
            return;

        $leads_to_send = min($unused_leads, $unsent_leads);

        // set leadsent = 1 for the leads_to_be_sent oldest leads
        $TYPO3_DB->sql_query(sprintf(
            'UPDATE tx_newslead_leads SET leadsent = 1 WHERE news_id = %s AND leadsent = 0 AND hidden = 0 AND deleted = 0 ORDER BY crdate LIMIT %d',
            $TYPO3_DB->fullQuoteStr($news_id), (int)$leads_to_send));

        // update unused_leads -= leads_to_be_sent
        $TYPO3_DB->sql_query(sprintf(
            'UPDATE tt_news SET tx_sponsorcontentscheduler_unused_leads = tx_sponsorcontentscheduler_unused_leads - %d WHERE uid = %s',
            (int)$leads_to_send, $TYPO3_DB->fullQuoteStr($news_id)));
    }
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_lead/class.tx_newslead.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_lead/class.tx_newslead.php' ]
// 	);
// }

?>
