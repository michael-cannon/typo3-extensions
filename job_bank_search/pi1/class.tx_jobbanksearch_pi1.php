<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Ritesh Gurung (ritesh@srijan.in)
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
*  but WITHOUT ANY WARRANTY; without even the implied warranty ofpi_list_browseresults
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Job Bank Seach' for the 'job_bank_search' extension.
 *
 * @author	Ritesh Gurng <ritesh@srijan.in>
 */


require_once(PATH_tslib."class.tslib_pibase.php");
//require_once(t3lib_extMgm::extPath('job_bank_search')."class.ux_tslib_pibase.php");

class tx_jobbanksearch_pi1 extends tslib_pibase {
	var $prefixId = "tx_jobbanksearch_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_jobbanksearch_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "job_bank_search";	// The extension key.
	var $tableName = "tx_jobbank_list";	// The Job Bank table Name.
	var $tableNameCareer = "tx_jobbank_career";	// The Career table Name.
	var $tableNameQualification = "tx_jobbank_qualification";	// The Qualification table Name.
	var $tableNameStatus = "tx_jobbank_status";	// The Status table Name.
	var $tableNameIndustry = "tx_t3consultancies_cat";	// The Category table Name.
	var $tableNameSponsor = "tx_t3consultancies";	// The Sponsors table Name.
	var $tableNameCountry = "static_countries";// The Country table Name.
	var $tableNameCountryZone = "static_country_zones";	// The Zone table Name.
	var $backpId;
	var $_SearchArray;
	
	var $arrFormatDt=array(
				0=>"dd/mm/yyyy",
				1=>"mm/dd/yyyy",
				2=>"yyyy/dd/mm",
				3=>"n/j/Y"
				);
	var $arrConvFormatDt=array(
				0=>"d/m/Y",
				1=>"m/d/Y",
				2=>"Y/d/m",
				3=>"n/j/Y"
				);
	
	var $arrayValPlacing=array(
			0=>"1,0,2",
			1=>"0,1,2",
			2=>"2,1,0");
	
	var $arrayValPlacingJs=array(
			0=>"2,1,0",
			1=>"2,0,1",
			2=>"0,2,1");

	var $checkBrief				= true;
	var $checkDetailed			= false;
	
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();    // Initialize class variables
		
		$template=array();
		$markerArray=array();
		//Get Template File
		$this->backpId= $this->pi_getPageLink($GLOBALS["TSFE"]->id);

		if($this->getData("templateFile")!='')
		{
			$this->templateCode = $this->cObj->fileResource($this->getData("templateFile"));
		}
		else
		{
			$this->templateCode = $this->cObj->fileResource("EXT:$this->extKey/jobbanksearch.tmpl");
		}
		
		$typolinkConfig = array(
								'parameter'=>$GLOBALS["TSFE"]->id
								);
		$action=t3lib_div::_GP('action');
		$_search=array();
		$_searchCriteria=array(
			"Employer"=>"sponsor_id",
			"Location"=>"location",
			"Industry"=>"industry"
			);
		 $refInfo = t3lib_div::getIndpEnv('HTTP_REFERER');
		
		if ( t3lib_div::_GP($this->extKey)) {
			$_searchArr=t3lib_div::_GP($this->extKey);
			$this->_SearchArray=t3lib_div::_GP($this->extKey);
		} else {
			// MLC don't recall search
//			$_searchArr=$GLOBALS["TSFE"]->fe_user->getKey('ses','job_bank_search');
//			$this->_SearchArray=$GLOBALS["TSFE"]->fe_user->getKey('ses','job_bank_search');
		}
	
		$markerArray['###FORMEXTENSION###']=$this->extKey;

		//Initialisation for filling the List location and category
		$_fieldsCategory="uid,title";
		$_whereConditionCategory="1=1 ".$this->cObj->enableFields($this->tableNameIndustry);
		$resCategory = $GLOBALS['TYPO3_DB']->exec_SELECTquery($_fieldsCategory,$this->tableNameIndustry,$_whereConditionCategory);
		$resCategoryArr = $this->prepareRecordsOption($resCategory); 
		
		
		$_fieldsLocation="$this->tableNameCountryZone.uid,CONCAT($this->tableNameCountry.cn_short_en,'-',$this->tableNameCountryZone.zn_name_local) as title";
		$_whereConditionLocationUs="$this->tableNameCountryZone.zn_country_iso_3=$this->tableNameCountry.cn_iso_3 and $this->tableNameCountry.cn_short_en = 'United States'";
		$_whereConditionLocation="$this->tableNameCountryZone.zn_country_iso_3=$this->tableNameCountry.cn_iso_3 and $this->tableNameCountry.cn_short_en != 'United States'";
		$_orederByLocation="CONCAT($this->tableNameCountry.cn_short_en,'-',$this->tableNameCountryZone.zn_name_local)";
	    
