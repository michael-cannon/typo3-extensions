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
 * Module 'Membership List' for the 'cb_sttwcc_membership' extension.
 *
 * @author	Michael Cannon <michael@peimic.com>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:cb_sttwcc_membership/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

// load Excel Parser
require_once( CB_COGS_DIR_THIRD_PARTY . 'abc_parserpro/excelparser.php' );

class tx_cbsttwccmembership_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $fileName				= 'cb_sttwcc_membership_excel_file';
	var $storagePid				= 4;
	// var $storagePid				= 187;

	// column numbers, recall off by -1 or column a, b, c... relation
	// done to line up with Excel parser

	// name is company
	var $nameCol				= 0;
	var $indivnameCol			= 1;
	var $address1Col			= 2;
	//ad2 var $address2Col			= 3;
	var $cityCol				= 3;
	var $stateCol				= 4;
	var $zipCol					= 5;
	var $phone1Col				= 6;
	var $faxCol					= 7;

	var $emailCol				= 8;
	var $websiteCol				= 9;
	var $listing1Col			= 10;
	var $listing2Col			= 11;
	var $listing3Col			= 12;

	var $listingCount			= 3;
	var $columnCount			= 13;

	// parsed file
	var $excel					= null;

	var $time					= null;

	// data containers
	var $categories				= null;
	var $categoryNames			= null;

	var $users					= null;
	var $userNames				= null;

	var $companies				= null;

	var $stateList				= null;

	var $groupNewsletter		= 1;
	var $groupPaid				= 2;
	var $groupWebsiteUser		= 3;

	// database connection object
	var $db						= null;

	/**
	 *  Initializer
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();

		// make database connection a little easier to type
		$this->db				= & $GLOBALS[ 'TYPO3_DB' ];

		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"),
				"2" => $LANG->getLL("function2"),
				"3" => $LANG->getLL("function3"),
			)
		);
		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	
		{
	
				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post"
								enctype="multipart/form-data">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);

			
			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:
				/*
				$content="<div align=center><strong>Hello World!</strong></div><BR>
					The 'Kickstarter' has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script '".substr(t3lib_extMgm::extPath("cb_sttwcc_membership"),strlen(PATH_site))."mod1/index.php' and edit it!
					<HR>
					<BR>This is the GET/POST vars sent to the script:<BR>".
					"GET:".t3lib_div::view_array($GLOBALS["HTTP_GET_VARS"])."<BR>".
					"POST:".t3lib_div::view_array($GLOBALS["HTTP_POST_VARS"])."<BR>".
					"";
				$this->content.=$this->doc->section("Message #1:",$content,0,1);
				*/
				$action			= t3lib_div::_POST( 
									'cb_sttwcc_membership_action' 
								);

				switch( $action )
				{
					// upload
					// import
					// show results
					case 'doUpload':
						$content		= $this->doUpload();
						break;

					// show upload
					case 'showUpload':
					default:
						$content		= $this->uploadForm();
						break;
				}

				$this->content	.= $this->doc->section( 
									"Memberlist Upload"
									, $content
									, 1
									, 1
								);
			break;
			case 2:
				$content="<div align=center><strong>Menu item #2...</strong></div>";
				$this->content.=$this->doc->section("Message #2:",$content,0,1);
			break;
			case 3:
				$content="<div align=center><strong>Menu item #3...</strong></div>";
				$this->content.=$this->doc->section("Message #3:",$content,0,1);
			break;
		} 
	}


	/**
	 * Returns string of upload form
	 *
	 * @return string
	 */
	function uploadForm ()
	{
		$string					= '';

		// Backend section is already wrapped in form elements, just need to
		// stick the little things here
		$string					.=<<<EOD
	Excel file: <input type="file" name="{$this->fileName}" />
	<input type="hidden" name="cb_sttwcc_membership_action" value="doUpload" />
	<!-- check that a file is marked for upload -->
	<input type="button" value="Upload"
		onclick="javascript:
			if ( document.forms[0].{$this->fileName}.value.length == 0 )
			{
				alert( 'Please browse for a file to upload.' ); 
				return; 
			}; 
			submit();"
	/>
EOD;

		return $string;
	}

	/**
	 * Returns string containing summary information of records uploaded.
	 *
	 * @return string
	 */
	function doUpload ()
	{
		// check for file
		// grab temp name
		// send off to parser
		// following ideas for loading categories, user, companies

		$string					= '';

		// javascript scripts forces file submission 
		$excel_file				= $_FILES[ $this->fileName ];

		if ( $excel_file )
		{
			$excel_file			= $excel_file['tmp_name'];
		}

		if( '' == $excel_file )
		{
			$string				= "No file uploaded";

			return $string;
		}

		$this->excel			= & new ExcelFileParser();
		$res					= $this->excel->ParseFromFile( $excel_file );

		switch ( $res )
		{
			case 0:
				$string			= '';
				break;

			case 1:
				$string			= "Can't open file";
				break;

			case 2:
				$string			= "File too small to be an Excel file";
				break;

			case 3:
				$string			= "Error reading file header";
				break;

			case 4:
				$string			= "Error reading file";             
				break;

			case 5:
				$string			= "This is not an Excel file or file stored in"
			   						. " Excel < 5.0";
				break;

			case 6:
				$string			= "File corrupted";                 
				break;

			case 7:
				$string			= "No Excel data found in file";    
				break;

			case 8:
				$string			= "Unsupported file version";       
				break;

			default:                                         
				$string			= "Unknown error";                      
				break;
		}

		// string contents dictates error
		if ( '' != $string )
		{
			return $string;
		}

		// sample dump
		// cbPrint2( 'this->excel', $this->excel );

		// is there something to import?
		if ( 0 != count( $this->excel->sst[ 'data' ] ) )
		{
			$this->time				= time();

			// load/check/update categories
			$string					.= $this->manageCategories();

			$this->loadStateList();

			// load/check/update users
			$string					.= $this->manageUsers();

			// load/check/update companies
			$string					.= $this->manageCompanies();
		}

		else
		{
			$string				.= '<p>No data found to import</p>';
		}

		$string					.= '<p><a href="javascript:history.go( -1 );"
										>Previous Page</a></p>';

		return $string;
	}

	/**
	 * Returns summary string of category management.
	 *
	 * load/check/update categories
	 *
	 * @return string
	 */
	function manageCategories ()
	{
		$string					= '';

		$this->loadCategories();

		if ( 0 == count( $this->categories ) )
		{
			$string				.= 'No pre-existing categories found.<br />';
		}
				
		$listings				= array();

		for ( $i = 1; $i <= $this->listingCount; $i++ )
		{
			// listing1Col...
			$columnId			= 'listing' . $i . 'Col';
			$columnData			= & $this->getExcelColumnData( 
									$this->{$columnId}
								);

			$listings			= array_merge( $listings, $columnData );
		}

		$listings				= array_unique( $listings );
		// cbPrint2( 'unique listings', $listings );

		// grab excel categories from listing?
		// remove loaded categories from new listings
		// combine with new
		// $listings				= array_diff( $listings, $this->categoryNames );
		foreach( $listings as $key => $value )
		{
			if ( in_array( $value, $this->categoryNames ) )
			{
				unset( $listings[ $key ] );
			}
		}

		// cbPrint2( 'diff listings', $listings );

		$listingsCount			= count( $listings );

		// update/insert missing
		$string					.= $listingsCount
									. ' categories added.<br />';

		foreach ( $listings as $key => $value )
		{
			$insertArray		= array(
									'pid'		=> $this->storagePid
									, 'tstamp'	=> $this->time
									, 'crdate'	=> $this->time
//									, 'cruser_id'	=> $value
									, 'deleted'	=> 0
									, 'title'	=> $value
								);

			$result				= $this->db->exec_INSERTquery(
									'tx_t3consultancies_cat'
									, $insertArray
								);
		}
	
		$this->loadCategories();

		if ( 0 == count( $this->categories ) )
		{
			$string				.= 'No categories found.<br />';
		}

		// cbPrint2( 'loaded categories', $this->categoryNames );
		// cbPrint( $string );
		// exit;
				
		return $string;
	}

	/**
	 * Load internal category array.
	 *
	 * @return void
	 */
	function loadCategories()
	{
		// load full current categories
		$select					= "
			SELECT
				uid
				, pid
				, tstamp
				, crdate
				, cruser_id
				, deleted
				, title
			FROM tx_t3consultancies_cat
			WHERE 1 = 1
				AND pid = {$this->storagePid}
				AND deleted = 0
		";

		$result					= $this->db->sql_query( $select );
		$loadedCategories		= array();
		$loadedCategoryNames	= array();

		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			do
			{
				$loadedCategories[]						= $data;
				$uid									= $data[ 'uid' ];
				$loadedCategoryNames[ $uid ]			= $data[ 'title' ];
			} while ( $data = $this->db->sql_fetch_assoc( $result ) );

			@$this->db->sql_free_result();
		}

		$this->categories		= $loadedCategories;
		$this->categoryNames	= $loadedCategoryNames;
		// cbPrint2( 'categories', $this->categories );
		// cbPrint2( 'categoryNames', $this->categoryNames );
	}

	/**
	 * Returns array with column data from parsed Excel object.
	 *
	 * @param integer column key
	 * @return array
	 */
	function & getExcelColumnData( $columnKey )
	{
		$array					= array();

		// worksheet with cell data coordinates
		$ws						= & $this->excel->worksheet[ 'data' ][ 0 ];

		// type, data, font container
		$cells					= & $ws[ 'cell' ];

		// type 0 data container
		$sst					= & $this->excel->sst;

		$max_row				= $ws[ 'max_row' ];
		$max_col				= $ws[ 'max_col' ];

		// don't grab invalid column
		if ( $this->columnCount <= $columnKey ||  $max_col < $columnKey )
		{
			return $array;
		}

		for ( $i = 0; $i < $max_row; $i++ )
		{
			$cell				= & $cells[ $i ][ $columnKey ];
			$data				= $cell[ 'data' ];
			$type				= $cell[ 'type' ];

			switch ( $type )
			{
				// string
				case 0:
					$value		= $sst[ 'unicode' ][ $data ];

					if ( cbIsNullBlank( $value ) )
					{
						$value	= $sst[ 'data' ][ $data ];
					}
					break;

				// integer
				// number
				case 1:
					$value		= $data;
					break;

				// float
				// number
				case 2:
					$value		= $data;
					break;

				// date
				case 3:
					$value		= $data;
					break;                               

				default:                                 
					$value		= 'data type unknown';
					break;                               
			}

			$array[]			= $value;
		}

		return $array;
	}

	/**
	 * Returns summary string of user management.
	 *
	 * load/check/update users
	 *
	 * @return string
	 */
	function manageUsers ()
	{
		$string					= '';

		$this->loadUsers();

		if ( 0 == count( $this->users ) )
		{
			$string				.= 'No pre-existing users found.<br />';
		}
				
		// columns relating to user data in excel file
		$userColKeys			= array(
									'name'			=> 'indivnameCol'
									, 'company'		=> 'nameCol'
									, 'address1'	=> 'address1Col'
		//ad2							, 'address2'	=> 'address2Col'
									, 'city'		=> 'cityCol'
									, 'state'		=> 'stateCol'
									, 'zip'			=> 'zipCol'
									, 'telephone'	=> 'phone1Col'
									, 'fax'			=> 'faxCol'
									, 'email'		=> 'emailCol'
									, 'www'			=> 'websiteCol'
								);

		$listings				= & $this->getExcelColumnsData( $userColKeys );

		// cbPrint2( 'listings', $listings );

		$listingsCount			= count( $listings );
		$removed				= array();
		$currentUserNames		= & $this->userNames;
		// cbPrint2( 'this->userNames', $this->userNames );

		// diff listings and users
		for ( $i = 0; $i < $listingsCount; $i++ )
		{
			$foundKey			=  array_search( $listings[ $i ][ 'name' ]
									, $currentUserNames 
								) ;
			if ( $foundKey )
			{
				$removed[ $foundKey ]		= $this->users[ $foundKey ]
												[ 'usergroup' ];
				unset( $listings[ $i ] );
			}
		}

		// cbPrint2( 'listings', $listings );
		// cbPrint2( 'removed', $removed );

		$removedCount			= count( $removed );
		
		// update/insert missing
		$string					.= $removedCount
									. ' members viewable.<br />';

		// remove paid membership grouping from user
		$this->removePaidUsergroup( $removed );

		// TODO reinstate paid membership grouping to user

		$listingsCount			= count( $listings );
		$string					.= $listingsCount
									. ' members added.<br />';

		foreach ( $listings as $key => $value )
		{
			$name				= $value[ 'name' ];
			$email				= $value[ 'email' ];

			$hasEmail			= ! cbIsNullBlank( $email );
									
			$username			= preg_replace( '#[^[:alnum:]]#'
									, ''
									, $name
								);

			// random, unique password
			$password			= uniqid( time() );
			$password			= strrev( $password );
			$password			= substr( $password, 0, 8 );

			$address			= $value[ 'address1' ];
	//ad2		$address			.= ( ! cbIsNullBlank( $value[ 'address2' ] ) )
	//ad2								? "\r\n" . $value[ 'address2' ]
	//ad2								: '';

			$state				= array_search( $value[ 'state' ]
									, $this->stateList
								);

			$country			= 'USA';

			$www				= $value[ 'www' ];

			$disable			= ( 'email' != $email )
									? 0
									: 1;

			// 1 newsletter, 2 paid membership, 3 website user
			$usergroup			= $this->groupNewsletter
									. ','
									. $this->groupPaid
									. ','
									. $this->groupWebsiteUser;

			$insertArray		= array(
									'pid'			=> $this->storagePid
									, 'tstamp'		=> $this->time
									, 'username'	=> $username
									, 'password'	=> $password
									, 'disable'		=> $disable
									, 'usergroup'	=> $usergroup
									, 'name'		=> $name
									, 'address'		=> $address
									, 'telephone'	=> $value[ 'telephone' ]
									, 'fax'			=> $value[ 'fax' ]
									, 'email'		=> $email
									, 'crdate'		=> $this->time
									, 'zip'			=> $value[ 'zip' ]
									, 'city'		=> $value[ 'city' ]
									, 'country'		=> $country
									, 'www'			=> $www
									, 'company'		=> $value[ 'company' ]
									, 'module_sys_dmail_category'		=> 1
									, 'module_sys_dmail_html'			=> 1
									, 'tx_cbsttwccmembership_country'	=> 220
									, 'tx_cbsttwccmembership_zone'	=> $state
								);

			if ( true )
			{
				$result			= $this->db->exec_INSERTquery(
									'fe_users'
									, $insertArray
								);
			}

			else
			{
				cbPrint2( 'insertArray', $insertArray );
			}
		}
	
		$this->loadUsers();

		if ( 0 == count( $this->users ) )
		{
			$string				.= 'No new users found.<br />';
		}
				
		// cbPrint( $string );
		// exit();

		return $string;
	}

	/**
	 * Load internal user array.
	 *
	 * @return void
	 */
	function loadUsers()
	{
		// load full current users
		$select					= "
			SELECT
				uid
				, pid
				, tstamp
				, username
				, password
				, usergroup
				, name
				, address
				, telephone
				, fax
				, email
				, crdate
				, title
				, zip
				, city
				, country
				, www
				, company
				, image
				, module_sys_dmail_category
				, module_sys_dmail_html
				, tx_cbsttwccmembership_country
				, tx_cbsttwccmembership_zone
			FROM fe_users
			WHERE 1 = 1
				AND pid = {$this->storagePid}
				AND disable = 0
				AND deleted = 0
		";

		$result					= $this->db->sql_query( $select );
		$loadedUsers		= array();
		$loadedUserNames	= array();

		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			do
			{
				$loadedUsers[]				= $data;
				$uid						= $data[ 'uid' ];
				$loadedUserNames[ $uid ]	= stripslashes( $data[ 'name' ] );
			} while ( $data = $this->db->sql_fetch_assoc( $result ) );

			@$this->db->sql_free_result();
		}

		$this->users		= $loadedUsers;
		$this->userNames	= $loadedUserNames;
		// cbPrint2( 'users', $this->users );
		// cbPrint2( 'userNames', $this->userNames );
	}

	/**
	 * Setups internal US state list from static_country_zones.
	 *
	 * @return void
	 */
	function loadStateList ()
	{
		$states					= & $this->stateList;

		// load if not already loaded
		if ( is_null( $states ) )
		{
			$select				= "
				SELECT
					uid
					, zn_code
				FROM static_country_zones
				WHERE 1 = 1
					AND zn_country_iso_nr = 840
			";

			$result				= $this->db->sql_query( $select );

			if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
			{
				do
				{
					$states[ $data[ 'uid' ] ]	= $data[ 'zn_code' ];
				} while ( $data = $this->db->sql_fetch_assoc( $result ) );

				@$this->db->sql_free_result();
			}
		}
		// cbPrint2( 'stateList', $this->stateList );
	}

	/**
	 * Returns array with column data from parsed Excel object.
	 *
	 * @param array column keys
	 * @return array
	 */
	function & getExcelColumnsData( $columnKeys )
	{
		$array					= array();

		// worksheet with cell data coordinates
		$ws						= & $this->excel->worksheet[ 'data' ][ 0 ];

		// type, data, font container
		$cells					= & $ws[ 'cell' ];

		// type 0 data container
		$sst					= & $this->excel->sst;

		$max_row				= $ws[ 'max_row' ];
		$max_col				= $ws[ 'max_col' ];

		$columnKeysCount		= count( $columnKeys );

		for ( $i = 0; $i < $max_row; $i++ )
		{
			foreach ( $columnKeys as $key => $value )
			{
				$columnKey			= $this->{$value};

				// don't grab invalid column
				if ( $this->columnCount <= $columnKey 
					||  $max_col < $columnKey 
				)
				{
					return $array;
				}

				$cell				= & $cells[ $i ][ $columnKey ];
				$data				= $cell[ 'data' ];
				$type				= $cell[ 'type' ];
				// cbPrint2( 'cell ' . $i . ':' . $columnKey, $cell );

				switch ( $type )
				{
					// string
					case 0:
						$value		= $sst[ 'unicode' ][ $data ];

						if ( cbIsNullBlank( $value ) )
						{
							$value	= $sst[ 'data' ][ $data ];
						}
						break;

					// integer
					// number
					case 1:
						$value		= $data;
						break;

					// float
					// number
					case 2:
						$value		= $data;
						break;

					// date
					case 3:
						$value		= $data;
						break;                               

					default:                                 
						$value		= 'data type unknown';
						break;                               
				}

				$array[ $i ][ $key ]	= $value;
			}
		}

		return $array;
	}

	/**
	 * Takes the uid of fe_users and removed the paid usergroup
	 *
	 * @param array
	 * @return void
	 */
	function removePaidUsergroup( & $removed )
	{
		// select those fe_users who've been removed
		// get uid, usergroup
		// removed paid user group from fe_users.usergroup
		// update fe_users with modified usergroup
		$removedMod				= array();

		foreach ( $removed as $uid => $usergroup )
		{
			$removedMod[]		= array(
									'uid'			=> $uid
									, 'usergroup'	=> preg_replace( 
										"#,?\b{$this->groupPaid}\b#"
										, ''
										, $usergroup
									)
									, 'tstamp'		=> $this->time
//									, 'disable'		=> 1
								);
		}

		$removedModCount		= count( $removedMod );

		for ( $i = 0; $i < $removedModCount; $i++ )
		{
			if ( '' == $removedMod[ $i ][ 'usergroup' ] )
			{
				$removedMod[ $i ][ 'usergroup' ]	= $this->groupWebsiteUser;
			}

			$this->db->exec_UPDATEquery( 'fe_users'
				, 'uid = ' . $removedMod[ $i ][ 'uid' ]
				, $removedMod[ $i ] );
		}
	}

	/**
	 * Returns summary string of company management.
	 *
	 * load/check/update companies
	 *
	 * @return string
	 */
	function manageCompanies ()
	{
		$string					= '';

		$this->loadCompanies();
		$currentCompanies		= & $this->companies;

		if ( 0 == count( $currentCompanies ) )
		{
			$string				.= 'No pre-existing companies found.<br />';
		}
				
		// columns relating to company data in excel file
		$colKeys				= array(
									'name'			=> 'indivnameCol'
									, 'company'		=> 'nameCol'
									, 'email'		=> 'emailCol'
									, 'www'			=> 'websiteCol'
									, 'services1'	=> 'listing1Col'
									, 'services2'	=> 'listing2Col'
									, 'services3'	=> 'listing3Col'
								);

		$listings				= & $this->getExcelColumnsData( $colKeys );

		// cbPrint2( 'listings', $listings );
		// exit();

		$listingsCount			= count( $listings );
		$removed				= array();
		$reloaded				= array();
		$longWhere				= '';

		// diff listings and companies
		for ( $i = 0; $i < $listingsCount; $i++ )
		{
			$title				= mysql_escape_string( $listings[ $i ][
									'company' ] );
			$url				= mysql_escape_string( $listings[ $i ][ 'www' ]
									);
			$contact_email		= mysql_escape_string( $listings[ $i ][ 'email'
									] );
			$contact_name		= mysql_escape_string( $listings[ $i ][ 'name' ]
									);

			$longWhere			.= "
				OR ( title = '$title'
					AND url = '$url'
					AND contact_email = '$contact_email'
					AND contact_name = '$contact_name'
				)
			";
		}

		$select					= "
			SELECT
				uid
				, title
				, url
				, contact_email
				, contact_name
			FROM tx_t3consultancies
			WHERE 1 = 1
				AND hidden = 0
				AND deleted = 0
				AND ( 1 = 0
					$longWhere
				)
		";

		// this also happens to be the same list of folks that might be
		// presently hidden so can safely pass to hidecompanies false
		$result					= $this->db->sql_query( $select );

		$foundCompanies			= array();
		$foundCompanyUids		= array();

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$foundCompanies[]	= $data;
			$foundCompanyUids[]	= $data[ 'uid' ];
		}
			
		@$this->db->sql_free_result();
				
		// cbPrint2( 'select', $select );
		// cbPrint2( 'foundCompanies', $foundCompanies );
		// exit();

		$reloadedCount			= count( $foundCompanyUids );
		
		// unhide previous
		$string					.= $reloadedCount
									. ' companies viewable.<br />';

		// unhide found companies whether or not hidden previously
		$this->hideCompanies( $foundCompanyUids, false );

		for ( $i = 0; $i < $listingsCount; $i++ )
		{
			// remove already loaded and newly unhidden folks from being
			// imported again
			$foundKey			= false;

			$listing			= & $listings[ $i ];
			$title				= $listing[ 'company' ];
			$url				= $listing[ 'www' ];
			$contact_email		= $listing[ 'email' ];
			$contact_name		= $listing[ 'name' ];

			$foundCompaniesCnt	= count( $foundCompanies );

			for ( $j = 0; $j < $foundCompaniesCnt; $j++ )
			{
				$company		= & $foundCompanies[ $j ];

				if ( $title == $company[ 'title' ]
					&& $url == $company[ 'url' ]
					&& $contact_email == $company[ 'contact_email' ]
					&& $contact_name == $company[ 'contact_name' ]
				)
				{
					$foundKey	= true;
				
					// don't find this company again
					unset( $foundCompanies[ $j ] );

					// break out of this loop
					break;
				}
			}

			if ( $foundKey )
			{
				unset( $listings[ $i ] );

				// break out of this loop iteration, but not the loop iteself
				continue;
			}

			// match servicesN to catgories.uid and set servicesN
			for ( $j = 1; $j <= $this->listingCount; $j++ )
			{
				$serviceN		= 'services' . $j;

				$listingValue	= $listings[ $i ][ $serviceN ];
				$listingValue	= array_search( $listingValue
									, $this->categoryNames
								);	
				$listings[ $i ][ $serviceN ]	= $listingValue;
			}
		}

		$removedCount			= count( $removed );
		
		// update/insert missing
		$string					.= $removedCount
									. ' non-member companies hidden.<br />';

		// make non-uploaded companies hidden
		// select uids of all companies, diff against found company ids
		$select					= "
			SELECT uid
			FROM tx_t3consultancies
			WHERE 1 = 1
				AND hidden = 0
				AND deleted = 0
		";

		// create ignore list
		if ( count( $foundCompanyUids ) )
		{
			$select				.= ' AND uid NOT IN '
									. implode( ',', $foundCompanyUids );
		}

		// this also happens to be the same list of folks that might be
		// presently hidden so can safely pass to hidecompanies false
		$result					= $this->db->sql_query( $select );

		$toBeHiddenCompanyUids	= array();

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$toBeHiddenCompanyUids[]		= $data[ 'uid' ];
		}

		@$this->db->sql_free_result();

		if ( 0 < count( $toBeHiddenCompanyUids ) )
		{
			$this->hideCompanies( $toBeHiddenCompanyUids );
		}

		// listings may have changed while linking found companies
		$listingsCount			= count( $listings );
		$string					.= $listingsCount
									. ' companies added.<br />';

		// cbPrint2( 'listings', $listings );
		// exit();

		foreach ( $listings as $key => $value )
		{
			$cname				= $value[ 'name' ];
			$cemail				= $value[ 'email' ];
			$www				= $value[ 'www' ];

			// 0 for linked fe_owner_id as set later on
			$user_id			= 0;

			$insertArray		= array(
									'pid'			=> $this->storagePid
									, 'tstamp'		=> $this->time
									, 'crdate'		=> $this->time
									, 'title'		=> $value[ 'company' ]
									, 'url'			=> $www
									, 'weight'		=> 0
									, 'cntry'		=> 220
									, 'services'	=> 0
									, 'fe_owner_user'	=> $user_id
									, 'contact_email'	=> $cemail
									, 'contact_name'	=> $cname
								);

			if ( true )
			{
				$result			= $this->db->exec_INSERTquery(
									'tx_t3consultancies'
									, $insertArray
								);

				// services relationships
				// grab insert id
				$insertId		= $this->db->sql_insert_id();
				// cbPrint2( 'insertId', $insertId, true );

				// create relation with categories
				// tx_t3consultancies_services_mm
				// uid_local company uid
				// uid_foreign category uid servicesN value
				// sorting servicesN N

				for ( $i = 1; $i <= $this->listingCount; $i++ )
				{
					$serviceN		= 'services' . $i;
					$serviceValue	= $value[ $serviceN ];

					// insert tx_t3consultancies_services_mm
					$insertArray	= array(
										'uid_local'			=> $insertId
										, 'uid_foreign'		=> $serviceValue
										, 'sorting'			=> $i
									);
					$insertResult	= $this->db->exec_INSERTquery(
										'tx_t3consultancies_services_mm'
										, $insertArray
									);
				}

				// update tx_t3consultancies
				$updateArray	= array(
									'services'	=> $this->listingCount
								);

				$updateResult	= $this->db->exec_UPDATEquery(
									'tx_t3consultancies'
									, 'uid = ' . $insertId
									, $updateArray
								);
				// cbPrint2( 'insertArray', $insertArray );
				// cbPrint2( 'updateArray', $updateArray );
			}

			else
			{
				cbPrint2( 'insertArray', $insertArray );
			}
		}

		// match name, company, email, and www already in system to
		// users and get uid for fe_owner_user
		$select					= "
			SELECT
				c.uid
				, f.uid fe_owner_user
			FROM fe_users f
				, tx_t3consultancies c
			WHERE
				1 = 1
				AND f.name = c.contact_name
				AND f.company = c.title
				/* only real matching is company and persons name
				AND f.email = c.contact_email
				AND f.www = c.url
				/*
				AND f.pid = {$this->storagePid}
				AND c.pid = {$this->storagePid}
/*			
				AND c.tstamp = {$this->time} 
*/
				AND f.deleted = 0
				AND f.disable = 0
				AND c.hidden = 0
				AND c.deleted = 0
		";
		// cbPrint2( 'select', $select );

		$result					= $this->db->sql_query( $select );

		while ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			$uResult			= $this->db->exec_UPDATEquery(
									'tx_t3consultancies'
									, 'uid = ' . $data[ 'uid' ]
									, $data
								);
		}

		@$this->db->sql_free_result();
	
		$this->loadCompanies();

		if ( 0 == count( $this->companies ) )
		{
			$string				.= 'No new companies found.<br />';
		}

		// cbPrint( $string );
		// exit();
				
		return $string;
	}

	/**
	 * Load internal company array.
	 *
	 * @return void
	 */
	function loadCompanies()
	{
		// load full current companies
		$select					= "
			SELECT
				uid
				, pid
				, tstamp
				, crdate
				, cruser_id
				, deleted
				, hidden
				, starttime
				, endtime
				, fe_group
				, title
				, description
				, url
				, contact_email
				, contact_name
				, services
				, selected
				, weight
				, fe_owner_user
				, logo
				, cntry
			FROM tx_t3consultancies
			WHERE 1 = 1
				AND pid = {$this->storagePid}
				AND hidden = 0
				AND deleted = 0
		";

		$result					= $this->db->sql_query( $select );
		$loadedCompanies		= array();

		if ( $result && $data = $this->db->sql_fetch_assoc( $result ) )
		{
			do
			{
				$loadedCompanies[]			= $data;
			} while ( $data = $this->db->sql_fetch_assoc( $result ) );
		
			@$this->db->sql_free_result();
		}

		$this->companies		= $loadedCompanies;
		// cbPrint2( 'companies', $this->companies );
		// exit();
	}

	/**
	 * Takes the uid of companies in t3consultancies and mark hidden
	 *
	 * @param array
	 * @param boolean hide true, unhide false
	 * @return void
	 */
	function hideCompanies( & $removed, $hide = true )
	{
		// select those tx_t3consultancies who've been removed
		// update tx_t3consultancies set hidden = 1 where uid = uid
		$hidden					= ( $hide )
									? 1
									: 0;

		$removedList			= implode( ',', $removed );
		$update					= "
			UPDATE tx_t3consultancies
			SET hidden = $hidden
				, deleted = $hidden
				, tstamp = {$this->time}
			WHERE uid IN ( $removedList )
		";

		$result					= $this->db->sql_query( $update );
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cb_sttwcc_membership/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cb_sttwcc_membership/mod1/index.php"]);
}

// Make instance:
$SOBE = t3lib_div::makeInstance("tx_cbsttwccmembership_module1");
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

/*
delete FROM `tx_t3consultancies_cat` WHERE pid in (4);
delete FROM `tx_t3consultancies` WHERE pid in (4);
delete FROM `fe_users` WHERE pid in (4);
delete FROM `tx_t3consultancies_services_mm` WHERE 1;
*/

?>
