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
 * Adds user personalization capabilities to tt_news.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newsuserinfo.php,v 1.1.1.1 2010/04/15 10:03:56 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

class tx_newsuserinfo extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newsuserinfo';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newsuserinfo.php';
	// The extension key.
	var $extKey					= 'news_userinfo';
	var $conf					= array();
	// template file
	var $templateFile			= 'EXT:news_userinfo/news_userinfo.tmpl';
	var $newsObject				= null;
	var $newsConf				= null;
	var $db						= null;
	var $user					= null;
	var $userUid				= null;
	var $markerArray			= array();

	function main ( $parentObject )
	{
		// current database object
		$this->db				= $GLOBALS[ 'TYPO3_DB' ];
		$this->db->debugOutput	= true;

		$this->newsObject		= $parentObject;
		$this->newsConf			= $this->newsObject->conf[ 'displaySingle.' ];

		// grab news_userinfo conf
		$this->conf				= $this->newsObject->conf[ 'news_userinfo.' ];

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

		// load user other than GLOBALS set
		if ( ! is_null( $this->userUid ) )
		{
			$this->user			= $this->loadUser( $this->userUid );
		}

		// load user from GLOBALS
		// generally currently logged in user
		else
		{
			$this->user			= $GLOBALS[ 'TSFE' ]->fe_user->user;
			$this->userUid		= $this->user[ 'uid' ];
		}
	}

	/**
	 * Return array containing user information based upon given userUid.
	 *
	 * @param integer user uid
	 * @return mixed array, false fail
	 */
	function loadUser ( $userUid = false )
	{
		$return					= array();

		if ( ! $userUid )
		{
			return $return;
		}

		$where					= '1 = 1 AND uid = ' . $userUid . ' ';
		// don't as some folks might be visitors
		// $where					.= $this->newsObject->cObj->enableFields(
		// 							'fe_users');

		// refer to for the database functions ~/www/t3lib/class.t3lib_db.php
		$user				= $this->db->exec_SELECTgetRows(
									'*'
									, 'fe_users'
									, $where
								);

		// check that our user array exists
		$user				= ( 0 < count( $user ) )
									? array_pop( $user )
									: $return;

		return $user;
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
	 * Populate markerArray based upon user existence.
	 *
	 * @return void
	 */
	function populateMarkerArray()
	{
		// check for user, if none, blank the markerArray entries
		if ( $this->user )
		{
			$this->populateUserLabels( $this->markerArray );
			$this->populateUserFields( $this->markerArray );
		}

		else
		{
			$this->blankUserFields( $this->markerArray );
		}
	}

	/**
	 * Create blanks for user fields.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function blankUserFields( & $markerArray )
	{
		$markerArray[ '###CONTACT_TEXT###' ] = '';
		$markerArray[ '###FIRST_NAME_TEXT###' ] = '';
		$markerArray[ '###LAST_NAME_TEXT###' ] = '';
		$markerArray[ '###EMAIL_ADDRESS_TEXT###' ] = '';
		$markerArray[ '###TELEPHONE_TEXT###' ] = '';
		$markerArray[ '###FAX_TEXT###' ] = '';
		$markerArray[ '###TITLE_TEXT###' ] = '';
		$markerArray[ '###COMPANY_TEXT###' ] = '';
		$markerArray[ '###WWW_TEXT###' ] = '';
		$markerArray[ '###ADDRESS_TEXT###' ] = '';
		$markerArray[ '###CITY_TEXT###' ] = '';
		$markerArray[ '###STATE_TEXT###' ] = '';
		$markerArray[ '###ZIP_TEXT###' ] = '';
		$markerArray[ '###COUNTRY_TEXT###' ] = '';
		$markerArray[ '###USER_UID_TEXT###' ] = '';
		$markerArray[ '###USER_USERNAME_TEXT###' ] = '';

		$markerArray[ '###FIRST_NAME###' ] = '';
		$markerArray[ '###LAST_NAME###' ] = '';
		$markerArray[ '###EMAIL_ADDRESS###' ] = '';
		$markerArray[ '###TELEPHONE###' ] = '';
		$markerArray[ '###FAX###' ] = '';
		$markerArray[ '###TITLE###' ] = '';
		$markerArray[ '###COMPANY###' ] = '';
		$markerArray[ '###WWW###' ] = '';
		$markerArray[ '###ADDRESS###' ] = '';
		$markerArray[ '###CITY###' ] = '';
		$markerArray[ '###STATE###' ] = '';
		$markerArray[ '###ZIP###' ] = '';
		$markerArray[ '###COUNTRY###' ] = '';
		$markerArray[ '###USER_UID###' ] = '';
		$markerArray[ '###USER_USERNAME###' ] = '';
	}

	function populateUserLabels( & $markerArray )
	{
		// pi_getLL entries come from locallang.php
		// these are labels
		$stdWrap			= $this->conf[ 'label.' ];

		$markerArray[ '###CONTACT_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'contact' ), $stdWrap );
		$markerArray[ '###FIRST_NAME_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'first_name' ), $stdWrap );
		$markerArray[ '###LAST_NAME_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'last_name' ), $stdWrap );
		$markerArray[ '###EMAIL_ADDRESS_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'email_address' ), $stdWrap );
		$markerArray[ '###TELEPHONE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'telephone' ), $stdWrap );
		$markerArray[ '###FAX_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'fax' ), $stdWrap );
		$markerArray[ '###TITLE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title' ), $stdWrap );
		$markerArray[ '###WWW_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'www' ), $stdWrap );
		$markerArray[ '###COMPANY_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'company' ), $stdWrap );
		$markerArray[ '###ADDRESS_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'address' ), $stdWrap );
		$markerArray[ '###CITY_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'city' ), $stdWrap );
		$markerArray[ '###STATE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'state' ), $stdWrap );
		$markerArray[ '###ZIP_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'zip' ), $stdWrap );
		$markerArray[ '###COUNTRY_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'country' ), $stdWrap );
		$markerArray[ '###USER_UID_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'uid' ), $stdWrap );
		$markerArray[ '###USER_USERNAME_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'username' ), $stdWrap );
	}

	/**
	 * Fill markerArray user fields with localized translations.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function populateUserFields( & $markerArray )
	{
		$user					= $this->user;
		$stdWrap				= $this->conf[ 'field.' ];
		$this->newsConf['email_stdWrap.']['wrap'] = $stdWrap[ 'wrap' ];

		$markerArray[ '###USER_UID###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'uid' ], $stdWrap );
		$markerArray[ '###USER_USERNAME###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'username' ], $stdWrap );
		$markerArray[ '###USER_PASSWORD###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'password' ], $stdWrap );

		$markerArray[ '###FIRST_NAME###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'first_name' ], $stdWrap );
		$markerArray[ '###LAST_NAME###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'last_name' ], $stdWrap );
		$markerArray[ '###EMAIL_ADDRESS###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'email' ], $this->newsConf['email_stdWrap.'] );
		$markerArray[ '###TELEPHONE###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'telephone' ], $stdWrap );
		$markerArray[ '###FAX###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'fax' ], $stdWrap );
		$markerArray[ '###TITLE###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'title' ], $stdWrap );
		$markerArray[ '###WWW###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'www' ], $this->newsConf['email_stdWrap.'] );
		$markerArray[ '###COMPANY###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'company' ], $stdWrap );
		$markerArray[ '###ADDRESS###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'address' ], $stdWrap );
		$markerArray[ '###CITY###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'city' ], $stdWrap[ 'address' ] );
		$markerArray[ '###STATE###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'zone' ], $stdWrap[ 'address' ] );
		$markerArray[ '###ZIP###' ] = $this->newsObject->local_cObj->stdWrap( $user[ 'zip' ], $stdWrap );

		// get country
		$countryUid				= $user[ 'static_info_country' ];

		$where					= "1 = 1 AND cn_iso_3 = '$countryUid' ";
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

		$markerArray[ '###COUNTRY###' ] = $this->newsObject->local_cObj->stdWrap( $country, $stdWrap );
	}

	function extraCodesProcessor( $parentObject )
	{
		$this->main( $parentObject );

		$content				= '';

		switch( $this->newsObject->theCode )
		{
			case 'USER_INFO':
				$content		= $this->getParsedUserTemplate();
				break;
		}
		
		return $content;
	}

	/**
	 * Return string contain parsed user template.
	 *
	 * @return string
	 */
	function getParsedUserTemplate()
	{
		$this->populateMarkerArray();

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab subpart
		$subpart				= $this->newsObject->cObj->getSubpart(
									$template
									, '###TEMPLATE_USERINFO###'
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
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_userinfo/class.tx_newsuserinfo.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_userinfo/class.tx_newsuserinfo.php' ]
// 	);
// }

?>
