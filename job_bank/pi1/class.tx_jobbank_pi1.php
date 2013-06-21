<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Ritesh Gurung (ritesh@srijan.in)
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
 * Plugin 'Job Bank for Sponsors' for the 'job_bank' extension.
 *
 * @author	Ritesh Gurung <ritesh@srijan.in>
 */

/*
*  38: class tx_jobbank_pi1
*
*              SECTION: Query execution
*  167:     function main($content,$conf)
*/
$GLOBALS["TSFE"]->set_no_cache();
require_once(PATH_tslib.'class.tslib_content.php');

require_once(PATH_tslib."class.tslib_pibase.php");

class tx_jobbank_pi1 extends tslib_pibase {
	var $prefixId = "tx_jobbank_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_jobbank_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "job_bank";	// The extension key.
	var $tableName = "tx_jobbank_list";	// The table Name.
	var $tableNameCareer = "tx_jobbank_career";	// The table Name.
	var $tableNameQualification = "tx_jobbank_qualification";	// The table Name.
	var $tableNameStatus = "tx_jobbank_status";	// The table Name.
	var $tableNameIndustry = "tx_t3consultancies_cat";	// The table Name.
	var $tableNameSponsor = "tx_t3consultancies";	// The table Name.
	var $tableNameCountry = "static_countries";// The table Name.
	var $tableNameCountryZone = "static_country_zones";	// The table Name.
	var $job_careerlevel;
	var $job_qualifiacation;
	var $job_status;
	var $job_industry;
	var $RTEObj;
	var $backPath;

	var $arrFormatDt=array(
				0=>"dd/mm/yyyy",
				1=>"mm/dd/yyyy",
				2=>"yyyy/dd/mm");
	var $arrConvFormatDt=array(
				0=>"d/m/Y",
				1=>"m/d/Y",
				2=>"Y/d/m");
	
	var $arrayValPlacing=array(
			0=>"1,0,2",
			1=>"0,1,2",
			2=>"2,1,0");
	
	var $arrayValPlacingJs=array(
			0=>"2,1,0",
			1=>"2,0,1",
			2=>"0,2,1");
	
	var $displayFieldsJob;
	
	var $sponsorId;
	
	
	
	function init() {
		
		/*
		*    Initialising the variables for Paging in the Job Bank
		*/
		
		if($this->getData("pagingRecordsPerPage")!='')
		{
			$this->internal['results_at_a_time'] = $this->getData("pagingRecordsPerPage");
		}
		else
		{
			$this->internal['results_at_a_time'] = 2;
		}
		// Settings for paging starts here
		
		$this->internal['maxPages'] = 10;
		$this->internal['dontLinkActivePage'] = false;
		$this->internal['pagefloat'] = 'center';
		// Settings for paging ends here

	}
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();    // Initialize class variables
		//Getting the template fle

		$this->templateCode = $this->cObj->fileResource($this->conf["templateFile"]);
		
		$this->displayFieldsJob=$this->getData("jobListingFields");

		$this->job_careerlevel='';
		$this->job_qualifiacation='';
		$this->job_status='';
		$this->job_industry='';
//		$this->sponsorId=t3lib_div::_GP('sponsor_id');	
		$this->sponsorId=$this->piVars['sponsor_id'];	
		//Getting page Id
		$page_id = $GLOBALS["TSFE"]->id;
		
		//Initializing the variables
		$template = array();
		$markerArray = array();
		$typolink_conf=array(
		"parameter" => $page_id,
		"additionalParams" => "&$this->prefixId[action]=add&$this->prefixId[sponsor_id]=".$this->sponsorId,
		"useCacheHash" => 1);
		
		//Finding the date format
		$key=array_search($this->getData('dateFormatCalendar'),$this->arrFormatDt);
		
		$positioningReplace=$this->arrayValPlacingJs[$key];
		
		//Initialising the Data for Career 
		$resCareerLevel=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,career_name', $this->tableNameCareer,'1=1 '.$this->cObj->enableFields($this->tableNameCareer));
		
		//Intialising the data for Qualification
		$resCareerQualification=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,qualification', $this->tableNameQualification,'1=1 '.$this->cObj->enableFields($this->tableNameQualification));
		
		//Initialiasing the data for Status
		$resCareerStatus=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,status_name', $this->tableNameStatus,'1=1 '.$this->cObj->enableFields($this->tableNameStatus));
		
		//Initialising the data for Industry
		$resJobIndustry=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', $this->tableNameIndustry,'tx_jobbank_status = 0 '.$this->cObj->enableFields($this->tableNameIndustry));
		
