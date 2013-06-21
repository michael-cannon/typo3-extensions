<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Skårhøj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'User listing' for the 'newloginbox' extension.
 *
 * $Id: class.tx_newloginbox_pi3.php,v 1.1.1.1 2010/04/15 10:03:54 peimic.comprock Exp $
 * XHTML compliant!
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   74: class tx_newloginbox_pi3 extends tslib_pibase 
 *   92:	 function main($content,$conf)	
 *  125:	 function listView($content,$conf)	
 *  191:	 function singleView($content,$conf)	
 *  390:	 function pi_list_row($c)	
 *  435:	 function pi_list_header()	
 *  476:	 function getFieldContent($fN)	
 *  497:	 function getFieldHeader($fN)	
 *  511:	 function getFieldHeader_sortLink($fN)	
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');














/**
 * Plugin 'User listing' for the 'newloginbox' extension.
 * 
 * @author	Kasper Skårhøj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_newloginbox
 */
class tx_newloginbox_pi3 extends tslib_pibase {
	var $prefixId = 'tx_newloginbox_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_newloginbox_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'newloginbox';	// The extension key.
	var $viewMode = 'listView';	// either listView or singleView
	var $conf = array();	// TypoScript configuration for the plugin
	
	
		// Internal vars:
	var $manualFieldOrder = FALSE;
	var $manualFieldOrder_details = array();
	var $manualFieldOrder_list = array();
	
