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
 *   46: class tx_jwcalendar_pi1 extends tx_jwcalendar_pi1_library
 *   54:     function main($content,$conf)
 *  139:     function displayHelpFile()
 *  157:     function init()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
require_once(t3lib_extMgm::extPath("overlib")."class.tx_overlib.php");
require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_library.php');

class tx_jwcalendar_pi1 extends tx_jwcalendar_pi1_library{

    var $extKey = 'jw_calendar';    // The extension key.
    var $prefixId = 'tx_jwcalendar_pi1';        // Same as class name
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';
	var $jwVars = array();

	function main($content,$conf){
		if($conf['static_uid']){
			$m_uid = $this->cObj->data['uid'];
			$this->cObj->data = $this->pi_getRecord('tt_content',$conf['static_uid']);
			$this->cObj->data['pid']= $m_uid;
		}
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->conf=$conf;
        $this->configure();
        $this->init();
		$this->conf['language']=$this->LLkey;
        $viewmode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'viewmode');
		if($viewmode == 'HELP')
        	return $this->displayHelpFile();
		//$GLOBALS['TSFE']->indexedDocTitle = $row['title'];
		$vars = t3lib_div::GPvar($this->prefixId);
		if(is_array($vars))
			$this->jwVars = array_merge($vars,$this->jwVars);
		if($this->jwVars['view'] && $this->jwVars['uid']==$this->uid)
			$viewmode = $this->jwVars['view'];
  		if($this->jwVars['action']
  			&& ($this->jwVars['uid']==$this->uid || $viewmode == "FE_ENTRY" ||  $viewmode == 'SINGLE')){
		    		switch($this->jwVars['action']){
    		    			case 'emptyFeEntry':
          						$View = $this->getClass('feEntryView');
	      						$out = $View->getEmptyForm();
		    				break;
				  			case 'previewFeEntry':
		    	  				$View = $this->getClass('feEntryView');
	    	  					$out = $View->getPreview();
		    				break;
		    				case 'insertFeEntry':
		    	  				$View = $this->getClass('feEntryView');
				  				$out = $View->insertFeEntry();
	    					break;
		    				case 'editFeEntry':
		    	  				$View = $this->getClass('editFE_UserEntries');
				  				$out = $View->editFeEntry();
	    					break;
	    					case 'confirmDeleteFeEntry':
		    	  				$View = $this->getClass('editFE_UserEntries');
				  				$out = $View->confirmDelete();
	    					break;
		    				case 'deleteFeEntry':
		    	  				$View = $this->getClass('editFE_UserEntries');
				  				$out = $View->deleteFeEntry();
	    					break;
					  		case 'singleView':
          						$View = $this->getClass('singleEventView');
          						$out = $View->getForm();
		    				break;
					  		case 'organizerView':
          						$View = $this->getClass('singleEventView');
          						$out = $View->getOrganizerView();
		    				break;
					  		case 'locationView':
          						$View = $this->getClass('singleEventView');
          						$out = $View->getLocationView();
		    				break;
					  		case 'upcomingView':
		  						$View = $this->getClass('upcomingEventsView');
	   							$out = $View->getForm();
	   						break;
					  		case 'dayView':
		  						$View = $this->getClass('dayView');
	   							$out = $View->getForm();
	   						break;
	    			} //switch
		}else{
			if($viewmode == 'MONTH'){
       			$View = $this->getClass('views');
				$out = $View->monthView();
			}
			else if($viewmode == 'LIST'){
				$View = $this->getClass('upcomingEventsView');
				$out = $View->getForm();
			}
			else if($viewmode == 'WEEK'){
				$View = $this->getClass('weekView');
				$out = $View->weekView();
			}
			else if($viewmode == 'DAY'){
				$View = $this->getClass('dayView');
				$out = $View->getForm();
			}
			else if($viewmode == 'SPEC'){
				$View = $this->getClass('specViews');
				$out = $View->getForm();
			}
 			else if($viewmode == 'FE_ENTRY'){
	       		$View = $this->getClass('feEntryView');
	    		$out = $View->getEmptyForm();
 			}
 		}
 		return $this->mainExtended($out);
	}

	function mainExtended($out){
		return $this->pi_wrapInBaseClass($out);
	}	

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function displayHelpFile(){
   		if(isset($this->conf['helpFile'])){
	    	$this->templateCode = $this->cObj->fileResource($this->conf['helpFile']);
	    	if ($this->templateCode=='') {
	      		return "<h3>jw_calendar: no helpfile found:</h3>".$this->conf['helpFile'];
	    	}
   	  		$helpT = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_DEFAULT###');
	    	$img=t3lib_extMgm::siteRelPath($this->extKey).'pi1/code.gif';
	    	return $this->cObj->substituteMarker($helpT,'###IMGPATH###',$img);

		}
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function init(){
		$this->caching=1;
	//	$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		//$overlibConfig = explode(',',$this->conf['overlibConfig']);
		$overlibConfig['ol_css']= 'CSSCLASS';
		$overlibConfig['ol_bgclass'] = '"jwcalendar_olbg"';		
		$overlibConfig['ol_fgclass'] = '"jwcalendar_olfg"';		
		$overlibConfig['ol_captionfontclass'] = '"jwcalendar_olcf"';		
		$overlibConfig['ol_textfontclass'] = '"jwcalendar_oltf"';		
		tx_overlib::setDefaults($overlibConfig);
		//unset($GLOBALS['tx_overlib']['defaults']['ol_fgcolor']);
		//unset($GLOBALS['tx_overlib']['defaults']['ol_bgcolor']);
		tx_overlib::includeLib();

        $this->pid = $this->cObj->data['pid'];
        $this->uid = $this->cObj->data['uid'];
        $this->formpid = $this->pid;
        $this->jwOptions['date_format'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'dateFormat');
     	if(empty($this->jwOptions['date_format']))
     		$this->jwOptions['date_format'] = '%a, %b %d, %Y';

     	$this->jwOptions['time_format'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'timeFormat');
     	if(empty($this->jwOptions['time_format']))
			$this->jwOptions['time_format'] = '%H:%M';

		$this->enableFieldsCategories = $this->cObj->enableFields('tx_jwcalendar_categories');
		$this->enableFieldsEvents = $this->cObj->enableFields('tx_jwcalendar_events');
		$this->enableFieldsExcEvents = $this->cObj->enableFields('tx_jwcalendar_exc_events');
		$this->enableFieldsExcGroups = $this->cObj->enableFields('tx_jwcalendar_exc_groups');
		$this->enableFieldsOrganizer = $this->cObj->enableFields('tx_jwcalendar_organizer');
		$this->enableFieldsLocation = $this->cObj->enableFields('tx_jwcalendar_location');

	    /* read the template file */
		$this->templateCode = $this->cObj->fileResource('uploads/'.$this->extPrefix().'/'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'template_file'));
    	if(empty($this->templateCode)){
	    	$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
	    	if (empty($this->templateCode)) {
	      		return "<h3>jw_calendar: no template file found:</h3>".$this->conf['templateFile'];
	    	}
		}
	    $this->jwOptions['categories']['show'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'headerCategories_show');

    	$this->jwOptions['fe_entry']['enable']   = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Entry','s_FE_Entries');
    	$this->jwOptions['fe_entry']['fe_button'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Button','s_FE_Entries');
    	$this->jwOptions['fe_entry']['fe_pencil'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'FE_Pencil','s_FE_Entries');

	   $this->jwOptions['tooltip']['show'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toolTipShow','s_toolTip');
	   $this->jwOptions['tooltip']['begin'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'timeFormatBegin','s_toolTip');
       //if(empty($this->jwOptions['tooltip']['begin']))$this->jwOptions['tooltip']['begin'] = 'From %H:%M';

	   $this->jwOptions['tooltip']['end'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'timeFormatEnd','s_toolTip');
	   //if(empty($this->jwOptions['tooltip']['end']))$this->jwOptions['tooltip']['end']='to %H:%M';

	   $this->jwOptions['tooltip']['begin_mdays'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'timeFormatBeginMdays','s_toolTip');
       if(empty($this->jwOptions['tooltip']['begin_mdays']))$this->jwOptions['tooltip']['begin_mdays'] = 'From %d.%b: %H:%M';

       $this->jwOptions['tooltip']['end_mdays'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'timeFormatEndMdays','s_toolTip');
       if(empty($this->jwOptions['tooltip']['end_mdays']))$this->jwOptions['tooltip']['end_mdays']='to %d.%b: %H:%M';

       $this->jwOptions['tooltip']['title'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toolTipTitle','s_toolTip');
       $this->jwOptions['tooltip']['teaser'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toolTipTeaser','s_toolTip');
       $this->jwOptions['tooltip']['description'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toolTipDescription','s_toolTip');
       $this->initExtended();
	}

	function initExtended(){
	}	

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1.php']);
}

?>