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
 *
 *
 *
 *   55: class tx_jwcalendar_pi1_upcomingEventsView extends tx_jwcalendar_pi1_library
 *   62:     function tx_jwcalendar_pi1_upcomingEventsView($cObj,$conf,$jwOptions)
 *   80:     function getForm()
 *  119:     function cuttersEvent($sims,$row)
 *  130:     function buildEvent($ItemT,$row)
 *  153:     function setCUT_submitEvent($ListViewT)
 *  170:     function buildTemplateHeader($eventuid=0,$time=0)
 *  187:     function buildTemplateArray($dataset)
 *  239:     function setForwardBackwardLink($dataset,$num)
 *  305:     function queryUpcomingEventsBase($select)
 *  330:     function jw_queryMySql($query)
 *  344:     function queryUpcomingEventsMessenger()
 *  368:     function queryOneDayEvents()
 *  389:     function queryFirstuidEvents()
 *  427:     function queryLastuidEvents()
 *  457:     function queryNewEntryInEvents()
 *
 * TOTAL FUNCTIONS: 15
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_jwcalendar_pi1_upcomingEventsView extends tx_jwcalendar_pi1_library {
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';
  	var $time;



	function tx_jwcalendar_pi1_upcomingEventsView($cObj,$conf,$jwOptions){
	 $this->conf = $conf;
	 $this->cObj = $cObj;
     $this->jwOptions = $jwOptions;
     $this->jwOptions['viewmode'] = 'LIST';

      $this->jwOptions['entry_count']  = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'entryCount','s_List_View');
	  if(!$this->jwOptions['entry_count'] )$this->jwOptions['entry_count'] =8;
	  $this->jwOptions['start_time'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startTime','s_List_View');
	  if(!$this->jwOptions['start_time'])$this->jwOptions['start_time']=time(); 
      //$this->jwOptions['entry_period']  = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'entryPeriod');
	}

  /* Workflows */

	function getForm(){
    $this->upcomingEventsTitle = $this->pi_getLL('upcomingEventsTitle');
   	$this->time = isset($time) ? $time : $this->jwOptions['start_time'];
    if($this->jwVars['uid'] != $this->uid){
    	$this->jwVars['begin']=0;
    	$this->jwVars['lastuid']=0;
   		$this->jwVars['firstuid']=0;
    }
	$this->parent_time=$this->jwVars['begin'];
    if($this->jwVars['time']){
		$this->parent_time = $this->jwVars['time'];
		$msgArray = $this->queryNewEntryInEvents();
    }
    else if($this->jwVars['lastuid'])
	{
		$msgArray = $this->queryLastuidEvents();
	}
	else if($this->jwVars['firstuid'])
	{
		$msgArray = $this->queryFirstuidEvents();
	}
    else if($this->jwVars['day'])
	{
		$msgArray = $this->queryOneDayEvents();
	}
	else
	{
		$msgArray = $this->queryUpcomingEventsMessenger();
	}

    $msgArray = $this->hscRecurs($msgArray); // make html special characters for all output

	$this->access=$this->checkFeEntry();

  	$ta = $this->buildTemplateArray($msgArray);
	if($this->jwOptions['fe_entry']['enable'] && $this->access){
			$ta .= $this->hiddenInput('eventuid',$this->eventuid);
	    	$ta .= $this->hiddenInput('parent_time',$this->parent_time);
			$ta = $this->wrap2Form($ta);
	}
  	return $ta;
  }


  /* Workers */

	//Zum Überladen von Childklassen
	function cuttersEvent($sims,$row){
		return $sims;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$ItemT: ...
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function buildEvent($ItemT,$row){
		$sims['###color###'] = $row['catcolor']?$this->cObj->wrap($row['catcolor'],$this->conf['wrapColorListView']):'';
	    $sims['###dateBegin###'] = strftime($this->jwOptions['date_format'], $row['begin']);
	    $sims['###timeBegin###'] = strftime($this->jwOptions['time_format'], $row['begin']);
	    $sims['###category###'] = $row['category'];
   		$rems['###CUT_teaser###'] = $row['teaser']?$this->cObj->substituteMarker($this->cObj->getSubpart($ItemT,'###CUT_teaser###'),'###teaser###',$row['teaser']):"";
        if(!empty($row['directlink'])){
			$sims['###readMore###'] = $this->pi_linkToPage($this->pi_getLL('readMoreLinkLabel'), $row['directlink']);
			$sims['###readMore###'] = $this->pi_linkToPage($row['title'], $row['directlink']);
        }else{
			$params = array('eventid' => $row['uid'],'uid' => $this->uid, 'action' => 'singleView');
		    if($row['event_type']>0)
				$params['day']=	strtotime(strftime('%Y-%m-%d',$row['begin']));
            $sims['###readMore###'] = $this->pi_linkTP($this->pi_getLL('readMoreLinkLabel'),Array($this->prefixId=>$params),$this->caching,$this->pidSingle);
            $sims['###title###'] = $this->pi_linkTP($row['title'],Array($this->prefixId=>$params),$this->caching,$this->pidSingle);
        }
		$sims = $this->cuttersEvent($sims, $row);
		return $this->cObj->substituteMarkerArrayCached($ItemT,$sims,$rems);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$ListViewT: ...
	 * @return	[type]		...
	 */
	function setCUT_submitEvent($ListViewT){
		if($this->jwVars['action'] == 'dayView'){
       		$sims['###submitEvent###'] = $this->submitBack($this->pi_getLL('backButtonLabel'));
		}else if($this->jwOptions['fe_entry']['enable'] && $this->access){
	   		if($this->access>0)
   		   		$this->formpid = $this->access;
			$sims['###submitEvent###'] = $this->access?$this->submitInput($this->pi_getLL('feEntrySubmitEventButtonLabel')):"";
		}
		if($sims['###submitEvent###']){
	  	    $CutterT = $this->cObj->getSubpart($ListViewT, '###CUT_submitEvent###');
			$out = $this->cObj->substituteMarkerArray($CutterT,$sims);
		}
		$rems['###CUT_submitEvent###']=$out;
		return $rems;
	}

//create Header with title
	function buildTemplateHeader($eventuid=0,$time=0){
    	$sims['###listTitle###'] = $this->upcomingEventsTitle;
    	$sims['###categoryTitle###']='';
    	if($this->jwOptions['categories']['show']){
	    	if($this->jwVars['time'])$time = $this->jwVars['time'];
			$sims['###categoryTitle###'] = $this->getCategoriesTitle($time,$eventuid).' ';
    	}
    	$sims['###categoryTitle###'] .= $this->getViewModes($time);
    	return $sims;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$dataset: ...
	 * @return	[type]		...
	 */
	function buildTemplateArray($dataset){
  	    $ListViewT = $this->cObj->getSubpart($this->templateCode, '###upcomingEvents###');
  	    $ItemsT = $this->cObj->getSubpart($ListViewT, '###items###');
  	    $ItemT = $this->cObj->getSubpart($ItemsT, '###item###');
    	$sims = array(); // Simple markers
   		$sims = $this->buildTemplateHeader($dataset[0]['uid'],$dataset[0]['begin']);
		$this->pidSingle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'singlePage','s_Single_View');
		if($this->pidSingle != $this->pid && $this->caching)
		    $GLOBALS['TSFE']->clearPageCacheContent_pidList($this->pidSingle);

    	$dataset = (!is_array($dataset)) ? array(): $dataset;
    	$num = 0;
  		$entry_count = $this->jwOptions['entry_count'];
  		foreach($dataset as $row){
        	$items[] = $this->buildEvent($ItemT,$row);
        	$num = count($items);
        	if($entry_count && $num >= $entry_count)
            		  break;
		}
		unset($item);
	    if($this->jwVars['firstuid']){
       		 $items = array_reverse($items);
			 if(is_array($dataset)){
			 	 	$item = $dataset[$num-1];
			 }
       	}else{
			 if(is_array($dataset)){
			 	 	reset($dataset);
			 	 	$item = $dataset[0];
			 }
       	}
 	 	$this->parent_time= $item['begin'];
 	 	$this->eventuid = $item['uid'];
		if(is_array($items)){
			foreach($items as $row)
				$rems['###items###'] .= $row;
		}else{
			$rems['###items###'] ='';
		}
		$rems = array_merge($rems,$this->setCUT_submitEvent($ListViewT));
    	$sims = array_merge($sims,$this->setForwardBackwardLink($dataset,$num));
    	return $this->cObj->substituteMarkerArrayCached($ListViewT,$sims,$rems);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$dataset: ...
	 * @param	[type]		$num: ...
	 * @return	[type]		...
	 */
	function setForwardBackwardLink($dataset,$num){
  		if(!$this->jwOptions['entry_count'] ){
           	$sims['###Prev_Events###'] = $this->pi_getLL('no_prev_events');
	       	$sims['###Next_Events###'] = $this->pi_getLL('no_next_events');
	       	return $sims;
 		}

     	if($num < $this->entryCount || !$dataset[$num]['uid']){
        	$num=0;
    	}

		$params = array();
		if($this->jwVars['cat'])
			$params['cat'] = $this->jwVars['cat'];
		$params['uid'] = $this->uid;
		$params['view'] = $this->jwOptions['viewmode'];
		if(!$this->jwVars['firstuid'] && !$this->jwVars['lastuid']){
       	    $sims['###Prev_Events###'] = $this->pi_getLL('no_prev_events');
 		}
    	else if($this->jwVars['lastuid']){
			$params['begin'] = $dataset[0]['begin'];
			$params['firstuid'] = $dataset[0]['uid'];
			$params['chuid'] = $dataset[0]['chuid'];
			$params['chchuid'] = $dataset[0]['chchuid'];
            $sims['###Prev_Events###'] = $this->pi_linkTP($this->pi_getLL('prev_events'),Array($this->prefixId=>$params),$this->caching);
    	}
    	else if($this->jwVars['firstuid']){
        	if($num >1){
			    $item=$dataset[$num-1];
				$params['begin'] = $item['begin'];
				$params['firstuid'] = $item['uid'];
				$params['chuid'] = $item['chuid'];
				$params['chchuid'] = $item['chchuid'];
            	$sims['###Prev_Events###'] = $this->pi_linkTP($this->pi_getLL('prev_events'),Array($this->prefixId=>$params),$this->caching);
        	}else
            	$sims['###Prev_Events###'] = $this->pi_getLL('no_prev_events');
    	}
		$params['firstuid']='';
    	if($num && ($this->jwVars['lastuid'] ||  (!$this->jwVars['firstuid'] && !$this->jwVars['lastuid']))){
			$item=$dataset[$num-1];
			$params['begin'] = $item['begin'];
			$params['lastuid'] = $item['uid'];
			$params['chuid'] = $item['chuid'];
			$params['chchuid'] = $item['chchuid'];
        	$sims['###Next_Events###'] = $this->pi_linkTP($this->pi_getLL('next_events'),Array($this->prefixId=>$params),$this->caching);
    	}else if($this->jwVars['firstuid']){
       		reset($dataset);
			$item=current($dataset);
			//$item=$dataset[$num-1];
			$params['begin'] = $item['begin'];
			$params['lastuid'] = $item['uid'];
			$params['chuid'] = $item['chuid'];
			$params['chchuid'] = $item['chchuid'];
        	$sims['###Next_Events###'] = $this->pi_linkTP($this->pi_getLL('next_events'),Array($this->prefixId=>$params),$this->caching);
    	}else
        	$sims['###Next_Events###'] = $this->pi_getLL('no_next_events');

    	return $sims;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$select: ...
	 * @return	[type]		...
	 */
  function queryUpcomingEventsBase($select){
        $query  = 'SELECT '.$select;
        $query .= ', tx_jwcalendar_events.uid as uid';
        $query .= ', tx_jwcalendar_events.title as title';
        $query .= ', tx_jwcalendar_categories.title as category';
       	$query .= ', tx_jwcalendar_categories.color as catcolor';
        $query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
        $query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
        $query .= ' WHERE 1';
        $query .=  $this->enableFieldsCategories;
        $query .=  $this->enableFieldsEvents;
        $query .= $this->query_selCats();
        $query .= $this->querySelectPages();
        $query .= ' AND event_type = 0';
       	$query .= " AND ((begin < '$this->time' AND  end >= '$this->time')" ;
       	$query .= " OR ( begin >= '$this->time'))";
       	return $query;
  }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
  function queryUpcomingEventsMessenger(){
       /* Query Eventes */
		$entrycount = $this->jwOptions['entry_count']+1;
		$query = $this->queryUpcomingEventsBase('*');
      /*  if($this->jwOptions['entry_period'] ){
        	$query_spez = ' AND begin <= '.strtotime($this->jwOptions['entry_period']);
        	$query .= $query_spez;
       	}*/
       	$query .= " ORDER BY tx_jwcalendar_events.begin,tx_jwcalendar_events.uid,tx_jwcalendar_categories.title LIMIT 0,$entrycount";
       	$array = $this->jw_queryMySql($query);
  		end($array);
  		$lastevent=current($array);
  		if(count($array) <= $this->jwOptions['entry_count'])
  			$lastevent['begin']=0x7fffffff;
  		$array = array_merge($array,$this->getRecurEventsList($this->time,$lastevent['begin'],$query_spez));
    	usort($array,jwSort);
		return $array;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryOneDayEvents(){
		$this->upcomingEventsTitle .= strftime(' '.$this->jwOptions['date_format'],$this->jwVars['day']);
    	$day = strftime('%m / %d  / %Y',$this->jwVars['day']);
    	$begin = strtotime($day);
    	$end = strtotime($day.'+1 day');
    	$this->time = 0;
		$query = $this->queryUpcomingEventsBase('*');
	    $query .= " AND ((begin >= '".$begin."'"." AND begin < '".$end."')";
	    $query .= " OR ( begin < '".$begin."'"." AND end >= '".$begin."'))";
       	$query .= ' ORDER BY begin,tx_jwcalendar_events.category,tx_jwcalendar_events.location';
       	$this->jwOptions['entry_count']  = 0;
  		$array = array_merge($this->jw_queryMySql($query),$this->getRecurEventsList($begin,$end));
    	usort($array,jwSort);
		return $array;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryFirstuidEvents(){
        $num=0;
		if($this->jwVars['firstuid']){
	        $query  = ' SELECT COUNT(begin) AS count';
        	$query .= ' FROM tx_jwcalendar_events';
        	$query .= ' WHERE 1';
	        $query .=  $this->enableFieldsEvents;
        	$query .= " AND tx_jwcalendar_events.begin = '".$this->jwVars['begin']."'";
    		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
	   		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$num = $row['count'];
   	   }
       /* Query Eventes */
		$query = $this->queryUpcomingEventsBase('*');
       	$query .= " AND  ((begin < '".$this->jwVars['begin']."')";
        $query .= " OR (begin = '".$this->jwVars['begin']."'";
        $query .= " AND  tx_jwcalendar_events.uid < '".$this->jwVars['firstuid']."'))";
      /*  if($this->jwOptions['entry_period'] ){
        	$query_spez = ' AND begin <= '.strtotime($this->jwOptions['entry_period']);
        	$query .= $query_spez;
       	}*/
    	$entryCount = $this->jwOptions['entry_count'] + 1 + $num;
       	$query .= " ORDER BY begin DESC,tx_jwcalendar_events.uid DESC LIMIT 0,$entryCount";
       	$array = $this->jw_queryMySql($query);
  		end($array);
  		$lastevent=current($array);
  		if(count($array) <= $this->jwOptions['entry_count'])
  			$lastevent['begin']=$this->time;
  		$array = array_merge($array,$this->getRecurEventsList($lastevent['begin'],$this->jwVars['begin'],$query_spez));
    	usort($array,jwSortReverse);
		return $array;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryLastuidEvents(){
		$query = $this->queryUpcomingEventsBase('*');
       	$query .= " AND (
       				    (begin = '".$this->jwVars['begin']."' AND tx_jwcalendar_events.uid > '".$this->jwVars['lastuid']."')
        		 	OR  (begin > '".$this->jwVars['begin']."' AND  tx_jwcalendar_events.uid != '".$this->jwVars['lastuid']."')
        	       )";

    /*    if($this->jwOptions['entry_period'] ){
        	$query_spez = ' AND begin <= '.strtotime($this->jwOptions['entry_period']);
        	$query .= $query_spez;
       	}*/
       	$query .= ' ORDER BY begin,tx_jwcalendar_events.uid LIMIT 0,'.($this->jwOptions['entry_count'] +1);
       	$array = $this->jw_queryMySql($query);
  		if(count($array) <= $this->jwOptions['entry_count'])
  			$begin = 0x7fffffff;
  		else{
	  		end($array);
  			$lastevent=current($array);
	  		$begin = $lastevent['begin']+ 1;
	  	}
  		$array = array_merge($array,$this->getRecurEventsList($this->jwVars['begin'],$begin,$query_spez));
    	usort($array,jwSort);
		return $array;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryNewEntryInEvents(){
   		$GLOBALS['TYPO3_DB']->debugOutput = true;
		$query = $this->queryUpcomingEventsBase('*');
       	$query .= " AND (begin < '".$this->jwVars['time']."')";
       	$query .= ' ORDER BY begin,tx_jwcalendar_events.uid';
		$array = array();
   		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
          		$array[] = $row;
        }
		$array = array_merge($array,$this->getRecurEventsList($this->time,$this->jwVars['time']));
    	usort($array,jwSort);
  	    $resarray=array();
   	    $numarray = count($array);
   	    if($numarray){
   	    	$res_mod = $numarray%$this->jwOptions['entry_count'];
	   		$previous = $numarray - $res_mod?1:0;
    		for($i=$numarray - $res_mod ;$i<$numarray;$i++){
		       	   $resarray[]=$array[$i];
    		}
 			$i = $this->jwOptions['entry_count'] - $res_mod;
        }else{
        	$i = $this->jwOptions['entry_count']+1;
		}
		//$i++;
		$count = 0;
		if($this->jwVars['eventuid']){
	        $query  = 'SELECT COUNT(*) AS  count';
        	$query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
        	$query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
        	$query .= ' WHERE 1';
        	$query .=  $this->enableFieldsCategories;
        	$query .=  $this->enableFieldsEvents;
        	$query .= $this->query_selCats();
        	$query .= $this->querySelectPages();
        	$query .= ' AND event_type = 0';
	       	$query .= ' AND begin = '.$this->jwVars['time'];
	   		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
	   		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	   		$count = $row['count'];
	   		$i +=$count;
		}
		$query = $this->queryUpcomingEventsBase('*');
       	$query .= ' AND begin >= '.$this->jwVars['time'];
       	$query .= " ORDER BY begin,tx_jwcalendar_events.uid LIMIT 0,$i"; //
   		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
   		$array = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
             $array[] = $row;
        }
        $array = array_merge($array,$resarray);

  		if(count($array) <= $this->jwOptions['entry_count']+$count)
  			$lastevent['begin']=0x7fffffff;
  		else{
  			end($array);
  			$lastevent=current($array);
  		}
		$array = array_merge($array,$this->getRecurEventsList($this->jwVars['time'],$lastevent['begin']));
    	if($previous)
				$this->jwVars['lastuid']=$previous;

    	usort($array,jwSort);
		if($this->jwVars['eventuid']){
			$i=0;$s=false;$count=count($array);
			$resarray = array();
			foreach($array as $item){
				$resarray[$i++]=$item;
				$count--;
	       	    if(!$s && $count>$this->jwOptions['entry_count']){
	    	       	if($this->jwVars['eventuid']==$item['uid'] && $item['begin'] >= $this->jwVars['time'])$s=true;
	    	       	else if($i>=$this->jwOptions['entry_count'])$i=0;
				}
	       	}
	       	return $resarray;
	    }
		return $array;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_upcomingEventsView.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_upcomingEventsView.php']);
}
?>