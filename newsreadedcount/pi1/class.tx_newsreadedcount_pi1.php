<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Jens Hirschfeld (Jens.Hirschfeld@KeepOut.de)
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
 * Plugin 'News readed - counter' for the 'newsreadedcount' extension.
 *
 * @author	Jens Hirschfeld <Jens.Hirschfeld@KeepOut.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_newsreadedcount_pi1 extends tslib_pibase {
	var $prefixId = 'tx_newsreadedcount_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_newsreadedcount_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'newsreadedcount';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $session_vars;
	var $newsUid;
	
	/**
	 * The main function is not used
	 */
	function main($content,$conf)	{
		return '';
	}
	
	/**
	 * Using a Hook in tt_news.
	 * If the news is shown in the singleview, increase the counter for this news in the table
	 * Increase it for a session only once, if a news is more than one time shown in single-view
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, $ttnewsObj)	{
		/*loads locallang.php*/
		$this->pi_loadLL();
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		$newsUid = $row['uid'];
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_newsreadedcount_readedcounter','tt_news','uid='.$newsUid);
		$news = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if (strtoupper($ttnewsObj->config['code']) == 'SINGLE' && $news !== false) {
			
			$session_vars = $GLOBALS['TSFE']->fe_user->getKey('ses','tx_newsreadedcount');
			
			if (!isset($session_vars[$newsUid]) || $session_vars[$newsUid] != '1') {
				$session_vars[$newsUid]='1';
				$GLOBALS['TSFE']->fe_user->setKey('ses','tx_newsreadedcount',$session_vars);
				$news['tx_newsreadedcount_readedcounter']++;
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news','uid='.$newsUid,$news);
			}
		}
		// Check total hits !
		// Init value
		$totalview = intval($news['tx_newsreadedcount_readedcounter']);
		$newCounterText = $this->pi_getLL('timeShowText');
		
		// Test value
		if ($totalview == 1) { 
			$newCounter =  sprintf($this->pi_getLL('OnetimeShown'),$totalview);
		} elseif ($totalview > 1) {
			$newCounter = sprintf($this->pi_getLL('timesShown'),$totalview);
		} else {
			// Hide value
			if ($ttnewsObj->conf['newsReadedCount.']['ShowNoHits']) {
				$newCounter = $this->pi_getLL('NoTimeShown');
				$newCounterValue = $totalview;
			} else {
				$newCounter = '';
				$newCounterText = '';
				$totalview = '';
			}
		}
		
		// Set markers
		$markerArray['###NEWS_COUNTER_VALUE###'] = $this->cObj->stdWrap($totalview,$ttnewsObj->conf['newsReadedCount.']['countervalue.']);
		$markerArray['###NEWS_COUNTER_TEXT###'] = $this->cObj->stdWrap($newCounterText,$ttnewsObj->conf['newsReadedCount.']['countertext.']);
		$markerArray['###NEWS_COUNTER###'] = $this->cObj->stdWrap($newCounter,$ttnewsObj->conf['newsReadedCount.']['counter.']);
		return $markerArray;
	}

	function processSelectConfHook( $parentObject, $selectConf ) {
		if ( strpos( $parentObject->originalCode, 'MOST_READ_' ) === 0  ) {
			$ascDesc = $parentObject->pi_getFFvalue($parentObject->cObj->data['pi_flexform'], 'ascDesc', 'sDEF');
			$ascDesc = $ascDesc ? $ascDesc : 'DESC';
			$selectConf[ 'orderBy' ]	= 'tx_newsreadedcount_readedcounter';
			$selectConf[ 'orderBy' ]	.= ' ' . $ascDesc;
		}

		return $selectConf;
	}

	function extraCodesProcessor($news) {
		if ( strpos( $news->theCode, 'MOST_READ_' ) === 0  ) {
			$codes				= explode( 'MOST_READ_', $news->theCode );
			$news->originalCode	= $news->theCode;
			$news->theCode		= $codes[1];

			return $news->displayList();
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsreadedcount/pi1/class.tx_newsreadedcount_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsreadedcount/pi1/class.tx_newsreadedcount_pi1.php']);
}

?>