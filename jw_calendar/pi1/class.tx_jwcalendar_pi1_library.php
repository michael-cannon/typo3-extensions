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

/*************************************************
* This class contains functions that are common for all
* plugins of this extension. It is intented do get extended
* by the plugin classes.
* tsliv_pibase is included indirectly.
*/
//require_once(t3lib_extMgm::extPath("overlib")."class.tx_overlib.php");
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_jwcalendar_pi1_library extends tslib_pibase {
	var $extKey;	     // The extension key.
#  var $extPrefix;
  var $templateCode;
  var $conf;
  var $uid;
  var $pid;
  var $formpid;
  var $jwVars;

	/***************************************************************/
	/* evaluate prefixes                                           */
	/**
	 * ************************************************************
	 *
	 * @return	[type]		...
	 */
	function extPrefix(){
		preg_match("/(.*)_[^_]*$/", $this->prefixId, $matches);
		return $matches[1];
	}

 	/***************************************************************/
	/* configuration methods                                       */
	/**
	 * ***********************************************************
	 *
	 *   configuration function must be called by each dispatched class of each plugin. Let us set some useful variables here!
	 *
	 * @return	[type]		...
	 */
  function configure(){
		/* set some usefull variables */
		if($GLOBALS['TSFE']->config['config']['locale_all'])
			$locale_result = setlocale(LC_ALL,$GLOBALS['TSFE']->config['config']['locale_all']);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
  }

  /* to include views from plugin main functions */

  function initChildClass(){
		return;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$class: ...
	 * @return	[type]		...
	 */
  function getClass($class){
	 require_once(t3lib_extMgm::extPath($this->extKey).$this->piDirRelPath."class.{$this->prefixId}_{$class}.php");
	 $c = $this->prefixId.'_'.$class;
	 $class = new $c($this->cObj,$this->conf,$this->jwOptions);
	 $class->extKey = $this->extKey;
	 $class->prefixId = $this->prefixId;
	 $class->jwVars = $this->jwVars;
     $class->uid = $this->uid;
     $class->pid = $this->pid;
     $class->formpid = $this->pid;
	 $class->caching = $this->caching;
	 $class->pi_USER_INT_obj = $this->pi_USER_INT_obj;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
     $class->enableFieldsCategories = $this->enableFieldsCategories;
     $class->enableFieldsEvents = $this->enableFieldsEvents;
	 $class->enableFieldsExcEvents = $this->enableFieldsExcEvents;
	 $class->enableFieldsExcGroups = $this->enableFieldsExcGroups;
	 $class->enableFieldsOrganizer = $this->enableFieldsOrganizer;
	 $class->enableFieldsLocation  = $this->enableFieldsLocation; 

     $class->templateCode = $this->templateCode;
   //  $class->jwOptions = $this->jwOptions;
     $class->configure();
	 $class->LLkey = $this->conf['language'];
     $class->initChildClass();
	 return $class;
  }

   /***************************************************************/
   /* Output methods                                              */
   /***************************************************************/

  /**
 * Date output for extended date output in one single row.
 * Local Language configuration: outputDate.
 * Setup configuration: dateFormat, timeFormat.
 *
 * @param	[type]		$time: ...
 * @param	boolean		$end: true if end time
 * @return	string,		with the formated time
 */
  function dateOutput($time, $end = false ){
    $mA = array();
    $mA['###date###'] = strftime($this->jwOptions['date_format'], $time);
    $mA['###time###'] = strftime($this->jwOptions['time_format'], $time);
    $out						= ( ! $end )
									? $this->pi_getLL('outputDate')
									: $this->pi_getLL('outputDateEnd');
    $out = $this->cObj->substituteMarkerArrayCached($out, $mA, array(), array() );
    return $out;
  }

   /***************************************************************/
   /* Form methods                                                */
   /**
 * ***********************************************************
 *
 *    wrap views to get a form
 *
 * @param	[type]		$out: ...
 * @param	[type]		$hidden: ...
 * @return	[type]		...
 */
  function wrapForm($out, $hidden='',$params = array()){
  return "
    <form method='post' enctype='multipart/form-data' action=\"".$this->pi_linkTP_keepPIvars_url($params,0,0,$this->formpid)."\" >
    $hidden
    $out
  </form>
  ";
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$out: ...
	 * @param	[type]		$hidden: ...
	 * @return	[type]		...
	 */
  function wrapForm2($out, $hidden=''){
  return '
    <form action="">
    $hidden
    $out
  </form>
  ';
  }
  /* make errorlist */

  function makeErrorlist(){
  	if(!$this->errors){
    	return "";
    }else{
	    $errorlist = '';
	    foreach($this->errors as $error) $errorlist .= "<li>$error</li>\n";
    	return  "\n<ul>\n$errorlist</ul>\n";
    }
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$value: ...
	 * @return	[type]		...
	 */
  function hiddenInput($name, $value){
  	$out = "<input type=\"hidden\" name=\"".$this->prefixId.'['.$name."]\" value=\"$value\" />\n";
  	return $out;
  }

  /**
 * Create a submit button.
 * The lables may be to translation keys: §§§tranlationKey§§§
 *
 * @param	[type]		$link: ...
 * @param	[type]		$backbutton: ...
 * @return	string
 * @label: string. The label.
 * @name: string.
 * @backbutton: boolean or string. If true make default backbutton, if string use it as label instead.
 */
  function backButton($link,$backbutton = false){
   $backButtonLabel = is_string($backbutton) ? $backbutton : $this->pi_getLL('backButtonLabel');

   $out .= "<input type=\"button\"  CLASS=\"tx_jw_input_button\" value=\"$backButtonLabel\" onClick=\"self.location.href='".$link."'\" /> ";
   return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$pid: ...
	 * @param	[type]		$backbutton: ...
	 * @return	[type]		...
	 */
  function submitBack($pid,$backbutton = false){
   $backButtonLabel = is_string($backbutton) ? $backbutton : $this->pi_getLL('backButtonLabel');

   $out .= "<input type=\"button\"  CLASS=\"tx_jw_input_button\" value=\"$backButtonLabel\"     onClick=\"window.history.back()\" /> ";
   return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$name: ...
	 * @param	[type]		$backbutton: ...
	 * @return	[type]		...
	 */
  function submitInput($label, $name = "submit", $backbutton = false ){
    if($backbutton){
       $backButtonLabel = is_string($backbutton) ? $backbutton : $this->pi_getLL('backButtonLabel');
       $out .= "<input type=\"button\"  class=\"tx_jw_input_button\" value=\"$backButtonLabel\" onClick=\"window.history.back()\" /> ";
    }
    $out .= "<input type=\"submit\" name=\"".$this->prefixId.'['.$name."]\" class=\"tx_jw_input_button\" value=\"$label\" />";
    return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$size: ...
	 * @return	[type]		...
	 */
  function fileInput($name, $size = ''){
		$out =  "\n<input type=\"file\" name=\"".$this->prefixId.'['.$name."]\" class=\"tx_jw_input_file\"  size=\"$size\" />\n";
  	return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$value: ...
	 * @param	[type]		$size: ...
	 * @return	[type]		...
	 */
  function textInput($name, $value, $size = ''){
		$out =  "\n<input type=\"text\" name=\"".$this->prefixId.'['.$name."]\" class=\"tx_jw_input_text\"  value=\"$value\" size=\"$size\" />\n";
  	return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$value: ...
	 * @param	[type]		$rows: ...
	 * @param	[type]		$cols: ...
	 * @return	[type]		...
	 */
	function areaInput($name, $value, $rows = "", $cols = ''){
  	$out = "\n<textarea id=\"".$this->prefixId."[$name]\" name=\"".$this->prefixId."[$name]\"  class=\"tx_jw_textarea\" cols=\"$cols\" rows=\"$rows\">$value</textarea>\n";
  	return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$array: ...
	 * @param	[type]		$selected: ...
	 * @param	[type]		$size: ...
	 * @param	[type]		$emptyItem: ...
	 * @return	[type]		...
	 */
  function selectInput($name, $array, $selected="", $size=1, $emptyItem=false){
		$out .= "<select name=\"".$this->prefixId.'['.$name."]\" class=\"tx_jw_select\"  size=\"$size\">\n";
   	if($emptyItem) $out .= "<option>--</option>\n";
    foreach($array as $value => $label){
      $sel = ((string)$value == (string)$selected)?" selected='true'":"";
    	$out .= "<option value=\"$value\" $sel>$label</option>\n";
    }
		$out .= "</select>\n";
    return $out;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$array: ...
	 * @param	[type]		$selected: ...
	 * @param	[type]		$onChange: ...
	 * @param	[type]		$size: ...
	 * @param	[type]		$emptyItem: ...
	 * @return	[type]		...
	 */
  function selectInputOnChange($name, $array, $selected="", $onChange="", $size=1, $emptyItem=false){
		$out .= "<select name=\"$name\" class=\"tx_jw_select\" onChange=\"$onChange\" size=\"$size\">\n";
   	if($emptyItem) $out .= "<option>--</option>\n";
    foreach($array as $value => $label){
      $sel = ((string)$label == (string)$selected)?" selected='true'":"";
    	$out .= "<option value=\"$value\" $sel>$label</option>\n";
    }
		$out .= "</select>\n";
    return $out;
  }
  

		function dateInput($name, $vars,$emptyItem=true){
			  /* If date is just loaded from db, $vars[date] exists, but not the single items. */
		    if( isset($vars[$name]) && !isset($vars["${name}_year"])){
		      		$vars = $this->_addDateItems($vars, $name, $name);
		      }
		    $now = strftime('%Y');
		    $fromYear = $now - $this->backwardYears;
		    $toYear = $now + $this->forwardYears;
			$minuteStep = $this->minuteStep? $this->minuteStep : 10;

		    $mA = array();
		    $mA['###year###'] = $this->yearSelect("{$name}_year", $vars["{$name}_year"], $fromYear, $toYear, $emptyItem);
		    $mA['###month###'] = $this->monthSelect("{$name}_month", $vars["{$name}_month"], $emptyItem);
		    $mA['###day###'] = $this->daySelect("{$name}_day", $vars["{$name}_day"], $emptyItem);
		    $mA['###hour###'] = $this->hourSelect("{$name}_hour", $vars["{$name}_hour"], $emptyItem);
		    $mA['###minute###'] = $this->minuteSelect("{$name}_minute", $vars["{$name}_minute"], $minuteStep, $emptyItem);
		    $out = $this->pi_getLL('inputDate');
		    $out = $this->cObj->substituteMarkerArrayCached($out, $mA, array(), array() );
		    return $out;
		  }
		  
		  function daySelect($name, $value, $emptyItem){
				foreach(range(1, 31) as $i) $array[$i] = $i;
				return $this->selectInput($name, $array, $value, 1, $emptyItem);
		  }
		  
		  function monthSelect($name, $value, $emptyItem){
				foreach(range(1, 12) as $i) $array[$i] = $i;
				return $this->selectInput($name, $array, $value, 1, $emptyItem);
		  }
		  
		  function yearSelect($name, $value, $from, $to , $emptyItem){
		    for($i = $from; $i <= $to; $i++)$array[$i] = $i;
				return $this->selectInput($name, $array, $value, 1, $emptyItem);
		  }
		  
		  function hourSelect($name, $value, $emptyItem){
				foreach(range(0, 23) as $i) $array[$i] = $i;
				return $this->selectInput($name, $array, $value, 1, $emptyItem);
		  }

		  function minuteSelect($name, $value, $step, $emptyItem){
		    $step = (is_numeric($step) && $step > 0)? $step : 5;
		    for($i=0; $i<60; $i=$i + $step)$array[$i] = $i;
				return $this->selectInput($name, $array, $value, 1, $emptyItem);
		  }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	 function handleUpload(){
	    if(!$this->FE_Image_alowed || empty($_FILES[$this->prefixId]['name']['image']))
	    	return "";
	    $uploadfile =  PATH_site.'uploads/'.$this->extPrefix().'/'.$_FILES[$this->prefixId]['name']['image'];
		if(is_file($uploadfile)){//file already exists?
			$res = unlink ($_FILES[$this->prefixId]['tmp_name']['image']);
			return $_FILES[$this->prefixId]['name']['image'];
		}
		if(move_uploaded_file($_FILES[$this->prefixId]['tmp_name']['image'],$uploadfile)) {//succes!
			return $_FILES[$this->prefixId]['name']['image'];
 		} else {
			return "";
		}
		return "";
	}

	/***************************************************************/
	/* Recursive encoding and decoding                             */
	/**
	 * ************************************************************
	 *
	 *    htmlspecialchars recursiv
	 *
	 * @param	[type]		$$in: ...
	 * @return	[type]		...
	 */
 	function hscRecurs( $in ) {
  	if(is_array($in))	foreach($in as $k => $v){
        $out[$k] = $this->hscRecurs($v);
    }
    else{
			if(1){
		  	$out = htmlspecialchars($in, ENT_QUOTES);
			} else {
        $out = str_replace(array('>', '<', '"', '\''), array('&gt;', '&lt;', '&quot;', '&#039;'), $in);
			}
		}
    return $out;
	}

  /* addslashes recursiv */
	function addslRecurs( $in ) {
  	if(is_array($in))	foreach($in as $k => $v) $out[$k] = $this->addslRecurs($v);
    else $out = addslashes($in);
    return $out;
	}

  /* stripslashes recursiv */
	function strslRecurs( $in ) {
  	if(is_array($in))	foreach($in as $k => $v) $out[$k] = $this->strslRecurs($v);
    else $out = stripslashes($in);
    return $out;
	}

  /* encode recursiv */
	function encRecurs( $in ) {
  	if(is_array($in))	foreach($in as $k => $v) $out[$k] = $this->encRecurs($v);
    else $out = urlencode($in);
    return $out;
	}

  /* decode recursiv */
	function decRecurs( $in ) {
  	if(is_array($in))
  		foreach($in as $k => $v)$out[$k] = $this->decRecurs($v);
 	else $out = urldecode($in);
    return $out;
	}

	/***************************************************************/
	/* Other methods                                               */
	/**
	 * ***********************************************************
	 *
	 *    transform user links to anker link
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$link: ...
	 * @return	[type]		...
	 */
	function makeLink($label, $link){
	  $myConf = $this->conf['typolink.'];
	  $myConf['parameter'] = $link;
	  return $this->cObj->typoLink($label, $myConf);
	}

	/*
	 * Make unixtime from the $vars input array.
	 *
	 * @return unixtime
	 * @param $name: The namepart of time items year, month, day, hour, minute.
	 *	             Pattern $vars[{$name}_year].
	 * @param $vars: the $vars array containing the time items.
	 */

	function makeTime($name, $vars){
	  foreach(split(', ', 'year, month, day, hour, minute') as $value){
			if($vars[$name.'_'.$value] == ''){ $vars[$name.'_'.$value] = 0; }
			elseif(!is_numeric($vars[$name.'_'.$value])){ $vars[$name.'_'.$value] = -1; }
		}
		$datestr = $vars[$name.'_year'].'-'.$vars[$name.'_month'].'-'.$vars[$name.'_day'].' '.$vars[$name.'_hour'].':'.$vars[$name.'_minute'];
 		$out = strtotime($datestr);
	  return $out;
	}

	/**
	 * Add date items for year, month, day, hour, minute to input array.
	 * Formats as full digits : (2000 12 31 23 59 59), (Y m d H i s)
	 * Helper method for dateInput($name, $vars).
	 *
	 * @param	$vars:		array that contains at least an item with the unix date
	 * @param	$prefix:		string, prefix for the item names in output array, default "date"
	 * @param	$dateitem:		name of the datefield of inputarray, default "date"
	 * @return	array		same as input with additional items
	 */
  function _addDateItems($vars, $prefix = "date", $dateitem = 'date' ){
		$unix = $vars[$dateitem];
		if(!$unix)return $vars;
		$vars[$prefix.'_year'] = date('Y', $unix);
		$vars[$prefix.'_month'] = date('m', $unix);
		$vars[$prefix.'_day'] = date('d', $unix);
		$vars[$prefix.'_hour'] = date('H', $unix);
		$vars[$prefix.'_minute'] = date('i', $unix);
		$vars[$prefix.'_second'] = date('s', $unix);
  	return $vars;
  }
// jwitt 10.04 begin

  function wrap2Form($out){
	$out .= $this->hiddenInput('uid', $this->uid);
    $out .= $this->hiddenInput('parent', $this->pid);
    $out .= $this->hiddenInput('parentview',$this->jwOptions['viewmode']);
    $out .= $this->hiddenInput('cat',$this->jwVars['cat']);
    $out .= $this->hiddenInput('action', 'emptyFeEntry');
    return $this->wrapForm($out);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
  function querySelectPages($table=' tx_jwcalendar_events'){
        $pages = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'pages');
        if(!$pages)return $pages;
        $spage = ' AND '.$table.'.pid IN (';
        $spage .= $this->pi_getPidList($pages,$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'recursive'));
        $spage .= ') ';
        return $spage;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$table: ...
	 * @param	[type]		$field: ...
	 * @param	[type]		$cat: ...
	 * @return	[type]		...
	 */
  function query_selCats($table="tx_jwcalendar_events",$field="category",$cat=true){
       if($this->jwVars['cat'] && $cat && ($this->jwVars['uid']==$this->uid || !$this->jwVars['uid']))
	  	    return " AND ".$table.'.'.$field.' = '.$this->jwVars['cat'];
       $condition = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'categoryMode');
       if(!$condition)
	   		return "";
       $cats= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'categorySelection');
	   if(!$cats)return $cats;
	   //	$condition = ZERO or "IN" or "NOT IN"
  	   $query=' AND '.$table.'.'.$field.' '.$condition.' (';
       $query .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'categorySelection');
       $query .= ') ';
  	   return $query;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @param	[type]		$eventuid: ...
	 * @return	[type]		...
	 */
  function getCategoriesTitle($time=false,$eventuid=false){
        if(!$this->jwOptions['categories']['show'])
        	return "";
		$query  = ' SELECT title,uid ';
    	$query .= ' FROM tx_jwcalendar_categories ';
    	$query .= ' WHERE 1 ';
    	$query .=  $this->enableFieldsCategories;
    	$query .=  $this->querySelectPages('tx_jwcalendar_categories');
    	$query .=  $this->query_selCats('tx_jwcalendar_categories','uid',false);
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
    	if($rows==1){
    		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    		return $row['title'];
    	}
    	if(!$rows)
    		return "";
    	$params = array();
        $params['view'] = $this->jwOptions['viewmode'];
        $params['uid'] = $this->uid;
        $params['time'] = $time;
        //$params['eventuid'] = $eventuid;
		if(!$this->jwOptions['no_catall'])
	    	$catTitles[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = 'Select a Category';
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
    		  if($this->jwVars['cat']==$row['uid'])
    		  	  $sel=$row['title'];
    		  $params['cat']=$row['uid'];
      		  $catTitles[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = $row['title'];
    	}
		$out = $this->selectInputOnChange('cat', $catTitles,$sel ,"document.location = '' + this.options[selectedIndex].value;");
//		$out .= $this->selectInputOnChange('cat', $catTitles,$sel ,'jwGo(this.form.cat.options[this.form.cat.options.selectedIndex].value)');
    //	return $this->wrapForm2($out);
    	return $out;
  }

  	function getFE_CategoriesUid($select){
 		$categoriesNames = $this->getFE_Categories();
 		$query  = ' SELECT uid';
	    $query .= ' FROM tx_jwcalendar_categories ';
    	$query .= " Where title = '".$categoriesNames[$select]."'";
    	$query .= $this->enableFieldsCategories;
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    	return $row['uid'];
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
 	function getFE_Categories(){
 		$query  = ' SELECT uid,title ';
	    $query .= ' FROM tx_jwcalendar_categories ';
    	$query .= ' WHERE 1 ';
    	$query .= $this->enableFieldsCategories;
    	$query .= ' AND fe_entry = 1';
        $query .= $this->query_selCats('tx_jwcalendar_categories','uid');
        $query .= $this->querySelectPages('tx_jwcalendar_categories');
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$categories = array();
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
    		$categories[$row['uid']] = $row['title'];
    	return $categories;
 	}

	function getFE_CategoriesTitle($uid){
 		$query  = ' SELECT title';
	    $query .= ' FROM tx_jwcalendar_categories ';
    	$query .= " Where uid = '".$uid."'";
    	$query .= $this->enableFieldsCategories;
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    	return $row['title'];
	}

  function getfirstCat(){	
        $condition = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'categoryMode');
        if(!$condition)
	   		return 1;
  		$cats= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'categorySelection');
  		if(empty($cats))return 1;
        if($condition=='IN'){
		    $cats = explode(',',$cats);
  			return $cats[0];
  		}else{
			$query  = ' SELECT uid ';
    		$query .= ' FROM tx_jwcalendar_categories ';
    		$query .= ' WHERE uid '.$condition.' ('.$cats.') ';
    		$query .= ' LIMIT 1';
	    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    		return $row['uid'];
  		}		
  }	


	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$time: ...
	 * @return	[type]		...
	 */
  function getViewModes($time=false){
	  $views = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'viewmodeSelection');
	  if(empty($views))return "";
	  $ViewModes = array();
	  $params = array('uid' => $this->uid,'cat' => $this->jwVars['cat'], 'time' => $time);
	  if(strpos($views,'LIST')!==false){
	  	$params['view'] = 'LIST';
	  	$ViewModes[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = $this->pi_getLL('LIST');
	  }
	  if(strpos($views,'MONTH')!==false){
		  $params['view'] = 'MONTH';
		  $ViewModes[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = $this->pi_getLL('MONTH');
	  }
	  if(strpos($views,'WEEK')!==false){
		  $params['view'] = 'WEEK';
		  $ViewModes[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = $this->pi_getLL('WEEK');
	  }
  	  if(strpos($views,'DAY')!==false){
		  $params['view'] = 'DAY';
		  $ViewModes[$this->pi_linkTP_keepPIvars_url($params,$this->caching)] = $this->pi_getLL('DAY');
	  }
  	  return $this->selectInputOnChange('view', $ViewModes ,$this->pi_getLL($this->jwOptions['viewmode']), "document.location = '' + this.options[selectedIndex].value;");
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$pid: ...
	 * @param	[type]		$table: ...
	 * @param	[type]		$condition: ...
	 * @return	[type]		...
	 */
	function check_fe_group($pid,$table,$condition){
        $query  = ' SELECT fe_group as groupId';
       	$query .= ' FROM '.$table;
       	$query .= ' WHERE '.$condition;
   		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
   		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$groupID = $row['groupId'];
		if($groupID){
			if($GLOBALS['TSFE']->fe_user->user &&
				($groupID ==-2 || $groupID == $GLOBALS['TSFE']->fe_user->user['usergroup'])){
				return $pid;
			}
		}
		return 0;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function checkFeEntry(){
    	if(!$this->jwOptions['fe_entry']['enable']  )
    		return 0;
    	$pid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Page','s_FE_Entries');
	    if(!$pid)
			return -1;
		if($this->check_fe_group($pid,'pages'," uid =".$pid))
			return $pid;
		return $this->check_fe_group($pid,'tt_content',"pid = ".$pid." AND list_type=\"".$this->extKey.'_pi1"');
	}
//**************************************************************************************************
// get I T E M S
//**************************************************************************************************

	function getRecurEventsList($from,$to,$query_spez=''){
		$query  = ' SELECT tx_jwcalendar_events.*';
    	$query .= ', tx_jwcalendar_categories.title as category';
    	$query .= ', tx_jwcalendar_categories.color as catcolor';
    	$query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
	    $query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
	    $query .= ' WHERE 1';
    	$query .=  $this->enableFieldsCategories;
	    $query .=  $this->enableFieldsEvents;
    	$query .=  $this->query_selCats();
	    $query .=  $this->querySelectPages();
    	$query .= " AND(
    		event_type > 0 AND event_type < 5 AND

    	(
  			rec_end_date = 0
    	OR  (begin >= '$from' AND (begin < '$to'))
    	OR  (begin < '$from' AND (rec_end_date >= '$from' OR  end >= '$from'))
    	))";

	    	$query .= $query_spez;
   		$query .= ' ORDER BY begin, tx_jwcalendar_events.uid';
    	$db_res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
   		$entries=array();
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_res)) {
    		$items=array();
        	$items = $this->setRecurItemsList($row,$from,$to);
        	$entries = array_merge($entries,$items);
		}
		return $entries;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
	function getEventsArray($from,$to){
		$query  = ' SELECT tx_jwcalendar_events.*';
    	$query .= ', tx_jwcalendar_categories.title as category';
	    $query .= ', tx_jwcalendar_categories.color as catcolor';
    	$query .= ' FROM tx_jwcalendar_events INNER JOIN tx_jwcalendar_categories';
	    $query .= ' ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid';
    	$query .= ' WHERE 1';
	    $query .=  $this->enableFieldsCategories;
    	$query .=  $this->enableFieldsEvents;
	    $query .=  $this->query_selCats();
    	$query .=  $this->querySelectPages();
	    $query .= " AND(
    	(
        	(begin < '$from' AND end >= '$from')
    	OR  (begin >= '$from' AND begin < '$to')
    	)";
    	$query .= " OR(
    		event_type > 0 AND event_type < 5 AND
    	(
    		(rec_end_date = 0)
    	OR  (begin < '$from' AND rec_end_date >= '$from')
    	)))";

   		$query .= ' ORDER BY begin, tx_jwcalendar_events.uid';
    	$db_res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
		return $this->makeArray($db_res,$from,$to);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$db_res: ...
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
  function makeArray($db_res,$from,$to){
    /* order result by days */
    $entries=array();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($db_res)) {
            $row = $this->setItems($row,$from,$to);
            foreach($row as $item){
				$d = strftime('%d', $item['begin']);
				$m = strftime('%m', $item['begin']);
 				$entries[$m][$d][] = $item;
 			}
	}
	return $entries;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$item: ...
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
  function setRecurItemsList($item,$from,$to){
        $begin =  $item['begin'];
        switch ($item['event_type']){
            case 1:
			     $interval = 'days';
		         $repeat_time = $item['repeat_days']?$item['repeat_days']:1;
            break;
            case 2:
			     $interval = 'weeks';
		         $repeat_time = $item['repeat_weeks']?$item['repeat_weeks']:1;
            break;
            case 3:
			     $interval = 'months';
		         $repeat_time = $item['repeat_months']?$item['repeat_months']:1;
            break;
            case 4:
			     $interval = 'years';
		         $repeat_time = $item['repeat_years']?$item['repeat_years']:1;
            break;
        }
		$end = $item['end'];
	    if($item['rec_time_x']){
	    	 $xend = strtotime(strftime('%Y-%m-%d %H:%M:0 ',$item['begin']).$item['rec_time_x']*$repeat_time.' '.$interval);
        }
        $item['rec_time_x'] = $item['rec_time_x']?$item['rec_time_x']:1;
       	$recend = $item['rec_end_date'];
       	if($xend){
       		if($recend && $xend > $recend)
        		$end = $recend;
	    	else{
	       		$end = $xend;
	       		$recend = 0;
	       	}
	    }else
        	$end = $recend;

        $itemarray = array();
        $time = $end - $begin;

        switch ($item['event_type']){
            case 1:	if($item['rec_daily_type']==0){
						$i_max = $item['rec_time_x']*$repeat_time;
						$i_add = $repeat_time;
					}
      				else if($item['rec_daily_type']>0){
						$i_max = $item['rec_time_x'];
						$i_add = 1;
						if($item['rec_daily_type']==1)
							$skip='06';
						else
							$skip='12345';
					}
            break;
            case 2:
        	   	 $from_week = strtotime(strftime('%Y-%m-%d %H:%M:0',$from).' -7 days');
			     //$interval = 'weeks';
			     if($item['rec_weekly_type']==0){
			     	$weekdays = array($item['repeat_week_monday'],$item['repeat_week_tuesday'],$item['repeat_week_wednesday'],$item['repeat_week_thursday'],$item['repeat_week_friday'],$item['repeat_week_saturday'],$item['repeat_week_sunday']);
			     }
			     else if($item['rec_weekly_type']==1){
			     	$weekdays = array(1,1,1,1,1,0,0);
			     }
			     else if($item['rec_weekly_type']==2){
			     	$weekdays = array(0,0,0,0,0,1,1);
			     }
				 $i_max = $item['rec_time_x']*$repeat_time;
				 $i_add = $repeat_time;
            break;
//            case 3:
			     //$interval = 'months';
  //          break;
    //        case 4:
			     //$interval = 'years';
      //      break;
            default:
				$i_max = $item['rec_time_x']*$repeat_time;
				$i_add = $repeat_time;
            break;
        }
        $exc_events = $this->getExceptionEvents($item,$begin,$end);
		for($i=0,$c=1;$i<$i_max||$recend>0;$i+=$i_add,$c++){
               $newitem = $item;
               $begin = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['begin']).'+'.$i.$interval);
               if($skip){
               	   	while(1){
			            if(strpos($skip,strftime('%w',$begin)) === false)
		            		break;
		            	else{
					        $i_max++;
					        $i++;
					        $begin = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['begin']).'+'.$i.$interval);
		           		}
		           	}
		    	 	$end = strtotime(strftime('%Y-%m-%d %H:%M',$item['end']).' +'.$i.$interval);
    	            $exc_events = $this->getExceptionEvents($item,$begin,$end);
			   }
               if($item['event_type']==2 && $begin >= $from_week){
               			$cc=0;
               			$sub = strftime('%w',$begin);
               			$sub =!$sub?6:$sub-1;
	               	   	for($add=0;$add<7;$add++){
				            if($weekdays[$add]){
		            		    $cc++;
		       					$newitem['begin'] = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['begin']).'-'.$sub.'days +'.$add.'days +'.$i.$interval);
			       				if($newitem['begin'] < $end && $newitem['begin'] >= $item['begin'] && $newitem['begin'] >= $from){
		       					  if(($this->jwVars['firstuid'] && $newitem['begin'] <= $to) || (!$this->jwVars['firstuid'] && $newitem['begin'] < $to)){
			       						if($item['end'])
			       							$newitem['end'] = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['end']).'-'.$sub.'days +'.$add.'days +'.$i.$interval);
						       			$newitem['chuid'] = $c;
						       			$newitem['chchuid'] = $cc;
					       				$exc_event = $this->testExceptionEvents($exc_events,$newitem['begin'],$newitem['end']);
						       			if(!$exc_event &&  !$this->testLastFirstUid($itemarray,$newitem,$from,$to)){
           									$itemarray[]= $newitem;
           								}
               					  }
               					}
				   			}
		           		}
               }
	           if($begin>=$from){
              		if(($this->jwVars['firstuid'] && $begin > $to) || (!$this->jwVars['firstuid'] && $begin >= $to) || $begin >= $end){
               			break;
               		}
               		if($item['event_type']!=2){
	   					if(!($exc_event &&  ($begin >= $exc_event['begin'] && $begin <= $exc_event['end']))){
   		           			$newitem['begin'] = $begin;
				       		if($item['end']>0)
				       			$newitem['end'] = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['end']).'+'.$i.$interval);
		    		   		$newitem['chuid'] = $c;
		       				$exc_event = $this->testExceptionEvents($exc_events,$newitem['begin'],$newitem['end']);
				    		if(!$exc_event && !$this->testLastFirstUid($itemarray,$newitem,$from,$to)){
  									$itemarray[]= $newitem;
							}
            		   	}
               		}
               }
 			   $exc_event = $this->testExceptionEvents($exc_events,$begin,strtotime(strftime('%Y-%m-%d %H:%M:0',$item['end']).'+'.$i.$interval));
			   if($exc_event){
			   		 $i_max+=$i_add;
			   		 if(!$recend){
				    	 if($item['rec_end_date'] && $end >= $item['rec_end_date'])
				    	 	 $end=$item['rec_end_date'];
				    	 else{
			    	         $exc_events = $this->getExceptionEvents($item,$begin,$end);
				    	 	 $end = strtotime(strftime('%Y-%m-%d %H:%M',$end).' +'.$i_add.$interval);
			    	     }
		       		}
		       }
        }
        return $itemarray;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$itemarray: ...
	 * @param	[type]		$newitem: ...
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
  	function testLastFirstUid($itemarray,$newitem,$from,$to){
  		if($this->jwVars['lastuid']){
			if($this->jwVars['lastuid']==$newitem['uid']){
      			if($this->jwVars['chuid'] == $newitem['chuid']){
        			if($this->jwVars['chchuid'] && $newitem['chchuid'] > $this->jwVars['chchuid']){
						return false;
					}
				}
        		else if($this->jwVars['chuid'] && $newitem['chuid'] > $this->jwVars['chuid']){
					return false;
				}
				return true;
			}
			if($newitem['uid'] < $this->jwVars['lastuid'] && $from == $newitem['begin']){
				return true;
			}
		}
		else if($this->jwVars['firstuid']){
			if($newitem['uid'] == $this->jwVars['firstuid']){
    			if($this->jwVars['chuid'] == $newitem['chuid']){
        			if($newitem['chchuid'] && $newitem['chchuid'] < $this->jwVars['chchuid']){
						return false;
					}
				}
				else if($newitem['chuid'] && $newitem['chuid'] < $this->jwVars['chuid']){
					return false;
				}
				return true;
		    }
			if($newitem['uid'] > $this->jwVars['firstuid'] && $to == $newitem['begin']){
				return true;
			}
		}
    	return false;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$item: ...
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
  function setItems($item,$from,$to){
        if($item['event_type'])
        	return $this->setRecurItemsList($item,$from,$to);
        $itemarray = array();
        if(strtotime(strftime("%Y-%m-%d",$item['begin']))== strtotime(strftime("%Y-%m-%d",$item['end']))){
	        $itemarray[]=$item;
	         return $itemarray;
	    }
        $begin = $item['begin'];
        $end = $item['end'];
        if($item['end'] && $begin != $end){
		        $day = 60*60*24;
		        $lastday = strtotime(strftime('%Y-%m-%d',$item['end']));
		        $firstday = 0;
                $item['mday']= $item['begin'];
                $itemarray[]=$item;
           		$i=0;
                while($firstday<$lastday){
               		$i++;
               		$newitem = $item;
               		$newitem['begin'] = strtotime(strftime('%Y-%m-%d %H:%M',$item['begin']).'+'.$i.'day');
               		$itemarray[]= $newitem;
                  	$firstday = strtotime(strftime('%Y-%m-%d',$item['begin']).'+'.$i.'day');
                }
        }else
                $itemarray[]=$item;
        return $itemarray;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$exc_events: ...
	 * @param	[type]		$begin: ...
	 * @param	[type]		$end: ...
	 * @return	[type]		...
	 */
  	function testExceptionEvents($exc_events,$begin,$end=false){
		if (!is_array($exc_events))
			return false;
  		foreach ($exc_events as $event){
			if(($begin >= $event['begin'] && $begin < $event['end']) || ($end && $begin < $event['begin'] && $end >= $event['begin'])){
				return true;
			}
  		}
  		return false;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$item: ...
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
  	function getExceptionEvents($item,$from,$to){
		$exc_events=array();
  		if($item['exc_event']){
  			$groups = explode(',',$item['exc_event']);
  			foreach($groups as $group){
				$exc_event = $this->getExcEvent($group,$from,$to);
				if($exc_event){
	   				$exc_events[]=$exc_event;
	   			}
	   		}	
  		}
  		if($item['exc_group']){
  			$groups = explode(',',$item['exc_group']);
  			foreach($groups as $group){
				$exc_group = $this->getExcGroup($group);
				if(!$exc_group)return 0;
				$query  = ' SELECT * ';
	        	$query .= ', tx_jwcalendar_exc_events.uid as uid';
	        	$query .= ', tx_jwcalendar_exc_events.title as title';
        		$query .= ' FROM tx_jwcalendar_exc_events INNER JOIN tx_jwcalendar_exc_groups';
        		$query .= ' ON tx_jwcalendar_exc_events.exc_group = tx_jwcalendar_exc_groups.uid';
		    	$query .= ' WHERE 1 ';
		    	$query .= $this->enableFieldsExcEvents;
      			$query .= $this->enableFieldsExcGroups;
      			$query .= ' AND tx_jwcalendar_exc_events.exc_group = '.$exc_group['uid'];
      			$query .= " AND (($from >= begin AND $from < end) OR ('$from' < begin AND  $to >= begin))";
    			$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
		    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
        	    	$exc_events[] = $row;
        		}
        	}	
  		}
		return empty($exc_events)?0:$exc_events;
  	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$uid: ...
	 * @return	[type]		...
	 */
	function getExcGroup($uid){
        $query  = 'SELECT * ';
        $query .= ' FROM tx_jwcalendar_exc_groups';
        $query .= ' WHERE 1';
        $query .= $this->enableFieldsExcGroups;
       	$query .= " AND uid = $uid";
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
	    return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$uid: ...
	 * @param	[type]		$begin: ...
	 * @param	[type]		$end: ...
	 * @return	[type]		...
	 */
	function getExcEvent($uid,$begin,$end){
        $query  = 'SELECT * ';
        $query .= ', tx_jwcalendar_exc_events.uid as uid';
        $query .= ', tx_jwcalendar_exc_events.title as title';
        $query .= ' FROM tx_jwcalendar_exc_events';
        $query .= ' WHERE 1';
        $query .= $this->enableFieldsExcEvents;
       	$query .= " AND tx_jwcalendar_exc_events.uid = $uid";
   		$query .= " AND (($begin >= begin AND $begin < end) OR ($begin < begin AND $end >= begin))";
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
	    return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$from: ...
	 * @param	[type]		$to: ...
	 * @return	[type]		...
	 */
	function setExcEventBgColor($from,$to){
        $query  = 'SELECT * ';
        $query .= ', tx_jwcalendar_exc_events.uid as uid';
        $query .= ', tx_jwcalendar_exc_events.title as title';
        $query .= ', tx_jwcalendar_exc_groups.color as color';
        $query .= ' FROM tx_jwcalendar_exc_events INNER JOIN tx_jwcalendar_exc_groups';
        $query .= ' ON tx_jwcalendar_exc_events.exc_group = tx_jwcalendar_exc_groups.uid';
        $query .= ' WHERE 1';
        $query .= $this->enableFieldsExcEvents;
        $query .= $this->enableFieldsExcGroups;
       	$query .= ' AND tx_jwcalendar_exc_groups.bgcolor = 1 ';
       	$query .= " AND (begin >= '$from' AND begin <= '$to'
       				 OR (begin <= '$from' AND  end > '$from'))";
       	$query .= ' ORDER BY tx_jwcalendar_exc_events.priority';
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$entries = array();
	    while ($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				while($item['begin'] && $item['begin'] < $from){
					$item['begin'] = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['begin']).'+1 day');
				}
             	$itemnext = $item;
             	$item['first'] = true;
				$d = strftime('%d', $item['begin']);
				$m = strftime('%m', $item['begin']);
				$entries[$m][$d] = $item;
				$begin = strtotime(strftime('%Y-%m-%d %H:%M:0',$item['begin']).'+1day');
				while($begin < $item['end']){
					$d = strftime('%d', $begin);
					$m = strftime('%m', $begin);
 					$entries[$m][$d] = $itemnext;
 					$begin = strtotime(strftime('%Y-%m-%d %H:%M:0',$begin).'+1day');
					$itemnext['begin']=$begin;
 				}
        }
        return $entries;
	}

  	function submitLink($item,$day,$align='right'){
		if($day < strtotime(strftime('%m/%d/%Y',time())) || !$this->jwOptions['fe_entry']['fe_pencil'])return $item;
   		if($this->access){
  	    	if($this->access>0)
   	    		$this->formpid = $this->access;
	    	$params = array('uid' => $this->uid, 'begin'=> $day,'parent_time'=> $day, 'parent' => $this->pid,'parentview'=>$this->jwOptions['viewmode'],'cat' =>$this->jwVars['cat'], 'action' => 'emptyFeEntry');
	    	$img = "<img src=\"".t3lib_extMgm::siteRelPath($this->extKey).'pi1/edit4.gif'.'" title="'.$this->pi_getLL('feEntrySubmitEventButtonLabel').'" align="'.$align.'" />';
			if($GLOBALS[CLIENT][BROWSER]=='msie' && $align=='right')
	   			return $item.$this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid);
			else
   				return $this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid).$item;
		}
		return $item;	
  	}

  	function submitEventLink($item, $event,$align='right'){
   		if(!($this->jwOptions['fe_entry']['fe_pencil'] && $this->jwOptions['fe_entry']['enable']))return $item;
   		if($event['fe_user'] == $GLOBALS['TSFE']->fe_user->user['uid']){
        	$params = array('uid' => $this->uid, 'parent' => $this->pid,'parent_time'=>$event['begin'], 'parentview'=>$this->jwOptions['viewmode'],'cat' =>$this->jwVars['cat'], 'edituid' => $event['uid']);
	    	$params['action']='editFeEntry';
	    	$img = "<img src=\"".t3lib_extMgm::siteRelPath($this->extKey).'pi1/edit4.gif'.'" title="'.$this->pi_getLL('feEntrySubmitEventButtonLabel').'" align="'.$align.'" />';
			if($GLOBALS[CLIENT][BROWSER]=='msie' && $align=='right')
	   			return $item.$this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid);
			else
   				return $this->pi_linkTP($img,Array($this->prefixId=>$params),0,$this->formpid).$item;
		}
		return $item;	
	}

	

	
	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$events: ...
	 * @param	[type]		$link: ...
	 * @return	[type]		...
	 */


	function getImage($SingleImageT,$image){
		$img['file.']['maxW'] = $this->jwOptions['maxW'] ? $this->jwOptions['maxW'] : 100;
   		$img['file'] = 'uploads/'.$this->extPrefix().'/'.$image;
		return $this->cObj->substituteMarker($SingleImageT,'###image###',$this->cObj->IMAGE($img));
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$query: ...
	 * @return	[type]		...
	 */
  	function jw_queryMySql($query){
    	$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
    	$array = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
             $array[] = $row;
        }
        return $array;
  	}



	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
  	function queryOrganizer($orguid,$table='tx_jwcalendar_organizer'){
		$query  = 'SELECT * FROM '.$table.'	WHERE uid = "'.$orguid.'"';
    	$query  .= $this->cObj->enableFields($table);
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

  	function queryLocation($locuid){
		$query  = 'SELECT *
    			   FROM tx_jwcalendar_location 
    	 			WHERE uid = "'.$locuid.'"';
    	$query  .= $this->enableFieldsLocation;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

  	function getLocationLink($locuid){
		 $loc = $this->queryLocation($locuid);	
	     $params = array('uid' => $this->uid, 'locuid' => $locuid);
		 $params['action']='locationView';
		 $link = $this->pi_linkTP($loc['location'],Array($this->prefixId=>$params),$this->caching,$this->formpid);
		 return $link; 
  	}	

  	function getOrganizerLink($orguid,$table){
		 $org = $this->queryOrganizer($orguid,$table);	
	     $params = array('uid' => $this->uid, 'orguid' => $orguid);
		 $params['action']='organizerView';
		 $link = $this->pi_linkTP($org['name'],Array($this->prefixId=>$params),$this->caching,$this->formpid);
		 return $link; 
  	}	

	
	function makeEventLink($event,$day){ 
		$linkContent = $event["title"];
        if($this->jwOptions['show_event_begin'])
   	    	$linkContent = strftime($this->jwOptions['time_format'],$event['begin']).'<br />'.$linkContent;
       	if(!empty($event['directlink'])){
      		if(!$this->jwOptions['tooltip']['show'] || !is_array($event))
		 		$link = $this->pi_linkTP_keepPIvars($linkContent,array(),$this->caching,0,$event['directlink']);
			else			
		 		$link = $this->pi_getPageLink($event['directlink']);
        }else{
 			if($this->jwOptions['single']['cuid'])
				$params['uid'] = $this->jwOptions['single']['cuid'];
			else
				$params['uid'] = $this->uid;
			$params['cat'] = $this->jwVars['cat'];
			if($event['event_type'])
				$params['day']=	$day;
			$params['eventid'] = $event['uid'];
			$params['action']  = 'singleView';
      		if(!$this->jwOptions['tooltip']['show'] || !is_array($event)){
	   			$link = $this->pi_linkTP_keepPIvars($linkContent,$params,$this->caching,0,$this->jwOptions['single']['puid']);
			}else{
   				$link = $this->pi_linkTP_keepPIvars_url($params,$this->caching,0,$this->jwOptions['single']['puid']);
		    }    
		}
   		if($this->jwOptions['tooltip']['show']&& is_array($event))
		        $link = $this->getToolTip($linkContent,$event,$link);

        return $link;
	}


  function _addField2ToolTip($event,$conf,$br=true){
        if(empty($event))
	        return $event;
        $event = str_replace("\r\n",'<br />',strip_tags(chop($event))); //str_replace('\n','<br />',
        if($br)
        	$event .='<br />';
       	return $this->cObj->wrap($event,$conf);
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$event: ...
	 * @return	[type]		...
	 */
  	function _addFields2ToolTip($event,$last=false){
 		if(!empty($this->jwOptions['tooltip']['begin'])){
              if($event['mday'])
	              $str = strftime($this->jwOptions['tooltip']['begin_mdays'],$event['mday']);
	          else
              	  $str = strftime($this->jwOptions['tooltip']['begin'],$event['begin']);
              $str =$this->_addField2ToolTip($str,$this->conf['wrapTooltipBegin'],false).' ';
        }
        if(!empty($this->jwOptions['tooltip']['end']) && $event['end']){
              if($event['mday'])
	              $strt = strftime($this->jwOptions['tooltip']['end_mdays'],$event['end']);
	          else
	              $strt = strftime($this->jwOptions['tooltip']['end'],$event['end']);
              $str .=$this->_addField2ToolTip($strt,$this->conf['wrapTooltipEnd']);
        }
        if($this->jwOptions['tooltip']['title'] && !empty($event['title'])){
	         $str .= $this->_addField2ToolTip($event['title'],$this->conf['wrapTooltipTitle']);
	    }     
        if($this->jwOptions['tooltip']['teaser']&& !empty($event['teaser'])){
             $str .= $this->_addField2ToolTip($event['teaser'],$this->conf['wrapTooltipTeaser']);
		}
        if($this->jwOptions['tooltip']['description'] && !empty($event['description']))
            $str .= $this->_addField2ToolTip($event['description'],$this->conf['wrapTooltipDescription'],$last);
        return chop($str);
  	}

  	function getToolTip($linkContent,$event,$link=""){
      if( $this->jwOptions['tooltip']['show']){
             $tooltip = $this->_addFields2ToolTip($event);
             return $this->overlib($linkContent,$tooltip,$event,$link);
      }
      return $link;
  	}
  	
  	function getToolTips($linkContent,$events,$link){
      	if( $this->jwOptions['tooltip']['show'] && is_array($events)){
             foreach( $events as $event){
				 $tooltip .= $this->_addFields2ToolTip($event,true);
             }
			 return $this->linkToolTip($link,$linkContent, $tooltip,'',$this->conf['overlibConfig']);
      	}
		if(!$this->jwOptions['tooltip']['show'])
			$link = '<a href="'.$link.'">'.$linkContent.'<a>';
      	return $link;
  	}

	function overlib($linkContent,$tooltip,$event,$link){
		$caption = $this->conf['caption'];
		if($caption == 'begin' || $caption == 'end')
			$caption = $this->dateOutput($event[$caption]);
		else if($caption == 'day')
			$caption = $linkContent;
		else
			$caption = $event[$caption];
		$link= $this->linkToolTip($link,$linkContent,$tooltip,$caption,$this->conf['overlibConfig']);		
		return $link;
	}
//
	function linkToolTip($link,$linkContent, $boxContent, $caption="", $config='')	{
		//$aTagParams='class="jwcalendar_ov"'; 
		$config=trim($config);
		if ($caption) $config .= ", CAPTION, '".t3lib_div::slashJS($caption,true)."'";
		if ($config) $config=str_replace(',,',',',','.trim($config));
		return '<a href="'.$link.'" '.$aTagParams.' onmouseover="return overlib.ov(\''.t3lib_div::slashJS($boxContent,true).'\''.$config.');" onmouseout="return overlib.nd();">'.nl2br($linkContent).'</a>';
	}

  } //endclass

  	function debA($a){
		foreach($a as $w => $s)
			echo "$w => ".$s.' <br>'; //['uid']
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$a: ...
	 * @return	[type]		...
	 */
  function debB($a){
		foreach($a as $w => $s){
			echo "$w => ".$s['title'].strftime('%d-%m-%Y',$s['begin']).'  uid:'.$s['uid'].' <br>'; //['uid']
			echo "$w => ".$s['description'].' <br>'; //['uid']
			//foreach($s as $ww => $ss)
			//	echo "$ww => ".$ss.' <br>'; //['uid']
		}

  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$aItem: ...
	 * @param	[type]		$bItem: ...
	 * @return	[type]		...
	 */
  function jwSort($aItem,$bItem){
	if($aItem['begin']>$bItem['begin']){
		return 1;
	}elseif($aItem['begin']<$bItem['begin']){
		return -1;
	}
	if($aItem['uid']<$bItem['uid']){
		return -1;
	}elseif($aItem['uid']>$bItem['uid'])
		return 1;
	return 0;
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$aItem: ...
	 * @param	[type]		$bItem: ...
	 * @return	[type]		...
	 */
  function jwSortReverse($aItem,$bItem){
	if($aItem['begin']<$bItem['begin']){
		return 1;
	}elseif($aItem['begin']>$bItem['begin']){
		return -1;
	}
	if($aItem['uid']<$bItem['uid']){
		return 1;
	}elseif($aItem['uid']>$bItem['uid'])
		return -1;
	return 0;
  }

// jwitt 10.04 end

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_library.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_library.php']);
}


?>