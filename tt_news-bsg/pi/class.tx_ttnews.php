<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skårhøj (kasper@typo3.com)
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
 * Creates a news system.
 * $Id: class.tx_ttnews.php,v 1.1.1.1 2010/04/15 10:04:11 peimic.comprock Exp $
 *
 * TypoScript config:
 * - See ext_typoscript_setup.txt
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
 *   85: class tx_ttnews extends tslib_pibase
 *  112:     function main_xmlnewsfeed($content, $conf)
 *  127:     function getStoriesResult()
 *  138:     function init($conf)
 *  297:     function main_news($content, $conf)
 *  358:     function newsArchiveMenu()
 *  484:     function displaySingle()
 *  539:     function displayList()
 *  786:     function getListContent($itemparts, $selectConf, $prefix_display)
 *  848:     function getSelectConf($where, $noPeriod = 0)
 *  952:     function initCategories()
 *  990:     function generatePageArray()
 * 1006:     function getItemMarkerArray ($row, $textRenderObj = 'displaySingle')
 * 1152:     function getCatMarkerArray($markerArray, $row, $lConf)
 * 1254:     function getImageMarkers($markerArray, $row, $lConf, $textRenderObj)
 * 1309:     function getRelated($uid)
 * 1364:     function userProcess($mConfKey, $passVar)
 * 1379:     function spMarker($subpartMarker)
 * 1396:     function searchWhere($sw)
 * 1407:     function formatStr($str)
 * 1422:     function getLayouts($templateCode, $alternatingLayouts, $marker)
 * 1440:     function getXmlHeader()
 * 1498:     function cleanXML($str)
 * 1515:     function getNewsSubpart($myTemplate, $myKey, $row = Array())
 *
 * TOTAL FUNCTIONS: 23
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

	var $categories = array(); // Is initialized with the categories of the news system
	var $pageArray = array(); // Is initialized with an array of the pages in the pid-list
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
	 * Init Function: here all the needed configuration Values are stored in class variables..
	 *
	 * @param	array		$conf : configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		// MLC debug db
		$GLOBALS[ 'TYPO3_DB' ]->debugOutput	= true;
		$this->conf = $conf; //store configuration
		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tt_news');
		$this->tt_news_uid = intval($this->piVars['tt_news']); // Get the submitted uid of a news (if any)

		// load available syslanguages
		$lres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', '1=1' . $this->cObj->enableFields('sys_language'));
		$this->langArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($lres)) {
			$this->langArr[$row['uid']] = $row;
		}
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode']:$GLOBALS['TSFE']->sys_language_mode;

		// "CODE" decides what is rendered: codes can be added by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->config['code'] = $code ? $code:$this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);


		// News Categories:
		// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
		$categoryMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoryMode', 'sDEF');
		$this->config['categoryMode'] = $categoryMode ? $categoryMode:$this->conf['categoryMode'];
		if (is_numeric($this->piVars['cat'])) {
		// catselection holds only the uids of the categories selected by GETvars
			$this->config['catSelection'] = $this->piVars['cat'];
		}
		$catExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection', 'sDEF');
		$catExclusive = $catExclusive?$catExclusive:trim($this->conf['categorySelection']);
		$this->catExclusive = $this->config['categoryMode']?$catExclusive:0; // ignore cat selection if categoryMode isn't set
		// get more category fields from FF or TS
		$fields = explode(',', 'catImageMode,catTextMode,catImageMaxWidth,catImageMaxHeight,maxCatImages,catTextLength,maxCatTexts');
		foreach($fields as $key) {
			$value = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, 's_category');
			$this->config[$key] = (is_numeric($value)?$value:$this->conf[$key]);
		}

		$catOrderBy = trim($this->conf['catOrderBy']);
		$this->config['catOrderBy'] = $catOrderBy?$catOrderBy:'uid';

		$this->initCategories(); // initialize category-array

		// Archive:
		$this->config['archiveMode'] = trim($this->conf['archiveMode']) ; // month, quarter or year listing in AMENU

		// arcExclusive : -1=only non-archived; 0=don't care; 1=only archived
		$arcExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'archive', 'sDEF');
		$this->arcExclusive = $arcExclusive?$arcExclusive:$this->conf['archive'];

		$this->config['datetimeDaysToArchive'] = intval($this->conf['datetimeDaysToArchive']);

		// pid_list is the pid/list of pids from where to fetch the news items.
		$pid_list = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages', 'sDEF');
		$pid_list = $pid_list?$pid_list:trim($this->cObj->stdWrap($this->conf['pid_list'], $this->conf['pid_list.']));
		$pid_list = $pid_list ? implode(t3lib_div::intExplode(',', $pid_list), ','):$GLOBALS['TSFE']->id;

		$recursive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'sDEF');
		$recursive = is_numeric($recursive)?$recursive:$this->cObj->stdWrap($conf['recursive'], $conf['recursive.']);
		// extend the pid_list by recursive levels
		$this->pid_list = $this->pi_getPidList($pid_list, $recursive);
		$this->pid_list = $this->pid_list?$this->pid_list:0;
		// generate array of page titles
		$this->generatePageArray();

		// itemLinkTarget is only used for categoryLinkMode 3 (catselector) in framesets
		$this->config['itemLinkTarget'] = trim($this->conf['itemLinkTarget']);
		// id of the page where the search results should be displayed
		$this->config['searchPid'] = intval($this->conf['searchPid']);

		// pid of the page with the single view. the old var PIDitemDisplay is still processed if no other value is found
		$singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDitemDisplay', 's_misc');
		$singlePid = $singlePid?$singlePid:intval($this->conf['singlePid']);
		$this->config['singlePid'] = $singlePid ? $singlePid : intval($this->conf['PIDitemDisplay']);
		// pid to return to when leaving single view
		$backPid = intval($this->piVars['backPid']);
		$backPid = $backPid?$backPid:intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPid', 's_misc'));
		$backPid = $backPid?$backPid:intval($this->conf['backPid']);
		$backPid = $backPid?$backPid:$GLOBALS['TSFE']->id ;
		$this->config['backPid'] = $backPid;

		// max items per page
		$FFlimit = t3lib_div::intInRange($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listLimit', 's_misc'), 0, 1000);

		$limit = t3lib_div::intInRange($this->conf['limit'], 0, 1000);
		$limit = $limit?$limit:50;
		$this->config['limit'] = $FFlimit?$FFlimit:$limit;

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
		$this->config['firstImageIsPreview'] = $fImgPreview?$fImgPreview:$this->conf['firstImageIsPreview'];

		// List start id
		$listStartId = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listStartId', 's_misc'));
		$this->config['listStartId'] = $listStartId?$listStartId:intval($this->conf['listStartId']);
		// MLC specific news id to show
		$newsId = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'newsId', 's_misc'));
		$this->config['newsId'] = $newsId?$newsId:intval($this->conf['newsId']);
		// supress pagebrowser
		$noPageBrowser = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noPageBrowser', 's_misc');
		$this->config['noPageBrowser'] = $noPageBrowser?$noPageBrowser:$this->conf['noPageBrowser'];


		// image sizes given from FlexForms
		$this->config['FFimgH'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxHeight', 's_template'));
		$this->config['FFimgW'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxWidth', 's_template'));

		// Get number of alternative Layouts (loop layout in LATEST and LIST view) default is 2:
		$altLayouts = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alternatingLayouts', 's_template'));
		$altLayouts = $altLayouts?$altLayouts:intval($this->conf['alternatingLayouts']);
		$this->alternatingLayouts = $altLayouts?$altLayouts:2;

		// Get cropping lenght
		$this->config['croppingLenght'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'croppingLenght', 's_template'));


		// read template-file and fill and substitute the Global Markers
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
		$this->templateCode = $this->cObj->fileResource($templateflex_file?"uploads/tx_ttnews/" . $templateflex_file:$this->conf['templateFile']);
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

		// Configure caching
		$this->allowCaching = $this->conf['allowCaching']?1:0;
		if (!$this->allowCaching) {
			$GLOBALS['TSFE']->set_no_cache();
		}

		// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (!$this->conf['displayXML.']['dontInsertSiteUrl']){
			$this->config['siteUrl'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}

		// Don't link active page in page browser
		$this->internal['dontLinkActivePage'] = 1;

	}

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
			// added the possibility to change the template, used whe displaying news with the 'insert records' content-element. if the value is empty, the code is 'single'
			$this->config['code'] = $this->conf['defaultCode']?trim($this->conf['defaultCode']):'SINGLE';
			$this->tt_news_uid = $this->cObj->data['uid'];
		}
		// get codes and decide which function is used to process the content
		$codes = t3lib_div::trimExplode(',', $this->config['code']?$this->config['code']:$this->conf['defaultCode'], 1);
		if (!count($codes)) $codes = array('');

		// <<< td@krendls.eu; 02.04.2007; "BSG Training System" support
		if(isset($this->conf['trainingRelationPage']) && in_array($GLOBALS['TSFE']->page['uid'], explode(',', $this->conf['trainingRelationPage'])))
		{
			$content = $this->displayTrainings($codes);
		}else{
		// >>> td@krendls.eu;

			while (list(, $theCode) = each($codes)) {
				$theCode = (string)strtoupper(trim($theCode));
				$this->theCode = $theCode;

				switch ($theCode) {
					case 'SINGLE':
					$content .= $this->displaySingle();
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
						$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang :$this->getNewsSubpart($helpTemplate, '###TEMPLATE_DEFAULT###');
						// Markers and substitution:
						$markerArray['###CODE###'] = $this->theCode;
						$markerArray['###EXTPATH###'] = $GLOBALS['TYPO3_LOADED_EXT']['tt_news']['siteRelPath'];
						$content .= $this->cObj->substituteMarkerArray($helpTemplate, $markerArray);
					}
					break;
				}
			}
		}
		return $content;
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
			$arcMode = $this->config['archiveMode']?$this->config['archiveMode']:'month';
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
			$this->conf['parent.']['addParams']	= $this->conf['archiveTypoLink.']['addParams'];
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

				$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('cat' => ($amenuLinkCat?$amenuLinkCat:null), 'pS' => $pArr['start'], 'pL' => ($pArr['stop'] - $pArr['start']), 'arc' => 1), $this->allowCaching, 1, ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)));

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
	 * generates the single view of a news article. Is also used when displaying single records
	 * with the 'insert records' content element
	 *
	 * @return	string		html-code for a single news item
	 */
	function displaySingle() {
		$singleWhere = 'tt_news.uid=' . intval($this->tt_news_uid);
		$singleWhere .= ' AND type=0' . $this->enableFields; // type=0->only real news.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', $singleWhere);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// get the translated record if the content language is not the default language
		if ($GLOBALS['TSFE']->sys_language_content) {
		$OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
		}
		if (is_array($row)) {
			// Get the subpart code
			$item = '';
			if ($this->conf['displayCurrentRecord']) {
				$item = trim($this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE_RECORDINSERT###'), $row));
			}

			if (!$item) {
				$item = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE###'), $row);
			}
			// reset marker array
			$wrappedSubpartArray = array();
			$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => null, 'backPid' => null), $this->allowCaching, '', $this->config['backPid'] ));
			// set the title of the single view page to the title of the news record
			if ($this->conf['substitutePagetitle']) {
				$GLOBALS['TSFE']->page['title'] = $row['title'];
				// set pagetitle for indexed search to news title
				$GLOBALS['TSFE']->indexedDocTitle = $row['title'];
			}

			$markerArray = $this->getItemMarkerArray($row, 'displaySingle');
			// Substitute
			$content = $this->cObj->substituteMarkerArrayCached($item, $markerArray, array(), $wrappedSubpartArray);
		}
		elseif ($this->sys_language_mode == 'strict' && $this->tt_news_uid) {
			$noTranslMsg = $this->local_cObj->stdWrap($this->pi_getLL('noTranslMsg','Sorry, there is no translation for this news-article'), $this->conf['noNewsIdMsg_stdWrap.']);
			$content .= $noTranslMsg;
		}

		else {
			// if singleview is shown with no tt_news_uid given from GPvars, an error message is displayed.
			$noNewsIdMsg = $this->local_cObj->stdWrap($this->pi_getLL('noNewsIdMsg'), $this->conf['noNewsIdMsg_stdWrap.']);
			$content .= $noNewsIdMsg;
		}
		return $content;
	}

	/**
	 * Display LIST,LATEST or SEARCH
	 * Things happen: determine the template-part to use, get the query parameters (add where if search was performed),
	 * exec count query to get the number of results, check if a browsebox should be displayed,
	 * get the general Markers for each item and fill the content array, check if a browsebox should be displayed
	 *
	 * @return	string		html-code for the plugin content
	 */
	function displayList() {
		$theCode = $this->theCode;

		$where = '';
		$content = '';
		switch ($theCode) {
			case 'LATEST':
			$prefix_display = 'displayLatest';
			$templateName = 'TEMPLATE_LATEST';
			if (!$this->conf['displayArchivedInLatest']) { // if this is set, latest will do the same as list
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

			$formURL = $this->pi_linkTP_keepPIvars_url(array('pointer' => null, 'cat' => null), 0, '', $this->config['searchPid']) ;
			// Get search subpart
			$t['search'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH###'));
			// Substitute the markers for teh searchform
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
			$this->config['limit'] = $this->conf['displayXML.']['xmlLimit']?$this->conf['displayXML.']['xmlLimit']:$this->config['limit'];

			switch ($this->conf['displayXML.']['xmlFormat']) {
				case 'rss091':
				$templateName = 'TEMPLATE_RSS091';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss091_tmplFile']);
				break;

				case 'rss2':
				$templateName = 'TEMPLATE_RSS2';
				$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss2_tmplFile']);
				break;


				// this will be possible later:
				// case 'rdf':
				// $templateName = 'TEMPLATE_RDF';
				// $this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rdf_tmplFile']);
				// break;

				// case 'atom':
				// $templateName = 'TEMPLATE_ATOM';
				// $this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['atom_tmplFile']);
				// break;

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
		$noPeriod = 0;

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
			// build parameter Array for List query
			$selectConf = $this->getSelectConf($where, $noPeriod);
			// performing query to count all news (we need to know it for browsing):
			$selectConf['selectFields'] = 'count(distinct(uid))'; //count(*)
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
				$selectConf['selectFields'] = '*';
				if ($this->config['groupBy']) {
					$selectConf['groupBy'] = $this->config['groupBy'];
				} else {
					$selectConf['groupBy'] = 'uid';
				}

				if ($this->config['orderBy']) {
					$selectConf['orderBy'] = $this->config['orderBy'] . ($this->config['ascDesc']?' ' . $this->config['ascDesc']:'');
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
				if ($theCode != 'LATEST' && !$this->conf['latestWithPagebrowser']) {
					$selectConf['begin'] = $this->piVars['pointer'] * $this->config['limit'];
				}
				// exclude news-records shown in LATEST from the LIST template
				if ($theCode == 'LIST' && $this->conf['excludeLatestFromList'] && !$this->piVars['pointer'] && !$this->piVars['cat']) {
					if ($this->config['latestLimit']) {
						$selectConf['begin'] += $this->config['latestLimit'];
						$newsCount -= $this->config['latestLimit'];
					} else {
						$selectConf['begin'] += $newsCount;
						// this will clean the display of LIST view when 'latestLimit' is unset because all the news have been shown in LATEST already
					}
				}

				// List start ID
				if (($theCode == 'LIST' || $theCode == 'LATEST') && $this->config['listStartId'] && !$this->piVars['pointer'] && !$this->piVars['cat']) {
	$selectConf['begin'] = $this->config['listStartId'];
				}

				// MLC specific news to show
				if ( $this->config['newsId'] )
				{
					$selectConf['where']	= "
						1 = 1
						AND tt_news.uid = {$this->config['newsId']}
					";
				}

				// Reset:
				$subpartArray = array();
				$wrappedSubpartArray = array();
				$markerArray = array();

				if ($theCode == 'XML') {
					$markerArray = $this->getXmlHeader();

					$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);
				}
				// get the list of news items and fill them in the CONTENT subpart
				$subpartArray['###CONTENT###'] = $this->getListContent($t['item'], $selectConf, $prefix_display);

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
					$this->internal['pagefloat'] = 'CENTER';
					if (!$this->conf['pageBrowser.']['showPBrowserText']) {
						$this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
					}
					if ($this->conf['userPageBrowserFunc']) {
						$markerArray = $this->userProcess('userPageBrowserFunc', $markerArray);
					} else {
						$markerArray['###BROWSE_LINKS###'] = $this->pi_list_browseresults($this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams']);
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
				// get the list of news items and fill them in the CONTENT subpart
				$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);

				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###HEADER###', $subpartArray['###HEADER###'], 0);
				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###CONTENT###', '', 0);

				$content .= $t['total'];
			}
			elseif ($this->arcExclusive && $this->piVars['pS'] && $GLOBALS['TSFE']->sys_language_content)  {
			// this matches if a user has switched languages within a archive period that contains no items in the desired language

				$content .= $this->local_cObj->stdWrap($this->pi_getLL('noNewsForArchPeriod','Sorry, there are no translated news-articles in this Archive period'), $this->conf['noNewsToListMsg_stdWrap.']);
			}

			else {
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

		$cc = 0;
		// Getting elements
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$wrappedSubpartArray = array();

			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');

			}

			if ($row['type']) {
				// News type article or external url
				$this->local_cObj->setCurrentVal($row['type'] == 1 ? $row['page']:$row['ext_url']);
				$wrappedSubpartArray['###LINK_ITEM###'] = $this->local_cObj->typolinkWrap($this->conf['pageTypoLink.']);

				// fill the link string in a register to access it from TS
				$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->local_cObj->typolink($this->pi_getLL('more'),$this->conf['pageTypoLink.'])), '');
			} else {

				//  Overwrite the singlePid from config array with a singlePid given from category
				#debug($this->categories[$row['uid']]);
				$singlePid = $this->categories[$row['uid']]['0']['single_pid']?$this->categories[$row['uid']]['0']['single_pid']:$this->config['singlePid'];

				$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => $row['uid'], 'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid));

				// fill the link string in a register to access it from TS
				$this->local_cObj->LOAD_REGISTER(array('newsMoreLink' => $this->pi_linkTP_keepPIvars($this->pi_getLL('more'), array('tt_news' => $row['uid'], 'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0), $singlePid)), '');

			}
			$markerArray = $this->getItemMarkerArray($row, $prefix_display);
			// XML
			if ($this->theCode=='XML') {
				if ($row['type']) {
			    	$rssUrl = ($row['type'] == 1 ? $this->config['siteUrl'] .$this->pi_getPageLink($row['page'],''):substr($row['ext_url'],0,strpos($row['ext_url'],' '))) ;
				} else {
			  		$rssUrl = $this->config['siteUrl'] . $this->pi_linkTP_keepPIvars_url(array('tt_news' => $row['uid'], 'backPid' => null), $this->allowCaching, '', $singlePid);

 				}
				// replace square brackets [] in links with their URLcodes and replace the &-sign with its ASCII code
			  	$rssUrl = preg_replace(array('/\[/','/\]/','/&/'),array('%5B','%5D','&#38;') , $rssUrl);
				$markerArray['###NEWS_LINK###'] = $rssUrl;

			}
			$layoutNum = $cc % $itempartsCount;
			// Store the result of template parsing in the Var $itemsOut, use the alternating layouts
			$itemsOut .= $this->cObj->substituteMarkerArrayCached($itemparts[$layoutNum], $markerArray, array(), $wrappedSubpartArray);
			$cc++;
			if ($cc == $this->config['limit']) {
				break;
			}
		}

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

	#	if ($GLOBALS['TSFE']->sys_language_content >= -1) {
			if ($this->sys_language_mode == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
				// mode == 'strict': If a certain language is requested, select only news-records from the default language which have a translation. The translated articles will be overlayed later in the list or single function.
				$tmpres = $this->cObj->exec_getQuery('tt_news', array('selectFields' => 'tt_news.l18n_parent', 'where' => 'tt_news.sys_language_uid = '.$GLOBALS['TSFE']->sys_language_content.$this->enableFields, 'pidInList' => $this->pid_list));
				$strictUids = array();
				while ($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tmpres)) {
					$strictUids[] = $tmprow['l18n_parent'];
				}
				$strStrictUids = implode(',', $strictUids);
				$selectConf['where'] .= ' AND (tt_news.uid IN (' . ($strStrictUids?$strStrictUids:0) . ') OR tt_news.sys_language_uid=-1)';
			} else {
				// mode != 'strict': If a certain language is requested, select only news-records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
				$selectConf['where'] .= ' AND tt_news.sys_language_uid IN (0,-1)';
			}
	#	}

		if ($this->arcExclusive > 0) {
			if ($this->piVars['arc']) {
				// allow overriding of the arcExclusive parameter from GET vars
				$this->arcExclusive = $this->piVars['arc'];
			}
			// select news from a certain period
			if (!$noPeriod && $this->piVars['pS']) {
				$selectConf['where'] .= ' AND tt_news.datetime>=' . $this->piVars['pS'];
				if ($this->piVars['pL']) {
					$selectConf['where'] .= ' AND tt_news.datetime<' . ($this->piVars['pS'] + $this->piVars['pL']);
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
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
				$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,0) IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
			}
			if ($this->config['categoryMode'] == -1) {
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON (tt_news.uid = tt_news_cat_mm.uid_local AND (tt_news_cat_mm.uid_foreign=';
				if (strstr($this->catExclusive, ',')) {
					$selectConf['leftjoin'] .= ereg_replace(',', ' OR tt_news_cat_mm.uid_foreign=', $this->catExclusive);
				} else {
					$selectConf['leftjoin'] .= $this->catExclusive?$this->catExclusive:0;
				}
				$selectConf['leftjoin'] .= '))';
				$selectConf['where'] .= ' AND (tt_news_cat_mm.uid_foreign IS NULL)';
			}
		} elseif ($this->config['categoryMode']) {
			$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,\'nocat\') ' . ($this->config['categoryMode'] > 0?'':'!') . '=\'nocat\')';
		}
		// function Hook for processing the selectConf array
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['selectConfHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['selectConfHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$selectConf = $_procObj->processSelectConfHook($this,$selectConf);
			}
		}
		// debug(array('select_conf',$this->piVars,$selectConf,$this->arcExclusive));
		return $selectConf;
	}

	/**
	 * Getting all tt_news_cat categories into internal array
	 *
	 * @return	void
	 */
	function initCategories() {
		// decide whether to look for categories only in the 'General record Storage page', or in the complete pagetree
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_news']);
		if ($confArr['useStoragePid']) {
			$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();
			$addWhere = ' AND tt_news_cat.pid IN (' . $storagePid['_STORAGE_PID'] . ')';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news_cat LEFT JOIN tt_news_cat_mm ON tt_news_cat_mm.uid_foreign = tt_news_cat.uid', '1=1' . $addWhere . $this->cObj->enableFields('tt_news_cat'),'','tt_news_cat.'.$this->config['catOrderBy']);

		$this->categories = array();
		$this->categorieImages = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$catTitle = '';
			if ($GLOBALS['TSFE']->sys_language_content) {
				// find translations of category titles
				$catTitleArr = t3lib_div::trimExplode('|', $row['title_lang_ol']);
				$catTitle = $catTitleArr[($GLOBALS['TSFE']->sys_language_content-1)];
			}
			$catTitle = $catTitle?$catTitle:$row['title'];

			if (isset($row['uid_local'])) {
				$this->categories[$row['uid_local']][] = array('title' => $catTitle,
					'image' => $row['image'],
					'shortcut' => $row['shortcut'],
					'shortcut_target' => $row['shortcut_target'],
					'single_pid' => $row['single_pid'],
					'catid' => $row['uid_foreign']);
			} else {
				$this->categories['0'][$row['uid']] = $catTitle;
			}
		}
	}

	/**
	 * Generates an array,->pageArray of the pagerecords from->pid_list
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
		// MLC no p wrap
		// $markerArray['###NEWS_AUTHOR###'] = $this->formatStr($newsAuthor);
		$markerArray['###NEWS_AUTHOR###'] = $newsAuthor;
		$markerArray['###NEWS_EMAIL###'] = $this->local_cObj->stdWrap($row['author_email'], $lConf['email_stdWrap.']);
		$markerArray['###NEWS_DATE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['date_stdWrap.']);
		$markerArray['###NEWS_TIME###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['time_stdWrap.']);
		$markerArray['###NEWS_AGE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['age_stdWrap.']);
		$markerArray['###TEXT_NEWS_AGE###'] = $this->local_cObj->stdWrap($this->pi_getLL('textNewsAge'), $lConf['textNewsAge_stdWrap.']);

if ($this->config['croppingLenght']){
$lConf['subheader_stdWrap.']['crop'] = $this->config['croppingLenght'];
}

		$markerArray['###NEWS_SUBHEADER###'] = $this->formatStr($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));

		$markerArray['###NEWS_CONTENT###'] = $this->formatStr($this->local_cObj->stdWrap($row['bodytext'], $lConf['content_stdWrap.']));


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
		if ($relatedNews) {

			$rel_stdWrap = t3lib_div::trimExplode('|',$this->conf['related_stdWrap.']['wrap']);
			$markerArray['###TEXT_RELATED###'] = $rel_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textRelated'), $this->conf['relatedHeader_stdWrap.']);

			$markerArray['###NEWS_RELATED###'] = $relatedNews.$rel_stdWrap[1];
		} else {
 			$markerArray['###TEXT_RELATED###'] = '';
  			$markerArray['###NEWS_RELATED###'] = '';
		}

		// Links
		if ($row['links']) {
			$links_stdWrap = t3lib_div::trimExplode('|',$lConf['links_stdWrap.']['wrap']);
		    $newsLinks = $this->local_cObj->stdWrap($this->formatStr($row['links']), $lConf['linksItem_stdWrap.']);
			$markerArray['###TEXT_LINKS###'] = $links_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textLinks'), $lConf['linksHeader_stdWrap.']);
			$markerArray['###NEWS_LINKS###'] = $newsLinks.$links_stdWrap[1];
		} else {
 			$markerArray['###TEXT_LINKS###'] = '';
  			$markerArray['###NEWS_LINKS###'] = '';
		}

		// filelinks
		if ($row['news_files']) {
			$files_stdWrap = t3lib_div::trimExplode('|',$this->conf['newsFiles_stdWrap.']['wrap']);
			$markerArray['###TEXT_FILES###'] = $files_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textFiles'), $this->conf['newsFilesHeader_stdWrap.']);
			$fileArr = explode(',', $row['news_files']);
			$files = '';
			while (list(, $val) = each($fileArr)) {
				// fills the marker ###FILE_LINK### with the links to the atached files
				$filelinks .= $this->local_cObj->filelink($val, $this->conf['newsFiles.']) ;
			}
			$markerArray['###FILE_LINK###'] = $filelinks.$files_stdWrap[1];
		} else {
			// no files atached
			$markerArray['###TEXT_FILES###'] = '';
			$markerArray['###FILE_LINK###'] = '';
		}
		// the both markers: ###ADDINFO_WRAP_B### and ###ADDINFO_WRAP_E### are only inserted, if there are any files, related news or links
		if ($relatedNews||$row['links']||$row['news_files']) {
			$addInfo_stdWrap =  t3lib_div::trimExplode('|',$lConf['addInfo_stdWrap.']['wrap']);
		    $markerArray['###ADDINFO_WRAP_B###'] = $addInfo_stdWrap[0];
			$markerArray['###ADDINFO_WRAP_E###'] = $addInfo_stdWrap[1];
		} else {
 			$markerArray['###ADDINFO_WRAP_B###'] = '';
  			$markerArray['###ADDINFO_WRAP_E###'] = '';
		}

		// Page fields:
		$markerArray['###PAGE_UID###'] = $row['pid'];
		$markerArray['###PAGE_TITLE###'] = $this->pageArray[$row['pid']]['title'];
		$markerArray['###PAGE_AUTHOR###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['author'], $lConf['author_stdWrap.']);
		$markerArray['###PAGE_AUTHOR_EMAIL###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['author_email'], $lConf['email_stdWrap.']);
		// XML
		if ($this->theCode == 'XML') {
		 	$markerArray['###NEWS_TITLE###'] = $this->cleanXML($this->local_cObj->stdWrap($row['title'], $lConf['title_stdWrap.']));
			$markerArray['###NEWS_SUBHEADER###'] = $this->cleanXML($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));
			$markerArray['###NEWS_AUTHOR###'] = $row['author_email']?'<author>'.$row['author_email'].'</author>':'';
			$markerArray['###NEWS_DATE###'] = date('r', $row['datetime']);
		}
		// get markers and links for categories
		$markerArray = $this->getCatMarkerArray($markerArray, $row, $lConf);
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

		if (isset($this->categories[$row['uid']]) && ($this->config['catImageMode'] || $this->config['catTextMode'])) {
			// wrap for all categories
			$cat_stdWrap = t3lib_div::trimExplode('|',$lConf['category_stdWrap.']['wrap']);
			$markerArray['###CATWRAP_B###'] = $cat_stdWrap[0];
			$markerArray['###CATWRAP_E###'] = $cat_stdWrap[1];
			$markerArray['###TEXT_CAT###'] = $this->pi_getLL('textCat');
			$markerArray['###TEXT_CAT_LATEST###'] = $this->pi_getLL('textCatLatest');

			$news_category = array();
			$theCatImgCode = '';
			$theCatImgCodeArray = array();
			while (list ($key, $val) = each ($this->categories[$row['uid']])) {
				// find categories, wrap them with links and collect them in the array $news_category.
				$catTitle = $this->categories[$row['uid']][$key]['title'];
				if ($this->config['catTextMode'] == 0) {
					$markerArray['###NEWS_CATEGORY###'] = '';
				} elseif ($this->config['catTextMode'] == 1) {
					// display but don't link
					$news_category[] = $catTitle;
				} elseif ($this->config['catTextMode'] == 2) {
					// link to category shortcut
					$news_category[] = $this->pi_linkToPage($catTitle, $this->categories[$row['uid']][$key]['shortcut'], $this->categories[$row['uid']][$key]['shortcut_target']);
				} elseif ($this->config['catTextMode'] == 3) {
					// act as category selector
					$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
					$news_category[] = $this->pi_linkTP_keepPIvars($catTitle, array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, 'pointer' => null), '', '', $catSelLinkParams);
				}

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
							$catPicConf['image.']['altText'] = $sCpage['title']?$this->pi_getLL('altTextCatShortcut') . $sCpage['title']:'';
							$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkToPage('|', $this->categories[$row['uid']][$key]['shortcut'], ($catLinkTarget?$catLinkTarget:$this->config['itemLinkTarget']));
						}
						if ($this->config['catImageMode'] == 3) {
							// act as category selector
							$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
							$catPicConf['image.']['altText'] = $this->pi_getLL('altTextCatSelector') . $catTitle;
							$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkTP_keepPIvars('|', array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, 'pointer' => null), '', '', $catSelLinkParams);
						}
					} else {
						$catPicConf['image.']['altText'] = $this->categories[$row['uid']][$key]['title'];
					}
					// add linked category image to output array
					$theCatImgCodeArray[] = $this->local_cObj->IMAGE($catPicConf['image.']);
				}
				// Load the uid of the last assigned category to the register 'newsCategoryUid'
				$this->local_cObj->LOAD_REGISTER(array('newsCategoryUid' => $this->categories[$row['uid']][$key]['catid']), '');
			}

			if ($this->config['catTextMode'] != 0) {
				$news_category = implode(', ', array_slice($news_category, 0, intval($this->config['maxCatTexts'])));
				if ($this->config['catTextLength']) {
					// crop the complete category titles if 'catTextLength' value is given
					$markerArray['###NEWS_CATEGORY###'] = (strlen($news_category) < intval($this->config['catTextLength'])?$news_category:substr($news_category, 0, intval($this->config['catTextLength'])) . '...');
				} else {
					$markerArray['###NEWS_CATEGORY###'] = $this->local_cObj->stdWrap($news_category, $lConf['categoryItem_stdWrap.']);
				}
			}
			if ($this->config['catImageMode'] != 0) {
				$theCatImgCode = implode('', array_slice($theCatImgCodeArray, 0, intval($this->config['maxCatImages']))); // downsize the image array to the 'maxCatImages' value
				$markerArray['###NEWS_CATEGORY_IMAGE###'] = $this->local_cObj->stdWrap($theCatImgCode, $lConf['categoryItem_stdWrap.']);
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
		if ($this->config['FFimgH']||$this->config['FFimgW']) {
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
			// unset the img in the image array in single view if the var firstImageIsPreview is set
			if (count($imgs) > 1 && $this->config['firstImageIsPreview'] && $textRenderObj == 'displaySingle') {
				$imageNum++;
				unset($imgs[0]);
				unset($imgsCaptions[0]);
				unset($imgsAltTexts[0]);
				unset($imgsTitleTexts[0]);
				$cc = 1;
			}
			while (list(, $val) = each($imgs)) {
				if ($cc == $imageNum) break;
				if ($val) {

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
	 * Find related news records, add links to them and wrap them with stdWraps from TS.
	 *
	 * @param	integer		$uid : it of the current news item
	 * @return	string		html code for the related news list
	 */
	function getRelated($uid) {
		$select_fields = 'uid,title,short,datetime,archivedate,type,page,ext_url';
		$lConf = $this->conf['getRelatedCObject.'];
		if ($lConf['groupBy']) {
			$groupBy = trim($lConf['groupBy']);
		}
		if ($lConf['orderBy']) {
			$orderBy = trim($lConf['orderBy']);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, 'tt_news,tt_news_related_mm AS M', 'tt_news.uid=M.uid_foreign AND M.uid_local=' . intval($uid).$this->enableFields, $groupBy, $orderBy);
		if ($res) {
			$veryLocal_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
			$lines = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$veryLocal_cObj->start($row, 'tt_news');
				if (!$row['type']) {
					// normal news
					$queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET'])) ;
					// debug ($GLOBALS['TSFE']->id);
					if ($queryString) {
						while (list(, $val) = each($queryString)) {
							$tmp = explode('=', $val);
							// debug($tmp);
							$paramArray[$tmp[0]] = $val;
						}

						$excludeList = 'id,tx_ttnews[tt_news],tx_ttnews[backPid],L';
						while (list($key, $val) = each($paramArray)) {
							if (!$val || ($excludeList && t3lib_div::inList($excludeList, $key))) {
								unset($paramArray[$key]);
							}
						}
						// $paramArray['id']='id='.$GLOBALS['TSFE']->id;
						$paramArray['tx_ttnews[tt_news]'] = 'tx_ttnews[tt_news]=' . $row['uid'];

						if (!$this->conf['dontUseBackPid']) {
							$paramArray['tx_ttnews[backPid]'] = 'tx_ttnews[backPid]=' . $this->config['backPid'];
						}


						$newsAddParams = '&' . implode($paramArray, '&');
						// debug ($newsAddParams);
					}
					// load the parameter string into the register 'newsAddParams' to access it from TS
					$veryLocal_cObj->LOAD_REGISTER(array('newsAddParams' => $newsAddParams), '');
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

		return $altSPM?$altSPM:$subpartMarker;
	}

	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$searchword(s)
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
	 * build the XML header (array of markers to substitute)
	 *
	 * @return	array		the filled XML header markers
	 */
	function getXmlHeader() {
		$markerArray = array();

		$markerArray['###SITE_TITLE###'] = $this->conf['displayXML.']['xmlTitle'];
		$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];
		$markerArray['###SITE_DESCRIPTION###'] = $this->conf['displayXML.']['xmlDesc'];
		$markerArray['###SITE_LANG###'] = $this->conf['displayXML.']['xmlLang'];
		$markerArray['###IMG###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgFile = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgSize = is_file($imgFile)?getimagesize($imgFile):'';

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
			$markerArray['###NEWS_LASTBUILD###'] = '<lastBuildDate>' . date('r', $row['maxval']) . '</lastBuildDate>';
		} else {
			$markerArray['###NEWS_LASTBUILD###'] = '';
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

		return $markerArray;
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
			array('/&nbsp;/','/&;/'),
			array(' '.'&amp;;'),
			$str);
		return $cleanedStr;
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
// <<< td@krendls.eu; 02.04.2007; "BSG Training System" functions
	function displayTrainings($codes)
	{
		$this->templateCode = t3lib_div::getURL(t3lib_extMgm::extPath('bsg_training').'res/templates.html');
		$content = '';
		foreach($codes as $code)
		{
			$funcName = 'displayTrainings'.ucfirst(strtolower($code));
			if(method_exists($this, $funcName))
			{
				$content .= $this->$funcName();
			}else{
				$content .= '<span title="'.$code.'">Wrong code given</span>';
			}
		}
		return $content;
	}
	function displayTrainingsSingle()
	{
		$content = '';
		$template = $this->cObj->getSubpart($this->templateCode, '###TRAINING_SINGLE###');

		// SELECT news item from DB
		$where = '`tt_news`.`tx_bsgtraining_is_traning`=1 AND `tt_news`.`uid`='.$this->tt_news_uid;
		$sqlResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', $where);

		if($GLOBALS['TYPO3_DB']->sql_num_rows($sqlResult) > 0)
		{
			$newsDB = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlResult);

			// get markers array
			$markersArray = $this->getTrainingMarkers($newsDB);
			$blocksMarkerArray = $this->getTrainingBlockMarkers($markersArray);
			$content = $this->cObj->substituteMarkerArrayCached($template, $markersArray, $blocksMarkerArray);
		}

		return $content;
	}
	function displayTrainingsList()
	{
		$content = '';
		if(isset($this->conf['trainingAllListPid']) && $this->conf['trainingAllListPid'] == $GLOBALS['TSFE']->page['uid'])
		{
			$content = $this->displayTrainingsListAll();
		}else{
			$content = $this->displayTrainingsListCity($GLOBALS['TSFE']->page['uid']);
		}
		return $content;
	}
	function getTrainingMarkers($trainingInfo)
	{
		$markers = array();
		$neededMarkers = array(
				'ITEM_TITLE'				=> 'title',
				'ITEM_PRESENTED_BY'			=> 'tx_bsgtraining_instructors',
				'ITEM_DESCRIPTION'			=> 'tx_bsgtraining_description',
				'ITEM_OUTLINE'				=> 'tx_bsgtraining_outline',
				'ITEM_OBJECTIVES'			=> 'tx_bsgtraining_objectives',
//				'ITEM_INSTRUCTOR_BIOGRAPHY'	=> 'tx_bsgtraining_instructors',
				'ITEM_TARGET_AUDIENCE'		=> 'tx_bsgtraining_target_audience',
				'ITEM_UNIQUE_VALUE'			=> 'tx_bsgtraining_unique_value',
				'ITEM_WILL_LEARN'			=> 'tx_bsgtraining_learn',
				'ITEM_PREREQUISITES'		=> 'tx_bsgtraining_prerequisites',
				'ITEM_INSTRUCTORS'			=> 'tx_bsgtraining_instructors',
				'ITEM_COURSE_TYPE'			=> 'tx_bsgtraining_course_type',
				'ITEM_DURATION'				=> 'uid',
//				'ITEM_DATE'					=> 'uid',
				'ITEM_DATE'					=> 'tx_bsgtraining_start_date',
				'ITEM_COST'					=> 'uid',
				'CITIES'					=> 'uid',
				'VIEW_DEMO'					=> 'tx_bsgtraining_demo_url',
			);
		foreach($neededMarkers as $marker => $value)
		{
			switch($marker)
			{
			case 'ITEM_PRESENTED_BY':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingInsrtuctors($trainingInfo[$value], true) : '';
				break;
/*			case 'ITEM_INSTRUCTOR_BIOGRAPHY':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) ? $this->getTrainingBiography($trainingInfo[$value]) : '';
				break;
*/			case 'ITEM_INSTRUCTORS':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingInsrtuctors($trainingInfo[$value], false) : '';
				break;
			case 'ITEM_COURSE_TYPE':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingType($trainingInfo[$value]) : '';
				break;
			case 'ITEM_DATE':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingDates($trainingInfo[$value]) : '';
				break;
			case 'ITEM_COST':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingCost($trainingInfo[$value]) : '';
				break;
/*			case 'CITY_NAME':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) ? $this->getTrainingCities($trainingInfo[$value]) : '';
				break;
*/			case 'VIEW_DEMO':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' && $trainingInfo[$value] != '' ? 'View Demo' : '&nbsp;';
				break;
			case 'ITEM_DURATION':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? $this->getTrainingDuration($trainingInfo[$value]) : '';
				break;
			default:$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) ? $trainingInfo[$value] : '';
			}
		}
		return $markers;
	}
	function getTrainingLinkMarkers($trainingInfo)
	{
		$markers = array();
		$neededMarkers = array(
				'LINK_ITEM'					=> 'uid',
				'LINK_CITY'					=> 'uid',
				'LINK_DEMO'					=> 'tx_bsgtraining_demo_url',
			);
		foreach($neededMarkers as $marker => $value)
		{
			switch($marker)
			{
			case 'LINK_ITEM':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) ? $this->getTrainingLink($trainingInfo[$value]) : array(0 => '', 1 => '');
				break;
			case 'LINK_DEMO':
				$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) && $trainingInfo[$value] != '' ? array(0 => "<a href='{$trainingInfo[$value]}'>", 1 => '</a>') : array(0 => '', 1 => '');
				break;
//			default:$markers['###'.$marker.'###'] = isset($trainingInfo[$value]) ? $trainingInfo[$value] : array(0 => '', 1 => '');
			}
		}
		return $markers;
	}
	function getTrainingInsrtuctors($instructorsList, $longText=false)
	{
		$result = '';
		$field = $longText == true ? 'description' : 'name';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field, 'tx_bsgtraining_instructors', "uid in ($instructorsList)");
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			while($instructor = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
				$result .= $instructor[$field]. ' / ';
			$result = substr($result, 0, -3);
		}
		return $result;
	}
	function getTrainingBiography($instructorsList)
	{
		$result = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('biography', 'tx_bsgtraining_instructors', 'uid IN ('.implode(',', $instructorsList).')');
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			$biographiesTemplate = $this->cObj->getSubpart($this->templateCode, '###INSTRUCTORS_BIOGRAPHY_BLOCK###');
			$biographyTemplate = $this->cObj->getSubpart($biographiesTemplate, '###INSTRUCTOR_BLOCK###');
			while($instructor = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
			{
				$result .= $this->cObj->substituteMarkerArrayCached($biographyTemplate, array('###ITEM_INSTRUCTOR_BIOGRAPHY###' => $instructor['biography']));
			}
			$result = $this->cObj->substituteMarkerArrayCached($biographiesTemplate, array(), array('###INSTRUCTOR_BLOCK###' => $result));
		}
		return $result;
	}
	function getTrainingType($courseTypeId)
	{
		$courseTitle = false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('`title`', '`tx_bsgtraining_course_types`', "`uid` = $courseTypeId");
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			$courseTitle = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$courseTitle = $courseTitle['title'];
		}
		return $courseTitle;
	}
	function getTrainingDates($trainingId)
	{
		return date('F j, Y', $trainingId);
	}
	function getTrainingCost($trainingId)
	{
		$content = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_bsgtraining_packages.*, tx_bsgtraining_city.info_page', 'tt_news_cat_mm, tx_bsgtraining_packages, tx_bsgtraining_city', "tt_news_cat_mm.uid_local = $trainingId AND tt_news_cat_mm.uid_foreign = tx_bsgtraining_city.news_category AND tx_bsgtraining_city.pricing = tx_bsgtraining_packages.uid");
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			$pricingSchemes = array();
			while ($pricingSchemes[] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res));
			array_pop($pricingSchemes);

			$duration = $this->getTrainingDuration($trainingId);

			foreach($pricingSchemes as $scheme)
			{
				if($this->conf['backPid'] == $scheme['info_page'])
				{
					$content = $this->getTrainingPricingScheme($scheme, $duration);
					break;
				}
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title', 'tx_bsgtraining_city', 'pricing = '.$scheme['uid']);
				$cityTitle = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$cityTitle = $cityTitle['title'];
				$content .= $cityTitle.'<br />';

				$content .= $this->getTrainingPricingScheme($scheme, $duration);
			}
		}
		return $content;
	}
	function getTrainingCities($trainingId)
	{
		$cityInfo = false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title, info_page', 'tx_bsgtraining_city, tt_news_cat_mm', 'tt_news_cat_mm.uid_local = '.$trainingId.' AND tt_news_cat_mm.uid_foreign = tx_bsgtraining_city.news_category');
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			while ($cityInfo[] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res));
			array_pop($cityInfo);
		}
		return $cityInfo;
	}
	function displayTrainingsListAll()
	{
		$template = $this->cObj->getSubpart($this->templateCode, '###TRAINING_LIST###');
		$content = '';

		$itemTemplate = $this->cObj->getSubpart($template, '###TRAINING_ITEM###');

		$sqlResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', '`tt_news`.`tx_bsgtraining_is_traning` = 1 AND `deleted`!=1 AND `hidden`!=1');

		if($GLOBALS['TYPO3_DB']->sql_num_rows($sqlResult) > 0)
		{
			$markers = '';
			while($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlResult))
			{
				$itemMarkers = $this->getTrainingMarkers($item);
				// get link markers
				$linksItems = $this->getTrainingLinkMarkers($item);
				$blockMarkers = $this->getTrainingBlockMarkers($itemMarkers);

				$itemsHTMLCodes .= $this->cObj->substituteMarkerArrayCached($itemTemplate, $itemMarkers, $blockMarkers, $linksItems);
			}
			$items['###TRAINING_ITEM###'] = $itemsHTMLCodes;
			$content = $this->cObj->substituteSubpart($template, '###TRAINING_ITEM###', $itemsHTMLCodes);
//			$content = $this->cObj->substituteMarkerArrayCached($template, array(), $items);
		}

		return $content;
	}
	function displayTrainingsListCity($cityPageId)
	{
		$template = $this->cObj->getSubpart($this->templateCode, '###TRAINING_CITY_LIST###');
		$content = '';

		$itemTemplate = $this->cObj->getSubpart($template, '###TRAINING_ITEM###');

		$sqlResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tt_news.*', '`tt_news`, `tt_news_cat_mm`, `tx_bsgtraining_city`', "tx_bsgtraining_city.info_page = $cityPageId AND tx_bsgtraining_city.news_category = tt_news_cat_mm.uid_foreign AND tt_news.uid = tt_news_cat_mm.uid_local AND tt_news.tx_bsgtraining_is_traning = 1 AND `tt_news`.`deleted` != 1 AND `tt_news`.`hidden` != 1");

//		var_dump($GLOBALS['TYPO3_DB']->lastBuildQuery());
		if($GLOBALS['TYPO3_DB']->sql_num_rows($sqlResult) > 0)
		{
			$startDate = 0;
			while($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlResult))
			{
				// get texts markers
				$itemMarkers = $this->getTrainingMarkers($item);
				// get link markers
				$linksItems = $this->getTrainingLinkMarkers($item);
				$blockMarkers = array('###DATE_HEADER###' => '');
				if($startDate != $item['tx_bsgtraining_start_date'])
				{
					$dateHeader = date('l, F j, Y, g:iA', $item['tx_bsgtraining_start_date']);
					if(isset($item['tx_bsgtraining_end_date']) && $item['tx_bsgtraining_end_date'] != '')
						$dateHeader .= date(' - g:i A', $item['tx_bsgtraining_end_date']);
					$blockMarkers['###DATE_HEADER###'] = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($template, '###DATE_HEADER###'), array('###DATE###' => $dateHeader), $blockMarkers, $linksItems);;
					$startDate = $item['tx_bsgtraining_start_date'];
				}

				$itemsHTMLCodes .= $this->cObj->substituteMarkerArrayCached($itemTemplate, $itemMarkers, $blockMarkers, $linksItems);
			}
			$items['###TRAINING_ITEM###'] = $itemsHTMLCodes;
			$content = $this->cObj->substituteSubpart($template, '###TRAINING_ITEM###', $itemsHTMLCodes);
