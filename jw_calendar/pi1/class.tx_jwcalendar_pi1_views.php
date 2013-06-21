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
class tx_jwcalendar_pi1_views extends tx_jwcalendar_pi1_library {
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1_views.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';
    var $errors = array();
    var $jwOptions = array();
  /* Constructor */

  function tx_jwcalendar_pi1_views($cObj,$conf,$jwOptions){
  	   $this->conf = $conf;
	   $this->cObj = $cObj;
	   $this->jwOptions = $jwOptions;
       $this->jwOptions['viewmode'] = 'MONTH';
	   $this->jwOptions['month']['show_rows']= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showWeeksAsRows','s_Month_View');
	   $this->jwOptions['month']['wrapitem']['disabled'] =$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'wrapItem','s_Month_View');
	   $this->jwOptions['month']['wrapitem']['catlen'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'wrapItemCatLen','s_Month_View');
       $this->jwOptions['month']['day_format'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dayFormat','s_Month_View');
	   if(empty($this->jwOptions['month']['day_format']))$this->jwOptions['month']['day_format'] = '%e';
	   $this->jwOptions['month']['only_days_of_month'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'onlyDaysofMonth','s_Month_View');
	   $this->jwOptions['show_event_begin'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showEventBegin','s_Month_View');
	   $this->jwOptions['month']['maxDaynameLenght'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxDaynameLenght','s_Month_View');
	   if(!$this->jwOptions['month']['maxDaynameLenght'])$this->jwOptions['month']['maxDaynameLenght']=3;
	   $this->jwOptions['day']['cuid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DayviewPluginUid','s_Day_View');
	   $this->jwOptions['day']['puid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DayviewPage','s_Day_View');

	   $this->jwOptions['single']['cuid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePluginUid','s_Single_View');
	   $this->jwOptions['single']['puid']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePage','s_Single_View');
 }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
  function monthView(){
   	$time = time();
	//if($this->jwVars['uid']==$this->uid){
		if($this->jwVars['time'])
			$time = $this->jwVars['time'];
    //}
    /* Templates */
  	$monthViewT = $this->cObj->getSubpart($this->templateCode, '###MONTH_VIEW###');
    $lastYearT = $this->cObj->getSubpart($monthViewT, '###GO_LAST_YEAR###');
    $nextYearT = $this->cObj->getSubpart($monthViewT, '###GO_NEXT_YEAR###');
	$submitEvent = $this->cObj->getSubpart($monthViewT, '###SUBMIT_EVENT###');

    $rems = array(); // Region markers
    $sims = array(); // Simple markers

    /* create year links */
    $lastYear = strtotime('12 / 1 / '.(strftime('%Y', $time) - 1));
    $nextYear = strtotime('1 / 1 / '.(strftime('%Y', $time) + 1));
    $labelLastYear = strftime('%Y', $lastYear);
    $labelNextYear = strftime('%Y', $nextYear);
    $sims = array(); // Region markers
	$Vars = array();
 	$Vars['time']=$lastYear;
    $Vars['uid'] = $this->uid;
    $Vars['view'] = $this->jwOptions['viewmode'];
  	$Vars['cat'] = $this->jwVars['cat'];
    $sims['###DATA###'] = $this->pi_linkTP($labelLastYear,Array($this->prefixId=>$Vars),$this->caching);
    $rems['###GO_LAST_YEAR###'] = $this->cObj->substituteMarkerArrayCached($lastYearT, $sims, array(),array());
 	$Vars['time']=$nextYear;
    $sims['###DATA###'] = $this->pi_linkTP($labelNextYear,Array($this->prefixId=>$Vars),$this->caching);
    $rems['###GO_NEXT_YEAR###'] = $this->cObj->substituteMarkerArrayCached($nextYearT, $sims, array(),array());

    $sims = array(); // Simple markers
//    $sims['###VIEW_TITLE###']= $this->pi_getLL('titleMonthView');
    $sims['###VIEW_TITLE###']= $this->pi_getLL('titleMonthView');
    $sims['###TIME_INFO###']= strftime('%B %Y', $time);

   	$rems['###CATEGORYTITLE###'] = '';
    if($this->jwOptions['categories']['show']){
		$rems['###CATEGORYTITLE###'] = $this->getCategoriesTitle($time).' ';
    }
	$rems['###CATEGORYTITLE###'] .= $this->getViewModes($time);
	$this->access=$this->checkFeEntry();
    $rems['###MONTH_NAVI###'] = $this->monthsNavi($time);
    $rems['###CALENDAR_TABLE###'] = $this->calendarTable($time);
    $rems['###SUBMIT_EVENT###'] = $this->submitForm($submitEvent,$time);

	$out = $this->cObj->substituteMarkerArrayCached($monthViewT, array(), $rems, array());
	$out = $this->cObj->substituteMarkerArrayCached($out, $sims, array(), array());

    //if($access){
	//	$out =	$this->wrap2Form($out);
 	//}
    return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$day: ...
	 * @param	[type]		$link: ...
	 * @return	[type]		...
	 */
  function wrapSingleDay($day, $link){
	if($this->jwOptions['month']['wrapitem']['disabled'])return $link;
   	$SingleDayWrap = $this->cObj->getSubpart($this->templateCode, '###SINGLE_DAY###');
   	$Markers = array();
	$Markers['###DAY###'] = $link;
	$Markers['###IMG###'] = $this->submitLink('',$day);
	return $this->cObj->substituteMarkerArray($SingleDayWrap,$Markers);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$item: ...
	 * @return	[type]		...
	 */
  function wrapExcEvent($item){
	if($this->jwOptions['month']['wrapitem']['disabled'])return "<br />".$item;
   	$SingleItemWrap = $this->cObj->getSubpart($this->templateCode, '###SINGLE_ITEM###');
   	$Markers['###ITEM###'] = $item;
	$Markers['###CAT###'] = '';
	$Markers['###CATCOLOR###'] =  '';
	return $this->cObj->substituteMarkerArray($SingleItemWrap,$Markers);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$item: ...
	 * @param	[type]		$event: ...
	 * @return	[type]		...
	 */
  function wrapSingleItem($item,$event){
	if($this->jwOptions['month']['wrapitem']['disabled'])return "<br />".$item;
   	$SingleItemWrap = $this->cObj->getSubpart($this->templateCode, '###SINGLE_ITEM###');// ");
   	$cat = $event['category'];
   	if($this->jwOptions['month']['wrapitem']['catlen']>0)
   		$cat = "<a title=\"$cat\" >".substr($cat,0,$this->jwOptions['month']['wrapitem']['catlen']).'</a>';
   		//$cat = substr($cat,0,$this->jwOptions['month']['wrapitem']['catlen']);
   	else if(!$this->jwOptions['month']['wrapitem']['catlen'])
   		$cat ='&nbsp;';
   	$Markers = array();
   	$Markers['###ITEM###'] = $this->submitEventLink($item,$event);
	$Markers['###CAT###'] = $cat;
	$Markers['###CATCOLOR###'] =" style=\"background-color:".$event['catcolor'].'"';
	return $this->cObj->substituteMarkerArray($SingleItemWrap,$Markers);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
  function calendarTable($time){
  		/* eval time borders */
    	$m = strftime('%m', $time);
    	$y = strftime('%y', $time);
    	$mondayPre = strtotime('last monday', strtotime("$m / 2 / $y 12:00"));
		$mondayPost = strtotime('first monday', strtotime(($m + 1)." / 1 / $y 12:00"));

    	/* load main template */
		$this->entries = $this->getEventsArray($mondayPre,$mondayPost);
    	$this->exc_color_events =$this->setExcEventBgColor($mondayPre,$mondayPost);
    	$this->miniView = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'miniMonthView','s_Month_View');
		if($this->jwOptions['single']['puid']!= $this->pid && $this->caching)
	    	$GLOBALS['TSFE']->clearPageCacheContent_pidList($this->jwOptions['single']['puid']);
    	if($this->jwOptions['month']['show_rows'])
    		return $this->calendarTableRow($time,$mondayPre,$mondayPost);
    	else
    		return $this->calendarTableCol($time,$mondayPre,$mondayPost);

	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @param	[type]		$mondayPre: ...
	 * @param	[type]		$mondayPost: ...
	 * @return	[type]		...
	 */
  function calendarTableCol($time,$mondayPre,$mondayPost){
	$calendarTableView = $this->cObj->getSubpart($this->templateCode, '###CALENDAR_TABLE###');
   	$rowWeekday = $this->cObj->getSubpart($calendarTableView, '###WEEKDAYS###');
   	$rowWeekend = $this->cObj->getSubpart($calendarTableView, '###WEEKEND###');

    $moment = $mondayPre;
    for($moment; $moment < $mondayPost; $moment = strtotime('+1 day', $moment)){
			/* order it by daytypes not by weeks for vertical weeks */
      /* Correction Mo = 0 ... Su = 6 */
			$daynr = strftime('%w', $moment) == 0 ? 6 : strftime('%w', $moment)-1;
      $daySets[$daynr][] = $moment;
      $dayTypes[$daynr] =  substr(strftime('%a', $moment), 0, $this->jwOptions['month']['maxDaynameLenght']); 
    }
    for($i = 0; $i <= 6; $i++){
    	/* load templates */
      if($i < 5){ // it's a weekday row
	      $row = $rowWeekday;
      }else{   // it's a weekend rows
	      $row = $rowWeekend;
      }
      $days = '';
	  foreach($daySets[$i] as $day){
            $days .= $this->getDayEvents($day,$row,$time);
	  }

	/* fill table rows */
     $rems = array(); // Region markers
     $daynameT = $this->cObj->getSubpart($row, '###DAYNAME###');
     $rems['###WEEK###']='';
     $rems['###DAYNAME###'] = $this->cObj->substituteMarker($daynameT,'###DATA###',$dayTypes[$i]);
     $rems['###DAYS###'] = $days;
	    $row = $this->cObj->substituteMarkerArrayCached($row, array(), $rems, array() );
        $rows .= $row;
    }
    /* create week headers */
	if(!$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showWeeksDisabled','s_Month_View')){
   		$weekHeader = $this->cObj->getSubpart($calendarTableView, '###WEEKS###');
    	$data = $this->cObj->getSubpart($weekHeader, '###WEEK###');
    	foreach($daySets[0] as $monday){
			$sims = array();
			if($GLOBALS['WINDIR'])
	      		$sims['###DATA###'] = strftime('%W', $monday).$this->pi_getLL('week');
			else
      			$sims['###DATA###'] = strftime('%V', $monday).$this->pi_getLL('week');
      		$weeks .= $this->cObj->substituteMarkerArrayCached($data, $sims, array(),array());
    	}
    	$rems['###WEEK###'] = $weeks;
	    $weekHeader = $this->cObj->substituteMarkerArrayCached($weekHeader, array(), $rems, array());
	}

		/* fill table */
    $rems = array(); // Region markers
    $rems['###TABLE###'] = $weekHeader.$rows;
		$out = $this->cObj->substituteMarkerArrayCached($calendarTableView, array(), $rems, array());
    return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @param	[type]		$mondayPre: ...
	 * @param	[type]		$mondayPost: ...
	 * @return	[type]		...
	 */
  function calendarTableRow($time,$mondayPre,$mondayPost){

   	$calendarTableView = $this->cObj->getSubpart($this->templateCode, '###CALENDAR_TABLE###');
    $rowWeekday = $this->cObj->getSubpart($calendarTableView, '###WEEKDAYS###');
    $rowWeekend = $this->cObj->getSubpart($calendarTableView, '###WEEKEND###');
	$WeekofYear = $this->cObj->getSubpart($calendarTableView, '###WEEK###');
    $moment = $mondayPre;
    $weeks = array();
    $weekend = 0;

  	$daynameHeader = $this->cObj->getSubpart($calendarTableView, '###DAYNAMES###');
    for($moment; $moment < $mondayPost; $moment = $weekend){
    	$rems = array(); // Region markers
		if(!$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showWeeksDisabled','s_Month_View')){
    		$rems['###WEEK###'] = $this->cObj->substituteMarker($WeekofYear,'###DATA###',strftime('%W',$moment).$this->pi_getLL('week'));
	    	$rems['###CORNER###'] = $this->cObj->getSubpart($daynameHeader, '###CORNER###');
    	}else{
    		$rems['###WEEK###'] = '';
	   		$rems['###CORNER###'] = '';
    	}
    	$rems['###DAYNAME###']='';
	    $weekend = strtotime('+1 week', $moment);
	    $days='';
	    for($day=$moment; $day < $weekend; $day = strtotime('+1 day', $day)){
			$daynr = strftime('%w', $day) == 0 ? 6 : strftime('%w', $day)-1;
			$weekdays[$daynr]=$day;
            if($daynr<5)
			     $row = $rowWeekday;
			else
			 	 $row = $rowWeekend;
			$days .=$this->getDayEvents($day,$row,$time);
		}
		$rems['###DAYS###'] = $days;
		$rows .= $this->cObj->substituteMarkerArrayCached($row, array(), $rems, array() );
    }
    /* create day headers */

   	$data = $this->cObj->getSubpart($daynameHeader, '###DAYNAME###');
   	foreach($weekdays as $day){
   		$dayName =  substr(strftime('%a', $day), 0, $this->jwOptions['month']['maxDaynameLenght']); 
   		$dayNames .= $this->cObj->substituteMarker($data,'###DATA###',$dayName);
   	}
    $rems['###DAYNAME###'] = $dayNames;
	$daynameHeader = $this->cObj->substituteMarkerArrayCached($daynameHeader, array(), $rems);
    $rems = array(); // Region markers
    $rems['###TABLE###'] = $daynameHeader.$rows;
	$out = $this->cObj->substituteMarkerArrayCached($calendarTableView, array(), $rems, array());
	return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$day: ...
	 * @param	[type]		$col: ...
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
  function getDayEvents($day,$col,$time){
		$sims = array();
	    $bOutday = false;
        if(strftime('%m', $day).'/'.strftime('%d', $day) == strftime('%m').'/'.strftime('%d')){
    	    if(strftime('%m', $day) == strftime('%m', $time))
               	$tDay=$this->cObj->getSubpart($col, '###TODAY###');
            else{
                $tDay=$this->cObj->getSubpart($col, '###DAY_OUTSIDE_MONTH###');
                $bOutday = true;
            }
        }else
           	if(strftime('%m', $day) == strftime('%m', $time))
                    $tDay=$this->cObj->getSubpart($col, '###DAY_INSIDE_MONTH###');
            else{
                $tDay=$this->cObj->getSubpart($col, '###DAY_OUTSIDE_MONTH###');
                $bOutday = true;
        }
	    if($this->jwOptions['month']['only_days_of_month'] && $bOutday){
            	$sims['###DATA###'] ='';
            	$sims['###COLOR###'] ='';
		}else{
			$m = strftime('%m', $day);
			$d = strftime('%d', $day);
	    	$stime = strftime($this->jwOptions['month']['day_format'], $day);
    		if(is_array($this->entries[$m][$d])){
	  			usort($this->entries[$m][$d],jwSort);
		  		$params = array();
				if(!empty($this->jwOptions['day']['cuid']))
					$params['uid']=$this->jwOptions['day']['cuid'];
				else
			    	$params['uid'] = $this->uid;
			    $params['cat'] = $this->jwVars['cat'];
	  			$params['time']= $day;
		  		$params['action']  = 'dayView';
	  			$Dayviewpid = $this->jwOptions['day']['puid'];
				if($this->miniView){
					$links = $this->pi_linkTP_keepPIvars_url($params,$this->caching,0,$Dayviewpid);
		    		$links = $this->getToolTips($stime,$this->entries[$m][$d],$links);
		    	}else{
					$links = $this->pi_linkTP_keepPIvars($stime,$params,$this->caching,0,$Dayviewpid);
					//$links .= ' title="'..'">'..'</a>';$this->pi_getLL('oneDayTitle')
				}	
           	}else
           		$links = $stime;
            $coloritem = $this->exc_color_events[$m][$d];
            if($coloritem){
            	if($coloritem['first'])
               		$links .= ' <span style="font-weight:normal">'.$coloritem['title'].'</span>';
            	$sims['###COLOR###'] = ' style="background-color:'.$coloritem['color'].'"';
            }
            else
            	$sims['###COLOR###'] ='';
			$links = $this->wrapSingleDay($day, $links);
			if(!$this->miniView){
    	       	$events = is_array($this->entries[$m][$d]) ? $this->entries[$m][$d] : array();
				foreach( $events as $event){
            		$link = $this->makeEventLink($event,$day);
               		$links .= $this->wrapSingleItem($link,$event);
           		}
        	}
	       	$sims['###DATA###']=$links;
        }
        return $this->cObj->substituteMarkerArrayCached($tDay, $sims);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
  function monthsNavi($time){

  	/* eval the time borders */
    $y = strftime('%Y', $time);
    $firstMonth = strtotime("1 / 1 / $y");
    $lastMonth = strtotime("12 / 31 / $y");

  	/* read the templates */
  	$out = $this->cObj->getSubpart($this->templateCode, '###MONTH_NAVI###');
    $rowT = $this->cObj->getSubpart($out, '###MONTH_ROW###');
    $cMonthT = $this->cObj->getSubpart($out, '###CURRENT_MONTH###');
    $oMonthT = $this->cObj->getSubpart($out, '###OTHER_MONTH###');

    /* collet months to rowsArray  */
    $divider = 6; // Fields in row
    $i = 0; // FieldCounter
    $moment = $firstMonth;
    for($moment; $moment <= $lastMonth; $moment = strtotime('+1 month', $moment)){
    	if(strftime('%m', $moment)==strftime('%m', $time)) $monthT = $cMonthT;
      else $monthT = $oMonthT;
			$sims = array(); // Simple markers
      $m = strftime('%b', $moment);

	  $params=array();
 	  $params['time']=$moment;
 	  $params['uid'] = $this->uid;
      $params['view'] = $this->jwOptions['viewmode'];
      if($this->jwVars['cat']){
		 $params['cat'] = $this->jwVars['cat'];
	  }
      $sims['###DATA###'] = $this->pi_linkTP($m,Array($this->prefixId=>$params),$this->caching);
	    /* replace it all */
	    $rowsArray[($i/$divider)] .= $this->cObj->substituteMarkerArrayCached($monthT, $sims, array(),array());
      $i++;
    }

    /* create the rows */

    $rems = array(); // Region markers
    foreach($rowsArray as $row){
	    $rems['###MONTHS###'] = $row;
	    $rows .= $this->cObj->substituteMarkerArrayCached($rowT, array(), $rems, array() );
    }

    /* create the table */

    $rems = array(); // Region markers
    $rems['###MONTH_ROW###'] = $rows;
    $out = $this->cObj->substituteMarkerArrayCached($out, array(), $rems, array() );
    return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$submitEventT: ...
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
  function submitForm($submitEventT,$time){
    //CUTTERS
    $cutters=array();
   	if($this->access && $this->jwOptions['fe_entry']['fe_button']){
   	    if($this->access>0)
   	    	$this->formpid = $this->access;
		$sims['###submitEvent###'] = $this->access?$this->submitInput($this->pi_getLL('feEntrySubmitEventButtonLabel')):"";
    }
   	$rems['###CUT_submitEvent###'] = $sims['###submitEvent###']?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($submitEventT, '###CUT_submitEvent###'),$sims):"";
	$rems['###CUT_submitEvent_no###'] = $sims['###submitEvent###']?"":$this->cObj->getSubpart($submitEventT, '###CUT_submitEvent_no###');
	$out = $this->cObj->substituteMarkerArrayCached($submitEventT,$rems,$rems);
    if($this->access && $this->jwOptions['fe_entry']['fe_button']){
		$out .= $this->hiddenInput('parent_time',$time);
		$out =	$this->wrap2Form($out);
 	}
	return $out;
  }
}

  /* Debug function */


  if(!function_exists('v')){

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$var: ...
	 * @return	[type]		...
	 */
	  function v($var){
	    print "<pre>------------------------\n";
	    print_r($var);
	    print "\n------------------------</PRE>";
	  }
  }

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_views.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_views.php']);
}

?>