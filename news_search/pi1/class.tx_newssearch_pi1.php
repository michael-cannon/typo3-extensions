<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Ritesh Gurung (ritesh@srijan.in)
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
 * Plugin 'News Search' for the 'news_search' extension.
 *
 * @author Ritesh Gurung <ritesh@srijan.in>
 */


//	require_once(PATH_tslib."class.tslib_pibase.php");
require_once(t3lib_extMgm::extPath('news_search')."class.ux_tslib_pibase.php");

class tx_newssearch_pi1 extends ux_tslib_pibase {
    var $prefixId = "tx_newssearch_pi1";
    // Same as class name
    var $scriptRelPath = "pi1/class.tx_newssearch_pi1.php"; // Path to this script relative to the extension dir.
    var $extKey = "news_search"; // The extension key.
    var $tabletSaveSearch = "tx_newssearch_result";
    var $tablettNews = "tt_news";
    var $tablettNewsCat = "tt_news_cat";
    var $tablettNewsCatMm = "tt_news_cat_mm";
    var $debug = false;
    var $cmdKey;
    var $catToLandingPage = array();
    var $_startUp;
	

    function init()
    {
		if ( true && ('59.94.211.80' == $_SERVER[ 'REMOTE_ADDR' ] 
			|| '70.84.110.68' == $_SERVER[ 'REMOTE_ADDR' ]
//			|| '71.255.125.178' == $_SERVER[ 'REMOTE_ADDR' ]
			)
		) {
			$this->debug = true;
		}

		//For Paging
        $this->internal['results_at_a_time'] = $this->conf['pagingSize'];
        $this->internal['maxPages'] = 7;
        $this->internal['dontLinkActivePage'] = true;
        $this->internal['pagefloat'] = 'center';
        $catgTT_News=$this->getCategory_ttnews('',1);
        $catgList='';
        if ($this->checkEmptyRecords($catgTT_News)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($catgTT_News)) {
                $catgList.=$row['uid'].',';
            }
        }
        $catgList=substr($catgList,0,-1);
        $this->conf['SearchCategory']=$catgList;

        global $TSFE;
        $bpm_or_soa = "bpm";
        if (strstr($TSFE->config['config']['baseURL'], "soainstitute"))
            $bpm_or_soa = "soa";
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf("
            SELECT
                uid,
                CASE WHEN tx_newssearch_%s_landing_page = '' THEN
                    tx_newssearch_bpm_landing_page
                ELSE
                    tx_newssearch_%s_landing_page
                END
            FROM
                tt_news_cat
            ", $bpm_or_soa, $bpm_or_soa));
        while ($row = $TYPO3_DB->sql_fetch_row($res))
            if (is_numeric($row[1]))
                $this->catToLandingPage[$row[0]] = $row[1];

    }

