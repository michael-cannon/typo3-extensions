<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Tony McCallie (TonyMcCallie@tfchurch.org)
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
 * Plugin 'TFC Lifegroup Search' for the 'tfc_lifegroups' extension.
 *
 * @author	Tony McCallie <TonyMcCallie@tfchurch.org>
 */



class ux_tx_tfclifegroups_pi1 extends tx_tfclifegroups_pi1 {
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		#t3lib_div::debug($conf);
		$GLOBALS['TSFE']->set_no_cache();
			// enable this line when working on the plugin
		//$GLOBALS['TYPO3_DB']-> debugOutput = TRUE;
		
		$this->pi_initPIflexForm();
		$this->lConf = array();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			foreach ( $data as $lang => $value ) {
				foreach ( $value as $key => $val ) {
					$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}
		
		$this->templateFileContent = $this->cObj->cObjGetSingle($this->conf['altTemplateFile'],$this->conf['altTemplateFile.']);
		
		if (strstr($this->cObj->currentRecord,"tt_content"))	{
			$conf["pidList"] = $this->cObj->data["pages"];
			$conf["recursive"] = $this->cObj->data["recursive"];
			if($conf['pidListSubrecords'])
				$conf["pidListAll"]=$conf["pidListSubrecords"];
			elseif($conf["pidList"])
				$conf["pidListAll"]= $this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']);
		}
		
		switch($this->lConf['view_mode']){
			case 1:
				if(!isset($this->piVars['selCategory'])) {
					return $this->pi_wrapInBaseClass($this->messageView($content,$conf));
				} else {
					return $this->pi_wrapInBaseClass($this->listView($content,$conf));
				}
			break;
			default:
				return $this->pi_wrapInBaseClass($this->searchView($content,$conf));
			break;
		}
	}
	
