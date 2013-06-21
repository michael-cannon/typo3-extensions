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
 *   74: class tx_jwcalendar_pi1_feEntryView extends tx_jwcalendar_pi1_library
 *   80:     function tx_jwcalendar_pi1_feEntryView($cObj,$conf,$jwOptions)
 *  101:     function getEmptyForm($edit=false)
 *  124:     function getEditForm()
 *  133:     function getUserEntries()
 *  149:     function getPreview()
 *  188:     function insertFeEntry()
 *  229:     function notificationMail($vars)
 *  244:     function prepareVars($vars)
 *  270:     function checkErrors(&$vars)
 *  332:     function getFE_CategoriesUid($select)
 *  348:     function getFE_Categories()
 *  369:     function makeForm($vars=array())
 *  415:     function makeConfirm($vars,$dberror)
 *  429:     function MakePreview($row)
 *  445:     function makeLabels(&$sims)
 *  470:     function dateInput($name, $vars)
 *  498:     function daySelect($name, $value)
 *  510:     function monthSelect($name, $value)
 *  524:     function yearSelect($name, $value, $from, $to )
 *  536:     function hourSelect($name, $value)
 *  549:     function minuteSelect($name, $value, $step)
 *  560:     function handleUpload()
 *  582:     function mimeAllowed($mime)
 *  596:     function extAllowed($filename)
 *  616:     function fileTooBig($filesize)
 *  629:     function getmxrr($hostname, &$mxhosts)
 *  646:     function check_email($email)
 *
 * TOTAL FUNCTIONS: 27
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_singleEventView.php');
require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_library.php');
require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_htmlmail.php');
require_once(PATH_t3lib.'class.t3lib_formmail.php');

class tx_jwcalendar_pi1_feEntryView extends tx_jwcalendar_pi1_library {
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';

  /* Constructor */

	function tx_jwcalendar_pi1_feEntryView($cObj,$conf,$jwOptions){
	   $this->conf = $conf;
	   $this->cObj = $cObj;
       $this->jwOptions = $jwOptions;

      $this->backwardYears = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_backwardYears','s_FE_Entries');
      $this->forwardYears = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_forwardYears','s_FE_Entries');
      if(!$this->forwardYears)$this->forwardYears=2;
      $this->minuteStep = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_minuteStep','s_FE_Entries');
	  $FE_Page =$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Page','s_FE_Entries');
      if($FE_Page)
    		$this->formpid = $FE_Page;
	  $this->FE_Image_alowed = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Image_alowed','s_FE_Entries');
	  $this->FE_editFE_Entries = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_editFE_Entries','s_FE_Entries');
    /* List of used dbfields */
	    $this->dbFieldList = ' pid,category, hidden, title, teaser, description, link, image, begin, end, location, organiser, email,fe_user';
	    $this->dbFieldArray = preg_split("/[\s,]+/", $this->dbFieldList);
	}

  /* Workflows */

