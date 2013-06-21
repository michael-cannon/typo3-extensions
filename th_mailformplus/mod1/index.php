<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 netdog (netdog@typoheads.com)
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
 * Module 'mailformplusbackend' for the 'th_mailformplus' extension.
 *
 * @author	netdog <netdog@typoheads.com>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:th_mailformplus/mod1/locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");

require_once (PATH_t3lib."class.t3lib_page.php");
require_once (PATH_t3lib."class.t3lib_tsparser_ext.php");

require_once (PATH_site.'tslib/'."class.tslib_content.php"); # for getting the cObj (needed for reading the template file
require_once (PATH_t3lib."class.t3lib_tstemplate.php");


$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_thmailformplus_module1 extends t3lib_SCbase {
	var $pageinfo, $theSetup;

	/**
	 * 
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT;

		$this->cObj = t3lib_div::makeInstance("tslib_cObj");

		$this->tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");	// Defined global here!
		$this->tmpl->tt_track = 0;	// Do not log time-performance information
		$this->tmpl->init();
		$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
		$rootLine = $sys_page->getRootLine(t3lib_div::_GP("id"));

		$template_uid = 0;
		$this->tmpl->runThroughTemplates($rootLine,$template_uid);	// This generates the constants/config + hierarchy info for the template.

		$tplRow = $this->tmpl->ext_getFirstTemplate(t3lib_div::_GP("id"),$template_uid);	// Get the row of the first VISIBLE template of the page. whereclause like the frontend.
		$this->tmpl->generateConfig();
		$this->theSetup = $this->tmpl->setup_constants;

		parent::init();

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
					
					function set_offset(wert) {
		    			    document.forms[0].offset.value=wert;
					    document.forms[0].submit();
					    return;
					}
		    
					function set_exportall(wert) {
					    document.forms[0].exportall.value = "1";
					    document.forms[0].submit();
					    return;
					}
		    
					function set_deleteall(wert) {
					    document.forms[0].deleteall.value = "1";
					    document.forms[0].submit();
					    return;
					}
					
					function set_deleteone(wert) {
					    document.forms[0].deleteone.value = wert;
					    document.forms[0].submit();
					    return;
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

		global $LANG;
		
		    # default settings
		$limit = 30;
		$offset = 0;


		    # build SQL for select
		$what = '*';
		$from = 'tx_thmailformplus_log';

		    # WHERE
		    # pid selected
		if (t3lib_div::_GP('pid') && $this->MOD_SETTINGS["function"] == '2') {
		    $where = 'pid="'.t3lib_div::_GP('pid').'"';
		}
		    # page from tree selected (single view only)
		if (t3lib_div::_GP('id') && $this->MOD_SETTINGS["function"] == '2') {
		    $where = 'pid="'.t3lib_div::_GP('id').'"';
		}
		    # multiple pages selected (via checkbox)
		$showPIDs = t3lib_div::_GP('showPIDs');
		    # show records from multiple pages
		if (is_array($showPIDs) && sizeof($showPIDs) > 0) {
		    $where = 'pid IN ('.implode(',', $showPIDs).')';
		}
		    # time range "from"
		if (sprintf('%u', t3lib_div::_GP('timeFromY')) && sprintf('%u', t3lib_div::_GP('timeFromMon'))) {
		    $year = sprintf('%u', t3lib_div::_GP('timeFromY'));
		    $mon = sprintf('%02u', t3lib_div::_GP('timeFromMon'));
		    $day = sprintf('%02u', t3lib_div::_GP('timeFromD'));
		    $hour = sprintf('%02u', t3lib_div::_GP('timeFromH'));
		    $min = sprintf('%02u', t3lib_div::_GP('timeFromMin'));
		    
		    if (strlen($where) > 0) {
			$where .= ' AND ';
		    }
		    
		    $where .= ' date > "'."$year-$mon-$day $hour:$min:00".'" ';
		}

		    # time range "till"
		if (sprintf('%u', t3lib_div::_GP('timeTillY')) && sprintf('%u', t3lib_div::_GP('timeTillMon'))) {
		    $year = sprintf('%u', t3lib_div::_GP('timeTillY'));
		    $mon = sprintf('%02u', t3lib_div::_GP('timeTillMon'));
		    $day = sprintf('%02u', t3lib_div::_GP('timeTillD'));
		    $hour = sprintf('%02u', t3lib_div::_GP('timeTillH'));
		    $min = sprintf('%02u', t3lib_div::_GP('timeTillMin'));
		    
		    if (strlen($where) > 0) {
			$where .= ' AND ';
		    }
		    
		    $where .= ' date < "'."$year-$mon-$day $hour:$min:00".'" ';
		}

								
		if (t3lib_div::_GP('showDownloaded') == "showDownloaded") {
#		    $where .= ' downloaded="y"';
		} else {

		        # show records already downloaded?
		    if (strlen($where) > 0) {
			$where .= ' AND ';
		    }

		    $where .= ' downloaded="n"';
		}

		    # GROUP BY
		if (t3lib_div::_GP('orderby')) {
		    $orderby = t3lib_div::_GP('orderby').',';
		}	
		
		    # ORDER BY
		$orderby .= 'date DESC';	
		
		    # LIMIT
		if (sprintf('%u', t3lib_div::_GP('limit')) >  0 ) {
		    $limit = sprintf('%u', t3lib_div::_GP('limit'));
		    
		    if (sprintf('%u', t3lib_div::_GP('offset'))) {
			$offset = sprintf('%u', t3lib_div::_GP('offset'));
		    }
		} 
		$sqlLimit = $offset.','.$limit;
		
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
		    $what,
		    $from,
		    $where,
		    $groupby,
		    $orderby,
		    $sqlLimit
		    );

		$query_count = $GLOBALS['TYPO3_DB']->SELECTquery(
		    'COUNT(*) as count',
		    $from,
		    $where,
		    '',
		    '',
		    '1'
		    );
		
		$query_export = $GLOBALS['TYPO3_DB']->SELECTquery(
		    $what,
		    $from,
		    $where,
		    $groupby,
		    $orderby,
		    ''
		    );
		    
		# delete query
		$query_delete = 'DELETE FROM '.$from;
		if ($where) {
		    $query_delete = $query_delete.' WHERE '.$where;
		}
		
		# update query
		$query_update = 'UPDATE '.$from.' SET downloaded="y" WHERE '.$where;
		if ($where) {$query_update = $query_update.' AND ';}

# print_r($query);

		    # select number of records totally affected
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_count);
                if ($res) {
		    if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->totalCount = $row['count'];
		    }
		}
		
		    # show next/back link?
		if ($offset > 0) {
		    $showBackLink = 1;
		    $this->BackLink = '<a href="#" onclick="set_offset(\''.(sprintf('%u', t3lib_div::_GP('offset'))-$limit).'\');">'.$LANG->getLL('back').'</a>';
		}
		if ($this->totalCount-sprintf('%u', t3lib_div::_GP('offset')) > $limit) {
		    $showNextLink = 1;
		    $this->NextLink = '<a href="#" onclick="set_offset(\''.(sprintf('%u', t3lib_div::_GP('offset'))+$limit).'\');">'.$LANG->getLL('next').'</a>';
		}
		
		    # page info
		$this->pageInfo = $LANG->getLL('page').' '.((sprintf('%u', t3lib_div::_GP('offset')/$limit))+1).' '.$LANG->getLL('of').' '.sprintf('%u', ($this->totalCount/$limit)+1);
		

		    #############################
		    # records should be exported
		    #############################
		if (t3lib_div::_GP('exportall') == "1") {
		    $content .= '<table border=2 width="100%" cellspacing=0 cellpadding=5 style="border-color:red;border-style:solid;"><tr><td>';
		    $content .= $this->export($query_export,$query_update);
		    $content .= '</td></tr></table>';
		}

		    #############################
		    # records should be deleted
		    #############################
		if (t3lib_div::_GP('deleteall') == "1") {

		    $content .= '<table border=2 width="100%" cellspacing=0 cellpadding=5 style="border-color:red;border-style:solid;"><tr><td>';

		    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_delete);
		    if ($res) {
			$content .= $this->totalCount.' '.$LANG->getLL('records').' '.$LANG->getLL('deleted').'.';
		    } else {
			$content .= $LANG->getLL('error_on_delete').' '.$query_delete;
		    }
		    $content .= '</td></tr></table>';

			# once more: select number of records totally affected
		    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_count);
		    $this->totalCount = 0;
            	    if ($res) {
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			    $this->totalCount = $row['count'];
			}
		    }

		}

		    #############################
		    # one record should be deleted
		    #############################
		if (sprintf('%u', t3lib_div::_GP('deleteone')) > 0) {
		    $query_delete_one = 'DELETE FROM '.$from.' WHERE uid="'.t3lib_div::_GP('deleteone').'"';
		    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_delete_one);
		    if ($res) {
			$content .= $LANG->getLL('records').' '.$LANG->getLL('deleted').'.';
			$this->totalCount--;
		    } else {
			$content .= $LANG->getLL('error_on_delete');
		    }
		}


		switch($this->MOD_SETTINGS["function"]) {
			case '1':
				$content .= $this->show_function1($query);
				$this->content.=$this->doc->section($LANG->getLL("Message#1"),$content,0,1);
				break;
			case '2':
				$content .= $this->show_function2($query);
				$this->content.=$this->doc->section($LANG->getLL("Message#2"),$content,0,1);
			break;
		}


	}


	##########################
	# function 1: TCA
	# shows all submitted forms
	# can be grouped by "pid" or limitted between two dates
	##########################
	function show_function1($query,$pid='') {

		global $TCA, $LANG;


		    # start form
		$content .= '<form action="index.php" method="POST" name="thmailform">';
		    # hidden field for offset
		$content .= '<input type="hidden" name="offset" value="'.t3lib_div::_GP('offset').'">';
		    # hidden field for export
		$content .= '<input type="hidden" name="exportall" value="">';
		    # hidden field for deleting
		$content .= '<input type="hidden" name="deleteall" value="">';
		    # hidden field for deleting one
		$content .= '<input type="hidden" name="deleteone" value="">';
		    
		    # start table 
		$content .= '<table border="1" cellspacing="0" cellpadding="3">';
		
		    # multiple page view
		    # show records from specific pages (multiple pageIDs seperated by ,)
		if (!$pid) {
		
		    $showPIDs = t3lib_div::_GP('showPIDs');
		
			# select all pages with mailform elements
		    $query2 = $GLOBALS['TYPO3_DB']->SELECTquery(
			'DISTINCT(pid)',
			'tx_thmailformplus_log',
			'',
			'',
			'',
			''
			);
		    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query2);
		    if ($res) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			    if (is_array($showPIDs) && in_array($row['pid'], $showPIDs)) {
				$checked = 'checked';
			    } else {
				$checked = '';
			    }
			    $temp .= '<input type="checkbox" name="showPIDs[]" value="'.$row['pid'].'" '.$checked.'> '.t3lib_BEfunc::getRecordPath(intval($row['pid']),'','').'<br/>';
			}
		    }
			
    		    $content .= '<tr><td><b>'.$LANG->getLL('show_records_of_this_page').'</b>: </td>'.
			'<td>'.$temp.'</td></tr>';
			
			
		} else {
		    $content .= '<tr><td colspan="2"><b>'.$LANG->getLL('selected').' '.$LANG->getLL('page').': '.$pid.'</b> <input type="hidden" name="pid" value="'.$pid.'"></td></tr>';
		}
		
		    # multiple page view
		    # generate pulldown for sort function
		if (!$pid) {
		    if (t3lib_div::_GP('orderby')) {
			$selected_pid = 'selected';
		    }
		    $content .= '<tr><td><b>'.$LANG->getLL('sorting').'</b>: </td>'.
			'<td><select name="orderby">'.
			'<option value="">'.$LANG->getLL('date').
			'<option value="pid" '.$selected_pid.'>PageID, '.$LANG->getLL('date').
			'</select></td></tr>';
		}
		    
		    # generate fields for "from" "till" fields
		$content .= '<tr><td><b>'.$LANG->getLL('period').'</b>:</td><td>'.
		    '<table border="0"><tr><td>&nbsp;</td><td>YYYY</td><td>MM</td><td>DD*</td><td>HH*</td><td>MM*</td></tr>'.
		    '<tr><td>'.$LANG->getLL('from').': </td>'.
		    '<td><input type="text" size="4" maxlength="4" name="timeFromY" value="'.t3lib_div::_GP('timeFromY').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeFromMon" value="'.t3lib_div::_GP('timeFromMon').'"></td> '.
		    '<td><input type="text" size="2" maxlength="2" name="timeFromD" value="'.t3lib_div::_GP('timeFromD').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeFromH" value="'.t3lib_div::_GP('timeFromH').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeFromMin" value="'.t3lib_div::_GP('timeFromMin').'"></td>'.
		    '</tr>'.
		    '<tr>'.
		    '<td>'.$LANG->getLL('till').': </td>'.
		    '<td><input type="text" size="4" maxlength="4" name="timeTillY" value="'.t3lib_div::_GP('timeTillY').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeTillMon" value="'.t3lib_div::_GP('timeTillMon').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeTillD" value="'.t3lib_div::_GP('timeTillD').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeTillH" value="'.t3lib_div::_GP('timeTillH').'"></td>'.
		    '<td><input type="text" size="2" maxlength="2" name="timeTillMin" value="'.t3lib_div::_GP('timeTillMin').'"></td>'.
		    '</tr></table>'.
		    '* ... '.$LANG->getLL('f1_t2').
		    '</td></tr>';

		    # limit
		$content .= '<tr><td><b>'.$LANG->getLL('f1_t3').'</b>: </td>'.
		    '<td><input type="text" name="limit" value="'.t3lib_div::_GP('limit').'" size="2" maxlength="4"> (default 30)</td></tr>';

		    # convert UTF-8 to ISO-1 ?
		if (t3lib_div::_GP('utf8toiso1') == 'utf8toiso1') {
		    $checked_utf8toiso1 = 'checked';
		}
		$content .= '<tr><td><b>'.$LANG->getLL('f1_t7').'</b>: </td>'.
		    '<td><input type="checkbox" name="utf8toiso1" value="utf8toiso1" '.$checked_utf8toiso1.'> ('.$LANG->getLL('f1_t8').')</td></tr>';


		    # raw-export: 
		    # - don't show headline with field names
		    # - export all records no matter if data format correlates or not
		if (t3lib_div::_GP('rawExport') == 'rawExport') {
		    $checked_rawExport = 'checked';
		}
		$content .= '<tr><td><b>'.$LANG->getLL('f1_t9').'</b>: </td>'.
		    '<td><input type="checkbox" name="rawExport" value="rawExport" '.$checked_rawExport.'> ('.$LANG->getLL('f1_t8').')</td></tr>';

		    # show downloaded records 
		if (t3lib_div::_GP('showDownloaded') == 'showDownloaded') {
		    $checked_showDownloaded = 'checked';
		}
		$content .= '<tr><td><b>'.$LANG->getLL('f1_t10').'</b>: </td>'.
		    '<td><input type="checkbox" name="showDownloaded" value="showDownloaded" '.$checked_showDownloaded.'></td></tr>';


		    # submit button
		$content .= '<tr><td colspan=2 align="right"><input type="submit" value="'.$LANG->getLL('reload').'" onclick="document.forms[0].deleteall.value=\'\';document.forms[0].exportall.value=\'\';"></td></tr>';

		    # end form
		$content .= '</form>';


		    # select the records
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		if ($res) {
		
		    
		    $content .= '<table width="100%">'.
			'<tr>'.
			'<td colspan="3" align="center">'.$this->BackLink.' '.$this->pageInfo.' '.$this->NextLink.'</td>'.
			'</tr>'.
			'<tr>'.
			'<td>PID</td>'.
			'<td>'.$LANG->getLL('values').'</td>'.
			'<td>'.$LANG->getLL('date').'</td>'.
			'<td>&nbsp;</td>'.
			'</tr>';
			
		    $count = 0;
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$count++;

			$class =  'class="bgColor5"';
			if ($count%2==0) {
				$class = 'class="bgColor4"';
			}

			    # be compatible to early version where fields were saved in DB seperated by ;
			if (strpos($row['submittedfields'], ';~')) {
			    $row['submittedfields'] = str_replace(",~", ";", str_replace(";", ",",$row['submittedfields']));
			}

		    
			$content .= '<tr>'.
			    '<td '.$class.'>'.$row['pid'].'</td>'.
			    '<td '.$class.'>'.nl2br($row['submittedfields']).'</td>'.
			    '<td '.$class.'>'.$row['date'].'</td>'.
			    '<td '.$class.'><a href="#" onclick="if (confirm(\''.$LANG->getLL('confirm_delete_one').'\') == true) set_deleteone(\''.$row['uid'].'\');">'.'delete</a></td>'.
			    '</tr>';
		    
		    }

		    $content .= '<tr>'.
			'<td colspan="3" align="center">'.$this->BackLink.' '.$this->pageInfo.' '.$this->NextLink.'</td>'.
			'</tr>';
		    $content .= '</table>';
		
		} else {
		    $content .= $LANG->getLL('no_rec_found');
		}

		$content .= $LANG->getLL('number_found_rec').': '.$this->totalCount.'<br>';
		
		    # export as CSV
		if ($this->totalCount > 0) {
		    $content .= $LANG->getLL('all').' '.$this->totalCount.' '.$LANG->getLL('records').' <b><a href="#" onclick="set_exportall();">'.$LANG->getLL('export').'</a></b> '.$LANG->getLL('or').' <b><a href="#" onclick="if (confirm(\''.str_replace('#1#', $this->totalCount, $LANG->getLL('confirm_delete')).'\') == true) set_deleteall();">'.$LANG->getLL('delete').'</a></b><br><br>';
		    $content .= '<b>'.$LANG->getLL('attention').'</b>:<ul>';
		    $content .= '<li>'.$LANG->getLL('f1_t4').'</li>';
		    $content .= '<li>'.$LANG->getLL('f1_t5').'</li>';
		    $content .= '<li>'.$LANG->getLL('f1_t6').'</li>';
		    $content .= '</ul>';
		}



		return $content;	
	}


	    ####################################
	    # exports all data records as CSV file
	    ####################################
	function export($query_export,$query_update) {

		global $LANG;


		    # which definitions should be exported
		if (t3lib_div::_GP('export_this')) {
    		    $export_this = t3lib_div::_GP('export_this');
#		    print $query_update;
#		    print_r($export_this);
#		    print_r($csv[$export_this[0]]);
		}


		    # select the records
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_export);
		if ($res) {
		
		    $definitions = array();
		    $csv = array();
		    $def = '';
		    $count = array();
		    $exportedUIDs = array();
		    $usedDefinitions = array();
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			    # split by first \n
			$definition = substr($row['submittedfields'], 0, strpos($row['submittedfields'], "\n"));

			    # only check definition if rawExport is not checked
			if (!t3lib_div::_GP('rawExport') || t3lib_div::_GP('rawExport') != 'rawExport') {
			    if ($definition != $def && (is_array($export_this) && in_array($definition, $export_this))) {
				    # only add definition if it's never been added
				if (!$csv[$definition]) {
	    			    $csv[$definition] .= $definition."\n";
				}
				$def = $definition;
				if (!$count[$definition]) {
				    $count[$definition] = 1;
				} else{
				    $count[$definition]++;
				}
			    } else {
				$count[$definition]++;
			    }
			} # end if rawExport

			if (t3lib_div::_GP('rawExport') == 'rawExport' || !is_array($export_this) || (is_array($export_this) && in_array($definition, $export_this))) {
			    $data = substr($row['submittedfields'], strpos($row['submittedfields'], "\n"), strlen($row['submittedfields']));
			    $data = str_replace(array("\r","\n"), ' ', trim($data))."\n";
			    $data = str_replace("\'", "'", str_replace('\"', '"', $data));
				# be compatible to early version where fields were saved in DB seperated by ;
			    if (false === strpos($data, ';~')) {
				$csv[$definition] .= $data;
			    } else {
				$csv[$definition] .= str_replace(",~", ";", str_replace(";", ",",$data));
			    }
				# this record should be marked as "downloaded" 
			    $exportedUIDs[] = $row['uid'];
#			    print $row['uid']." ";
			} # end if rawExport OR in export_this

			
			if (!in_array($definition, $definitions)) {
			    $definitions[] = $definition;
			}
			
		    }
		}


	        # rawExport: export all - no need to check different structures
	    if (t3lib_div::_GP('rawExport') == 'rawExport') {
	        $export_this = $definitions;
	    }


	    if (!is_array($definitions) || sizeof($definitions) == 0) {
		$content .= $LANG->getLL('exp_t1')."<br>";
	    } elseif ((sizeof($definitions) == 1) || (sizeof($export_this) > 0) || t3lib_div::_GP('rawExport') == 'rawExport') {
	    
		if (!is_array($export_this) || sizeof($export_this) == 0) {
		    $export_this[] = $definitions[0];
		}
		

		    # mark exported records as "downloaded"
		$query_update = $query_update.' uid in ('.implode(',', $exportedUIDs).')';
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query_update);

		header("Content-type: application/download");

		header('Pragma: public'); 
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: pre-check=0, post-check=0, max-age=0'); 
		header('Content-Transfer-Encoding: none');

		    //This should work for IE & Opera 
		header('Content-Type: application/csv; name="mailform"'); 
		    //This should work for the rest 
		header('Content-Type: application/csv; name="mailform"'); 
		header('Content-Disposition: inline; filename="mailform"');

		foreach($export_this as $exp) {
			# decode utf8 to iso-1 if checked
		    if (t3lib_div::_GP('utf8toiso1') == 'utf8toiso1') {
			$csv[$exp] = utf8_decode($csv[$exp]);
		    }
		    print $csv[$exp];
		}

		exit;
	    } else {
		$content .= str_replace('#1#', sizeof($definitions), $LANG->getLL('exp_t2')).'<br>';
		$content .= $LANG->getLL('exp_t3').'<br>';
		$content .= '<table border=0>';
		foreach($definitions as $def) {
		    $content .= '<tr><td align="right">('.$count[$def].')</td><td><input type="checkbox" name="export_this[]" value="'.$def.'"></td>';
		    $content .= '<td>'.$def.'</td></tr>';
		}
		$content .= '</table>';
		$content .= $LANG->getLL('exp_t4').' <input type="button" value="'.$LANG->getLL('download').'" onclick="set_exportall();">';
	    } 
	
	    return $content;
	}


	##########################
	# function 2: fields
	# shows overview of the used fields
	##########################
	function show_function2(&$query) {

		global $TCA, $LANG;

		if (!t3lib_div::_GP('id')) {
		    $content .= $LANG->getLL('select_page').'<br>';
		} else {
		
		    $content .= $this->show_function1($query, t3lib_div::_GP('id'));
		    
		}

		return $content;
	}


}





if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/th_mailformplus/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/th_mailformplus/mod1/index.php"]);
}




// Make instance:
$SOBE = t3lib_div::makeInstance("tx_thmailformplus_module1");
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>