	    // Uncomment to edit this
	    // echo $GLOBALS['TYPO3_DB']->SELECTquery($_fieldsLocation,$this->tableNameCountryZone,$_whereConditionLocation);
		$resLocationUs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($_fieldsLocation,"$this->tableNameCountryZone,$this->tableNameCountry",
		                                                        $_whereConditionLocationUs,'',$_orederByLocation);
		$resLocation   = $GLOBALS['TYPO3_DB']->exec_SELECTquery($_fieldsLocation,"$this->tableNameCountryZone,$this->tableNameCountry",
		                                                        $_whereConditionLocation,'',$_orederByLocation);
		
		$resLocationArrUs    = $this->prepareRecordsOption($resLocationUs);
		$resLocationArrNonUs = $this->prepareRecordsOption($resLocation,0);
		
//		$resLocationArr=t3lib_div::array_merge($resLocationArrUs,$resLocationArrNonUs);
		if($_searchArr['submit_button'])
		{
			$_resLocationOption = $this->createOptionList($resLocationArrUs,$_searchArr['search_text_location']);
			$_resLocationOption .= $this->createOptionList($resLocationArrNonUs,$_searchArr['search_text_location']);
			$_resCategoryOption = $this->createOptionList($resCategoryArr,$_searchArr['search_text_category']);
			
			
		}else{
			$_resLocationOption = $this->createOptionList($resLocationArrUs);
			$_resLocationOption .= $this->createOptionList($resLocationArrNonUs);
			$_resCategoryOption = $this->createOptionList($resCategoryArr);
		}

		if ($action=='') {
			$action="showadvance";
		}
		
		//echo $action;
		switch ( $action) {
			case 'showadvance':
				// MLC Default to detailed view
				if( ! isset( $_searchArr['search_text_view'] ) )
				{
					$_searchArr['search_text_view']	= 2;
					$this->checkBrief				= false;
					$this->checkDetailed			= true;
				}
							
				$typolinkConfig['additionalParams']='';
				$template['job_bank_header'] = $this->cObj->getSubpart($this->templateCode,"###JOBBANKSEARCHADV###");
				$markerArray['###ADVANCESEARCH_TEXT###']=$this->cObj->typolink('Regular Search',$typolinkConfig);
				$markerArray['###FORMACTION###']=$this->pi_getPageLink($GLOBALS["TSFE"]->id);
				$markerArray['###ACTION###']=$action;
				$markerArray['###JOBSEARCH_LOCATION###']=$_resLocationOption;
				$markerArray['###JOBSEARCH_CATEGORY###']=$_resCategoryOption;
				$markerArray['###SEARCH_TEXT_EMPLOYER_VALUE###']='';
				$markerArray['###SEARCH_TEXT_KEYWORDS_VALUE###']='';
				$markerArray['###JOB_STATUS###']=$this->_getJobStatusSelection($_searchArr['job_status'],$_searchArr['submit_button']);
				
				$markerArray['###SEARCH_TEXT_KEYWORDS_VALUE###']=$_searchArr[search_text_keywords];
				
				if($_searchArr['submit_button'])
				{
					$GLOBALS["TSFE"]->fe_user->setKey('ses','job_bank_search',$_searchArr);
					$markerArray['###SEARCH_TEXT_EMPLOYER_VALUE###']=$_searchArr[search_text_employer];				
					$markerArray['###SEARCH_TEXT_KEYWORDS_VALUE###']=$_searchArr[search_text_keywords];	
					if($_searchArr['search_text_view']==2)
					{
						$this->checkBrief		= false;
						$this->checkDetailed	= true;
					}
							
					$tempContent=$this->buildCriteria($_searchArr);
				}
				$tempContent=$this->buildCriteria($_searchArr);
				$content = $this->cObj->substituteMarkerArrayCached($template['job_bank_header'], $markerArray, array(),array());
				break;

			case 'showcompanyDetails':
			        $content.=$this->showJobDetail(t3lib_div::_GP('job_id'));
			    break;

			default:
				$typolinkConfig['additionalParams']='&action=showadvance';
				$template['job_bank_header'] = $this->cObj->getSubpart($this->templateCode,"###JOBBANKSEARCH###");
				$markerArray['###ADVANCESEARCH_TEXT###']=$this->cObj->typolink('Advance Search',$typolinkConfig);
				$markerArray['###FORMACTION###']=$this->pi_getPageLink($GLOBALS["TSFE"]->id);
				$content = $this->cObj->substituteMarkerArrayCached($template['job_bank_header'], $markerArray, array(),array());
				$_searchArr=t3lib_div::_POST($this->extKey);
				$_search['text']=$_searchArr['search_text'];
				$_search['criteria']='all';
				if($_searchArr['submit_button'])
					$tempContent=$this->buildCriteria($_search);
				$tempContent=$this->buildCriteria($_search);

				break;
		}
		
		$content.=$tempContent;
	