	function searchView($content,$conf) {
		if($this->templateFileContent){
			$dataRowContent = $this->cObj->getSubpart($this->templateFileContent,'###SEARCH###');
			
			$subStrArray = Array();
			$subStrArray['###ACTION###'] = $GLOBALS['TSFE']->baseUrl.$this->pi_getPageLink($this->lConf['action']);
			$subStrArray['###CATEGORY###'] = $this->getCategories($conf);
			$subStrArray['###DAY###'] = $this->getDays($conf);
			$subStrArray['###AGES###'] = $this->getAges($conf);
			$subStrArray['###SEMESTER###'] = $this->getSemester($conf);
			$subStrArray['###INTEREST###'] = $this->getInterests($conf);
			$subStrArray['###SWORD###'] = $this->piVars['sword'];
						
			return $this->cObj->substituteMarkerArrayCached($dataRowContent,$subStrArray);
		} else {
			return "Template File Not Found";
		}
	}
	
	
	/**
	 * [Put your description here]
	 */
	function listView($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		
		$this->arRecurrences = $this->setRecurrences();
		$this->arDays = $this->setDays();
		$this->arInterests = $this->setInterests();
		$this->arAges = $this->setAges();
		
		$lConf = $this->conf['listView.'];	// Local settings for the listView function
	
		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

			// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,$this->perPage);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,15);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='title,leader1_firstname,leader1_lastname,leader1_phone,leader1_email,leader2_firstname,leader2_lastname,leader2_phone,leader2_email,location,descr';
		$this->internal['orderByList']='title,leader1_firstname,leader1_lastname';


		if((isset($this->piVars["selCategory"]))&&($this->piVars["selCategory"]!="NULL")) {
			$strWhere.=' AND category = '.$this->piVars["selCategory"];
		}
		
		if((isset($this->piVars["selDay"]))&&($this->piVars["selDay"]!="NULL")) {
			// $strWhere.=' AND '.$this->piVars["selDay"].' IN (day)';
			$strWhere.=' AND FIND_IN_SET('.$this->piVars["selDay"].' , day)';
		}

		if((isset($this->piVars["selInterest"]))&&($this->piVars["selInterest"]!="NULL")) {
			// $strWhere.=' AND '.$this->piVars["selInterest"].' IN (interests)';
			$strWhere.=' AND FIND_IN_SET('.$this->piVars["selInterest"].' , interests)';
		}

		if((isset($this->piVars["selSemester"]))&&($this->piVars["selSemester"]!="NULL")) {
			// $strWhere.=' AND '.$this->piVars["selInterest"].' IN (interests)';
			$strWhere.=' AND FIND_IN_SET('.$this->piVars["selSemester"].' , semesters)';
		}
		elseif(isset($this->piVars["selSemester"]))
			$strWhere.='';
                elseif ($this->lConf['semester'])
		// $strWhere = " AND `semesters` LIKE '%".$this->lConf['semester']."%'";
			$strWhere.=' AND FIND_IN_SET('.$this->lConf['semester'].' , semesters)';

		if((isset($this->piVars["selAge"]))&&($this->piVars["selAge"]!="NULL")) {
			// $strWhere.=' AND '.$this->piVars["selAge"].' IN (ages)';
			$strWhere.=' AND FIND_IN_SET('.$this->piVars["selAge"].' , ages)';
		}

		if (isset($this->piVars['uid']) && 0 < $this->piVars['uid']) {
			$uid = $this->piVars['uid'];
			$strWhere = ' AND uid = ' . $uid;
		}

			// Get number of records:
		$res = $this->pi_exec_query('tx_tfclifegroups_lifegroups',1,$strWhere);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			// Make listing query, pass query to SQL database:
		$query = $this->pi_list_query('tx_tfclifegroups_lifegroups',0,$strWhere,'','', $this->internal['orderByList']);
		echo '<!--';
		echo $query;
		echo '-->';
		$res = $this->pi_exec_query('tx_tfclifegroups_lifegroups',0,$strWhere,'','', $this->internal['orderByList']);
		$this->internal['currentTable'] = 'tx_tfclifegroups_lifegroups';

		
		//Set number of records for Grouping
		if($this->internal["results_at_a_time"]>$this->perPage = $this->internal["res_count"]){
			$this->perPage = $this->internal["res_count"];
		} else {
			if((($this->piVars["pointer"]+1)*$this->internal["results_at_a_time"])>$this->internal["res_count"]){
				$this->perPage = $this->internal["res_count"] - ($this->piVars["pointer"]*$this->internal["results_at_a_time"]);
			} else {
				$this->perPage = $this->internal["results_at_a_time"];
			}
		}
		
		//$this->columns = 2; // this is set when plugin is initialized - top of file.
		//$this->perCol = 5; // this is set when plugin is initialized - top of file.
		if(($this->internal["res_count"]<=$this->perPage)&&($this->internal["res_count"]>$this->perCol)){
			$this->perCol = round($this->internal["res_count"]/2);
		}

		#debug(array($this->pi_list_query('tx_tfclifegroups_lifegroups')));

			// Put the whole list together:
		$fullTable='';	// Clear var;
	#	$fullTable.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!

			// Adds the mode selector.
		#$fullTable.=$this->pi_list_modeSelector($items);

			// Adds the whole list table
		#$fullTable.=$this->pi_list_makelist($res);
		$fullTable.=$this->pi_list_makelist($res, ' class="xds_smallgroup_base_table" cellpadding="0" cellspacing="0"');
			// Adds the search box:
		#$fullTable.=$this->pi_list_searchBox();

			// Adds the result browser:
		#$fullTable.=$this->pi_list_browseresults();
		
		
		
		
			// CUSTOM LIST BROWSER
			$strBrowse = $this->pi_list_browseresults();
			$arBrowse = explode("</span>", $strBrowse);
			$this->strHead = $arBrowse[0].$arBrowse[1];
			$this->strFoot = $arBrowse[2];
			#t3lib_div::debug($this->strHead, '$this->strHead');
			#t3lib_div::debug($this->strFoot, '$this->strFoot');
			$this->strFoot = str_replace('</p><table><tr><td', '<table class="tfc_lifegroups_browser"><tr><td', $this->strFoot);
			$this->strFoot = str_replace('</td></tr></table></div>', '</td></tr></table>', $this->strFoot);
			#t3lib_div::debug($this->strFoot, '$this->strFoot (cleaned)');
		$fullTable = $this->strFoot . $fullTable;
		
		

			// Returns the content from the plugin.
		return $fullTable;
	}
	
