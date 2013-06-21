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
 * Plugin 'Shopping menu' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi6 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi6';		// Same as class name
	var $scriptRelPath = 'pi6/class.tx_shoppingsystem_pi6.php';	// Path to this script relative to the extension dir.
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
//		var_dump($this->cObj->data['pages']);
//		var_dump($this->cObj->data['recursive']);
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
// >> td@krendls.eu
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		$parent_cat = $piFlexForm['data']['sDEF']['lDEF']['categorySelectionParent']['vDEF'];
		$markNew = explode(',', $piFlexForm['data']['sDEF']['lDEF']['categoryNew']['vDEF']);
		// selecting all child categories
		$sqlRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'shortcut',
			'tt_news_cat',
			'tt_news_cat.uid = '.$parent_cat);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlRes);
		$linkToMain = $this->pi_getPageLink($row['shortcut']);

		// selecting all child categories
		$sqlRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'`pages`, `tt_news_cat`',
			'tt_news_cat.parent_category ='.$parent_cat . ' AND `tt_news_cat`.deleted<>"1" AND `tt_news_cat`.hidden<>"1" AND
    		pages.uid = tt_news_cat.shortcut AND pages.deleted <> 1 AND
			pages.hidden <> 1 AND pages.nav_hide <> 1'
			, ''
			, 'pages.sorting');
		$content .= '<div style="background-color:black;text-align:left">';
		$content .= '<div style="text-align:center;padding:5px"><a title="Shopping" href="'.$linkToMain.'"><img alt="Shopping" border="0" src="/uploads/tx_shoppingsystem/shopping.gif" /></a></div><hr />';
		$content .= '<div style="padding:4px">';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlRes))
		{
			if($row['shortcut'] != 0)
			{
				$content .= '<div style="margin: 5px 4px 10px 4px;">';
				$content .= '<a href="'.$this->pi_getPageLink($row['shortcut']).'" title="'.$row['title'].'">';

				if(in_array($row['uid'], $markNew) !== false)
					$content .= '<img src="/typo3conf/ext/shopping_system/genImage.php?text=NEW&amp;typeT=6" border="0" alt="NEW" />';

				$content .= '<img alt="'.$row['title'].'" border="0" src="/typo3conf/ext/shopping_system/genImage.php?text='.rawurlencode($row['title']).'&amp;typeT=2" /><br />';
				$content .= '</a></div>';
			}
		}
		$content .= '</div></div>';
// << td@krendls.eu
		return $this->pi_wrapInBaseClass($content);
	}

	function extraCodesProcessor($news)
	{
		if($news->theCode == 'PRODUCTS_SEARCH')
        {
        }
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi6/class.tx_shoppingsystem_pi6.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi6/class.tx_shoppingsystem_pi6.php']);
}

?>
