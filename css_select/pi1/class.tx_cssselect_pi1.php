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
	 * Plugin 'StyleSheet Selector' for the 'css_select' extension.
	 *
	 * @author		Jean-David Gadina <info@macmade.net>
	 * @version		2.2
	 */
	
	 /**
	 * [CLASS/FUNCTION INDEX OF SCRIPT]
	 * 
	 * SECTION:		1 - MAIN
	 *      73:		function main($content,$conf)
	 *     138:		function buildIndex($cssArray)
	 *     161:		function buildImports($cssArray,$confArray)
	 *     183:		function getCSSFiles
	 * 
	 *				TOTAL FUNCTIONS: 4
	 */
	
	// Typo3 FE plugin class
	require_once(PATH_tslib.'class.tslib_pibase.php');
	
	class tx_cssselect_pi1 extends tslib_pibase {
		
		// Same as class name
		var $prefixId = 'tx_cssselect_pi1';
		
		// Path to this script relative to the extension dir
		var $scriptRelPath = 'pi1/class.tx_cssselect_pi1.php';
		
		// The extension key
		var $extKey = 'css_select';
		
		/***************************************************************
		 * SECTION 1 - MAIN
		 *
		 * Base functions.
		 ***************************************************************/
		
		/**
		 * Adds one or more stylesheets.
		 * 
		 * This function includes additionnal stylesheet(s) to the page.
		 * 
		 * @param		$content			The content object
		 * @param		$conf				The TS setup
		 * @return		The header data
		 */
		function main($content,$conf) {
			
			// Store TS configuration array
			$this->conf = $conf;
			
			// Reads extension configuration
			$confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['css_select']);
			
			// CSS files
			$cssArray = $this->getCSSFiles();
			
			if (is_array($cssArray) && count($cssArray)) {
			
				// Storage
				$content = array();
				
				// Checks if there is one or more stylesheeet(s) to include
				if (is_array($cssArray) && count($cssArray)) {
					
					// Check how to include the stylesheets
					if ($conf['importRules']) {
						
						// Build <style> tag
						$content[] = '<style type="' . $conf['cssType'] . '" media="' . $conf['cssMedia'] . '">';
						$content[] = '<!--';
						
						// Add comments if required
						if ($conf['cssComments']) {
							$content[] = '/***************************************************************';
							$content[] = ' * Styles added by plugin "tx_cssselect_pi1"';
							$content[] = ' * ';
							$content[] = ' * Index:';
							$content[] = $this->buildIndex($cssArray);
							$content[] = ' ***************************************************************/';
						}
						
						// Add stylesheets
						$content[] = $this->buildImports($cssArray,$confArray);
						$content[] = '-->';
						$content[] = '</style>';
					} else {
						
						// Build a <link> tag for each stylesheet
						foreach($cssArray as $stylesheet) {
							
							// Close tag for XHTML compliance, if required
							$xHTML = ($conf['xHTML']) ? '/' : '';
							
							// Add stylesheet
							$content[] = '<link rel="' . $conf['linkRel'] . '" href="' . $confArray['CSSDIR'] . $stylesheet . '" type="' . $conf['cssType'] . '" media="' . $conf['cssMedia'] . '" charset="' . $conf['linkCharset'] . '"' . $xHTML . '>';
						}
					}
				}
				
				// Returns header data
				return implode(chr(10),$content) . chr(10);
			}
		}
		
		/**
		 * Build the index of the page stylesheets.
		 * 
		 * @param		$cssArray			The page stylesheet(s)
		 * @return		An index of the stylesheets
		 */
		function buildIndex($cssArray) {
			
			// Init
			$index = array();
			$i = 1;
			
			// Builds comments
			foreach($cssArray as $stylesheet) {
				$index[] = ' * ' . $i . ') ' . $stylesheet;
				$i++;
			}
			
			// Returns the index
			return implode(chr(10),$index);
		}
		
		/**
		 * Build @import commands.
		 * 
		 * @param		$cssArray			The page stylesheet(s)
		 * @param		$confArray			The configuration of the extension
		 * @return		A CSS @import command for each stylesheet
		 */
		function buildImports($cssArray,$confArray) {
			
			// Init
			$imports = array();
			
			// Builds import commands
			foreach($cssArray as $stylesheet) {
				$imports[] = '@import url("' . $confArray['CSSDIR'] . $stylesheet . '");';
			}
			
			// Returns the import commands
			return implode(chr(10),$imports);
		}
		
		/**
		 * Return all CSS file
		 * 
		 * This function returns the specified CSS files for the current page. It also
		 * checks, if needed, for stylesheets on the top paes.
		 * 
		 * @return		An array with the stylesheets to load
		 */
		function getCSSFiles() {
			
			// Checking if the recursive option si set
			if ($this->conf['recursive'] == 1) {
				
				// Storage
				$cssArray = array();
				
				// Check each top page
				// MLC start from end page
				$pages			= $GLOBALS['TSFE']->config['rootLine'];
				$pages			= array_reverse( $pages );

				foreach($pages as $topPage) {
					// Check if a stylesheet is specified
					// Thanx to Wolfgang Klinger for the debug
					if ($topPage['tx_cssselect_stylesheets'])
					{
						
						// Add CSS files
						$cssArray = array_merge($cssArray,explode(',',$topPage['tx_cssselect_stylesheets']));
					}

					// MLC stop recursion
					if ($topPage['tx_cssselect_stop'])
					{
						break;
					}
				}
			} else if (!empty($GLOBALS['TSFE']->page['tx_cssselect_stylesheets'])) {
				
				// Get page only stylesheets
				$cssArray = explode(',',$GLOBALS['TSFE']->page['tx_cssselect_stylesheets']);
			}
			
			// Return CSS files
			return $cssArray;
		}
	}
	
	/** 
	 * XClass inclusion.
	 */
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/css_select/pi1/class.tx_cssselect_pi1.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/css_select/pi1/class.tx_cssselect_pi1.php']);
	}
?>
