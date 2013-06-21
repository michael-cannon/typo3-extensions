<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'Consultancies' for the 't3consultancies' extension.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
/**
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_t3consultancies_pi1.php,v 1.1.1.1 2010/04/15 10:04:06 peimic.comprock Exp $
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_t3consultancies_pi1 extends tslib_pibase {
	var $prefixId = "tx_t3consultancies_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_t3consultancies_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "t3consultancies";	// The extension key.
	
		// Internal
	var $categories=array();
	var $countries=array();
	var $showRef=0;	// If set, then references for consultancies are shown.
	var $singleViewOn=0;	// If set, a single item is shown

	// limit selection of categories
	var $categoriesList			= '';

	// default template file
	var $templateFile			= 'EXT:t3consultancies/pi1/t3consultancies.html';

	// template marker contents
	var $markerArray			= array();
	var $db						= null;

	/**
	 * Main function
	 */
	function main($content,$conf)	{
		$this->pi_setPiVarDefaults();

		// Loading the LOCAL_LANG values
		$this->pi_loadLL();

		// current database object
		$this->db				= $GLOBALS[ 'TYPO3_DB' ];
		$this->db->debugOutput	= true;

		$this->showRef			= $conf["t3references_disabled"]
									? 0 
									: t3lib_extMgm::isLoaded("t3references");
		$this->sitesMade		= ! $conf["sites_made_disabled"];

		if (strstr($this->cObj->currentRecord,"tt_content"))
		{
			$conf["pidList"] = $this->cObj->data["pages"];
			$conf["recursive"] = $this->cObj->data["recursive"];
			$conf["selectedOnly"] = $this->cObj->data["tx_t3consultancies_selected_only"];

			// allow tt_content to define what to show here
			$conf[ 'CMD' ]		= $this->cObj->data[
									'tx_t3consultancies_command' ];

 			$this->conf[ 'categoryListingPage' ]	= ( $this->cObj->data[
										'tx_t3consultancies_categorylisting' ]
									)
									?  $this->cObj->data[
										'tx_t3consultancies_categorylisting' ]
 									: $this->conf[ 'categoryListingPage' ];

 			$this->conf[ 'alphabeticalListingPage' ]	= ( $this->cObj->data[
										'tx_t3consultancies_alphabeticallisting' ]
									)
									?  $this->cObj->data[
									'tx_t3consultancies_alphabeticallisting' ]
 									: $this->conf[ 'alphabeticalListingPage' ];

			// check for tt_content defined templateFile
			$conf[ 'templateFile' ]	= ( $this->cObj->data[
										'tx_t3consultancies_template' ]
									)
										? "uploads/tx_t3consultancies/"
											.  $this->cObj->data[
												'tx_t3consultancies_template' ]
										: $conf[ 'templateFile' ];

			// set templateFile to use for remainder
			$this->templateFile	= ( $conf[ 'templateFile' ] )
									? $conf[ 'templateFile' ]
									: $this->templateFile;

			$categoriesList		= $this->cObj->data[
									'tx_t3consultancies_categories' ];

			if ( $categoriesList )
			{
				$this->categoriesList	= ( 'featuredAd' != $conf[ 'CMD' ] )
											? " AND tx_t3consultancies_cat.uid IN ( $categoriesList )"
											: " AND tx_t3consultancies_services_mm.uid_foreign IN ( $categoriesList )";
			}

		}

		$conf					= $this->buildTextImageConf( $conf );
	
		$this->conf				= $conf;

		switch( $conf["CMD"] )
		{
			case "navigation":
				return $this->pi_wrapInBaseClass( $this->navigationView(
					$content
					, $conf
				) );
			break;

			// force if a single element should be displayed:
			case "singleView":
			case ( isset( $this->piVars[ 'showUid' ] ) ):
				$this->internal["currentTable"] = "tx_t3consultancies";
				$this->internal["currentRow"] = $this->pi_getRecord("tx_t3consultancies",$this->piVars["showUid"]);
				
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			return $content;
			break;

			case "featuredAd":
			case "alphabetical":
				return $this->pi_wrapInBaseClass( $this->multipleItemView(
					$content
					, $conf
				) );
			break;

			case "category":
				return $this->pi_wrapInBaseClass( $this->categoryView(
					$content
					, $conf
				) );
			break;

			case "listView":
			default:
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}
	
	/**
	 * Makes the list view of consultancies
	 */
	function listView($content,$conf)	{
		$lConf = $this->conf["listView."];	// Local settings for the listView function



		$this->pi_autoCacheEn=1;
		$this->pi_autoCacheFields = Array();
		$this->pi_autoCacheFields["pointer"] = array("range"=>array(0,10));
		
		if ($this->conf["selectCountryFirst"])	{
			$this->pi_autoCacheFields["lang"] = array("range"=>array(0,1000));
			
#			$this->pi_isOnlyFields = "mode,pointer,lang";
#			$this->pi_lowerThan=1000;
		}
		

		# So the preview button will always be shown...
		$this->pi_alwaysPrev=1;
		
		$this->editAdd=0;
		if ((string)$this->piVars["editAdd"])	{
			$this->editAdd=1;
			$GLOBALS["TSFE"]->set_no_cache();
		}

		if ($this->editAdd && !$GLOBALS["TSFE"]->loginUser)
		{
			return '<p>'.sprintf($this->pi_getLL("noUserLoggedInWarning"),'<a href="'.$this->pi_getPageLink($this->conf["loginPageId"]).'">','</a>').'</p>';
		}
		
		elseif ($this->editAdd)
		{
#				$GLOBALS["TSFE"]->set_no_cache();
			
			$fullTable="";
				
			$feAConf=$this->getFe_adminLibConf();
			$fullTable.= $this->cObj->cObjGetSingle($this->conf["fe_adminLib"],$feAConf);

			$fullTable.= '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Return_to_listing"),array("editAdd"=>"")).'</p>';
				// Returns the content from the plugin.
			return $fullTable;
		}
		
		else
		{
			if (!isset($this->piVars["pointer"]))
			{
				$this->piVars["pointer"]=0;
			}

				// Initializing the query parameters:
			$this->internal["results_at_a_time"]=t3lib_div::intInRange($lConf["results_at_a_time"],0,1000,30);		// Number of results to show in a listing.
			$this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,5);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
			$this->internal["searchFieldList"]="title,description,url,contact_email,contact_name";
	
			$this->addWhere="";

				// Only selected:
			$this->addWhere.= $this->conf["selectedOnly"]	? ' AND tx_t3consultancies.selected' : '';


				// Getting the list of countries used  - piVars["lang"]
			$this->loadCountries();
				// Getting the categories used - piVars["service"]
			$this->loadCategories();
#debug($this->countries);
#debug($this->categories);
			

				// Countries:
			$this->addWhere.= $this->piVars["lang"] ? ' AND tx_t3consultancies.cntry='.intval($this->piVars["lang"]) : '';
#debug($this->addWhere);

			if ($this->piVars["service"])	{
				$mm_cat=array();
				$mm_cat["mmtable"]="tx_t3consultancies_services_mm";
				$mm_cat["table"]="tx_t3consultancies_cat";
				$mm_cat["catUidList"]=intval($this->piVars["service"]);
			} else $mm_cat="";


				// Get number of records:
			$query = $this->pi_list_query('tx_t3consultancies',1,$this->addWhere,$mm_cat);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			list($this->internal["res_count"]) = mysql_fetch_row($res);
	
				// Make listing query, pass query to MySQL:
			$query = $this->pi_list_query("tx_t3consultancies",0,$this->addWhere,$mm_cat,""," ORDER BY tx_t3consultancies.weight DESC, tx_t3consultancies.title");
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			$this->internal["currentTable"] = "tx_t3consultancies";
	
				// Put the whole list together:
			$fullTable="";	// Clear var;


			if (!$this->conf["selectCountryFirst"] || $this->piVars["lang"])	{
				$fullTable.=$this->makeSelectors();

				if ($this->internal["res_count"] > $this->internal["results_at_a_time"])	{
						// Adds the result browser:
					$fullTable.=$this->pi_list_browseresults();
				}
				
					// Adds the whole list table
				$fullTable.=$this->pi_list_makelist($res);
				
					// Adds the search box:
				$fullTable.=$this->pi_list_searchBox();
			} else {
				$fullTable.=$this->makeSelectors(1);
			}
				
			$fullTable.=$this->makeEditAddButton();
			
				// Returns the content from the plugin.
			return $fullTable;
		}
	}
	
	/**
	 * Make selector boxes in the top of page
	 */
	function makeSelectors($noServiceBox=0)	{
		$fullTable.=$this->makeCountrySelect($noServiceBox);
		if (!$noServiceBox)	$fullTable.=$this->makeServiceSelect();
		return '<DIV'.$this->pi_classParam("modeSelector").'>'.
			$fullTable.
			'</DIV>';
	}
	
	/**
	 * Make the selectorbox with countries.
	 */
	function makeCountrySelect($altLabel=0)	{
		$opt=array();
		reset($this->countries);

		$this->pi_linkTP_keepPIvars($v,array("lang"=>"","pointer"=>""));
		$opt[]='<option value="'.htmlentities($this->cObj->lastTypoLinkUrl).'">'.$this->pi_getLL(!$altLabel?'All_countries':'Sel_countries').'</option>';
		while(list($k,$v)=each($this->countries))	{
			$this->pi_linkTP_keepPIvars($v,array("lang"=>$k,"pointer"=>""));
			$opt[]='<option value="'.htmlentities($this->cObj->lastTypoLinkUrl).'"'.($this->piVars["lang"]==$k?" SELECTED":"").'>'.htmlentities($v).'</option>';
		}

		return '<select onChange="document.location=this.options[this.selectedIndex].value;">'.implode("",$opt).'</select>';
	}
	
	/**
	 * Make the selectorbox with categories/services.
	 */
	function makeServiceSelect()	{
		$opt=array();
		reset($this->categories);

		$this->pi_linkTP_keepPIvars($v,array("service"=>"","pointer"=>""));
		$opt[]='<option value="'.htmlentities($this->cObj->lastTypoLinkUrl).'">'.$this->pi_getLL('All_services').'</option>';

		while(list($k,$v)=each($this->categories))	{
			$url				= $this->categoryUrl( $k );
			$opt[]='<option value="'.htmlentities($url).'"'.($this->piVars["service"]==$k?" SELECTED":"").'>'.htmlentities($v).'</option>';
		}

#		return '<select onChange="document.location=unescape(\''.rawurlencode($this->cObj->lastTypoLinkUrl).'\')+\'&tx_t3consultancies_pi1[service]=\'+this.options[this.selectedIndex].value;">'.implode("",$opt).'</select>';
		return '<select onChange="document.location=this.options[this.selectedIndex].value;">'.implode("",$opt).'</select>';
	}
	
	/**
	 * Makes a link to/from edit mode
	 */
	function makeEditAddButton()	{
		if ($this->conf["editAdd_enabled"])	{
			if (!$this->editAdd)	{
				$this->pi_moreParams="&cmd=edit";
				return '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Edit_or_add_entries"),array("editAdd"=>1),1,1).'</p>';
			} else {
				return '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Cancel_edit_mode"),array("editAdd"=>""),1,1).'</p>';
			}
		}
	}
	
	/**
	 * Loading the countries used in the list into an internal array, $this->countries
	 */
	function loadCountries()	{
		$pidList = $this->pi_getPidList($this->conf["pidList"],$this->conf["recursive"]);
		$query = "SELECT static_countries.cn_short_en,static_countries.uid 
			FROM static_countries,tx_t3consultancies 
			WHERE tx_t3consultancies.cntry=static_countries.uid AND tx_t3consultancies.pid IN (".$pidList.")".
			$this->cObj->enableFields("tx_t3consultancies").
			$this->addWhere.
			" GROUP BY tx_t3consultancies.cntry ORDER BY static_countries.cn_short_en";
		$res = mysql(TYPO3_db,$query);
		$this->countries=array();

		while($row=mysql_fetch_assoc($res))	{
			$this->countries[$row["uid"]]=$row["cn_short_en"];
		}
	}

	/**
	 * Loading the categories into an internal array, $this->categories
	 */
	function loadCategories()
	{
		$query					= $this->pi_categoriesUsed(
									"tx_t3consultancies_cat"
									, "tx_t3consultancies_services_mm"
									, "tx_t3consultancies"
									, $this->addWhere
										. $this->categoriesList
								);

		$res					= mysql(TYPO3_db,$query);
		
		$this->categories		= array();

		while( $row=mysql_fetch_assoc($res) )
		{
			$this->categories[$row["uid"]]=$row["title"];
		}
	}

	function pi_categoriesUsed($cat_table,$mm_table,$table,$addWhere="")	{
			// Fetches the list of PIDs to select from. 
			// TypoScript property .pidList is a comma list of pids. If blank, current page id is used.
			// TypoScript property .recursive is a int+ which determines how many levels down from the pids in the pid-list subpages should be included in the select.
		$pidList = $this->pi_getPidList($this->conf["pidList"],$this->conf["recursive"]);
		
			// Begin Query:
		$query="FROM ".$table.",".$cat_table.",".$mm_table.chr(10).
				" WHERE ".$table.".uid=".$mm_table.".uid_local AND ".$cat_table.".uid=".$mm_table.".uid_foreign ".chr(10).
				" AND ".$table.".pid IN (".$pidList.")".chr(10).
				$this->cObj->enableFields($cat_table).
				$this->cObj->enableFields($table).chr(10);
		if ($addWhere)	{$query.=" ".$addWhere.chr(10);}
		$query.=" GROUP BY ".$cat_table.".uid";

		$query = "SELECT ".$cat_table.".title,".$cat_table.".uid ".chr(10).$query;
		return $query;
	}
	
	/**
	 * Renders a single entry
	 */
	function singleView( $content, $conf )
	{
		if ( ! $this->internal["currentRow"]["uid"] )
		{
			return $this->pi_getLL( 'no_record' );
		}
		
		if ( $this->conf["selectCountryFirst"] && ! $this->piVars["lang"] )
		{
			$GLOBALS["TSFE"]->set_no_cache();
		}
		
		$this->singleViewOn		= 1;
	
		// This sets the title of the page for use in indexed search results:
		if ($this->internal["currentRow"]["title"])
		{
			$GLOBALS["TSFE"]->indexedDocTitle =
								$this->internal["currentRow"]["title"];
		}
		
		$templatePart			= '###TEMPLATE_SINGLE###';

		$string					= $this->parseTemplate( $templatePart );

		$content				= $this->cObj->substituteMarkerArrayCached(
									$string
									, $this->markerArray
									// , array()
								);
	
		return $content;
	}	

	/**
	 * Renders multiple ad entries by category selection
	 *
	 * @param string incoming text
	 * @param array configuration ( not used )
	 * @return string
	 */
	function categoryView( $content, $conf )
	{
		switch ( $this->conf[ 'CMD' ] )
		{
			case 'category':
			default:
				$templatePart	= '###TEMPLATE_CATEGORY###';
				break;
		}

		$string					= '';

		// Local settings for the listView function
		$lConf					= $this->conf["listView."];

		// look in database for featured records
		$selectedOnly			= ( $this->conf[ 'selectedOnly' ] )
									? ' AND tx_t3consultancies.selected'
									: '';

		// grab inputted service
		$serviceSelection		= ( $this->piVars[ 'service' ] )
									? " AND tx_t3consultancies_services_mm.uid_foreign IN (
											{$this->piVars[ 'service' ]}
										)"
									: '';

		// build up query
		$query					= "
			SELECT
				tx_t3consultancies.*
			FROM
				tx_t3consultancies
				LEFT JOIN tx_t3consultancies_services_mm
					ON tx_t3consultancies.uid =
						tx_t3consultancies_services_mm.uid_local
			WHERE
				1 = 1
				$selectedOnly
				/* {$this->categoriesList} */
				$serviceSelection
		";
		$query					.= $this->cObj->enableFields(
									'tx_t3consultancies' );

		// this is okay while advertiser list is small, less a few hundred
		$query					.= "
			GROUP BY tx_t3consultancies.uid ASC	
			ORDER BY tx_t3consultancies.weight DESC	
				, tx_t3consultancies.title ASC	
		";
		
		$result					= $this->db->sql( TYPO3_db, $query );

		// if results, grab template
		if ( $result && $ad = $this->db->sql_fetch_assoc( $result ) )
		{
			// cycle through template for each record
			do
			{
				$string			.= $this->parseTemplate(
									$templatePart
									, true
									, $ad
								);
			} while ( $ad = $this->db->sql_fetch_assoc( $result ) );
		}

		else
		{
			$string				.= $this->pi_getLL( 'no_record' );
		}

		// build up category selection query
		$query					= "
			SELECT
				tx_t3consultancies_cat.*
			FROM
				tx_t3consultancies_cat
				LEFT JOIN tx_t3consultancies_services_mm
					ON tx_t3consultancies_cat.uid =
						tx_t3consultancies_services_mm.uid_foreign
			WHERE
				1 = 1
				/* {$this->categoriesList} */
				$serviceSelection
		";
		$query					.= $this->cObj->enableFields(
									'tx_t3consultancies_cat' );

		// this is okay while advertiser list is small, less a few hundred
		$query					.= "
			ORDER BY tx_t3consultancies_cat.title ASC	
		";
		
		$result					= $this->db->sql( TYPO3_db, $query );

		$contentMarkerArray		= array();
		$contentMarkerArray[ '###CONTENT###' ]		= $string;

		// if results, grab template
		if ( $result && $category = $this->db->sql_fetch_assoc( $result ) )
		{
			$contentMarkerArray[ '###TITLE###' ]		= $category[ 'title' ];
			$contentMarkerArray[ '###TITLE_IMAGE###' ]	= $this->textImage(
									$category[ 'title' ]
								);

			if ( $category[ 'image' ] )
			{
				$image			= $this->cObj->imageLinkWrap( $this->getImage(
										$category[ 'image' ]
										, $this->conf[ 'featuredLogo.' ]
									)
									, "uploads/tx_t3consultancies/"
										. $category[ 'image' ]
									, $this->conf[ 'enlargeImage.' ]
								);
			}

			else
			{
				$image			= '';
			}

			$contentMarkerArray[ '###IMAGE###' ]		= $image;
			$contentMarkerArray[ '###DESCRIPTION###' ]	= $category[
									'description' ];
		}

		else
		{
			$contentMarkerArray[ '###TITLE###' ]		= '';
			$contentMarkerArray[ '###IMAGE###' ]		= '';
			$contentMarkerArray[ '###DESCRIPTION###' ]	= '';
		}

		$subpart				= $this->templateSubpart ( $templatePart );

		$content				= $this->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, $contentMarkerArray
								);

		// Returns the content from the plugin.
		return $content;
	}

	/**
	 * Renders multiple ad entries by selection
	 *
	 * @param string incoming text
	 * @param array configuration ( not used )
	 * @return string
	 */
	function multipleItemView( $content, $conf )
	{
		switch ( $this->conf[ 'CMD' ] )
		{
			case 'featuredAd':
				$templatePart	= '###TEMPLATE_FEATURED_AD###';
				break;

			case 'alphabetical':
				$templatePart	= '###TEMPLATE_ALPHABETICAL###';
				break;

			case 'listView':
				$templatePart	= '###TEMPLATE_CATEGORY###';
				break;
		}

		$string					= '';

		// Local settings for the listView function
		$lConf					= $this->conf["listView."];

		// look in database for featured records
		$selectedOnly			= ( 'featuredAd' == $this->conf[ 'CMD' ]
									|| $this->conf[ 'selectedOnly' ]
								)
									? ' AND tx_t3consultancies.selected'
									: '';

		// build up query
		$query					= "
			SELECT
				tx_t3consultancies.*
			FROM
				tx_t3consultancies
				LEFT JOIN tx_t3consultancies_services_mm
					ON tx_t3consultancies.uid =
						tx_t3consultancies_services_mm.uid_local
			WHERE
				1 = 1
				$selectedOnly
				{$this->categoriesList}
		";
		$query					.= $this->cObj->enableFields(
									'tx_t3consultancies' );

		// this is okay while advertiser list is small, less a few hundred
		$query					.= "
			GROUP BY tx_t3consultancies.uid ASC	
			ORDER BY tx_t3consultancies.weight DESC	
				, tx_t3consultancies.title ASC	
		";
		
		$result					= $this->db->sql( TYPO3_db, $query );

		// if results, grab template
		if ( $result && $ad = $this->db->sql_fetch_assoc( $result ) )
		{
			// cycle through template for each record
			do
			{
				$string			.= $this->parseTemplate(
									$templatePart
									, true
									, $ad
								);
			} while ( $ad = $this->db->sql_fetch_assoc( $result ) );
		}

		$contentMarkerArray		= array();
		$contentMarkerArray[ '###CONTENT###' ] = $string;

		$subpart				= $this->templateSubpart ( $templatePart );

		$content				= $this->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, $contentMarkerArray
								);

		// Returns the content from the plugin.
		return $content;
	}

	/**
	 * Renders navigation entries
	 *
	 * @param string incoming text
	 * @param array configuration ( not used )
	 * @return string
	 */
	function navigationView( $content, $conf )
	{
		$templatePart			= '###TEMPLATE_MENU###';
		// $templatePart			= '###TEMPLATE_MENU_ALPHA###';

		$string					= '';

		// Local settings for the listView function
		$lConf					= $this->conf["listView."];

		// build up query
		$query					= "
			SELECT
				tx_t3consultancies_cat.*
			FROM
				tx_t3consultancies_cat
			WHERE
				1 = 1
				{$this->categoriesList}
		";
		$query					.= $this->cObj->enableFields(
									'tx_t3consultancies_cat' );

		// this is okay while advertiser list is small, less a few hundred
		$query					.= "
			ORDER BY tx_t3consultancies_cat.title ASC	
		";
		
		$result					= $this->db->sql( TYPO3_db, $query );

		// if results, grab template
		if ( $result && $category = $this->db->sql_fetch_assoc( $result ) )
		{
			// cycle through template for each record
			do
			{
				$string			.= $this->parseTemplate(
									$templatePart
									, true
									, $category
								);
			} while ( $category = $this->db->sql_fetch_assoc( $result ) );
		}

		$contentMarkerArray		= array();

		$contentMarkerArray[ '###CONTENT###' ] =
								$string;

		$contentMarkerArray[ '###TITLE_ALPHABETICAL###' ] =
								$this->pi_linkToPage(
									$this->pi_getLL( 'alphabetical_title' )
									, $this->alphabeticalUrl()
								);

		$subpart				= $this->templateSubpart ( $templatePart );

		$content				= $this->cObj->substituteMarkerArrayCached(
									$subpart
									, $this->markerArray
									, $contentMarkerArray
								);

		// Returns the content from the plugin.
		return $content;
	}

	/**
	 * Return string containing tempalte subpart.
	 *
	 * @param string template part
	 * @return string
	 */
	function templateSubpart ( $templatePart )
	{
		static $template;

		// try not to grab the template more than once if necessary
		// TODO ensure this doesn't mess up for multiple same page instances
		if ( ! $template )
		{
			$template			= $this->cObj->fileResource(
									$this->templateFile
								);
		}

		// grab subpart
		$subpart				= $this->cObj->getSubpart(
									$template
									, $templatePart
								);

		return $subpart;
	}

	/**
	 * Return string containing string parsed for given template.
	 *
	 * @param string template part name
	 * @param boolean true - content repeated
	 * @param array data
	 * @return string
	 */
	function parseTemplate( $templatePart, $repeating = false, $data = false )
	{
		$subpartEach			= $this->templateSubpart ( $templatePart );

		if ( $repeating )
		{
			$subpartEach		= $this->cObj->getSubpart(
									$subpartEach
									, '###EACH###'
								);
		}

		// set internal current row to our input data
		if ( ! $data )
		{
			$data				= $this->internal[ 'currentRow' ];
		}

		else
		{
			$this->internal[ 'currentRow' ]	= $data;
		}

		// create link to detail page
		$this->markerArray[ '###DETAILS###' ]	= $this->pi_linkToPage(
									 $this->pi_getLL( 'Details' )
									 , $this->detailsUrl()
								);

		$this->markerArray[ '###BACK###' ]	= $this->pi_linkToPage(
									 $this->pi_getLL( 'Back' )
									 , $_SERVER[ 'HTTP_REFERER' ]
								);

		// create title as image 
		if ( isset( $data[ 'title' ] ) )
		{
			$this->markerArray[ '###TITLE_IMAGE###' ]	= $this->textImage(
									$data[ 'title' ]
								);
		}

		foreach( $data as $key => $value )
		{
			$upperKey			= strtoupper( $key ) . '###';
			$dataKey			= '###' . $upperKey;
			$labelKey			= '###LABEL_' . $upperKey;

			$this->markerArray[ $dataKey ]	= $this->getFieldContent(
									$key
								);
			$this->markerArray[ $labelKey ]	= $this->pi_getLL(
									'listFieldHeader_' . $key
								);
		}

		$string					.= $this->cObj->substituteMarkerArrayCached(
									$subpartEach
									, $this->markerArray
								);

		return $string;
	}

	/**
	 * Return string of alphabetical page URL
	 *
	 * @return string
	 */
	function alphabeticalUrl()
	{
		$pid					= ( $this->conf[ 'alphabeticalListingPage' ] )
									? $this->conf[ 'alphabeticalListingPage' ] 
									: $this->cObj->data[ 'pid' ];

 		$linkArray				= array(
 									'parameter'			=> $pid
 									, 'returnLast'		=> 'url'
 									, 'no_cache'		=> 0
 									, 'useCacheHash'	=> 0
 									, 'additionalParams'	=> ''
 								);
 		$url					= '/'
 									. $this->cObj->typolink(
										''
 										, $linkArray
 									);

		return $url;
	}

	/**
	 * Return string of category page URL
	 *
	 * @param integer category uid
	 * @return string
	 */
	function categoryUrl( $uid )
	{
		$pid					= ( $this->conf[ 'categoryListingPage' ] )
									? $this->conf[ 'categoryListingPage' ] 
									: $this->cObj->data[ 'pid' ];

 		$linkArray				= array(
 									'parameter'			=> $pid
 									, 'returnLast'		=> 'url'
 									, 'no_cache'		=> 0
 									, 'useCacheHash'	=> 0
 									, 'additionalParams'	=>
									"&tx_t3consultancies_pi1[service]={$uid}"
 								);
 		$url					= '/'
 									. $this->cObj->typolink(
										''
 										, $linkArray
 									);

		return $url;
	}

	/**
	 * Return string of detail URL
	 *
	 * @return string
	 */
	function detailsUrl()
	{
 		$linkArray				= array(
 									'parameter'			=> $this->conf[
															'detailPid' ]
 									, 'returnLast'		=> 'url'
 									, 'no_cache'		=> 0
 									, 'useCacheHash'	=> 0
 									, 'additionalParams'	=> "&tx_t3consultancies_pi1[showUid]={$this->internal['currentRow']['uid']}"
 								);
 		$url					= '/'
 									. $this->cObj->typolink(
										''
 										, $linkArray
 									);

		return $url;
	}

	/**
	 * Selects and renders the referencelist for the consultancy.
	 */
	function getRefListForRecord()	{
		if ($this->showRef)	{
			$value="";
			$pLR = $this->pidListForReferences();
			if ($pLR)	{
				$pLR="pid IN (".$pLR.") AND";
			} else $pLR="";
			$query = "SELECT * FROM tx_t3references WHERE ".$pLR." dev_rel=".intval($this->internal["currentRow"]["uid"]).
				($this->conf["selectedOnly"]	? ' AND tx_t3references.selected' : '').
				$this->cObj->enableFields("tx_t3references").
				" ORDER BY tx_t3references.weight DESC, tx_t3references.launchdate DESC, tx_t3references.title";

			$res = mysql(TYPO3_db,$query);
#echo mysql_error();
			
			$marginBetweenRefs = t3lib_div::intInRange($this->conf["singleView."]["showRefList."]["marginBetweenRefs"],1,100,10);
			$marginToImg = t3lib_div::intInRange($this->conf["singleView."]["showRefList."]["marginToImg"],1,100,10);
			$refPage = intval($this->conf["singleView."]["showRefList."]["refPage"]);
			
			$items=array();
			while ($row=mysql_fetch_assoc($res))	{
				list($srcDump) = t3lib_div::trimExplode(",",$row["screendump"],1);
				$img = $this->getReferencesImage($srcDump,$this->conf["singleView."]["showRefList."]["screenDump."]);
				$descr = t3lib_div::fixed_lgd(strip_tags($row["description"]),200).
							" ".$this->linkSingleRef($this->pi_getLL("more"),$row["uid"],$refPage);

				$title = $row["title"];

				$url	= 'URL: ';
				$url	.= $this->pi_linkToPage(
							$this->internal["currentRow"][ 'url' ]
							, ( $this->internal["currentRow"][ 'real_url' ] )
								? $this->internal["currentRow"][ 'real_url' ]
								: $this->internal["currentRow"][ 'url' ]
						);
				
				$items[]='<tr>
					<td width="95%" valign="top">
						<h3>'.$title.'</h3>
						<p>'.$descr.'</p>
						<p'.$this->pi_classParam("reflist-url").'>'.$url.'</p>
					</td>
					<td><img src="clear.gif" width='.$marginToImg.' height=1></td>
					<td valign="top">'.$img.'</td>
				</tr>
				<tr>
					<td colspan=3><img src="clear.gif" width=1 height='.$marginBetweenRefs.'></td>
				</tr>';
			}
			if (count($items))	{
				$retVal = '<table '.$this->conf["singleView."]["showRefList."]["tableParams"].'>'.implode("",$items).'</table>';
			} else {
				$retVal = '<p>Currently no references is available for this consultancy.</p>';
			}
			$retVal = '<h2>References:</h2>'.$retVal;
			$retVal = '<div'.$this->pi_classParam("reflist").'>'.$retVal.'</div>';
			return $retVal;
		} else {
			return '<font color="red">Sorry, the references could not be listed, because the reference plugin is not enabled</font>';
		}
	}

	/**
	 * Wraps the $str in a link to a single display of the record.
	 */	
	function linkSingleRef($str,$uid,$refPage)	{
		if ($refPage)	{
			$this->pi_tmpPageId=$refPage;
			
			$str = $this->pi_linkTP($str,array("tx_t3references_pi1[showUid]" => $uid),1);
		}
		$this->pi_tmpPageId=0;
		return $str;
	}
	
	/**
	 * Returns the list of items.
	 */	
	function pi_list_makelist($res)	{
			// Make list table header:
		$tRows=array();
		$this->internal["currentRow"]="";
		$tRows[]=$this->pi_list_header();

			// Make list table rows
		$c=0;
		while($this->internal["currentRow"] = mysql_fetch_assoc($res))	{
			$tRows[]=$this->pi_list_row_2($c);
			$c++;
		}

		$out = '<DIV'.$this->pi_classParam("listrow").'><table>'.implode("",$tRows).'</table></DIV>';
		return $out;
	}

	/**
	 * Displays the consultancy list:
	 */
	function pi_list_row_2($c)	{
		$editPanel = $this->pi_getEditPanel();
		if ($editPanel)	$editPanel="<TD>".$editPanel."</TD>";
		return '<tr'.($c%2 ? $this->pi_classParam("listrow-odd") : "").'>
				<td valign="top"><P>'.$this->getFieldContent("title").'</P></td>
				<td valign="top"><P>'.$this->getFieldContent("cntry").'</P></td>
				<td valign="top"><P>'.$this->getFieldContent("services").'</P></td>
				'.($this->showRef && $this->sitesMade?'<td valign="top" align="center"><P>'.$this->getFieldContent("_ref").'</P></td>':'').'
				<td valign="top"><P>'.$this->getFieldContent("details").'</P></td>
				'.$editPanel.'
			</tr>';
	}

	/**
	 * Displays consultancy list header
	 */
	function pi_list_header()	{
		return '<tr'.$this->pi_classParam("listrow-header").'>
				<td nowrap><P>'.$this->getFieldHeader("title").'</P></td>
				<td nowrap><P>'.$this->getFieldHeader("cntry").'</P></td>
				<td nowrap><P>'.$this->getFieldHeader("services").'</P></td>
				'.($this->showRef && $this->sitesMade?'<td><P>'.$this->getFieldHeader("references").'</P></td>':'').'
				<td><P>'.$this->getFieldHeader("details").'</P></td>
			</tr>';
	}
	
	/**
	 * Returns processed content for a given fieldname ($fN) from the current row
	 * 
	 * @param array optional data
	 * @return string
	 */
	function getFieldContent($fN, $data = false )
	{
		switch($fN) {
			case "details":
				return $this->pi_list_linkSingle($this->pi_getLL("Details"),$this->internal["currentRow"]["uid"],1,array("lang"=>$this->conf["selectCountryFirst"]?$this->piVars["lang"]:"", "pointer"=>$this->piVars["pointer"]));
			break;

			case "description":
				$content = implode("<br/>",t3lib_div::trimExplode(chr(10),strip_tags(t3lib_div::fixed_lgd($this->internal["currentRow"]["description"],t3lib_div::intInRange($this->conf["truncate_limit"],1,100000,1000),$this->pi_getLL("trunc")),"<b><i><u><strong><em><a>"),1));
				return $content;
			break;

			case "title":
				$title = $this->internal["currentRow"]["title"];

				if ( 'navigation' == $this->conf[ 'CMD' ] )
				{
					return $this->pi_linkToPage(
						$title
						, $this->categoryUrl(
							$this->internal[ 'currentRow' ][ 'uid' ]
						)
					);
				}

				elseif ( $this->singleViewOn
					&& ( $this->internal["currentRow"]["real_url"]
						|| $this->internal["currentRow"]["url"]
					)
					&& $this->conf["linkTitle"]
				)
				{
					// show real_url if it exists
					return $this->pi_linkToPage(
						// todo image version of title
						$title
						, ( $this->internal["currentRow"][ 'real_url' ] )
							? $this->internal["currentRow"][ 'real_url' ]
							: $this->internal["currentRow"][ 'url' ]
					);
				}

				elseif ( $this->singleViewOn && ! $this->conf["linkTitle"] )
				{
					return $title;
				}

				else
				{
					return $this->pi_linkToPage(
						$title
						, $this->detailsUrl()
					);
				}
			break;

			case "cntry":
				if (isset($this->countries[$this->internal["currentRow"][$fN]]))	{
					return $this->countries[$this->internal["currentRow"][$fN]];
				} else {
					$cntryRec = $this->pi_getRecord("static_countries",$this->internal["currentRow"][$fN]);
					return $cntryRec["cn_short_en"];
				}
			break;

			case "services":
				$query = "SELECT uid_foreign FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($this->internal["currentRow"]["uid"])." ORDER BY sorting";
				$res = mysql(TYPO3_db,$query);
				$services=array();
				while($row=mysql_fetch_assoc($res))	{
					if (isset($this->categories[$row["uid_foreign"]]))	{
						$services[]=$this->categories[$row["uid_foreign"]];
					} else {
						$catRec = $this->pi_getRecord("tx_t3consultancies_cat",$row["uid_foreign"]);
						$services[]=$catRec["title"];
					}
				}
				return implode($this->singleViewOn?', ':'</BR>',$services);
			break;

			case "_ref":
				if ($this->showRef)	{
					$value="";
					$pLR = $this->pidListForReferences();
					if ($pLR)	{
						$pLR="pid IN (".$pLR.") AND";
					} else $pLR="";
					$query = "SELECT count(*) FROM tx_t3references WHERE ".$pLR." dev_rel=".intval($this->internal["currentRow"]["uid"]).
						($this->conf["selectedOnly"]	? ' AND tx_t3references.selected' : '').
						$this->cObj->enableFields("tx_t3references");
					$res = mysql(TYPO3_db,$query);
					if ($row=mysql_fetch_assoc($res))	{
						$value=$row["count(*)"]?$row["count(*)"]:"-";
					}
					return $value;
				}
			break;

			case "contact_email":
				if ($this->internal["currentRow"][$fN])
				{
					return $this->pi_linkToPage(
						$this->internal["currentRow"][$fN]
						, $this->internal["currentRow"][$fN]
					);
				}
			break;

			case "fe_owner_user":
				$fe_user = $this->pi_getRecord("fe_users",$this->internal["currentRow"][$fN]);
				if (is_array($fe_user))	{
					return '<strong>'.$fe_user["username"].'</strong>';
				} else {
					return '<em>'.$this->pi_getLL("NA").'</em>';
				}
			break;

			case 'url':
				if ($this->internal['currentRow']['url'])
				{
					// show real_url if it exists
					return $this->pi_linkToPage(
						$this->internal["currentRow"][$fN]
						, ( $this->internal["currentRow"][ 'real_url' ] )
							? $this->internal["currentRow"][ 'real_url' ]
							: $this->internal["currentRow"][$fN]
					);
				}

				break;


			case 'map_url':
				if ( $this->internal['currentRow'][ $fN ] )
				{
					$name		= $this->pi_getLL( 'map_url' );

					$value		= '<a href="'
									. $this->internal['currentRow'][$fN]
									. '" target="_blank">'
									. $name
									. '</a>';

					return $value;
				}

				break;

			case 'logo':
				if ( $this->internal[ 'currentRow' ][ $fN ] )
				{
					return $this->cObj->imageLinkWrap( $this->getImage(
							$this->internal[ 'currentRow' ][ $fN ]
							, $this->conf[ 'logoImage.' ]
						)
						, "uploads/tx_t3consultancies/"
							. $this->internal[ 'currentRow' ][ $fN ]
						, $this->conf[ 'logoImage.' ]
					);
				}
			break;

			case 'featured_logo':
				if ( $this->internal[ 'currentRow' ][ $fN ] )
				{
					return $this->cObj->imageLinkWrap( $this->getImage(
							$this->internal[ 'currentRow' ][ $fN ]
							, $this->conf[ 'logo.' ]
						)
						, "uploads/tx_t3consultancies/"
							. $this->internal[ 'currentRow' ][ $fN ]
						, $this->conf[ 'enlargeImage.' ]
					);
				}
			break;

			case 'coupon':
				if ( $this->internal[ 'currentRow' ][ $fN ] )
				{
					return $this->cObj->imageLinkWrap( $this->getImage(
							$this->internal[ 'currentRow' ][ $fN ]
							, $this->conf[ 'coupon.' ]
						)
						, "uploads/tx_t3consultancies/"
							. $this->internal[ 'currentRow' ][ $fN ]
						, $this->conf[ 'coupon.' ]
					);
				}
			break;

			case 'address':
				return nl2br( $this->internal[ 'currentRow' ][ $fN ] );
				break;

			case 'city':
			default:
				return ( $this->internal[ 'currentRow' ][ $fN ] )
						? $this->internal[ 'currentRow' ][ $fN ] . ','
						: '';
			break;

			case '':
			default:
				return $this->internal[ 'currentRow' ][ $fN ];
			break;
		}

		return '';
	}
	
	/**
	 * Returns a list of integer-pids for selecting the references belonging to consultancies
	 */
	function pidListForReferences()	{
		$v= implode(",",t3lib_div::intExplode(",",$this->conf["pidList_references"]));
		return $v;
	}

	/**
	 * Returns the header text of a field (from locallang)
	 */
	function getFieldHeader($fN)	{
		switch($fN) {
			default:
				return $this->pi_getLL("listFieldHeader_".$fN,"[".$fN."]");
			break;
		}
	}
	
	/**
	 * Returns an image given by $TSconf
	 */
    function getImage($filename,$TSconf)    {
        list($theImage)=explode(",",$filename);
        $TSconf["file"] = "uploads/tx_t3consultancies/".$theImage;

        $img = $this->cObj->IMAGE($TSconf);
        return $img;
    }

	/**
	 * Returns an image given by $TSconf for references
	 */
    function getReferencesImage($filename,$TSconf)    {
        list($theImage)=explode(",",$filename);
        $TSconf["file"] = "uploads/tx_t3references/".$theImage;

        $img = $this->cObj->IMAGE($TSconf);
        return $img;
    }
	
	/**
	 * Makes the editing form for submitting information by frontend users.
	 */
	function getFe_adminLibConf()	{
		$feAConf = $this->conf["fe_adminLib."];
		$feAConf["templateContent"]='

<!-- ###TEMPLATE_EDIT### -->
<h3>Edit "###FIELD_title###"</h3>
<table border=0 cellpadding=1 cellspacing=2>
<FORM name="tx_t3consultancies_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
'.$this->makeFormFromConfig($feAConf["edit."],$feAConf["table"]).'
<tr>
	<td></td>
	<td></td>
	<td>
		###HIDDENFIELDS###
		<input type="Submit" name="submit" value="'.$this->pi_getLL("feAL_save").'">
	</td>
</tr>
</FORM>
</table>
<p>&nbsp;</p>
<p style="color: red;"><a href="###FORM_URL###&cmd=delete&backURL=###FORM_URL_ENC###&rU=###REC_UID###" onClick="return confirm(\'Are you sure?\');">'.$this->pi_getLL("feAL_delete").'</a></p>
<p>&nbsp;</p>
<!-- ###TEMPLATE_EDIT### end-->


<!-- ###TEMPLATE_EDIT_SAVED### begin-->
<h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
<p>'.$this->pi_getLL("feAL_contentSaved").'</p>
<p>&nbsp;</p>
<!-- ###TEMPLATE_EDIT_SAVED### end-->


<!-- ###TEMPLATE_CREATE_LOGIN### -->
<h3>Create new consultancy entry</h3>
<table border=0 cellpadding=1 cellspacing=2>
<FORM name="tx_t3consultancies_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
'.$this->makeFormFromConfig($feAConf["create."],$feAConf["table"]).'
<tr>
	<td></td>
	<td></td>
	<td>
		###HIDDENFIELDS###
		<input type="Submit" name="submit" value="'.$this->pi_getLL("feAL_save").'">
	</td>
</tr>
</FORM>
</table>
<!-- ###TEMPLATE_CREATE_LOGIN### end-->


<!-- ###TEMPLATE_CREATE_SAVED### begin-->
<h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
<p>'.$this->pi_getLL("feAL_contentSaved").'</p>
<p>&nbsp;</p>
<!-- ###TEMPLATE_CREATE_SAVED### end-->







<!-- ###TEMPLATE_DELETE_SAVED### begin-->
<h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
<p>'.$this->pi_getLL("feAL_deleteSaved").'</p>
<p>&nbsp;</p>
<!-- ###TEMPLATE_DELETE_SAVED### end-->





<!-- ###TEMPLATE_EDITMENU### begin -->
	<h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
	<p>'.$this->pi_getLL("feAL_listOfItems").'</p>
	<p>--</p>
		<!-- ###ALLITEMS### begin -->
			<!-- ###ITEM### begin -->
				<p><a href="###FORM_URL###&rU=###FIELD_uid###&cmd=edit">###FIELD_title###</a></p>
			<!-- ###ITEM### end -->
		<!-- ###ALLITEMS### end -->
	<p>--</p>
	<p><a href="###FORM_URL###&cmd=">'.$this->pi_getLL("feAL_createNew").'</a></p>
	<p>&nbsp;</p>
<!-- ###TEMPLATE_EDITMENU### -->

<!-- ###TEMPLATE_EDITMENU_NOITEMS### begin -->
	<h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
	<p>'.$this->pi_getLL("feAL_noItems").'</p>
	<p><a href="###FORM_URL###&cmd=">'.$this->pi_getLL("feAL_createNew").'</a></p>
	<p>&nbsp;</p>
<!-- ###TEMPLATE_EDITMENU_NOITEMS### -->









<!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### begin -->
New consultancy created.

	<!--###SUB_RECORD###-->
	Title: ###FIELD_title###
	Description: ###FIELD_description###
	Contact email: ###FIELD_contact_email###
	Contact name: ###FIELD_contact_name###

	
	Approve:
	###THIS_URL######FORM_URL######SYS_SETFIXED_approve###
	
	Delete:
	###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
	<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### end-->

<!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### begin -->
Consultancy record edited.

	<!--###SUB_RECORD###-->
	Title: ###FIELD_title###
	Description: ###FIELD_description###
	Contact email: ###FIELD_contact_email###
	Contact name: ###FIELD_contact_name###

	
	Approve:
	###THIS_URL######FORM_URL######SYS_SETFIXED_approve###
	
	Delete:
	###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
	<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### end-->



<!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->
Consultancy DELETED!

<!--###SUB_RECORD###-->
Title: ###FIELD_title###
Description: ###FIELD_description###

Your entry has been deleted by the admin for some reason.

- kind regards.
<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->



<!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->
Consultancy approved

<!--###SUB_RECORD###-->
Title: ###FIELD_title###
Description: ###FIELD_description###

Your consultancy entry has been approved! 

- kind regards.
<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->













<!-- ###TEMPLATE_SETFIXED_OK### -->
<h3>Setfixed succeeded</h3>
Record uid; ###FIELD_uid###
<!-- ###TEMPLATE_SETFIXED_OK### end-->

<!-- ###TEMPLATE_SETFIXED_OK_DELETE### -->
<h3>Setfixed delete record "###FIELD_uid###"</h3>
<!-- ###TEMPLATE_SETFIXED_OK_DELETE### end-->

<!-- ###TEMPLATE_SETFIXED_FAILED### -->
<h3>Setfixed failed!</h3>
<p>May happen if you click the setfixed link a second time (if the record has changed since the setfixed link was generated this error will happen!)</p>
<!-- ###TEMPLATE_SETFIXED_FAILED### end-->



<!-- ###TEMPLATE_AUTH### -->
<h3>Authentication failed</h3>
<p>Of some reason the authentication failed. </p>
<!-- ###TEMPLATE_AUTH### end-->

<!-- ###TEMPLATE_NO_PERMISSIONS### -->
<h3>No permissions to edit record</h3>
<p>Sorry, you did not have permissions to edit the record.</p>
<!-- ###TEMPLATE_NO_PERMISSIONS### end-->

';

		$feAConf["addParams"]=$this->conf["parent."]["addParams"].t3lib_div::implodeArrayForUrl($this->prefixId,$this->piVars,"",1);
		return $feAConf;
	}
	
	/**
	 * 
	 */
	function makeFormFromConfig($conf,$table)	{
#	debug($table);
		$fields = array_unique(t3lib_div::trimExplode(",",$conf["fields"],1));
		$reqFields = array_unique(t3lib_div::trimExplode(",",$conf["required"],1));
#debug($conf);

		$tableRows = array();
		while(list(,$fN)=each($fields))	{
			$fieldCode = $this->getFormFieldCode($fN,'FE['.$table.']['.$fN.']');
			if ($fieldCode)	{
				if (in_array($fN,$reqFields))	{
					$reqMsg='<!--###SUB_REQUIRED_FIELD_'.$fN.'###--><p style="color: red; font-weight: bold;">'.$this->pi_getLL("feAL_required").'</p><!--###SUB_REQUIRED_FIELD_'.$fN.'###-->';
					$reqMarker=$this->pi_getLL("feAL_requiredMark");
				} else {
					$reqMsg='';
					$reqMarker='';
				}
				
				$tableRows[]='<tr>
					<td><p>'.$this->pi_getLL("feAL_fN_".$fN,"feAL_fN_".$fN).' '.$reqMarker.'</p></td>
					<td><img src="clear.gif" width=10 height=1></td>
					<td>'.$reqMsg.$fieldCode.'</td>
				</tr>';
			}
		}
		
		return implode(chr(10),$tableRows);
#	debug($tableRows);
	}
	function getFormFieldCode($fN,$fieldName)	{
		switch($fN)	{
			case "description":
				return '<textarea name="'.$fieldName.'" rows="5" wrap="virtual" style="width: 300px;"></textarea>';
			break;
			case "logo":
				return '<input type="file" name="'.$fieldName.'[]" style="width: 300px;">';
			break;
			case "hidden":
				return "";
			break;
			case "cntry":
				$opt=array();
				$opt[]='<option value="0"></option>';
				$query = "SELECT uid,cn_short_en FROM static_countries ORDER BY cn_short_en";
				$res = mysql(TYPO3_db,$query);
				while($row=mysql_fetch_assoc($res))	{
					$opt[]='<option value="'.$row["uid"].'">'.$row["cn_short_en"].'</option>';
				}
				return '<select name="'.$fieldName.'">'.implode("",$opt).'</select>';
			break;
			case "services":
				$opt=array();
				$query = "SELECT uid,title FROM tx_t3consultancies_cat WHERE 1=1 ".
							$this->cObj->enableFields("tx_t3consultancies_cat").
							" ORDER BY title";
				$res = mysql(TYPO3_db,$query);
				while($row=mysql_fetch_assoc($res))	{
					$opt[]='<option value="'.$row["uid"].'">'.$row["title"].'</option>';
				}
				return '<select name="'.$fieldName.'[]" multiple size='.count($opt).'>'.implode("",$opt).'</select>';
			break;
			default:
				return '<input type="text" name="'.$fieldName.'" style="width: 300px;">';
			break;
		}
	}
	
	
	function afterSave($content,$conf)	{
		$inVar = t3lib_div::GPvar("FE",1);
		$services = $inVar["tx_t3consultancies"]["services"];
		$uid = $content["rec"]["uid"];
		
		if (intval($uid)>0 && is_array($services))	{
			$query = "DELETE FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($uid);
			$res = mysql(TYPO3_db,$query);
			
			if (is_array($services))	{
				reset($services);
				while(list($k,$sId)=each($services))	{
					$query = "INSERT INTO tx_t3consultancies_services_mm (uid_local,uid_foreign,tablenames,sorting) VALUES (".intval($uid).",".intval($sId).",'',".intval($k).")";
					$res = mysql(TYPO3_db,$query);
				}
			}
		}
	}
	function updateArray($content,$conf)	{
		
		$content["services"]=array();
		$query = "SELECT uid_foreign FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($content["uid"]);
		$res = mysql(TYPO3_db,$query);
		while($row=mysql_fetch_assoc($res))	{
			$content["services"][]=$row["uid_foreign"];
		}

		unset($content["weight"]);
		unset($content["selected"]);

		return $content;
	}

	/**
	 * Returns array containing textImage helper
	 *
	 * @param array conf
	 * @return array
	 */
	function buildTextImageConf( $conf )
	{
		$title					= $conf[ 'title.' ];
		$temp					= array();

		$temp['alttext.']['current']		= 1;
		$temp['stdWrap.']['wrap']			=
								'<div style="border: 0;"> | </div>';
		$temp['file']						= 'GIFBUILDER';
		$temp['file.']['XY']				= '[10.w],[10.h]';
		$temp['file.']['backColor']			= $title[ 'backColor' ];
		$temp['file.']['10']				= 'TEXT';
		$temp['file.']['10.']['fontColor']	= $title[ 'fontColor' ];
		$temp['file.']['10.']['fontFile']	= $title[ 'fontFile' ];
		$temp['file.']['10.']['fontSize']	= $title[ 'fontSize' ];
		$temp['file.']['10.']['maxWidth']	= 600;
		$temp['file.']['10.']['align']		= 'left';
		$temp['file.']['10.']['offset']		= '0,17';
		$temp['file.']['10.']['niceText']	= 1;

		$conf[ 'textImage' ]	= $temp;

		return $conf;
	}

	/**
	 * Return string of textual image.
	 *
	 * @param string image text
	 * @return string
	 */
	function textImage( $text )
	{
		$this->conf[ 'textImage' ]['file.']['10.']['text']	= $text;

		$image					= $this->cObj->cObjGetSingle( 'IMAGE'
									, $this->conf[ 'textImage' ]
								);

		return $image;
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3consultancies/pi1/class.tx_t3consultancies_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3consultancies/pi1/class.tx_t3consultancies_pi1.php"]);
}

?>
