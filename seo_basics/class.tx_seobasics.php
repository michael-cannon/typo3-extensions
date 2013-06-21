<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Benjamin Mack (www.xnos.org) 
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
 * @author		Benjamin Mack (www.xnos.org) 
 * @subpackage	tx_seobasics
 * 
 * This package includes all hook implementations.
 */

class tx_seobasics {

	/**
	 * Hook function for cleaning output XHTML
	 * hooks on "class.tslib_fe.php:2946"
	 *
	 * @param       array           hook parameters
	 * @param       object          Reference to parent object (TSFE-obj)
	 * @return      void
	 */
	function processOutputHook(&$feObj, $ref) {
		if ($GLOBALS['TSFE']->type != 0) {
			return;
		}
		$spltContent = explode("\n", $ref->content);
		$level = 0;

		$cleanContent = array();
		foreach($spltContent as $lineNum => $line)	{
			$line = trim($line);
			if (empty($line)) continue;
			$out = $line;

			// ugly strpos => TODO: use regular expressions
			// starts with an ending tag
			if (strpos($line, '</div>') === 0
			|| (strpos($line, '<div')   !== 0 && strpos($line, '</div>') === strlen($line)-6)
			|| strpos($line, '</html>') === 0
			|| strpos($line, '</body>') === 0
			|| strpos($line, '</head>') === 0
			|| strpos($line, '</ul>') === 0)
				$level--;


				// add indention if there is 
			if (strpos($line, '</textarea>') === false) {
				for($i = 0; $i < $level; $i++)	{
					$out = "\t".$out;
				}
			}

			// starts with an opening <div>, <ul>, <head> or <body>
			if ((strpos($line, '<div') === 0 && strpos($line, '</div>')  !== strlen($line)-6)
			|| (strpos($line, '<body') === 0 && strpos($line, '</body>') !== strlen($line)-7)
			|| (strpos($line, '<head') === 0 && strpos($line, '</head>') !== strlen($line)-7)
			|| (strpos($line, '<ul')   === 0 && strpos($line, '</ul>')   !== strlen($line)-5))
				$level++;


			$cleanContent[] = $out;
		}

		$ref->content = implode("\n", $cleanContent);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics.php']) {
   include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics.php']);
}
?>
