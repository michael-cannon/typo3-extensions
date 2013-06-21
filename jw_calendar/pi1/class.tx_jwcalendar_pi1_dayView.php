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
 */


require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_upcomingEventsView.php');
require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_singleEventView.php');

class tx_jwcalendar_pi1_dayView extends tx_jwcalendar_pi1_upcomingEventsView{
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';
  	var $time;



	function tx_jwcalendar_pi1_dayView($cObj,$conf,$jwOptions){
    	$this->tx_jwcalendar_pi1_upcomingEventsView($cObj,$conf,$jwOptions);
	 	$this->conf = $conf;
	 	$this->cObj = $cObj;
     	$this->jwOptions['viewmode'] = 'DAY';
     	$this->jwOptions['day']['dayDateFormat']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dayDateFormat','s_Day_View');

     	$this->jwOptions['day']['simpleDayView']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'simpleDayView','s_Day_View');

     	$this->jwOptions['day']['SingleView']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'daySingleView','s_Day_View');

     	$this->jwOptions['day']['linkPrevNextDay']=	$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkPrevNextDay','s_Day_View');

     	$this->jwOptions['day']['queryfirstDayFound']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'queryfirstDayFound','s_Day_View');

     	$this->jwOptions['day']['searchDayMax']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'querySearchDayMax','s_Day_View');
     	if(!$this->jwOptions['day']['searchDayMax'])$this->jwOptions['day']['searchDayMax']=30;

     	$this->jwOptions['day']['mixedmode']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mixedModeDisplay','s_Day_View');
	}

  /* Workflows */

	function getForm(){
		if(!$this->jwVars['time']){
				$this->jwVars['time'] = time();
		}
		$this->parent_time=$this->jwVars['day'];
		$msgArray = $this->queryDayEvents();
		if(empty($msgArray) && $this->jwOptions['day']['queryfirstDayFound']){
			$msgArray = $this->queryfirstDayFound();
		}
    	$this->upcomingEventsTitle = $this->pi_getLL('dayViewTitle').' '.strftime($this->jwOptions['day']['dayDateFormat'],$this->jwVars['time']);
  		$out = $this->buildTemplateArray($msgArray);
  		return $out;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$ItemT: ...
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function buildEvent($ItemT,$row){
     	if($this->jwOptions['day']['SingleView'] || $this->jwOptions['day']['mixedmode']){
			if($this->jwOptions['day']['mixedmode'])$this->jwOptions['day']['mixedmode']--;
			return  tx_jwcalendar_pi1_singleEventView::buildTemplateArray($row);
		}else
			return tx_jwcalendar_pi1_upcomingEventsView::buildEvent($ItemT,$row);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$dataset: ...
	 * @param	[type]		$num: ...
	 * @return	[type]		...
	 */
	function setForwardBackwardLink($dataset,$num){
       	if(!$this->jwOptions['day']['linkPrevNextDay']){
           	$sims['###Prev_Events###'] = '';
	       	$sims['###Next_Events###'] = '';
	       	return $sims;
	    }
		$timePrev = strtotime(strftime('%m / %d  / %Y',$this->jwVars['time']).'-1 day');
		$timeNext = strtotime(strftime('%m / %d  / %Y',$this->jwVars['time']).'+1 day');
		$params['cat'] = $this->jwVars['cat'];
		$params['uid'] = $this->uid;
		//$params['view'] = $this->jwOptions['viewmode'];
     	if($this->jwOptions['day']['simpleDayView'])
  			$params['action']  = 'dayView';
  		else
  			$params['view']  = $this->jwOptions['viewmode'];
		$this->jwVars['day']='prev';
		$this->jwVars['time']=$timePrev;
		$events=$this->queryfirstDayFound();
		if($events){
			$params['day'] = 'prev';
			$params['time']=$this->jwVars['time'];
   	    	$sims['###Prev_Events###'] = $this->pi_linkTP($this->pi_getLL('day_prev'),Array($this->prefixId=>$params),$this->caching);
   	    }else
   	    	$sims['###Prev_Events###'] = '';
		$this->jwVars['day']='next';
		$this->jwVars['time']=$timeNext;
		$events=$this->queryfirstDayFound();
   		if($events){
			$params['day'] = 'next';
			$params['time']=$this->jwVars['time'];
		    $sims['###Next_Events###'] = $this->pi_linkTP($this->pi_getLL('day_next'),Array($this->prefixId=>$params),$this->caching);
		}else
			$sims['###Next_Events###'] = '';
		return $sims;
	}


	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryfirstDayFound(){
		$i = $this->jwOptions['day']['searchDayMax'];
		$events=array();
		do{
			if($this->jwVars['day']=='prev')
				$this->jwVars['time'] = strtotime(strftime('%m / %d  / %Y',$this->jwVars['time']).'-1 day');
			else
				$this->jwVars['time'] = strtotime(strftime('%m / %d  / %Y',$this->jwVars['time']).'+1 day');
			$events = $this->queryDayEvents();
			if(!$i--)break;
		}while(empty($events));
		return $events;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function queryDayEvents(){
		$day = strftime('%m / %d  / %Y',$this->jwVars['time']);
    	$end = strtotime($day.'+1 day');
   		$day = strtotime($day);
		$this->dayViewTitle .= strftime(' '.$this->jwOptions['day']['dayDateFormat'],$day);
    	$query = $this->queryUpcomingEventsBase('*');
        $query .= " AND ((begin < '$day' AND  end >= '$day') OR ( begin >= '$day'))
       			    AND ((begin >= '$day' AND begin < '$end') OR ( begin < '$day' AND end >= '$day'))
       				ORDER BY begin,tx_jwcalendar_events.category,tx_jwcalendar_events.location";
       	$this->jwOptions['entry_count']  = 0;
  		$array = array_merge($this->jw_queryMySql($query),$this->getRecurEventsList($day,$end));
    	usort($array,jwSort);
		return $array;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_dayView.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_dayView.php']);
}
?>