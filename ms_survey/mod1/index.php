<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Michael Scharkow (mscharkow@gmx.net)
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
 * Module 'Survey Results' for the 'ms_survey' extension.
 *
 * @author	Michael Scharkow <mscharkow@gmx.net>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:ms_survey/mod1/locallang.php');
#include ('locallang.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_mssurvey_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * @return   [type]      ...
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();

		/*
		if (t3lib_div::GPvar('clear_all_cache'))	{
			$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return   [type]      ...
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return   [type]      ...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
	
				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
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

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$headerSection);
		//	$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			
			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return   [type]      ...
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 *
	 * @return   [type]      ...
	 */
	function moduleContent()	{
		
		global $LANG;
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				array($resultarray);
				array($allvars);
				$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,results,fe_cruser_id,crdate','tx_mssurvey_results','pid='.intval($this->id).' AND deleted=0');
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)){
			
					$result = explode('","',trim($row[1],'"'));
					array(results);	
					array(vars);
					foreach ($result as $item){
						$vars = explode('":"',$item);
						$results[$vars[0]] =  stripslashes($vars[1]);
					}
					$resultarray[$row[0]]['results'] = $results;
					$resultarray[$row[0]]['survey_user'] = $row[2];
					$resultarray[$row[0]]['crdate'] = t3lib_BEfunc::datetime($row[3]);

					$allvars = array_merge($allvars,array_keys($results));

					unset($vars);
					unset($results);
				}
				
				if (!$allvars) {
					$content = '<p>'.$LANG->getLL('noresults').'</p>'; 
					$this->content.=$this->doc->section($LANG->getLL('error'),$content,0,1);
					break;
				}
				
				$allvars = array_values(array_unique($allvars));
				
				if (t3lib_div::GPvar('cleanvars')) {
					$cleanvars = preg_replace('/[^a-zA-Z0-9ÄÖÜäöüß]+/','_',$allvars);
				} else {
					$cleanvars = $allvars;
				}
				$csv = '"id",';
				$csv .= '"datetime",';
				$csv .= '"survey_user","';
				$csv .= implode('","',$cleanvars).'"'."\n";

				foreach ($resultarray as $name=>$values){

					if (t3lib_div::GPvar('cleanvars')) {
						$values['results'] = str_replace('"',"'",$values['results']);
						}
				
					$csv .= '"'.$name;
					$csv .= '","'.$values['crdate'];
					$csv .= '","'.$values['survey_user'];
					foreach ($allvars as $var){
						$csv .= '","'.$values['results'][$var];
					}
					$csv .= '"'."\n";
				}
				$as_csv .= t3lib_div::GPvar('csv');
				$content .= '<p>'.implode('<br>',$allvars).'</p>';
				
				
				$stats = '<p>'.$LANG->getLL('numcases').': '.count($resultarray).'</p>';
				$stats .= '<p>'.$LANG->getLL('numvars').': '.count($allvars).'</p>';
				
				$this->content.=$this->doc->section($LANG->getLL('stats'),$stats,0,1);
				$this->content.=$this->doc->section($LANG->getLL('varlist'),$content,0,1);
				
				$form .='<input type="hidden" value="1" name="csv"/>';
				$form .='<p><input type="checkbox" name="cleanvars" value="1"/> '.$LANG->getLL('cleancsv').'</p>';
				$form .='<p><input type="submit" value="'.$LANG->getLL('csvsubmit').'"/></p>';
				$this->content.=$this->doc->section($LANG->getLL('csvexport'),$form,0,1);
				
				if ($as_csv){
				$mimetype = 'text/comma-separated-values';
				$title .= str_replace(' ', '', $this->pageinfo['title']);
				$filename = 'res_'.$title.'_'.date('dmy-Hi').'.csv';
				Header('Content-Type: '.$mimetype);
				Header('Content-Disposition: attachment; filename='.$filename);
				echo $csv;
				exit;

				}
				
			break;
/*			case 2:
				$content='<div align=center><strong>Menu item #2...</strong></div>';
				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 3:
				$content='<div align=center><strong>Menu item #3...</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break; */
		} 
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ms_survey/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ms_survey/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_mssurvey_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
