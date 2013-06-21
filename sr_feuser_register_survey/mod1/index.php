<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Chetan Thapliyal (chetan@srijan.in)
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
 * Module 'Regist. Survey' for the 'sr_feuser_register_survey' extension.
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:sr_feuser_register_survey/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_srfeuserregistersurvey_module1 extends t3lib_SCbase {
	var $pageinfo;
	
	/**
	 * Front usergroups table
	 *
	 * @access private
	 * @var string
	 */
	 var $user_groups;

	/**
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		
		// Initialize class variables
		$this->user_groups = 'fe_groups';

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

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

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
			//$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection, ''));
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
		global $LANG;
		
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:
				// generate quick stats
				$select  = ' count( sr.uid ) caseCount';
				$table   = ' tx_mssurvey_results AS sr';
				$join    = ' LEFT JOIN fe_users AS fu ON sr.fe_cruser_id = fu.uid';
				$where   = ' sr.pid = '.intval( $this->id);
				$where  .= ( t3lib_div::_POST( 'survey_domain'))
						   ? ' AND domain_group_id = '.intval( t3lib_div::_POST( 'survey_domain'))
						   : '';
				$where  .= ' AND sr.deleted = 0';
				$where  .= ' AND sr.hidden = 0';
				$where  .= ' AND ( fu.deleted = 0 OR sr.fe_cruser_id = 0 )';
				// $where  .= ' AND fu.disable = 0';

				// uncomment this for debugging
				
				$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table.$join, $where);
				
				if ( $rs
					&& $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs))
				{
					$caseCount	= $row[ 'caseCount' ];
					//  $bpmCount	= $row[ 'bpmCount' ];
					//  $soaCount	= $row[ 'soaCount' ];
				}

				if ( ! $caseCount )
				{
					$content = '<p>'.$LANG->getLL('noresults').'</p>'; 
					$this->content.=$this->doc->section($LANG->getLL('error'),$content,0,1);
					break;
				}

				// MLC display quick stats
				$stats    = '<p>'.$LANG->getLL('numcases').': '.$caseCount.'</p>';
				//  $stats    .= '<p>'.$LANG->getLL('bpmCount').': '.$bpmCount.'</p>';
				//  $stats    .= '<p>'.$LANG->getLL('soaCount').': '.$soaCount.'</p>';
				
				$this->content.=$this->doc->section($LANG->getLL('stats'),$stats,0,1);

				// MLC display download link
				$form = $this->getSurveyDomainSelection();
				$form .= '<br /><br /><input type="hidden" value="1" name="csv"/>';
				$form .= '<input type="hidden" value="1" name="csv"/>';
// 				$form .= '<p><input type="checkbox" name="cleanvars" value="1"/> '.$LANG->getLL('cleancsv').'</p>';
				$form .= '<p><input type="submit" value="'.$LANG->getLL('csvsubmit').'"/></p>';
				$this->content.=$this->doc->section($LANG->getLL('csvexport'),$form,0,1);

				// download report
				$as_csv  = t3lib_div::GPvar('csv');

				// do download if needed
				if ( $as_csv )
				{
					$cleanvarsBool	= t3lib_div::GPvar('cleanvars');

					// MLC processing time
					set_time_limit( 600 );

					// file container
					$filenameTmp	= '/tmp/' . uniqid( '' ) . '.csv';
					$filelink		= fopen( $filenameTmp, 'w+' );

					// create list of all survey result keys
					$allvars     = array();
					
					$select  = ' sr.results';
					$where  .= ' limit 20000';

					// uncomment this for debugging
					// $this->debug_print( $GLOBALS['TYPO3_DB']->SELECTquery( $select, $table.$join, $where));
					
					$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table.$join, $where);
					
					while ( $rs && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs))
					{
						$result = explode( '","', trim( $row['results'], '"'));
						$results = array();	
						
						foreach ( $result as $item )
						{
							list( $itemName, $answer) = explode( '":"', $item);
							$itemName = str_replace( '":', '', $itemName );
							$results[$itemName] =  stripslashes( $answer);
						}
						
						$allvars = array_merge($allvars, array_keys($results));
						$allvars = array_values( array_unique( $allvars));
					
						unset($results);
					}

					if ( empty( $allvars ) )
					{
						$content = '<p>'.$LANG->getLL('noresults').'</p>'; 
						$this->content.=$this->doc->section($LANG->getLL('error'),$content,0,1);
						break;
					}

					// prevent first item from disappearing per next blank
					// removal
					if ( isset( $allvars[ 0 ] ) )
					{
						array_push( $allvars, $allvars[ 0 ] );
					}

					unset( $allvars[ array_search( '', $allvars ) ] );
					@natsort( $allvars );
					
					if ( $cleanvarsBool ) {
						$cleanvars = preg_replace( '/[^a-zA-Z0-9�������]+/', '_', $allvars);
					} else {
						$cleanvars = $allvars;
					}

					$csv  = '"Survey ID",';
					$csv .= '"Create Date Time",';
					$csv .= '"Update Date Time",';
					$csv .= '"Survey User ID",';
					$csv .= '"Survey User Name",';
					$csv .= '"Company",';
					$csv .= '"Email ",';
					$csv .= '"State/Province",';
					$csv .= '"Country",';
					$csv .= '"User IP",';
					$csv .= '"The_BPM_Bulletin",';
					$csv .= '"SOA_Newsletter",';
					$csv .= '"Business_Rules_Newsletter",';
					$csv .= '"Operational_Performance_Newsletter",';
					$csv .= '"RFID_Newsletter",';
					$csv .= '"Governance_Newsletter",';
					$csv .= '"Business_Architecture",';
					$csv .= '"Compliance",';
					$csv .= '"Government",';
                    $csv .= '"Innovation",';					
					$csv .= '"'
							. implode( '","', array_map( 'ucfirst', $cleanvars))
							. '"'
							. "\n";

					fwrite( $filelink, $csv );
					
					// query user details and results as normal
					$select  = ' sr.uid, sr.results, sr.fe_cruser_id, sr.tstamp,';
					$select .= ' sr.crdate, sr.domain_group_id, fu.first_name, fu.last_name';
					$select .= ' , sr.remoteaddress';
					$select .= ' , fu.email';
					$select .= ' , fu.company';
					$select .= ' , c.cn_short_en country';
					$select .= ' , fu.zone';
					$select .= "
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter1 = 1 ) THEN 'Yes'
			ELSE 'No'
			END The_BPM_Bulletin
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter5 = 1 ) THEN 'Yes'
			ELSE 'No'
			END SOA_Newsletter
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter2 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Business_Rules_Newsletter
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter3 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Operational_Performance_Newsletter
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter4 = 1 ) THEN 'Yes'
			ELSE 'No'
			END RFID_Newsletter
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter6 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Governance_Newsletter
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter7 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Business_Architecture
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter8 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Compliance
		, CASE
			WHEN ( fu.tx_bpmprofile_newsletter9 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Government
        , CASE
		            WHEN ( fu.tx_bpmprofile_newsletter10 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Innovation
					";
					$table   = ' tx_mssurvey_results AS sr';
					$join    = ' LEFT JOIN fe_users AS fu ON sr.fe_cruser_id = fu.uid';
					$join    .= ' LEFT JOIN static_countries c ON fu.static_info_country = c.cn_iso_3';
					$where   = ' sr.pid = '.intval( $this->id);
					$where  .= ( t3lib_div::_POST( 'survey_domain'))
							   ? ' AND domain_group_id = '.intval( t3lib_div::_POST( 'survey_domain'))
							   : '';
					$where  .= ' AND sr.deleted = 0';
					$where  .= ' AND sr.hidden = 0';
					// $where  .= ' AND ( fu.deleted = 0 OR sr.fe_cruser_id = 0 )';
					// $where  .= ' AND fu.disable = 0';
					$where  .= ' limit 20000';

					// uncomment this for debugging
					// $this->debug_print( $GLOBALS['TYPO3_DB']->SELECTquery( $select, $table.$join, $where));
					
					$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table.$join, $where);
					
					while ( $rs && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs))
					{
						$resultarray = array();
						$result = explode( '","', trim( $row['results'], '"'));
						$results = array();	
						
						foreach ( $result as $item){
							list( $itemName, $answer) = explode( '":"', $item);
							$itemName = str_replace( '":', '', $itemName );
							$results[$itemName] =  stripslashes( 
													preg_replace( "#\s#"
														, ' '
														, $answer )
													);
						}
						
						if ( $cleanvarsBool )
						{
							$results = str_replace( '"', "'", $results);
						}
				
						$resultarray['survey_user_id'] = $row['fe_cruser_id'];

						// MLC anonymous user case
						if ( 0 != $resultarray['survey_user_id'] )
						{
							$resultarray['survey_user'] = $row['first_name'].' '.$row['last_name'];
						}

						else
						{
							$resultarray['survey_user'] = 'Anonymous';
						}

						$resultarray['tstamp'] = t3lib_BEfunc::datetime( $row['tstamp']);
						$resultarray['crdate'] = t3lib_BEfunc::datetime( $row['crdate']);
						$resultarray['results'] = $results;
						$resultarray['uid'] = $row['uid'];
	
						// create csv line
						$csv = '"'.$resultarray['uid'] .'",';
						$csv .= '"'.$resultarray['crdate'] .'",';
						$csv .= '"'.$resultarray['tstamp'] .'",';
						$csv .= '"'.$resultarray['survey_user_id'] .'",';
						$csv .= '"'.$resultarray['survey_user'] .'",';
						$csv .= '"'.$row['company'] .'",';
						$csv .= '"'.$row['email'] .'",';
						$csv .= '"'.$row['zone'] .'",';
						$csv .= '"'.$row['country'] .'",';
						$csv .= '"'.$row['remoteaddress'] .'",';
						$csv .= '"'.$row['The_BPM_Bulletin'] .'",';
						$csv .= '"'.$row['SOA_Newsletter'] .'",';
						$csv .= '"'.$row['Business_Rules_Newsletter'] .'",';
						$csv .= '"'.$row['Operational_Performance_Newsletter'] .'",';
						$csv .= '"'.$row['RFID_Newsletter'] .'",';
						$csv .= '"'.$row['Governance_Newsletter'] .'",';
						$csv .= '"'.$row['Business_Architecture'] .'",';
						$csv .= '"'.$row['Compliance'] .'",';
						$csv .= '"'.$row['Government'] .'",';
                        $csv .= '"'.$row['Innovation'] .'"';						
						
						foreach ( $allvars as $var){
							$csv .= ',"' . $resultarray['results'][$var] . '"';
						}

						$csv .= "\n";
						fwrite( $filelink, $csv );
					
						unset($results);
						unset($resultarray);
					}

					fclose( $filelink );

					$mimetype = 'text/comma-separated-values';
					$title .= str_replace(' ', '', $this->pageinfo['title']);
					$filename = 'res_'.$title.'_'.date('ymd-Hi').'.csv';
					Header('Content-Type: '.$mimetype);
					Header('Content-Disposition: attachment; filename='.$filename);
					$csv		= file_get_contents( $filenameTmp );
					unlink( $filenameTmp );

					echo $csv;
					exit;
				}
				
			break;
		}
	}

    function getSurveyDomainSelection() {
        $content = '
            <select name="survey_domain">
                <option value="">-- Select Domain --</option>
        ';
        
        // Obtain comma separated values of valid survey domain user group ids
        $extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sr_feuser_register_survey']);
        $survey_domain_usergroups = $extConf['surveyDomainUserGroups'];
        
        $select = 'uid, title';
        $table  = $this->user_groups;
        $where  = 'uid IN ( '.$survey_domain_usergroups.' )';
        
        $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select, $table, $where);
        
        if ( $rs) {
        
            if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {
            
                while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {
                
                    $content .= '<option value="'.$row['uid'].'">'.$row['title'].'</option>';
                    
                }
                
            }
        }

        $content .= '</select>';

        return $content;
    }
	
	function debug_print( $var) {
		echo '<pre>';
		print_r( $var);
		echo '</pre>';
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register_survey/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_feuser_register_survey/mod1/index.php"]);
}




// Make instance:
$SOBE = t3lib_div::makeInstance("tx_srfeuserregistersurvey_module1");
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