	/**
	 * [Put your description here]
	 */
	function pi_list_row($c)	{
		$htmlParse = "";		
		if($this->templateFileContent){
			if($this->curRow==0){
				$htmlParse.='<table width="310" class="details">';
				//$htmlParse.='<table cellpadding="0">';
			}
			
			$this->curCol++;
			$this->curRow++;
			$this->curRec++;
		
			$dataRowContent = $this->cObj->getSubpart($this->templateFileContent,'###RESULTS###');
			$subStrArray = Array();
			$subStrArray['###NAME###'] = $this->getFieldContent("title");
			$subStrArray['###LEADER_PHONE###'] = $this->getFieldContent("leader_phone");
			// MLC 20100920 add email
			$subStrArray['###LEADER_EMAIL###'] = $this->getFieldContent("leader_email");
			$subStrArray['###AGES###'] = $this->getFieldContent("ages");
			$subStrArray['###INTERESTS###'] = $this->getFieldContent("interests");
			$subStrArray['###ADDRESS###'] = $this->getFieldContent("location");
			$subStrArray['###DAY###'] = $this->getFieldContent("day");
			$subStrArray['###TIME###'] = $this->getFieldContent("time");
			$subStrArray['###RECUR###'] = $this->getFieldContent("recurrence");
			$subStrArray['###URL###'] = $this->getFieldContent("url");
			$subStrArray['###DESC###'] = $this->getFieldContent("descr");
				
			$htmlParse.= $this->cObj->substituteMarkerArrayCached($dataRowContent,$subStrArray);
			
			if(($this->curRow==$this->perCol)&&($this->curRec!=$this->perPage)){			
				//$htmlParse.='</table></td><td valign=top><table width="340">'; // orig
				$htmlParse.='</table></td><td valign=top>';
				//$htmlParse.='</table></td><td valign="top"><table cellpadding="0">';
				$this->curRow=0;
			}
			
			if($this->curRec==$this->perPage) {
				//FOOTER
				$htmlParse.="</table>";
				$dataFootContent = $this->cObj->getSubpart($this->templateFileContent,'###RESULTS_TAIL###');
				$subStrArray['###COLSPAN###'] = $this->columns;
				$subStrArray['###FOOTER###'] = $this->strFoot;
				#$subStrArray['###FOOTER###'] = $this->pi_list_browseresults();
				$htmlParse.= $this->cObj->substituteMarkerArrayCached($dataFootContent,$subStrArray);	
			}
			
			return $htmlParse;
		} else {
			return 'Template not found';
		}
	}
	
