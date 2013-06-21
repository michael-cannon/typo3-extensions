<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Titarenko Dmitri <td@krendls.eu>
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
 * Plugin 'Shopping SelectBox' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi5 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi5';		// Same as class name
	var $scriptRelPath = 'pi5/class.tx_shoppingsystem_pi5.php';	// Path to this script relative to the extension dir.
	var $extKey = 'shopping_system';	// The extension key.
	var $pi_checkCHash = TRUE;

	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		session_start();

		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		$display_cats_mode = $piFlexForm['data']['sDEF']['lDEF']['categorySelectionMode']['vDEF'];

		$parent_cat = $piFlexForm['data']['sDEF']['lDEF']['categorySelectionParent']['vDEF'];
		
		//$category_get = isset($_REQUEST['category']) ? (int)$_REQUEST['category'] : '';
		$category_get = isset($_SESSION['savysort']['category']) ? (int)$_SESSION['savysort']['category'] : '';

		$singleWhere = 'uid = "'.$parent_cat.'"';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'shortcut',
			'tt_news_cat',
			$singleWhere);

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$shortcut = $row['shortcut'];
		
		$shortcut = $piFlexForm['data']['sDEF']['lDEF']['categorySearchPage']['vDEF']; 

		if($display_cats_mode == 'manually')
		{
			$arr_cats_manual = explode(',', $piFlexForm['data']['sDEF']['lDEF']['categorySelectionAll']['vDEF']);

			$num_rows = 0;
			$singleWhere = 'uid IN(';

			foreach($arr_cats_manual as $cat_id)
			{
				$singleWhere .= $cat_id;
				if($num_rows < count($arr_cats_manual)-1)
					$singleWhere .= ', ';

				$num_rows++;
			}

			$singleWhere .= ') AND deleted<>"1" AND hidden<>"1" ORDER BY title';
		}

		if($display_cats_mode == 'parent')
		{
			$singleWhere = 'tt_news_cat.parent_category ='.$parent_cat . ' AND deleted<>"1" AND hidden<>"1" ORDER BY title';
		}

		$templateFile = t3lib_div::getURL(t3lib_extMgm::extPath('shopping_system').'res/template.html');
		$templates['formRegTemplate'] = $this->cObj->getSubpart($templateFile, '###TEMPLATE_SELECT_BOX###');

        
        /// options
        
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
		'*',
		'tt_news_cat',
		$singleWhere);

		$templates['formOptionTemplate'] = $this->cObj->getSubpart($templateFile, '###OPTIONS###');
        
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
		{
			$category = $row['title'];
			$cat_id = $row['uid'];

			$templateMarkers['###CAT_URL###'] = $cat_id;
			$templateMarkers['###CAT_TITLE###'] = $category;
	
			if($category_get == $row['uid'])
				$templateMarkers['###SELECTED###'] = "selected='selected'";
			
			else
				$templateMarkers['###SELECTED###'] = "";

			$insidecontent .= $this->cObj->substituteMarkerArrayCached($templates['formOptionTemplate'], $templateMarkers);
            
            
            
		}
		$content = $this->cObj->substituteSubpart($templates['formRegTemplate'], '###OPTIONS###', $insidecontent);

       
        /// options browse[begin]
        
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        '*',
        'tt_news_cat',
        $singleWhere);

        $templates['formOptionBrowseTemplate'] = $this->cObj->getSubpart($templateFile, '###OPTIONS_BROWSE###');
        
        $insidecontent = '';
        //echo htmlentities($templates['formOptionBrowseTemplate']);
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
        {
            $category = $row['title'];
            $cat_id = $row['uid'];

            $templateMarkers = array();
            $templateMarkers['###CAT_URL_BROWSE###'] = $cat_id;
            $templateMarkers['###CAT_TITLE_BROWSE###'] = $category;
            
        
            $data = $this->cObj->substituteMarkerArrayCached($templates['formOptionBrowseTemplate'], $templateMarkers);
            $insidecontent .= $data ;
            
            
        }
        $content = $this->cObj->substituteSubpart($content, '###OPTIONS_BROWSE###', $insidecontent);

        /// options browse[end]
        
        
		$templateMarkers['###SEARCH_LINK###'] = $this->pi_getPageLink($shortcut);
		$content = $this->cObj->substituteMarkerArrayCached($content, $templateMarkers);

		$product = isset($_REQUEST['product']) ? $_REQUEST['product'] : '';
		if(isset($_POST['sortform']['pricerange']) && isset($_SESSION['savysort']['keyword']))
			$product = $_SESSION['savysort']['keyword'];
		
				
		if(!empty($product))
			$content .= "
			<script type='text/javascript'>
				document.getElementById('search_input').value = '".$product."';
			</script>";
		
		$content .= "
		<script type='text/javascript'>";
		
		$content .= "
			function check_search()
			{
				if(document.getElementById('search_input').value == 'Search')
					document.getElementById('search_input').value = '';
				return true;	
			}
		</script>";

		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi5/class.tx_shoppingsystem_pi5.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi5/class.tx_shoppingsystem_pi5.php']);
}

?>