		return $this->pi_wrapInBaseClass($content);
	}
	
    /**
     * Function to initialize class variables
     *
     * @access private
     * @author Chetan Thapliyal <chetan@srijan.in>
     */
	function init() {
		
		if($this->getData("pagingRecordsPerPage")!='')
		{
			$this->internal['results_at_a_time'] = $this->getData("pagingRecordsPerPage");
		}
		else
		{
			$this->internal['results_at_a_time'] = 2;
		}
		// Settings for paging starts here
		
		$this->internal['maxPages'] = 7;
		$this->internal['dontLinkActivePage'] = true;
		$this->internal['pagefloat'] = 'center';
		// Settings for paging ends here

	}
	
	function buildCriteria($arrSearch)
	{
		
		$fields = "$this->tableName.occupation as occupation,$this->tableNameCountryZone.zn_name_local as location,$this->tableNameCountryZone.zn_country_iso_3 as zone,$this->tableNameSponsor.title as sponsor,$this->tableName.starttime as opening_date,$this->tableName.uid as job_id, $this->tableNameQualification.qualification as qualification, $this->tableNameStatus.status_name as status, $this->tableNameCareer.career_name as career,$this->tableName.joboverview as description_job";
		$table = "$this->tableName,$this->tableNameCountryZone,$this->tableNameSponsor,$this->tableNameQualification,$this->tableNameStatus,$this->tableNameCareer";
					
		$where_condition = "$this->tableName.location = $this->tableNameCountryZone.uid 
		                    AND $this->tableName.sponsor_id = $this->tableNameSponsor.uid
		                    AND $this->tableName.qualification = $this->tableNameQualification.uid
		                    AND $this->tableName.status = $this->tableNameStatus.uid
		                    AND $this->tableName.position_filled = 0
		                    AND $this->tableName.clevel=$this->tableNameCareer.uid".$this->cObj->enableFields($this->tableName);
		
		$order_by="$this->tableName.starttime DESC";
		
		//Condition check for Location
		if($arrSearch['search_text_location']!='' && !($arrSearch['search_text_location'][array_search('all',$arrSearch['search_text_location'])]=='all'))
		{
			$industryIds='';
			while(list($key,$val)=each($arrSearch['search_text_location'])) 
			{
				$industryIds.="$val,";
			}
			$industryIds=substr($industryIds,0,strlen($industryIds)-1);
			$where_condition_level1= $GLOBALS['TYPO3_DB']->listQuery("$this->tableName.location",$industryIds,$this->tableName);
			$industryIdsArr=explode(",",$industryIds);
			$where_condition_level2='';
			foreach($industryIdsArr as $indId)
			{
				$where_condition_level3=trim($GLOBALS['TYPO3_DB']->listQuery("$this->tableName.location",$indId,$this->tableName));
				$where_condition_level2.= " OR ".substr($where_condition_level3,1,strlen($where_condition_level3)-2);
			}
			$final_condition=substr($where_condition_level1,0,strlen($where_condition_level1)-1).$where_condition_level2.")";
			$where_condition.= " and ".$final_condition;

		}
		
//		debug($arrSearch['search_text_category']);
//		echo ($arrSearch['search_text_category'][array_search('all',$arrSearch['search_text_category'])]=='all')?"true":"False";
		//Condition check for Category
		if($arrSearch['search_text_category']!='' && !($arrSearch['search_text_category'][array_search('all',$arrSearch['search_text_category'])]=='all'))
		{
			$categoryIds='';
			while(list($key,$val)=each($arrSearch['search_text_category'])) 
			{
				$categoryIds.="$val,";
			}
			$categoryIds=substr($categoryIds,0,strlen($categoryIds)-1);
			$where_condition_level1= $GLOBALS['TYPO3_DB']->listQuery("$this->tableName.industry",$categoryIds,$this->tableName);
			$categoryIdsArr=explode(",",$categoryIds);
			$where_condition_level2='';
			foreach($categoryIdsArr as $catId)
			{
				$where_condition_level3 = trim($GLOBALS['TYPO3_DB']->listQuery("$this->tableName.industry",$catId,$this->tableName));
				$where_condition_level2.= " OR ".substr($where_condition_level3,1,strlen($where_condition_level3)-2);
			}
			$final_condition=substr($where_condition_level1,0,strlen($where_condition_level1)-1).$where_condition_level2.")";
			$where_condition.= " and ".$final_condition;

		}
		
		if($arrSearch['search_text_keywords']!='')
		{
			$tableFields=$GLOBALS['TYPO3_DB']->admin_get_fields($this->tableName);
			$where_condition_level='';
			foreach ($tableFields as $fieldsName=>$value)
				$where_condition_level.= "$this->tableName.$fieldsName like '%".$arrSearch['search_text_keywords']."%' or ";
			$where_condition_level="(".substr($where_condition_level,0,strlen($where_condition_level)-3).")";
			$where_condition.=" and ".$where_condition_level;
		
		}
		
		//$arrSearch['job_status'] = intval($arrSearch['job_status']);
		
		if (!empty($arrSearch['job_status'])){
			$where_condition_status = ' AND(';
			foreach($arrSearch['job_status'] as $k=>$v){
				
				$where_condition_status .= '('.$this->tableName.'.status='.$v.') OR ';
			}
			
			$where_condition_status = substr($where_condition_status, 0, -3);
			//echo "where_condition_status". $where_condition_status;
			$where_condition .= $where_condition_status.')';
		
		}
	
		/*$where_condition        .= ( $arrSearch['job_status'] > 0)
		                           ? ' AND '.$this->tableName.'.status = '.$arrSearch['job_status']
		                           : '';*/
		
		if($arrSearch['search_text_employer']!='')
		{
            $where_condition.= " and $this->tableName.sponsor_id in (".$this->getSponsor($arrSearch['search_text_employer']).")";
  		}
  		
		// Uncomment to debug
		//$this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($fields,$table,$where_condition,'',$order_by));
		 
		$resResult=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where_condition,'',$order_by);

		if($resResult) {

		    $this->internal['res_count'] = $GLOBALS['TYPO3_DB']->sql_num_rows($resResult);
		  
		    $tmp = $this->pi_list_browseresults();
		   // echo $tmp;
			return $this->showResult($resResult,$arrSearch['search_text_view']).$tmp;
		}
	}
	

	function showResult($resultSet,$viewType) {
	
		$content='';

		// MLC detailed
	    if($viewType==2)
	    {
            $template['job_search_result']=$this->cObj->getSubpart($this->templateCode,'###JOBBANKSEARCHRESULTDETAIL###');
		}

		// MLC brief
		else
		{
			$template['job_search_result']=$this->cObj->getSubpart($this->templateCode,'###JOBBANKSEARCHRESULT###');
		}

		$template['job_search_result_record'] = $this->cObj->getSubpart($template['job_search_result'],'###JOBBANKSEARCHRESULTRECORD###');
		$template['job_search_result_data']   = $this->cObj->getSubpart($template['job_search_result'],'###JOBBANKSEARCHRESULTDATA###');
		$template['job_search_result_header']   = $this->cObj->getSubpart($template['job_search_result'],'###JOBBANKSEARCHRESULTHEADER###');
		$template['job_search_result_no_data']   = $this->cObj->getSubpart($template['job_search_result'],'###JOBBANKSEARCHRESULTNODATA###');
		$template['job_search_result_radio_view']   = $this->cObj->getSubpart($template['job_search_result'],'###JOBBANKSEARCHRESULTRADIOVIEW###');
		
		$opening_Result='';

		if($this->checkRecords($resultSet))
		{
			$data_rows='';
			
			// Limit in which records have to display
    		$start = intval( $this->piVars['pointer']) * $this->internal['results_at_a_time'];
    		$limit = $this->internal['results_at_a_time'];
    		$GLOBALS['TYPO3_DB']->sql_data_seek($resultSet, $start);
			
			while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resultSet)) && $limit--)
			{
				$data_rows .=$this->getRecordContent($row, $template['job_search_result_data'],$viewType);
			}
		
			
		}

		if($data_rows=='') {
			
			$data_rows='<tr><td colspan="4" align="center"><p><b>No job listings found.</b></p></td></tr>';
			$dataMarkerArray2['###JOBBANKSEARCHRESULTHEADER###'] = '';
			$dataMarkerArray2['###JOBBANKSEARCHRESULTNODATA###']= $this->cObj->substituteMarkerArrayCached($template['job_search_result_no_data'],array(), array(), array());
			$dataMarkerArray2['###JOBBANKSEARCHRESULTDATA###']= '';	
			$dataMarkerArray2['###JOBBANKSEARCHRESULTRADIOVIEW###']= '';	
		}else{
			$typoConfigLink=array(
					"parameter" => $GLOBALS['TSFE']->id,
					"additionalParams" =>"&".$this->extKey."[search_text_view]=1&".$this->extKey."[search_text_keywords]=".$this->_SearchArray[search_text_keywords]."&".$this->extKey."[submit_button]=Search"
			);
			$dataMarkerArray2['###JOBBANKSEARCHRESULTHEADER###'] =$this->cObj->substituteMarkerArrayCached($template['job_search_result_header'],array(), array(), array());;
			$dataMarkerArray2['###JOBBANKSEARCHRESULTDATA###']= $data_rows;
			$dataMarkerArray2['###JOBBANKSEARCHRESULTNODATA###']= '';
			$markerArrayRadio['###CHECKED_BRIEF###']= ( $this->checkBrief )
									? ' checked="checked"'
									: '';
			$markerArrayRadio['###CHECKED_DETAIL###']= ( $this->checkDetailed )
									? ' checked="checked"'
									: '';
			$markerArrayRadio['###LINK_BRIEF###']=$this->cObj->typoLink_url($typoConfigLink);
			$typoConfigLink['additionalParams']="&".$this->extKey."[search_text_view]=2&".$this->extKey."[search_text_keywords]=".$this->_SearchArray[search_text_keywords]."&".$this->extKey."[submit_button]=Search";
			$markerArrayRadio['###LINK_DETAIL###']=$this->cObj->typoLink_url($typoConfigLink);
			$dataMarkerArray2['###JOBBANKSEARCHRESULTRADIOVIEW###']= $this->cObj->substituteMarkerArrayCached($template['job_search_result_radio_view'],$markerArrayRadio, array(), array());	
			
		}

		//$dataMarkerArray2['###JOBBANKSEARCHRESULTDATA###']= $data_rows;
		
		$content=$this->cObj->substituteMarkerArrayCached($template['job_search_result'],array(), $dataMarkerArray2, array());
		
		
		return $content;
	}
	
	
	function getRecordContent ($row, $template,$viewType=1)
	{
		$key=array_search($this->getData('dateFormatCalendar'),$this->arrFormatDt);
	
		## create the markers for each column 
		$data_rows = '';	
		$typolinkConfig = array(
								'parameter'=> $GLOBALS["TSFE"]->id." _self morelink",
								'additionalParams'=>"&job_id=$row[job_id]&action=showcompanyDetails"
								);
							
		## initialize the array for each new record
	
		$dataMarkerArray = array();
		$dataMarkerArray['###JOBBANK_OPENING###']    = date($this->arrConvFormatDt[$key],$row['opening_date']);
		$dataMarkerArray['###JOBBANK_OCCUPATION###'] = "<b>".$this->cObj->typolink($row['occupation'],$typolinkConfig)."</b>";
		$dataMarkerArray['###JOBBANK_COMPANY###']    = $row['sponsor'];
		if($viewType==2)
		{
    		$details_link = '';
    		
			if($row['description_job']!='')
			{
				if(strlen($row['description_job'])>120) {
				    $jobbankDescription="<BR/>".substr($row['description_job'],0,120);
				    $details_link = $this->cObj->typolink('Job listing details...',$typolinkConfig);
    			} else {
    			    $jobbankDescription="<BR/>".$row['description_job'];
    			   $details_link = $this->cObj->typolink('Job listing details...',$typolinkConfig);
				}
				
				$dataMarkerArray['###JOBBANK_OCCUPATION###'].=$jobbankDescription;
	 		}
			if($row['status']!='')
			{
				$dataMarkerArray['###JOBBANK_OCCUPATION###'].="<br/><B>Job Status</B>: ".$row['status'];
			}
			if($row['qualification']!='')
			{
				$dataMarkerArray['###JOBBANK_OCCUPATION###'].="<br/><B>Education Level</B>: ".$row['qualification'];
			}
			if($row['career']!='')
			{
				$dataMarkerArray['###JOBBANK_OCCUPATION###'].='<br/><div style="display:block; float: left;"><B>Career Level</B>: '.$row['career'].'</div>';

				
			}
			if($row['endtime']!='')
			{
				$dataMarkerArray['###JOBBANK_ENDDATE###'].="<br/><B>Closes on</B>: ".date("mm/dd/Y",$row['endtime']);
			}
  		}
		$dataMarkerArray['###JOBBANK_LOCATION###']="$row[location] ($row[zone])";
		
		if ( $details_link !== '') {
					$dataMarkerArray['###JOB_DETAILS_LINK###'].='<div style="display:block; float: right;">'.$details_link.'</div>';
				   
				}
		$dataMarkerArray['###JOBBANK_APPLY###']="";
		if($this->checkLogin())
		{
			$typolinkConfig['additionalParams']="&job_id=".$row['job_id']."&action=apply";
			$dataMarkerArray['###JOBBANK_APPLY###']=$this->cObj->typolink("Apply Now",$typolinkConfig,'_blank','morelink');
		}
		

		$dataRec = $this->cObj->substituteMarkerArrayCached($template, $dataMarkerArray, array(), array());
		return $dataRec;
	}
	
	function showJobDetail($jobId,$_postValues='')
	{
		
		$template['job_bank_detail_comp'] = $this->cObj->getSubpart($this->templateCode,"###JOBBANK_COMPANY_DETAILS###");
		$_LOCALDB['fields'] = "$this->tableName.occupation as occupation
			, $this->tableName.city as city
			, $this->tableName.company_description as  company_description
			, $this->tableName.additional_requirement as additional_requirement
			, $this->tableName.major_responsibilities as major_responsibilities
			, $this->tableNameCountryZone.zn_name_local as zone
			, $this->tableNameCountryZone.zn_country_iso_3 as country
			, $this->tableNameSponsor.title as sponsor
			, $this->tableName.starttime as opening_date
			, $this->tableName.uid as job_id
			, $this->tableNameQualification.qualification as qualification
			, $this->tableNameStatus.status_name as status
			, $this->tableNameCareer.career_name as career
			, $this->tableName.joboverview as description_job
			, $this->tableNameSponsor.contact_name as contact_name
			, $this->tableNameSponsor.contact_email as email
			, $this->tableNameSponsor.url as url
			, $this->tableNameSponsor.logo as logo
			, $this->tableNameSponsor.tx_jobbankresumemgr_resumecontactname
			, $this->tableNameSponsor.tx_jobbankresumemgr_resumecontactemail
		";
		$_LOCALDB['table'] = "$this->tableName,$this->tableNameCountryZone,$this->tableNameSponsor,$this->tableNameQualification,$this->tableNameStatus,$this->tableNameCareer";
					
		$_LOCALDB['where_condition'] = "$this->tableName.location = $this->tableNameCountryZone.uid and $this->tableName.sponsor_id = $this->tableNameSponsor.uid and $this->tableName.qualification = $this->tableNameQualification.uid and $this->tableName.status = $this->tableNameStatus.uid and $this->tableName.clevel=$this->tableNameCareer.uid and $this->tableName.uid=$jobId".$this->cObj->enableFields($this->tableName);
		$_LOCALDB['order_by']="$this->tableName.starttime DESC";
		
		//echo $GLOBALS['TYPO3_DB']->SELECTquery($_LOCALDB['fields'],$_LOCALDB['table'],$_LOCALDB['where_condition'],$_LOCALDB['order_by']); 
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($_LOCALDB['fields'],$_LOCALDB['table'],$_LOCALDB['where_condition'],$_LOCALDB['order_by']);
		$typolinkConfig = array(
								'parameter'=>$this->getData('resumePId'),
								'additionalParams'=>"&job_id==$jobId"
								);
		//$this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($_LOCALDB['fields'],$_LOCALDB['table'],$_LOCALDB['where_condition'],$_LOCALDB['order_by']));
		$markerArray = array(
				'###JOBBANKINFO_COMPANYNAME###' => '',
				'###JOBBANKINFO_CITY###' => '',
				'###JOBBANKINFO_STATE###' => '',
				'###JOBBANKINFO_COUNTRY###' => '',
				'###JOBBANKINFO_STATUS###' => '',
				'###JOBBANKINFO_SPONSOR_LOGO###' =>'',
				'###JOBANKINFO_CONTACTPERSON###' =>'',
				'###JOBBANKINFO_EMAIL###' =>'',
				'###JOBBANKINFO_PHONE###' =>'',
				'###JOBBANKINFO_URL###' =>'',
				'###JOBBANKINFO_COMPANY_DESCRIPTION###' =>'',
				'###JOBBANK_DESCRIPTION###' =>'',
				'###JOBBANKINFO_QUALIFICATION###' =>'',
				'###JOBBANKINFO_ADDITIONAL_REQUIREMENTS###' =>'',
				'###MAJOR_RESPONSIBILITIES###' =>'',
			);
		if($res)
		{
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$markerArray['###COMPANY_NAME###']=$row['sponsor'];
			if ($row['occupation']!=''){
				$markerArray['###JOBBANKINFO_POSITION###']="<strong>Job Title</strong> <br>".$row['occupation'];
			}
			
				$markerArray['###BACKPID###']=$this->pi_getPageLink($GLOBALS['TSFE']->id);
			if($row['sponsor']!='')
			{
				$markerArray['###JOBBANKINFO_COMPANYNAME###']="<strong>Company</strong> ".$row['sponsor'];
			}
			if($row['city']!='')
			{
				$markerArray['###JOBBANKINFO_CITY###']=$row['city'].", ";
			}
			if($row['zone']!='')
			{
				$markerArray['###JOBBANKINFO_STATE###']=$row['zone'];
			}
			if($row['country']!='')
			{
				$markerArray['###JOBBANKINFO_COUNTRY###']=" (".$row['country'].")";
			}
			if($row['status']!='')
			{
				$markerArray['###JOBBANKINFO_STATUS###']="<br/><strong>Status</strong> ".$row['status'];
			}
			if($row['description_job']!='')
			{
				$markerArray['###JOBBANK_DESCRIPTION###']= "<strong>Job Description</strong><BR/>".nl2br(trim($row['description_job']));
			}
			if($row['contact_name']!='')
			{
				$contact_name	= ( '' != $row[ 'tx_jobbankresumemgr_resumecontactname' ] )
									? $row[ 'tx_jobbankresumemgr_resumecontactname' ]
									: $row[ 'contact_name' ];

				$markerArray['###JOBANKINFO_CONTACTPERSON###'].= "<strong>Contact Person</strong> ".$contact_name;
			}
			
			if($row['email']!='')
			{
				$contact_email	= ( '' != $row[ 'tx_jobbankresumemgr_resumecontactemail' ] )
									? $row[ 'tx_jobbankresumemgr_resumecontactemail' ]
									: $row[ 'email' ];

				$markerArray['###JOBBANKINFO_EMAIL###']="<br/><strong>Email</strong> ".$contact_email;
			}
			if($row['phone']!='')
			{
				$markerArray['###JOBBANKINFO_PHONE###']="<br/><strong>Phone</strong> ".$row['phone'];
			}
						
			if($row['url']!='')
			{
				$markerArray['###JOBBANKINFO_URL###']="<br/><strong>URL</strong> ".$row['url'];
			}
			if($row['company_description']!='')
			{
				$markerArray['###JOBBANKINFO_COMPANY_DESCRIPTION###'] = "<br/><strong>Company Description</strong><p>"
					. nl2br($row['company_description'])
					.  "</p>";
			}
			if($row['qualification']!='')
			{
				$markerArray['###JOBBANKINFO_QUALIFICATION###']="<strong>Qualifications</strong><br/>".nl2br($row['qualification']);
			}
			if($row['additional_requirement']!='')
			{
				$markerArray['###JOBBANKINFO_ADDITIONAL_REQUIREMENTS###'] = "<strong>Additional Requirements </strong><br/>".nl2br($row['additional_requirement']);
			}
			if($row['major_responsibilities']!='')
			{
				$markerArray['###MAJOR_RESPONSIBILITIES###']="<strong>Major Responsibilities</strong><br/>".nl2br($row['major_responsibilities']);
			}
			if($row['career']!='')
			{
				$markerArray['###JOBBANKINFO_CAREER###'] = "<strong>Career Level </strong><br/>".$row['career'];
			}
			if($row['logo']!='')
			{
				$logoImage=$this->getData("logoDirectory").$row['logo'];
				
				$markerArray['###JOBBANKINFO_SPONSOR_LOGO###'] ='';
				if(file_exists($logoImage) && ($row['logo']!='')){
					$mysock = getimagesize($logoImage); 
					$imageDisp="<img src='$logoImage' ".$this->imageResize($mysock[0],  $mysock[1], 150)." style='border:1px solid #666666;'><br/>";
					$markerArray['###JOBBANKINFO_SPONSOR_LOGO###'] =$imageDisp;
				}

			}
			
			$PageLinkId=$this->getData('resumePId');

			$markerArray['###ACTIONLINK###']=$this->pi_getPageLink($PageLinkId);
			$markerArray['###BACKPAGEID###']=$this->pi_getPageLink($GLOBALS['TSFE']->id);
			$markerArray['###EXTENSION_KEY###']=$this->prefixId;
			$my_vars = $GLOBALS["TSFE"]->fe_user->getKey('ses','job_bank_search');
			
			
			
			$markerArray['###JOBID###']=$jobId;
			
			
			$dataRec = $this->cObj->substituteMarkerArrayCached($template['job_bank_detail_comp'], $markerArray, array(), array());
			
		}
		
		return $dataRec;
 	}
	
	function getLocation($locationName,$idSearch=false)
	{
		$_locationId='';
		$resLocation = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,zn_name_local',$this->tableNameCountryZone,'zn_name_local like "'.$locationName.'%"');
		if($this->checkRecords($resLocation))
		{
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resLocation))
			{
				$_locationId.="$row[uid],";
			}
			$_locationId=substr($_locationId,0,strlen($_locationId)-1);
		}

		return $_locationId;
	
	}
	
	
	function getSponsor($sponsorName)
	{
		$_sponsorId='';
		$resSponsor = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid',$this->tableNameSponsor,'title like "'.$sponsorName.'%"');
		
		if($this->checkRecords($resSponsor))
		{
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSponsor))
			{
				$_sponsorId.="$row[uid],";
				
			}
			$_sponsorId=substr($_sponsorId,0,strlen($_sponsorId)-1);
			
		return $_sponsorId;
		}
		
	}
	
	
	function getIndustry($industryName)
	{
		$_industryId='';
		$resIndustry = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid',$this->tableNameIndustry,'title like "'.$industryName.'%" and tx_jobbank_status=0 and deleted=0');
		if($this->checkRecords($resIndustry))
		{
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resIndustry))
			{
				$_industryId.="$row[uid],";
				
			}
			$_industryId=substr($_industryId,0,strlen($_industryId)-1);
			
		return $_industryId;
		}
		
	}
	
	
	function checkRecords($resName)
	{
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resName)=='')
			return false;
		else 
			return true;
	}
	
	function prepareRecordsOption($res,$_allSelected=1)
	{
		$returnArr=array();
		if($_allSelected)
			$returnArr['all']="-- Select All --";
		if($this->checkRecords($res))
		{
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
			{
				$returnArr[$row['uid']]=$row['title'];
			}	
		}
		return $returnArr;
	}
	
	
	/**
		* To create option List based on Resource Id
		*
		* @param array  Array of options
		* @param String  Selected value
		* @return String  returns option List
		*/
		function createOptionList($res, $selected = array()) {
			$optionList = '';
			while (list($optionVal, $optionName) = each($res)) {
				if(count($selected)>0)
				{
					if (in_array($optionVal,$selected))
						$optionList .= "<option value='$optionVal' selected>$optionName</option>\n";
					else
						$optionList .= "<option value='$optionVal'>$optionName</option>\n";
				}
				else
					$optionList .= "<option value='$optionVal'>$optionName</option>\n";
				
			}
			return $optionList;
		}
	/**
		* To create option List based on Resource Id
		*
		* @param array  Array of options
		* @param String  Selected value
		* @return String  returns option List
		*/
		function createOptionListCountry($res, $selected = '') {
			$optionList = '';
			while (list($optionVal, $optionName) = each($res)) {
				if ($selected == $optionVal)
				$optionList .= "<option value='$optionVal' selected>$optionName</option>\n";
				else
					$optionList .= "<option value='$optionVal'>$optionName</option>\n";
			}
			return $optionList;
		}
	
	function checkLogin()
	{
		if($GLOBALS['TSFE']->fe_user->user['uid']!='' && $GLOBALS['TSFE']->fe_user->user['uid']!=0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getData ($keyName){
		
		$data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		
		return $data[$keyName];
			
	}

	function imageResize($width, $height, $target) {

		//takes the larger size of the width and height and applies the  formula accordingly...this is so this script will work  dynamically with any size image

		if ($width > $height) {
			$percentage = ($target / $width);
		} else {
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		//returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the
		return "width=\"$width\" height=\"$height\"";
	}
	
	/**
	 * Function to get the Job status selection list
	 *
	 * @access private
	 * @param  integer $default_status  Default selected job status
	 * @return string
	 * @author Chetan Thapliyal <chetan@srijan.in>
	 */
/*	function _getJobStatusSelection( $default_status = 0) {
	
	   $content  = '';
	   
	   $content .= '<select id="'.$this->extKey.'[job_status]" name="'.$this->extKey.'[job_status]">
	                   <option value="">--Select--</option>';
	                   
	   $select   = 'uid, status_name';
	   $from     = $this->tableNameStatus;
	   $where   .= 'deleted = 0 ';
	   $where   .= 'AND hidden = 0';
	   $order_by = 'status_name';
	   
	   // Uncomment to debug
	   // $this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($select, $from, $where, '', $order_by));
	   
	   $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', $order_by);
	   
	   if ( $rs) {
	       if ( $GLOBALS['TYPO3_DB']->sql_num_rows($rs)) {
	           while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs)) {
	               $content .= '<option value="'.$row['uid'].'" ';
	               
	               if ( $row['uid'] == $default_status) {
	                   $content .= 'selected';
	               }
	               
	               $content .= '>'.$row['status_name'].'</option>';
	           }
	       }
	   }
	                   
	   $content .= '</select>';
	   
	   return $content;
	}*/


	/**
	 * Function to get the Job status selection list
	 *
	 * @access private
	 * @param  integer $default_status  Default selected job status
	 * @return string
	 * @author Chetan Thapliyal <chetan@srijan.in>
	 */
	function _getJobStatusSelection( $default_status = null, $submit_button = 0) {
	  
		$content  = '';
	   
	  // $content .= '<select id="'.$this->extKey.'[job_status]" name="'.$this->extKey.'[job_status]">
	                 //  <option value="">--Select--</option>';
	                   
	   $select   = 'uid, status_name';
	   $from     = $this->tableNameStatus;
	   $where   .= 'deleted = 0 ';
	   $where   .= 'AND hidden = 0';
	   $order_by = 'status_name';
	   
	   // Uncomment to debug
	   // $this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($select, $from, $where, '', $order_by));
	   
	   $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', $order_by);
	   
	   if ( $rs) {
	       if ( $GLOBALS['TYPO3_DB']->sql_num_rows($rs)) {
	           while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs)) {

	             //  $content .= '<option value="'.$row['uid'].'" ';
	                 $content .= '<input type="checkbox" name="'.$this->extKey.'[job_status][]" value="'.$row['uid'].'" ';
					
					if ($submit_button!=''){
						if(!empty($default_status)){
			                 foreach ($default_status as $k=>$uid){
				                 if ( $row['uid'] == $uid) {
				                   $content .= 'CHECKED';
				              	 }//Enf if
							}//Enf foreach
							$content .= '>'.$row['status_name'].'</option>';
						}
					
					}else{
						
						$content .= ' CHECKED>'.$row['status_name'].'</option>';
					}
	               
	           }
	       }
	   }
	 
	   return $content;
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
		$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p> | </p></td>';
		$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p> | </p></td>';
		$wrapper['activeLinkWrap'] = '<td'.$this->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p> | </p></td>';
		$wrapper['browseLinksWrap'] = trim('<table '.$tableParams).'><tr>|</tr></table>';
		$wrapper['showResultsWrap'] = '<p> | </p>';
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
					$links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText),array($pointerName => ($pointer-1?$pointer-1:''))).'</p></td>';
				} elseif ($alwaysPrev)	{
					$links[]='<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText).'</p></td>';
				}
			}
			for($a=$firstPage;$a<$lastPage;$a++)	{ // Links to pages
				if ($this->internal['showRange']) {
					$pageText = (($a*$results_at_a_time)+1).'-'.min($count,(($a+1)*$results_at_a_time));
				} else {
					$pageText =trim($this->pi_getLL('pi_list_browseresults_page','Page',$hscText).($a+1));
				}
				if ($pointer == $a) { // current page
					if ($this->internal['dontLinkActivePage']) {
						$links[] = $this->cObj->wrap($pageText,$wrapper['activeLinkWrap']);
					} else {
						$links[] ='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName  => ($a?$a:''))).'</p></td>';
					}
				} else {
					$str_link= '';
					$links[] =  '<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName => ($a?$a:'')),$pi_isOnlyFields).'</p></td>';
				}
			}

			if ($pointer<$totalPages-1 || $showFirstLast)	{
				if ($pointer==$totalPages-1) { // Link to next page
					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),$wrapper['disabledLinkWrap']);
				} else {
					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),array($pointerName => $pointer+1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				}
			}
			if ($showFirstLast) { // Link to last page
				if ($pointer<$totalPages-1) {
					$links[]='<td nowrap="nowrap"><p> &nbsp;'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),array($pointerName => $totalPages-1)).'</p></td>';
				} else {
					$links[]='<td nowrap="nowrap"><p> &nbsp;'.$this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText).'</p></td>';
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
	
	function _debug( $var) {
	   echo '<PRE>'.print_r($var, true).'</PRE>';
	}
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank_search/pi1/class.tx_jobbanksearch_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank_search/pi1/class.tx_jobbanksearch_pi1.php"]);
}

?>
