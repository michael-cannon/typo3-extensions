<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Peimic.com
* All rights reserved
*
* You can redistribute this file and/or modify it under the terms of the 
* GNU General Public License as published by the Free Software Foundation; 
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

/**
 * Class that adds the wizard icon.
 * 
 * @author Michael Cannon <michael@peimic.com>
 * @package TYPO3
 * @subpackage tx_typo3button
 */
class tx_typo3button_pi1_wizicon {

	/**
	 * Adds the typo3_button wizard icon
	 * 
	 * @param	array		Input array with wizard items for plugins
	 * @return	array		Modified input array, having the item for wec_flashplayer added.
	 */
	function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_typo3button_pi1'] = array(
			'icon'=>t3lib_extMgm::extRelPath('typo3_button').'pi1/ce_wiz.gif',
			'title'=>$LANG->getLLL('pi1_title',$LL),
			'description'=>$LANG->getLLL('pi1_plus_wiz_description',$LL),
			'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=typo3_button_pi1'
		);

		return $wizardItems;
	}

	/**
	 * Includes the locallang file for the 'typo3_button' extension
	 * 
	 * @return	array		The LOCAL_LANG array
	 */
	function includeLocalLang()	{
		include(t3lib_extMgm::extPath('typo3_button').'locallang_db.php');
		return $LOCAL_LANG;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_button/pi1/class.tx_typo3button_pi1_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_button/pi1/class.tx_typo3button_pi1_wizicon.php']);
}
?>