	function getEmptyForm($edit=false){
	    $ta = $this->makeForm($this->jwVars);
	    $ta .= $this->hiddenInput('update', $edit);
	    if($edit)
    		$ta .= $this->hiddenInput('edituid', $this->jwVars['edituid']);
    	$ta .= $this->hiddenInput('eventuid', $this->jwVars['eventuid']);
    	$ta .= $this->hiddenInput('uid', $this->jwVars['uid']);
	    $ta .= $this->hiddenInput('parent',$this->jwVars['parent']);
        $ta .= $this->hiddenInput('parentview',$this->jwVars['parentview']);
        $ta .= $this->hiddenInput('parent_time',$this->jwVars['parent_time']);
        $ta .= $this->hiddenInput('cat',$this->jwVars['cat']);
    	$ta .= $this->hiddenInput('action', 'previewFeEntry');
	    $ta = $this->wrapForm($ta);
	    if($this->FE_editFE_Entries)
    		$ta .= $this->getUserEntries();
		return $ta;
    }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getEditForm(){
		return $this->getEmptyForm(true);
    }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
    function getUserEntries(){
		if($GLOBALS['TSFE']->fe_user->user['uid'] > 0){
			$View = $this->getClass('editFE_UserEntries');
			$res=$View->queryUpcomingEventsMessenger();
			if(empty($res))return "";
			else $View->query=$res;
			$out = $View->getForm();
		}
		return $out;
    }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getPreview(){
        $vars = $this->jwVars;
		// strip slashes
 	  	$vars = $this->strslRecurs($vars);
    /* for all: do some evaluations to prepare input  */
		$vars = $this->prepareVars($vars);
		if($this->checkErrors($vars)){
	    /* recall Form */
		    $vars = $this->hscRecurs($vars); // make html special characters for form
	    	$ta = $this->makeForm($vars);
	    	$ta .= $this->hiddenInput('action', 'previewFeEntry');
		}else{
	    /* create preview, use htmlspecialchars */
			$vars['image'] = $this->handleUpload();
		   	$ta = $this->makePreview($vars);
	    /* prepare hidden vars, encode them, remember slashes are stripped!!! */
	    	$cvar = $this->encRecurs($vars);
	    	foreach($this->dbFieldArray as $field)
	      		$ta .= $this->hiddenInput($field, $vars[$field]);
		    $ta .= $this->hiddenInput('action', 'insertFeEntry');
	    }
	    $ta .= $this->hiddenInput('update', $vars['update']);
	    if($vars['update']==1)
		    $ta .= $this->hiddenInput('edituid', $vars['edituid']);
	    $ta .= $this->hiddenInput('eventuid', $this->jwVars['eventuid']);
	    $ta .= $this->hiddenInput('uid', $vars['uid']);
        $ta .= $this->hiddenInput('parent',$vars['parent']);
        $ta .= $this->hiddenInput('parentview',$vars['parentview']);
        $ta .= $this->hiddenInput('parent_time',$vars['parent_time']);
        $ta .= $this->hiddenInput('cat',$this->jwVars['cat']);
        $ta = $this->wrapForm($ta);
  		return $ta;
  }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
    function insertFeEntry(){
         $vars = $this->jwVars;
	  	 if($vars['parent'])
      		$this->formpid = $vars['parent'];

	  /* decode $vars (and add slashes) */
		/* no it don't want's slashes, probalbly DBgetInsert adds them itself */
    	$vars = $this->decRecurs($vars);
  		//$vars['category'] = $this->getFE_CategoriesUid($vars['category']);
		$pid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_storagePid','s_FE_Entries');
	  	if($pid)$vars['pid']=$pid;
	  	$pid = is_numeric($pid) ? $pid : $this->pid;
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_showControlled','s_FE_Entries'))
        	$vars['hidden']=1;
		if($GLOBALS['TSFE']->fe_user)$vars['fe_user'] = $GLOBALS['TSFE']->fe_user->user['uid'];
    	$table = 'tx_jwcalendar_events';
    	$fieldlist = $this->dbFieldList;
    	if($vars['update']){
		  	$query = $this->cObj->DBgetUpdate($table, $vars['edituid'], $vars, $fieldlist);
    	}else
	  		$query = $this->cObj->DBgetInsert($table, $pid, $vars, $fieldlist);
	  	$res=$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$ta = $this->makeConfirm($vars,$GLOBALS['TYPO3_DB']->sql_error());
  		if($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Mail_enabled','s_FE_Entries'))
  			$this->notificationMail($vars);
	    $ta .= $this->hiddenInput('uid', $vars['uid']);
        $ta .= $this->hiddenInput('parent',$vars['parent']);
        $ta .= $this->hiddenInput('view',$vars['parentview']);
	    $ta .= $this->hiddenInput('time', $vars['begin']);
    	if($vars['update'])
	    	$ta .= $this->hiddenInput('eventuid', $vars['edituid']);
	    $GLOBALS['TSFE']->clearPageCacheContent_pidList($this->formpid);
		return $this->wrapForm($ta);
   }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$vars: ...
	 * @return	[type]		...
	 */
   function notificationMail($vars){
    	$vars['recipient'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Mail_recipient','s_FE_Entries');
    	$vars['subject'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Mail_subject','s_FE_Entries');
    	$vars['from_email'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Mail_from','s_FE_Entries');
	    $vars['begin'] = strftime($this->jwOptions['date_format'].' - '.$this->jwOptions['time_format'],$vars['begin']);
	    if(!empty($vars['end']))
	    	$vars['end'] = strftime($this->jwOptions['date_format'].' - '.$this->jwOptions['time_format'],$vars['end']);
		$vars['success'] = $GLOBALS['TYPO3_DB']->sql_error();
    	$htmlmail = t3lib_div::makeInstance('t3lib_formmail');
		$htmlmail->start($vars);
		$res = $htmlmail->sendtheMail();
   }

  /* Workers */

	function prepareVars($vars){
		/* begin via am. format */
 		$vars['begin_date'] = strtotime($vars['begin_month'].'/'.$vars['begin_day'].'/'.$vars['begin_year']);

		$vars['begin_time'] = strtotime($vars['begin_hour'].':'.$vars['begin_minute']);
    $vars['begin'] = strtotime($vars['begin_month'].'/'.$vars['begin_day'].'/'.$vars['begin_year'].' '.$vars['begin_hour'].':'.$vars['begin_minute']);

		/* end via am. format */
    $vars['end_date'] = strtotime($vars['end_month'].'/'.$vars['end_day'].'/'.$vars['end_year']);
    $vars['end_time'] = strtotime($vars['end_hour'].':'.$vars['end_minute']);
    $vars['end'] = strtotime($vars['end_month'].'/'.$vars['end_day'].'/'.$vars['end_year'].' '.$vars['end_hour'].':'.$vars['end_minute']);

		/* some tidings */
    $vars['begin'] = $vars['begin'] < 0 ? "" : $vars['begin'];
    $vars['end'] = $vars['end'] < 0 ? "" : $vars['end'];
		$vars['link'] = ($vars['link'] == 'http://') ? "": $vars['link'];

	  return $vars;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$$vars: ...
	 * @return	[type]		...
	 */
  function checkErrors(&$vars){
    /* begin */
		if($vars['begin_date'] < 0)	 $this->errors[] = $this->pi_getLL('beginDayError');
		if($vars['begin_time'] < 0)	 $this->errors[] = $this->pi_getLL('beginTimeError');

    /* end: check if any field is set */
    if( is_numeric($vars['end_month']) || is_numeric($vars['end_day']) || is_numeric($vars['end_year']) || is_numeric($vars['end_hour']) || is_numeric($vars['end_minute'])){
	    if($vars['end_date'] < 0) $this->errors[] = $this->pi_getLL('endDayError');
	    if($vars['end_time'] < 0) $this->errors[] = $this->pi_getLL('endTimeError');
      if($vars['begin'] > 0 && $vars['end'] > 0 && $vars['begin'] >= $vars['end'])
				$this->errors[] = $this->pi_getLL('timeOrderError');
		}

    /* Empty and format checks */
		$vars['teaser']=nl2br(strip_tags($vars['teaser']));
		$vars['title']=strip_tags($vars['title']);
		if(empty($vars['title'])) $this->errors[] = $this->pi_getLL('emptyTitleError');
		$vars['description']=nl2br(strip_tags($vars['description']));
		if(empty($vars['description'])) $this->errors[] = $this->pi_getLL('emptyDescriptionError');
		$vars['location']=strip_tags($vars['location']);
		if(empty($vars['location'])) $this->errors[] = $this->pi_getLL('emptyLocationError');
		$vars['organiser']=strip_tags($vars['organiser']);
		if(empty($vars['organiser'])) $this->errors[] = $this->pi_getLL('emptyOrganiserError');
	    if($this->check_email($vars['email'])==false)
			$this->errors[] = $this->pi_getLL('emailFormatError');

	if($this->FE_Image_alowed && !empty($_FILES[$this->prefixId]['name']['image'])){
		$uploaddir = PATH_site.'uploads/'.$this->extPrefix();
		$uploadfile = $uploaddir.'/'.$_FILES[$this->prefixId]['name']['image'];
		$res = false;
		if(is_file($uploadfile) && !$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Image_noOverwrite','s_FE_Entries')){//file already exists?
			$this->errors[]=$this->pi_getLL('FileExistError');
			$res = true;
		}

		if($this->fileTooBig($_FILES[$this->prefixId]['size']['image'])){
			$this->errors[]=$this->pi_getLL('FileMaxSizeError');
			$res = true;
		}

		if(!$this->mimeAllowed($_FILES[$this->prefixId]['type']['image'])){ //mimetype allowed?
			$this->errors[]=$this->pi_getLL('FileMimeError');
			$res = true;
		}

		if(!$this->extAllowed($_FILES[$this->prefixId]['name']['image'])){ //extension allowed?
			$this->errors[]=$this->pi_getLL('FileExtensionError');
			$res = true;
		}
		if($res){
			$res = unlink ($_FILES[$this->prefixId]['tmp_name']['image']);
		}
	}
	return (count($this->errors));
  }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$select: ...
	 * @return	[type]		...
	 */

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$vars: ...
	 * @return	[type]		...
	 */
 	function makeForm($vars=array()){

	  	$size = 40;
    	$cols = 30;
   		$rows = 13;

    	/* the template */
  		$feEntryFormT = $this->cObj->getSubpart($this->templateCode, '###feEntryForm###');
   		$errors = $this->makeErrorlist();
   		//$sims['###htmlarea###'] = $this->gethtmlAreaScript('http://dragon/dummy/typo3conf/ext/jw_calendar/pi1/htmlarea/'); //t3lib_extMgm::extPath('jw_calendar').'pi1/htmlarea/'
   		//$sims['###htmlarea###'] .= $this->gethtmlAreaScript4TextArea("tx_jwcalendar_pi1[description]");
   		$sims['###CUT_errors###'] = $errors?$this->cObj->substituteMarker($this->cObj->getSubpart($feEntryFormT, '###CUT_errors###'),'###errors###',$errors):"";
    	$this->makeLabels($sims);
		$categories = $this->getFE_Categories();
		if(!$categories)$categories[]='Select at least one Categorie for FE-Entry in the BE';
		$sims['###categoryInput###'] = $this->selectInput('category',$categories,$vars['cat']);
    	/* inputs */
    	$sims['###beginInput###'] = $this->dateInput('begin', $vars);
    	$sims['###endInput###'] = $this->dateInput('end', $vars);
	    $sims['###titleInput###'] = $this->textInput('title', strip_tags($vars['title']), $cols);
    	$sims['###teaserInput###'] = $this->areaInput('teaser', strip_tags($vars['teaser']), $rows, $cols);
	    $sims['###descriptionInput###'] = $this->areaInput('description', strip_tags($vars['description']),$rows, $cols);

		if($this->FE_Image_alowed){
   			$sims_tmp['###imageLabel###'] = $this->pi_getLL('imageLabel');
			$sims_tmp['###imageInput###'] = $this->fileInput('image', $size);
			$sims['###CUT_image###'] = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($feEntryFormT, '###CUT_image###'),$sims_tmp);
		}else
			$sims['###CUT_image###'] = '';

	    $sims['###linkInput###'] = $this->textInput('link', $vars['link'], $cols);
    	$sims['###locationInput###'] = $this->textInput('location', strip_tags($vars['location']), $cols);
	    $sims['###organiserInput###'] = $this->textInput('organiser', strip_tags($vars['organiser']), $cols);
    	$sims['###emailInput###'] = $this->textInput('email', $vars['email'], $cols);

		$params = array('uid'=>$vars['uid'],'time'=>$vars['parent_time'], 'cat' => $this->jwVars['cat'],'eventuid' => $this->jwVars['eventuid'],'view'=>$vars['parentview']);
		$backlink = $this->pi_linkTP_keepPIvars_url($params,$this->caching,0,$vars['parent']);
    	$sims['###submit###'] = $this->backButton($backlink,$this->pi_getLL('backTo').$this->pi_getLL($vars['parentview'])).$this->submitInput($this->pi_getLL('previewButtonLabel'), 'submit',true);
		return $this->cObj->substituteMarkerArrayCached($feEntryFormT,array(),$sims);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$vars: ...
	 * @param	[type]		$dberror: ...
	 * @return	[type]		...
	 */
    function makeConfirm($vars,$dberror){
  		$confirmFormT = $this->cObj->getSubpart($this->templateCode, '###confirmForm###');
		$sims['###CUT_dberror###'] = $dberror?$this->cObj->substituteMarker($this->cObj->getSubpart($confirmFormT, '###CUT_dberror###'),'###dbError###',$this->pi_getLL('dbError')):"";
		$sims['###CUT_success###'] = $dberror?"":$this->cObj->substituteMarker($this->cObj->getSubpart($confirmFormT, '###CUT_success###'),'###thxForEntryMessage###',$this->pi_getLL('thxForEntryMessage'));
        $sims['###confirmEntry###'] = $this->submitInput($this->pi_getLL('backTo').$this->pi_getLL($vars['parentview']));
		return $this->cObj->substituteMarkerArrayCached($confirmFormT,array(),$sims);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function MakePreview($row){
		$params = array('uid'=>$row['uid'],'time'=>$row['parent_time'], 'cat' => $this->jwVars['cat'],'eventuid' => $this->jwVars['eventuid'], 'view'=>$row['parentview']);
		$backlink = $this->pi_linkTP_keepPIvars_url($params,$this->caching,0,$row['parent']);
    	$row['submit'] = $this->backButton($backlink,$this->pi_getLL('backTo').$this->pi_getLL($row['parentview'])).$this->submitInput($this->pi_getLL('saveButtonLabel'),'submit',true);
		$categories = $this->getFE_Categories();
		$row['category'] = $categories[$row['category']];
		return  tx_jwcalendar_pi1_singleEventView::buildTemplateArray($row);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$$sims: ...
	 * @return	[type]		...
	 */
	function makeLabels(&$sims){
    	$sims['###viewTitle###'] = $this->jwVars['submit'];
		//    $sims['viewTitle'] = $this->pi_getLL('feEntryFormTitle');
    	$sims['###beginLabel###'] = $this->pi_getLL('beginLabel');
    	$sims['###endLabel###'] = $this->pi_getLL('endLabel');
    	$sims['###categoryLabel###'] = $this->pi_getLL('categoryLabel');
    	$sims['###titleLabel###'] = $this->pi_getLL('titleLabel');
    	$sims['###teaserLabel###'] = $this->pi_getLL('teaserLabel');
    	$sims['###descriptionLabel###'] = $this->pi_getLL('descriptionLabel');
    	$sims['###linkLabel###'] = $this->pi_getLL('linkLabel');
    	$sims['###locationLabel###'] = $this->pi_getLL('locationLabel');
    	$sims['###organiserLabel###'] = $this->pi_getLL('organiserLabel');
    	$sims['###emailLabel###'] = $this->pi_getLL('emailLabel');
    	//$sims['###imageLabel###'] = $this->pi_getLL('imageLabel');
	}

	  /**
 * Date input with select iputs for year, month, day, hour, minute.
 * Local Language configuration: inputDate.
 * Setup configuration: minuteStep for the minutes select.
 *
 * @param	[type]		$name: ...
 * @param	[type]		$vars: ...
 * @return	string,		with the html selects
 */
  
	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$mime: ...
	 * @return	[type]		...
	 */
	function mimeAllowed($mime){
		$includelist = explode(',',$this->conf['feEntryEntries.']['image.']['mimeInclude']);
		$excludelist = explode(',',$this->conf['feEntryEntries.']['image.']['mimeExclude']);		//overrides includelist
		//debug($excludelist);
		//debug($includelist);
		return (   (in_array($mime,$includelist) || in_array('*',$includelist))   &&   (!in_array($mime,$excludelist))  );
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$filename: ...
	 * @return	[type]		...
	 */
	function extAllowed($filename){
		$includelist = explode(',',$this->conf['feEntryEntries.']['image.']['extInclude']);
		$excludelist = explode(',',$this->conf['feEntryEntries.']['image.']['extExclude']);	//overrides includelist
		//debug($excludelist);
		//debug($includelist);
		$extension='';
		if($extension=strstr($filename,'.')){
			$extension=substr($extension, 1);
			return ((in_array($extension,$includelist) || in_array('*',$includelist)) && (!in_array($extension,$excludelist)));
		} else {
			return FALSE;
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$filesize: ...
	 * @return	[type]		...
	 */
	function fileTooBig($filesize){
		$fsize = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Image_maxSize','s_FE_Entries');
		if(!$fsize)$fsize=50000;
		return $filesize > $fsize;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$hostname: ...
	 * @param	[type]		$mxhosts: ...
	 * @return	[type]		...
	 */
	function getmxrr($hostname, &$mxhosts){
   		$mxhosts = array();
   		exec('nslookup -type=mx '.$hostname, $result_arr);
   		foreach($result_arr as $line)
   		{
     	if (preg_match('/.*mail exchanger = (.*)/', $line, $matches))
        	 $mxhosts[] = $matches[1];
   		}
   		return( count($mxhosts) > 0 );
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$email: ...
	 * @return	[type]		...
	 */
	function check_email($email){
   		return true;
    	if(eregi("^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}\$",$email)){
			$email = explode('@',$email);
		    $host = $email[1];
    		$host=$host.'.';
			if($GLOBALS['WINDIR'])
				$hosts=$this->getmxrr($host, $mxhosts);
			else
				$hosts=getmxrr($host, $mxhosts);
			if($hosts)
        		return true;
    	}
        return false;
    }
    
	function gethtmlAreaScript($path){
		return "";//HTMLArea.replaceAll();
		/*$script="		
			<script language=\"Javascript1.2\">
   			_editor_url = \"".$path."\";
   			_editor_lang = \"en\";
   			</script>
   			<script language=\"Javascript1.2\" src=\"".$path."htmlarea.js\"></script>	
   			<script language=\"Javascript1.2\" src=\"".$path."htmlarea_css.js\"></script>	
			<script language=\"Javascript1.2\" defer=\"1\">
    		HTMLArea.replace(\"tx_jwcalendar_pi1[description]\");
			</script>";
		return $script;*/
	 	$script="	
    	<script language=\"Javascript1.2\"><!-- // load htmlarea
		    _editor_url = \"".$path."\";                     // URL to htmlarea files
   			_editor_lang = \"en\";
			var win_ie_ver = parseFloat(navigator.appVersion.split(\"MSIE\")[1]);
			if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
			if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
			if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
			if (win_ie_ver >= 5.5) {
 				document.write('<scr' + 'ipt src=\"' +_editor_url+ 'htmlarea.js\"');
 				document.write(' language=\"Javascript1.2\"></scr' + 'ipt>');  
			//} else { 
			//	document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>'); 
			/}
    		document.write('HTMLArea.replace(\"tx_jwcalendar_pi1[description]\"));
			// --></script>";
		return $script;		
	}

	function gethtmlAreaScript4TextArea($fieldname){
		return "";
	 	$script="<script language=\"JavaScript1.2\" defer>
			var config = new Object(); // create new config object
			config.width = \"90%\";
			config.height = \"200px\";
			config.bodyStyle = 'background-color: white; font-family: \"Verdana\"; font-size: x-small;';
			config.debug = 0;
			config.toolbar = [
  				['fontname'],
  				['fontsize'],
  			//	['fontstyle'],
  			//	['linebreak'],
  				['bold','italic','underline','separator'],
  				['strikethrough','subscript','superscript','separator'],
  				['justifyleft','justifycenter','justifyright','separator'],
  				['OrderedList','UnOrderedList','Outdent','Indent','separator'],
  				['forecolor','backcolor','separator'],
			//	//['custom1','custom2','custom3','separator'],
  			//	['HorizontalRule','Createlink','htmlmode','separator'],
  				//['about','help']
];

				editor_generate('".$fieldname."',config);
				</script>";
	 	return $script;	
	}	
			
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_feEntryView.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_feEntryView.php']);
}

?>