//			$content = $this->cObj->substituteMarkerArrayCached($template, array(), $items);
		}

		return $content;
	}
	function getTrainingLink($trainingId)
	{
		return explode('12Link_to_training', $this->pi_linkTP_keepPIvars('12Link_to_training',	array('tt_news' => $trainingId, 'backPid' => ($this->conf['dontUseBackPid'] ? null : $this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0),$this->config['singlePid']));
	}
	function getTrainingBlockMarkers($singleMarkers)
	{
		$retMarkers = array();
		$markersRealations = array(
			'PRESENTED_BY_BLOCK'			=> 'ITEM_PRESENTED_BY',
			'DESCRIPTION_BLOCK'				=> 'ITEM_DESCRIPTION',
			'OUTLINE_BLOCK'					=> 'ITEM_OUTLINE',
			'OBJECTIVES_BLOCK'				=> 'ITEM_OBJECTIVES',
			'INSTRUCTORS_BIOGRAPHY_BLOCK'	=> 'ITEM_INSTRUCTOR_BIOGRAPHY', //!!!
			'TARGET_AUDIENCE_BLOCK'			=> 'ITEM_TARGET_AUDIENCE',
			'UNIQUE_VALUE_BLOCK'			=> 'ITEM_UNIQUE_VALUE',
			'WILL_LEARN_BLOCK'				=> 'ITEM_WILL_LEARN',
			'PREREQUISITES_BLOCK'			=> 'ITEM_PREREQUISITES',
			'CITES_LIST'					=> 'CITIES',
		);

		foreach($markersRealations as $blockMarker => $contentMarker)
		{
			$blockContent = '';
			switch($blockMarker)
			{
			case 'CITES_LIST':
				{
					if(isset($singleMarkers["###$contentMarker###"]))
					{
						$cityInfo = $this->getTrainingCities($singleMarkers["###$contentMarker###"]);
						if($cityInfo != false)
						{
							$cityTemplate = $this->cObj->getSubpart($this->templateCode, "###CITES_LIST###");
							foreach($cityInfo as $city)
							{
								$cityURL = $this->pi_getPageLink($city['info_page']);
								$linkMarkers = array(
									'###LINK_CITY###' => array("<a href='$cityURL'>", '</a>')
								);
								$blockContent .= $this->cObj->substituteMarkerArrayCached($cityTemplate, array("###CITY_NAME###" => $city['title']), array(), $linkMarkers);
							}
						}
					}
				}break;
			case 'INSTRUCTORS_BIOGRAPHY_BLOCK':
				{
					if(isset($singleMarkers["###$contentMarker###"]) && $singleMarkers["###$contentMarker###"] != '')
					{
						$instructorsId = explode(',', $singleMarkers["###$contentMarker###"]);
						$blockContent = $this->getTrainingBiography($instructorsId);
					}
				}break;
			default:
				{
					if(isset($singleMarkers["###$contentMarker###"]) && $singleMarkers["###$contentMarker###"] != '')
					{
						$blockTemplate = $this->cObj->getSubpart($this->templateCode, "###$blockMarker###");
						$blockContent = $this->cObj->substituteMarkerArrayCached($blockTemplate, array("###$contentMarker###" => $singleMarkers["###$contentMarker###"]));
					}
				}
			}
			$retMarkers['###'.$blockMarker.'###'] = $blockContent;
		}
		return $retMarkers;
	}
	function getTrainingPricingScheme($schemeInfo, $duration)
	{
		$content = '';
		$userGroup2price = array(
			$this->conf['trainingGovermentUsergroup']	=> '_goverment',
			$this->conf['trainingProfessionalUsergroup']=> '_professional',
			$this->conf['trainingMemberUsergroup']		=> '_member',
			0											=> '_public',
		);
		$birdRates = array(
			'sebr' => 'Super Early Bird Rate',
			'ebr' => 'Early Bird Rate',
			'losr' => 'Late/On-Site Rate');
		$curPricingGroups = array_intersect($GLOBALS['TSFE']->fe_user->groupData['uid'], array_flip($userGroup2price));

		if(count($curPricingGroups) == 0)
		{
			foreach($birdRates as $code => $text)
			{
				$startDate = isset($schemeInfo[$code.'_start']) && $schemeInfo[$code.'_start'] != '' ? date('j F', $schemeInfo[$code.'_start']) : false;
				$endDate = isset($schemeInfo[$code.'_end']) && $schemeInfo[$code.'_end'] != '' ? date('j F', $schemeInfo[$code.'_end']) : false;

				$date = '';
				if(!$startDate && $endDate){
					$date = " Until $endDate";
				}elseif ($startDate && $endDate){
					$date = " $startDate - $endDate";
				}elseif ($startDate && !$endDate){
					$date = ": $startDate - On-Site";
				}
			    $content .= "<strong>$text</strong>: \$".($schemeInfo[$code.$userGroup2price[0]]*$duration)."($text$date)<br />";
			}
		}else{
			foreach($curPricingGroups as $group)
			{
				foreach($birdRates as $code => $text)
				{
					$startDate = isset($schemeInfo[$code.'_start']) && $schemeInfo[$code.'_start'] != '' ? date('j F', $schemeInfo[$code.'_start']) : false;
					$endDate = isset($schemeInfo[$code.'_end']) && $schemeInfo[$code.'_end'] != '' ? date('j F', $schemeInfo[$code.'_end']) : false;

					$date = '';
					if(!$startDate && $endDate){
						$date = " Until $endDate";
					}elseif ($startDate && $endDate){
						$date = " $startDate - $endDate";
					}elseif ($startDate && !$endDate){
						$date = ": $startDate - On-Site";
					}
				    $content .= "<strong>$text</strong>: \$".($schemeInfo[$code.$userGroup2price[0]]*$duration)."($text$date)<br />";
				}
				$content .= "<br />";
				break;
			}
		}
		$content .= "<br />";
		return $content;
	}
	function getTrainingDuration($trainingId)
	{
		$duration = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DATEDIFF(FROM_UNIXTIME(tx_bsgtraining_end_date),FROM_UNIXTIME(tx_bsgtraining_start_date)) `diff`', '`tt_news`', "`uid` = $trainingId");
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			$duration = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$duration = $duration['diff']+1;
		}
		return $duration;

	}
// >>> td@krendls.eu
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_news/pi/class.tx_ttnews.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_news/pi/class.tx_ttnews.php']);
}

?>
