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
 * Plugin 'Products Search result' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi3 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_shoppingsystem_pi3.php';	// Path to this script relative to the extension dir.
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
		return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
	}
	
	function extraCodesProcessor($news)
	{
        if($news->theCode == 'PRODUCTS_SEARCH')
        {
			$content='
				Products Search	
			';
        }
	
		return $this->pi_wrapInBaseClass($content);
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi3/class.tx_shoppingsystem_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi3/class.tx_shoppingsystem_pi3.php']);
}

?>