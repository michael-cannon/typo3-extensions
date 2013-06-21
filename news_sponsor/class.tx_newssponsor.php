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
 * Adds sponsor display capabilities to tt_news.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newssponsor.php,v 1.1.1.1 2010/04/15 10:03:56 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

class tx_newssponsor extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newssponsor';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newssponsor.php';
	// The extension key.
	var $extKey					= 'news_sponsor';
	var $conf					= array();
	// template file
	var $templateFile			= 'EXT:news_sponsor/news_sponsor.tmpl';
	var $newsObject				= null;
	var $newsConf				= null;
	var $newsRow				= null;
	var $db						= null;
	var $sponsor				= null;
	var $markerArray			= array();
	var $sponsorUid				= null;

	function main ( $parentObject )
	{
		// current database object
		$this->db				= $GLOBALS[ 'TYPO3_DB' ];
		$this->db->debugOutput	= true;

		$this->newsObject		= $parentObject;
		$this->newsConf			= $this->newsObject->conf[ 'displaySingle.' ];
		$this->newsRow			= ( isset(
										$this->newsObject->local_cObj->data[
											'uid' ] )
									)
									? $this->newsObject->local_cObj->data
									: $this->loadNewsRow();

		// grab news_sponsor conf
		$this->conf				= $this->newsObject->conf[ 'news_sponsor.' ];

		//Initiate language
		$this->pi_loadLL();

		$templateflex_file		= $this->pi_getFFvalue(
									$this->newsObject->cObj->data['pi_flexform']
									, 'template_file'
									, 's_template'
								);
		$this->templateFile		= ( $templateflex_file )
									? 'uploads/tx_ttnews/' . $templateflex_file
									: $this->conf[ 'templateFile' ];

		$this->sponsorUid		= ( ! is_null( $this->sponsorUid ) )
									? $this->sponsorUid
									: $this->newsRow[ 'tx_newssponsor_sponsor' ]
								;

		$this->sponsor			= $this->loadSponsor( $this->sponsorUid );
	}

	/**
	 * Return array containing current news item.
	 *
	 * @return array
	 */
	function loadNewsRow()
	{
		$newsUid				= $this->newsObject->tt_news_uid;
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
		$newsItem				= ( 0 < count( $newsItem ) )
									? array_pop( $newsItem )
									: false;

		return $newsItem;
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
	 * Populate markerArray based upon sponsor existence.
	 *
	 * @return void
	 */
	function populateMarkerArray()
	{
		// check for sponsor, if none, blank the markerArray entries
		if ( $this->sponsor )
		{
			$this->populateSponsorLabels( $this->markerArray );
			$this->populateSponsorFields( $this->markerArray );
		}

		else
		{
			$this->blankSponsorFields( $this->markerArray );
		}
	}

	/**
	 * Return array containing sponsor information.
	 *
	 * @param integer user uid
	 * @return mixed array, false fail
	 */
	function loadSponsor ( $sponsorUid = false )
	{
		if ( ! $sponsorUid )
		{
			return false;
		}

		$where					= '1 = 1 AND uid = ' . $sponsorUid . ' ';
		$where					.= $this->newsObject->cObj->enableFields(
									'tx_t3consultancies');

		// refer to for the database functions ~/www/t3lib/class.t3lib_db.php
		$sponsor				= $this->db->exec_SELECTgetRows(
									'*'
									, 'tx_t3consultancies'
									, $where
								);

		// check that our sponsor array exists
		$sponsor				= ( 0 < count( $sponsor ) )
									? array_pop( $sponsor )
									: array();

		return $sponsor;
	}

	/**
	 * Create blanks for sponsor fields.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function blankSponsorFields( & $markerArray )
	{
		$markerArray[ '###SPONSOR_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_MESSAGE_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_TITLE_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_IMAGE_TITLE_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_DESCRIPTION_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_URL_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_CONTACT_EMAIL_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_CONTACT_NAME_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_SERVICES_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_LOGO_TEXT###' ] = '';
		$markerArray[ '###SPONSOR_COUNTRY_TEXT###' ] = '';

		$markerArray[ '###SPONSOR_UID###' ] = '';
		$markerArray[ '###SPONSOR_MESSAGE###' ] = '';

		$markerArray[ '###SPONSOR_TITLE###' ] = '';
		$markerArray[ '###SPONSOR_DESCRIPTION###' ] = '';
		$markerArray[ '###SPONSOR_URL###' ] = '';
		$markerArray[ '###SPONSOR_CONTACT_NAME###' ] = '';
		$markerArray[ '###SPONSOR_CONTACT_EMAIL###' ] = '';
		$markerArray[ '###SPONSOR_SERVICES###' ] = '';
		$markerArray[ '###SPONSOR_LOGO###' ] = '';
		$markerArray[ '###SPONSOR_COUNTRY###' ] = '';
	}

	function populateSponsorLabels( & $markerArray )
	{

		// pi_getLL entries come from locallang.php
		// these are labels
		$labelStdWrap			= $this->conf[ 'label.' ];
		$markerArray[ '###SPONSOR_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'sponsor' ), $labelStdWrap );

		$markerArray[ '###SPONSOR_MESSAGE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'message' ), $labelStdWrap );

		$markerArray[ '###SPONSOR_TITLE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title' ), $this->conf[ 'label.' ][ 'title.' ] );

		$markerArray[ '###SPONSOR_IMAGE_TITLE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'image_title'), $this->conf[ 'label.' ][ 'image.' ][ 'title.' ] );

		$markerArray[ '###SPONSOR_DESCRIPTION_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'description'), $labelStdWrap );

		$markerArray[ '###SPONSOR_URL_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'url' ), $labelStdWrap );

		$markerArray[ '###SPONSOR_CONTACT_EMAIL_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'contact_email'), $labelStdWrap );

		$markerArray[ '###SPONSOR_CONTACT_NAME_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'contact_name'), $labelStdWrap );

		$markerArray[ '###SPONSOR_SERVICES_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL('services' ), $labelStdWrap );

		$markerArray[ '###SPONSOR_LOGO_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'logo' ), $labelStdWrap );

		$markerArray[ '###SPONSOR_COUNTRY_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'country' ), $labelStdWrap );
	}

	/**
	 * Fill markerArray sponsor fields with localized translations.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function populateSponsorFields( & $markerArray )
	{
		$sponsor				= $this->sponsor;
		$stdWrap				= $this->conf[ 'field.' ];

		$markerArray[ '###SPONSOR_UID###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor[ 'uid' ], $stdWrap );

		$markerArray[ '###SPONSOR_MESSAGE###' ] = $this->newsObject->local_cObj->stdWrap( $this->newsRow[ 'tx_newssponsor_message' ], $stdWrap[ 'message.' ] );

		$markerArray[ '###SPONSOR_TITLE###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor['title'], $stdWrap[ 'title.' ] ); 

		$markerArray[ '###SPONSOR_DESCRIPTION###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor['description'], $stdWrap[ 'description.' ] );

		$this->newsConf['email_stdWrap.']['wrap'] = $stdWrap[ 'wrap' ];

		$markerArray[ '###SPONSOR_URL###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor['url'], $this->newsConf['email_stdWrap.'] );

		$markerArray[ '###SPONSOR_CONTACT_EMAIL###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor['contact_email'], $this->newsConf['email_stdWrap.'] );

		$markerArray[ '###SPONSOR_CONTACT_NAME###' ] = $this->newsObject->local_cObj->stdWrap( $sponsor['contact_name'], $stdWrap );

		$servicesUid			= $this->sponsor[ 'uid' ];

		$where					= 'AND tx_t3consultancies.uid = ' 
									. $servicesUid . ' ';
		$where					.= $this->newsObject->cObj->enableFields(
									'tx_t3consultancies');
		$where					.= $this->newsObject->cObj->enableFields(
									'tx_t3consultancies_cat');

		$serviceResult			= $this->db->exec_SELECT_mm_query(
									'tx_t3consultancies_cat.title'
									, 'tx_t3consultancies'
									, 'tx_t3consultancies_services_mm'
									, 'tx_t3consultancies_cat'
									, $where
								);

		$services				= '';

		if ( $serviceResult )
		{
			while ( $serviceResult
				&& $result = $this->db->sql_fetch_assoc(
					$serviceResult )
			)
			{
				$services		.= $this->newsObject->local_cObj->stdWrap(
									$result[ 'title' ]
									, $stdWrap[ 'services.' ][ 'inner.' ]
								);
			}

			$services			= $this->newsObject->local_cObj->stdWrap(
									$services
									, $stdWrap[ 'services.' ][ 'outer.' ]
								);
		}

		$markerArray[ '###SPONSOR_SERVICES###' ] = $services;

		// generate image
		$this->newsConf['image.']['file.']['maxW']	= $this->conf[ 'logoMaxW' ];
		$this->newsConf['image.']['file.']['maxH']	= $this->conf[ 'logoMaxH' ];
		$this->newsConf['image.']['altText']		= $sponsor[ 'title' ];

		// dir from t3consultancies logo tca
		$this->newsConf['image.']['file']	= 'uploads/tx_t3consultancies/'
												. $sponsor[ 'logo' ];

		$markerArray[ '###SPONSOR_LOGO###' ] = $this->newsObject->local_cObj->IMAGE( $this->newsConf[ 'image.' ] );

		// set URL as absolute
		$markerArray[ '###SPONSOR_LOGO###' ]	= preg_replace(
										'#(<img src=")(.*)#'
										, '\1'
											. $this->conf[ 'baseUrl' ]
											. '\2'
										, $markerArray[ '###SPONSOR_LOGO###' ]
									);

		$markerArray[ '###SPONSOR_IMAGE_TITLE_TEXT###' ] =
								( $markerArray[ '###SPONSOR_LOGO###' ] )
									? $markerArray[ '###SPONSOR_IMAGE_TITLE_TEXT###' ]
									: '';

		// get country
		$countryUid				= $sponsor[ 'cntry' ];

		$where					= '1 = 1 AND uid = ' . $countryUid . ' ';
		$where					.= $this->newsObject->cObj->enableFields(
									'static_countries');
		$country				= $this->db->exec_SELECTgetRows(
										'cn_short_en'
										, 'static_countries'
										, $where
									);

		// check that our country array exists
		$country				= ( 0 < count( $country ) )
									? $country[ 0 ][ 'cn_short_en' ]
									: '';

		$markerArray[ '###SPONSOR_COUNTRY###' ] = $this->newsObject->local_cObj->stdWrap( $country, $stdWrap );
	}

	function extraCodesProcessor( $parentObject )
	{
		$this->main( $parentObject );

		$content				= '';

		switch( $this->newsObject->theCode )
		{
			case 'SPONSOR':
				$content		= $this->getParsedSponsorTemplate();
				break;
		}
		
		return $content;
	}

	/**
	 * Return string contain parsed sponsor template.
	 *
	 * @return string
	 */
	function getParsedSponsorTemplate()
	{
		$this->populateMarkerArray();

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab subpart
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, '###TEMPLATE_SPONSOR###'
								);

		$content				= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, array()
									, array()
								);

		return $content;
	}
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_sponsor/class.tx_newssponsor.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_sponsor/class.tx_newssponsor.php' ]
// 	);
// }

?>