		$resJobSponsor=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', $this->tableNameSponsor,'uid ='.$this->sponsorId . ' '.$this->cObj->enableFields($this->tableNameSponsor));

		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerLevel))
		{
			$this->job_careerlevel.=$row[uid]."|".$row[career_name]."||";
		}
		
		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerQualification))
		{
			$this->job_qualifiacation.=$row[uid]."|".$row[qualification]."||";
		}

		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerStatus))
		{
			$this->job_status.=$row[uid]."|".$row[status_name]."||";
		}
		
		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resJobIndustry))
		{
			$this->job_industry.=$row[uid]."|".$row[title]."||";
		}
		$rowSponsor=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resJobSponsor);
		$template['job_bank_header'] = $this->cObj->getSubpart($this->templateCode,"###JOBBANK_HEADER###"); 
		$markerArray["###STYLE_TEMPLATE###"] = t3lib_extMgm::siteRelPath($this->extKey).'styles/style.css';
		$markerArray["###STYLE_TEMPLATE_TEST###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/bgmenu.gif';
		$markerArray["###STYLE_TEMPLATE_TEST1###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/bgmenu_hover.gif';
		$markerArray["###SCRIPTNAME###"] = t3lib_extMgm::siteRelPath($this->extKey).'styles/lib.js';
		$markerArray["###SCRIPTNAME_DATEFUNCTIONS###"] = t3lib_extMgm::siteRelPath($this->extKey).'styles/jsDate.js';
		$markerArray["###CHAINEDSCRIPT###"] = t3lib_extMgm::siteRelPath($this->extKey).'styles/chainedselects.js';
		$markerArray["###ADD_LINK###"] =$this->cObj->typolink("Add",$typolink_conf);
		$typolink_conf["additionalParams"]="&$this->prefixId[sponsor_id]=".$this->sponsorId;
		$this->backPath = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->cObj->typoLink_URL($typolink_conf);
		//$markerArray["###LISTINGS_LINK###"] =$this->cObj->typolink("Listings",$typolink_conf);
		$markerArray["###LISTINGS_LINK###"] ='';
		$markerArray["###DATEFORMATCALENDARJS###"] =$this->getData('dateFormatCalendar');
		$markerArray["###DATEFORMATCALENDARJS###"] =$this->getData('dateFormatCalendar');
		$markerArray["###SPONSOR_NAME###"] = $rowSponsor['title'];
		$markerArray['###FORMNAMEEXTENSIONJS###']=$this->prefixId;
		$markerArray['###DYANAMIC_JS###']=$this->getCountryZone(1);
		$markerArray["###SPLITFORMAT###"]=$positioningReplace;
		
		

		$content = $this->cObj->substituteMarkerArrayCached($template['job_bank_header'], $markerArray, array(),array());
		
		//Case to show the screen
//		switch(t3lib_div::_GP('action'))
		switch($this->piVars['action'])
		{
			case 'add':$content.=$this->addForm();break;
			case 'edit':$content.=$this->editForm($this->piVars['jobUid']);break;
			case 'Save':$this->saveDatainfo();$content.=$this->showListings();break;
			case 'Update':$this->updateDatainfo();$content.=$this->showListings();break;
			case 'listingSubmit':$content.=$this->deleteDataInfo();break;
			default:$content.=$this->showListings();
		}
			
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Creates a form for addition of Job listing 
	 *
	 * @return	String
	 */

	function addForm()
	{
		$content = '';
		$template['job_bank_add'] = $this->cObj->getSubpart($this->templateCode,"###ADD_JOB_PLACEHOLDER###"); 
		
		$subPartArray['###CAREERLEVEL###']=$this->createDropDown($this->job_careerlevel);
		$subPartArray['###QUALIFICATION###']=$this->createDropDown($this->job_qualifiacation);
		$subPartArray['###STATUS###']=$this->createDropDown($this->job_status);
	//	$subPartArray['###JOB_INDUSTRY###']=$this->createDropDown($this->job_industry);
		
		$subPartArray['###DATEFORMATCALENDAR###']=$this->getData('dateFormatCalendar');
		$subPartArray['###DATEFORMATCALENDARJS###']=$this->getData('dateFormatCalendar');
		
		
		$subPartArray['###SCRIPTNAME_POPUP_HELP_JS###']= t3lib_extMgm::siteRelPath($this->extKey).'styles/overlib.js';
		//$subPartArray['###SCRIPTNAME_POPUP_HELP_CSS###']= t3lib_extMgm::siteRelPath($this->extKey).'styles/tooltip.css';
		
		$subPartArray["###HELP_IMAGE###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/help.gif';
		//$subPartArray['###SCRIPTNAME_POPUP_CALENDER_JS###']= t3lib_extMgm::siteRelPath($this->extKey).'styles/overlib.js';
		
		$subPartArray['###FORMNAMEEXTENSION###']=$this->prefixId;
		$subPartArray['###FORMNAMEEXTENSIONJOB###']=$this->prefixId;
		$subPartArray['###FORMNAMEEXTENSIONJOB1###']=$this->prefixId;
		$subPartArray["###CALENDARIMAGE0###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/cal.gif';
		$subPartArray["###CALENDARIMAGE1###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/cal.gif';
		$subPartArray["###SPONSOR_ID###"] = $this->sponsorId;
		$subPartArray['###JOB_BANK_LOCATION###']=$this->getCountryZone();
		$content .= $this->cObj->substituteMarkerArrayCached($template['job_bank_add'],$subPartArray,array(),array());
		
		return $this->pi_wrapInBaseClass($content);

	}
	
	/**
	 * Creates a form for editing the Job listing
	 *
	 * @param	int		Job Id
	 * @return	String		
	 */

	function editForm($jobUid)
	{
		$flagJobOther=false;
		
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->tableName, 'uid='.$jobUid);
		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		//$resIndustry=$GLOBALS['TYPO3_DB']->exec_SELECTquery('title',$this->tableNameIndustry,'uid in ('.$row['industry'].') and tx_jobbank_status=1');
		/*if($GLOBALS['TYPO3_DB']->sql_num_rows($resIndustry)!='')
		{
			$flagJobOther=true;
			$rowHiddenIndustry=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resIndustry);
		}*/
		$key=array_search($this->getData('dateFormatCalendar'),$this->arrFormatDt);

		$template['job_bank_edit'] = $this->cObj->getSubpart($this->templateCode,"###EDIT_JOB_PLACEHOLDER###"); 
	//	echo $template['job_bank_edit'];
		
		$careerLevel=$this->getData('careerLevel');
		$qualification=$this->getData('qualification');
		$this->job_industry.="oth|Others||";
		
		
		$resCountryZone = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_country_iso_3,uid,zn_name_local',$this->tableNameCountryZone,"uid='".$row['location']."'");
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resCountryZone)!='')
		{
			$row1=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountryZone);
			$_selectedCountry=$row1['zn_country_iso_3'];
			
		}
		
		$subPartArray['###JOB_BANK_LOCATION###']= $this->getCountryZoneEdit($_selectedCountry);
				
		$subPartArray['###CAREERLEVEL###']=$this->createDropDown($this->job_careerlevel,$row['clevel']);
		$subPartArray['###QUALIFICATION###']=$this->createDropDown($this->job_qualifiacation,$row['qualification']);
		$subPartArray['###JOB_INDUSTRY###']=$row['industry'];
		/*if($flagJobOther)
		{
			//$subPartArray['###JOB_INDUSTRY###']=$this->createDropDown($this->job_industry,'oth');
			$subPartArray['###JOB_INDUSTRY###']=$row['industry'];
			$subPartArray['###HIDDENSCRIPT###']='eval("document."+formName+".industry_oth.className=\'job_show\';");';
			$subPartArray['###INDUSTRY_OTH###']=$rowHiddenIndustry['title'];
			$subPartArray['###TX_T3CONSULTANCIES_CAT_UID###']=$row['industry'];
		}
		else 
		{
			//$subPartArray['###JOB_INDUSTRY###']=$this->createDropDown($this->job_industry,$row['industry']);
			$subPartArray['###JOB_INDUSTRY###']=$row['industry'];
			$subPartArray['###INDUSTRY_OTH###']='';
			$subPartArray['###HIDDENSCRIPT###']='';
			$subPartArray['###TX_T3CONSULTANCIES_CAT_UID###']='';
		}*/
		$subPartArray['###SCRIPTNAME_POPUP_HELP_JS###']= t3lib_extMgm::siteRelPath($this->extKey).'styles/overlib.js';
		
		$subPartArray["###HELP_IMAGE###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/help.gif';
		
		
		$subPartArray['###SELECTED_COUNTRY###']=$_selectedCountry;
		$subPartArray['###SELECTED_STATE###']=$row['location'];
		$subPartArray['###STATUS###']=$this->createDropDown($this->job_status,$row['status']);
		$subPartArray['###DATEFORMATCALENDAR###']=$this->getData('dateFormatCalendar');
		$subPartArray['###DATEFORMATCALENDARJS###']=$this->getData('dateFormatCalendar');
		$subPartArray['###FORMNAMEEXTENSION###']=$this->prefixId;
		
		$subPartArray['###FORMNAMEEXTENSIONJOB###']=$this->prefixId;
		$subPartArray['###OCCUPATIONNAME###']=$row['occupation'];
		$subPartArray['###JOB_OVERVIEW###']=$row['joboverview'];
		$subPartArray['###JOB_STARTTIME###']=date($this->arrConvFormatDt[$key],$row['starttime']);
		$subPartArray['###JOB_CLOSETIME###']=date($this->arrConvFormatDt[$key],$row['endtime']);
		$subPartArray['###JOB_UID###']=$jobUid;
		$subPartArray['###FORMNAMEEXTENSIONJOB1###']=$this->prefixId;
		$subPartArray['###SELECTEDCOUNTRYINFO###']=$_selectedCountry;
		$subPartArray['###SELECTEDCOUNTRYINFOSELECTED###']=$_selectedCountry;
		$subPartArray['###SELECTEDCOUNTRYINFO_SUB###']=$row['location'];
		$subPartArray['###JOB_BANK_CITY###']=$row['city'];
		$subPartArray['###ADDITIONAL_REQUIREMENT###']=$row['additional_requirement'];
		$subPartArray['###MAJOR_RESPONSIBILITIES###']=$row['major_responsibilities'];
		$subPartArray['###COMPANY_DESCRIPTION###']=$row['company_description'];
		$subPartArray['###JOB_BANK_CITY###']=$row['city'];
		$subPartArray["###CALENDARIMAGE0###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/cal.gif';
		$subPartArray["###CALENDARIMAGE1###"] = t3lib_extMgm::siteRelPath($this->extKey).'images/cal.gif';
		$subPartArray["###SPONSOR_ID###"] = $this->sponsorId;
		$subPartArray["###BACKPATH###"] = $this->backPath;
		
		if ($row['hidden'] == 1) {
		    $subPartArray["###PAUSE###"] = 'CHECKED';
		}else{
			$subPartArray["###PAUSE###"] = '';
		}
		if ($row['position_filled'] == 1) {
		    $subPartArray["###POSITION_FILLED###"] = 'CHECKED';
		}else{
			$subPartArray["###POSITION_FILLED###"] = '';
		}
		
		$content= $this->cObj->substituteMarkerArrayCached($template['job_bank_edit'],array(),$subPartArray,array());
		return $content;

	}
	
	/**
	 * Deletes a Job from Job listing
	 *
	 * @return	String		
	 */

	function deleteDataInfo()
	{
		$formPost=array();
		$formPost[deleted]=1;
		
		foreach($this->piVars['selectionList'] as $idVal)
		{
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName,'uid='.$idVal,$formPost);
		}
		return $this->showListings();
	}
	
	function showListings()
	{
		$subpartArray=array();
		$content='';
		$template['job_bank_listing'] = $this->cObj->getSubpart($this->templateCode,"###LISTINGS_PLACEHOLDER###"); 
		
		$template['###HEADER_INFO###'] = $this->cObj->getSubpart($template['job_bank_listing'],"###HEADER_INFO###");
		
		$template['DISPLAY_LIST'] = $this->cObj->getSubpart($template['job_bank_listing'],"###DISPLAY_LIST###"); 
		
		$subpartArray['###HEADER_INFO###']=$this->generateHeaders($this->displayFieldsJob);
		
		// echo $GLOBALS['TYPO3_DB']->SELECTquery($this->generateHeaders($this->displayFieldsJob,1).'pid, uid', $this->tableName, 'deleted=0 and sponsor_id='.$this->sponsorId, '', '');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($this->generateHeaders($this->displayFieldsJob,1).'pid, uid', $this->tableName, 'deleted=0 and sponsor_id='.$this->sponsorId, '', '');
		$colDisplayFieldArr=$this->generateHeaders($this->displayFieldsJob,2);
		
		//print_r($colDisplayFieldArr);
		if($res) {
			/*
			* Preparing the data for Paging
			*/
		    $this->internal['res_count'] = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		  
		    $tmp = $this->pi_list_browseresults();
		   // echo $tmp;
			
		}
		
		/*
		* Initialising the varibles for seting the start and limit to show the records.
		*/
		$start = intval( $this->piVars['pointer']) * $this->internal['results_at_a_time'];
		$limit = $this->internal['results_at_a_time'];
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
			$GLOBALS['TYPO3_DB']->sql_data_seek($res, $start);
		}
		$data_row_displ='';
		
		while(($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) && $limit--)
		{
				$data_row_displ.=$this->displayContents($row,$colDisplayFieldArr);		
		}
		
		$subpartArray['###DATARECORDS###']=$data_row_displ;
		$subpartArray['###FORMNAMEEXTENSION###']=$this->prefixId;
		$subpartArray['###SPONSOR_ID###'] = $this->sponsorId;
		$content= $this->cObj->substituteMarkerArrayCached($template['job_bank_listing'],$subpartArray,array(),array());
		return $content.$tmp;
		
	}
	
	function generateHeaders($colomnName,$returnName=0)
	{
		$returnVal="<td><input type='checkbox' name='selectall' onclick='checkall()'></td>";
		$returnColName='';
		$returnColNameArr=array();
		$headerArr=explode(",",$colomnName);
	
		foreach($headerArr as $colName)
		{
			$displayArr=explode("|",$colName);
			$returnColName.=$displayArr[0].",";
			$returnColNameArr[]=$displayArr[0];
			$returnVal.="<td>".$displayArr[1]."</td>";
		}
		switch($returnName)
		{
			case 1:return $returnColName;break;
			case 2: return $returnColNameArr;break;
			default:return $returnVal;break;
		}
		
	}
	
	function displayContents($datarow,$col)
	{
		$key=array_search($this->getData('dateFormatCalendar'),$this->arrFormatDt);
		$dataRec="<tr class='detailinfoinner'><td><input type='checkbox' name='$this->prefixId[selectionList][]' value='$datarow[uid]'></td>";
		$typolinkConfig = array(
								'parameter'=>$GLOBALS["TSFE"]->id);
		$LinkFlag=1;
		foreach ($col as $colName)
		{
			$typolinkConfig['additionalParams']="&$this->prefixId[action]=edit&$this->prefixId[jobUid]=".$datarow['uid']."&$this->prefixId[sponsor_id]=".$this->sponsorId;
			switch($colName)
			{
				case 'location':$dataRec.="<td>".$this->getCountryInfo($datarow[$colName])."</td>";break;
				case 'crdate':
				case 'starttime':
				case 'endtime':
							$dataRec.="<td>".date($this->arrConvFormatDt[$key],$datarow[$colName])."</td>";break;
				default:
					if($LinkFlag)
						$dataRec.="<td>".$this->cObj->typolink($datarow[$colName],$typolinkConfig)."</td>";
					else
						$dataRec.="<td>".$datarow[$colName]."</td>";
			}
			$LinkFlag=0;
			
		}
		 $dataRec.="</tr>";
		/*$subPartArray["###DATARECORDS###"]=$dataRec;
		$content=$this->cObj->substituteMarkerArrayCached($template,array(),$subPartArray,array());*/
		
		return $dataRec;
	}
	
	function getCountryInfo($CountryId)
	{
	
		$returnValue='';
		if($CountryId!='')
		{
			$resCountryZone = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_country_iso_3,uid,zn_name_local',$this->tableNameCountryZone,"uid=".$CountryId);
			if($GLOBALS['TYPO3_DB']->sql_num_rows($resCountryZone)!='')
			{
				$row1=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountryZone);
				$returnValue=$row1['zn_name_local']."[".$row1['zn_country_iso_3']."]";
				
			}
		}
		else 
		{
			$returnValue='<font color=#FF0000>Location not Selected</font>';
		}
		return $returnValue;
	}
	
	function createDropDown($valArr,$selectedOption=''){
		$returnValue='';
		$selectedOptionArr=explode(",",$selectedOption);
		
		$valArrConvert=explode("||",$valArr);
		foreach ($valArrConvert as $valArrVal) {
			if($valArrVal!='')
			{
				
				$valArrValArr=explode("|",$valArrVal);
				if(is_numeric(array_search($valArrValArr[0],$selectedOptionArr)))
					$returnValue.="<option value='$valArrValArr[0]' selected>$valArrValArr[1]</option>";
				else
					$returnValue.="<option value='$valArrValArr[0]'>$valArrValArr[1]</option>";
			}
			
		}
		return $returnValue;
	}
	
	function getData ($keyName,$reverse=FALSE){
		
		$data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		
		return $data[$keyName];
			
	}
	
	function saveDatainfo()
	{
		$formPost=array();
		$selectedIndustry='';
		$postArr=t3lib_div::_POST();
		
		// $this->_debug($postArr);
		foreach($postArr as $nameArr=>$valArr)
		{
			if($nameArr!='action' && $nameArr!='industry_oth' && $nameArr!='zone_location' && $nameArr!='tx_jobbank_pi1')
			{
				if($nameArr!='starttime' && $nameArr!='endtime')
					$formPost[$nameArr]="$valArr";
				else
					$formPost[$nameArr]=$this->date2timestamp($valArr);
			}
		}
		
		//$this->_debug($formPost);
		/*if(is_array(t3lib_div::_POST('industry')))
		{
			foreach(t3lib_div::_POST('industry') as $valIndustry)
				$selectedIndustry.=$valIndustry.",";
			$selectedIndustry=substr($selectedIndustry,0,strlen($selectedIndustry)-1);
		}*/
		
		
		
		//$formPost['industry']=$selectedIndustry;
		$formPost["tstamp"]=time();
		$formPost["crdate"]=time();
		$formPost["pid"]=$this->getData("storagePID");
		/*if($postArr["industry_oth"]!='')
		{
			$formPostIndustry=Array(
				"pid"=>$this->getData('industryPID'),
				"tstamp"=>time(),
				"crdate"=>time(),
				"title"=>$postArr["industry_oth"],
				"tx_jobbank_status"=>1
			);
			$resCheckIndustry = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title',$this->tableNameIndustry,"title='".$postArr["industry_oth"]."' and deleted=0 and pid=".$this->getData('industryPID')); 
			if(!(0<$GLOBALS['TYPO3_DB']->sql_num_rows($resCheckIndustry)))
			{
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableNameIndustry,$formPostIndustry);
				$insertIndustryId=$GLOBALS['TYPO3_DB']->sql_insert_id();
				$formPost["industry"]=$insertIndustryId;
			}
			else 
			{
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCheckIndustry);
				$formPost["industry"]=$row['uid'];
				
			}
		}*/
		
		/* REENA- CODE BEGIN*/
		// Why hidden field is 1 by default
		//$formPost['hidden']=1;
		
		//Make deleted field to 1 if  $formPost['position_filled'] is not empty
		// echo "Save Info <hr>".$GLOBALS['TYPO3_DB']->INSERTquery($this->tableName, $formPost)."<hr>";
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableName, $formPost);
	}

	function updateDatainfo()
	{
		$where='uid='.t3lib_div::_POST('uid');
		$selectedIndustry='';
		$formPost=array();
		$postArr=t3lib_div::_POST();
		foreach($postArr as $nameArr=>$valArr)
		{
			if($nameArr!='action' && $nameArr!='uid' && $nameArr!='industry_oth' && $nameArr!='tx_t3consultancies_cat_uid' && $nameArr!='zone_location' && $nameArr!='tx_jobbank_pi1')
			{
				if($nameArr!='starttime' && $nameArr!='endtime'){
					$formPost[$nameArr]="$valArr";
					
				}else{
					$formPost[$nameArr]=$this->date2timestamp($valArr);
					
				}
				
			}
		}
		//Check if position_filled key is present in an array
		if (!array_key_exists('position_filled', $formPost)) {
		    $formPost['position_filled'] = 0;
		}
		
		//Similarly check for hidden
		if (!array_key_exists('hidden', $formPost)) {
		    $formPost['hidden'] = 0;
		}
		
		//$this->_debug($formPost);
		
		if(is_array(t3lib_div::_POST('industry'))){
			foreach(t3lib_div::_POST('industry') as $valIndustry)
				$selectedIndustry.=$valIndustry.",";
		}
		$selectedIndustry=substr($selectedIndustry,0,strlen($selectedIndustry)-1);
		//$formPost['industry']=$selectedIndustry;
		$formPost["tstamp"]=time();
		$formPost["crdate"]=time();
		$formPost["pid"]=$this->getData("storagePID");
		/*if($postArr["industry_oth"]!='')
		{
			$formPostIndustry=Array(
				"pid"=>$this->getData('industryPID'),
				"tstamp"=>time(),
				"crdate"=>time(),
				"title"=>$postArr["industry_oth"],
				"tx_jobbank_status"=>1
			);
			$resCheckIndustry = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title',$this->tableNameIndustry,"title='".$postArr["industry_oth"]."' and deleted=0 and pid=".$this->getData('industryPID')); 
			if(!(0<$GLOBALS['TYPO3_DB']->sql_num_rows($resCheckIndustry)))
			{
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableNameIndustry,$formPostIndustry);
				$insertIndustryId=$GLOBALS['TYPO3_DB']->sql_insert_id();
				$formPost["industry"]=$insertIndustryId;
			}
			else 
			{
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCheckIndustry);
				$formPost["industry"]=$row['uid'];
				
			}
		}*/
		
//		$this->_debug($GLOBALS['TYPO3_DB']->UPDATEquery($this->tableName, $where,$formPost));
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName, $where,$formPost);
	}

	function date2timestamp($timeStamp)
	{
		
		$arrTimeStamp=explode("/",$timeStamp);
		$key=array_search($this->getData('dateFormatCalendar'),$this->arrFormatDt);
		$positioningArray=explode(",",$this->arrayValPlacing[$key]);
		//var_dump($positioningArray);
		$m=$arrTimeStamp[$positioningArray[0]];
		$d=$arrTimeStamp[$positioningArray[1]];
		$y=$arrTimeStamp[$positioningArray[2]];
		//echo "$m,$d,$y,$timeStamp<br>";
		return mktime(0,0,0,$m,$d,$y);
	}
	
	
	function getCountryZone($zoneFlag=0,$selected='')
	{
		$varReturnVal='';
		if($zoneFlag)
		{
			$resZone = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_country_iso_3,uid,zn_name_local',$this->tableNameCountryZone,"zn_name_local!=''",'','zn_country_iso_3, zn_name_local');
			$oldVal='';
			$ValCountryZone='';
			$ctrZone=0;
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resZone))
			{
					
					if($oldVal!=$row[zn_country_iso_3])
					{
						if($ctrZone==0)
						{
							$ctrZone++;
						}
						else 
						{				
							$varReturnVal=substr($varReturnVal,0,strlen($varReturnVal)-1).")";
						}
						$varReturnVal.="\n\nvar Menu$row[zn_country_iso_3]Menu=new Array(new Array(\"Select a State\",\"\"),";
						$oldVal=$row[zn_country_iso_3];
						$ValCountryZone.="'".$oldVal."',";
					}
					$varReturnVal.="new Array(\"$row[zn_name_local]\", \"$row[uid]\"),";
				
					
			}
			$varReturnVal=substr($varReturnVal,0,strlen($varReturnVal)-1).")";
			$ValCountryZone=substr($ValCountryZone,0,strlen($ValCountryZone)-1);
			$resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3",$this->tableNameCountry,"cn_iso_3 not in (".$ValCountryZone.")");
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountry))
			{
				$varReturnVal.="\n\nvar Menu$row[cn_iso_3]Menu=new Array(new Array(\"Select a State\",\"\"))";
			}
		
			
			
		}
		else
		{
			//echo $GLOBALS['TYPO3_DB']->SELECTquery("cn_iso_3,cn_short_en",$this->tableNameCountry,"cn_short_en!=''",'','cn_short_en');
			$resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3,cn_short_en",$this->tableNameCountry,"cn_short_en!=''",'','cn_short_en');
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountry))
			{
				if($row[cn_iso_3]=='USA')
				{
					$varReturnVal.="<option value=\"$row[cn_iso_3]\" selected>$row[cn_short_en]</option>\n";
				}
				else 
				{
					$varReturnVal.="<option value=\"$row[cn_iso_3]\">$row[cn_short_en]</option>\n";
				}
			}
			
		}
		return $varReturnVal;
	}

	function getCountryZoneEdit($selected='')
	{
		
			
			$resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3,cn_short_en",$this->tableNameCountry,"cn_short_en!=''",'','cn_short_en');
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountry))
			{
				if($row[cn_iso_3]==$selected)
				{
					$varReturnVal.="<option value=\"$row[cn_iso_3]\" selected>$row[cn_short_en]</option>\n";
				}
				else 
				{
					$varReturnVal.="<option value=\"$row[cn_iso_3]\">$row[cn_short_en]</option>\n";
				}
			}
		
		return $varReturnVal;
	}

	function _debug($arr)
	{
		echo "<PRE>";
		var_dump($arr);
		echo "</PRE>";
	}
	
	
	
	
	/***************************
	 *
	 * Functions for listing, browsing, searching etc.
	 *
	 **************************/

	/**
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->piVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
	 * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
	 * Using $this->internal['showFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
	 * Using $this->internal['pagefloat']: this defines were the current page is shown in the list of pages in the Pagebrowser. If this var is an integer it will be interpreted as position in the list of pages. If its value is the keyword "center" the current page will be shown in the middle of the pagelist.
	 * Using $this->internal['showRange']: this var switches the display of the pagelinks from pagenumbers to ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
	 * Using $this->pi_isOnlyFields: this holds a comma-separated list of fieldnames which - if they are among the GETvars - will not disable caching for the page with pagebrowser.
	 *
	 * The third parameter is an array with several wraps for the parts of the pagebrowser. The following elements will be recognized:
	 * disabledLinkWrap, inactiveLinkWrap, activeLinkWrap, browseLinksWrap, showResultsWrap, showResultsNumbersWrap, browseBoxWrap.
	 *
	 * If $wrapArr['showResultsNumbersWrap'] is set, the formatting string is expected to hold template markers (###FROM###, ###TO###, ###OUT_OF###, ###FROM_TO###, ###CURRENT_PAGE###, ###TOTAL_PAGES###)
	 * otherwise the formatting sting is expected to hold sprintf-markers (%s) for from, to, outof (in that sequence)
	 *
	 * @param	integer		determines how the results of the pagerowser will be shown. See description below
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 * @param	array		Array with elements to overwrite the default $wrapper-array.
	 * @param	string		varname for the pointer.
	 * @param	boolean		enable htmlspecialchars() for the pi_getLL function (set this to FALSE if you want f.e use images instead of text for links like 'previous' and 'next').
	 * @return	string		Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
	 *										otherwise wrapping is totally controlled/modified by this array
	 */
	function pi_list_browseresults($showResultCount=1,$tableParams='',$wrapArr=array(), $pointerName = 'pointer', $hscText = TRUE){		

		// example $wrapArr-array how it could be traversed from an extension

		/* $wrapArr = array(

			'browseBoxWrap' => '<div class="browseBoxWrap">|</div>',

			'showResultsWrap' => '<div class="showResultsWrap">|</div>',

			'browseLinksWrap' => '<div class="browseLinksWrap">|</div>',

			'showResultsNumbersWrap' => '<span class="showResultsNumbersWrap">|</span>',

			'disabledLinkWrap' => '<span class="disabledLinkWrap">|</span>',

			'inactiveLinkWrap' => '<span class="inactiveLinkWrap">|</span>',

			'activeLinkWrap' => '<span class="activeLinkWrap">|</span>'

		); */



			// Initializing variables:

		$pointer = intval($this->piVars[$pointerName]);

		$count = intval($this->internal['res_count']);

		$results_at_a_time = t3lib_div::intInRange($this->internal['results_at_a_time'],1,1000);

		$totalPages = ceil($count/$results_at_a_time);

		$maxPages = t3lib_div::intInRange($this->internal['maxPages'],1,100);

		$pi_isOnlyFields = $this->pi_isOnlyFields($this->pi_isOnlyFields);



			// $showResultCount determines how the results of the pagerowser will be shown.

			// If set to 0: only the result-browser will be shown

			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.

			//	 		 2: only the text "Displaying results..." will be shown

		$showResultCount = intval($showResultCount);



			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.

		$showFirstLast = $this->internal['showFirstLast'];



			// if this has a value the "previous" button is always visible (will be forced if "showFirstLast" is set)

		$alwaysPrev = $showFirstLast?1:$this->pi_alwaysPrev;



		if (isset($this->internal['pagefloat'])) {

			if (strtoupper($this->internal['pagefloat']) == 'CENTER') {

				$pagefloat = ceil(($maxPages - 1)/2);

			} else {

				// pagefloat set as integer. 0 = left, value >= $this->internal['maxPages'] = right

				$pagefloat = t3lib_div::intInRange($this->internal['pagefloat'],-1,$maxPages-1);

			}

		} else {

			$pagefloat = -1; // pagefloat disabled

		}



			// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr

		$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';

		$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';

		$wrapper['activeLinkWrap'] = '<td'.$this->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p>|</p></td>';

		$wrapper['browseLinksWrap'] = trim('<table '.$tableParams).'><tr>|</tr></table>';

		$wrapper['showResultsWrap'] = '<p>|</p>';

		$wrapper['browseBoxWrap'] = '

		<!--

			List browsing box:

		-->

		<div '.$this->pi_classParam('browsebox').'>

			|

		</div>';



			// now overwrite all entries in $wrapper which are also in $wrapArr

		$wrapper = array_merge($wrapper,$wrapArr);



		if ($showResultCount != 2) { //show pagebrowser

			if ($pagefloat > -1) {

				$lastPage = min($totalPages,max($pointer+1 + $pagefloat,$maxPages));

				$firstPage = max(0,$lastPage-$maxPages);

			} else {

				$firstPage = 0;

				$lastPage = t3lib_div::intInRange($totalPages,1,$maxPages);

			}

			$links=array();



				// Make browse-table/links:

			if ($showFirstLast) { // Link to first page

				if ($pointer>0)	{

					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),array($pointerName => null),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);

				} else {

					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),$wrapper['disabledLinkWrap']);

				}

			}

			if ($alwaysPrev>=0)	{ // Link to previous page

				if ($pointer>0)	{

					$links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText),array($pointerName => ($pointer-1?$pointer-1:''))).' &nbsp;| </p></td>';

				} elseif ($alwaysPrev)	{

					$links[]='<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText).' &nbsp; | </p></td>';

				}

			}

			for($a=$firstPage;$a<$lastPage;$a++)	{ // Links to pages

				if ($this->internal['showRange']) {

					$pageText = (($a*$results_at_a_time)+1).'-'.min($count,(($a+1)*$results_at_a_time));

				} else {

					$pageText =trim($this->pi_getLL('pi_list_browseresults_page','Page',$hscText).' '.($a+1));

				}

				if ($pointer == $a) { // current page

					if ($this->internal['dontLinkActivePage']) {

						$links[] = $this->cObj->wrap($pageText,$wrapper['activeLinkWrap']);

					} else {

						$links[] ='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName  => ($a?$a:''))).'&nbsp; | </p></td>';

					}

				} else {

					$links[] =  '<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName => ($a?$a:'')),$pi_isOnlyFields).'&nbsp; | </p></td>';

				}

			}
