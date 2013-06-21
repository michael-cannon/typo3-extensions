<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Jens Mittag (jens.mittag@prime23.de)
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
 * Plugin 'Random Quotation.' for the 'jm_quote' extension.
 *
 * @author	Jens Mittag <jens.mittag@prime23.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_jmquote_pi1 extends tslib_pibase {
	var $prefixId 		= 'tx_jmquote_pi1';
	var $scriptRelPath 	= 'pi1/class.tx_jmquote_pi1.php';
	var $extKey 		= 'jm_quote';
	
	var $quote_table 	= 'tx_jmquote_quotation';
	
	/**
	 * Main function which returns a random quote
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;
		$listMode				= $this->conf['listMode'];
	
		if ( ! $listMode )
		{
			$quote = $this->getRandomQuote();

			$cite = $this->cObj->wrap($quote['text'], $this->conf['citeWrap']);
			if ($quote["author"] > "") 
				$author = $this->cObj->wrap($quote['author'], $this->conf['authorWrap']);
			else
				$author = '';
			$content = $this->cObj->wrap($cite . $author, $this->conf['contentWrap']);
		}
		
		else
		{
			$content			= $this->getQuotes();
		}
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * @return 	Array	Quote-Information (fields: text, author)
	 */
	function getRandomQuote () {
		$fields = "*";
		$from_table = $this->quote_table;
		$pid = $this->conf["pid_list"] != 0 ? $this->conf["pid_list"] : $this->cObj->data['pid'];
        $where_clause = " pid='".$pid."' AND deleted = 0 AND hidden = 0";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $from_table, $where_clause, "", "RAND()", "0,1");
		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
	}
	
	/**
	 * @return 	string	Quotes
	 */
	function getQuotes () {
		$fields = "*";
		$from_table = $this->quote_table;
		$pid = $this->conf["pid_list"] != 0 ? $this->conf["pid_list"] : $this->cObj->data['pid'];
        $where_clause = " pid='".$pid."' AND deleted = 0 AND hidden = 0";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $from_table, $where_clause, "", "tstamp DESC");

		$content				= '';
		while ( $result && $quote =
			$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) )
		{
			$cite = $this->cObj->wrap($quote['text'], $this->conf['citeWrap']);
			if ($quote["author"] > "") 
				$author = $this->cObj->wrap($quote['author'], $this->conf['authorWrap']);
			else
				$author = '';
			$content .= $this->cObj->wrap($cite . $author, $this->conf['contentWrap']);
		}

		return $content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jm_quote/pi1/class.tx_jmquote_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jm_quote/pi1/class.tx_jmquote_pi1.php']);
}

?>
