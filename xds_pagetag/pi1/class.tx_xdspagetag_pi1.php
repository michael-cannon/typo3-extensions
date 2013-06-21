<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Kasper (kasper2005@typo3.com)
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
 * Plugin 'Page Tag Redirection' for the 'xds_pagetag' extension.
 *
 * @author	Kasper <kasper2005@typo3.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Page Tag Redirection'
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_xdspagetag
 */
class tx_xdspagetag_pi1 extends tslib_pibase {
	var $prefixId = 'tx_xdspagetag_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_xdspagetag_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'xds_pagetag';	// The extension key.

	/**
	 * Plugin main function
	 *
	 * @param	string		IGNORE
	 * @param	array		TypoScript Configuration array
	 * @return	string		Returns empty string in case of "bypass", otherwise it will REDIRECT!
	 */
	function main($content,$conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!


			// Get keyword from indexed_search sword field. Clean it up so it's lowercase and alphanum:
		$indexed_search_data = t3lib_div::_GP('tx_indexedsearch');
#debug($indexed_search_data);
		$keyword = trim(strtolower(ereg_replace('[^ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-]','',$indexed_search_data['sword'])));

			// Look for keyword alias in pages table:
		list($matchingPage) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid',
			'pages',
			$GLOBALS['TYPO3_DB']->listQuery('tx_xdspagetag_pagetag', $keyword,'pages').
				$this->cObj->enableFields('pages'),
			'',
			'',
			1
		);

			// If page tag found:
		if (is_array($matchingPage) && $keyword!=='') {

				// Try to make URL for found page:
			$url = $this->pi_getPageLink($matchingPage['uid']);

				// Record the hit for stats:
			if ($url)	{
				$fields_values = array (
					'searchstring' => $keyword,
					'tstamp' => time()
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_pagetag_stat',
					$fields_values
				);
			}
		}

			// Default is forward to indexed search page if no tag found:
		if (!$url && $this->cObj->data['pages']) {
			$url = $this->pi_getPageLink($this->cObj->data['pages'],'',t3lib_div::implodeArrayForUrl('tx_indexedsearch',$indexed_search_data));
		}

			// Forward to URL otherwise:
		if ($url)	{
			if ($conf['showKeywordFoundBox'])	{
				return '
				<div style="border: 1px solid black; background-color: #eeeeee; padding: 10px 10px 10px 10px; margin-bottom: 10px; margin-top: 10px;">
					<b>Keyword found!</b><br/>
					We have found an exact match for the word you searched for! <a href="'.htmlspecialchars($url).'">Go to this page now!</a>
				</div>
				';
			} else {
				header('Location: '.t3lib_div::locationHeaderUrl($url));
				exit;
			}
		} else {
			return '';	// Return empty string (this is if no page tag was found and the indexed search page was not redirected to).
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xds_pagetag/pi1/class.tx_xdspagetag_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xds_pagetag/pi1/class.tx_xdspagetag_pi1.php']);
}

?>