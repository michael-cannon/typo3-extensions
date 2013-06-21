<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Tony McCallie (TonyMcCallie@tfchurch.org)
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
 * Plugin 'TFC Lifegroup Tree' for the 'tfc_lifegroups' extension.
 *
 * @author	Tony McCallie <TonyMcCallie@tfchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_tfclifegroups_pi2 extends tslib_pibase {
	var $prefixId = 'tx_tfclifegroups_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_tfclifegroups_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'tfc_lifegroups';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		if ($this->piVars["showUid"]){
			$startPid = $this->piVars["showUid"];
			$startTitle = $this->piVars["showTitle"];
		} else {
			$startPid = $this->cObj->data["pages"];
			$startTitle = 'Small Groups';
		}

		$this->pi_initPIflexForm();
		$this->lConf = array();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			foreach ( $data as $lang => $value ) {
				foreach ( $value as $key => $val ) {
					$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}

		#die($this->lConf['semester']);

		$this->getLevel($startPid, $startTitle);
		$this->html.= $this->intTotal.' total groups.<br />'.$this->pi_linkToPage("Full Tree",$GLOBALS["TSFE"]->id,'',array());
		$content=$this->html;
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	*
	*/
	function getLevel($parent,$title){
		$this->html.= '<ul>
					<li>';
		#'<a href="index.php?id='.$GLOBALS["TSFE"]->id.'&'.$this->prefixId.'[showUid]='.$parent.'&'.$this->prefixId.'[showTitle]='.$title.'">'.$title;
		
		$sqlGroups = "SELECT CONCAT(leader1_lastname,', ',leader1_firstname) AS first_leader, 
						CONCAT(leader2_lastname,', ',leader2_firstname) AS second_leader,
						leader1_phone
						FROM tx_tfclifegroups_lifegroups
						WHERE 1 AND `pid` =".$parent.$this->cObj->enableFields("tx_tfclifegroups_lifegroups")."
						AND `semesters` LIKE '%".$this->lConf['semester']."%'
						ORDER BY leader1_lastname";
						
		
		#die($sqlGroups);
		$rsGroups = mysql(TYPO3_db,$sqlGroups);
		$intRows = mysql_num_rows($rsGroups);
		$this->intTotal+=$intRows;
		$strTitle = $title;
		if($intRows > 0){
			$strTitle.="&nbsp($intRows)";
		}
		
		$this->html.=$this->pi_list_linkSingle($strTitle,$parent,FALSE,array('showTitle'=>$title)).'<br />';
		
		#echo $intRows.'<br>';
		while($rowGroups=mysql_fetch_assoc($rsGroups)){
			$this->html.=''.$rowGroups['first_leader'];
			if($rowGroups['second_leader']!=", ") {
				$this->html.=' / '.$rowGroups['second_leader'];
			}
			$this->html.='&nbsp;&nbsp;&nbsp;&nbsp;'.$rowGroups['leader1_phone'].'<br />';
		}
		
		$sqlFolders = 	'SELECT uid, title FROM pages WHERE pid = '.$parent.$this->cObj->enableFields('pages').' ORDER BY sorting';
		#debug(array($title));
		$rsPages = mysql(TYPO3_db,$sqlFolders);
		while($rowPages=mysql_fetch_assoc($rsPages)){
			$this->getLevel($rowPages['uid'], $rowPages['title']);
			#$this->html.='<ul><li>'.$rowPages['uid'].'</li></ul>';
		}
		$this->html.='</li>';
		
		#$sqlGroups = 	'SELECT uid, title FROM pages WHERE pid = '.$parent.$this->cObj->enableFields('pages').' ORDER BY sorting';
		
		#$this->html.='</li>';
		
		$this->html.='</ul>';
		#return $this->html;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tfc_lifegroups/pi2/class.tx_tfclifegroups_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tfc_lifegroups/pi2/class.tx_tfclifegroups_pi2.php']);
}

?>