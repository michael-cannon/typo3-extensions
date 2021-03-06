<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Sk�rh�j (kasper@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* class.tx_ttnews.php
*
* versatile news system for TYPO3.
* $Id: class.tx_ttnews.php,v 1.1.1.1 2010/04/15 10:04:09 peimic.comprock Exp $
*
* TypoScript setup:
* - See static/ts_new/setup.txt
* - See tt_news Reference: http://typo3.org/documentation/document-library/tt_news/
* - See TSref: http://typo3.org/documentation/document-library/doc_core_tsref/
*
* @author Rupert Germann <rupi@gmx.li>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  103: class tx_ttnews extends tslib_pibase
 *  134:     function main_news($content, $conf)
 *  209:     function init($conf)
 *  350:     function newsArchiveMenu()
 *  491:     function displaySingle()
 *  549:     function displayVersionPreview ()
 *  601:     function displayList($excludeUids = 0)
 *  890:     function getListContent($itemparts, $selectConf, $prefix_display)
 * 1011:     function getSelectConf($where, $noPeriod = 0)
 * 1115:     function generatePageArray()
 * 1131:     function getItemMarkerArray ($row, $textRenderObj = 'displaySingle')
 * 1351:     function insertPagebreaks($text,$firstPageWordCrop)
 * 1401:     function makeMultiPageSView($bodytext,$lConf)
 * 1431:     function makePageBrowser($showResultCount=1,$tableParams='',$pointerName='pointer')
 * 1512:     function getCategories($uid)
 * 1563:     function getSubCategories($catlist, $cc = 0)
 * 1586:     function displayCatMenu()
 * 1633:     function getCatMenuContent($array_in,$lConf, $l=0)
 * 1668:     function getSubCategoriesForMenu ($catlist, $fields, $cc = 0)
 * 1691:     function getCatMarkerArray($markerArray, $row, $lConf)
 * 1820:     function getImageMarkers($markerArray, $row, $lConf, $textRenderObj)
 * 1883:     function getRelated($uid)
 * 1990:     function userProcess($mConfKey, $passVar)
 * 2005:     function spMarker($subpartMarker)
 * 2023:     function searchWhere($sw)
 * 2034:     function formatStr($str)
 * 2049:     function getLayouts($templateCode, $alternatingLayouts, $marker)
 * 2067:     function initLanguages ()
 * 2081:     function initCategoryVars()
 * 2141:     function checkRecords($recordlist)
 * 2171:     function initTemplate()
 * 2196:     function initPidList ()
 * 2223:     function getXmlHeader()
 * 2312:     function getW3cDate($datetime)
 * 2337:     function main_xmlnewsfeed($content, $conf)
 * 2352:     function getStoriesResult()
 * 2366:     function cleanXML($str)
 * 2380:     function convertDates()
 * 2413:     function getHrDateSingle($tstamp)
 * 2426:     function displayFEHelp()
 * 2447:     function validateFields($fieldlist)
 * 2468:     function getNewsSubpart($myTemplate, $myKey, $row = Array())
 *
 * TOTAL FUNCTIONS: 41
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once (PATH_t3lib . 'class.t3lib_xml.php');
require_once (PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'news' for the 'tt_news' extension.
 *
 * @author Rupert Germann <rupi@gmx.li>
 * @package TYPO3
 * @subpackage tt_news
 */

// << YA; Class for character count in pagebreak; Jun 2007
class s_tag
{
	var $m_pos;
	var $m_IsCloseTagNeed = 0;
	function setPos($pos) {
   		$this->m_pos = $pos;
  }
  	function getPos() {
   		return $this->m_pos;
  }
  	function setIsCloseTagNeed($need) {
   		$this->m_IsCloseTagNeed = $need;
  }
  	function getIsCloseTagNeed() {
   		return $this->m_IsCloseTagNeed;
  }
}

// >> YA

class tx_ttnews extends tslib_pibase {

	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId = 'tx_ttnews'; // Same as class name
	var $scriptRelPath = 'pi/class.tx_ttnews.php'; // Path to this script relative to the extension dir.
	var $extKey = 'tt_news'; // The extension key.
	var $tt_news_uid;
	var $conf;
	var $config;
	var $langArr;
	var $sys_language_mode;
	var $alternatingLayouts;
	var $allowCaching;
	var $catExclusive;
	var $arcExclusive;
	var $searchFieldList = 'short,bodytext,author,keywords,links,imagecaption,title';
	var $theCode = '';
	var $rdfToc = '';
	var $vPrev = false; // versioning preview

	var $categories = array(); // Is initialized with the categories of the news system
	var $pageArray = array(); // Is initialized with an array of the pages in the pid-list

	/**
	 * Main news function: calls the init_news() function and decides by the given CODEs which of the
	 * functions to display news should by called.
	 *
	 * @param	string		$content : function output is added to this
	 * @param	array		$conf : configuration array
	 * @return	string		$content: complete content generated by the tt_news plugin
	 */
	function main_news($content, $conf) {
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$this->init($conf);

		if ($this->conf['displayCurrentRecord']) {
			$this->config['code'] = $this->conf['defaultCode']?trim($this->conf['defaultCode']):'SINGLE';
			$this->tt_news_uid = $this->cObj->data['uid'];
		}

		// get codes and decide which function is used to process the content
		$codes = t3lib_div::trimExplode(',', $this->config['code']?$this->config['code']:$this->conf['defaultCode'], 1);
		if (!count($codes)) { // no code at all
			$codes = array();
			$noCode = true;
		}
		while (list(, $theCode) = each($codes)) {
			$theCode = (string)strtoupper(trim($theCode));
			$this->theCode = $theCode;
			switch ($theCode) {
				case 'SINGLE':
				$content .= $this->displaySingle();
				break;
				case 'VERSION_PREVIEW':
				$content .= $this->displayVersionPreview();
				break;
				case 'LATEST':
				case 'LIST':
				case 'SEARCH':
				case 'XML':
				$content .= $this->displayList();
				break;
				case 'AMENU':
				$content .= $this->newsArchiveMenu();
				break;
				case 'CATMENU':
				$content .= $this->displayCatMenu();
				break;
				default:
				// Adds hook for processing of extra codes
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraCodesHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$content .= $_procObj->extraCodesProcessor($this);
					}
				} else {
					$langKey = strtoupper($GLOBALS['TSFE']->config['config']['language']);
					$helpTemplate = $this->cObj->fileResource('EXT:tt_news/pi/news_help.tmpl');
					// Get language version of the help-template
					$helpTemplate_lang = '';
					if ($langKey) {
						$helpTemplate_lang = $this->getNewsSubpart($helpTemplate, "###TEMPLATE_" . $langKey . '###');
					}
					$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang :
					$this->getNewsSubpart($helpTemplate, '###TEMPLATE_DEFAULT###');
					// Markers and substitution:
					$markerArray['###CODE###'] = $this->theCode;
					$markerArray['###EXTPATH###'] = $GLOBALS['TYPO3_LOADED_EXT']['tt_news']['siteRelPath'];
					$content .= $this->displayFEHelp();
				}
				break;
			}
		}
		if($noCode) {
			$content .= $this->displayFEHelp();
		}
		return $content;
	}


	/**
	 * Init Function: here all the needed configuration values are stored in class variables..
	 *
	 * @param	array		$conf : configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		$this->conf = $conf; //store configuration
		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tt_news');
		$this->tt_news_uid = intval($this->piVars['tt_news']); // Get the submitted uid of a news (if any)

        
        
		// load available syslanguages
		$this->initLanguages();
		// sys_language_mode defines what to do if the requested translation is not found
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;

		// "CODE" decides what is rendered: codes can be set by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->config['code'] = $code ? $code : $this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);

		// initialize category vars
		$this->initCategoryVars();

			// get fieldnames from the tt_news db-table
		$this->fieldNames = array_keys($GLOBALS['TYPO3_DB']->admin_get_fields('tt_news'));

		if ($this->conf['searchFieldList']) {
			$searchFieldList = $this->validateFields($this->conf['searchFieldList']);
			if ($searchFieldList) {
				$this->searchFieldList = $searchFieldList;
			}
		}
			// Archive:
		$this->config['archiveMode'] = trim($this->conf['archiveMode']) ; // month, quarter or year listing in AMENU
		$this->config['archiveMode'] = $this->config['archiveMode']?$this->config['archiveMode']:'month';

		// arcExclusive : -1=only non-archived; 0=don't care; 1=only archived
		$arcExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'archive', 'sDEF');
		$this->arcExclusive = $arcExclusive?$arcExclusive:$this->conf['archive'];

		$this->config['datetimeDaysToArchive'] = intval($this->conf['datetimeDaysToArchive']);

		if ($this->conf['useHRDates']) {
			$this->convertDates();
		}

		// list of pages where news records will be taken from
		$this->initPidList();

		// itemLinkTarget is only used for categoryLinkMode 3 (catselector) in framesets
		$this->config['itemLinkTarget'] = trim($this->conf['itemLinkTarget']);
		// id of the page where the search results should be displayed
		$this->config['searchPid'] = intval($this->conf['searchPid']);

		// pages in Single view will be divided by this token
		$this->config['pageBreakToken'] = trim($this->conf['pageBreakToken'])?trim($this->conf['pageBreakToken']):'<---newpage--->';

		$this->config['singleViewPointerName'] = trim($this->conf['singleViewPointerName'])?trim($this->conf['singleViewPointerName']):'sViewPointer';


		$maxWordsInSingleView = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxWordsInSingleView', 's_misc'));
		$maxWordsInSingleView = $maxWordsInSingleView?$maxWordsInSingleView:intval($this->conf['maxWordsInSingleView']);
		$this->config['maxWordsInSingleView'] = $maxWordsInSingleView?$maxWordsInSingleView:0;
		
		$maxCharsInSingleView = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxCharsInSingleView', 's_misc'));
		$maxCharsInSingleView = $maxCharsInSingleView?$maxCharsInSingleView:intval($this->conf['maxCharsInSingleView']);
		$this->config['maxCharsInSingleView'] = $maxCharsInSingleView?$maxCharsInSingleView:0;
		
		$this->config['useMultiPageSingleView'] = $maxWordsInSingleView>1?1:$this->conf['useMultiPageSingleView'];

		// pid of the page with the single view. the old var PIDitemDisplay is still processed if no other value is found
		$singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDitemDisplay', 's_misc');
		$singlePid = $singlePid?$singlePid:intval($this->conf['singlePid']);
		$this->config['singlePid'] = $singlePid ? $singlePid:intval($this->conf['PIDitemDisplay']);

		// pid to return to when leaving single view
		$backPid = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPid', 's_misc'));
		$backPid = $backPid?$backPid:intval($this->conf['backPid']);
		$backPid = $backPid?$backPid:intval($this->piVars['backPid']);
		$backPid = $backPid?$backPid:$GLOBALS['TSFE']->id ;
		$this->config['backPid'] = $backPid;

		// max items per page
		$FFlimit = t3lib_div::intInRange($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listLimit', 's_misc'), 0, 1000);

		$limit = t3lib_div::intInRange($this->conf['limit'], 0, 1000);
		$limit = $limit?$limit:	50;
		$this->config['limit'] = $FFlimit?$FFlimit:	$limit;

		$latestLimit = t3lib_div::intInRange($this->conf['latestLimit'], 0, 1000);
		$latestLimit = $latestLimit?$latestLimit:10;
		$this->config['latestLimit'] = $FFlimit?$FFlimit:$latestLimit;

		// orderBy and groupBy statements for the list Query
		$orderBy = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listOrderBy', 'sDEF');
		$orderByTS = trim($this->conf['listOrderBy']);
		$orderBy = $orderBy?$orderBy:$orderByTS;
		$this->config['orderBy'] = $orderBy;

		if ($orderBy && !$orderByTS) {
			// orderBy is set from FF
			$ascDesc = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascDesc', 'sDEF');
			$this->config['ascDesc'] = $ascDesc;
		}
		$this->config['groupBy'] = trim($this->conf['listGroupBy']);

		// if this is set, the first image is handled as preview image, which is only shown in list view
		$fImgPreview = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'firstImageIsPreview', 's_misc');
		$this->config['firstImageIsPreview'] = $fImgPreview?$fImgPreview : $this->conf['firstImageIsPreview'];

		// List start id
		$listStartId = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listStartId', 's_misc'));
		$this->config['listStartId'] = $listStartId?$listStartId:intval($this->conf['listStartId']);
		// MLC specific news id to show
		$newsSelection = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'newsSelection', 's_misc');
		$this->config['newsSelection'] = $newsSelection?$newsSelection:-1;
		// supress pagebrowser
		$noPageBrowser = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noPageBrowser', 's_misc');
		$this->config['noPageBrowser'] = $noPageBrowser?$noPageBrowser:	$this->conf['noPageBrowser'];


		// image sizes given from FlexForms
		$this->config['FFimgH'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxHeight', 's_template'));
		$this->config['FFimgW'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxWidth', 's_template'));

		// Get number of alternative Layouts (loop layout in LATEST and LIST view) default is 2:
		$altLayouts = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alternatingLayouts', 's_template'));
		$altLayouts = $altLayouts?$altLayouts:intval($this->conf['alternatingLayouts']);
		$this->alternatingLayouts = $altLayouts?$altLayouts:2;

		// Get cropping lenght
		$this->config['croppingLenght'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'croppingLenght', 's_template'));

		$this->initTemplate();

		// Configure caching
		$this->allowCaching = $this->conf['allowCaching']?1:0;
		if (!$this->allowCaching) {
			$GLOBALS['TSFE']->set_no_cache();
		}

		// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (!$this->conf['displayXML.']['dontInsertSiteUrl']) {
			$this->config['siteUrl'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}
	}


	/**
	 * generates the News archive menu
	 *
	 * @return	string		html code of the archive menu
	 */
	function newsArchiveMenu() {
		$this->arcExclusive = 1;
		$selectConf = $this->getSelectConf('', 1);
		// Finding maximum and minimum values:
		$selectConf['selectFields'] = 'max(datetime) as maxval, min(datetime) as minval';
		$res = $this->cObj->exec_getQuery('tt_news', $selectConf);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['minval'] || $row['maxval']) {
			// if ($row['minval']) {
			$dateArr = array();
			$arcMode = $this->config['archiveMode'];
			$c = 0;
			do {
				switch ($arcMode) {
					case 'month':
					$theDate = mktime (0, 0, 0, date('m', $row['minval']) + $c, 1, date('Y', $row['minval']));
					break;
					case 'quarter':
					$theDate = mktime (0, 0, 0, floor(date('m', $row['minval']) / 3) + 1 + (3 * $c), 1, date('Y', $row['minval']));
					break;
					case 'year':
					$theDate = mktime (0, 0, 0, 1, 1, date('Y', $row['minval']) + $c);
					break;
				}
				$dateArr[] = $theDate;
				$c++;
				if ($c > 1000) break;
			}
			while ($theDate < $GLOBALS['SIM_EXEC_TIME']);

			reset($dateArr);
			$periodAccum = array();

			$selectConf2['where'] = $selectConf['where'];
			while (list($k, $v) = each($dateArr)) {
				if (!isset($dateArr[$k + 1])) {
					break;
				}

				$periodInfo = array();
				$periodInfo['start'] = $dateArr[$k];
				$periodInfo['stop'] = $dateArr[$k + 1]-1;
				$periodInfo['HRstart'] = date('d-m-Y', $periodInfo['start']);
				$periodInfo['HRstop'] = date('d-m-Y', $periodInfo['stop']);
				$periodInfo['quarter'] = floor(date('m', $dateArr[$k]) / 3) + 1;
				// execute a query to count the archive periods
				$selectConf['selectFields'] = 'count(distinct(uid))';
				$selectConf['where'] = $selectConf2['where'] . ' AND datetime>=' . $periodInfo['start'] . ' AND datetime<' . $periodInfo['stop'];
				$res = $this->cObj->exec_getQuery('tt_news', $selectConf);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
				$periodInfo['count'] = $row[0];

				if (!$this->conf['archiveMenuNoEmpty'] || $periodInfo['count']) {
					$periodAccum[] = $periodInfo;
				}
			}
			// get template subpart
			$t['total'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_ARCHIVE###'));
			$t['item'] = $this->getLayouts($t['total'], $this->alternatingLayouts, 'MENUITEM');
			$cc = 0;

			$veryLocal_cObj = t3lib_div::makeInstance('tslib_cObj');
			// reverse amenu order if 'reverseAMenu' is given
			if ($this->conf['reverseAMenu']) {
				arsort($periodAccum);
			}

			$archiveLink = $this->conf['archiveTypoLink.']['parameter'];
			$this->conf['parent.']['addParams'] = $this->conf['archiveTypoLink.']['addParams'];
			reset($periodAccum);
			$itemsOutArr = array();
			while (list(, $pArr) = each($periodAccum)) {
				// Print Item Title
				$wrappedSubpartArray = array();

				if ($this->config['catSelection'] && $this->config['amenuWithCatSelector']) {
					// use the catSelection from GPvars only if 'amenuWithCatSelector' is given.
					$amenuLinkCat = $this->config['catSelection'];
				} else {
					$amenuLinkCat = $this->catExclusive;
				}

				if ($this->conf['useHRDates']) {
					$year = date('Y',$pArr['start']);
					$month = date('m',$pArr['start']);
					if ($arcMode == 'year') {
						$archLinkArr = $this->pi_linkTP_keepPIvars('|', array('cat' => ($amenuLinkCat?$amenuLinkCat:null), 'year' => $year), $this->allowCaching, 1, ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id));
					} else {
						$archLinkArr = $this->pi_linkTP_keepPIvars('|', array('cat' => ($amenuLinkCat?$amenuLinkCat:null), 'year' => $year, 'month' => $month), $this->allowCaching, 1, ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id));
					}
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $archLinkArr);
				} else {
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('cat' => ($amenuLinkCat?$amenuLinkCat:null), 'pS' => $pArr['start'], 'pL' => ($pArr['stop'] - $pArr['start']), 'arc' => 1), $this->allowCaching, 1, ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)));
				}

				$markerArray = array();
				$veryLocal_cObj->start($pArr, '');
				$markerArray['###ARCHIVE_TITLE###'] = $veryLocal_cObj->cObjGetSingle($this->conf['archiveTitleCObject'], $this->conf['archiveTitleCObject.'], 'archiveTitle');
				$markerArray['###ARCHIVE_COUNT###'] = $pArr['count'];
				$markerArray['###ARCHIVE_ITEMS###'] = $this->pi_getLL('archiveItems');

				// fill the generated data to an array to pass it to a userfuction as a single variable
				$itemsOutArr[] = array('html' => $this->cObj->substituteMarkerArrayCached($t['item'][($cc % count($t['item']))], $markerArray, array(), $wrappedSubpartArray), 'data' => $pArr);
				$cc++;
			}
			// Pass to user defined function
			if ($this->conf['newsAmenuUserFunc']) {
				$itemsOutArr = $this->userProcess('newsAmenuUserFunc', $itemsOutArr);
			}

			foreach ($itemsOutArr as $itemHtml) {
				$tmpItemsArr[] = $itemHtml['html'];
			}

			if (is_array($tmpItemsArr)) {
				$itemsOut = implode('', $tmpItemsArr);
			}

			// Reset:
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray = array();
			$markerArray['###ARCHIVE_HEADER###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveHeader'), $this->conf['archiveHeader_stdWrap.']);
			// Set content
			$subpartArray['###CONTENT###'] = $itemsOut;
			$content = $this->cObj->substituteMarkerArrayCached($t['total'], $markerArray, $subpartArray, $wrappedSubpartArray);
		} else {
			// if nothing is found in the archive display the TEMPLATE_ARCHIVE_NOITEMS message
			$markerArray['###ARCHIVE_HEADER###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveHeader'), $this->conf['archiveHeader_stdWrap.']);
			$markerArray['###ARCHIVE_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveEmptyMsg'), $this->conf['archiveEmptyMsg_stdWrap.']);
			$noItemsMsg = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_ARCHIVE_NOITEMS###'));
			$content = $this->cObj->substituteMarkerArrayCached($noItemsMsg, $markerArray);
		}
		return $content;
	}

	/**
	 * Displays the "single view" of a news article. Is also used when displaying single news records with the "insert records" content element.
	 *
	 * @return	string		html-code for the "single view"
	 */
	function displaySingle() {
		$singleWhere = 'tt_news.uid=' . intval($this->tt_news_uid);
		$singleWhere .= ' AND type NOT IN(1,2)' . $this->enableFields; // only real news -> type=0
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', $singleWhere);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// get the translated record if the content language is not the default language
		if ($GLOBALS['TSFE']->sys_language_content) {
			$OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
		}
		if (is_array($row) && ($row['pid'] > 0 || $this->vPrev)) { // never display versions of a news record (having pid=-1) for normal website users
				// Get the subpart code
			if ($this->conf['displayCurrentRecord']) {
				$item = trim($this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE_RECORDINSERT###'), $row));
			}
			if (!$item) {
				$item = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE###'), $row);
			}
				// reset marker array
			$wrappedSubpartArray = array();
			if ($this->conf['useHRDates']) {
				$pointerName = 'pointer';
				$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => null, 'backPid' => null, 'year' => $this->piVars['year'], 'month' => $this->piVars['month'], $pointerName=>$this->piVars[$pointerName]), $this->allowCaching, 1, $this->config['backPid'] ));
			} else {
				$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => null, 'backPid' => null), $this->allowCaching, '', $this->config['backPid'] ));
			}
				// set the title of the single view page to the title of the news record
			if ($this->conf['substitutePagetitle']) {
				$GLOBALS['TSFE']->page['title'] = $row['title'];
				// set pagetitle for indexed search to news title
				$GLOBALS['TSFE']->indexedDocTitle = $row['title'];
			}
			$markerArray = $this->getItemMarkerArray($row, 'displaySingle');
			// Substitute
			$content = $this->cObj->substituteMarkerArrayCached($item, $markerArray, array(), $wrappedSubpartArray);
		} elseif ($this->sys_language_mode == 'strict' && $this->tt_news_uid && $GLOBALS['TSFE']->sys_language_content) { // not existing translation
			$noTranslMsg = $this->local_cObj->stdWrap($this->pi_getLL('noTranslMsg'), $this->conf['noNewsIdMsg_stdWrap.']);
			$content = $noTranslMsg;
		} elseif ($row['pid'] < 0) { // a non-public version of a record was requested
			$nonPlublicVersion = $this->local_cObj->stdWrap($this->pi_getLL('nonPlublicVersionMsg'), $this->conf['nonPlublicVersionMsg_stdWrap.']);
			$content = $nonPlublicVersion;
		} else { // if singleview is shown with no tt_news uid given from GETvars (&tx_ttnews[tt_news]=) an error message is displayed.
			$noNewsIdMsg = $this->local_cObj->stdWrap($this->pi_getLL('noNewsIdMsg'), $this->conf['noNewsIdMsg_stdWrap.']);
			$content = $noNewsIdMsg;
		}
		return $content;
	}

	/**
	 * Displays the "versioning preview".
	 * The functions checks:
	 * - if the extension "version" is loaded
	 * - if a BE_user is logged in
	 * - the plausibility of the requested "version preview".
	 * If this is all OK, "displaySingle()" is executed to display the "versioning preview".
	 *
	 * @return	string		html code for the "versioning preview"
	 */
	function displayVersionPreview () {
		if (t3lib_extMgm::isLoaded('version')) {
			$vPrev = t3lib_div::_GP('ADMCMD_vPrev');
			if ($this->piVars['ADMCMD_vPrev']) {
				$piADMCMD = unserialize(rawurldecode($this->piVars['ADMCMD_vPrev']));
			}
			if ((is_array($vPrev) || is_array($piADMCMD)) && is_object($GLOBALS['BE_USER'])) { // check if ADMCMD_vPrev is set and if a BE_user is logged in. $this->piVars['ADMCMD_vPrev'] is needed for previewing a "single view with pagebrowser"
				if (!is_array($vPrev)) { $vPrev = $piADMCMD; }
				list($table,$t3ver_oid) = explode(':',key($vPrev));
				if ($table == 'tt_news') {
					if ($testrec = $this->pi_getRecord('tt_news', intval($vPrev[key($vPrev)]))) { // check if record exists before doing anything
						if ($testrec['t3ver_oid'] == intval($t3ver_oid) && $testrec['pid']==-1) { // check if requested t3ver_oid is the t3ver_oid of the requested tt_news record, and if the pid of the record is -1 (=non-plublic version)
							$GLOBALS['TSFE']->set_no_cache(); // version preview will never be cached
								// make version preview message with a link to the public version of hte record which is previewed
							$vPrevHeader = $this->local_cObj->stdWrap(
								$this->pi_getLL('versionPreviewMessage').
									$this->local_cObj->typolink(
										$this->local_cObj->stdWrap(
											$this->pi_getLL('versionPreviewMessageLinkToOriginal'),$this->conf['versionPreviewMessageLinkToOriginal_stdWrap.']
										),
										array(
											'parameter' => $this->config['singlePid'].' _blank',
											'additionalParams' => '&tx_ttnews[tt_news]='.$t3ver_oid,
											'no_cache' => 1
										)
									),
								$this->conf['versionPreviewMessage_stdWrap.']
							);
							$this->tt_news_uid = $this->piVars['tt_news'] = $vPrev[key($vPrev)];
							$this->piVars['ADMCMD_vPrev'] = rawurlencode(serialize(array($table.':'.$t3ver_oid => $this->tt_news_uid)));
							$this->theCode = 'SINGLE';
							$this->vPrev = true;
							$content = $vPrevHeader.$this->displaySingle();
						} else { // error: t3ver_oid mismatch
							$GLOBALS['TT']->setTSlogMessage('tt_news: ERROR! The "t3ver_oid" of requested tt_news record and the "t3ver_oid" from GPvars doesn\'t match.');
						}
					}
				}
			}
		}
		return $content;
	}

	/**
	 * Display LIST,LATEST or SEARCH
	 * Things happen: determine the template-part to use, get the query parameters (add where if search was performed),
	 * exec count query to get the number of results, check if a browsebox should be displayed,
	 * get the general Markers for each item and fill the content array, check if a browsebox should be displayed
	 *
	 * @param	string		$excludeUids : commaseparated list of tt_news uids to exclude from display
	 * @return	string		html-code for the plugin content
	 */
	function displayList($excludeUids = 0) {
		$theCode = $this->theCode;

		$where = '';
		$content = '';
		switch ($theCode) {
			case 'LATEST':
			$prefix_display = 'displayLatest';
			$templateName = 'TEMPLATE_LATEST';
			if (!$this->conf['displayArchivedInLatest']) {
				// if this is set, latest will do the same as list
				$this->arcExclusive = -1; // Only latest, non archive news
			}
			$this->config['limit'] = $this->config['latestLimit'];
			break;

			case 'LIST':
			$prefix_display = 'displayList';
			$templateName = 'TEMPLATE_LIST';
			break;

			case 'SEARCH':
			$prefix_display = 'displayList';
			$templateName = 'TEMPLATE_LIST';

			$formURL = $this->pi_linkTP_keepPIvars_url(array('pointer' => null, 'cat' => null), 0, 1, $this->config['searchPid']) ;
			// Get search subpart
			$t['search'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH###'));
			// Substitute markers for the searchform
			$out = $t['search'];

			$out = $this->cObj->substituteMarker($out, '###FORM_URL###', $formURL);
			$out = $this->cObj->substituteMarker($out, '###SWORDS###', htmlspecialchars($this->piVars['swords']));
			$out = $this->cObj->substituteMarker($out, '###SEARCH_BUTTON###', $this->pi_getLL('searchButtonLabel'));
			// Add to content
			$content .= $out;
			// do the search and add the result to the $where string
			if ($this->piVars['swords']) {
				$where = $this->searchWhere(trim($this->piVars['swords']));
				$theCode = 'SEARCH';
			} else {
				$where = ($this->conf['emptySearchAtStart']?'AND 1=0':''); // display an empty list, if 'emptySearchAtStart' is set.
			}
			break;
			// xml news export
			case 'XML':
			$prefix_display = 'displayXML';
			// $this->arcExclusive = -1; // Only latest, non archive news
			$this->allowCaching = $this->conf['displayXML.']['xmlCaching'];
			$this->config['limit'] = $this->conf['displayXML.']['xmlLimit']?$this->conf['displayXML.']['xmlLimit']:
			$this->config['limit'];

			switch ($this->conf['displayXML.']['xmlFormat']) {
				case 'rss091':
				$templateName = 'TEMPLATE_RSS091';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss091_tmplFile']);
				break;

				case 'rss2':
				$templateName = 'TEMPLATE_RSS2';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss2_tmplFile']);
				break;

				case 'rdf':
				$templateName = 'TEMPLATE_RDF';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rdf_tmplFile']);
				break;

				case 'atom03':
				$templateName = 'TEMPLATE_ATOM03';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['atom03_tmplFile']);
				break;

			}
			break;
		}
		// process extra codes from $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']
		$userCodes = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'];

		if ($userCodes && !$prefix_display && !$templateName) {
			while (list(, $ucode) = each($userCodes)) {
				if ($theCode == $ucode[0]) {
					$prefix_display = 'displayList';
					$templateName = 'TEMPLATE_' . $ucode[0] ;
				}
			}
		}
		$noPeriod = 0; // used to call getSelectConf without a period lenght (pL) at the first archive page
		$pointerName = $this->pointerName = 'pointer';

		if (!$this->conf['emptyArchListAtStart']) {
			// if this is true, we're listing from the archive for the first time (no pS set), to prevent an empty list page we set the pS value to the archive start
			if (($this->arcExclusive > 0 && !$this->piVars['pS'] && $theCode != 'SEARCH')) {
				// set pS to time minus archive startdate
				$this->piVars['pS'] = ($GLOBALS['SIM_EXEC_TIME'] - ($this->config['datetimeDaysToArchive'] * 86400));
			}
		}
		if ($this->piVars['pS'] && !$this->piVars['pL']) {
			$noPeriod = 1; // override the period lenght checking in getSelectConf
		}
		// Allowed to show the listing? periodStart must be set, when listing from the archive.
		if (!($this->arcExclusive > -1 && !$this->piVars['pS'] && $theCode != 'SEARCH')) {
			if ($this->conf['displayCurrentRecord'] && $this->tt_news_uid) {
				$this->pid_list = $this->cObj->data['pid'];
				$where = 'AND tt_news.uid=' . $this->tt_news_uid;
			}
			if ($excludeUids) {
				$where = ($where?' ':'').'AND tt_news.uid NOT IN ('.$excludeUids.')';
			}

			// build parameter Array for List query
			$selectConf = $this->getSelectConf($where, $noPeriod);
			// performing query to count all news (we need to know it for browsing):
			$selectConf['selectFields'] = 'COUNT(DISTINCT(tt_news.uid))'; //count(*)
			$res = $this->cObj->exec_getQuery('tt_news', $selectConf);
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$newsCount = $row[0];
			// Only do something if the queryresult is not empty
			if ($newsCount > 0) {
				// Init Templateparts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
				// $t['item'] is an array with the alternative subparts (NEWS, NEWS_1, NEWS_2 ...)
				$t = array();
				$t['total'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###' . $templateName . '###'));

				$t['item'] = $this->getLayouts($t['total'], $this->alternatingLayouts, 'NEWS');
				// build query for display:
				$selectConf['selectFields'] = 'tt_news.*';
				if ($this->config['groupBy']) {
					$selectConf['groupBy'] = $this->config['groupBy'];
				} else {
					$selectConf['groupBy'] = 'tt_news.uid';
				}

				if ($this->config['orderBy']) {
					if (strtoupper($this->config['orderBy']) == 'RANDOM') {
						$selectConf['orderBy'] = 'RAND()';
					} else {
						$selectConf['orderBy'] = $this->config['orderBy'] . ($this->config['ascDesc']?' ' . $this->config['ascDesc']:'');
					}
				} else {
					$selectConf['orderBy'] = 'datetime DESC';
				}

				// overwrite the groupBy value for categories
				if (!$this->catExclusive && $selectConf['groupBy'] == 'category') {
					$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
					$selectConf['groupBy'] = 'tt_news_cat_mm.uid_foreign';
					$selectConf['selectFields'] = 'DISTINCT tt_news.uid,tt_news.*';
				}
				// exclude the LATEST template from changing its content with the pagebrowser. This can be overridden by setting the conf var latestWithPagebrowser
//				if ($theCode != 'LATEST' || $this->conf['latestWithPagebrowser']) {
					$selectConf['begin'] = $this->piVars[$pointerName] * $this->config['limit'];
//				}
				// exclude news-records shown in LATEST from the LIST template
				if ($theCode == 'LIST' && $this->conf['excludeLatestFromList'] && !$this->piVars[$pointerName] && !$this->piVars['cat']) {
					if ($this->config['latestLimit']) {
						$selectConf['begin'] += $this->config['latestLimit'];
						$newsCount -= $this->config['latestLimit'];
					} else {
						$selectConf['begin'] += $newsCount;
						// this will clean the display of LIST view when 'latestLimit' is unset because all the news have been shown in LATEST already
					}
				}

				// List start ID
				if (($theCode == 'LIST' || $theCode == 'LATEST') && $this->config['listStartId'] && !$this->piVars[$pointerName] && !$this->piVars['cat']) {
					$selectConf['begin'] = $this->config['listStartId'];
				}

                // MLC specific news to show
                if ( $this->config['newsSelection'] 
					&& strcmp( '-1', $this->config['newsSelection'] )
				)
                {
                    $selectConf['where']    = "
                        1 = 1
                        AND tt_news.uid IN ( {$this->config['newsSelection']} )
                    ";
                }

				// Reset:
				$subpartArray = array();
				$wrappedSubpartArray = array();
				$markerArray = array();

				// get the list of news items and fill them in the CONTENT subpart
				$subpartArray['###CONTENT###'] = $this->getListContent($t['item'], $selectConf, $prefix_display);

				if ($theCode == 'XML') {
					$markerArray = $this->getXmlHeader();
					$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);
					if($this->conf['displayXML.']['xmlFormat']) {
						if(!empty($this->rdfToc)) {
							$markerArray['###NEWS_RDF_TOC###'] = '<rdf:Seq>'."\n".$this->rdfToc."\t\t\t".'</rdf:Seq>';
						} else {
							$markerArray['###NEWS_RDF_TOC###'] = '';
						}
					}
					$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);
				}

				$markerArray['###GOTOARCHIVE###'] = $this->pi_getLL('goToArchive');
				$markerArray['###LATEST_HEADER###'] = $this->pi_getLL('latestHeader');
				$wrappedSubpartArray['###LINK_ARCHIVE###'] = $this->local_cObj->typolinkWrap($this->conf['archiveTypoLink.']);
				// unset pagebrowser markers
				$markerArray['###LINK_PREV###'] = '';
				$markerArray['###LINK_NEXT###'] = '';
				$markerArray['###BROWSE_LINKS###'] = '';
				// render a pagebrowser if needed
				if ($newsCount > $this->config['limit'] && !$this->config['noPageBrowser']) {
					// configure pagebrowser vars
					$this->internal['res_count'] = $newsCount;
					$this->internal['results_at_a_time'] = $this->config['limit'];
					$this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'];

					if (!$this->conf['pageBrowser.']['showPBrowserText']) {
						$this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
					}
					if ($this->conf['userPageBrowserFunc']) {
						$markerArray = $this->userProcess('userPageBrowserFunc', $markerArray);
					} else {
						if ($this->conf['usePiBasePagebrowser']) {
							$this->internal['pagefloat'] = $this->conf['pageBrowser.']['pagefloat'];
							$this->internal['showFirstLast'] = $this->conf['pageBrowser.']['showFirstLast'];
							$this->internal['showRange'] = $this->conf['pageBrowser.']['showRange'];
							$this->internal['dontLinkActivePage'] = $this->conf['pageBrowser.']['dontLinkActivePage'];

							$$wrapArrFields = explode(',', 'disabledLinkWrap,inactiveLinkWrap,activeLinkWrap,browseLinksWrap,showResultsWrap,showResultsNumbersWrap,browseBoxWrap');
							$wrapArr = array();
							foreach($$wrapArrFields as $key) {
								if ($this->conf['pageBrowser.'][$key]) {
									$wrapArr[$key] = $this->conf['pageBrowser.'][$key];
								}
							}
							// if there is a GETvar in the URL that is not in this list, caching will be disabled for the pagebrowser links
							$this->pi_isOnlyFields = $pointerName.',tt_news,year,month,day,pS,pL,arc';
							$this->pi_alwaysPrev = $this->conf['pageBrowser.']['alwaysPrev'];
							$markerArray['###BROWSE_LINKS###'] = $this->pi_list_browseresults($this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams'],$wrapArr, $pointerName, $this->conf['pageBrowser.']['hscText']);
						} else {
							$markerArray['###BROWSE_LINKS###'] = $this->makePageBrowser($this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams'],$pointerName,"List");
						}
					}
				}

				// Adds hook for processing of extra global markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraGlobalMarkerHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraGlobalMarkerHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$markerArray = $_procObj->extraGlobalMarkerProcessor($this, $markerArray);
					}
				}
				$content .= $this->cObj->substituteMarkerArrayCached($t['total'], $markerArray, $subpartArray, $wrappedSubpartArray);
			} elseif (ereg('1=0', $where)) {
				// first view of the search page with the parameter 'emptySearchAtStart' set
				$markerArray['###SEARCH_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('searchEmptyMsg'), $this->conf['searchEmptyMsg_stdWrap.']);
				$searchEmptyMsg = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH_EMPTY###'));

				$content .= $this->cObj->substituteMarkerArrayCached($searchEmptyMsg, $markerArray);
			} elseif ($this->piVars['swords']) {
				// no results
				$markerArray['###SEARCH_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('noResultsMsg'), $this->conf['searchEmptyMsg_stdWrap.']);
				$searchEmptyMsg = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH_EMPTY###'));
				$content .= $this->cObj->substituteMarkerArrayCached($searchEmptyMsg, $markerArray);
			} elseif ($theCode == 'XML') {
				// fill at least the template header
				// Init Templateparts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
				$t = array();
				$t['total'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###' . $templateName . '###'));
				// Reset:
				$subpartArray = array();
				$wrappedSubpartArray = array();
				$markerArray = array();
				// header data
				$markerArray = $this->getXmlHeader();
				$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);
				// substitute the xml declaration (it's not included in the subpart ###HEADER###)
				$t['total'] = $this->cObj->substituteMarkerArray($t['total'], array('###XML_DECLARATION###' => $markerArray['###XML_DECLARATION###']));
				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###HEADER###', $subpartArray['###HEADER###'], 0);
				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###CONTENT###', '', 0);

				$content .= $t['total'];
			} elseif ($this->arcExclusive && $this->piVars['pS'] && $GLOBALS['TSFE']->sys_language_content) {
				// this matches if a user has switched languages within a archive period that contains no items in the desired language

				$content .= $this->local_cObj->stdWrap($this->pi_getLL('noNewsForArchPeriod', 'Sorry, there are no translated news-articles in this Archive period'), $this->conf['noNewsToListMsg_stdWrap.']);
			} else {
				$content .= $this->local_cObj->stdWrap($this->pi_getLL('noNewsToListMsg'), $this->conf['noNewsToListMsg_stdWrap.']);
			}
		}
		return $content;
	}

	/**
	 * get the content for a news item NOT displayed as single item (List & Latest)
	 *
	 * @param	array		$itemparts : parts of the html template
	 * @param	array		$selectConf : quety parameters in an array
	 * @param	string		$prefix_display : the part of the TS-setup
	 * @return	string		$itemsOut: itemlist as htmlcode
	 */
	function getListContent($itemparts, $selectConf, $prefix_display) {
		$res = $this->cObj->exec_getQuery('tt_news', $selectConf); //get query for list contents
		$itemsOut = '';
		$itempartsCount = count($itemparts);
		$pTmp = $GLOBALS['TSFE']->ATagParams;
		$cc = 0;
        
        // Getting elements
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            unset($this->conf['pageTypoLink.']["###"] );                
			$wrappedSubpartArray = array();
			$lConf = $this->conf[$prefix_display.'.'];
			$titleField = $lConf['linkTitleField']?$lConf['linkTitleField']:'';

			$GLOBALS['TSFE']->ATagParams = $pTmp.' title="'.$this->local_cObj->stdWrap(trim(htmlspecialchars($row[$titleField])), $lConf['linkTitleField.']).'"';

			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
			}
			$this->categories[$row['uid']] = $this->getCategories($row['uid']);

            
            
                
            
			if ($row['type'] == 1 || $row['type'] == 2) {
                
                
                // News type article or external url
				$this->local_cObj->setCurrentVal($row['type'] == 1 ? $row['page']:$row['ext_url']);
                
                $wrappedSubpartArray['###LINK_ITEM###'] = $this->local_cObj->typolinkWrap($this->conf['pageTypoLink.']);
                // fill the link string in a register to access it from TS
				$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->local_cObj->typolink($this->pi_getLL('more'), $this->conf['pageTypoLink.'])), '');
			} else {
					//  Overwrite the singlePid from config-array with a singlePid given from the first entry in $this->categories
				if ($this->conf['useSPidFromCategory'] && is_array($this->categories)) {
					$tmpcats = $this->categories;
					$catSPid = array_shift($tmpcats[$row['uid']]);
				}
				// MLC use content element config before category
				$singlePid = $this->config['singlePid']?$this->config['singlePid']:$catSPid['single_pid'];
				if ($this->conf['useHRDates'] && !$this->conf['useHRDatesSingle']) {
					$piVarsArray = array(
						'tt_news' => $row['uid'],
						'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid']),
						'year' => ($this->conf['dontUseBackPid']?null:($this->piVars['year']?$this->piVars['year']:null)),
						'month' => ($this->conf['dontUseBackPid']?null:($this->piVars['month']?$this->piVars['month']:null)),
						'pS' => null,
						'pL' => null,
						'arc' => null,
				// << YA; Delete ShowComment GET parametr in LIST view; Jun 2007
				//		"ShowComment" => "",
				// >> YA
						);
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', $piVarsArray, $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid));

					$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->pi_linkTP_keepPIvars($this->pi_getLL('more'), $piVarsArray, $this->allowCaching,($this->conf['dontUseBackPid']?1:0), $singlePid)), '');
				} elseif ($this->conf['useHRDates'] && $this->conf['useHRDatesSingle']) {
					$tmpY = $this->piVars['year'];
					$tmpM = $this->piVars['month'];
					$tmpD = $this->piVars['day'];

					$this->getHrDateSingle($row['datetime']);
					$piVarsArray = array(
						'tt_news' => $row['uid'],
						'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid']),
						'year' => $this->piVars['year'],
						'month' => $this->piVars['month'],
						'day' => ($this->piVars['day']?$this->piVars['day']:null),
						'pS' => null,
						'pL' => null,
						'arc' => null,
						);
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|',$piVarsArray, $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid));

					// fill the link string in a register to access it from TS
					$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->pi_linkTP_keepPIvars($this->pi_getLL('more'), $piVarsArray, $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid)), '');

					$this->piVars['year'] = $tmpY;
					$this->piVars['month'] = $tmpM;
					$this->piVars['day'] = $tmpD;

				} else {
                    $wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => $row['uid'], 'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid));

					// fill the link string in a register to access it from TS
					$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->pi_linkTP_keepPIvars($this->pi_getLL('more'), array('tt_news' => $row['uid'], 'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid)), '');
				}


			}
			$markerArray = $this->getItemMarkerArray($row, $prefix_display);

			// XML
			if ($this->theCode == 'XML') {
				if ($row['type'] == 1 || $row['type'] == 2) {
					$rssUrl = ($row['type'] == 1 ? $this->config['siteUrl'] .$this->pi_getPageLink($row['page'], ''):substr($row['ext_url'], 0, strpos($row['ext_url'], ' '))) ;
				} else {
					$rssUrl = $this->config['siteUrl'] . $this->pi_linkTP_keepPIvars_url(array('tt_news' => $row['uid'], 'backPid' => null), $this->allowCaching, '', $singlePid);

				}
				// replace square brackets [] in links with their URLcodes and replace the &-sign with its ASCII code
				$rssUrl = preg_replace(array('/\[/', '/\]/', '/&/'), array('%5B', '%5D', '&#38;') , $rssUrl);
				$markerArray['###NEWS_LINK###'] = $rssUrl;

				if($this->conf['displayXML.']['xmlFormat'] == 'rdf') {
					$this->rdfToc .= "\t\t\t\t".'<rdf:li resource="'.$rssUrl.'" />'."\n";
				}

			}

			$layoutNum = $cc % $itempartsCount;
			// Store the result of template parsing in the Var $itemsOut, use the alternating layouts
			$itemsOut .= $this->cObj->substituteMarkerArrayCached($itemparts[$layoutNum], $markerArray, array(), $wrappedSubpartArray);
			$cc++;
			if ($cc == $this->config['limit']) {
				break;
			}
		}
		$GLOBALS['TSFE']->ATagParams = $pTmp;

		return $itemsOut;
	}

	/**
	 * build the selectconf (array of query-parameters) to get the news items from the db
	 *
	 * @param	string		$where : where-part of the query
	 * @param	integer		$noPeriod : if this value exists the listing starts with the given 'period start' (pS). If not the value period start needs also a value for 'period lenght' (pL) to display something.
	 * @return	array		the selectconf for the display of a news item
	 */
	function getSelectConf($where, $noPeriod = 0) {
		// Get news
		$selectConf = Array();
		$selectConf['pidInList'] = $this->pid_list;
		// exclude latest from search
		$selectConf['where'] = '1=1 ' . ($this->theCode == 'LATEST'?'':$where);

		if ($this->sys_language_mode == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
			// sys_language_mode == 'strict': If a certain language is requested, select only news-records from the default language which have a translation. The translated articles will be overlayed later in the list or single function.
			$tmpres = $this->cObj->exec_getQuery('tt_news', array('selectFields' => 'tt_news.l18n_parent', 'where' => 'tt_news.sys_language_uid = '.$GLOBALS['TSFE']->sys_language_content.$this->enableFields, 'pidInList' => $this->pid_list));
			$strictUids = array();
			while ($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tmpres)) {
				$strictUids[] = $tmprow['l18n_parent'];
			}
			$strStrictUids = implode(',', $strictUids);
			$selectConf['where'] .= ' AND (tt_news.uid IN (' . ($strStrictUids?$strStrictUids:0) . ') OR tt_news.sys_language_uid=-1)';
		} else {
			// sys_language_mode != 'strict': If a certain language is requested, select only news-records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
			$selectConf['where'] .= ' AND tt_news.sys_language_uid IN (0,-1)';
		}

		if ($this->arcExclusive > 0) {
			if ($this->piVars['arc']) {
				// allow overriding of the arcExclusive parameter from GET vars
				$this->arcExclusive = intval($this->piVars['arc']);
			}
			// select news from a certain period
			if (!$noPeriod && intval($this->piVars['pS'])) {
				$selectConf['where'] .= ' AND tt_news.datetime>=' . intval($this->piVars['pS']);
				if (intval($this->piVars['pL'])) {
					$selectConf['where'] .= ' AND tt_news.datetime<' . (intval($this->piVars['pS']) + intval($this->piVars['pL']));
				}
			}
		}

		if ($this->arcExclusive) {
			if ($this->conf['enableArchiveDate']) {
				if ($this->arcExclusive < 0) {
					// show archived
					$selectConf['where'] .= ' AND (tt_news.archivedate=0 OR tt_news.archivedate>' . $GLOBALS['SIM_EXEC_TIME'] . ')';
				} elseif ($this->arcExclusive > 0) {
					$selectConf['where'] .= ' AND tt_news.archivedate<' . $GLOBALS['SIM_EXEC_TIME'];
				}
			}
			if ($this->config['datetimeDaysToArchive']) {
				$theTime = $GLOBALS['SIM_EXEC_TIME'] - intval($this->config['datetimeDaysToArchive']) * 3600 * 24;
				if ($this->arcExclusive < 0) {
					$selectConf['where'] .= ' AND (tt_news.datetime=0 OR tt_news.datetime>' . $theTime . ')';

				} elseif ($this->arcExclusive > 0) {
					$selectConf['where'] .= ' AND tt_news.datetime<' . $theTime;
				}
			}
		}
		// exclude LATEST and AMENU from changing their contents with the cat selector. This can be overridden by setting the TSvars 'latestWithCatSelector' or 'amenuWithCatSelector'
		if ($this->config['catSelection'] && (($this->theCode == 'LATEST' && $this->conf['latestWithCatSelector']) || ($this->theCode == 'AMENU' && $this->conf['amenuWithCatSelector']) || ($this->theCode == 'LIST' || $this->theCode == 'SEARCH'))) {
			$this->config['categoryMode'] = 1; // force 'select categories' mode if cat is given in GPvars
			$this->catExclusive = $this->config['catSelection']; // override category selection from other news content-elements with the selection from the catselector
		}
		// find newsitems by their categories if categoryMode is '1' or '-1'
		if ($this->conf['oldCatDeselectMode'] && $this->config['categoryMode'] == -1) {
			$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,0) NOT IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
		} elseif ($this->catExclusive) {
			if ($this->config['categoryMode'] == 1) {
				// show items with selected categories
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
				$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,0) IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
			}
			if ($this->config['categoryMode'] == -1) {
				// do not show items with selected categories
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON (tt_news.uid = tt_news_cat_mm.uid_local';
				$selectConf['where'] .= ' AND NOT (tt_news_cat_mm.uid_foreign=';
				if (strstr($this->catExclusive, ',')) {
					// more than one category de-selected
					$selectConf['where'] .= ereg_replace(',', ' OR tt_news_cat_mm.uid_foreign=', $this->catExclusive);
				} else {
					$selectConf['where'] .= $this->catExclusive?$this->catExclusive:0;
				}
				$selectConf['leftjoin'] .= ')';
				$selectConf['where'] .= ') AND (tt_news_cat_mm.uid_foreign)';
			}
		} elseif ($this->config['categoryMode']) {
			// special case: show only non-categized records
			$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,\'nocat\') ' . ($this->config['categoryMode'] > 0?'':'!') . '=\'nocat\')';
		}
		// function Hook for processing the selectConf array
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['selectConfHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['selectConfHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$selectConf = $_procObj->processSelectConfHook($this, $selectConf);
			}
		}
		#debug(array($this->config['categoryMode'],$selectConf,$this->catExclusive),'select_conf');
		return $selectConf;
	}


	/**
	 * Generates an array, $this->pageArray of the pagerecords from $this->pid_list
	 *
	 * @return	void
	 */
	function generatePageArray() {
		// Get pages (for category titles)
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title,uid,author,author_email', 'pages', 'uid IN (' . $this->pid_list . ')');
		$this->pageArray = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->pageArray[$row['uid']] = $row;
		}
	}

	/**
	 * Fills in the markerArray with data for a news item
	 *
	 * @param	array		$row : result row for a news item
	 * @param	array		$textRenderObj : conf vars for the current template
	 * @return	array		$markerArray: filled marker array
	 */
	function getItemMarkerArray ($row, $textRenderObj = 'displaySingle') {
		// get config for current template part:
		$lConf = $this->conf[$textRenderObj . '.'];
		$this->local_cObj->start($row, 'tt_news');

		$markerArray = array();
		// get image markers
		$markerArray = $this->getImageMarkers($markerArray, $row, $lConf, $textRenderObj);

		// find categories for the current record
		if (!is_array($this->categories[$row['uid']])) {
			$this->categories[$row['uid']] = $this->getCategories($row['uid']);
		}

		// get markers and links for categories
		$markerArray = $this->getCatMarkerArray($markerArray, $row, $lConf);

		$markerArray['###NEWS_UID###'] = $row['uid'];
		// show language label and/or flag
		$markerArray['###NEWS_LANGUAGE###'] = '';
		if ($this->conf['showLangLabels']) {
			$L_uid = $row['sys_language_uid'];
			$markerArray['###NEWS_LANGUAGE###'] = $this->langArr[$L_uid]['title'];
		}

		if ($this->langArr[$L_uid]['flag'] && $this->conf['showFlags']) {
			$fImgFile = ($this->conf['flagPath']?$this->conf['flagPath']:'media/uploads/flag_') . $this->langArr[$L_uid]['flag'];
			$fImgConf = $this->conf['flagImage.'];
			$fImgConf['file'] = $fImgFile;
			$flagImg = $this->local_cObj->IMAGE($fImgConf);
			// debug ($fImgConf);
			$markerArray['###NEWS_LANGUAGE###'] .= $flagImg;
		}

		$markerArray['###NEWS_TITLE###'] = $this->local_cObj->stdWrap($row['title'], $lConf['title_stdWrap.']);

		$newsAuthor = $this->local_cObj->stdWrap($row['author']?$this->pi_getLL('preAuthor').' '.$row['author']:'', $lConf['author_stdWrap.']);

// << YA; Insert "Continued from " in the author field; Jun 2007
		if ($textRenderObj == 'displaySingle')
		{
			$arr = $_GET["tx_ttnews"];
			$pname = $this->config['singleViewPointerName'];
			if (isset($arr[$pname]) && $arr[$pname]!=0)
			{
				$lastpage=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				$lastpage = substr ($lastpage, 0 , strpos($lastpage, "?"));
				if ($arr[$pname]>1)
		$str = "<h2>Continued from <span class='tx-ttnews-author-link'>". $this->pi_linkTP_keepPIvars("page ".$arr[$pname],array($pname=>($arr[$pname]-1)),$this->allowCaching)."</span></h2>";
		else
$str = "<h2>Continued from <span class='tx-ttnews-author-link'>".$this->pi_linkTP_keepPIvars("page ".$arr[$pname],array($pname=>""),$this->allowCaching)."</span></h2>";
				$markerArray['###NEWS_AUTHOR###'] = $this->formatStr($str);
			}
			else
				$markerArray['###NEWS_AUTHOR###'] = $this->formatStr($newsAuthor);
		}
		else
		{
			$markerArray['###NEWS_AUTHOR###'] = $this->formatStr($newsAuthor);
		}
// >> YA

		$markerArray['###NEWS_EMAIL###'] = $this->local_cObj->stdWrap($row['author_email'], $lConf['email_stdWrap.']);
		$markerArray['###NEWS_DATE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['date_stdWrap.']);
		$markerArray['###NEWS_TIME###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['time_stdWrap.']);
		$markerArray['###NEWS_AGE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['age_stdWrap.']);
		$markerArray['###TEXT_NEWS_AGE###'] = $this->local_cObj->stdWrap($this->pi_getLL('textNewsAge'), $lConf['textNewsAge_stdWrap.']);

		if ($this->config['croppingLenght']) {
			$lConf['subheader_stdWrap.']['crop'] = $this->config['croppingLenght'];
		}
		$markerArray['###NEWS_SUBHEADER###'] = '';
		if (!$this->piVars[$this->config['singleViewPointerName']] || $this->conf['subheaderOnAllSViewPages']) {
			$markerArray['###NEWS_SUBHEADER###'] = $this->formatStr($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));
		}

		$markerArray['###NEWS_KEYWORDS###'] = $this->local_cObj->stdWrap($row['keywords'], $lConf['keywords_stdWrap.']);

		if (!$this->piVars[$this->config['singleViewPointerName']]) {
			if ($textRenderObj == 'displaySingle') {
				// load the keywords the register 'newsKeywords' to access it from TS
				$this->local_cObj->LOAD_REGISTER(array(
					'newsKeywords' => $row['keywords'],
					'newsSubheader' => $row['short']
				), '');
			}
		}
//<< YA			
//		if($this->config['maxCharsInSingleView'] > 1) {
//			$this->config['maxWordsInSingleView'] = 0;
//		}
//>> YA			

		if ($textRenderObj == 'displaySingle' && !$row['no_auto_pb'] && $this->config['maxCharsInSingleView']>1) {
			
			if (!isset($_GET['All']))
			$row['bodytext'] = $this->insertPagebreaksByChar($row['bodytext'],count(t3lib_div::trimExplode(' ',$row['short'],1)));
/*			else 
			{
				if (!isset($_GET['f']))
				{
					$Location = "Location: http://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]."&tx_ttnews[ShowComment]=1&f";
					header($Location);
				}
			}*/
			
//			$row['bodytext'] = $this->insertPagebreaks($row['bodytext'],count(t3lib_div::trimExplode(' ',$row['short'],1)));
		}
//<< YA		
//		elseif($this->config['maxCharsInSingleView'] > 1) {
//			$row['bodytext'] = $this->insertPagebreaksByChar($row['bodytext'],count(t3lib_div::trimExplode(' ',$row['short'],1)));
//			echo "1!!!!!!!!1";
//		}
//>> YA	
		if (strpos($row['bodytext'],$this->config['pageBreakToken'])) {
			if ($this->config['useMultiPageSingleView'] && $textRenderObj == 'displaySingle') {
				$tmp = $this->makeMultiPageSView($row['bodytext'],$lConf);
				$newscontent = $tmp[0];
				$sViewPagebrowser = $tmp[1];
			} else {
				$newscontent = $this->formatStr($this->local_cObj->stdWrap(preg_replace('/'.$this->config['pageBreakToken'].'/','',$row['bodytext']), $lConf['content_stdWrap.']));
			}
		} else {
			$newscontent = $this->formatStr($this->local_cObj->stdWrap($row['bodytext'], $lConf['content_stdWrap.']));
		}
		if ($this->conf['appendSViewPBtoContent']) {
			$newscontent = $newscontent.$sViewPagebrowser;
			$sViewPagebrowser = '';
		}
		$markerArray['###NEWS_CONTENT###'] = $newscontent;
		$markerArray['###NEWS_SINGLE_PAGEBROWSER###'] = $sViewPagebrowser;


		$markerArray['###MORE###'] = $this->pi_getLL('more');
		// get title (or its language overlay) of the page where the backLink points to (this is done only in single view)
		if ($this->config['backPid'] && $textRenderObj == 'displaySingle') {
			if ($GLOBALS['TSFE']->sys_language_content) {
				$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages_language_overlay', '1=1 AND pid=' . $this->config['backPid'] . ' AND  sys_language_uid=' . $GLOBALS['TSFE']->sys_language_content);
				$backP = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($p_res);
			} else {
				$backP = $this->pi_getRecord('pages', $this->config['backPid']);
			}
		}
		// generate the string for the backLink. By setting the conf-parameter 'hscBackLink',
		// you can switch whether the string is parsed through htmlspecialchars() or not.
		$markerArray['###BACK_TO_LIST###'] = sprintf($this->pi_getLL('backToList', '', $this->conf['hscBackLink']), $backP['title']);
		// get related news
		if ($textRenderObj == 'displaySingle') {
			$relatedNews = $this->getRelated($row['uid']);
		}
		$markerArray['###TEXT_RELATED###'] = '';
		$markerArray['###NEWS_RELATED###'] = '';
		if ($relatedNews) {
			$rel_stdWrap = t3lib_div::trimExplode('|', $this->conf['related_stdWrap.']['wrap']);
			$markerArray['###TEXT_RELATED###'] = $rel_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textRelated'), $this->conf['relatedHeader_stdWrap.']);

			$markerArray['###NEWS_RELATED###'] = $relatedNews.$rel_stdWrap[1];
		}

		// Links
		$markerArray['###TEXT_LINKS###'] = '';
		$markerArray['###NEWS_LINKS###'] = '';
		if ($row['links']) {
			$links_stdWrap = t3lib_div::trimExplode('|', $lConf['links_stdWrap.']['wrap']);
			$newsLinks = $this->local_cObj->stdWrap($this->formatStr($row['links']), $lConf['linksItem_stdWrap.']);
			$markerArray['###TEXT_LINKS###'] = $links_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textLinks'), $lConf['linksHeader_stdWrap.']);
			$markerArray['###NEWS_LINKS###'] = $newsLinks.$links_stdWrap[1];
		}

		// filelinks
		$markerArray['###TEXT_FILES###'] = '';
		$markerArray['###FILE_LINK###'] = '';
		if ($row['news_files']) {
			$files_stdWrap = t3lib_div::trimExplode('|', $this->conf['newsFiles_stdWrap.']['wrap']);
			$markerArray['###TEXT_FILES###'] = $files_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textFiles'), $this->conf['newsFilesHeader_stdWrap.']);
			$fileArr = explode(',', $row['news_files']);
			$files = '';
			while (list(, $val) = each($fileArr)) {
				// fills the marker ###FILE_LINK### with the links to the atached files
				$filelinks .= $this->local_cObj->filelink($val, $this->conf['newsFiles.']) ;
			}
			$markerArray['###FILE_LINK###'] = $filelinks.$files_stdWrap[1];
		}

		// show news with the same categories in SINGLE view
		if ($textRenderObj == 'displaySingle' && $this->conf['showRelatedNewsByCategory'] && count($this->categories[$row['uid']])) {
			$tmpcatExclusive = $this->catExclusive;
			$tmpcode = $this->theCode;
			if(is_array($this->categories[$row['uid']])) {
				$this->catExclusive = implode(array_keys($this->categories[$row['uid']]),',');
			}
			$this->config['categoryMode'] = 1;
			$this->theCode = 'LIST';
			$relNewsByCat = trim($this->displayList($row['uid']));
			$this->theCode = $tmpcode;
			$this->catExclusive = $tmpcatExclusive;

		}
		$markerArray['###NEWS_RELATEDBYCATEGORY###'] = '';
		$markerArray['###TEXT_RELATEDBYCATEGORY###'] = '';
		if ($this->conf['showRelatedNewsByCategory'] && $relNewsByCat) {
			$cat_rel_stdWrap = t3lib_div::trimExplode('|', $this->conf['relatedByCategory_stdWrap.']['wrap']);
			$markerArray['###TEXT_RELATEDBYCATEGORY###'] = $cat_rel_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textRelatedByCategory'), $this->conf['relatedByCategoryHeader_stdWrap.']);
			$markerArray['###NEWS_RELATEDBYCATEGORY###'] = $relNewsByCat.$cat_rel_stdWrap[1];
		}

		// the both markers: ###ADDINFO_WRAP_B### and ###ADDINFO_WRAP_E### are only inserted, if there are any files, related news or links
		$markerArray['###ADDINFO_WRAP_B###'] = '';
		$markerArray['###ADDINFO_WRAP_E###'] = '';
		if ($relatedNews || $row['links'] || $row['news_files'] || $relNewsByCat) {
			$addInfo_stdWrap = t3lib_div::trimExplode('|', $lConf['addInfo_stdWrap.']['wrap']);
			$markerArray['###ADDINFO_WRAP_B###'] = $addInfo_stdWrap[0];
			$markerArray['###ADDINFO_WRAP_E###'] = $addInfo_stdWrap[1];
		}



		// Page fields:
		$markerArray['###PAGE_UID###'] = $row['pid'];
		$markerArray['###PAGE_TITLE###'] = $this->pageArray[$row['pid']]['title'];
		$markerArray['###PAGE_AUTHOR###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['author'], $lConf['author_stdWrap.']);
		$markerArray['###PAGE_AUTHOR_EMAIL###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['author_email'], $lConf['email_stdWrap.']);
		// XML
		if ($this->theCode == 'XML') {
			$markerArray['###NEWS_TITLE###'] = $this->cleanXML($this->local_cObj->stdWrap($row['title'], $lConf['title_stdWrap.']));
			$markerArray['###NEWS_AUTHOR###'] = $row['author_email']?'<author>'.$row['author_email'].'</author>':'';
			if($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
				$markerArray['###NEWS_AUTHOR###'] =	$row['author'];
			}

			if($this->conf['displayXML.']['xmlFormat'] == 'rss2' ||
				$this->conf['displayXML.']['xmlFormat'] == 'rss091') {
				$markerArray['###NEWS_SUBHEADER###'] = $this->cleanXML($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));
			} elseif ($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
				//html doesn't need to be striped off in atom feeds
				$lConf['subheader_stdWrap.']['stripHtml'] = 0;
				$markerArray['###NEWS_SUBHEADER###'] = $this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']);
				//just removing some whitespace to ease atom feed building
				$markerArray['###NEWS_SUBHEADER###'] = str_replace('\n', '', $markerArray['###NEWS_SUBHEADER###']);
				$markerArray['###NEWS_SUBHEADER###'] = str_replace('\r', '', $markerArray['###NEWS_SUBHEADER###']);
			}

			if($this->conf['displayXML.']['xmlFormat'] == 'rss2' ||
				$this->conf['displayXML.']['xmlFormat'] == 'rss091') {
				$markerArray['###NEWS_DATE###'] = date('D, d M Y H:i:s O', $row['datetime']);
			} elseif ($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
				$markerArray['###NEWS_DATE###'] = $this->getW3cDate($row['datetime']);
			}
			//dates for atom03
			$markerArray['###NEWS_CREATED###'] = $this->getW3cDate($row['crdate']);
			$markerArray['###NEWS_MODIFIED###'] = $this->getW3cDate($row['tstamp']);

			if($this->conf['displayXML.']['xmlFormat'] == 'atom03' && !empty($this->conf['displayXML.']['xmlLang'])) {
				$markerArray['###SITE_LANG###'] = ' xml:lang="'.$this->conf['displayXML.']['xmlLang'].'"';
			}

			$markerArray['###NEWS_ATOM_ENTRY_ID###'] = 'tag:'.substr($this->config['siteUrl'], 11, -1).','.date('Y', $row['crdate']).':article'.$row['uid'];
			$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];

		}

		// Adds hook for processing of extra item markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'] as $_classRef) {
                $_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraItemMarkerProcessor($markerArray, $row, $lConf, $this);
			}
		}
		// Pass to user defined function
		if ($this->conf['itemMarkerArrayFunc']) {
			$markerArray = $this->userProcess('itemMarkerArrayFunc', $markerArray);
		}

		return $markerArray;
	}
	/**
	 * inserts pagebreaks after a certain amount of words
	 *
	 * @param	string		text which can contain manully inserted 'pageBreakTokens'
	 * @param	integer		amount of words in the subheader (short). The lenght of the first page will be reduced by that amount of words added to the value of $this->conf['cropWordsFromFirstPage'].
	 * @return	string		the processed text
	 */
	function insertPagebreaks($text,$firstPageWordCrop) {
		$paragraphs = explode(chr(10), $text); // get paragraphs
 		$wtmp = array();
		$firstPageCrop = $firstPageWordCrop+intval($this->conf['cropWordsFromFirstPage']);
		$cc = 0; // wordcount
		$isfirst = true; // first paragraph
		while (list($k,$p) = each ($paragraphs))	{
			$words = explode(' ', $p); // get words
			$pArr = array();
			$break = false;
			foreach ($words as $w) {
			#if (trim($w)=='&nbsp;') debug ($w);
				if (strpos($w,$this->config['pageBreakToken'])) { // manually inserted pagebreaks
					$cc = 0;
					$pArr[] = $w;
					$isfirst = false;
				} elseif ($cc >= t3lib_div::intInRange($this->config['maxWordsInSingleView']-($isfirst && !$this->conf['subheaderOnAllSViewPages'] ? $firstPageCrop:0),0,$this->config['maxWordsInSingleView'])) {
					if (trim($paragraphs[$k+1])=='&nbsp;') unset($paragraphs[$k+1]);

					if (!$this->conf['useParagraphAsPagebreak'] && substr($w,-1)=='.') { // break at dot
   					   $pArr[] = $w.$this->config['pageBreakToken'];
					} else { // break at paragraph
						$break = true;
						$pArr[] = $w;
						#$pArr[] = '<b> '.$cc.' </b>';
					}
					$cc = 0;
					$isfirst = false;
				} else {
					$pArr[] = $w;
				}
				$cc++;
			}
			if ($break) { // add break at end of current paragraph
				array_push ($pArr, $this->config['pageBreakToken']);
			}
			$wtmp[] = implode($pArr,' ');
		}
		$processedText = implode($wtmp,chr(10));
		return $processedText;
	}

