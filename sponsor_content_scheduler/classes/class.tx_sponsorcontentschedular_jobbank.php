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
 * @author  Ritesh Gurung <ritesh@srijan.in>
 */

/*
*  38: class tx_jobbank_pi1
*
*              SECTION: Query execution
*  167:     function main($content,$conf)
*/


class tx_sponsorcontentschedular_jobbank extends tx_sponsorcontentscheduler_base {
    var $tableName = "tx_jobbank_list"; // The table Name.
    var $tableNameCareer = "tx_jobbank_career"; // The table Name.
    var $tableNameQualification = "tx_jobbank_qualification";   // The table Name.
    var $tableNameStatus = "tx_jobbank_status"; // The table Name.
    var $tableNameIndustry = "tx_t3consultancies_cat";  // The table Name.
    var $tableNameSponsor = "tx_t3consultancies";   // The table Name.
    var $tb_user="fe_users";
    var $tb_consultancies = "tx_t3consultancies";
    var $job_careerlevel;
    var $job_qualifiacation;
    var $job_status;
    var $job_industry;
    var $RTEObj;
    var $backPath;
    var $__loggedInSponsorId;
    var $__loggedInUserId;
    var $lConf;
    var $displayFieldsJob;
    var $sponsorId;
    var $cObj;
    var $piVars;
    
