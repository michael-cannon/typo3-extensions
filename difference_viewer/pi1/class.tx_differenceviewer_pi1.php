<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Gordienko <dmitry.gordienko@gmail.com>
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

require_once(PATH_tslib.'class.tslib_pibase.php');

// additional setup
require_once(t3lib_extMgm::extPath('log_analyzer').'LogAnalyzer.php');
/**
 * Plugin 'Difference viewer' for the 'difference_viewer' extension.
 *
 * @author	Dmitry Gordienko <dmitry.gordienko@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_differenceviewer
 */
class tx_differenceviewer_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_differenceviewer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_differenceviewer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'difference_viewer';	// The extension key.
	var $pi_checkCHash = true;
	
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
		
		$content.= '<link rel="stylesheet" type="text/css" href="/typo3/stylesheet.css" />';
		
		$content .= '<div style="text-align:right"><a href="#" onclick="window.close()">
		<img src="'.t3lib_extMgm::extRelPath('difference_viewer').'close.gif" />
		</a></div>';
	
		if ((isset($_GET['uid']) && preg_match("/^\d+$/",$_GET['uid'])) ||
		    (isset($_GET['log_uid']) && preg_match("/^\d+$/",$_GET['log_uid']))) {
   	       $analyze = new LogAnalyzer();
   		   // 2 for FE module
           $analyze->setMode(2);
           $content .= $analyze->init();
		} else {
		   $content = "<p align='center'>This is a system page<br />
		   You may use it only with the difference viewer.</p>";
		}
      
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/difference_viewer/pi1/class.tx_differenceviewer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/difference_viewer/pi1/class.tx_differenceviewer_pi1.php']);
}
?>
