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
 * Plugin 'TYPO3 Button' for the 'typo3_button' extension.
 *
 * @author	Michael Cannon <michael@peimic.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once (PATH_t3lib.'class.t3lib_stdgraphic.php');

/**
 * Main typo3_button class..
 * 
 * @author	Michael Cannon <michael@peimic.com>
 * @package TYPO3
 * @subpackage tx_typo3button
 */
class tx_typo3button_pi1 extends tslib_pibase {
	var $prefixId = 'tx_typo3button_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_typo3button_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'typo3_button';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * Main function for the class that passes handling to the draw functions.
	 * @param	string	Content coming into the extension.
	 * @param	array		Configuration options from Typoscript.
	 * @return	string	HTML output of extension.
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
				
		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		/* Select a drawing mode based on demoMode flag */
		if ($this->conf['demoMode']) {
			$content = $this->drawInDemoMode();
		}
		else {
			$imgPath = t3lib_extMgm::siteRelPath($this->extKey)."images/".$this->pi_getFFvalue($piFlexForm, "button", "default");
			$content = $this->drawButton($imgPath);
		}

		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Initiates drawing in demo mode, drawing all buttons regardless of backend selection
	 * @return	string	HTML representation of all buttons
	 */
	function drawInDemoMode() {
		$imgFolder = t3lib_extMgm::siteRelPath($this->extKey)."images/";
		
		$buttons = t3lib_div::getFilesInDir($imgFolder,'gif,jpg,jpeg,png',0);
		sort($buttons);
		
		$content = array();
		/* For every button, draw it and append the HTML to the content array */
		foreach ($buttons as $button) {
			$imgPath = $imgFolder.$button;
			
			if (@is_file($imgPath)) {
				$content[] = $this->drawButton($imgPath);
			}
		}
		
		return implode("<br />", $content);
	}
	
	/**
	 * Draws a single button
	 * @param	string	Path to the images file for the button.
	 * @return	string	HTML representation of the button.
	 */
	function drawButton($imgPath) {		
		/* Get the template cObj and the appropriate subpart */
		$templateFile = $this->cObj->fileResource($this->conf['templateFile']);				
		$template = $this->cObj->getSubpart($templateFile, '###TEMPLATE###');
		$altText = $this->conf['altText'];
		$titleText = $this->conf['titleText'];
		$url = $this->conf['url'];
		$target = $this->conf['target'];
		$align = $this->conf['align'];

		/* Define markers */
		$markers = array();
		$markers['###URL###'] = $url;
		$markers['###ALIGN###'] = $align;
		$markers['###IMAGE###'] = $imgPath;
		$markers['###ALT_TEXT###'] = $altText;
		$markers['###TITLE_TEXT###'] = $titleText;
		$markers['###TARGET###'] = $target;

		/* Substitute the markers into the template */
		$content = $this->cObj->substituteMarkerArrayCached($template, $markers, array(), array());

		return $content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_button/pi1/class.tx_typo3button_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_button/pi1/class.tx_typo3button_pi1.php']);
}

?>