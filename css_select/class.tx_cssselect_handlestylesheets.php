<?php
	/***************************************************************
	 * Copyright notice
	 * 
	 * (c) 2004 Jean-David Gadina (info@macmade.net)
	 * All rights reserved
	 * 
	 * This script is part of the TYPO3 project. The TYPO3 project is 
	 * free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 * 
	 * The GNU General Public License can be found at
	 * http://www.gnu.org/copyleft/gpl.html.
	 * 
	 * This script is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 * 
	 * This copyright notice MUST APPEAR in all copies of the script!
	***************************************************************/
	
	/** 
	 * Class/Function which manipulates the item-array for table/field pages_tx_cssselect_stylesheets.
	 *
	 * @author		Jean-David Gadina <info@macmade.net>
	 * @version		1.0
	 */
	
	/**
	 * [CLASS/FUNCTION INDEX OF SCRIPT]
	 * 
	 * SECTION:		1 - MAIN
	 *      59:		function main(&$params,&$pObj)
	 * 
	 *				TOTAL FUNCTIONS: 1
	 */
	
	class tx_cssselect_handleStylesheets {
		
		/***************************************************************
		 * SECTION 1 - MAIN
		 *
		 * Base functions.
		 ***************************************************************/
		
		/**
		 * Adds items to the stylesheet selector.
		 * 
		 * This function reads all the CSS file in the defined stylesheet
		 * directory, and adds the references to the selector.
		 * 
		 * @param		&$params			The parameters of the form
		 * @param		&$pObj				Reference to the parent object
		 * @return		Nothing
		 */
		function main(&$params,&$pObj)	{
			
			// Conf
			$confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['css_select']);
		
			// Path Values
			$readPath = t3lib_div::getFileAbsFileName($confArray['CSSDIR']);
			
			if (@is_dir($readPath)) {
				
				// Gets all CSS files
				$cssFiles = t3lib_div::getFilesInDir($readPath,$confArray['CSSEXT'],1,1);
				
				foreach($cssFiles as $stylesheet) {
					
					// Reset
					$selectorBoxItem_title = '';
					
					// Adds items
					$selectorBoxItem_title = basename($stylesheet);
					$params['items'][] = array(
						$selectorBoxItem_title,
						basename($stylesheet)
					);
				}
			}
		}
	}
	
	/**
	 * XClass inclusion.
	 */
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/css_select/class.tx_cssselect_handlestylesheets.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/css_select/class.tx_cssselect_handlestylesheets.php']);
	}

?>
