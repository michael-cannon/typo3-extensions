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
 * Plugin 'Related Product' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi4 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_shoppingsystem_pi4.php';	// Path to this script relative to the extension dir.
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

		$newsUid = (int)$_GET['tx_ttnews']['tt_news'];

		if($newsUid != 0)
		{
			$sqlRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'`t2`.*',
				'`tt_news` `t1`, `tt_news` `t2`',
				"`t1`.`uid` = $newsUid AND `t1`.`tx_shoppingsystem_related_product` = `t2`.`uid` AND t2.hidden<>1 AND t2.deleted<>1");
			if($GLOBALS['TYPO3_DB']->sql_num_rows($sqlRes) > 0)
			{
				$relatedProduct = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlRes);
				$imageSrc = '/uploads/tx_shoppingsystem/no_products.gif';
				if($relatedProduct['tx_shoppingsystem_product_image_small'] != '')
					$imageSrc = "/uploads/tx_shoppingsystem/{$relatedProduct['tx_shoppingsystem_product_image_small']}";

				$template = t3lib_div::getURL(t3lib_extMgm::extPath('shopping_system').'res/template.html');
				$template = $this->cObj->getSubpart($template, '###TEMPLATE_RELATED###');

				$markers = array(
					'###PRODUCT_TITLE###' => $relatedProduct['title'],
					'###PRODUCT_LINK###' => $relatedProduct['tx_shoppingsystem_product_merchant_url'],
					'###PRODUCT_IMAGE###' => $imageSrc
				);

				$content = $this->cObj->substituteMarkerArrayCached($template, $markers);
			}
		}

		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi4/class.tx_shoppingsystem_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi4/class.tx_shoppingsystem_pi4.php']);
}

?>