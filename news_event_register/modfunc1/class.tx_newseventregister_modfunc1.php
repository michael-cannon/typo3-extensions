<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Michael Cannon (michael@peimic.com)
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
 * Module extension (addition to function menu) 'Leads Download' for the 'news_event_register' extension.
 *
 * @author	Michael Cannon <michael@peimic.com>
 */



require_once( PATH_t3lib . 'class.t3lib_extobjbase.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

class tx_newseventregister_modfunc1 extends t3lib_extobjbase
{
	// local database object
	var $db;
	// input size
	var $inputSize				= 20;
	// mulitple select size
	var $selectSize				= 15;
	// title length
	var $maxTitleLength			= 100;

	function modMenu ()
	{
		global $LANG;

		$menu					= array (
									'tx_newseventregister_modfunc1_check'	=>
										''
								);		

		return $menu;
	}

	/**
	 * Main function creating the content for the module.
	 *
	 * @return	string		HTML content for the module, actually a "section" made through the parent object in $this->pObj
	 */
	function main()
	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->db				= $GLOBALS['TYPO3_DB'];
		$this->db->debugOutput	= true;

		// check for download request
		if ( t3lib_div::_GP( 'tx_newseventregister_modfunc1_download' ) )
		{
			$this->doDownload();
		}

		// check for display request
		elseif ( t3lib_div::_GP( 'tx_newseventregister_modfunc1_display' ) )
		{
			$this->doDisplay();
		}

		// check that there's participants on this page, if not show 'error'
		$query					= "
			SELECT p.uid
			FROM tx_newseventregister_participants p
			WHERE
				1 = 1
				AND p.pid = {$this->pObj->id}
		";

		$result					= $this->db->sql_query( $query );

		if ( $result && ! $data = $this->db->sql_fetch_assoc( $result ) )
		{
			return $LANG->getLL( 'noresults' );
		}
		
		// theOutput is what's displayed on the function wizard content area
		$theOutput				= '';

		// interestingly we're already inside of a form element
		// therefore there's no need to create it ourselves
		// we just need to create the contents that we want to work with
		$theOutput				= $this->description();
		$theOutput				.= $this->downloadOptionForm();

		return $theOutput;
	}

	/**
	 * Determine which download is being requested and redirect to it.
	 *
	 * @return void
	 */
	function doDownload ()
	{
		$members				= $this->getParticipantData();

		// convert members to csv for download
		$membersCsv				= '';

		foreach ( $members as $key => $value )
		{
			$membersCsv			.= cbMkCsvString( $value );
		}

		$sqlParts				= $this->sqlParts();

		// download members list
		// send to browser as download
		cbBrowserDownload( $sqlParts[ 'filename' ], $membersCsv );
		exit();
	}

	/**
	 * Display participant query results
	 *
	 * @return void
	 */
	function doDisplay ()
	{
		global $LANG;

		$members				= $this->getParticipantData();

		echo cbArr2Html( $members, $LANG->getLL( 'title' )
			, ! t3lib_div::_GP( 'tx_newseventregister_modfunc1_survey' )
		);
	}

	/**
	 * Return array containing queried participant data.
	 *
	 * @return array
	 */
	function getParticipantData ()
	{
		$sqlParts				= $this->sqlParts();
		$sql					= $sqlParts[ 'select' ]
									. $sqlParts[ 'from' ]
									. $sqlParts[ 'where' ]
									. $sqlParts[ 'group' ]
									. $sqlParts[ 'order' ];

		// query database
		$result					= $this->db->sql_query( $sql );

		$members				= array();

		// push results into single rows CSV format
		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			// create headings
			$headers			= array_flip( array_keys( $data ) );
			unset( $headers[ 'cn_iso_3' ] );
			$headers			= array_flip( $headers );

			$headerCount		= count( $headers );

			for ( $i = 0; $i < $headerCount; $i++ )
			{
				$headers[ $i ]	= cbMkReadableStr( $headers[ $i ] );
			}

			$members[]			= $headers;

			// cycle through memeber results
			do
			{
				// load zone name if one
				$this->setZoneName( $data );

				// address needs new lines stripped out
				if ( isset( $data[ 'address' ] ) )
				{
					$data[ 'address' ]	= str_replace( "\n"
											, ' '
											, $data[ 'address' ]
										);
				}

				// push member onto members array
				$members[]				= $data;

				// once cycle complete, all done
			} while ( $data = $this->db->sql_fetch_assoc( $result ) );

			// free up our result set
			$this->db->sql_free_result( $result );
		}

		if ( t3lib_div::_GP( 'tx_newseventregister_modfunc1_survey' ) )
		{
			$members			= $this->appendSurvey( $members );
		}

		return $members;
	}

	/**
	 * Returns an array containing basic report sql parts in select, from,
	 * where, and order keys.
	 *
	 * @return array
	 */
	function sqlParts ()
	{
		$today					= cbSqlNow( true );

		$participant			= '';
		$surveySelect			= '';
		$surveyFrom				= '';
		$surveyWhere			= '';

		// grab participant type if available
		if ( $GLOBALS[ 'TCA' ][ 'fe_users' ][ 'columns' ]
				[ 'tx_bpmprofile_involvement' ]
			)
		{
			$participant		= '
				, CASE
					WHEN "" != u.tx_bpmprofile_involvement_text
						THEN u.tx_bpmprofile_involvement_text
					ELSE u.tx_bpmprofile_involvement
					END involvement
			';
		}

		if ( t3lib_div::_GP( 'tx_newseventregister_modfunc1_survey' ) )
		{
			// look up survey results
			// match results to particpants by news, member
			$surveySelect		= "
				, r.results
				, n.uid newsUid
			";

			$surveyFrom			= "
				LEFT JOIN tx_mssurvey_results r ON p.pid = r.pid
			";

			$surveyWhere		= "
				AND r.surveyid = n.uid
				AND r.fe_cruser_id = p.fe_user_id
				AND r.hidden = 0
				AND r.deleted = 0
			";
		}

		// build non-summary select statement
		if( ! t3lib_div::_GP( 'tx_newseventregister_modfunc1_summary' ) )
		{
			$select				= "
				SELECT DISTINCT
					FROM_UNIXTIME( p.crdate, '%M %e, %Y %l:%i %p' ) date
					, u.first_name
					, u.last_name
					, u.username
					, u.email
					, u.title
					, u.company
					, u.address
					, u.city
					, u.zone state
					, u.zip
					, sc.cn_iso_3
					, sc.cn_short_en country
					, u.telephone
					, u.fax
					, u.www
					, n.title event_title
					, c.title sponsor_name
					$participant
					$surveySelect
			";

			$from				= "
				FROM tx_newseventregister_participants p
					INNER JOIN fe_users u ON p.fe_user_id = u.uid
					INNER JOIN tt_news n ON p.news_id = n.uid
					LEFT JOIN tx_t3consultancies c ON n.tx_newssponsor_sponsor = c.uid
					LEFT JOIN static_countries sc ON u.static_info_country = sc.cn_iso_3
					$surveyFrom
			";

			$where				= "
				WHERE 1 = 1
					AND p.hidden = 0
					AND p.deleted = 0
					AND u.disable = 0
					AND u.deleted = 0
					AND n.hidden = 0
					AND n.deleted = 0
					/*
					AND p.pid = {$this->pObj->id}
					*/
					$surveyWhere
			";

			$group				= '';

			$order				= "
				ORDER BY p.crdate DESC
			";

			// make filename with today's date and memberlist
			$filename			= 'participants_' . $today . '.csv';
		}

		else
		{
			$select				= "
				SELECT
					nc.title event_category
					, n.title event_title
					, c.title sponsor_name
					, COUNT( u.uid ) registrations
			";

			$from				= "
				FROM tx_newseventregister_participants p
					INNER JOIN fe_users u
						ON p.fe_user_id = u.uid
					INNER JOIN tt_news n
						ON p.news_id = n.uid
					LEFT JOIN tx_t3consultancies c
						ON n.tx_newssponsor_sponsor = c.uid
					LEFT JOIN tt_news_cat_mm ncm
						ON p.news_id = ncm.uid_local
					LEFT JOIN tt_news_cat nc
						ON ncm.uid_foreign = nc.uid
			";

			$where				= "
				WHERE 1 = 1
					AND p.hidden = 0
					AND p.deleted = 0
					AND u.disable = 0
					AND u.deleted = 0
					AND n.hidden = 0
					AND n.deleted = 0
					/*
					AND p.pid = {$this->pObj->id}
					*/
			";

			$group				= "
				GROUP BY nc.uid ASC
					, n.uid ASC
					, c.uid ASC
			";

			$order				= "
			";

			// make filename with today's date and memberlist
			$filename			= 'participants_summary_' . $today . '.csv';
		}

		// build where clause from participants selections
		$startdate				= t3lib_div::_GP(
									'tx_newseventregister_modfunc1_startdate'
								);
		$where					.= ( $startdate )
									? ' AND n.tx_newseventregister_startdateandtime >= '
										. strtotime( $startdate )
										. "\n"
									: '';

		$enddate				= t3lib_div::_GP(
									'tx_newseventregister_modfunc1_enddate'
								);
		$where					.= ( $enddate )
									? ' AND n.tx_newseventregister_enddateandtime <= '
										. strtotime( $enddate )
										. "\n"
									: '';

		$sponsors				= t3lib_div::_GP(
									'tx_newseventregister_modfunc1_sponsors'
								);
		$where					.= ( 0 < count( $sponsors ) )
									? ' AND c.uid IN ( '
										. implode( ', ', $sponsors )
										. ' )'
										. "\n"
									: '';

		$news					= t3lib_div::_GP(
									'tx_newseventregister_modfunc1_news'
								);
		$where					.= ( 0 < count( $news ) )
									? ' AND n.uid IN ( '
										. implode( ', ', $news )
										. ' )'
										. "\n"
									: '';

		$newscategories			= t3lib_div::_GP(
								'tx_newseventregister_modfunc1_newscategories'
								);
		$where					.= ( 0 < count( $newscategories ) )
									? ' AND nc.uid IN ( '
										. implode( ', ', $newscategories )
										. ' )'
										. "\n"
									: '';

		$users					= t3lib_div::_GP(
									'tx_newseventregister_modfunc1_users'
								);
		$where					.= ( 0 < count( $users ) )
									? ' AND u.uid IN ( '
										. implode( ', ', $users )
										. ' )'
										. "\n"
									: '';

		$array					= array( 
									'select'		=> $select
									, 'from'		=> $from
									, 'where'		=> $where
									, 'order'		=> $order
									, 'group'		=> $group
									, 'filename'	=> $filename
								);

		return $array;
	}

	/**
	 * Set state long name for data row if available.
	 *
	 * @param & array member data
	 * @return array member data
	 */
	function setZoneName ( & $memberData )
	{
		// memberData has cn_iso_3 and zone 
		// match against
		//	z.zn_country_iso_3
		//	, z.zn_code
		// to get
		//	z.zn_name_local

		// memberData has zone and cn_iso_3
		$sql					= "
			SELECT 
				z.zn_name_local
			FROM 
				static_country_zones z
			WHERE 1 = 1
				AND z.zn_country_iso_3 = '{$memberData[ 'cn_iso_3' ]}'
				AND z.zn_code = '{$memberData[ 'state' ]}'
		";

		$result					= $this->db->sql_query( $sql );

		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$memberData[ 'state' ]	= $data[ 'zn_name_local' ];
		}

		unset( $memberData[ 'cn_iso_3' ] );
	}

	/**
	 * Returns string containing description of module.
	 *
	 * @return string
	 */
	function description ()
	{
		global $LANG;

		// section header followed by content
		$string					= $this->pObj->doc->section(
									$LANG->getLL( 'title' )
									, $LANG->getLL( 'description' )
									, 0
									, 1
								);

		return $string;
	}

	/**
	 * Returns string containing form components for selecting participants by one or
	 * more of the following; sponsor, date.
	 *
	 * @return string
	 */
	function downloadOptionForm ()
	{
		global $LANG;

		// display participants download input form
		// offer option to download participants by

		// news
		$news					= $this->getNews();
		$news					= $this->pObj->doc->section(
									$LANG->getLL( 'news' )
									, $news
									, 1
								);

		// newscategories
		$newscategories			= $this->getNewsCategories();
		$newscategories			= $this->pObj->doc->section(
									$LANG->getLL( 'newscategories' )
									, $newscategories
									, 1
								);

		// sponsor
		$sponsor				= $this->getSponsors();
		$sponsor				= $this->pObj->doc->section(
									$LANG->getLL( 'sponsor' )
									, $sponsor
									, 1
								);

		// date
		$startdate				= $this->getDate( 'startdate' );
		$startdate				= $this->pObj->doc->section(
									$LANG->getLL( 'startdate' )
									, $startdate
									, 1
								);

		$enddate				= $this->getDate( 'enddate' );
		$enddate				= $this->pObj->doc->section(
									$LANG->getLL( 'enddate' )
									, $enddate
									, 1
								);

		// user
		// $user					= $this->getUsers();
		$user					= $this->pObj->doc->section(
									$LANG->getLL( 'user' )
									, $user
									, 1
								);

		$input					= '
			<input type="checkbox" name="tx_newseventregister_modfunc1_summary"
				value="1" ' . ($this->getPreviousValue('tx_newseventregister_modfunc1_summary') == 1 ? ' checked ' : ''). '/>
		';
		$input					.= $LANG->getLL( 'summary' )
									. '<br />';

		$input					.= '
			<input type="checkbox" name="tx_newseventregister_modfunc1_survey"
				value="1" ' . ($this->getPreviousValue('tx_newseventregister_modfunc1_survey') == 1 ? ' checked ' : '') . ' />
		';
		$input					.= $LANG->getLL( 'survey' )
									. '<br />';

		$hidden					= '';

		// submit to download as CSV
		$submit					= '<input type="submit"
										name="tx_newseventregister_modfunc1_download"
										value="'
									. $LANG->getLL( 'submit_download' )
									. '" />';
		$submit					.= '&nbsp;';
		$submit					.= '<input type="submit"
										name="tx_newseventregister_modfunc1_display"
										value="'
									. $LANG->getLL( 'submit_display' )
									. '" />';
		$submit					.= '&nbsp;';
		$submit					.= '<input type="button" onclick="clearOptions()" value="'
									. $LANG->getLL( 'reset' )
									. '" />';
		$script 				=	' <script type="text/javascript" src="/typo3conf/ext/news_lead/clearOptions.js"></script> ';

		// combine and order the fields
		$string					= ''
									. $this->pObj->doc->spacer( 5 )
									. $input
									. $this->pObj->doc->spacer( 5 )
									. $submit
									. $news
									. $sponsor
									. $newscategories
//									. $user
									. $startdate
									. $enddate
									. $this->pObj->doc->spacer( 5 )
									. $input
									. $this->pObj->doc->spacer( 5 )
									. $hidden
									. $submit
									. $script;

		return $string;
	}

	/**
	 * Returns string of multiple select form with user's first and last name as
	 * labels and uid as value.
	 *
	 * @return string
	 */
	function getUsers ()
	{
		$string					= '
			<select name="tx_newseventregister_modfunc1_users[]"
				multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and participants table for fe_user uid and name
		$select					= "
			SELECT DISTINCT
				u.uid
				, CONCAT( u.first_name, ' ', u.last_name ) name
			FROM tx_newseventregister_participants p
				INNER JOIN fe_users u
					ON p.fe_user_id = u.uid
			WHERE
				1 = 1
				AND p.hidden = 0
				AND p.deleted = 0
				AND u.disable = 0
				AND u.deleted = 0
				AND p.pid = {$this->pObj->id}
			ORDER BY u.last_name ASC
				, u.first_name ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option'
									. $this->selectIfPreviouslySelected('tx_newseventregister_modfunc1_users', $data['uid'])
									.' value="' . $data[ 'uid' ] . '">'
									. substr( $data[ 'name' ]
										, 0
										, $this->maxTitleLength
									)
									. '</option>';
		}

		$string					.= '</select>';

		return $string;
	}

	/**
	 * Returns string of date input value line.
	 *
	 * @param string field name
	 * @return string
	 */
	function getDate ( $field )
	{
		$elementName = 'tx_newseventregister_modfunc1_' . $field;
		$string					= '<input type="text" name="' . $elementName . '"'
			. ' value = "' . $this->getPreviousValue($elementName) . '" '
			. ' size="' . $this->inputSize . '" />';

		return $string;
	}

	/**
	 * Returns string of multiple select form with sponsors title as labels and
	 * sponsors uid as value.
	 *
	 * @return string
	 */
	function getSponsors ()
	{
		$string					= '
			<select name="tx_newseventregister_modfunc1_sponsors[]"
				multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and participants table for new uid and title
		$select					= "
			SELECT DISTINCT
				c.uid
				, c.title
			FROM tx_newseventregister_participants p
				INNER JOIN tt_news n
					ON p.news_id = n.uid
				INNER JOIN tx_t3consultancies c
					ON n.tx_newssponsor_sponsor = c.uid
			WHERE
				1 = 1
				AND p.hidden = 0
				AND p.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND c.hidden = 0
				AND c.deleted = 0
				AND p.pid = {$this->pObj->id}
			ORDER BY c.title ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option'
									. $this->selectIfPreviouslySelected('tx_newseventregister_modfunc1_sponsors', $data['uid'])	
									. ' value="' . $data[ 'uid' ] . '">'
									. substr( $data[ 'title' ]
										, 0
										, $this->maxTitleLength
									)
									. '</option>';
		}

		$string					.= '</select>';

		return $string;
	}

	/**
	 * Returns string of multiple select form with news category title as labels
	 * and news uid as value.
	 *
	 * @return string
	 */
	function getNewsCategories ()
	{
		$string					= '
			<select name="tx_newseventregister_modfunc1_newscategories[]"
				multiple="multiple" size="' . $this->selectSize . '">
		';

		// query a join of string and participants table for new uid and title
		$select					= "
			SELECT DISTINCT
				nc.uid
				, nc.title
			FROM tx_newseventregister_participants p
				INNER JOIN tt_news n
					ON p.news_id = n.uid
				LEFT JOIN tt_news_cat_mm ncm
					ON p.news_id = ncm.uid_local
				LEFT JOIN tt_news_cat nc
					ON ncm.uid_foreign = nc.uid
			WHERE
				1 = 1
				AND p.hidden = 0
				AND p.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND p.pid = {$this->pObj->id}
			ORDER BY nc.title ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option' 
									. $this->selectIfPreviouslySelected('tx_newseventregister_modfunc1_newscategories', $data['uid'])						
									. ' value="' . $data[ 'uid' ] . '">'
									. substr( $data[ 'title' ]
										, 0
										, $this->maxTitleLength
									)
									. '</option>';
		}

		$string					.= '</select>';

		return $string;
	}

	/**
	 * Returns string of multiple select form with news title as labels and news
	 * uid as value.
	 *
	 * @return string
	 */
	function getNews ()
	{
		$string					= '
			<select name="tx_newseventregister_modfunc1_news[]"
				multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and participants table for new uid and title
		$select					= "
			SELECT DISTINCT
				n.uid
				, n.title
				, FROM_UNIXTIME( n.datetime, '%m/%d/%Y' ) date
			FROM tx_newseventregister_participants p
				INNER JOIN tt_news n
					ON p.news_id = n.uid
			WHERE
				1 = 1
				AND p.hidden = 0
				AND p.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND p.pid = {$this->pObj->id}
			ORDER BY n.title ASC
				, n.tx_newseventregister_startdateandtime DESC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option  ' 
									. $this->selectIfPreviouslySelected('tx_newseventregister_modfunc1_news', $data['uid'])
									. 'value="' . $data[ 'uid' ] . '">'
									. substr( $data[ 'title' ]
										, 0
										, $this->maxTitleLength
									)
									. ' ('
									. $data[ 'date' ]
									. ' )'
									. '</option>';
		}

		$string					.= '</select>';

		return $string;
	}

	/**
	 * Return array of survey questions and their options.
	 *
	 * @param integer news id
	 * @return array
	 */
	function loadQuestions ( $newsUid )
	{
		$questions				= array();

		$query					= "
			SELECT
				tx_mssurvey_items.title
				, tx_mssurvey_items.itemvalues
				, tx_mssurvey_items.type
			FROM tx_mssurvey_items
				, tt_news
				, tt_news_tx_mssurvey_items_mm
			WHERE
				tx_mssurvey_items.uid = tt_news_tx_mssurvey_items_mm.uid_foreign
				AND tt_news.uid = tt_news_tx_mssurvey_items_mm.uid_local
				AND tt_news.uid IN ( $newsUid )
				AND tx_mssurvey_items.hidden = 0
				AND tx_mssurvey_items.deleted = 0
			ORDER BY tt_news_tx_mssurvey_items_mm.sorting
		";

		$result					= $this->db->sql_query( $query );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$questions[]		= $data;
		}

		return $questions;
	}

	/**
	 * Return an array containing members data with survey results.
	 *
	 * @param array members array
	 * @return array
	 */
	function appendSurvey ( $data )
	{
		if ( 1 > count( $data ) )
		{
			return $data;
		}

		$questionRows			= array(
									'questions'		=> array()
									, 'items'		=> array()
									, 'keys'		=> array()
								);
		$dataRows				= array();

		// first row of data is headers
		$header					= array_shift( $data );

		// remove extra header columns
		$header					= array_values( $header );
		$header					= array_flip( $header );
		unset( $header[ 'results' ] );
		unset( $header[ 'Results' ] );
		unset( $header[ 'newsUid' ] );
		unset( $header[ 'cn_iso_3' ] );
		$header					= array_flip( $header );

		// count the columns to konw where to append data later on
		$columnCount			= count( $header );
		$questionColumnCount	= 0;

		// second and on is member rows
		$dataCount				= count( $data );

		// load questions
		$questions				= $this->loadQuestions(
									$data[ 0 ][ 'newsUid' ] );

		$questionCount			= count( $questions );

		// create question array - adjust column headers
		for ( $j = 0; $j < $questionCount; $j++ )
		{
			// add questions on first row header - title
			// use title to help match answers from user results
			$questionRows[ 'questions' ][]	= $questions[ $j ][
												'title' ];
			
			// question options are line break separated
			$items			= explode( "\n"
								, $questions[ $j ][
								'itemvalues' ]
							);
			$itemCount		= count( $items );

			// add question options on second row header - itemvalues
			for ( $k = 0; $k < $itemCount; $k++ )
			{
				$questionItem					= $items[ $k ];
				$questionRows[ 'items' ][]		= $questionItem;

				// create user result like entry
				// check boxes append title with answer
				if ( 3 != $questions[ $j ][ 'type' ] )
				{
					$questionRows[ 'keys' ][]		= '"'
								. $questions[ $j ][ 'title' ]
								. '":"'
								. trim( trim( $questionItem ), '@' )
								. '"';
				}

				else
				{
					$questionRows[ 'keys' ][]		= '"'
								. $questions[ $j ][ 'title' ]
								. '_'
								. trim( trim( $questionItem ), '@' )
								. '":"1"';
				}

				// when there's more than one item, another column needs
				// to be added to the question title
				if ( 0 < $k )
				{
					$questionRows[ 'questions' ][]	= '';
				}
			}
		}

		$questionColumnCount	= count( $questionRows[ 'questions' ] );

		// cycle through data to find the first news record
		// get survey questions
		for ( $i = 0; $i < $dataCount; $i++ )
		{
			$results			= $data[ $i ][ 'results' ];
			// $results			= preg_quote( $results, '#' );

			$resultsNew			= array_fill( 0, $questionColumnCount, '' );

			for ( $m = 0; $m < $questionColumnCount; $m++ )
			{
				$key			= $questionRows[ 'keys' ][ $m ];
				$key			= preg_quote( $key, '#' );

				if ( preg_match( '#' . $key . '#sim', $results ) )
				{
					$resultsNew[ $m ]	= trim( trim(
												$questionRows[ 'items' ][ $m ]
											)
											, '@'
										);
					$results			= preg_replace( '#' . $key . '#sim'
											, ''
											, $results
										);
				}

				elseif ( '' == $questionRows[ 'items' ][ $m ] )
				{
					// a bit much to pull the question title out of the keys
					// indice and in turn find the corresponding user survey
					// result
					$key		= preg_replace( '#(:")[^"]*"#'
									, '\1'
									, $questionRows[ 'keys' ][ $m ]
								);

					$key		= preg_quote( $key, '#' );
					$result		= preg_match( '#' . $key. '([^"]+)"#sim'
									, $results
									, $resultArray
								);

					$resultsNew[ $m ]	= $resultArray[ 1 ];
				}
			}

			// remove extra fields
			unset( $data[ $i ][ 'results' ] );
			unset( $data[ $i ][ 'newsUid' ] );

			$dataRows[]			= array_merge( $data[ $i ], $resultsNew );
		}
		
		// append survey questions to header
		$header					= array_merge( $header
									, $questionRows[ 'questions' ]
								);

		// add second row, padded for above headers, then tack on question
		// values
		$answerFiller			= array_fill( 0, $columnCount, '' );
		$headerItems			= array_merge( $answerFiller
									, $questionRows[ 'items' ]
								);

		// place headers before data
		array_unshift( $dataRows, $header, $headerItems );

		return $dataRows;
	}


	/**
	Returns ' selected ' if the value in a multiselect element whose name is passed
	was selected previously by the user.  This is meant to be used to plug right into
	the HTML for a SELECT element.
	How do we know item x is supposed to be selected?
	1. Is there a _POST value _POST[optionName] ?
	2. If so, does the value show up in the previously selected items:
	If array, is there a value match in the form POST?  If so, return " selected ".
	Else return a single space.
	@param string the name of the option element being tested
	@param string the value being tested
	@return Either the previous value, or if none, a single space.
	*/
	function selectIfPreviouslySelected($optionName, $value) {

		$isMatch = false;
		$returnValue = ' '; 
		$array = t3lib_div::_GP( $optionName ); 
		if ( $array ) {
			$isMatch = in_array( $value, $array );
		}
		if ($isMatch) {
			$returnValue = ' selected '; 
		}
		return $returnValue;

	}
	
	/**
	Returns the previous value (entered by the user) of the option element whose
	name is passed.
	How do we know item x is supposed to be selected?
	1. Is there a _POST value _POST[optionName] ?
	If scalar, is there a value in the form POST?  If so, return it.
	Else return blank string.
	If this is being used for a checkbox, and the "value" is 1 or 0, caller should convert
	1 to "checked" after getting the return value.
	@param string the name of the option element being tested
	@return Either the previous value, or if none, a blank string..
	*/
	function getPreviousValue($optionName) {

		$returnValue = ''; 
		if ( t3lib_div::_GP( $optionName ) ) {
			$returnValue = t3lib_div::_GP( $optionName );
			//echo $returnValue;
		}
		return $returnValue;

	}

	/**
	Set the default values for the form.
	*/
	function setDefaultFormValues() {
		if ($this->debug) {
			echo "setDefaultFormValues() <br>\n";
			echo 'count($_POST)' . count($_POST);
		}
		if (count($_POST)==0) {
			if ($this->debug) echo 'not set _post';
			$_POST['tx_newslead_modfunc1_summary']=1;
			$_POST['tx_newslead_modfunc1_nonsent']=1;
		}
	}



}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_event_register/modfunc1/class.tx_newseventregister_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_event_register/modfunc1/class.tx_newseventregister_modfunc1.php']);
}

?>