	/**
	 * Main function, called from TypoScript
	 * 
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * @return	string		HTML for the plugin
	 */
	function main($content,$conf)	{
		$this->conf = $conf;
			// Init FlexForm configuration for plugin:
		$this->pi_initPIflexForm();
		$this->manualFieldOrder = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'field_manualOrder') ? TRUE : FALSE;
		$this->manualFieldOrder_details = t3lib_div::trimExplode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'field_orderDetails'),1);
		$this->manualFieldOrder_list = t3lib_div::trimExplode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'field_orderList'),1);

		switch((string)$conf['CMD'])	{
			case 'singleView':
				list($t) = explode(':',$this->cObj->currentRecord);
				$this->internal['currentTable']=$t;
				$this->internal['currentRow']=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}
	
	/**
	 * List view, listing the records from the table. 
	 * Does also provide the single view if the "showUid" piVar is set.
	 * 
	 * @param	string		HTML input content - not used, ignore.
	 * @param	array		TypoScript configuration array
	 * @return	string		HTML content for the listing.
	 */
	function listView($content,$conf)	{
	
			// Init:
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->lConf = $lConf = $this->conf['listView.'];	// Local settings for the listView function
		$this->pi_alwaysPrev = $this->lConf['alwaysPrev'];
		$this->viewMode = 'listView';
		
			// Select either single view or list view:
		if ($this->piVars['showUid'])	{	// If a single element should be displayed:
			$this->internal['currentTable'] = 'fe_users';
			$this->internal['currentRow'] = $this->pi_getRecord('fe_users',$this->piVars['showUid']);
			
			$content = $this->singleView($content,$conf);
			return $content;
		} else {
		
			if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
	
				// Initializing the query parameters:
			list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
			$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,3);		// Number of results to show in a listing.
			$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", 'Page 2', etc.
			$this->internal['searchFieldList']='username,name,email,country,city,zip,telephone,address,title';
			$this->internal['orderByList']='username,name,email,country,city,zip';
			
				// Get number of records:
			$query = $this->pi_list_query('fe_users',1);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			list($this->internal['res_count']) = mysql_fetch_row($res);
	
				// Make listing query, pass query to MySQL:
			$query = $this->pi_list_query('fe_users');
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			$this->internal['currentTable'] = 'fe_users';
	
				// Put the whole list together:
			$fullTable='';	// Clear var;
			
				// Adds the search box:
			$fullTable.=$this->pi_list_searchBox();
				
				// Adds the result browser:
			$fullTable.=$this->pi_list_browseresults();
			
				// Adds the whole list table
			$fullTable.=$this->pi_list_makelist($res,$this->conf['listView.']['tableParams_list']);
			
				// Returns the content from the plugin.
			return $fullTable;
		}
	}

	/**
	 * Single view display of users.
	 * 
	 * When this function is called, $this->internal['currentTable'] is set to the table name, "fe_users" and $this->internal['currentRow'] will contain the record.
	 * 
	 * @param	string		Content input. Not used. Ignore.
	 * @param	array		TypoScript configuration
	 * @return	string		HTML content for display of user details.
	 */
	function singleView($content,$conf)	{

			// Init
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->viewMode = 'singleView';
	
			// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title'])	$GLOBALS['TSFE']->indexedDocTitle=$this->internal['currentRow']['title'];
		
		
		
		
		if ($this->manualFieldOrder)	{
			$rows='';
			foreach ($this->manualFieldOrder_details as $fieldName)	{
				switch((string)$fieldName)	{
					case '---':
						$rows.='
							<tr>
								<td colspan="2"><img src="clear.gif" width="1" height="2" alt="" /></td>
							</tr>
						';
					break;
					default:
						$rows.='
							<tr>
								<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader($fieldName).'</p></td>
								<td valign="top"><p>'.$this->getFieldContent($fieldName).'</p></td>
							</tr>
						';
					break;
				}
			}
			
			$theTable = '
				<table '.$this->conf['tableParams_details'].'>'.$rows.'
				</table>';
			$content='
			
				<!--
					Single view content:
				-->
				<div'.$this->pi_classParam('singleView').'>
				<h3>'.$this->pi_getLL('singleview_detailsAbout','',TRUE).' '.htmlspecialchars($this->internal['currentRow']['name']).' ('.htmlspecialchars($this->internal['currentRow']['username']).'):</h3>
				'.$theTable;		
		} else {
			if ($GLOBALS['TSFE']->loginUser)	{
					$image='';
					if ($this->internal['currentRow']['tx_extrepmgm_images'])	{
						$imgArr = t3lib_div::trimExplode(',',$this->internal['currentRow']['tx_extrepmgm_images'],1);
						$GLOBALS['TSFE']->make_seed();
						$randval = intval(rand(0,count($imgArr)-1));
						$imgFile = 'uploads/tx_extrepmgm/'.$imgArr[$randval];
						$imgInfo = getimagesize(PATH_site.$imgFile);
						if (is_array($imgInfo))	{
							$image='<img src="'.$imgFile.'" '.$imgInfo[3].' alt="" />';
						}
					}
		
					$theTable = '
					<table '.$this->conf['tableParams_details'].'>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('username').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('username').'</p></td>
							<td valign="top" rowspan="20">'.$image.'</td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('name').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('name').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('title').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('title').'</p></td>
						</tr>'.($this->conf['listView.']['show.']['email_in_details_when_logged_in']?'
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('email').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('email').'</p></td>
						</tr>':'').'
						
						<tr>
							<td colspan="2"><img src="clear.gif" width="1" height="2" alt="" /></td>
						</tr>
		
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('address').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('address').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('city').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('zip').'-'.$this->getFieldContent('city').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('country').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('country').'</p></td>
						</tr>
						
						<tr>
							<td colspan="2"><img src="clear.gif" width="1" height="2" alt="" /></td>
						</tr>
		
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('telephone').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('telephone').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('fax').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('fax').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('company').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('company').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('homepage').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('www').'</p></td>
						</tr>
						
						<tr>
							<td colspan="2"><img src="clear.gif" width="1" height="2" alt="" /></td>
						</tr>
		
						<tr>
							<td valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('contribute').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('tx_extrepmgm_contribute').'</p></td>
						</tr>
						<tr>
							<td valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('personallife').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('tx_extrepmgm_personallife').'</p></td>
						</tr>
						<tr>
							<td valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('typo3experiences').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('tx_extrepmgm_typo3experiences').'</p></td>
						</tr>
						
						<tr>
							<td colspan="2"><img src="clear.gif" width="1" height="2" alt="" /></td>
						</tr>
		
						<tr>
							<td nowrap="nowrap"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->pi_getLL('singleview_created','',TRUE).'</p></td>
							<td valign="top"><p>'.date('d-m-Y H:i',$this->internal['currentRow']['crdate']).'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->pi_getLL('singleview_lastLogin','',TRUE).'</p></td>
							<td valign="top"><p>'.date('d-m-Y H:i',$this->internal['currentRow']['lastlogin']).'</p></td>
						</tr>
					</table>
				';
	
				$content='
				
					<!--
						Single view content:
					-->
					<div'.$this->pi_classParam('singleView').'>
					<h3>'.$this->pi_getLL('singleview_detailsAbout','',TRUE).' '.htmlspecialchars($this->internal['currentRow']['name']).' ('.htmlspecialchars($this->internal['currentRow']['username']).'):</h3>
					'.$theTable;			
			} else {
				$content='<div'.$this->pi_classParam('singleView').'>
					<h3>'.$this->pi_getLL('singleview_limitedDetailsAbout','',TRUE).' '.htmlspecialchars($this->internal['currentRow']['name']).' ('.htmlspecialchars($this->internal['currentRow']['username']).'):</h3>
					<table '.$this->conf['tableParams_details'].'>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('username').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('username').'</p></td>
						</tr>
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('name').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('name').'</p></td>
						</tr>
						'.($this->conf['listView.']['show.']['email_in_details']?'
						<tr>
							<td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('email').'</p></td>
							<td valign="top"><p>'.$this->getFieldContent('email').'</p></td>
						</tr>':'').'
					</table>
				';
			}
		}
		
		
		if ($this->piVars['returnUrl'])	{
			$content.='<p><a href="'.htmlspecialchars($this->piVars['returnUrl']).'">'.$this->pi_getLL('singleview_back','',TRUE).'</a></p>';
		} else {
			$content.='<p>'.$this->pi_list_linkSingle($this->pi_getLL('singleview_back','',TRUE),0).'</p>';
		}
		
		$content.='</div>'.
		$this->pi_getEditPanel();
	
		return $content;
	}

	/**
	 * A single list row displayed:
	 * 
	 * @param	integer		Counter for rows displayed.
	 * @return	string		HTML content; a Table row, <tr>...</tr>
	 */
	function pi_list_row($c)	{
		$editPanel = $this->pi_getEditPanel();
		if ($editPanel)	$editPanel='<td>'.$editPanel.'</td>';
		
		if ($this->manualFieldOrder)	{
			$cells = array();
			foreach($this->manualFieldOrder_list as $fieldName)	{
				if ($fieldName != 'email' || $this->lConf['show.']['email'])	{
					$cells[]='
						<td valign="top"><p>'.$this->getFieldContent($fieldName).'</p></td>';
				}
			}

			return '
				<tr'.($c%2 ? $this->pi_classParam('listrow-odd') : "").'>
					'.implode('',$cells).'
					'.$editPanel.'
				</tr>';
		} else {
			if ($GLOBALS['TSFE']->loginUser)	{
				return '
					<tr'.($c%2 ? $this->pi_classParam('listrow-odd') : "").'>
						<td valign="top"><p>'.$this->getFieldContent('name').'</p></td>
						<td valign="top"><p>'.$this->getFieldContent('city').'</p></td>
						<td valign="top"><p>'.$this->getFieldContent('country').'</p></td>
						'.($this->lConf['show.']['email'] ? '<td valign="top"><p>'.$this->getFieldContent('email').'</p></td>' :'').'
						<td valign="top"><p>'.$this->getFieldContent('username').'</p></td>
						'.$editPanel.'
					</tr>';
			} else {
				return '
					<tr'.($c%2 ? $this->pi_classParam('listrow-odd') : "").'>
						<td valign="top"><p>'.$this->getFieldContent('name').'</p></td>
						<td valign="top"><p>'.$this->getFieldContent('username').'</p></td>
						'.$editPanel.'
					</tr>';
			}
		}
	}

	/**
	 * List header row, showing column names:
	 * 
	 * @return	string		HTML content; a Table row, <tr>...</tr>
	 */
	function pi_list_header()	{
		if ($this->manualFieldOrder)	{
			$cells = array();
			foreach($this->manualFieldOrder_list as $fieldName)	{
				if ($fieldName != 'email' || $this->lConf['show.']['email'])	{
					$cells[]='
						<td><p>'.$this->getFieldHeader_sortLink($fieldName).'</p></td>';
				}
			}

			return '
				<tr'.$this->pi_classParam('listrow-header').'>
					'.implode('',$cells).'
				</tr>';
			
		} else {
			if ($GLOBALS['TSFE']->loginUser)	{
				return '
					<tr'.$this->pi_classParam('listrow-header').'>
						<td><p>'.$this->getFieldHeader_sortLink('name').'</p></td>
						<td><p>'.$this->getFieldHeader_sortLink('city').'</p></td>
						<td><p>'.$this->getFieldHeader_sortLink('country').'</p></td>
						'.($this->lConf['show.']['email'] ? '<td><p>'.$this->getFieldHeader_sortLink('email').'</p></td>' :'').'
						<td><p>'.$this->getFieldHeader_sortLink('username').'</p></td>
					</tr>';
			} else {
				return '
					<tr'.$this->pi_classParam('listrow-header').'>
						<td><p>'.$this->getFieldHeader_sortLink('name').'</p></td>
						<td><p>'.$this->getFieldHeader_sortLink('username').'</p></td>
					</tr>';
			}
		}
	}

	/**
	 * Field content, processed, prepared for HTML output.
	 * 
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getFieldContent($fN)	{

			// feature added by Ingmar Schlecht <ingmar@typo3.org>
		if($this->conf[$this->viewMode.'.']['customProcessing.'][$fN]) {
				// keep old data array for later restorage
			$temp = $this->cObj->data;

				// load currentRow as cObj->data
			$this->cObj->data = $this->internal['currentRow'];
				// "execute" the TypoScript, i.e. do the custom processing for the field
			$content = $this->cObj->cObjGetSingle($this->conf[$this->viewMode.'.']['customProcessing.'][$fN],$this->conf[$this->viewMode.'.']['customProcessing.'][$fN.'.']);

				// restore old data array
			$this->cObj->data = $temp;

			return $content;
		}

		switch($fN) {
			case 'name':
			case 'username':
				return $this->pi_list_linkSingle(htmlspecialchars($this->internal['currentRow'][$fN]),$this->internal['currentRow']['uid'],1);
			break;
			case 'www':
			case 'email':
				return $this->cObj->gettypolink(htmlspecialchars($this->internal['currentRow'][$fN]),$this->internal['currentRow'][$fN]);
			default:
				return htmlspecialchars($this->internal['currentRow'][$fN]);
			break;
		}
	}

	/**
	 * Field header name; Getting the label for field headers.
	 * 
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getFieldHeader($fN)	{
		switch($fN) {
			default:
				return $this->pi_getLL('listFieldHeader_'.$fN,'['.$fN.']',1);
			break;
		}
	}
	
	/**
	 * Field header name, but wrapped in a link for sorting by column.
	 * 
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getFieldHeader_sortLink($fN)	{
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newloginbox/pi3/class.tx_newloginbox_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newloginbox/pi3/class.tx_newloginbox_pi3.php']);
}
?>