// << YA; PagebreaksByChar; Jun 2007
	
	function insertPagebreaksByChar($text,$firstPageCharCrop) {
		//$text = str_replace("\n", "<br /><br />", $text);
		$text = str_replace("<p></p>", "<br/><br/>", $text);
		$paragraphs[] = $text;
		$CountOfManBr = 0;
		$CountOfManInsBr = 0;
 		$wtmp = array();
		$firstPageCrop = $firstPageCharCrop+intval($this->conf['cropCharFromFirstPage']);
		
		$cc = 0; // charcount
		$isfirst = true; // first paragraph
		$acount = 0;
		$acountneed = 0;
		
		$Ctag = new s_tag();
		$tags = array();			
		
		while (list($k,$p) = each ($paragraphs))	{
			$pArr = '';
			if (strpos($p,$this->config['pageBreakToken'])) { // manually inserted pagebreaks
				$pArr .= $p;
				$isfirst = false;
				$CountOfManInsBr++;
			}

			for ($i = 0; $i < strlen($p); $i++) {
				$c = $p[$i];

				$Ctag->setPos($i);
				if ($c == '<')
				{
					if ($p[$i+1]=='/')
					{				
						array_pop($tags);
						if ($acountneed == 1) $acountneed = 0;
					}
					else 
					{
						if ($p[$i+1]=='b' && $p[$i+2]=='>')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='s' && $p[$i+2]=='p')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='l' && $p[$i+2]=='i')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='p')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='e' && $p[$i+2]=='m')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='s' && $p[$i+2]=='t')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='i' && $p[$i+2]!='m')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='o' && $p[$i+2]!='l')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='u' && $p[$i+2]!='l')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='h')
							$Ctag->setIsCloseTagNeed(1);
						elseif ($p[$i+1]=='a')
						{
							$Ctag->setIsCloseTagNeed(1);
							$acountneed = 1;
						}
						else
							$Ctag->setIsCloseTagNeed(0);
						array_push($tags,$Ctag);
					}
				}
				if ($acountneed == 1) $acount++;
				if ($c == '>')
				{		
					if (count($tags)!=0)		
					if ($tags[count($tags)-1]->getIsCloseTagNeed() == 0)
						array_pop($tags);
				}
				if ($cc >= $acount+t3lib_div::intInRange($this->config['maxCharsInSingleView']-($isfirst && !$this->conf['subheaderOnAllSViewPages'] ? $firstPageCrop:0),0,$this->config['maxCharsInSingleView'])) {
					//if (trim($paragraphs[$k+1])=='&nbsp;') unset($paragraphs[$k+1]);
				if (($p[$i+1]=='<' && 
						$p[$i+2]=='b' &&
						$p[$i+3]=='r' &&
						$p[$i+4]==' ' &&
						$p[$i+5]=='/' &&
						$p[$i+6]=='>' &&
						$p[$i+7]=='<' && 
						$p[$i+8]=='b' &&
						$p[$i+9]=='r' &&
						$p[$i+10]==' ' &&
						$p[$i+11]=='/' &&
						$p[$i+12]=='>')
					 &&
						strlen($p)-$i > 100 ) 
					{
						$pArr .= $c;
						// break at BR's
						$i = $i + 12;
						$c = $p[$i];
						if (count($tags)==0)
						{
							//$pArr .= $c." ...".$this->config['pageBreakToken'];
							//$pArr .= $c.$this->config['pageBreakToken'];
							$pArr .= $this->config['pageBreakToken'];
							 //print("<br><br>");
							 //print_r($tags);//print tags

						}
						else
						{
							 //print("<br><br>");  
							 //print_r($tags);		//print tags
							//$pArr .= $c;
							$temp = "";
							//$temp = substr($pArr,0,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']) + 4)*$CountOfManBr));
							$temp = substr($pArr,0,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							//$temp = $temp." ...";
							$temp .= $this->config['pageBreakToken'];
							$temp .= substr($pArr,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							$temp .= substr($pArr,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							$pArr = $temp;	
							while (count($tags[0]))array_pop($tags); 						
						}
						$CountOfManBr++;
						$cc = 0;
						$acount = 0;
						$acountneed = 0;						
   					   
					} 
					elseif ($p[$i+1]==chr(10) && strlen($p)-$i > 100 )// break at chr_10
					{
						if (count($tags)==0)
						{
							$pArr .= $this->config['pageBreakToken'];
						}
						else
						{
							$temp = "";
							$temp = substr($pArr,0,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							//$temp = $temp." ...";
							$temp .= $this->config['pageBreakToken'];
							$temp .= substr($pArr,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							$temp .= substr($pArr,($tags[0]->getPos()+(strlen($this->config['pageBreakToken']))*$CountOfManBr));
							$pArr = $temp;	
							while (count($tags[0]))array_pop($tags); 						
						}
						$CountOfManBr++;
						$cc = 0;
						$acount = 0;
						$acountneed = 0;
					} 
					else 
					{	
						$pArr .= $c;
					}
					$isfirst = false;
				} else {
					$pArr .= $c;
				}
				$cc++;
			}
			$wtmp[] = $pArr;
		}
		$arr = $_GET["tx_ttnews"];
/*		if ($CountOfManBr == 0 && $CountOfManInsBr == 0 && !isset($arr['ShowComment']))
		{
			$Location = "Location: http://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]."?tx_ttnews[ShowComment]=1";
			header($Location);
		}*/
			
		$CountOfManBr = $CountOfManBr + $CountOfManInsBr;
		if (isset($_GET["Last"]))
		{
			$arr[$this->config['singleViewPointerName']] = $CountOfManBr;
			$this->piVars = $arr;
			$out = $this->pi_linkTP_keepPIvars_url();
			$Location = "Location: ".t3lib_div::locationHeaderUrl($out);
			header($Location);
		}
		
// Adding the First parameter		
		if (isset($_GET["First"]))
		{
			$arr[$this->config['singleViewPointerName']] = null;
			$this->piVars = $arr;
			$out = $this->pi_linkTP_keepPIvars_url();
			$Location = "Location: ".t3lib_div::locationHeaderUrl($out);
			header($Location);
		}
		
		 //print_r($wtmp); //print all text
		$processedText = implode($wtmp,chr(10));
		//print_r($processedText);
		return $processedText;
	}
		
// >> YA;
	
	/**
	 * divides the bodytext field of a news single view to pages and returns the part of the bodytext
	 * that is choosen by piVars[$pointerName]
	 *
	 * @param	string		the text with 'pageBreakTokens' in it
	 * @param	array		config array for the single view
	 * @return	string		the current bodytext part wrapped with stdWrap
	 */
	function makeMultiPageSView($bodytext,$lConf) {
		$pointerName=$this->config['singleViewPointerName'];
		$pagenum = $this->piVars[$pointerName]?$this->piVars[$pointerName]:0;
		$textArr = t3lib_div::trimExplode($this->config['pageBreakToken'],$bodytext,1);
		$pagecount = count($textArr);
		// render a pagebrowser for the single view
		if ($pagecount > 1) {
			// configure pagebrowser vars
			$this->internal['res_count'] = $pagecount;
			$this->internal['results_at_a_time'] = 1;
			$this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'];
			if (!$this->conf['pageBrowser.']['showPBrowserText']) {
				$this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
			}
			$pagebrowser = $this->makePageBrowser(0, $this->conf['pageBrowser.']['tableParams'],$pointerName);
		}
		return array($this->formatStr($this->local_cObj->stdWrap($textArr[$pagenum], $lConf['content_stdWrap.'])),$pagebrowser);
	}

	/**
	 * this is a copy of the function pi_list_browseresults from class.tslib_piBase.php
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "$pointerName" will be pointing to the "result page" to show.
	 * Using $this->piVars['$pointerName'] as pointer to the page to display
	 * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 *
	 * @param	boolean		If set (default) the text "Displaying results..." will be show, otherwise not.
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 * @param	string		varname for the pointer
	 * @return	string		Output HTML, wrapped in <div>-tags with a class attribute
	 */
	function makePageBrowser($showResultCount=1,$tableParams='',$pointerName='pointer',$typeview='Single') {
  		if ($this->conf['useHRDates']) {
			$tmpPS = $this->piVars['pS'];
			unset($this->piVars['pS']);
			$tmpPL = $this->piVars['pL'];
			unset($this->piVars['pL']);
		}

			// Initializing variables:
		$pointer=$this->piVars[$pointerName];
		$count=$this->internal['res_count'];
		$results_at_a_time = t3lib_div::intInRange($this->internal['results_at_a_time'],1,1000);
		$maxPages = t3lib_div::intInRange($this->internal['maxPages'],1,100);
		$max = t3lib_div::intInRange(ceil($count/$results_at_a_time),1,$maxPages);
		$pointer=intval($pointer);
		$links=array();

			// Make browse-table/links:
		if ($this->pi_alwaysPrev>=0)	{
			if ($pointer>0)	{
				$links[]='
					<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev','Previous'),array($pointerName=>($pointer-1?$pointer-1:'')),$this->allowCaching).'&nbsp</p></td>';
			} elseif ($this->pi_alwaysPrev)	{
				$links[]='
					<td nowrap="nowrap"><p>'.$this->pi_getLL('pi_list_browseresults_prev','Previous').'&nbsp</p></td>';
			}
		}
		$links[]='<td'.$this->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p><span>Page</span></p></td>';

// << YA matched page links for pagebreaks; Dec 2006
		for($a=0;$a<$max;$a++)	{
		if ($a == 0)
		{
				if ($pointer==$a)
				{
				$links[]='
						<td'.($pointer==$a?$this->pi_classParam('browsebox-SCell'):'').' nowrap="nowrap"><p><span>'.trim($this->pi_getLL('pi_list_browseresults_page','Page').' '.($a+1)).
					'</span></p></td>';	
				}
				else
				{
					$links[]='
						<td'.($pointer==$a?$this->pi_classParam('browsebox-SCell'):'').' nowrap="nowrap"><p>'.
					$this->pi_linkTP_keepPIvars(trim($this->pi_getLL('pi_list_browseresults_page','Page').' '.($a+1)),array($pointerName=>($a?$a:'')),$this->allowCaching).
					'</p></td>';
				}
		}
		else
		{
			$links[]='<td'.$this->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p><span>|</span></p></td>';
				if ($pointer==$a)
				{
				$links[]='
						<td'.($pointer==$a?$this->pi_classParam('browsebox-SCell'):'').' nowrap="nowrap"><p><span>'.trim($this->pi_getLL('pi_list_browseresults_page','Page').' '.($a+1)).
					'</span></p></td>';	
				}
				else
				{
					$links[]='
						<td'.($pointer==$a?$this->pi_classParam('browsebox-SCell'):'').' nowrap="nowrap"><p>'.
					$this->pi_linkTP_keepPIvars(trim($this->pi_getLL('pi_list_browseresults_page','Page').' '.($a+1)),array($pointerName=>($a?$a:'')),$this->allowCaching).
					'</p></td>';
				}
		}	
		} //end for

		if ($pointer<ceil($count/$results_at_a_time)-1)	{

				$links[]='
					<td nowrap="nowrap"><p>&nbsp'.
				$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next','Next'),array($pointerName=>$pointer+1),$this->allowCaching).
				'</p></td>';	
		}
// >> YA

		$pR1 = $pointer*$results_at_a_time+1;
		$pR2 = $pointer*$results_at_a_time+$results_at_a_time;
		$sTables = '

		<!--
			List browsing box:
		-->
		<div'.$this->pi_classParam('browsebox').'>'.
			($showResultCount ? '
			<p>'.
				($this->internal['res_count'] ?
    			sprintf(
					str_replace('###SPAN_BEGIN###','<span'.$this->pi_classParam('browsebox-strong').'>',$this->pi_getLL('pi_list_browseresults_displays','Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),
					$this->internal['res_count'] > 0 ? $pR1 : 0,
					min(array($this->internal['res_count'],$pR2)),
					$this->internal['res_count']
				) :
				$this->pi_getLL('pi_list_browseresults_noResults','Sorry, no items were found.')).'</p>':''
			).'

			<'.trim('table '.$tableParams).'>
				<tr>
					'.implode('',$links).'
				</tr>
			</table>
		</div>';
		if ($this->conf['useHRDates']) {
			if ($tmpPS) $this->piVars['pS'] = $tmpPS;
			if ($tmpPL) $this->piVars['pL'] = $tmpPL;
		}

		return $sTables;
	}

	/**
	 * gets categories and subcategories for a news record
	 *
	 * @param	integer		$uid : uid of the current news record
	 * @return	array		$categories: array of found categories
	 */
	function getCategories($uid, $getAll=false) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query ('tt_news_cat.*,tt_news_cat_mm.sorting AS mmsorting', 'tt_news', 'tt_news_cat_mm', 'tt_news_cat', $whereClause = ' AND tt_news_cat_mm.uid_local='.($uid?$uid:0).$this->SPaddWhere.($getAll?' AND tt_news_cat.deleted=0':$this->enableCatFields), $groupBy = '', ($this->config['catOrderBy']&&$this->config['catOrderBy']!='sorting'?$this->config['catOrderBy']:'mmsorting'), $limit = '');

		$categories = array();
		$maincat = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			$maincat .= ','.$row['uid'];
			$row = array($row);
			if ($this->conf['displaySubCategories'] && $this->conf['useSubCategories']) {
				$subCategories = array();
				$subcats = implode(',', array_unique(explode(',', $this->getSubCategories($row[0]['uid']))));
				$subres = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('tt_news_cat.*', 'tt_news_cat', 'tt_news_cat.uid IN ('.($subcats?$subcats:0).')'.$this->SPaddWhere.$this->enableCatFields, $groupBy = '', 'tt_news_cat.'.$this->config['catOrderBy'], $limit = '');
				while ($subrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($subres)) {
					$subCategories[] = $subrow;
				}
				$row = array_merge($row, $subCategories);
			}

			while (list (, $val) = each ($row)) {
				$catTitle = '';
				if ($GLOBALS['TSFE']->sys_language_content) {
					// find translations of category titles
					$catTitleArr = t3lib_div::trimExplode('|', $val['title_lang_ol']);
					$catTitle = $catTitleArr[($GLOBALS['TSFE']->sys_language_content-1)];
				}
				$catTitle = $catTitle?$catTitle:$val['title'];

				$categories[$val['uid']] = array(
				'title' => $catTitle,
					'image' => $val['image'],
					'shortcut' => $val['shortcut'],
					'shortcut_target' => $val['shortcut_target'],
					'single_pid' => $val['single_pid'],
					'catid' => $val['uid'],
					'parent_category' => (!t3lib_div::inList($maincat,$val['uid']) && $this->conf['displaySubCategories']?$val['parent_category']:''),
					'sorting' => $val['sorting'],
					'mmsorting' => $val['mmsorting'],
				);
			}
		}
		return $categories;
	}

	/**
	 * extends a given list of categories by their subcategories
	 *
	 * @param	string		$catlist: list of categories which will be extended by subcategories
	 * @param	integer		$cc: counter to detect recursion in nested categories
	 * @return	string		extended $catlist
	 */
	function getSubCategories($catlist, $cc = 0) {
		$pcatArr = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_news_cat', 'tt_news_cat.parent_category IN ('.$catlist.')'.$this->SPaddWhere.$this->enableCatFields);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$cc++;
			if ($cc > 100) {
				$GLOBALS['TT']->setTSlogMessage('tt_news: one or more recursive categories where found');
				return implode(',', $pcatArr);
			}
			$subcats = $this->getSubCategories($row['uid'], $cc);
			$subcats = $subcats?','.$subcats:'';
			$pcatArr[] = $row['uid'].$subcats;
		}
		$catlist = implode(',', $pcatArr);
		return $catlist;
	}


	/**
	 * Displays a hirarchical menu from tt_news categories
	 *
	 * @return	string		html for the category menu
	 */
	function displayCatMenu() {
		$lConf = $this->conf['displayCatMenu.'];
		$mode = $lConf['mode']?$lConf['mode']:'tree';
		switch ($mode) {
			case 'nestedWraps';
				$orderBy = 'sorting';
				$fields = '*';
				$lConf = $this->conf['displayCatMenu.'];
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'tt_news_cat', 'parent_category=0' .$this->SPaddWhere. $this->enableCatFields,'',$this->config['catOrderBy']);
				$cArr = array();
				$cArr[] = $this->local_cObj->stdWrap($this->pi_getLL('catmenuHeader','Select a category:'),$lConf['catmenuHeader_stdWrap.']);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$cArr[] = $row;
					$subcats = $this->getSubCategoriesForMenu($row['uid'],$fields);
					if (count($subcats))	{
						$cArr[] = $subcats;
					}
				}
				$content = $this->getCatMenuContent($cArr,$lConf);
			break;
			case 'tree':
				include_once(t3lib_extMgm::extPath('tt_news').'class.tx_ttnews_catmenu.php');
				$treeViewObj = t3lib_div::makeInstance('tx_ttnews_catmenu');

				$treeViewObj->table = 'tt_news_cat';
				$treeViewObj->init($this->SPaddWhere.$this->enableCatFields, $this->config['catOrderBy']);
				$treeViewObj->backPath = 't3lib/';
				$treeViewObj->parentField = 'parent_category';
				$treeViewObj->expandAll = 1;
				$treeViewObj->expandFirst = 1;
				$treeViewObj->fieldArray = array('uid','title','title_lang_ol','description','image'); // those fields will be filled to the array $treeViewObj->tree
				$treeViewObj->ext_IconMode = '1'; // no context menu on icons
				$treeViewObj->title = $this->pi_getLL('catmenuHeader','Select a category:');
				$treeViewObj->getTree(0);
				$treeViewObj->tt_news_obj = &$this;

				$content = $treeViewObj->getBrowsableTree();
			break;
			default:
				// hook for user catmenu
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['userDisplayCatmenuHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['userDisplayCatmenuHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$content = $_procObj->userDisplayCatmenu($lConf, $this);
					}
				}
			break;
		}
		return $this->local_cObj->stdWrap($content, $lConf['catmenu_stdWrap.']);
	}

	/**
	 * This function calls itself recursively to convert the nested category array to HTML
	 *
	 * @param	array		$array_in: the nested categories
	 * @param	array		$lConf: TS configuration
	 * @param	integer		$l: level counter
	 * @return	string		HTML for the category menu
	 */
	function getCatMenuContent($array_in,$lConf, $l=0) {
		$titlefield = 'title';
		if (is_array($array_in))	{
			$result = '';
			while (list($key,$val)=each($array_in))	{
				if ($key == $titlefield||is_array($array_in[$key])) {
					if ($l) {
						$catmenuLevel_stdWrap = explode('|||',$this->local_cObj->stdWrap('|||',$lConf['catmenuLevel'.$l.'_stdWrap.']));
						$result.= $catmenuLevel_stdWrap[0];
					}
					if (is_array($array_in[$key]))	{
						$result.=$this->getCatMenuContent($array_in[$key],$lConf,$l+1);
					} elseif ($key == $titlefield) {
						if ($GLOBALS['TSFE']->sys_language_content && $array_in['uid']) {
							// get translations of category titles
							$catTitleArr = t3lib_div::trimExplode('|', $array_in['title_lang_ol']);
							$syslang = $GLOBALS['TSFE']->sys_language_content-1;
							$val = $catTitleArr[$syslang]?$catTitleArr[$syslang]:$val;
						}
						if (!$title) $title = $val;
						$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
						$pTmp = $GLOBALS['TSFE']->ATagParams;
						if ($this->conf['displayCatMenu.']['insertDescrAsTitle']) {
							$GLOBALS['TSFE']->ATagParams = ($pTmp?$pTmp.' ':'').'title="'.$array_in['description'].'" alt="'.$array_in['description'].'"';
						}
						if ($array_in['uid']) {
							if ($this->piVars['cat']==$array_in['uid']) {
								$result.= $this->local_cObj->stdWrap($this->pi_linkTP_keepPIvars($val, array('cat' => $array_in['uid']), $this->allowCaching, 1, $catSelLinkParams),$lConf['catmenuItem_ACT_stdWrap.']);
							} else {
								$result.= $this->local_cObj->stdWrap($this->pi_linkTP_keepPIvars($val, array('cat' => $array_in['uid']), $this->allowCaching, 1, $catSelLinkParams),$lConf['catmenuItem_NO_stdWrap.']);
							}
						} else {
							$result.= $this->pi_linkTP_keepPIvars($val, array(), $this->allowCaching, 1, $catSelLinkParams);
						}
						$GLOBALS['TSFE']->ATagParams = $pTmp;
					}
					if ($l) { $result.= $catmenuLevel_stdWrap[1]; }
				}
			}
		}
		return $result;
	}

	/**
	 * extends a given list of categories by their subcategories. This function returns a nested array with subcategories (the function getSubCategories() return only a commaseparated list of category UIDs)
	 *
	 * @param	string		$catlist: list of categories which will be extended by subcategories
	 * @param	string		$fields: list of fields for the query
	 * @param	integer		$cc: counter to detect recursion in nested categories
	 * @return	array		all categories in a nested array
	 */
	function getSubCategoriesForMenu ($catlist, $fields, $cc = 0) {
		$pcatArr = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'tt_news_cat', 'tt_news_cat.parent_category IN ('.$catlist.')'.$this->SPaddWhere.$this->enableCatFields);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$cc++;
			if ($cc > 50) {
				$GLOBALS['TT']->setTSlogMessage('tt_news: one or more recursive categories where found');
				return $pcatArr;
			}
			$subcats = $this->getSubCategoriesForMenu($row['uid'], $fields, $cc);
			$pcatArr[] = is_array($subcats)?array_merge($row,$subcats):'';
		}
		return $pcatArr;
	}

	/**
	 * Fills in the Category markerArray with data
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a news item
	 * @param	array		$lConf : configuration for the current templatepart
	 * @return	array		$markerArray: filled markerarray
	 */
	function getCatMarkerArray($markerArray, $row, $lConf) {
		// clear the category text and image markers if the news item has no categories
		$markerArray['###NEWS_CATEGORY_IMAGE###'] = '';
		$markerArray['###NEWS_CATEGORY###'] = '';
		$markerArray['###TEXT_CAT###'] = '';
		$markerArray['###TEXT_CAT_LATEST###'] = '';
		$markerArray['###CATWRAP_B###'] = '';
		$markerArray['###CATWRAP_E###'] = '';

		if (count($this->categories[$row['uid']]) && ($this->config['catImageMode'] || $this->config['catTextMode'])) {
			// wrap for all categories
			$cat_stdWrap = t3lib_div::trimExplode('|', $lConf['category_stdWrap.']['wrap']);
			$markerArray['###CATWRAP_B###'] = $cat_stdWrap[0];
			$markerArray['###CATWRAP_E###'] = $cat_stdWrap[1];
			$markerArray['###TEXT_CAT###'] = $this->pi_getLL('textCat');
			$markerArray['###TEXT_CAT_LATEST###'] = $this->pi_getLL('textCatLatest');

			$news_category = array();
			$theCatImgCode = '';
			$theCatImgCodeArray = array();
			$catTextLenght = 0;

			foreach ($this->categories[$row['uid']] as $key => $val) {
			#while (list ($key, $val) = each ($this->categories[$row['uid']])) {
				// find categories, wrap them with links and collect them in the array $news_category.
				$catTitle = $this->categories[$row['uid']][$key]['title'];
				#$catTextLenght += strlen($catTitle);
				if ($this->config['catTextMode'] == 0) {
					$markerArray['###NEWS_CATEGORY###'] = '';
				} elseif ($this->config['catTextMode'] == 1) {
					// display but don't link
					$news_category[] = $this->local_cObj->stdWrap($catTitle, $lConf[($this->categories[$row['uid']][$key]['parent_category'] > 0?'subCategoryTitleItem_stdWrap.':'categoryTitleItem_stdWrap.')]);
				} elseif ($this->config['catTextMode'] == 2) {
					// link to category shortcut
					$news_category[] = $this->local_cObj->stdWrap($this->pi_linkToPage($catTitle, $this->categories[$row['uid']][$key]['shortcut'], $this->categories[$row['uid']][$key]['shortcut_target']), $lConf[($this->categories[$row['uid']][$key]['parent_category'] > 0?'subCategoryTitleItem_stdWrap.':'categoryTitleItem_stdWrap.')]);
				} elseif ($this->config['catTextMode'] == 3) {
					// act as category selector
					$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);

					if ($this->conf['useHRDates']) {
						$news_category[] = $this->pi_linkToPage($this->pi_linkTP_keepPIvars($catTitle, array(
							'cat' => $this->categories[$row['uid']][$key]['catid'],
							'year' => ($this->piVars['year']?$this->piVars['year']:null),
							'month' => ($this->piVars['month']?$this->piVars['month']:null)
							), '', 1, $catSelLinkParams), $this->categories[$row['uid']][$key]['shortcut'], $this->categories[$row['uid']][$key]['shortcut_target']);

					} else {
						$news_category[] = $this->pi_linkToPage($this->pi_linkTP_keepPIvars($catTitle, array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, $this->pointerName => null), '', '', $catSelLinkParams), $this->categories[$row['uid']][$key]['shortcut'], $this->categories[$row['uid']][$key]['shortcut_target']);
					}

				}

				$catTextLenght += strlen($catTitle);
				if ($this->config['catImageMode'] == 0 or empty($this->categories[$row['uid']][$key]['image'])) {
					$markerArray['###NEWS_CATEGORY_IMAGE###'] = '';
				} else {
					$catPicConf = array();
					$catPicConf['image.']['file'] = 'uploads/pics/' . $this->categories[$row['uid']][$key]['image'];
					$catPicConf['image.']['file.']['maxW'] = intval($this->config['catImageMaxWidth']);
					$catPicConf['image.']['file.']['maxH'] = intval($this->config['catImageMaxHeight']);
					$catPicConf['image.']['stdWrap.']['spaceAfter'] = 0;
					// clear the imagewrap to prevent category image from beeing wrapped in a table
					$lConf['imageWrapIfAny'] = '';
					if ($this->config['catImageMode'] != 1) {
						if ($this->config['catImageMode'] == 2) {
							// link to category shortcut
							$sCpageId = $this->categories[$row['uid']][$key]['shortcut'];
							$sCpage = $this->pi_getRecord('pages', $sCpageId); // get the title of the shortcut page
							$catPicConf['image.']['altText'] = $sCpage['title']?$this->pi_getLL('altTextCatShortcut') . $sCpage['title']:
							'';
							$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkToPage('|', $this->categories[$row['uid']][$key]['shortcut'], ($catLinkTarget?$catLinkTarget:$this->config['itemLinkTarget']));
						}
						if ($this->config['catImageMode'] == 3) {
							// act as category selector
							$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
							$catPicConf['image.']['altText'] = $this->pi_getLL('altTextCatSelector') . $catTitle;
							if ($this->conf['useHRDates']) {
								$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkTP_keepPIvars('|', array(
									'cat' => $this->categories[$row['uid']][$key]['catid'],
									'year' => ($this->piVars['year']?$this->piVars['year']:null),
									'month' => ($this->piVars['month']?$this->piVars['month']:null)
									), '', 1, $catSelLinkParams);
							} else {
								$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkTP_keepPIvars('|', array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, $this->pointerName => null), '', '', $catSelLinkParams);

							}
						}
					} else {
						$catPicConf['image.']['altText'] = $this->categories[$row['uid']][$key]['title'];
					}
					# debug($this->categories[$row['uid']][$key]['parent_category'],'pcat');
					# debug($lConf,'lConf');
					// add linked category image to output array
					$theCatImgCodeArray[] = $this->local_cObj->stdWrap($this->local_cObj->IMAGE($catPicConf['image.']), $lConf[($this->categories[$row['uid']][$key]['parent_category'] > 0?'subCategoryImgItem_stdWrap.':'categoryImgItem_stdWrap.')]);
				}
				if (!$wroteRegister){
					// Load the uid of the first assigned category to the register 'newsCategoryUid'
					$this->local_cObj->LOAD_REGISTER(array('newsCategoryUid' => $this->categories[$row['uid']][$key]['catid']), '');
					$wroteRegister = true;
				}
			}
			if ($this->config['catTextMode'] != 0) {
				$news_category = implode(', ', array_slice($news_category, 0, intval($this->config['maxCatTexts'])));
				if ($this->config['catTextLength']) {
					// crop the complete category titles if 'catTextLength' value is given
					$markerArray['###NEWS_CATEGORY###'] = (strlen($news_category) < intval($this->config['catTextLength'])?$news_category:substr($news_category, 0, intval($this->config['catTextLength'])) . '...');
				} else {
					$markerArray['###NEWS_CATEGORY###'] = $this->local_cObj->stdWrap($news_category, $lConf['categoryTitles_stdWrap.']);
				}
			}
			if ($this->config['catImageMode'] != 0) {
				$theCatImgCode = implode('', array_slice($theCatImgCodeArray, 0, intval($this->config['maxCatImages']))); // downsize the image array to the 'maxCatImages' value
				$markerArray['###NEWS_CATEGORY_IMAGE###'] = $this->local_cObj->stdWrap($theCatImgCode, $lConf['categoryImages_stdWrap.']);
			}
			// XML
			if ($this->theCode == 'XML') {
				$markerArray['###NEWS_CATEGORY###'] = $news_category;
			}
		}
		return $markerArray;
	}

	/**
	 * Fills the image markers with data. if a userfunction is given in "imageMarkerFunc",
	 * the marker Array is processed by this function.
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a news item
	 * @param	array		$lConf : configuration for the current templatepart
	 * @param	string		$textRenderObj : name of the template subpart
	 * @return	array		$markerArray: filled markerarray
	 */
	function getImageMarkers($markerArray, $row, $lConf, $textRenderObj) {
		// overwrite image sizes from TS with the values from the content-element if they exist.
		if ($this->config['FFimgH'] || $this->config['FFimgW']) {
			$lConf['image.']['file.']['maxW'] = $this->config['FFimgW'];
			$lConf['image.']['file.']['maxH'] = $this->config['FFimgH'];
		}

		if ($this->conf['imageMarkerFunc']) {
			$markerArray = $this->userProcess('imageMarkerFunc', array($markerArray, $lConf));
		} else {
            
            
			$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
			$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
			$theImgCode = '';
			$imgs = t3lib_div::trimExplode(',', $row['image'], 1);
			$imgsCaptions = explode(chr(10), $row['imagecaption']);
			$imgsAltTexts = explode(chr(10), $row['imagealttext']);
			$imgsTitleTexts = explode(chr(10), $row['imagetitletext']);

			reset($imgs);

			$cc = 0;
			// remove first img from the image array in single view if the TSvar firstImageIsPreview is set
			if (count($imgs) > 1 && $this->config['firstImageIsPreview'] && $textRenderObj == 'displaySingle') {
				array_shift($imgs);
				array_shift($imgsCaptions);
				array_shift($imgsAltTexts);
				array_shift($imgsTitleTexts);
			}
			// get img array parts for single view pages
			if ($this->piVars[$this->config['singleViewPointerName']]) {
				$spage = $this->piVars[$this->config['singleViewPointerName']];
				$astart = $imageNum*$spage;
				$imgs = array_slice($imgs,$astart,$imageNum);
				$imgsCaptions = array_slice($imgsCaptions,$astart,$imageNum);
				$imgsAltTexts = array_slice($imgsAltTexts,$astart,$imageNum);
				$imgsTitleTexts = array_slice($imgsTitleTexts,$astart,$imageNum);
			}

			while (list(, $val) = each($imgs)) {
				if ($cc == $imageNum) break;
				if ($val) {
					// MLC todo add watermark code
					$lConf['image.']['altText'] = $imgsAltTexts[$cc];
					$lConf['image.']['titleText'] = $imgsTitleTexts[$cc];
					$lConf['image.']['file'] = 'uploads/pics/' . $val;
				}
				$theImgCode .= $this->local_cObj->IMAGE($lConf['image.']) . $this->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				$cc++;
			}
			$markerArray['###NEWS_IMAGE###'] = '';
			if ($cc) {
				$markerArray['###NEWS_IMAGE###'] = $this->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
			}
		}
		return $markerArray;
	}

	/**
	 * Find related news records and pages, add links to them and wrap them with stdWraps from TS.
	 *
	 * @param	integer		$uid : it of the current news record
	 * @return	string		html code for the related news list
	 */
	function getRelated($uid) {
		$lConf = $this->conf['getRelatedCObject.'];
		// find visible categories and their singlePids
		$catres = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('tt_news_cat.uid,tt_news_cat.single_pid', 'tt_news_cat','1=1'.$this->SPaddWhere.$this->enableCatFields);
		$catTemp = array();
		$sPidByCat = array();
		while ($catrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($catres)) {
			$sPidByCat[$catrow['uid']] = $catrow['single_pid'];
			$catTemp[] = $catrow['uid'];
		}
		if ($this->conf['checkCategoriesOfRelatedNews']) {
			$visibleCategories = implode($catTemp,',');
		}

		if ($this->conf['usePagesRelations']) {
			$relPages = array();
			$pres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,title,tstamp,description,subtitle,M.tablenames',
				'pages,tt_news_related_mm AS M',
				'pages.uid=M.uid_foreign AND M.uid_local=' . $uid . ' AND M.tablenames="pages"'.$this->cObj->enableFields('pages'),
				'', 'title');
			while ($prow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pres)) {
				if ($GLOBALS['TSFE']->sys_language_content) {
					$prow = $GLOBALS['TSFE']->sys_page->getPageOverlay($prow, $GLOBALS['TSFE']->sys_language_content);
				}

				$relPages[] = array(
					'title' => $prow['title'],
					'datetime' => $prow['tstamp'],
					'archivedate' => 0,
					'type' => 1,
					'page' => $prow['uid'],
					'short' => $prow['subtitle']?$prow['subtitle']:$prow['description'],
					'tablenames' => $prow['tablenames']
				);
			}
		}
		$select_fields = 'DISTINCT uid, pid, title, author, short, datetime, archivedate, type, page, ext_url, sys_language_uid, l18n_parent, M.tablenames';

		$where = 'tt_news.uid=M.uid_foreign AND M.uid_local=' . $uid . ' AND M.tablenames!="pages"';

		if ($lConf['groupBy']) {
			$groupBy = trim($lConf['groupBy']);
		}
		if ($lConf['orderBy']) {
			$orderBy = trim($lConf['orderBy']);
		}

		if ($this->conf['useBidirectionalRelations']) {
			$where = '(('.$where.') OR (tt_news.uid=M.uid_local AND M.uid_foreign=' . $uid .' AND M.tablenames!="pages"))';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, 'tt_news,tt_news_related_mm AS M', $where . $this->enableFields. $groupBy, $orderBy);

		if ($res) {
			$relrows = array();
			while ($relrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$currentCats = array();
				if ($this->conf['checkCategoriesOfRelatedNews'] || $this->conf['useSPidFromCategory']) {
					$currentCats = $this->getCategories($relrow['uid'],true);
				}
				if ($this->conf['checkCategoriesOfRelatedNews']) {
					if (count($currentCats))  { // record has categories
						foreach ($currentCats as $cUid) {
							if (t3lib_div::inList($visibleCategories,$cUid['catid'])) { // if the record has at least one visible category assigned it will be shown
								$relrows[$relrow['uid']] = $relrow;
							}
						}
					} else { // record has NO categories
						$relrows[$relrow['uid']] = $relrow;
					}
				} else {
					$relrows[$relrow['uid']] = $relrow;
				}

					// check if there's a single pid for the first category of a news record and add 'sPidByCat' to the $relrows array.
				if ($this->conf['useSPidFromCategory'] && count($currentCats) && $relrows[$relrow['uid']]) {
					$firstcat = array_shift($currentCats);
					if ($firstcat['catid'] && $sPidByCat[$firstcat['catid']]) {
						$relrows[$relrow['uid']]['sPidByCat'] = $sPidByCat[$firstcat['catid']];
					}
				}
			}
			if (is_array($relPages[0]) && $this->conf['usePagesRelations']) {
				$relrows = array_merge_recursive($relPages,$relrows);
			}

			$veryLocal_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
			$lines = array();
			foreach($relrows as $k => $row) {
				if ($GLOBALS['TSFE']->sys_language_content && $row['tablenames']!='pages') {
					$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
				}
				$veryLocal_cObj->start($row, 'tt_news');
				if ($row['type']!=1 && $row['type']!=2) { // only normal news
					$queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET'])) ;

					if ($queryString) {
						while (list(, $val) = each($queryString)) {
							$tmp = explode('=', $val);
							$paramArray[$tmp[0]] = $val;
						}

						$excludeList = 'id,tx_ttnews[tt_news],tx_ttnews[backPid],L,tx_ttnews['.$this->config['singleViewPointerName'].']';
						while (list($key, $val) = each($paramArray)) {
							if (!$val || ($excludeList && t3lib_div::inList($excludeList, $key))) {
								unset($paramArray[$key]);
							}
						}
						$paramArray['tx_ttnews[tt_news]'] = 'tx_ttnews[tt_news]=' . $row['uid'];

						if (!$this->conf['dontUseBackPid']) {
							$paramArray['tx_ttnews[backPid]'] = 'tx_ttnews[backPid]=' . $this->config['backPid'];
						}
						$newsAddParams = '&' . implode($paramArray, '&');
						// debug ($newsAddParams);
					}
					// load the parameter string into the register 'newsAddParams' to access it from TS
					$veryLocal_cObj->LOAD_REGISTER(array('newsAddParams' => $newsAddParams), '');
					$catSPid = false;
					if ($row['sPidByCat'] && $this->conf['useSPidFromCategory']) {
						$catSPid = $row['sPidByCat'];
					}
					$sPid = ($catSPid?$catSPid:$this->config['singlePid']);
					$veryLocal_cObj->LOAD_REGISTER(array('newsSinglePid' => $sPid), '');
					$this->conf['getRelatedCObject.']['10.']['default.']['10.']['typolink.']['parameter'] = $sPid;
				}
				$lines[] = $veryLocal_cObj->cObjGetSingle($this->conf['getRelatedCObject'], $this->conf['getRelatedCObject.'], 'getRelated');
			}
			return implode('', $lines);
		}
	}

	/**
	 * Calls user function defined in TypoScript
	 *
	 * @param	integer		$mConfKey : if this value is empty the var $mConfKey is not processed
	 * @param	mixed		$passVar : this var is processed in the user function
	 * @return	mixed		the processed $passVar
	 */
	function userProcess($mConfKey, $passVar) {
		if ($this->conf[$mConfKey]) {
			$funcConf = $this->conf[$mConfKey . '.'];
			$funcConf['parentObj'] = & $this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	}

	/**
	 * returns the subpart name. if 'altMainMarkers.' are given this name is used instead of the default marker-name.
	 *
	 * @param	string		$subpartMarker : name of the subpart to be substituted
	 * @return	string		new name of the template subpart
	 */
	function spMarker($subpartMarker) {
		$sPBody = substr($subpartMarker, 3, -3);
		$altSPM = '';
		if (isset($this->conf['altMainMarkers.'])) {
			$altSPM = trim($this->cObj->stdWrap($this->conf['altMainMarkers.'][$sPBody], $this->conf['altMainMarkers.'][$sPBody . '.']));
			$GLOBALS['TT']->setTSlogMessage('Using alternative subpart marker for \'' . $subpartMarker . '\': ' . $altSPM, 1);
		}

		return $altSPM?$altSPM:
		$subpartMarker;
	}

	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->searchFieldList, 'tt_news');
		return $where;
	}

	/**
	 * Format string with general_stdWrap from configuration
	 *
	 * @param	string		$string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr($str) {
		if (is_array($this->conf['general_stdWrap.'])) {
			$str = $this->local_cObj->stdWrap($str, $this->conf['general_stdWrap.']);
		}
		return $str;
	}

	/**
	 * Returns alternating layouts
	 *
	 * @param	string		$html code of the template subpart
	 * @param	integer		$number of alternatingLayouts
	 * @param	string		$name of the content-markers in this template-subpart
	 * @return	array		html code for alternating content markers
	 */
	function getLayouts($templateCode, $alternatingLayouts, $marker) {
		$out = array();
		for($a = 0; $a < $alternatingLayouts; $a++) {
			$m = '###' . $marker . ($a?'_' . $a:'') . '###';
			if (strstr($templateCode, $m)) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart($templateCode, $m);
			} else {
				break;
			}
		}
		return $out;
	}

	/**
	 * fills the internal array '$this->langArr' with the available syslanguages
	 *
	 * @return	void
	 */
	function initLanguages () {
		$lres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', '1=1' . $this->cObj->enableFields('sys_language'));
		$this->langArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($lres)) {
			$this->langArr[$row['uid']] = $row;
		}
	}


	/**
	 * initialize category related vars and add subcategories to the category selection
	 *
	 * @return	void
	 */
	function initCategoryVars() {

		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_news']);
		if ($confArr['useStoragePid']) {
			$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();
			$this->SPaddWhere = ' AND tt_news_cat.pid IN (' . $storagePid['_STORAGE_PID'] . ')';
		}

		$this->enableCatFields = $this->cObj->enableFields('tt_news_cat');

		$catOrderBy = trim($this->conf['catOrderBy']);
		if ($catOrderBy) { $catOrderBy = $this->validateFields($catOrderBy); }
		$this->config['catOrderBy'] = $catOrderBy?$catOrderBy:'sorting';

		// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
		$categoryMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoryMode', 'sDEF');
		$this->config['categoryMode'] = $categoryMode ? $categoryMode: intval($this->conf['categoryMode']);
		// catselection holds only the uids of the categories selected by GETvars
		if ($this->piVars['cat']) {

			// catselection holds only the uids of the categories selected by GETvars
			$this->config['catSelection'] =  $this->checkRecords($this->piVars['cat']);

			if ($this->conf['useSubCategories'] && $this->config['catSelection']) {
				// get subcategories for selection from getVars
				$subcats = $this->getSubCategories($this->config['catSelection']);
				$this->config['catSelection'] = implode(',', array_unique(explode(',', $this->config['catSelection'].($subcats?','.$subcats:''))));



			}
		}
		$catExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection', 'sDEF');
		$catExclusive = $catExclusive?$catExclusive:trim($this->cObj->stdWrap($this->conf['categorySelection'], $this->conf['categorySelection.']));
		$this->catExclusive = $this->config['categoryMode']?$catExclusive:0; // ignore cat selection if categoryMode isn't set

		$this->catExclusive = $this->checkRecords($this->catExclusive);


		// get subcategories
		if ($this->conf['useSubCategories'] && $this->catExclusive) {
			$subcats = $this->getSubCategories($this->catExclusive);
			$this->catExclusive = implode(',', array_unique(explode(',', $this->catExclusive.($subcats?','.$subcats:''))));


		}
		// get more category fields from FF or TS
		$fields = explode(',', 'catImageMode,catTextMode,catImageMaxWidth,catImageMaxHeight,maxCatImages,catTextLength,maxCatTexts');
		foreach($fields as $key) {
			$value = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, 's_category');
			$this->config[$key] = (is_numeric($value)?$value:$this->conf[$key]);
		}
	}


	/**
	 * Checks the visibility of a list of category-records
	 *
	 * @param	string		$recordlist: comma seperated list of category uids
	 * @return	string		$clearedlist: the cleared list
	 */
	function checkRecords($recordlist) {
		if ($recordlist) {
			$temp = t3lib_div::intExplode(',', $recordlist);
			$newtemp = array();
			while (list(, $val) = each($temp)) {
				$val = intval($val);
				if ($val) {
					$test = $GLOBALS['TSFE']->sys_page->checkRecord('tt_news_cat',$val,1); // test, if the record is visible
					if ($test) {
						$newtemp[] = $val;
					}
				}
			}
			reset($newtemp);
			if (!count($newtemp)){
				// select category 'null' if no visible category was found
				$newtemp[] = 'null';
			}
			$clearedlist = implode(',', $newtemp);
			return $clearedlist;
		}

	}

	/**
	 * read the template file, fill in global wraps and markers and write the result
	 * to '$this->templateCode'
	 *
	 * @return	void
	 */
	function initTemplate() {
		
        
        // read template-file and fill and substitute the Global Markers
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
        
		$this->templateCode = $this->cObj->fileResource($templateflex_file?'uploads/tx_ttnews/' . $templateflex_file:$this->conf['templateFile']);
        
        $splitMark = md5(microtime());
		$globalMarkerArray = array();
		list($globalMarkerArray['###GW1B###'], $globalMarkerArray['###GW1E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap1.']));
		list($globalMarkerArray['###GW2B###'], $globalMarkerArray['###GW2E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap2.']));
		list($globalMarkerArray['###GW3B###'], $globalMarkerArray['###GW3E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $this->conf['wrap3.']));
		$globalMarkerArray['###GC1###'] = $this->cObj->stdWrap($this->conf['color1'], $this->conf['color1.']);
		$globalMarkerArray['###GC2###'] = $this->cObj->stdWrap($this->conf['color2'], $this->conf['color2.']);
		$globalMarkerArray['###GC3###'] = $this->cObj->stdWrap($this->conf['color3'], $this->conf['color3.']);
		$globalMarkerArray['###GC4###'] = $this->cObj->stdWrap($this->conf['color4'], $this->conf['color4.']);
		$this->templateCode = $this->cObj->substituteMarkerArray($this->templateCode, $globalMarkerArray);
        
    }
	/**
	 * extends the pid_list given from $conf or FF recursively by the pids of the subpages
	 * generates an array from the pagetitles of those pages
	 *
	 * @return	void
	 */
	function initPidList () {
		// pid_list is the pid/list of pids from where to fetch the news items.
		$pid_list = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages', 'sDEF');
		$pid_list = $pid_list?$pid_list:
		trim($this->cObj->stdWrap($this->conf['pid_list'], $this->conf['pid_list.']));
		$pid_list = $pid_list ? implode(t3lib_div::intExplode(',', $pid_list), ','):
		$GLOBALS['TSFE']->id;

		$recursive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'sDEF');
		$recursive = is_numeric($recursive)?$recursive:
		$this->cObj->stdWrap($this->conf['recursive'], $this->conf['recursive.']);
		// extend the pid_list by recursive levels
		$this->pid_list = $this->pi_getPidList($pid_list, $recursive);
		$this->pid_list = $this->pid_list?$this->pid_list:0;
		// generate array of page titles
		$this->generatePageArray();


	}



	/**
	 * builds the XML header (array of markers to substitute)
	 *
	 * @return	array		the filled XML header markers
	 */
	function getXmlHeader() {
		$markerArray = array();

		$markerArray['###SITE_TITLE###'] = $this->conf['displayXML.']['xmlTitle'];
		$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];
		$markerArray['###SITE_DESCRIPTION###'] = $this->conf['displayXML.']['xmlDesc'];
		if(!empty($markerArray['###SITE_DESCRIPTION###']) && $this->conf['displayXML.']['xmlFormat'] == 'atom03') {
			$markerArray['###SITE_DESCRIPTION###'] = '<tagline>'.$markerArray['###SITE_DESCRIPTION###'].'</tagline>';
		}

		$markerArray['###SITE_LANG###'] = $this->conf['displayXML.']['xmlLang'];
		if($this->conf['displayXML.']['xmlFormat'] == 'rss2') {
			$markerArray['###SITE_LANG###'] = '<language>'.$markerArray['###SITE_LANG###'].'</language>';
		} elseif($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
			$markerArray['###SITE_LANG###'] = ' xml:lang="'.$markerArray['###SITE_LANG###'].'"';
		}
		if(empty($this->conf['displayXML.']['xmlLang'])) {
			$markerArray['###SITE_LANG###'] = '';
		}

		$markerArray['###IMG###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgFile = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgSize = is_file($imgFile)?getimagesize($imgFile):
		'';

		$markerArray['###IMG_W###'] = $imgSize[0];
		$markerArray['###IMG_H###'] = $imgSize[1];

		$markerArray['###NEWS_WEBMASTER###'] = $this->conf['displayXML.']['xmlWebMaster'];
		$markerArray['###NEWS_MANAGINGEDITOR###'] = $this->conf['displayXML.']['xmlManagingEditor'];

		$selectConf = Array();
		$selectConf['pidInList'] = $this->pid_list;
		// select only normal news (type=0) for the RSS feed. You can override this with other types with the TS-var 'xmlNewsTypes'
		$selectConf['selectFields'] = 'max(datetime) as maxval';
		$res = $this->cObj->exec_getQuery('tt_news', $selectConf);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// optional tags
		if ($this->conf['displayXML.']['xmlLastBuildDate']) {
			$markerArray['###NEWS_LASTBUILD###'] = '<lastBuildDate>' . date('D, d M Y H:i:s O', $row['maxval']) . '</lastBuildDate>';
		} else {
			$markerArray['###NEWS_LASTBUILD###'] = '';
		}

		if($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
			$markerArray['###NEWS_LASTBUILD###'] = $this->getW3cDate($row['maxval']);
		}

		if ($this->conf['displayXML.']['xmlWebMaster']) {
			$markerArray['###NEWS_WEBMASTER###'] = '<webMaster>' . $this->conf['displayXML.']['xmlWebMaster'] . '</webMaster>';
		} else {
			$markerArray['###NEWS_WEBMASTER###'] = '';
		}

		if ($this->conf['displayXML.']['xmlManagingEditor']) {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '<managingEditor>' . $this->conf['displayXML.']['xmlManagingEditor'] . '</managingEditor>';
		} else {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '';
		}

		if ($this->conf['displayXML.']['xmlCopyright']) {
			$markerArray['###NEWS_COPYRIGHT###'] = '<copyright>' . $this->conf['displayXML.']['xmlCopyright'] . '</copyright>';
		} else {
			$markerArray['###NEWS_COPYRIGHT###'] = '';
		}

		$charset = ($GLOBALS['TSFE']->metaCharset?$GLOBALS['TSFE']->metaCharset:'iso-8859-1');
		if ($this->conf['displayXML.']['xmlDeclaration']) {
			$markerArray['###XML_DECLARATION###'] = trim($this->conf['displayXML.']['xmlDeclaration']);
		} else {
			$markerArray['###XML_DECLARATION###'] = '<?xml version="1.0" encoding="'.$charset.'"?>';
		}

		//promoting TYPO3 in atom feeds, supress the subversion
		$version = explode('.',($GLOBALS['TYPO3_VERSION']?$GLOBALS['TYPO3_VERSION']:$GLOBALS['TYPO_VERSION']));
		unset($version[2]);
		$markerArray['###TYPO3_VERSION###'] = implode($version,'.');

		return $markerArray;
	}

	/**
	 * Generates the date format needed for Atom feeds
	 * see: http://www.w3.org/TR/NOTE-datetime (same as ISO 8601)
	 * in php5 it would be so easy: date('c', $row['datetime']);
	 *
	 * @param	integer		the datetime value to be converted to w3c format
	 * @return	string		datetime in w3c format
	 */
	function getW3cDate($datetime) {
		$offset = date('Z', $datetime) / 3600;
		if($offset < 0) {
			$offset *= -1;
			if($offset < 10) {
				$offset = '0'.$offset;
			}
			$offset = '-'.$offset;
		} elseif ($offset == 0) {
			$offset = '+00';
		} elseif ($offset < 10) {
			$offset = '+0'.$offset;
		} else {
			$offset = '+'.$offset;
		}
		return strftime('%Y-%m-%dT%T', $datetime).$offset.':00';
 	}

	/**
	 * this the old [DEPRECIATED] function for XML news feed.
	 *
	 * @param	string		$content : ...
	 * @param	array		$conf : configuration array from TS
	 * @return	string		news content as xml string
	 */
	function main_xmlnewsfeed($content, $conf) {
		$className = t3lib_div::makeInstanceClassName('t3lib_xml');
		$xmlObj = new $className('typo3_xmlnewsfeed');
		$xmlObj->setRecFields('tt_news', 'title,datetime'); // More fields here...
		$xmlObj->renderHeader();
		$xmlObj->renderRecords('tt_news', $this->getStoriesResult());
		$xmlObj->renderFooter();
		return $xmlObj->getResult();
	}

	/**
	 * returns the db-result for the news-item displayed by the xmlnewsfeed function
	 *
	 * @return	pointer		MySQL select result pointer / DBAL object
	 */
	function getStoriesResult() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', 'pid=' . intval($GLOBALS['TSFE']->id) . $this->cObj->enableFields('tt_news'), '', 'datetime DESC');
		return $res;
	}


	/**
	 * cleans the content for rss feeds. removes '&nbsp;' and '?;' (dont't know if the scond one matters in real-life).
	 * The rest of the cleaning/character-conversion is done by the stdWrap functions htmlspecialchars,stripHtml and csconv.
	 * For details see http://typo3.org/documentation/document-library/doc_core_tsref/stdWrap/
	 *
	 * @param	string		$str: inputstring to clean
	 * @return	string		the cleaned string
	 */
	function cleanXML($str) {
		$cleanedStr = preg_replace(
			array('/&nbsp;/', '/&;/'),
			array(' '.'&amp;;'),
			$str);
		return $cleanedStr;
	}

	/**
	 * Converts the piVars 'pS' and 'pL' to a human readable format which will be filled to
	 * the piVars 'year' and 'month'.
	 *
	 * @return	void
	 */
	function convertDates() {
		//readable archivedates
 		 if ($this->piVars['year'] || $this->piVars['month']) {
			$this->arcExclusive = 1;
		}
		if (!$this->piVars['year'] && $this->piVars['pS']) {
			$this->piVars['year'] = date('Y',$this->piVars['pS']);
		}
		if (!$this->piVars['month'] && $this->piVars['pS']) {
			$this->piVars['month'] = date('m',$this->piVars['pS']);
		}
		if ($this->piVars['year'] || $this->piVars['month']) {
		$this->piVars['pS'] = mktime (0, 0, 0, $this->piVars['month'], 1, $this->piVars['year']);
			switch ($this->config['archiveMode']) {
				case 'month':
					$this->piVars['pL'] = mktime (0, 0, 0, $this->piVars['month']+1, 1, $this->piVars['year'])-$this->piVars['pS']-1;
				break;
				case 'quarter':
					$this->piVars['pL'] = mktime (0, 0, 0, $this->piVars['month']+3, 1, $this->piVars['year'])-$this->piVars['pS']-1;
				break;
				case 'year':
					$this->piVars['pL'] = mktime (0, 0, 0, 1, 1, $this->piVars['year']+1)-$this->piVars['pS']-1;
				break;
			}
		}
	}

	/**
	 * converts the datetime of a record into variables you can use in realurl
	 *
	 * @param	integer		the timestamp to convert into a HR date
	 * @return	void
	 */
	function getHrDateSingle($tstamp) {
		$this->piVars['year'] = date('Y',$tstamp);
		$this->piVars['month'] = date('m',$tstamp);
		if (!$this->conf['useHRDatesSingleWithoutDay'])	{
			$this->piVars['day'] = date('d',$tstamp);
		}
	}

	/**
	 * returns a help message wich will be displayed on the website when no "code" is given or when the given "code" doesn't exist.
	 *
	 * @return	string		HTML code for the help message
	 */
	function displayFEHelp() {
		$langKey = strtoupper($GLOBALS['TSFE']->config['config']['language']);
		$helpTemplate = $this->cObj->fileResource('EXT:tt_news/pi/news_help.tmpl');
		// Get language version of the help-template
		$helpTemplate_lang = '';
		if ($langKey) {
			$helpTemplate_lang = $this->getNewsSubpart($helpTemplate, "###TEMPLATE_" . $langKey . '###');
		}
		$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $this->getNewsSubpart($helpTemplate, '###TEMPLATE_DEFAULT###');
		// Markers and substitution:
		$markerArray['###CODE###'] = $this->theCode?$this->theCode:'no CODE given!';
		$markerArray['###EXTPATH###'] = $GLOBALS['TYPO3_LOADED_EXT']['tt_news']['siteRelPath'];
		return $this->cObj->substituteMarkerArray($helpTemplate, $markerArray);
	}

	/**
	 * checks for each field of a list of items if it exists in the database ($this->fieldNames) and returns the validated fields
	 *
	 * @param	string		$fieldlist: a list of fields to ckeck
	 * @return	string		the list of validated fields
	 */
	function validateFields($fieldlist) {
		$checkedFields = array();
		$fArr = t3lib_div::trimExplode(',',$this->conf['searchFieldList'],1);
			while (list(,$fN) = each($fArr)) {
				if (in_array($fN,$this->fieldNames)) {
					$checkedFields[] = $fN;
				}
			}
		$checkedFieldlist = implode($checkedFields,',');
		return $checkedFieldlist;
	}

	/**
	 * Returns a subpart from the input content stream.
	 * Enables pre-/post-processing of templates/templatefiles
	 *
	 * @param	string		$Content stream, typically HTML template content.
	 * @param	string		$Marker string, typically on the form "###...###"
	 * @param	array		$Optional: the active row of data - if available
	 * @return	string		The subpart found, if found.
	 */
	function getNewsSubpart($myTemplate, $myKey, $row = Array()) {
		return ($this->cObj->getSubpart($myTemplate, $myKey));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_news/pi/class.tx_ttnews.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_news/pi/class.tx_ttnews.php']);
}

?>
