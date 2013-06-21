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
 */
require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_library.php');
 
class tx_jwcalendar_pi1_singleEventView extends tx_jwcalendar_pi1_library {
	var $scriptRelPath = 'pi1/class.tx_jwcalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $piDirRelPath = 'pi1/';

  /* Constructor */

	function tx_jwcalendar_pi1_singleEventView($cObj,$conf,$jwOptions){
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
  	    $this->conf = $conf;
	    $this->cObj = $cObj;
        $this->jwOptions = $jwOptions;
        $this->jwOptions['organizerTable']  = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'organizerTable','s_Single_View');
	}

  /* Workflows */

  	function getForm(){
  		$this->jwOptions['maxW']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'image_maxWidth','s_Single_View');
    	$event=$this->querySingleEvent();
		$event['submit']= $this->submitBack($this->pi_getLL('backButtonLabel'));
  		$out = $this->buildTemplateArray($event);
  		return $this->wrapForm($out);
  	}

  	function getOrganizerView(){
  		$this->jwOptions['maxW']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'image_maxWidth','s_Single_View');
    	$org = $this->queryOrganizer($this->jwVars['orguid'],$this->jwOptions['organizerTable']);
		$org['submit']= $this->submitBack($this->pi_getLL('backButtonLabel'));
  		if($this->jwOptions['organizerTable'] == 'fe_users')
  			$out = $this->buildOrganizerView_fe_users($org);
  		else
  			$out = $this->buildOrganizerView($org);
  		return $this->wrapForm($out);
  	}

  	function getLocationView(){
  		$this->jwOptions['maxW']=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'image_maxWidth','s_Single_View');
    	$loc = $this->queryLocation($this->jwVars['locuid']);
		$loc['submit']= $this->submitBack($this->pi_getLL('backButtonLabel'));
  		$out = $this->buildLocationView($loc);
  		return $this->wrapForm($out);
  	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
  	function querySingleEvent(){
    /* Query eventes */
    	//simple but not secure
    	//return $this->pi_getRecord('tx_jwcalendar_events',$this->jwVars['eventid']);
		$query  = 'SELECT *
				 , tx_jwcalendar_categories.title as category
				 , tx_jwcalendar_events.title as title
	     		 , tx_jwcalendar_categories.color as catcolor
    			   FROM tx_jwcalendar_events 
    			   INNER JOIN tx_jwcalendar_categories ON tx_jwcalendar_events.category = tx_jwcalendar_categories.uid
   				';
    	$query .= " WHERE tx_jwcalendar_events.uid = '".$this->jwVars['eventid']."'";
    	$query .=  $this->enableFieldsCategories;
        $query .=  $this->enableFieldsEvents;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}


  /* Workers */

	function buildLocationView($row){
	  	$LocationViewT = $this->cObj->getSubpart($this->templateCode,'###LOCATION###');
    	$sims = array(); // Simple markers
    	$sims['###title###'] = $this->pi_getLL('LocationTitle');
    	$rems['###CUT_image###'] =	$row['image']? $this->getImage($this->cObj->getSubpart($LocationViewT,'###CUT_image###'),$row['image']): "";
	    $sims['###location###'] = $row['location'];
		$row['description']		= nl2br( nl2br( $row['description'] ) );	
	    $sims['###description###'] = $this->cObj->parseFunc($row['description'],  $this->conf['parseFunc.']);
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
    	$sims['###address_label###']=$this->pi_getLL('addressLabel');
    	$sims['###name###']= $row['name'];
    	$sims['###zip###']= $row['zip'] ? $row['zip'] : "&nbsp;";
    	$sims['###street###']= $row['street'] ? $row['street'] : "&nbsp;";
    	$sims['###city###']= $row['city'] ? $row['city'] : "&nbsp;";
	    $sims['###phone_label###'] = $this->pi_getLL('phoneLabel');
    	$sims['###phone###']= $row['phone'] ? $row['phone'] : "&nbsp;";
	    $sims['###email_label###']=$row['email']?$this->pi_getLL('emailLabel'):''; 
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
		$rems['###CUT_email###'] = $row['email']?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($LocationViewT,'###CUT_email###'),$sims) : "";		
		$sims['###link_label###']= $row['link'] ? $this->pi_getLL('homepageLabel'):''; 
		$sims['###link###'] = $row['link'] ? $this->makeLink($row['link'],$row['link']):'';
		$rems['###CUT_link###'] = $row['link'] ? $this->cObj->substituteMarkerArray($this->cObj->getSubpart($LocationViewT,'###CUT_link###'),$sims) : "";
    	$sims['###back###'] = $row['submit'];
    	return $this->cObj->substituteMarkerArrayCached($LocationViewT,$sims,$rems);
	}

	function buildOrganizerView($row){
	  	$OrganizerViewT = $this->cObj->getSubpart($this->templateCode,'###ORGANIZER###');
    	$sims = array(); // Simple markers
    	$sims['###title###'] = $this->pi_getLL('OrganizerTitle');
    	$rems['###CUT_image###'] =	$row['image']? $this->getImage($this->cObj->getSubpart($OrganizerViewT,'###CUT_image###'),$row['image']): "";
	    $sims['###name###'] = $row['name'];
		$row['description']		= nl2br( nl2br( $row['description'] ) );	
	    $sims['###description###'] = $this->cObj->parseFunc($row['description'],  $this->conf['parseFunc.']);
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
    	$sims['###address_label###']=$this->pi_getLL('addressLabel');
    	$sims['###addr_name###']= $row['name'];
    	$sims['###zip###']= $row['zip'] ? $row['zip'] : "&nbsp;";
    	$sims['###street###']= $row['street'] ? $row['street'] : "&nbsp;";
    	$sims['###city###']= $row['city'] ? $row['city'] : "&nbsp;";
	    $sims['###phone_label###'] = $this->pi_getLL('phoneLabel');
    	$sims['###phone###']= $row['phone'] ? $row['phone'] : "&nbsp;";
	    $sims['###email_label###']=$row['email']?$this->pi_getLL('emailLabel'):''; 
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
		$rems['###CUT_email###'] = $row['email']?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($OrganizerViewT,'###CUT_email###'),$sims) : "";		
		$sims['###link_label###']= $row['link'] ? $this->pi_getLL('homepageLabel'):''; 
		$sims['###link###'] = $row['link'] ? $this->makeLink($row['link'],$row['link']):'';
		$rems['###CUT_link###'] = $row['link'] ? $this->cObj->substituteMarkerArray($this->cObj->getSubpart($OrganizerViewT,'###CUT_link###'),$sims) : "";
    	$sims['###back###'] = $row['submit'];
    	return $this->cObj->substituteMarkerArrayCached($OrganizerViewT,$sims,$rems);
	}
	
	function getfeImage($SingleImageT,$image){
		$img['file.']['maxW'] = $this->jwOptions['maxW'] ? $this->jwOptions['maxW'] : 100;
   		$img['file'] = 'uploads/tx_srfeuserregister/'.$image;
		return $this->cObj->substituteMarker($SingleImageT,'###image###',$this->cObj->IMAGE($img));
	}

	function buildOrganizerView_fe_users($row){
	  	$OrganizerViewT = $this->cObj->getSubpart($this->templateCode,'###ORGANIZER###');
    	$sims = array(); // Simple markers
    	$sims['###title###'] = $this->pi_getLL('OrganizerTitle');
    	$rems['###CUT_image###'] =	$row['image']? $this->getfeImage($this->cObj->getSubpart($OrganizerViewT,'###CUT_image###'),$row['image']): "";
	    $sims['###name###'] = $row['name'];
		$row['description']		= nl2br( nl2br( $row['description'] ) );	
	    $sims['###description###'] = $this->cObj->parseFunc($row['description'],  $this->conf['parseFunc.']);
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
    	$sims['###address_label###']=$this->pi_getLL('addressLabel');
    	$sims['###addr_name###']= $row['name'];
    	$sims['###zip###']= $row['zip'] ? $row['zip'] : "&nbsp;";
    	$sims['###street###']= $row['address'] ? $row['address'] : "&nbsp;";
    	$sims['###city###']= $row['city'] ? $row['city'] : "&nbsp;";
	    $sims['###phone_label###'] = $this->pi_getLL('phoneLabel');
    	$sims['###phone###']= $row['telephone'] ? $row['telephone'] : "&nbsp;";
	    $sims['###email_label###']=$row['email']?$this->pi_getLL('emailLabel'):''; 
	    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
		$rems['###CUT_email###'] = $row['email']?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($OrganizerViewT,'###CUT_email###'),$sims) : "";		
		$sims['###link_label###']= $row['www'] ? $this->pi_getLL('homepageLabel'):''; 
		$sims['###link###'] = $row['www'] ? $this->makeLink($row['www'],$row['www']):'';
		$rems['###CUT_link###'] = $row['www'] ? $this->cObj->substituteMarkerArray($this->cObj->getSubpart($OrganizerViewT,'###CUT_link###'),$sims) : "";
    	$sims['###back###'] = $row['submit'];
    	return $this->cObj->substituteMarkerArrayCached($OrganizerViewT,$sims,$rems);
	}
	

	function buildTemplateArray($row){
	  	$SingleViewT = $this->cObj->getSubpart($this->templateCode,'###singleEvent###');
    	$sims = array(); // Simple markers
    	$sims['###viewTitle###'] = $this->pi_getLL('singleEventTitle');
    	$sims['###category###'] = $row['category'];
    	$rems['###CUT_image###'] =	$row['image']? $this->getImage($this->cObj->getSubpart($SingleViewT,'###CUT_image###'),$row['image']): "";
	    $sims['###title###'] = $row['title'];
		$rems['###CUT_teaser###'] = $row['teaser']?$this->cObj->substituteMarker($this->cObj->getSubpart($SingleViewT,'###CUT_teaser###'),'###teaser###',$row['teaser']):"";
		$row['description']		= nl2br( nl2br( $row['description'] ) );	
	    $sims['###description###'] = $this->cObj->parseFunc($row['description'],  $this->conf['parseFunc.']);
		$rems['###CUT_link###'] = $row['link'] ? $this->cObj->substituteMarker($this->cObj->getSubpart($SingleViewT,'###CUT_link###'),'###link###',$this->makeLink($this->pi_getLL('readMoreLinkLabel'), $row['link'])) : "";
    	if($row['organizer_id']){
			$sims['###organiser###']= $this->getOrganizerLink($row['organizer_id'],'tx_jwcalendar_organizer'); 
			$sims['###email###'] = '';
    	}else if($row['organiser']){	
    		$sims['###organiser###']= $row['organiser'] ? $row['organiser'] : "&nbsp;";
		    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
    	}else{	
    		$sims['###organiser###']= $this->getOrganizerLink($row['organizer_feuser'],'fe_users'); 
		    $sims['###email###'] = $row['email']? $this->makeLink($row['email'],$row['email']):'';
		}    
    	if($row['location_id']){
			$sims['###location###']= $this->getLocationLink($row['location_id']); 
    	}else	
	    	$sims['###location###'] = $row['location'] ? $row['location'] : "&nbsp;";
    	$sims['###locationLabel###'] = $this->pi_getLL('locationLabel');
    	$sims['###organiserLabel###'] = $this->pi_getLL('organiserLabel');
    	$sims['###beginLabel###'] = $this->pi_getLL('beginLabel');
    	$sims['###color###'] =	$row['catcolor']?$this->cObj->wrap($row['catcolor'],$this->conf['wrapColorSingleView']):"";
    	if($this->jwVars['day']){
    		$day = strtotime(strftime('%Y-%m-%d',$this->jwVars['day']));
    		$day_begin = strtotime(strftime('%Y-%m-%d',$row['begin'])); 
    		$day_end  =  strtotime(strftime('%Y-%m-%d',$row['end']));
    		$daydiff = $day_end - $day_begin; 
    		$row['begin'] += $day - $day_begin;
    		$row['end'] = $row['end']? $row['end'] + $day + $daydiff - $day_end: 0 ;
    	}
    	$sims['###begin###'] = $this->dateOutput($row['begin']);
   		$rems['###CUT_end###'] = $row['end'] ? $this->cObj->substituteMarkerArray($this->cObj->getSubpart($SingleViewT,'###CUT_end###'),array('###endLabel###' => $this->pi_getLL('endLabel'),'###end###' => $this->dateOutput($row['end'], true))):"";
		// ---------------------------------------------------
    	$sims['###submit###'] = $row['submit'];
    	return $this->cObj->substituteMarkerArrayCached($SingleViewT,$sims,$rems);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_singleEventView.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_singleEventView.php']);
}
?>