    function main($content, $conf) {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->init();
        $typolink_conf = array(
                "parameter" => $GLOBALS['TSFE']->id,
                );
        //echo $this->cmdKey;
        //Get Template file
        if($this->conf['templateFile']!='')
        {
            $this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
        }
        else
        {
            $this->templateCode = $this->cObj->fileResource("EXT:$this->extKey/news_search.tmpl");
        }

        //			$_postVarFrm = t3lib_div::_POST($this->extKey);
        //$this->_debug(t3lib_div::_GP($this->extKey));
        if( t3lib_div::_GP($this->extKey) )
        {
            $_postVarFrm=t3lib_div::_GP($this->extKey);
            $GLOBALS["TSFE"]->fe_user->setKey('ses',$this->extKey,$_postVarFrm);
        }
        // MLC don't recall from session unless browsing
        elseif (array_key_exists('pointer', $this->piVars))
        {
            $_postVarFrm=$GLOBALS["TSFE"]->fe_user->getKey('ses',$this->extKey); 
        }

        $template['headers'] = $this->cObj->getSubpart($this->templateCode, '###SEARCH_HEADER###');
        $template['saved_search'] = $this->cObj->getSubpart($this->templateCode, '###SAVED_SEARCH_RESULT###');
        $template['saved_search_data'] = $this->cObj->getSubpart($template['saved_search'], '###SAVED_SEARCH_DATA###');

        if (isset($_postVarFrm)) {
            $markerArray['###SEARCH_TEXT###'] = $_postVarFrm['search_text'];
            $_selected_cat = $_postVarFrm['category'];
            $_selected_style = $_postVarFrm['style'];
            $_resultSearch = $this->processResult($_postVarFrm);
            $markerArray['###CATEGORY_LIST###'] = $this->getCategory_ttnews($_selected_cat);

        } else {
            $markerArray['###SEARCH_TEXT###'] = '';
            $markerArray['###CATEGORY_LIST###'] = $this->getCategory_ttnews();
        }

        switch(t3lib_div::_GP('action'))
        {
            case 'saveresult':
                $resCheckSearchName =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->tabletSaveSearch,'title="'.t3lib_div::_POST('search_name').'"');
                if(!$this->checkEmptyRecords($resCheckSearchName))
                {
                    $_fieldsInsert=array(
                            "tstamp"=>time(),
                            "pid"=>29,
                            "crdate"=>time(),
                            "title"=>t3lib_div::_POST('search_name'),
                            "user_id"=>$GLOBALS['TSFE']->fe_user->user['uid']
                            );
                    $_postVarFrmSaveSearch=t3lib_div::_POST($this->extKey);
                    while(list($key,$val)=each($_postVarFrmSaveSearch))
                    {
                        $arrKeyVal='';
                        if(is_array($val))
                        {
                            foreach($val as $newKey)
                            {
                                $arrKeyVal.=$newKey.",";
                            }
                            $val=substr($arrKeyVal,0,strlen($arrKeyVal)-1);
                        }
                        $_fieldsInsert[$key]=$val;
                    }
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tabletSaveSearch,$_fieldsInsert);
                }
                break;
            case 'removeSearch':
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tabletSaveSearch,'uid='.t3lib_div::_GP('search_id'),array("deleted"=>1));
                break;

        }
        if(t3lib_div::_POST('action')=='saveresult')
        {
            $resCheckSearchName =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->tabletSaveSearch,'title="'.t3lib_div::_POST('search_name').'"');
            if(!$this->checkEmptyRecords($resCheckSearchName))
            {

                $_fieldsInsert=array(
                        "tstamp"=>time(),
                        "crdate"=>time(),
                        "title"=>t3lib_div::_POST('search_name'),
                        'user_id'=>$GLOBALS['TSFE']->fe_user->user['uid'],
                        );
                $_postVarFrmSaveSearch=t3lib_div::_POST($this->extKey);
                while(list($key,$val)=each($_postVarFrmSaveSearch))
                {
                    $arrKeyVal='';
                    if(is_array($val))
                    {
                        foreach($val as $newKey)
                        {
                            $arrKeyVal.=$newKey.",";
                        }
                        $val=substr($arrKeyVal,0,strlen($arrKeyVal)-1);
                    }
                    $_fieldsInsert[$key]=$val;
                }
                $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tabletSaveSearch,$_fieldsInsert);
            }

        }

        // Section to display saved search //////////////////////
        $_resultSavedSearchTemp='';
        $_resultSavedSearch='';
        if($GLOBALS['TSFE']->fe_user->user['uid']!='')
        {
            $resSavedSearchResult =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->tabletSaveSearch,"user_id=".$GLOBALS['TSFE']->fe_user->user['uid'].$this->cObj->enableFields($this->tabletSaveSearch),'','title'); 
            if($this->checkEmptyRecords($resSavedSearchResult))
            {
                if($this->debug) {debug($resSavedSearchResult);}
                while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSavedSearchResult))
                {
                    $_additionalParams='';
                    if($row['search_text']!='')
                        $_additionalParams.="&$this->extKey[search_text]=".$row['search_text'];
                    if($row['category']!='')
                        $_additionalParams.="&$this->extKey[category][]=".$row['category'];	
                    if($row['style']!='')
                        $_additionalParams.="&$this->extKey[style]=".$row['style'];

                    $typolink_conf['additionalParams']=$_additionalParams;
                    $markerArraySearchRes['###SAVED_SEARCH_LIST###']=$this->cObj->typoLink($row['title'],$typolink_conf);
                    $typolink_conf['additionalParams']="&search_id=".$row['uid']."&action=removeSearch";
                    $markerArraySearchRes['###SAVED_SEARCH_OPTION###']=$this->cObj->typoLink("Remove",$typolink_conf);
                    $_resultSavedSearchTemp.= $this->cObj->substituteMarkerArrayCached($template['saved_search_data'],$markerArraySearchRes,array(),array());


                }
            }
            $markerArraySearchResTemp['###SAVED_SEARCH_DATA###']=$_resultSavedSearchTemp;				
            $_resultSavedSearch = $this->cObj->substituteMarkerArrayCached($template['saved_search'], array(), $markerArraySearchResTemp, array());
        }
        // Section Ends ////////////////////////////////////////////
        $markerArray['###FORMACTION###'] = $this->pi_getPageLink($GLOBALS["TSFE"]->id, $GLOBALS["TSFE"]->sPre);
        $markerArray['###FORMEXTENSION###'] = $this->extKey;

        //$markerArray['###STYLE_LIST###'] = $this->createOptionList(array('normal' => 'Normal', 'curriculum' => 'Curriculum'), $_selected_style);

        $content .= $this->cObj->substituteMarkerArrayCached($template['headers'], $markerArray, array(), array());
        $content .= $_resultSavedSearch;
        $content .= $_resultSearch;


        return $this->pi_wrapInBaseClass($content);
    }

    /**
     * This function is used to get the Category of News
     *
     * @param selected Category Used to select the category
     * @return String  Returns a string of option List
     */
    function getCategory_ttnews($selected_cat = array(),$returnRes=0) {

        //echo $selected_cat; //tmp debug
		$resArray = array();
        //$whereCategory="$this->tablettNewsCat.uid IN(".$this->conf['SearchCategory'].")".$this->cObj->enableFields($this->tablettNewsCat);
		$searchCategory = $this->conf['SearchCategory'];
		$inClause = (!empty($searchCategory) && $searchCategory!=0 ) 
					 ? "AND $this->tablettNewsCat.uid IN(".$this->conf['SearchCategory'].") "
					 : "";
        $whereCategory="$this->tablettNewsCat.title NOT LIKE '%featured%'  ".$this->cObj->enableFields($this->tablettNewsCat);
        //echo $whereCategory; //tmp debug
		$resCategory = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->tablettNewsCat, $whereCategory);
        if(1==$returnRes)
            return $resCategory;
        if ($this->checkEmptyRecords($resCategory)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCategory)) {
                $resArray[$row['uid']] = $row['title'];
            }
        }
        return $this->createOptionList($resArray, $selected_cat);

    }


    /**

    /**
     * This function is used to get the Category Name
     *
     * @param selected Category Used to select the category
     * @return String  Returns a string 
     */
    function getCategory_ttnews_byName($selected_cat = '') {

        $resArray = array();
        $resCategory = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title', $this->tablettNewsCat, '1=1  and '.$this->tablettNewsCat.'.uid = '.$selected_cat.' '.$this->cObj->enableFields($this->tablettNewsCat));
        if(1==$returnRes)
            return $resCategory;
        if ($this->checkEmptyRecords($resCategory)) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCategory);
            return $row['title'];

        }
        else
            return '';

    }


    /**
     * Check if a table is empty
     *
     * @param Object  Resource id of table
     * @return boolean
     */
    function checkEmptyRecords($res) {
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == '' || $GLOBALS['TYPO3_DB']->sql_num_rows($res)=== NULL)
            return false;
        else
            return true;
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
        while (list($optionVal, $optionName) = each($res)) 
        {                 
            if(count($selected)>0)
            {                     
                if (in_array($optionVal,$selected))
                    $optionList .= "<option value='$optionVal' selected>$optionName</option>\n";
                else
                    $optionList .= "<option value='$optionVal'>$optionName</option>\n";                 }
            else
                $optionList .= "<option value='$optionVal'>$optionName</option>\n";

        }
        return $optionList;
    }

    /**
     * Process the result based on the submitted values through search
     *
     * @param array  array of submitted values
     * @return String  String of contents replaced in Template
     */
    function processResult($submitValues) {

        if ($submitValues['search_text'] != '') {
            //$tableFields = $GLOBALS['TYPO3_DB']->admin_get_fields($this->tablettNews);
            $tableFields=array("title","category","short","bodytext","author","author_email","news_files","title","keywords");
            foreach ($tableFields as $fieldsName => $value){
                //$where_condition_level .= "$this->tablettNews.$value like '%".$submitValues['search_text']."%' or ";
                $conditionBuild=$this->getNewCondition($submitValues['search_text'],$this->tablettNews.".".$value);
                $where_condition_level .= "($conditionBuild) or ";
            }
            $where_condition_level = " and (".substr($where_condition_level, 0, -3).")";
        }
        //$this->_debug($where_condition_level);			
        //$this->_debug($submitValues['category']);
        $subPartArray['###SEARCH_PAGER###']='';
        $_whereCondition .= $where_condition_level.$this->cObj->enableFields($this->tablettNews);
        $_fieldsDistinct = "DISTINCT COUNT(*) as numRec";
        $_fields = "$this->tablettNews.*,$this->tablettNewsCat.title as cat_title,$this->tablettNewsCat.uid as cat_id";
        $_tables = "$this->tablettNews,$this->tablettNewsCat";
        $_tables_mm = "$this->tablettNewsCatMm";
        $_groupBy='';
        $_orderBy="";
        $_postPointer=t3lib_div::_GET($this->extKey);

        //$this->_debug($HTTP_GET_VARS);
        //$this->_debug($_postPointer);
        $_startPointer=0;
        if($this->piVars['pointer'])
            $_startPointer =$this->piVars['pointer'] * $this->internal['results_at_a_time'];
        //			$this->_debug($_startPointer);
        $_limit = "$_startPointer, ".$this->internal['results_at_a_time'];
        //$this->_debug($_limit);
        if($submitValues['style']=='curriculum')
        {
            $_groupBy=$this->tablettNews.'.category';
            $_orderBy='('.$this->tablettNews.'.title+'.$this->tablettNews.'.datetime)';
        }

        $template['search_result'] = $this->cObj->getSubpart($this->templateCode, '###SEARCH_RESULT###');
        $template['search_result_nodata'] = $this->cObj->getSubpart($this->templateCode, '###SEARCH_RESULT_NODATA###');

        $_templateNoData=$this->cObj->substituteMarkerArrayCached($template['search_result_nodata'], array(), array(), array());
        //Initialising the Templates
        $resCategoryAll=$this->getCategory_ttnews('',1);
        if ($this->checkEmptyRecords($resCategoryAll)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCategoryAll)) {
                $subPartArray['###SEARCH_RESULT_DATA_'.$row['uid'].'###']= '';
                $subPartArray['###SEARCH_RESULT_DATA_TITLE_'.$row['uid'].'###']= '';
            }
        }
        $subPartArray['###SAVE_SEARCH_TEXT###']='';
        if($GLOBALS['TSFE']->fe_user->user['uid']!='')
        {
            $subPartArray['###SAVE_SEARCH_TEXT###']='<a href="#" onclick="document.savesearchresult.search_name.value=prompt(\'Please enter the search name\');document.savesearchresult.submit();">Save Search</a>';
        }
        $subPartArray['###SEARCH_RESULT_DATA_DEFAULT###']= '';
        $subPartArray['###SEARCH_RESULT_DATA_TITLE_DEFAULT###']= '';

        // Log search for later reference and statistics
        if (!array_key_exists("pointer", $this->piVars) && (strlen($submitValues['search_text']) > 0)) {
            global $TYPO3_DB;
            $TYPO3_DB->exec_INSERTquery("tx_newssearch_log",
                    array("user" => $TSFE->fe_user->user['uid'],
                        "search_string" => $submitValues['search_text'],
                        "crdate" => time()));
        }

        if(is_array($submitValues['category']) && $submitValues['category'][0]!='all')
        {
            $_CatgSelected='';
            foreach($submitValues['category'] as $catVal)
            {
                $_CatgSelected.=$catVal.",";	
            }
            $_CatgSelected=substr($_CatgSelected,0,strlen($_CatgSelected)-1);

            $_whereConditionInLoop=$_whereCondition ." and $this->tablettNewsCatMm.uid_foreign IN (".$_CatgSelected. ") ".$this->cObj->enableFields($this->tablettNews);

            $resCount=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($_fieldsDistinct, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereConditionInLoop,$_groupBy,"$this->tablettNewsCat.uid, $this->tablettNews.datetime DESC");
            $res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($_fields, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereConditionInLoop,$_groupBy,"$this->tablettNewsCat.uid, $this->tablettNews.datetime DESC",$_limit);
            //$this->_debug($GLOBALS['TYPO3_DB']->SELECT_mm_query($_fields, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereConditionInLoop,$_groupBy,"$this->tablettNewsCat.uid",$_limit));

            $data_detail = '';

            $_lastCat='';
            if($this->checkEmptyRecords($res))
            {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

                    //Check for linking page
                    if($this->checkKeyExist($row['cat_id'], $this->catToLandingPage))
                    {
                        $linkPageId=$this->catToLandingPage[$row['cat_id']];
                    }					
                    else 
                    {
                        $linkPageId=$this->catToLandingPage[$this->newsCatLookup($row['uid'])]; 
                    }

                    if($_lastCat!=$row['cat_id'])
                    {
                        $template['search_result_data_title'] = $this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_TITLE_".$row['cat_id']."###");
                        if(strlen($template['search_result_data_title'])<1 || is_null($template['search_result_data']))
                        {
                            $template['search_result_data_title'] = $this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_TITLE_DEFAULT###");
                            $subPartArrayTitle['###CATEGORY_TITLE###'] = $this->getCategory_ttnews_byName($row['cat_id']);
                            $subPartArray["###SEARCH_RESULT_DATA_DEFAULT###"] .=$this->cObj->substituteMarkerArrayCached($template['search_result_data_title'], $subPartArrayTitle, array(), array());

                        }else{
                            $subPartArrayTitle['###CATEGORY_TITLE###'] = $this->getCategory_ttnews_byName($row['cat_id']);
                            $subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"] .=$this->cObj->substituteMarkerArrayCached($template['search_result_data_title'], $subPartArrayTitle, array(), array());
                        }

                    }
                    $_lastCat=$row['cat_id'];

                    $template['search_result_data']=$this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_".$row['cat_id']."###");
                    if(strlen($template['search_result_data'])<1 || is_null($template['search_result_data']))
                    {
                        $template['search_result_data']=$this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_DEFAULT###");
                        $subPartArray["###SEARCH_RESULT_DATA_DEFAULT###"] .= $this->prepareResult($template['search_result_data'], $row,$linkPageId);
                    }else{

                        $subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"] .= $this->prepareResult($template['search_result_data'], $row,$linkPageId);
                    }
                }
                //$subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"]=$data_detail;
            }

            if($resCount)
            {
                $rowCount=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCount);
                $this->internal['res_count'] = $rowCount['numRec'];
            }
            $pagerResult=trim(strip_tags($this->pi_list_browseresults()));
            if($pagerResult!="Displaying results 0 to 0 out of 0"){
                $subPartArray['###SEARCH_PAGER###']=$this->pi_list_browseresults();
            }else{
                $subPartArray['###SEARCH_PAGER###']=$template['search_result_nodata'];
            }




        }
        else
        {
            //$_fields.=",  $this->tablettNewsCat.uid as cat_id";
            //				$_fieldsDistinct="distinct(".$this->tablettNewsCat.".uid) as cat_id";
            //$this->_debug($this->conf['SearchCategory']); //tmp debug
			//$this->conf['SearchCategory'] = "48,49,50,52,56,57,71,78,109";
	        $_fieldsDistinct = "COUNT(*) as numRec";
			$_groupBy="$this->tablettNews.title";
			$_orderBy="";
            $_whereCondition .= " and $this->tablettNewsCat.uid IN (".$this->conf['SearchCategory'].")".$this->cObj->enableFields($this->tablettNews);

            $resCount=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($_fieldsDistinct, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereCondition,$_groupBy,$_orderBy);
			// $query = $GLOBALS['TYPO3_DB']->SELECT_mm_query($_fieldsDistinct, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereCondition,$_groupBy,$_orderBy);
			// cbDebug( 'query', $query );	

            $_orderBy="$this->tablettNews.datetime DESC, $this->tablettNewsCat.uid";
            $_orderBy="$this->tablettNewsCat.uid ASC, $this->tablettNews.datetime DESC";
            $res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($_fields, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereCondition,$_groupBy,$_orderBy,$_limit);
            // $query = $GLOBALS['TYPO3_DB']->SELECT_mm_query($_fields, $this->tablettNews, $_tables_mm, $this->tablettNewsCat, $_whereCondition,$_groupBy,$_orderBy,$_limit);
			// cbDebug( 'query', $query );	

            $data_detail = '';
            $_lastCat='';
            if($this->checkEmptyRecords($res))
            {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

                    //Check for linking page
                    if($this->checkKeyExist($row['cat_id'], $this->catToLandingPage))
                    {
                        $linkPageId=$this->catToLandingPage[$row['cat_id']];
                    }					
                    else 
                    {
                        // $linkPageId=$GLOBALS['TSFE']->id;
                        $linkPageId=$this->catToLandingPage[$this->newsCatLookup($row['uid'])]; 
                    }

                    if($_lastCat!=$row['cat_id'])
                    {
                        $template['search_result_data_title'] = $this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_TITLE_".$row['cat_id']."###");

                        if(is_null($template['search_result_data_title'])) {
                            $template['search_result_data_title'] = $this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_TITLE_DEFAULT###");

                            $subPartArrayTitle['###CATEGORY_TITLE###'] = $this->getCategory_ttnews_byName($row['cat_id']);
                            $subPartArray["###SEARCH_RESULT_DATA_DEFAULT###"] .=$this->cObj->substituteMarkerArrayCached($template['search_result_data_title'], $subPartArrayTitle, array(), array());

                        }else{
                            $subPartArrayTitle['###CATEGORY_TITLE###'] = $this->getCategory_ttnews_byName($row['cat_id']);
                            $subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"] .=$this->cObj->substituteMarkerArrayCached($template['search_result_data_title'], $subPartArrayTitle, array(), array());
                        }

                    }
                    $_lastCat=$row['cat_id'];

                    $template['search_result_data']=$this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_".$row['cat_id']."###");
                    if(strlen($template['search_result_data'])<1 && is_null($template['search_result_data']))
                    {
                        $template['search_result_data']=$this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_DEFAULT###");
                        $subPartArray["###SEARCH_RESULT_DATA_DEFAULT###"] .= $this->prepareResult($template['search_result_data'], $row,$linkPageId);
                    }else{

                        $subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"] .= $this->prepareResult($template['search_result_data'], $row,$linkPageId);
                    }
                }
                //$subPartArray["###SEARCH_RESULT_DATA_".$row['cat_id']."###"]=$data_detail;
            }

            if($resCount)
            {
                $this->internal['res_count'] = 0;
				while( $resCount &&
					$rowCount=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCount))
				{
                	$this->internal['res_count']++;
				}
            }
            //$subPartArray['###SEARCH_PAGER###']=$this->pi_list_browseresults();
            $pagerResult=trim(strip_tags($this->pi_list_browseresults()));
            if($pagerResult!="Displaying results 0 to 0 out of 0"){
                $subPartArray['###SEARCH_PAGER###']=$this->pi_list_browseresults();
            }else{
                $subPartArray['###SEARCH_PAGER###']=$template['search_result_nodata'];
            }
        }
        $_returnTraverse=false;
        $_lastMarkerId='';
        foreach($subPartArray as $label=>$labelVal)
        {
            if(eregi('###SEARCH_RESULT_',$label))
                $_returnTraverse = true;
            $_lastMarkerId = $label;
        }
        if(!$_returnTraverse)
        {
            $this->_debug($subPartArray);
            $subPartArray[$_lastMarkerId] = $_templateNoData;
        }

        $_hiddenfrmField='';
        foreach($submitValues as $key=>$val)
        {
            if($key!='search')
            {
                if(is_array($val))
                {
                    foreach($val as $keyVal)
                        $_hiddenfrmField.="<input type='hidden' name='$this->extKey[$key][]' value='$keyVal'>\n";
                }
                else
                {
                    $_hiddenfrmField.="<input type='hidden' name='$this->extKey[$key]' value='$val'>\n";
                }
            }
        }
        $_hiddenfrmField.="<input type='hidden' name='action' value='saveresult'>\n";

        $subPartArray['###FORMACTION###'] = $this->pi_getPageLink($GLOBALS["TSFE"]->id, $GLOBALS["TSFE"]->sPre);
        $subPartArray['###HIDDEN_FORM_FILEDS###'] = $_hiddenfrmField;

        // Handle backlink
        $subPartArray['###BACKLINK###'] = '';
        if ($this->cObj->data['tx_newssearch_backlink_to_page']) {
            $backlinktemplate = $this->cObj->getSubpart($template['search_result'], '###BACKLINK###');
            $subPartArray['###BACKLINK###'] = $this->cObj->substituteMarkerArray(
                $backlinktemplate, array(
                    '###BACKLINK_URL###' => $this->pi_getPageLink($this->cObj->data['tx_newssearch_backlink_to_page'])));
        }

        $content = $this->cObj->substituteMarkerArrayCached($template['search_result'], array(), $subPartArray, array());
        //js get the crosssite results by topic:
		if ($this->debug) { print_r($this->conf); }
		if ($this->conf['showTopicResults']) { 
			$content = $this->getTopicWiseLinksContent($submitValues['search_text']) . $content;
		}
		//echo $content;
		return $content;

    }

	/**
	Returns a link to topic-wise landing pages.
	The end of the link displays search results that pertain only to a single, specified topic.
	
	@author Jaspreet Singh
	@param string the search text. (I.e., the terms that were searched for).
	@return HTML
	*/
	function getTopicWiseLinksContent($searchText) {
		
		if (empty($searchText)) {return '';}
		
		$TOPIC_TITLE_INDEX 		= 0;
		$TOPIC_CATEGORIES_INDEX 	= 1;
		
		//$searchCategory		= '48,49,50,52,56,57,71,78';
		//$anchorText			= 'Business Rules';
		//Note: Should be a TypoScript setting
		//$topicCategoriesSetup	= 'Business Rules:bpminstitute.org:544:48,49,50,52,56,57,71,78,109|Enterprise Architecture:bpminstitute.org:555:80,81,82,84,88,89,110|Organizational Performance:bpminstitute.org:549:58,59,60,62,66,67,72,77,108|Service-Oriented Architecture:soainstitute.org:339:38,39,40,42,46,47,69,73,76,101,103,105,107';
		//$topicCategoriesSetup	= 'Service-Oriented Architecture:soainstitute.org:Search for :<br>:339:38,39,40,42,46,47,69,73,76,101,103,105,107|Business Rules:bpminstitute.org:::544:48,49,50,52,56,57,71,78,109|Enterprise Architecture:bpminstitute.org:::555:80,81,82,84,88,89,110|Organizational Performance:bpminstitute.org:::549:58,59,60,62,66,67,72,77,108';
		$topicCategoriesSetup	= $this->conf['topicCategoriesSetup'];
		//Take the text-form topic-categories map and convert it to a PHP array
		$topicCategoriesArray1 = explode('|', $topicCategoriesSetup);
		//$topicCategoriesArray1 = explode($topicCategoriesSetup, ':');
		$topicCategoriesArray		= array();
		foreach($topicCategoriesArray1 as $item) {
			if ($this->debug) { echo $item . '<br>' ; }
			//$topicData 		= explode(':', $item);
			//$title			= $topicData[$TOPIC_TITLE_INDEX];
			//$categories	= $topicData[$TOPIC_CATEGORIES_INDEX];
			list($title, $siteURL, $anchorTextPrefix, $afterAnchorHTML, $pageID, $categories) = explode(':', $item);
			
			$topicCategoriesArray[]   = array(
				'title' 		=> $title
				,'siteURL'		=> $siteURL
				, 'categories' 	=> $categories
				, 'pageID'		=> $pageID
				, 'afterAnchorHTML'		=> $afterAnchorHTML
				, 'anchorTextPrefix'	=> $anchorTextPrefix
				);
			//$topicCategoriesArray[$title]	= $categories;
			//$topicCategoriesArray['title'] 		= $title;
			//$topicCategoriesArray['categories'] 	= $categories;
			//print_r($topicCategoriesArray);
		}
		if ($this->debug) { echo '$topicCategoriesArray'; print_r($topicCategoriesArray);}
		
		$linkContent = '';
		foreach( $topicCategoriesArray as $topic) {
			
			//$anchorText			= $topic[$TOPIC_TITLE_INDEX];
			//$searchCategory		= $topic[$title];
			$title					= $topic['title'];
			$searchCategory		= $topic['categories'];
			$anchorTextPrefix		= empty($topic['anchorTextPrefix']) ? 'Results for ' : $topic['anchorTextPrefix'];
			$afterAnchorHTML		= $topic['afterAnchorHTML'];
			$pageID					= $topic['pageID'];
			$siteURL				= $topic['siteURL'];
			
			if (false && $this->debug) {
				print_r($topic);
				echo 'inside loop';
				echo $title;
				echo $searchCategory;
			}

			$param_siteName 	= $_SERVER['HTTP_HOST'];
			$param_siteName 	= 'stage.bpminstitute.org'; //TODO delete only for testing
			$param_siteName 	= 'www.' . $siteURL; //only for testing
			
			$param_index_php	= 'index.php';
			$param_pageID		= "?id=$pageID";
			$param_searchText	= "news_search[search_text]=$searchText";
			// MLC 20070611 enable if really needed. Add to additionalParams
			// $param_searchCategory	= "news_search[category][]=$searchCategory";
			$param_anchorText	= "$anchorTextPrefix '$searchText' at $title"; 

			$urlConfig			= array(
				'parameter' => $pageID,
				'additionalParams' => "&$param_searchText"
			);
			
			// $linkContent .= "<p><a href='http://$param_siteName/index.php$param_pageID&$param_searchText&$param_searchCategory'>$param_anchorText</a></p>$afterAnchorHTML";
			$linkContent .= '<p>';
			$linkContent .= $this->cObj->typolink($param_anchorText, $urlConfig);;
			$linkContent .= "</p>$afterAnchorHTML";
		}
		
		$linkContent = "<h1>Search Results by Topic</h1>" . $linkContent . '<br><br>';
		
		return $linkContent;
	}


	/**
     * To populate the records with the values in template
     *
     * @param String  Template Name
     * @param Object  Recordset
     * @return String
     */
    function prepareResult($template, $row,$linkId) {
        //debug($row);
        $template['search_result'] = $this->cObj->getSubpart($this->templateCode, '###SEARCH_RESULT###');
        if((strlen($template)<1) || (is_null($template)))
        {
            $template= $this->cObj->getSubpart($template['search_result'], "###SEARCH_RESULT_DATA_DEFAULT###");
        }
        //$this->_debug($template);
        $subPartArray['###IMAGE_FILENAME###']='';
        if($row['image']!='')
            $subPartArray['###IMAGE_FILENAME###'] = "<img src='uploads/pics/".$row['image']."' style='float: left; border: 1px #666666 solid; margin: 2px;' alt=".$row['title'].">";
		if ( 2 != $row['type'] )
		{
        	$urlConfig=array(
                'parameter' => $linkId,
                'additionalParams' => '&tx_ttnews[tt_news]=' . $row['uid']	. '&tx_ttnews[backPid]=' . $GLOBALS['TSFE']->id
                );
	        $subPartArray['###NEWS_TITLE###'] = $this->cObj->typolink('<b>'.$row['title'].'</b>', $urlConfig);
		}

		else
		{
	        $subPartArray['###NEWS_TITLE###'] = '<a href="' . $row['ext_url']
				. '" target="_blank"><b>' . $row['title'] . '</b></a>';
		}

        $subPartArray['###MORE_LINK###'] = $this->cObj->typolink('<b>more</b>', $urlConfig);;
        $_author_text='';
        if($row['author']!='')
            $_author_email.='<B>Author</B> : '.$row['author'];
        if($row['author_email']!='')
            $_author_email.=',&nbsp; <B>Email</B> : '.$row['author_email'];
        $subPartArray['###NEWS_AUTHOR_EMAIL###'] = $_author_email;
        $subPartArray['###COURTSEY###'] = $row['author'];
        $subPartArray['###NEWS_DATE###'] = strftime('%A %B %e, %Y', $row['datetime']);
        $content = $this->cObj->substituteMarkerArrayCached($template, $subPartArray, array(), array());
        //$this->_debug($urlConfig);
        return $content;

    }

    /**
     * To retreive the keyvalue from the extension configuration
     *
     * @param String  keyName for which the value is required
     * @return String
     */
    function getData($keyName) {
        $data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

        return $data[$keyName];
    }

    function checkKeyExist($_needle,$_heyStack)
    {
        //			$this->_debug($_needle);
        //			$this->_debug($_heyStack);
        while(list($_key,$_val)=each($_heyStack))
        {
            if($_key==$_needle)
                return true;
        }
        return false;
    }

    function getNewCondition($searchText,$fieldname)
    {
        // escape string 
        $searchText			= mysql_escape_string( $searchText );

        // check whitespace
        $searchText			= preg_replace( "#\s\+#", ' ', $searchText );

        // create array
        $searchTextArr		= explode( ' ', $searchText );

        $sql				= '';

        // should the term be included or not
        $skipTerm			= false;

        // cycle through array to create sql query
        $searchTextArrCount	= count( $searchTextArr );

        for ( $i = 0; $i < $searchTextArrCount; $i++ )
        {
            $key			= $i;
            $term			= $searchTextArr[ $key ];

            // check for multiple conditions in row
            // only run if current and next terms are conditionals
            // ignore all but last
            $condSearch		= "#^(\band\b|\bor\b|\bnot\b)$#i";

            if ( preg_match( $condSearch, $term )
                    && isset( $searchTextArr[ $key + 1 ] )
                    && preg_match( $condSearch, $searchTextArr[ $key + 1 ] )
               )
            {
                continue;
            }

            switch ( strtolower( $term ) ) 
            {
                // and >> AND LIKE
                case 'and':
                    $sql		.= " AND $fieldname LIKE ";
                    $skipTerm	= true;
                    break;

                    // not >> AND NOT LIKE
                case 'not':
                    $sql		.= " AND $fieldname NOT LIKE ";
                    $skipTerm	= true;
                    break;

                    // or >> OR LIKE
                case 'or':
                    $sql		.= " OR $fieldname LIKE ";
                    $skipTerm	= true;
                    break;
            }

            if ( ! $skipTerm )
            {
                // no prior sql
                if ( ! $sql )
                {
                    $sql	.= "$fieldname LIKE '%$term%'";
                }

                else
                {
                    // AND below sets default search set for inclusion
                    // essentially it means return only results with all of
                    // the terms searched for
                    // no prior condition
                    $sql	.= ( ! preg_match( "#'$#",$sql ) )
                        ? "'%$term%'"
                        : " AND $fieldname LIKE '%$term%'";
                }

            }

            $skipTerm		= false;
        }

        return $sql;
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

                    $links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),array($pointerName => '0')),$wrapper['inactiveLinkWrap']);

                } else {

                    $links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),$wrapper['disabledLinkWrap']);

                }

            }

            if ($alwaysPrev>=0)	{ // Link to previous page

                if ($pointer>0)	{

                    $links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText),array($pointerName => $pointer-1)).'</p></td>';

                } elseif ($alwaysPrev)	{

                    $links[]='<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText).'</p></td>';

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

                        $links[] ='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName  => $a)).'</p></td>';

                    }

                } else {

                    $links[] =  '<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($pageText,array($pointerName => $a)).'</p></td>';

                }

            }

            //Remove the last hanging pipe
            $links_last_element = count($links) - 1;
            //echo "LST ELEMENT:".$links[$links_last_element];
            $links[$links_last_element] = substr($links[$links_last_element], 0, -3).'</p>';
            //	echo "<BR>".$links[$links_last_element];


            if ($pointer<$totalPages-1 || $showFirstLast)	{

                if ($pointer==$totalPages-1) { // Link to next page

                    $links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),$wrapper['disabledLinkWrap']);

                } else {

                    $links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),array($pointerName => $pointer+1)),$wrapper['inactiveLinkWrap']);

                }

            }

            if ($showFirstLast) { // Link to last page

                if ($pointer<$totalPages-1) {

                    $links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),array($pointerName => $totalPages-1)).'&nbsp; &nbsp;| </p></td>';

                } else {

                    $links[]='<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText).' &nbsp;&nbsp; | </p></td>';

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

    function _debug($varIable)
    {
        echo "<PRE>";
        var_export($varIable);
        echo "</PRE>";
    }

	function newsCatLookup( $newsUid ) {
		$_whereCondition = "uid_local = $newsUid";
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery('uid_foreign', 'tt_news_cat_mm', $_whereCondition,'','sorting');
		// cbDebug( 'query', $query );	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', 'tt_news_cat_mm', $_whereCondition,'','sorting');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['uid_foreign'];
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/news_search/pi1/class.tx_newssearch_pi1.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/news_search/pi1/class.tx_newssearch_pi1.php"]);
}

?>