    function tx_sponsorcontentschedular_jobbank($cObj){
        $this->cObj = $cObj;
        $this->lConf = $this->__getFlexFormField();
        
        $this->piVars = (is_array(t3lib_div::_GP($this->prefixId)))?t3lib_div::_GP($this->prefixId):t3lib_div::_POST($this->prefixId);
        
        
        $this->displayFieldsJob=$this->lConf['jobListingFields'];
        
        /*
        *    Initialising the variables for Paging in the Job Bank
        */
        
        if($this->getData("pagingRecordsPerPage")!='')
        {
            $this->internal['results_at_a_time'] = $this->lConf['pagingRecordsPerPage'];
        }
        else
        {
            $this->internal['results_at_a_time'] = 7;
        }
        // Settings for paging starts here
        
        $this->internal['maxPages'] = 10;
        $this->internal['dontLinkActivePage'] = true;
        $this->internal['pagefloat'] = 'center';
        
        // Settings for paging ends here
        
        
        
        $this->__loggedInUserId=$GLOBALS['TSFE']->fe_user->user['uid'];
        if(strtoupper($this->cObj->data['select_key'])=='SALES')
        {
            $this->__loggedInSponsorId = $this->piVars['sponsor_id'];
        }else{
            $this->__loggedInSponsorId = $this->__getSponsorRec();
        }
        
        
    }
    /**
     * [Put your description here]
     */
    function showContent()  {
        $this->job_careerlevel='';
        $this->job_qualifiacation='';
        $this->job_status='';
        $this->job_industry='';
        $this->sponsorId=$this->__loggedInSponsorId;
        
        

        //Getting page Id
        $page_id = $GLOBALS["TSFE"]->id;
        
        //Initializing the variables
        $template = array();
        $markerArray = array();
        
        
        /* Getting the records from tables
        *
        */
        $resCareerLevel=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,career_name', $this->tableNameCareer,'1=1 '.$this->cObj->enableFields($this->tableNameCareer));
        
        //Intialising the data for Qualification
        $resCareerQualification=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,qualification', $this->tableNameQualification,'1=1 '.$this->cObj->enableFields($this->tableNameQualification));
        
        //Initialiasing the data for Status
        $resCareerStatus=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,status_name', $this->tableNameStatus,'1=1 '.$this->cObj->enableFields($this->tableNameStatus));
        
        //Initialising the data for Industry
        $resJobIndustry=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', $this->tableNameIndustry,'tx_jobbank_status = 0 '.$this->cObj->enableFields($this->tableNameIndustry));
        
        $resJobSponsor = false;
        if ($this->sponsorId)
            $resJobSponsor=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', $this->tableNameSponsor,'uid ='.$this->sponsorId . ' '.$this->cObj->enableFields($this->tableNameSponsor));
        
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerLevel)) {
            $this->job_careerlevel.=$row[uid]."|".$row[career_name]."||";
        }
        
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerQualification)) {
            $this->job_qualifiacation.=$row[uid]."|".$row[qualification]."||";
        }

        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCareerStatus)) {
            $this->job_status.=$row[uid]."|".$row[status_name]."||";
        }
        
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resJobIndustry)) {
            $this->job_industry.=$row[uid]."|".$row[title]."||";
        }

        if($resJobSponsor) {
            $rowSponsor = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resJobSponsor);
        }
        
        $typolink_conf = array(
            "parameter" => $page_id,
            "additionalParams" => "&$this->prefixId[action]=job_conf&$this->prefixId[mode]=add&$this->prefixId[sponsor_id]=".$this->sponsorId,
            "useCacheHash" => 1);
        
        $template['job_bank_header'] = $this->cObj->getSubpart($this->__getTemplateCode(),"###JOBBANK_HEADER###"); 

        $markerArray["###ADD_LINK###"]                  = $this->cObj->typolink("Add", $typolink_conf);
        $markerArray['###DYNAMIC_JS###']                = $this->getCountryZone(1);
        $markerArray['###FORMNAMEEXTENSIONJS###']       = $this->prefixId;
        $markerArray["###JS_CALENDAR_CSS###"]           = t3lib_extMgm::siteRelPath('erotea_date2cal') . 'jscalendar/calendar-win2k-1.css';
        $markerArray["###JS_CALENDAR_LANG###"]          = t3lib_extMgm::siteRelPath('erotea_date2cal') . 'jscalendar/lang/calendar-en.js';
        $markerArray["###JS_CALENDAR_SETUP###"]         = t3lib_extMgm::siteRelPath('erotea_date2cal') . 'jscalendar/calendar-setup.js';
        $markerArray["###JS_CALENDAR###"]               = t3lib_extMgm::siteRelPath('erotea_date2cal') . 'jscalendar/calendar.js';
        $markerArray["###SCRIPTNAME_DATEFUNCTIONS###"]  = t3lib_extMgm::siteRelPath($this->extKey) . 'styles/jsDate.js';
        $markerArray["###SCRIPTNAME###"]                = t3lib_extMgm::siteRelPath($this->extKey) . 'styles/lib.js';
        $markerArray["###SPONSOR_NAME###"]              = $rowSponsor['title'];
        $markerArray["###STYLE_TEMPLATE_TEST1###"]      = t3lib_extMgm::siteRelPath($this->extKey) . 'images/bgmenu_hover.gif';
        $markerArray["###STYLE_TEMPLATE_TEST###"]       = t3lib_extMgm::siteRelPath($this->extKey) . 'images/bgmenu.gif';

        $typolink_conf["additionalParams"] ="&$this->prefixId[sponsor_id]=".$this->sponsorId;
        $this->backPath = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->cObj->typoLink_URL($typolink_conf);

        $content = $this->cObj->substituteMarkerArrayCached($template['job_bank_header'], $markerArray, array(),array());
        
        switch($this->piVars['mode'])
        {
            case 'add':
                $content .= $this->addForm();
                break;
            case 'edit':
                $content .= $this->editForm($this->piVars['jobUid']);
                break;
            case 'save':
                $this->saveDatainfo();
                $content .= $this->showListings();
                break;
            case 'update':
                $this->updateDatainfo();
                $content .= $this->showListings();
                break;
            case 'listingSubmit':
                $content .= $this->deleteDataInfo();
                break;
            default:
                $content .= $this->showListings();
        }
        
        return $content;
        
        
    }
    
    /**
     * Creates a form for addition of Job listing 
     *
     * @return  String
     */
    function addForm()
    {
        $content = '';
        $template['job_bank_add'] = $this->cObj->getSubpart($this->__getTemplateCode(),"###ADD_JOB_PLACEHOLDER###"); 

        $subPartArray['###CAREERLEVEL###']              = $this->createDropDown($this->job_careerlevel);
        $subPartArray['###FORMNAMEEXTENSIONJOB###']     = $this->prefixId;
        $subPartArray['###FORMNAMEEXTENSION###']        = $this->prefixId;
        $subPartArray["###HELP_IMAGE###"]               = t3lib_extMgm::siteRelPath($this->extKey).'images/help.gif';
        $subPartArray['###JOB_BANK_LOCATION###']        = $this->getCountryZone();
        $subPartArray['###QUALIFICATION###']            = $this->createDropDown($this->job_qualifiacation);
        $subPartArray['###SCRIPTNAME_POPUP_HELP_JS###'] = t3lib_extMgm::siteRelPath($this->extKey).'styles/overlib.js';
        $subPartArray["###SPONSOR_ID###"]               = $this->sponsorId;
        $subPartArray['###STATUS###']                   = $this->createDropDown($this->job_status);

        $content .= $this->cObj->substituteMarkerArrayCached($template['job_bank_add'],$subPartArray,array(),array());
        
        return $this->pi_wrapInBaseClass($content);

    }
    
    function saveDatainfo()
    {
        $insertFields = array(
            "tstamp"     => time(),
            "crdate"     => time(),
            "sponsor_id" => $this->__loggedInSponsorId,
            "pid"        => $this->lConf['storagePID']
        );
        
        foreach(t3lib_div::_POST() as $nameArr => $valArr)
        {
            switch ($nameArr) {
                case 'action':
                case 'industry_oth':
                case 'tx_sponsorcontentscheduler_pi1':
                    continue;
                case 'starttime':
                case 'endtime':
                    $insertFields[$nameArr] = $this->date2timestamp($valArr);
                    break;
                case 'zone_location':
                    $insertFields[$nameArr] = $this->getCountryId($valArr);
                    break;
                default:
                    $insertFields[$nameArr] = "$valArr";
            }
        }
        
        global $TYPO3_DB;
        $TYPO3_DB->exec_INSERTquery($this->tableName, $insertFields);

        // don't call save again
        unset($this->piVars['mode']);
    }
    
    function updateDatainfo()
    {
        $where = 'uid='.t3lib_div::_POST('uid');
        $updateFields = array(
            'position_filled' => 0,
            'hidden'          => 0,
            'tstamp'          => time()
        );
        foreach(t3lib_div::_POST() as $nameArr => $valArr)
        {
            switch ($nameArr) {
                case 'action':
                case 'uid':
                case 'industry_oth':
                case 'tx_t3consultancies_cat_uid':
                case 'tx_sponsorcontentscheduler_pi1':
                    continue;
                case 'starttime':
                case 'endtime':
                    $updateFields[$nameArr] = $this->date2timestamp($valArr);
                    break;
                case 'zone_location':
                    $updateFields[$nameArr] = $this->getCountryId($valArr);
                    break;
                default:
                    $updateFields[$nameArr] = "$valArr";
            }
            
        }
        global $TYPO3_DB;
        $TYPO3_DB->exec_UPDATEquery($this->tableName, $where, $updateFields);

        // don't call update again
        unset($this->piVars['mode']);
    }
    
    function date2timestamp($timeStamp)
    {
        
        list($d, $m, $y) = explode("-", $timeStamp);
        return mktime(0, 0, 0, $m, $d, $y);
    }
    
    function showListings()
    {
        $subpartArray=array();
        $content='';
        $template['job_bank_listing'] = $this->cObj->getSubpart($this->__getTemplateCode(),"###LISTINGS_PLACEHOLDER###"); 
        
        $template['###HEADER_INFO###'] = $this->cObj->getSubpart($template['job_bank_listing'],"###HEADER_INFO###");
        
        $template['DISPLAY_LIST'] = $this->cObj->getSubpart($template['job_bank_listing'],"###DISPLAY_LIST###"); 
        
        $subpartArray['###HEADER_INFO###']=$this->generateHeaders($this->displayFieldsJob);
        
        $res = false;
        if ($this->sponsorId)
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($this->generateHeaders($this->displayFieldsJob,1).'pid, uid', $this->tableName, 'deleted=0 and sponsor_id='.$this->sponsorId, '', '');
        $colDisplayFieldArr=$this->generateHeaders($this->displayFieldsJob,2);
        
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
        
        $data_row_displ='';
        if ($res) {
            if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
                $GLOBALS['TYPO3_DB']->sql_data_seek($res, $start);
            }
            
            while(($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) && $limit--)
            {
                    $data_row_displ.=$this->displayContents($row,$colDisplayFieldArr);      
            }
        }
        
        $subpartArray['###DATARECORDS###']=$data_row_displ;
        $subpartArray['###FORMNAMEEXTENSION###']=$this->prefixId;
        $subpartArray['###SPONSOR_ID###'] = $this->sponsorId;
        $content= $this->cObj->substituteMarkerArrayCached($template['job_bank_listing'],$subpartArray,array(),array());
        return $content.$tmp;
        
    }
    
    function displayContents($datarow,$col)
    {
        $dataRec = "<tr class='detailinfoinner'><td><input type='checkbox' name='$this->prefixId[selectionList][]' value='$datarow[uid]'></td>";
        $typolinkConfig = array('parameter' => $GLOBALS["TSFE"]->id);
        $LinkFlag = true;
        foreach ($col as $colName)
        {
            $typolinkConfig['additionalParams'] = "&$this->prefixId[action]=job_conf&$this->prefixId[mode]=edit&$this->prefixId[jobUid]=".$datarow['uid']."&$this->prefixId[sponsor_id]=".$this->sponsorId;
            switch($colName)
            {
                case 'zone_location':
                    $dataRec .= "<td>".htmlspecialchars($this->getCountryName($datarow[$colName]))."</td>";
                    break;
                case 'crdate':
                case 'starttime':
                case 'endtime':
                    $dataRec .= "<td>" . date('d-m-Y', $datarow[$colName]) . "</td>";
                    break;
                default:
                    if($LinkFlag)
                        $dataRec .= "<td>" . $this->cObj->typolink(htmlspecialchars($datarow[$colName]), $typolinkConfig) . "</td>";
                    else
                        $dataRec .= "<td>" . htmlspecialchars($datarow[$colName]) . "</td>";
            }
            $LinkFlag = false;
        }
        $dataRec .= "</tr>";
        return $dataRec;
    }
    
    /**
     * Creates a form for editing the Job listing
     *
     * @param   int     Job Id
     * @return  String      
     */

    function editForm($jobUid)
    {
        $flagJobOther=false;
        
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->tableName, 'uid='.$jobUid);
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
    
        $template['job_bank_edit'] = $this->cObj->getSubpart($this->__getTemplateCode(),"###EDIT_JOB_PLACEHOLDER###"); 
        
        $careerLevel=$this->getData('careerLevel');
        $qualification=$this->getData('qualification');
        $this->job_industry.="oth|Others||";
        
        
        $resCountryZone = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_country_iso_3,uid,zn_name_local','static_country_zones',"uid='".$row['location']."'");
        if($GLOBALS['TYPO3_DB']->sql_num_rows($resCountryZone)!='')
        {
            $row1=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountryZone);
            $_selectedCountry=$row1['zn_country_iso_3'];
            
        } else {
            $_selectedCountry = $this->getCountryIso3($row['zone_location']);
        }
        
        $subPartArray['###ADDITIONAL_REQUIREMENT###']       = $row['additional_requirement'];
        $subPartArray["###BACKPATH###"]                     = $this->cObj->typoLink_URL(array(
                                                                'parameter' => $GLOBALS['TSFE']->id,
                                                                'additionalParams' => '&'
                                                                    . $this->prefixId
                                                                    . '[action]=job_conf&'
                                                                    . $this->prefixId
                                                                    . '[sponsor_id]='
                                                                    . $this->sponsorId));
        $subPartArray['###CAREERLEVEL###']                  = $this->createDropDown($this->job_careerlevel,$row['clevel']);
        $subPartArray['###COMPANY_DESCRIPTION###']          = $row['company_description'];
        $subPartArray['###FORMNAMEEXTENSIONJOB###']         = $this->prefixId;
        $subPartArray['###FORMNAMEEXTENSION###']            = $this->prefixId;
        $subPartArray["###HELP_IMAGE###"]                   = t3lib_extMgm::siteRelPath($this->extKey).'images/help.gif';
        $subPartArray['###JOB_BANK_CITY###']                = $row['city'];
        $subPartArray['###JOB_BANK_CITY###']                = $row['city'];
        $subPartArray['###JOB_BANK_LOCATION###']            = $this->getCountryZoneEdit($_selectedCountry);
        $subPartArray['###JOB_CLOSETIME###']                = date('d-m-Y', $row['endtime']);
        $subPartArray['###JOB_INDUSTRY###']                 = $row['industry'];
        $subPartArray['###JOB_OVERVIEW###']                 = $row['joboverview'];
        $subPartArray['###JOB_STARTTIME###']                = date('d-m-Y', $row['starttime']);
        $subPartArray['###JOB_UID###']                      = $jobUid;
        $subPartArray['###MAJOR_RESPONSIBILITIES###']       = $row['major_responsibilities'];
        $subPartArray['###OCCUPATIONNAME###']               = $row['occupation'];
        $subPartArray['###QUALIFICATION###']                = $this->createDropDown($this->job_qualifiacation,$row['qualification']);
        $subPartArray['###SCRIPTNAME_POPUP_HELP_JS###']     = t3lib_extMgm::siteRelPath($this->extKey).'styles/overlib.js';
        $subPartArray['###SELECTEDCOUNTRYINFO###']          = $_selectedCountry;
        $subPartArray['###SELECTEDCOUNTRYINFOSELECTED###']  = $_selectedCountry;
        $subPartArray['###SELECTEDCOUNTRYINFO_SUB###']      = $row['location'];
        $subPartArray['###SELECTED_COUNTRY###']             = $_selectedCountry;
        $subPartArray['###SELECTED_STATE###']               = $row['location'];
        $subPartArray["###SPONSOR_ID###"]                   = $this->sponsorId;
        $subPartArray['###STATUS###']                       = $this->createDropDown($this->job_status,$row['status']);
        
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
        
        $content= $this->cObj->substituteMarkerArrayCached($template['job_bank_edit'],$subPartArray,array(),array());
        return $content;

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
    
    /**
     * Deletes a Job from Job listing
     *
     * @return  String      
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
    
    function getCountryIso3($countryId)
    {
        if (!$countryId)
            return false;
        global $TYPO3_DB;
        $res = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'cn_iso_3',
            // FROM
            'static_countries',
            // WHERE
            'uid = ' . $countryId);
        if ($TYPO3_DB->sql_num_rows($res) == 0)
            return false;
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        return $row['cn_iso_3'];
    }
    
    function getCountryName($countryId)
    {
        if (!$countryId)
            return 'Location not specified';
    
        global $TYPO3_DB;
        $resCountryZone = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'cn_short_en,
             cn_iso_3',
            // FROM
            'static_countries',
            // WHERE
            "uid = " . $countryId);

        if ($TYPO3_DB->sql_num_rows($resCountryZone) == 0)
            return 'Location not specified';

        $row = $TYPO3_DB->sql_fetch_assoc($resCountryZone);

        return $row['cn_short_en']." (".$row['cn_iso_3'].")";
    }

    function getCountryId($countryISO3)
    {
        global $TYPO3_DB;
        $res = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'uid',
            // FROM
            'static_countries',
            // WHERE
            sprintf("cn_iso_3 = '%s'", mysql_real_escape_string($countryISO3)));
        if ($TYPO3_DB->sql_num_rows($res) == 0)
            return 0;
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        return $row['uid'];
    }
    
    /**
     * Get the country name according to zone
     *
     * @param integer $zoneFlag
     * @param string $selected
     * @return unknown
     */
    function getCountryZone($zoneFlag=0,$selected='')
    {
        $varReturnVal='';
        if($zoneFlag)
        {
            $resZone = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_country_iso_3,uid,zn_name_local','static_country_zones',"zn_name_local!=''",'','zn_country_iso_3, zn_name_local');
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
            $resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3",'static_countries',"cn_iso_3 not in (".$ValCountryZone.")");
            while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountry))
            {
                $varReturnVal.="\n\nvar Menu$row[cn_iso_3]Menu=new Array(new Array(\"Select a State\",\"\"))";
            }
        }
        else
        {
            //echo $GLOBALS['TYPO3_DB']->SELECTquery("cn_iso_3,cn_short_en",'static_countries',"cn_short_en!=''",'','cn_short_en');
            $resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3,cn_short_en",'static_countries',"cn_short_en!=''",'','cn_short_en');
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
            $resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery("cn_iso_3,cn_short_en",'static_countries',"cn_short_en!=''",'','cn_short_en');
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
    
    function  getData ($keyName,$reverse=FALSE)
    {
        $data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        return $data[$keyName];
    }
    
    function __getSponsorId($returnField=''){
        $table="$this->tb_user,$this->tb_consultancies";
        $columns="$this->tb_user.username,$this->tb_consultancies.uid,$this->tb_consultancies.title, $this->tb_consultancies.logo,$this->tb_consultancies.fe_owner_user" ;
        //$where="fe_users.uid='$user_id' AND fe_users.tx_sponsorcontentscheduler_sponsor_id=tx_t3consultancies.uid";
        $where="$this->tb_user.uid='$this->__loggedInUserId' AND $this->tb_consultancies.uid=$this->tb_user.tx_sponsorcontentscheduler_sponsor_id";
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        if($result){
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        }
        if($returnField==''){
            return $row['uid'];
        }else{
            return $row[$returnField];
        }
    }
    
    function __getSponsorRec($field=''){
        if($field ==''){
            $field = 'uid';
        }
        $sponsorName=($this->__getSponsorId($field)=='')?$this->__getSponsorById($field):$this->__getSponsorId($field);
        return $sponsorName;
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
    
    function _debug($arr)
    {
        echo "<PRE>";
        var_dump($arr);
        echo "</PRE>";
    }
    
    function __getTemplateCode(){
        $_templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/jobbank/main.html');
        return $_templateCode;
    }
    
    function __getFlexFormField(){
        $this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
         $this->lConf = array(); // Setup our storage array...
         // Assign the flexform data to a local variable for easier access
         $piFlexForm = $this->cObj->data['pi_flexform'];
         // Traverse the entire array based on the language...
         // and assign each configuration option to $this->lConf array...
         foreach ( $piFlexForm['data'] as $sheet => $data ){
            foreach ( $data as $lang => $value ){
                foreach ( $value as $key => $val ){
                    $this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
                }
            }
         }
         
         return $this->lConf;
    }
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/classes/class.tx_sponsorcontentschedular_jobbank.php"])  {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/classes/class.tx_sponsorcontentschedular_jobbank.php"]);
}

?>
