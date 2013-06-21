<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Chetan Thapliyal (chetan@srijan.in)
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
 * Addition of an item to the clickmenu
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */

class tx_bahagphotogallery_cm1 {

	function main(&$backRef,$menuItems,$table,$uid)	{
		global $BE_USER,$TCA,$LANG;
	
		$localItems = Array();

		if (!$backRef->cmLevel && @getImageSize( $table))	{
			
				// Adds the regular item:
			$LL = $this->includeLL();
			
				// Repeat this (below) for as many items you want to add!
				// Remember to add entries in the localconf.php file for additional titles.
			$url = t3lib_extMgm::extRelPath("bahag_photogallery")."cm1/index.php?id=".$uid.'&img='.urlencode( $table);
			$localItems[] = $backRef->linkItem(
				$GLOBALS["LANG"]->getLLL("cm1_title",$LL),
				$backRef->excludeIcon('<img src="'.t3lib_extMgm::extRelPath("bahag_photogallery").'cm1/cm_icon.gif" width="15" height="12" border=0 align=top>'),
				$backRef->urlRefForCM($url),
				1	// Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
			);
			
			
			
			// Simply merges the two arrays together and returns ...
			$menuItems=array_merge($menuItems,$localItems);

		}
		return $menuItems;
	}
	
	/**
	 * Includes the [extDir]/locallang.php and returns the $LOCAL_LANG array found in that file.
	 */
	function includeLL()	{
		include(t3lib_extMgm::extPath('bahag_photogallery').'locallang.php');
		return $LOCAL_LANG;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bahag_photogallery/class.tx_bahagphotogallery_cm1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bahag_photogallery/class.tx_bahagphotogallery_cm1.php']);
}

?>
