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
 * Module extension (addition to function menu) 'Leads Report' for the 'news_lead' extension.
 * This is a cross-tabulated report that allows viewing sent/unsent/total leads
 * by month, category, and name.  Viewable as HTML and downloadable as Excel.
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newslead_modfunc2.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */



require_once( PATH_t3lib . 'class.t3lib_extobjbase.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

class tx_newslead_modfunc2 extends t3lib_extobjbase
{
	// local database object
	var $db;
	// input size
	var $inputSize				= 20;
	// mulitple select size
	var $selectSize				= 15;
	// title length
	var $maxTitleLength			= 100;
	//whether or not debug output should be shown.
	var $debug 						= false;
	

	function modMenu ()
	{
		global $LANG;

		$menu					= array (
									'tx_newslead_modfunc2_check'	=> ''
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
		if ( '59.94.208.148' == $_SERVER[ 'REMOTE_ADDR' ] ) {
			$this->debug = true; /* debugging */ /*exit();*/
			//phpinfo();
			//$this->testOptionRemembrance();
		}
		$this->setDefaultFormValues();
		
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->db				= $GLOBALS['TYPO3_DB'];
		$this->db->debugOutput	= true;

		// check for download request
		if ( t3lib_div::_GP( 'tx_newslead_modfunc2_download' ) )
		{
			$this->doDownload();
		}

		// check for display request
		elseif ( t3lib_div::_GP( 'tx_newslead_modfunc2_display' ) )
		{
			$this->doDisplay();
		}

		// check that there's participants on this page, if not show 'error'
		$query					= "
			SELECT l.uid
			FROM tx_newslead_leads l
			WHERE
				1 = 1
				AND l.pid = {$this->pObj->id}
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
	 * Return array containing queried lead data.
	 *
	 * @return array
	 */
	function getLeadData ()
	{
		$sqlParts				= $this->sqlParts();
		$sql					= $sqlParts[ 'select' ]
									. $sqlParts[ 'from' ]
									. $sqlParts[ 'where' ]
									. $sqlParts[ 'group' ]
									. $sqlParts[ 'order' ];

		// cbPrint2( 'sql', $sql ); exit();

		// query database
		$result					= $this->db->sql_query( $sql );
		if ($this->debug) echo $sql;

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
				// cbPrint2( 'data', $data );

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

		// return $this->dropOrderingFields($members);
		return $members;
	}
	
	/** Drop fields that are required only for ordering and not meant for display
	@param array db query result as array of array
	@return a new array of array with undesired fields dropped
	*/
	function dropOrderingFields($rows)
	{
		//drops the last column
		//this assumes the last column is undesired!
		$newRows = array();
		//print_r($rows);
		foreach ($rows as $row) {
			$columnCount = count($row);
			$newRows[] = array_slice($row, 0, $columnCount-1);
		}
		return $newRows;
	}


	/**
	 * Display lead query results
	 *
	 * @return void
	 */
	function doDisplay ()
	{
		global $LANG;

		$members				= $this->getLeadData();
		// cbPrint2( 'members', $members );
		echo cbArr2Html( $members, $LANG->getLL( 'title' ), true );
	}


	/**
	 * Determine which download is being requested and redirect to it.
	 *
	 * @return void
	 */
	function doDownload ()
	{
		global $LANG;

		$members				= $this->getLeadData();
		// download members list
		// send to browser as download
		//function cbArr2Html ( $array, $title = 'Query Results', $skipFirst = false, $repeatHeaders = true )
		$htmlData = cbArr2Html( $members, $LANG->getLL( 'title' ), false, false );
		
		cbBrowserDownload( 'LeadsSentByMonth-'.cbSqlNow( true ).'.xls', $htmlData );
		
		exit();
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

		// grab participant type if available
		if ( $GLOBALS[ 'TCA' ][ 'fe_users' ][ 'columns' ]
				[ 'tx_bpmprofile_involvement' ]
			)
		{
			$participant		= '
				, CASE
					WHEN "" NOT LIKE u.tx_bpmprofile_involvement_text
						THEN u.tx_bpmprofile_involvement_text
					ELSE u.tx_bpmprofile_involvement
					END involvement
			';
		}

		// build non-summary select statement
		if( ! t3lib_div::_GP( 'tx_newslead_modfunc2_summary' ) )
		{
			$select				= "
				SELECT
					FROM_UNIXTIME( l.crdate, '%M %e, %Y %l:%i %p' ) lead_date
					, u.first_name
					, u.last_name
					, u.email
					, u.title
					, u.company
					, u.address
					, u.city
					, u.zone
					, u.zip
					, sc.cn_iso_3
					, sc.cn_short_en country
					, u.telephone
					, u.fax
					, u.www
					/*
					, nc.title news_category
					*/
					, n.title news_title
					, c.title sponsor_name
					, l.filename
					$participant
			";

			$from				= "
				FROM tx_newslead_leads l
					INNER JOIN fe_users u
						ON l.fe_user_id = u.uid
					INNER JOIN tt_news n
						ON l.news_id = n.uid
					LEFT JOIN tx_t3consultancies c
						ON n.tx_newssponsor_sponsor = c.uid
					LEFT JOIN static_countries sc
						ON u.static_info_country = sc.cn_iso_3
					LEFT JOIN tt_news_cat_mm ncm
						ON l.news_id = ncm.uid_local
					LEFT JOIN tt_news_cat nc
						ON ncm.uid_foreign = nc.uid
			";

			$where				= "
				WHERE 1 = 1
					AND l.hidden = 0
					AND l.deleted = 0
					AND u.disable = 0
					AND u.deleted = 0
					AND n.hidden = 0
					AND n.deleted = 0
					AND l.pid = {$this->pObj->id}
			";

			$group				= "
				GROUP BY l.news_id ASC
					, u.uid ASC
			";

			$order				= "
				ORDER BY u.last_name ASC
					, u.first_name ASC
					, l.filename ASC
			";

			// make filename with today's date and memberlist
			$filename			= 'leads_' . $today . '.csv';
		}

		else
		{
			$select				= "
				SELECT
					nc.title Category
					, n.title Document_Title
					, FROM_UNIXTIME(l.date, '%M %Y' ) Month
					, c.title Sponsor
					, SUM(IF(l.leadsent=1, 1, 0)) AS Sent_Leads
					, SUM(IF(l.leadsent=0, 1, 0)) AS Unsent_Leads
					, COUNT( DISTINCT u.uid ) Total_Unique_Leads
					, FROM_UNIXTIME(n.datetime, '%M %e, %Y') Document_Date
					, GROUP_CONCAT( DISTINCT lp.description ORDER BY lp.description DESC SEPARATOR '; ' ) AS Lead_Period
					, FROM_UNIXTIME(l.date, '%Y %m' ) YearMonth
			";

			$from				= "
				FROM tx_newslead_leads l
					INNER JOIN fe_users u
						ON l.fe_user_id = u.uid
					INNER JOIN tt_news n
						ON l.news_id = n.uid
					LEFT JOIN tx_t3consultancies c
						ON n.tx_newssponsor_sponsor = c.uid
					LEFT JOIN tt_news_cat_mm ncm
						ON l.news_id = ncm.uid_local
					LEFT JOIN tt_news_cat nc
						ON ncm.uid_foreign = nc.uid
					LEFT JOIN tx_newslead_leadperiod lp
						ON l.leadtimeframe = lp.uid 
			";

			$where				= "
				WHERE 1 = 1
					AND l.hidden = 0
					AND l.deleted = 0
					AND u.disable = 0
					AND u.deleted = 0
					AND n.hidden = 0
					AND n.deleted = 0
					AND l.pid = {$this->pObj->id}
			";

			$group				= "
				GROUP BY
					nc.uid ASC
					, n.uid ASC
					, YearMonth DESC
			";

			$order				= "
				ORDER BY
					nc.title ASC
					, n.title ASC
			";

			// make filename with today's date and memberlist
			$filename			= 'leads_summary_' . $today . '.csv';
		}

		// build where clause from leads selections
		$startdate				= t3lib_div::_GP(
									'tx_newslead_modfunc2_startdate'
								);
		$where					.= ( $startdate )
									? ' AND l.crdate >= '
										. strtotime( $startdate )
										. "\n"
									: '';

		$enddate				= t3lib_div::_GP(
									'tx_newslead_modfunc2_enddate'
								);
		$where					.= ( $enddate )
									? ' AND l.crdate <= '
										. strtotime( $enddate )
										. "\n"
									: '';

		$sponsors				= t3lib_div::_GP(
									'tx_newslead_modfunc2_sponsors'
								);
		$where					.= ( 0 < count( $sponsors ) )
									? ' AND c.uid IN ( '
										. implode( ', ', $sponsors )
										. ' )'
										. "\n"
									: '';

		$news					= t3lib_div::_GP(
									'tx_newslead_modfunc2_news'
								);
		$where					.= ( 0 < count( $news ) )
									? ' AND n.uid IN ( '
										. implode( ', ', $news )
										. ' )'
										. "\n"
									: '';

		$newscategories			= t3lib_div::_GP(
									'tx_newslead_modfunc2_newscategories'
								);
		$where					.= ( 0 < count( $newscategories ) )
									? ' AND nc.uid IN ( '
										. implode( ', ', $newscategories )
										. ' )'
										. "\n"
									: '';

		$users					= t3lib_div::_GP(
									'tx_newslead_modfunc2_users'
								);
		$where					.= ( 0 < count( $users ) )
									? ' AND u.uid IN ( '
										. implode( ', ', $users )
										. ' )'
										. "\n"
									: '';

		$filenames				= t3lib_div::_GP(
									'tx_newslead_modfunc2_filenames'
								);
		$where					.= ( 0 < count( $filenames ) )
									? ' AND l.filename IN ( "'
										. implode( '", "', $filenames )
										. '" )'
										. "\n"
									: '';

		// exclude "tracking" leads
		$nonsent				= t3lib_div::_GP(
									'tx_newslead_modfunc2_nonsent'
								);
		$where					.= ( ! $nonsent )
									? ' AND l.leadsent = 1'
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
				AND z.zn_code = '{$memberData[ 'zone' ]}'
		";

		$result					= $this->db->sql_query( $sql );

		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$memberData[ 'zone' ]	= $data[ 'zn_name_local' ];
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
	 * Returns string containing form components for selecting leads by one or
	 * more of the following; sponsor, date, or filename.
	 *
	 * @return string
	 */
	function downloadOptionForm ()
	{
		global $LANG;

		// display leads download input form
		// offer option to download leads by

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

		// filename
		$filename				= $this->getFilenames();
		$filename				= $this->pObj->doc->section(
									$LANG->getLL( 'filename' )
									, $filename
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
			<input type="checkbox" name="tx_newslead_modfunc2_summary"
				value="1" ' . ($this->getPreviousValue('tx_newslead_modfunc2_summary') == 1 ? ' checked ' : ''). '/>
		';
		$input					.= $LANG->getLL( 'summary' )
									. '<br />';

		$input					.= '
			<input type="checkbox" name="tx_newslead_modfunc2_nonsent"
				value="1" ' . ($this->getPreviousValue('tx_newslead_modfunc2_nonsent') == 1 ? ' checked ' : '') . ' />
		';
		$input					.= $LANG->getLL( 'nonsent' )
									. '<br />';
		$hidden					= '';

		// submit to download as CSV
		$submit					= '<input type="submit"
										name="tx_newslead_modfunc2_download"
										value="'
									. $LANG->getLL( 'submit_download' )
									. '" />';
		$submit					.= '&nbsp;';
		$submit					.= '<input type="submit"
										name="tx_newslead_modfunc2_display"
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
									. $filename
									. $newscategories
//									. $user
									. $startdate
									. $enddate
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
			<select name="tx_newslead_modfunc2_users[]" multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and leads table for fe_user uid and name
		$select					= "
			SELECT DISTINCT
				u.uid
				, CONCAT( u.first_name, ' ', u.last_name ) name
			FROM tx_newslead_leads l
				INNER JOIN fe_users u
					ON l.fe_user_id = u.uid
			WHERE
				1 = 1
				AND l.hidden = 0
				AND l.deleted = 0
				AND u.disable = 0
				AND u.deleted = 0
				AND l.pid = {$this->pObj->id}
			ORDER BY u.last_name ASC
				, u.first_name ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option'
									. $this->selectIfPreviouslySelected('tx_newslead_modfunc2_users', $data['uid'])
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
		$elementName = 'tx_newslead_modfunc2_' . $field;
		$string					= '<input type="text" name="' . $elementName . '"'
			. ' value = "' . $this->getPreviousValue($elementName) . '" '
			. ' size="' . $this->inputSize . '" />';

		return $string;
	}

	/**
	 * Returns string of multiple select form with filename title as labels and
	 * filename as value.
	 *
	 * @return string
	 */
	function getFilenames ()
	{
		$string					= '
			<select name="tx_newslead_modfunc2_filenames[]" multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and leads table for new uid and title
		$select					= "
			SELECT DISTINCT
				l.filename
			FROM tx_newslead_leads l
			WHERE
				1 = 1
				AND l.hidden = 0
				AND l.deleted = 0
				AND l.pid = {$this->pObj->id}
			ORDER BY l.filename ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option'
									. $this->selectIfPreviouslySelected('tx_newslead_modfunc2_filenames', $data['filename'])
									.' value="' . $data[ 'filename' ]
									. '">'
									. substr( $data[ 'filename' ]
										, 0
										, $this->maxTitleLength
									)
									. '</option>';
		}

		$string					.= '</select>';

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
			<select name="tx_newslead_modfunc2_sponsors[]" multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and leads table for new uid and title
		$select					= "
			SELECT DISTINCT
				c.uid
				, c.title
			FROM tx_newslead_leads l
				INNER JOIN tt_news n
					ON l.news_id = n.uid
				INNER JOIN tx_t3consultancies c
					ON n.tx_newssponsor_sponsor = c.uid
			WHERE
				1 = 1
				AND l.hidden = 0
				AND l.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND c.hidden = 0
				AND c.deleted = 0
				AND l.pid = {$this->pObj->id}
			ORDER BY c.title ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option'
									. $this->selectIfPreviouslySelected('tx_newslead_modfunc2_sponsors', $data['uid'])	
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
			<select name="tx_newslead_modfunc2_newscategories[]"
				multiple="multiple" size="' . $this->selectSize . '">
		';

		// query a join of string and leads table for new uid and title
		$select					= "
			SELECT DISTINCT
				nc.uid
				, nc.title
			FROM tx_newslead_leads l
				INNER JOIN tt_news n
					ON l.news_id = n.uid
				LEFT JOIN tt_news_cat_mm ncm
					ON l.news_id = ncm.uid_local
				LEFT JOIN tt_news_cat nc
					ON ncm.uid_foreign = nc.uid
			WHERE
				1 = 1
				AND l.hidden = 0
				AND l.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND l.pid = {$this->pObj->id}
			ORDER BY nc.title ASC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option' 
									. $this->selectIfPreviouslySelected('tx_newslead_modfunc2_newscategories', $data['uid'])						
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
			<select name="tx_newslead_modfunc2_news[]" multiple="multiple"
				size="' . $this->selectSize . '">
		';

		// query a join of string and leads table for new uid and title
		$select					= "
			SELECT DISTINCT
				n.uid
				, n.title
				, FROM_UNIXTIME( n.datetime, '%m/%d/%Y' ) date
			FROM tx_newslead_leads l
				INNER JOIN tt_news n
					ON l.news_id = n.uid
			WHERE
				1 = 1
				AND l.hidden = 0
				AND l.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND l.pid = {$this->pObj->id}
			ORDER BY n.title ASC
				, n.datetime DESC
		";

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$string				.= '<option  ' 
									. $this->selectIfPreviouslySelected('tx_newslead_modfunc2_news', $data['uid'])
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
	Tests the functions selectIfPreviouslySelected() and getPreviousValue().
	*/
	function testOptionRemembrance() {

		$optionName = 'tx_newslead_modfunc2_sponsors'; 
		$value = 16;
		echo "\n<hr>Selected?:" . $this->selectIfPreviouslySelected($optionName, $value);

		$optionName = 'tx_newslead_modfunc2_startdate'; 
		echo "\n<hr>Previous value startdate:" . $this->getPreviousValue($optionName);

		$optionName = 'tx_newslead_modfunc2_nonsent'; 
		echo "\n<hr>Previous value nonsent:" . $this->getPreviousValue($optionName);

		$optionName = 'tx_newslead_modfunc2_summary';
		echo "\n<hr>Previous value count:" . $this->getPreviousValue($optionName);
		
				$input					= '
			<input type="checkbox" name="tx_newslead_modfunc2_summary"
				value="1" ' . ($this->getPreviousValue('tx_newslead_modfunc2_summary') == 1 ? ' checked ' : ''). '/>
		';
		echo $input;
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
			$_POST['tx_newslead_modfunc2_summary']=1;
			$_POST['tx_newslead_modfunc2_nonsent']=1;
		}
	}

}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_lead/modfunc1/class.tx_newslead_modfunc2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_lead/modfunc1/class.tx_newslead_modfunc2.php']);
}

?>
