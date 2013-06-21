<?php
/***************************************************************
*  Copyright notice
*  
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
 *Frontend Plugin 'Browser warning' for the 'browser_warning' extension.
 
 This detects whether the user is using an older browser (defined here as
 IE 4 or 5, or 5.5. A message is displayed to the user.
 
 * @author Jaspreet Singh
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_browserwarning_pi1 extends tslib_pibase {
	var $prefixId = "tx_browserwarning_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_browserwarning_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "browser_warning";	// The extension key.
	
	/**
	 * Main entry point for Browser Warning plugin
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
			
		$content = $this->oldBrowserMessage();
	
		return $this->pi_wrapInBaseClass($content);
	}

	/**Return true if the user agent is some version of Internet Explorer 5 or 4
	@see http://msdn.microsoft.com/library/?url=/workshop/author/dhtml/overview/aboutuseragent.asp
	*/
	function isIE5Or4() {
		$is = false;
		$IE5StringPattern = "/MSIE 5/";
		$IE4StringPattern = "/MSIE 4/";
		$userAgentString = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match( $IE5StringPattern, $userAgentString) || preg_match( $IE4StringPattern, $userAgentString)) {
			$is = true;
		}
		return $is;
	}
	
	
	/**Returns a message to be displayed in the case that the user agent is old.
	Currently only checks for IE5 or 4*/
	function oldBrowserMessage() {
		$oldBrowserMessage = "<p>It seems you are using an older version of Internet Explorer. You may want to 
		upgrade to the latest version available from Microsoft at <a 
		href='http://microsoft.com/windows/ie/downloads/'>http://microsoft.com/windows/ie/downloads/</a>.  
		<br>If you have trouble using the form, please contact us at <a 
		href='mailto:lodonnell@bpminstitute.org'>lodonnell@bpminstitute.org</a> and we will process your 
		information manually.<p>";
		if ($this->isIE5Or4()) {
			return $oldBrowserMessage;  
		} else {
			return "";
		}
	}



	/**
	 * ExtMgr-created main entry point for Browser Warning plugin
	 */
	function oldMain($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
			
		$content='
			<strong>This is a few paragraphs:</strong><BR>
			<p>This is line 1</p>
			<p>This is line 2.</p>
	
			<h3>This is a form:</h3>
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST">
				<input type="hidden" name="no_cache" value="1">
				<input type="text" name="'.$this->prefixId.'[input_field]" value="'.htmlspecialchars($this->piVars["input_field"]).'">
				<input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>
			<BR>
			<p>You can click here to '.$this->pi_linkToPage("get to this page again",$GLOBALS["TSFE"]->id).'</p>
		';
	
		return $this->pi_wrapInBaseClass($content);
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/browser_warning/pi1/class.tx_browserwarning_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/browser_warning/pi1/class.tx_browserwarning_pi1.php"]);
}

?>