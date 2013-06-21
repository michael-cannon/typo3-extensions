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
 * Plugin 'Security Question Verification' for the 'security_question' extension.
 *
 @author Jaspreet Singh
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_securityquestion_pi1 extends tslib_pibase {
	var $cObj;
	var $prefixId = "tx_securityquestion_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_securityquestion_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "security_question";	// The extension key.
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
			
		$content='
			<strong>This is a few paragraphs:</strong><BR>
			<p>This is line 1</p>
			<p>This is line 2</p>
	
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
	
	
	/**
	* Retrieves and returns the data rows from the security questions table.
	* Doesn't returned disabled/delete/hidden/etc. rows.
	* @return the data rows.
	* @author Jaspreet Singh
	*/
	function getSecurityQuestionRows()
	{
		if ($this->debug) {
			echo 'getSecurityQuestionRows()<br>';
		}

		$this->pi_setPiVarDefaults();
		$db = $GLOBALS['TYPO3_DB'];
        $table 		= 'tx_securityquestion_questions';
		$columns 	= 'uid, question';
        $where 		= ' 1=1 ' . $this->cObj->enableFields($table);
		$groupby 	= '';
		$orderby 	= 'sorting';
		$rows 		=  $db->exec_SELECTgetRows($columns, $table, $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $columns; echo $where; 
			echo 'Questions rows:';
			foreach ( $rows as $row ) {
				print_r($row);
				//$output .= '';
			}
			reset( $rows );
		}
		return $rows;
	}
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/security_question/pi1/class.tx_securityquestion_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/security_question/pi1/class.tx_securityquestion_pi1.php"]);
}

?>