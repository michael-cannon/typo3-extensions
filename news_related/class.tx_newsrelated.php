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
 * Adds related news display capabilities to tt_news.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newsrelated.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

class tx_newsrelated extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newsrelated';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newsrelated.php';
	// The extension key.
	var $extKey					= 'news_related';
	var $conf					= array();
	// template file
	var $templateFile			= 'EXT:news_related/news_related.tmpl';
	var $newsObject				= null;
	var $newsConf				= null;
	var $newsRow				= null;
	var $db						= null;
	var $relatedNews			= null;
	var $relatedCategories		= null;
	var $markerArray			= array();

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

		// grab news_related conf
		$this->conf				= $this->newsObject->conf[ 'news_related.' ];

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

		$this->relatedCategories	= $this->getRelatedCategories();
		$this->relatedNews		= $this->getRelatedNews();
	}

	/**
	 * Return array containing current news item.
	 *
	 * @return array
	 */
	function loadNewsRow()
	{
		$newsStartUid			= $this->newsObject->config[ 'listStartId' ];
		$newsUid				= ( ! $newsStartUid )
									? $this->newsObject->tt_news_uid
									: $newsStartUid;
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
		$this->newsConf				= $lConf;
		$this->markerArray			= $markerArray;
		$this->populateMarkerArray();

		return $this->markerArray;
	}

	/**
	 * Populate markerArray based upon related existence.
	 *
	 * @return void
	 */
	function populateMarkerArray()
	{
		// check for relatedNews, if none, blank the markerArray entries
		if ( $this->relatedNews )
		{
			$this->populateRelatedLabels( $this->markerArray );
		}

		else
		{
			$this->blankRelatedFields( $this->markerArray );
		}
	}

	/**
	 * Return array containing related news items by current news.
	 *
	 * @return mixed array, false fail
	 */
	function getRelatedNews ()
	{
		$relatedUids			= $this->relatedCategories;

		if ( ! $relatedUids || ! $this->newsRow[ 'uid' ] )
		{
			return false;
		}

		$relatedUids			= implode( ',', $relatedUids );

		$where					= 'AND tt_news_cat.uid IN ('
									. $relatedUids
									. ') ';
		// don't include current news item
		$where					.= 'AND tt_news.uid != '
									. $this->newsRow[ 'uid' ];
		$where					.= $this->newsObject->cObj->enableFields(
									'tt_news');
		$where					.= $this->newsObject->cObj->enableFields(
									'tt_news_cat');

		$relatedResult			= $this->db->exec_SELECT_mm_query(
									'tt_news_cat.uid cat_uid'
									. ', tt_news_cat.title cat_title'
									. ', tt_news.*'
									, 'tt_news'
									, 'tt_news_cat_mm'
									, 'tt_news_cat'
									, $where
									, ''	// group by
									, 'tt_news.datetime DESC'	// order by
								);

		$relateds				= array();

		while ( $relatedResult
			&& $result = $this->db->sql_fetch_assoc( $relatedResult )
		)
		{
			// category uid
			$relateds[]			= $result;
		}

		return $relateds;
	}

	/**
	 * Return array containing related news items by current news.
	 *
	 * @return mixed array, false fail
	 */
	function getRelatedCategories ()
	{
		$baseArray				= array( 0 );
		$relatedUid				= $this->newsRow[ 'uid' ];

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
									. ', tt_news_cat.title'
									, 'tt_news'
									, 'tt_news_cat_mm'
									, 'tt_news_cat'
									, $where
								);

		$relateds				= $baseArray;

		while ( $relatedResult
			&& $result = $this->db->sql_fetch_assoc( $relatedResult )
		)
		{
			// category uid
			$relateds[]			= $result[ 'uid' ];
		}

		return $relateds;
	}

	/**
	 * Create blanks for related fields.
	 *
	 * @param & array marker array
	 * @return void
	 */
	function blankRelatedFields( & $markerArray )
	{
		$markerArray[ '###RELATED_TITLE_TEXT###' ] = '';
	}

	function populateRelatedLabels( & $markerArray )
	{
		// pi_getLL entries come from locallang.php
		// these are labels
		$labelStdWrap			= $this->conf[ 'label.' ];

		$markerArray[ '###RELATED_TITLE_TEXT###' ] = $this->newsObject->local_cObj->stdWrap( $this->pi_getLL( 'title' ), $labelStdWrap[ 'title.' ] );
	}

	function extraCodesProcessor( $parentObject )
	{
		$this->main( $parentObject );

		$content				= '';

		switch( $this->newsObject->theCode )
		{
			case 'RELATED':
				$content		= $this->getParsedRelatedTemplate();
				break;
		}
		
		return $content;
	}

	/**
	 * Return string contain parsed related template.
	 *
	 * @return string
	 */
	function getParsedRelatedTemplate()
	{
		$this->populateMarkerArray();

		$template				= $this->newsObject->cObj->fileResource(
									$this->templateFile
								);

		// grab subpart
		$subpart				= $this->newsObject->getNewsSubpart(
									$template
									, '###TEMPLATE_RELATED###'
								);

		$subpartNews			= $this->newsObject->getLayouts( $subpart
									, 1
									, 'NEWS'
								);

		$selectConf				= $this->newsObject->getSelectConf( '' );

		$contentMarkerArray		= array();
		$contentMarkerArray[ '###CONTENT###' ] =
								$this->newsObject->getListContent(
									$subpartNews
									, $selectConf
									, 'displayList'
								);

		// Remove header if no contnet to show
		if( ! $contentMarkerArray[ '###CONTENT###' ] )
		{
			$this->blankRelatedFields( $contentMarkerArray );
		}

		$content				= $this->newsObject->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, $contentMarkerArray
								);

		return $content;
	}

	function processSelectConfHook( $parentObject, $selectConf )
	{
		$this->main( $parentObject );

		if ( 'RELATED' == $this->newsObject->theCode )
		{
			$selectConf[ 'selectFields' ]	= 'DISTINCT tt_news.uid, tt_news.*';
			$selectConf[ 'leftjoin' ]		= '
				tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local
			';

			if ( $this->newsRow[ 'uid' ] )
			{
				$selectConf[ 'where' ]		.= '
					AND tt_news_cat_mm.uid_foreign IN ( '
					. implode( ',' , $this->relatedCategories )
					. ' )'
					. ' AND tt_news.uid != ' . $this->newsRow[ 'uid' ];
			}

			$selectConf[ 'max' ]			= $this->newsObject->config[
												'limit' ];
			$selectConf[ 'orderBy' ]		= $this->newsObject->config[ 'orderBy' ];
			$selectConf[ 'orderBy' ]		.= ' '
												. $this->newsObject->config[ 'ascDesc' ]
												;
		}

		return $selectConf;
	}
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_related/class.tx_newsrelated.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_related/class.tx_newsrelated.php' ]
// 	);
// }

?>