//Remove the last hanging pipe
			$links_last_element = count($links) - 1;
			//echo "LST ELEMENT:".$links[$links_last_element];
			$links[$links_last_element] = substr($links[$links_last_element], 0, -12).'</p>';
		//	echo "<BR>".$links[$links_last_element];
			

			if ($pointer<$totalPages-1 || $showFirstLast)	{

				if ($pointer==$totalPages-1) { // Link to next page

					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),$wrapper['disabledLinkWrap']);

				} else {

					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),array($pointerName => $pointer+1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);

				}

			}

			if ($showFirstLast) { // Link to last page

				if ($pointer<$totalPages-1) {

					$links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),array($pointerName => $totalPages-1)).'&nbsp; &nbsp| </p></td>';

				} else {

					$links[]='<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText).' &nbsp;&nbsp | </p></td>';

				}

			}

			$theLinks = $this->cObj->wrap(implode(chr(10),$links),$wrapper['browseLinksWrap']);

		} else {

			$theLinks = '';

		}


		$pR1 = $pointer*$results_at_a_time+1;

		$pR2 = $pointer*$results_at_a_time+$results_at_a_time;



		if ($showResultCount) {

			if ($wrapper['showResultsNumbersWrap']) {

				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).

				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'



				$markerArray['###FROM###'] = $this->cObj->wrap($this->internal['res_count'] > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);

				$markerArray['###TO###'] = $this->cObj->wrap(min($this->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);

				$markerArray['###OUT_OF###'] = $this->cObj->wrap($this->internal['res_count'],$wrapper['showResultsNumbersWrap']);

				$markerArray['###FROM_TO###'] = $this->cObj->wrap(($this->internal['res_count'] > 0 ? $pR1 : 0).' '.$this->pi_getLL('pi_list_browseresults_to','to').' '.min($this->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);

				$markerArray['###CURRENT_PAGE###'] = $this->cObj->wrap($pointer+1,$wrapper['showResultsNumbersWrap']);

				$markerArray['###TOTAL_PAGES###'] = $this->cObj->wrap($totalPages,$wrapper['showResultsNumbersWrap']);

				// substitute markers

				$resultCountMsg = $this->cObj->substituteMarkerArray($this->pi_getLL('pi_list_browseresults_displays','Displaying results ###FROM### to ###TO### out of ###OUT_OF###'),$markerArray);

			} else {

				// render the resultcount in the "traditional" way using sprintf

				$resultCountMsg = sprintf(

					str_replace('###SPAN_BEGIN###','<span'.$this->pi_classParam('browsebox-strong').'>',$this->pi_getLL('pi_list_browseresults_displays','Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),

					$count > 0 ? $pR1 : 0,

					min($count,$pR2),

					$count);

			}

			$resultCountMsg = $this->cObj->wrap($resultCountMsg,$wrapper['showResultsWrap']);

		} else {

			$resultCountMsg = '';

		}



		$sTables = $this->cObj->wrap($resultCountMsg.$theLinks,$wrapper['browseBoxWrap']);



		return $sTables;

	}
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank/pi1/class.tx_jobbank_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank/pi1/class.tx_jobbank_pi1.php"]);
}

?>