	/**
	 * [Put your description here]
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case 'leader_phone':
				$strHtml = $this->internal['currentRow']['leader1_firstname'].' '.$this->internal['currentRow']['leader1_lastname'].' '.$this->internal['currentRow']['leader1_phone'];
				if($this->internal['currentRow']['leader2_lastname']!="") {
					$strHtml.='<br /> '.$this->internal['currentRow']['leader2_firstname'].' '.$this->internal['currentRow']['leader2_lastname'];
				}
				return $strHtml;
			break;
			// MLC 20100920 add email
			case 'leader_email':
				$strHtml = $this->internal['currentRow']['leader1_email'];
				if($this->internal['currentRow']['leader2_email']!="") {
					$strHtml.='<br /> '.$this->internal['currentRow']['leader2_email'];
				}
				return $strHtml;
			break;
			case 'ages':
				$arAges = explode(',',$this->internal['currentRow'][$fN]);
				while(list($k,$v) = each($arAges)) {
					$arAges[$k] = $this->arAges[$v];
				}
				$strHtml = implode(', ',$arAges);
				return $strHtml;
			break;
			case 'interests':
				$arInterests = explode(',',$this->internal['currentRow'][$fN]);
				while(list($k,$v) = each($arInterests)) {
					$arInterests[$k] = $this->arInterests[$v];
				}
				$strHtml = implode(', ',$arInterests);
				return $strHtml;
			break;
			case 'semesters':
				$arSemesters = explode(',',$this->internal['currentRow'][$fN]);
				while(list($k,$v) = each($arSemesters)) {
					$arSemesters[$k] = $this->arSemesters[$v];
				}
				$strHtml = implode(', ',$arSemesters);
				return $strHtml;
			break;
			case 'day':
				$arDays = explode(',',$this->internal['currentRow'][$fN]);
				while(list($k,$v) = each($arDays)) {
					$arDays[$k] = $this->arDays[$v];
				}
				$strHtml = implode(', ',$arDays);
				return $strHtml;
			break;
			case 'recurrence':
				return $this->arRecurrences[$this->internal['currentRow'][$fN]];
			break;
			case 'time':
				return date('h:i a',$this->internal['currentRow'][$fN]+21600);
			break;
			//case "title":
					// This will wrap the title in a link.
			//	return $this->pi_list_linkSingle($this->internal['currentRow']['title'],$this->internal['currentRow']['uid'],1);
			//break;
			case "url":
				// This will wrap the title in a link.
				$url = trim( $this->internal['currentRow']['url'] );
				if ( ! $url )
					return $url;
				elseif ( is_numeric($url) ) {
					// internal link
					$string = '<a href="';
					$string .= ( preg_match( '#^http#', $url ) )
						? $url
						: 'http://' . $url;
					$string .= '" target="_blank">' . $url . '</a>';

					// return $string;
					return $this->pi_linkToPage('website', $url);
				} else {
					// external link
					$string = '<a href="';
					$string .= ( preg_match( '#^http#', $url ) )
						? $url
						: 'http://' . $url;
					$string .= '" target="_blank">' . $url . '</a>';

					return $string;
				}
			break;
			default:
				return $this->internal['currentRow'][$fN];
			break;
		}
	}
	
	function getCategories($conf) {
		
		if($conf['pidListSubrecords.']['category'])
			$pidList =$conf['pidListSubrecords.']['category'];
		elseif($conf["pidListAll"]) {
			$pidList =' AND pid IN ('.$conf["pidListAll"].')';
		}
		/*
		if (strstr($this->cObj->currentRecord,"tt_content"))	{
			$conf["pidList"] = $this->cObj->data["pages"];
			$conf["recursive"] = $this->cObj->data["recursive"];
		}
		*/
		$html = '<select name="tx_tfclifegroups_pi1[selCategory]" style="width: 200px">
					<option value="NULL">'.$this->pi_getLL('all_categories').'</option>';
		/*
		 * exec_SELECTquery(
		 * 	fields,
		 * 	table,
		 * 	WHERE,
		 * 	GROUP BY,
		 * 	ORDER BY,
		 * 	LIMIT) 
		 */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, uid',
			'tx_tfclifegroups_categories',
			'1'.$this->cObj->enableFields("tx_tfclifegroups_categories").$pidList,
			'',
			'title');
		
		$sel = 'SELECTED';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($this->piVars["selCategory"]==$row['uid']){
				$sel = 'SELECTED';
			} else {
				$sel = '';
			}
			$html.='<option value="'.$row['uid'].'" '.$sel.' >'.$row['title'].'</option>';
		}	
		$html.='</select>';
		return $html;	
	}
	
	function getSemester($conf) {
		
		if($conf['pidListSubrecords.']['semesters'])
			$pidList =$conf['pidListSubrecords.']['semesters'];
		elseif($conf["pidListAll"]) {
			$pidList =' AND pid IN ('.$conf["pidListAll"].')';
		}
		
		/*
		if (strstr($this->cObj->currentRecord,"tt_content"))	{
			$conf["pidList"] = $this->cObj->data["pages"];
			$conf["recursive"] = $this->cObj->data["recursive"];
		}
		*/
		$html = '<select name="tx_tfclifegroups_pi1[selSemester]" style="width: 200px">
					<option value="NULL">'.$this->pi_getLL('all_semesters').'</option>';
		/*
		 * exec_SELECTquery(
		 * 	fields,
		 * 	table,
		 * 	WHERE,
		 * 	GROUP BY,
		 * 	ORDER BY,
		 * 	LIMIT) 
		 */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, uid',
			'tx_tfclifegroups_semesters',
			'1'.$this->cObj->enableFields("tx_tfclifegroups_semesters").$pidList,
			'',
			'title');		
		$sel = 'SELECTED';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($this->piVars["selCategory"]==$row['uid']){
				$sel = 'SELECTED';
			} else {
				$sel = '';
			}
			$html.='<option value="'.$row['uid'].'" '.$sel.' >'.$row['title'].'</option>';
		}	
		$html.='</select>';
		return $html;	
	}
	
	function getDays($conf) {
				
		if($conf['pidListSubrecords.']['day'])
			$pidList =$conf['pidListSubrecords.']['day'];
		elseif($conf["pidListAll"]) {
			$pidList =' AND pid IN ('.$conf["pidListAll"].')';
		}
		
		$html = '<select name="tx_tfclifegroups_pi1[selDay]" style="width: 200px">
					<option value="NULL">'.$this->pi_getLL('all_days').'</option>';
		/*
		 * exec_SELECTquery(
		 * 	fields,
		 * 	table,
		 * 	WHERE,
		 * 	GROUP BY,
		 * 	ORDER BY,
		 * 	LIMIT) 
		 */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, uid',
			'tx_tfclifegroups_days',
			'1'.$this->cObj->enableFields("tx_tfclifegroups_days").$pidList,
			'',
			'uid');
		
		$sel = 'SELECTED';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($this->piVars["selDay"]==$row['uid']){
				$sel = 'SELECTED';
			} else {
				$sel = '';
			}
			$html.='<option value="'.$row['uid'].'" '.$sel.' >'.$row['title'].'</option>';
		}	
		$html.='</select>';
		return $html;	
	}
		
	function getInterests($conf) {	
		
		if($conf['pidListSubrecords.']['interests'])
			$pidList =$conf['pidListSubrecords.']['interests'];
		elseif($conf["pidListAll"]) {
			$pidList =' AND pid IN ('.$conf["pidListAll"].')';
		}
		
		$html = '<select name="tx_tfclifegroups_pi1[selInterest]" style="width: 200px">
					<option value="NULL">'.$this->pi_getLL('all_interests').'</option>';
		/*
		 * exec_SELECTquery(
		 * 	fields,
		 * 	table,
		 * 	WHERE,
		 * 	GROUP BY,
		 * 	ORDER BY,
		 * 	LIMIT) 
		 */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, uid',
			'tx_tfclifegroups_interests',
			'1'.$this->cObj->enableFields("tx_tfclifegroups_interests").$pidList,
			'',
			'title');
		
		$sel = 'SELECTED';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($this->piVars["selInterest"]==$row['uid']){
				$sel = 'SELECTED';
			} else {
				$sel = '';
			}
			$html.='<option value="'.$row['uid'].'" '.$sel.' >'.$row['title'].'</option>';
		}	
		$html.='</select>';
		return $html;	
	}
		
	function getAges($conf) {
		
		if($conf['pidListSubrecords.']['ages'])
			$pidList =$conf['pidListSubrecords.']['ages'];
		elseif($conf["pidListAll"]) {
			$pidList =' AND pid IN ('.$conf["pidListAll"].')';
		}
		
		$html = '<select name="tx_tfclifegroups_pi1[selAge]" style="width: 200px">
					<option value="NULL">'.$this->pi_getLL('all_ages').'</option>';
		/*
		 * exec_SELECTquery(
		 * 	fields,
		 * 	table,
		 * 	WHERE,
		 * 	GROUP BY,
		 * 	ORDER BY,
		 * 	LIMIT) 
		 */
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, uid',
			'tx_tfclifegroups_ages',
			'1'.$this->cObj->enableFields("tx_tfclifegroups_ages").$pidList,
			'',
			'uid');
		
		$sel = 'SELECTED';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($this->piVars["selAge"]==$row['uid']){
				$sel = 'SELECTED';
			} else {
				$sel = '';
			}
			$html.='<option value="'.$row['uid'].'" '.$sel.' >'.$row['title'].'</option>';
		}	
		$html.='</select>';
		return $html;	
	}
	
}
?>