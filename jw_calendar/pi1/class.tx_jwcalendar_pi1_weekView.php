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
class tx_jwcalendar_pi1_weekView extends tx_jwcalendar_pi1_library {
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1_weekView.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';
    var $errors = array();
    var $jwOptions = array();
  /* Constructor */

  function tx_jwcalendar_pi1_weekView($cObj,$conf,$jwOptions){
  	   $this->conf = $conf;
	   $this->cObj = $cObj;
	   $this->jwOptions = $jwOptions;
   	   $this->access=$this->checkFeEntry();
       $this->jwOptions['viewmode'] = 'WEEK';
	   $this->jwOptions['week']['start_t']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'start_hour','s_Week_View');
	   if(!$this->jwOptions['week']['start_t'] || $this->jwOptions['week']['start_t']<0 || $this->jwOptions['week']['start_t']>24)
			$this->jwOptions['week']['start_t']=0;
	   $this->jwOptions['week']['stop_t']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'stop_hour','s_Week_View');
	   if(!$this->jwOptions['week']['stop_t'])$this->jwOptions['week']['stop_t']=24;
	   $this->jwOptions['week']['jump_empty']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'query_empty_weeks','s_Week_View');
	   $this->jwOptions['week']['max_t']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'max_hours','s_Week_View');
	   if(!$this->jwOptions['week']['max_t'])$this->jwOptions['week']['max_t']=24;

	   //$this->jwOptions['week']['']
	   $this->jwOptions['show_event_begin'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showEventBegin','s_Month_View');
	   $this->jwOptions['day']['cuid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DayviewPluginUid','s_Day_View');
	   $this->jwOptions['day']['puid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DayviewPage','s_Day_View');

	   $this->jwOptions['single']['cuid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePluginUid','s_Single_View');
	   $this->jwOptions['single']['puid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePage','s_Single_View');

       $this->jwOptions['month']['day_format'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dayFormat','s_Month_View');
	   if(empty($this->jwOptions['month']['day_format']))$this->jwOptions['month']['day_format'] = '%d';


	   $this->jwOptions['categories']['show'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'headerCategories_show');
 }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
  function weekView(){
    /* Templates */
  	$this->weekViewT = $this->cObj->getSubpart($this->templateCode, '###WEEK_VIEW###');
    $rems = array(); // Region markers
    $sims = array(); // Simple markers
	while(1){
		if($this->jwVars['time'])
			$time = $this->jwVars['time'];
		else
		   	$time = time();
		$daynr = strftime('%w', $time) == 0 ? 6 : strftime('%w', $time)-1;
    	$Week_first = strtotime(strftime('%Y-%m-%d',$time).'-'.$daynr.'days');
    	$Week_last  = strtotime(strftime('%Y-%m-%d',$Week_first).'+1week')-1;
		$entries = $this->getEventsArray($Week_first,$Week_last+1);
		if(!empty($entries)|| !$this->jwOptions['week']['jump_empty'])
			break;
		$this->jwOptions['week']['jump_empty']--;
  		if($this->jwVars['dir'] == 'next')
			$this->jwVars['time']=$Week_last+1;
		else
			$this->jwVars['time']=$Week_first-1;
	}
    /* create week links */
    $sims['###VIEW_TITLE###']=$this->pi_getLL('weekViewTitle');
	$rems['###CATEGORYTITLE###'] = $this->getCategoriesTitle($time).' ';
	$rems['###CATEGORYTITLE###'] .= $this->getViewModes($time);
 	$sims['###TIME_INFO###']=strftime($this->jwOptions['date_format'],$Week_first)." - ".strftime($this->jwOptions['date_format'],$Week_last);
 	$params=array();
 	$params['time']=$Week_first -1;
    $params['uid'] = $this->uid;
    $params['view'] = $this->jwOptions['viewmode'];
  	$params['cat'] = $this->jwVars['cat'];
  	$params['dir'] = 'prev';
    $sims['###PREW_WEEK###']=$this->pi_linkTP($this->pi_getLL('week_prev'),array($this->prefixId=>$params),$this->caching);
    //$sims['###PREW_WEEK###']=$this->pi_linkTP($labelPrevWeek,array($this->prefixId=>$params),$this->caching);
  	$params['dir'] = 'next';
 	$params['time']=$Week_last+1;  
    $sims['###NEXT_WEEK###']=$this->pi_linkTP($this->pi_getLL('week_next'),array($this->prefixId=>$params),$this->caching);
	$rems['###WEEK_TABLE###'] = $this->weekTable($Week_first,$Week_last,$entries);
	$out = $this->cObj->substituteMarkerArrayCached($this->weekViewT, $sims, $rems, array());
    return $out;
  }

  	function weekTable($firstDay,$lastDay,$entries){
    	/* load main template */
  		$exc_color_events =$this->setExcEventBgColor($firstDay,$lastDay+1);
   		$weekTableT = $this->cObj->getSubpart($this->weekViewT, '###WEEK_TABLE###');
   		$moment = $firstDay;
    	$sims = array(); 
		$rems = array();
		$timesT = $this->cObj->getSubpart($weekTableT, '###TIMES###'); 
		$this->timeT = $this->cObj->getSubpart($timesT, '###TIME###'); 
		$this->dayT = $this->cObj->getSubpart($weekTableT, '###DAY###'); 
		$this->eventsT = $this->cObj->getSubpart($this->dayT, '###EVENTS###'); 
  		$this->eventT = $this->cObj->getSubpart($this->eventsT, '###EVENT###');
  		$this->event_timeT = $this->cObj->getSubpart($this->eventsT, '###EVENT_TIME###');

		$width = str_replace(',','.',100/($this->jwOptions['week']['stop_t'] - $this->jwOptions['week']['start_t']));
		for($i=$this->jwOptions['week']['start_t'];$i < $this->jwOptions['week']['stop_t'];$i++){
			$sims['###WIDTH###'] = $width;
			$sims['###DATA###']=$i;	
			$rems['###TIME###'] .= $this->cObj->substituteMarkerArrayCached($this->timeT, $sims);
		}
		$rems['###TIMES###'] .= $this->cObj->substituteMarkerArrayCached($timesT,$sims, $rems);
    	$sims = array(); // Region markers
   		for($moment; $moment < $lastDay; $moment = strtotime('+1 day',$moment)){
	    	$m = strftime('%m', $moment);
			$d = strftime('%d', $moment);
    		if(is_array($entries[$m][$d])){
	  			usort($entries[$m][$d],jwSort);
			}
   	       	$events = $entries[$m][$d]; //is_array($entries[$m][$d]) ?  : array();
   	       	$exc_events = $exc_color_events[$m][$d];
			$rems['###DAY###'] .= $this->dayTable($moment,$events,$exc_events,strftime('%a', $moment));
			
		}
		return $this->cObj->substituteMarkerArrayCached($weekTableT, array(), $rems, array());
	}	
	
  	function dayTable($moment,$events,$exc_events,$day){
		/*$width = str_replace(',','.',100/($this->jwOptions['week']['stop_t'] - $this->jwOptions['week']['start_t']));
		for($i=$this->jwOptions['week']['start_t'];$i < $this->jwOptions['week']['stop_t'];$i++){
			$sims['###WIDTH###'] = $width;
			$sims['###DATA###']=$i;	
			$rems['###TIME###'] .= $this->cObj->substituteMarkerArrayCached($this->timeT, $sims);
		}*/
		$sims['###WEEK_DAY###']=$this->submitLink($day,$moment,'left');
		$rems['###EVENTS###'] ="";
		if(!is_array($events)){
			$rems['###EVENTS###'] = $this->emptyTable($moment,$exc_events);
		}else
			foreach( $events as $event){
				$rems['###EVENTS###'] .= $this->eventTable($moment,$event,$exc_events);
			}
		return $this->cObj->substituteMarkerArrayCached($this->dayT, $sims, $rems);
	}
	
  	function emptyTable($day,$exc_events){
		$sims['###COLSPAN###'] = '1';
		$sims['###WIDTH###'] = 100;
		$sims['###DATA###']='';
		$sims['###STYLE###'] = $exc_events['bgcolor'] ? 'style="background:'.$exc_events['color'].';text-align:left;"':'';
		$rems['###EVENT_TIME###'] = $this->cObj->substituteMarkerArray($this->event_timeT,$sims);
		$sims['###DATA###'] = $exc_events['first']?$exc_events['title']:'&nbsp;';	
		$rems['###EVENT###'] = $this->cObj->substituteMarkerArray($this->eventT,$sims);
		return $this->cObj->substituteMarkerArrayCached($this->eventsT, $sims, $rems);
	}
	
  	function eventTable($day,$event,$exc_events){
	   	$start_t = $this->jwOptions['week']['start_t'];
	   	$stop_t = $this->jwOptions['week']['stop_t'];
	   	$max_t = $this->jwOptions['week']['max_t'];
	   	if($event['mday'])
		   	$start_day = strtotime(strftime('%Y-%m-%d',$event['mday']));
		else
	   		$start_day = strtotime(strftime('%Y-%m-%d',$event['begin']));
		if($start_day < $day)
			$start_h = 0;
		else
			$start_h= strftime('%H',$event['begin']);
		if($event['end']>0){
	   		$stop_day = strtotime(strftime('%Y-%m-%d',$event['end']));
			if($stop_day > $day)
				$stop_h = 24;
			else
				$stop_h =strftime('%H',$event['end']);
		}else
			$stop_h = $start_h + 1;

		$bgcolor=$exc_events['bgcolor'] ? 'background:'.$exc_events['color'].';':'';
		$sims['###DATA###']= '';//'&nbsp';
		$width = str_replace(',','.',(100/($stop_t - $start_t)));
		for($i=$start_t;$i<$stop_t;$i++){
			if($i >= $start_h && $i < $stop_h){
				$sims['###STYLE###'] = 'style="width:'.$width.'%;background:'.$event['catcolor'].';border:1px solid '.$event['catcolor'].';"';
				$rems['###EVENT_TIME###'] .= $this->cObj->substituteMarkerArray($this->event_timeT,$sims);
			}else{
				if($bgcolor)
					$sims['###STYLE###'] = 'style="width:'.$width.'%; '.$bgcolor.';border-top:1px solid '.$event['catcolor'].';"';
				else	
					$sims['###STYLE###'] = 'style= " width:'.$width.'%; border-top:1px solid '.$event['catcolor'].';"';
				$rems['###EVENT_TIME###'] .= $this->cObj->substituteMarkerArray($this->event_timeT,$sims);
			}	
		}	
		$stop_h=$stop_t;
		$sims['###STYLE###'] = 'style="text-align:left; width:'.$width.'%;'.$bgcolor.'"';
		$sims['###DATA###'] = $exc_events['first']?$exc_events['title']:'&nbsp;';	
		$sims['###COLSPAN###'] = $start_h - $start_t;
		if($sims['###COLSPAN###']>($max_t-$start_t))
			$sims['###COLSPAN###']=$max_t-$start_t;
		if($sims['###COLSPAN###']>0)
			$rems['###EVENT###'] .= $this->cObj->substituteMarkerArray($this->eventT,$sims);
		
		$sims['###STYLE###'] = 'style="text-align:left; width:'.$width.'%;';
		if($start_h > $max_t)
			$sims['###STYLE###'] = 'style="text-align:right; width:'.$width.'%;';
		$sims['###STYLE###'] .= $bgcolor.'border-left:1px solid '.$event['catcolor'].';'; 

		$sims['###DATA###']=$this->submitEventLink($this->makeEventLink($event,$day),$event,'left');	
		$sims['###COLSPAN###'] = $stop_t - $sims['###COLSPAN###'];  

		$sims['###STYLE###'] .= '"'; 
		$rems['###EVENT###'] .= $this->cObj->substituteMarkerArray($this->eventT,$sims);
		return $this->cObj->substituteMarkerArrayCached($this->eventsT, $sims, $rems);
	}
	


  
  

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_weekView.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_weekView.php']);
}

?>