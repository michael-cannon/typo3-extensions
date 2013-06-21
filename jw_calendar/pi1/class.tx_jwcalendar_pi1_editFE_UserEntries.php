<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Jens Witt (jwitt@witttec.de)
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
 * Plugin 'JW Calendar' for the 'jw_calendar' extension.
 *
 * @author	Jens Witt <jwitt@witttec.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_upcomingEventsView.php');


class tx_jwcalendar_pi1_editFE_UserEntries extends tx_jwcalendar_pi1_upcomingEventsView{
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';

	function tx_jwcalendar_pi1_editFE_UserEntries($cObj,$conf,$jwOptions){
	   $this->tx_jwcalendar_pi1_upcomingEventsView($cObj,$conf,$jwOptions);
	   $this->conf = $conf;
	   $this->cObj = $cObj;
       $this->jwOptions = $jwOptions;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function initChildClass(){
  	 	$this->jwOptions['fe_entry']['enable']  =0;
  	 	$this->jwOptions['entry_count'] =2000;
  	 	$this->jwOptions['categories']['show']=false;
  	 	$this->upcomingEventsTitle = $this->pi_getLL('feEntryPrevEventsTitle');
		return;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function editFeEntry(){
		$row = $this->querysingleFE_Entry($this->jwVars['edituid']);
		$dbFieldList = 'category, title, teaser, description, link, image, begin, end, location, organiser, email';
	    $dbFieldArray = preg_split("/[\s,]+/", $dbFieldList);
    	foreach($dbFieldArray as $field)
			 $this->jwVars[$field] = $row[$field];
		$View = $this->getClass('feEntryView');
		return $View->getEditForm();
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function confirmDelete(){
		$ta = $this->makeConfirm($this->jwVars);
	    $ta .= $this->hiddenInput('edituid', $this->jwVars['edituid']);
	    $ta .= $this->hiddenInput('uid', $this->jwVars['uid']);
        $ta .= $this->hiddenInput('parent',$this->jwVars['parent']);
        $ta .= $this->hiddenInput('parentview',$this->jwVars['parentview']);
        $ta .= $this->hiddenInput('parent_time',$this->jwVars['parent_time']);
	    $ta .= $this->hiddenInput('action', 'deleteFeEntry');
	    $GLOBALS['TSFE']->clearPageCacheContent_pidList($this->formpid);
		return $this->wrapForm($ta);

	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function deleteFeEntry(){
    	$table = 'tx_jwcalendar_events';
		$query = $this->cObj->DBgetDelete($table, $this->jwVars['edituid']);
	  	$res=$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$View = $this->getClass('feEntryView');
		return $View->getEditForm();
		//return $this->editFeEntry();
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$vars: ...
	 * @return	[type]		...
	 */
    function makeConfirm($vars){
  		$confirmFormT = $this->cObj->getSubpart($this->templateCode, '###confirmForm###');
		$sims['###CUT_dberror###'] = '';
		$sims['###CUT_success###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($confirmFormT, '###CUT_success###'),'###thxForEntryMessage###',$this->pi_getLL('ConfirmDeleteMessage'));
        $sims['###confirmEntry###'] = $this->submitInput($this->pi_getLL('yesButtonLabel'), 'submit',$this->pi_getLL('noButtonLabel'));
		return $this->cObj->substituteMarkerArrayCached($confirmFormT,array(),$sims);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$uid: ...
	 * @return	[type]		...
	 */
  	function querysingleFE_Entry($uid){
       /* Query Eventes */
        $query  = 'SELECT *';
        $query .= ', tx_jwcalendar_events.uid as uid';
        $query .= ', tx_jwcalendar_events.title as title';
        $query .= ', tx_jwcalendar_categories.title as category';
        $query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
        $query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
        $query .= ' WHERE 1';
       	$query .= " AND tx_jwcalendar_events.uid = '".$uid."'";
        $query .=  $this->enableFieldsCategories;
        $query .=  $this->enableFieldsEvents;
       	$query .= ' AND tx_jwcalendar_events.fe_user = '.$GLOBALS['TSFE']->fe_user->user['uid'];
	    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
	    return $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$sims: ...
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
  	function cuttersEvent($sims,$row){
   		 if(!$this->secure)return $sims;
  		 $title = $sims['###title###'];
	     $params = array('uid' => $this->jwVars['uid'], 'parent' => $this->jwVars['parent'],'parentview' => $this->jwVars['parentview'], 'parent_time' => $this->jwVars['parent_time'],'eventuid' => $this->jwVars['eventuid'], 'edituid' => $row['uid']);
		 $params['action']='confirmDeleteFeEntry';
	     $img = "<img src=\"".t3lib_extMgm::siteRelPath($this->extKey).'pi1/garbage.gif'."\" title=\"".$this->pi_getLL('deleteButtonLabel')."\" align=\"right\"/>" ;
		 $link = ' '.$this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid);
		 $params['action']='editFeEntry';
	     $img = "<img src=\"".t3lib_extMgm::siteRelPath($this->extKey).'pi1/edit2.gif'."\" title=\"".$this->pi_getLL('feEntryEditEventButtonLabel')."\" align=\"right\"/>" ;
		 $link .= $this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid);
		 $sims['###title###'] =	$link.$sims['###title###'];
  	 	 return $sims;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$dataset: ...
	 * @param	[type]		$num: ...
	 * @return	[type]		...
	 */
	function setForwardBackwardLink($dataset,$num){
   	    $sims['###Prev_Events###'] = '';
   	    $sims['###Next_Events###'] = '';
   	    return $sims;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$event: ...
	 * @return	[type]		...
	 */
  	function wrap2Form($event){
  		 return $event;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$eventuid: ...
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
   	function buildTemplateHeader($eventuid=0,$time=0){
   		$sims['###listTitle###'] = '';
    	$sims['###categoryTitle###'] ='';
    	return $sims;
   	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
  	function queryUpcomingEventsMessenger(){
  		$this->secure=true;
       /* Query Eventes */
		if($GLOBALS['TSFE']->fe_user->user['uid']<=0)return array();
		if($this->query)return $this->query;
		$query  = 'SELECT *';
        $query .= ', tx_jwcalendar_events.uid as uid';
        $query .= ', tx_jwcalendar_events.title as title';
        $query .= ', tx_jwcalendar_categories.title as category';
        $query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
        $query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
        $query .= ' WHERE 1';
        $query .=  $this->enableFieldsCategories;
        $query .=  $this->enableFieldsEvents;
       	$query .= ' AND tx_jwcalendar_events.fe_user = '.$GLOBALS['TSFE']->fe_user->user['uid'];
		return $this->jw_queryMySql($query);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_editFE_UserEntries.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_editFE_UserEntries.php']);
}

?>
