<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007-2008 Benjamin Mack (www.xnos.org) 
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * @author	Benjamin Mack (www.xnos.org) 
 * @subpackage	tx_seobasics
 * 
 * This package includes all functions for generating XML sitemaps
 */

require_once(PATH_t3lib.'class.t3lib_pagetree.php');

class tx_seobasics_sitemap {
	var $conf;


	/**
	 * Generates a XML sitemap from the page structure
	 *
	 * @param       string	the content to be filled, usually empty
	 * @param       array	additional configuration parameters
	 * @return      string	the XML sitemap ready to render
	 */
	function renderXMLSitemap($content, $conf) {
		$this->conf = $conf;
		$id = intval($GLOBALS['TSFE']->id);
		$depth = 50;


		// precedence: config.baseURL, the domain record, config.absRefPrefix
		$baseURL = $GLOBALS['TSFE']->baseUrl;
		if (!$baseURL) {
			$domainUid = $GLOBALS['TSFE']->findDomainRecord();
			if ($domainUid) {
				$domainRecord = $GLOBALS['TSFE']->sys_page->getRawRecord('sys_domain', $domainUid);
				if (count($domainRecord)) {
					$baseURL = $domainRecord['domainName'];
				}
				if ($baseURL && !strpos('://', $baseURL)) {
					$baseURL = 'http://'.$baseURL;
				}
			}
		}
		if (!$baseURL && $GLOBALS['TSFE']->absRefPrefix) {
			$baseURL = $GLOBALS['TSFE']->absRefPrefix;
		}
		if (!$baseURL) {
			die('Please add a Domain Record at the root of your TYPO3 site in your TYPO3 backend.');
		}

		// add appending slash
		if (substr($baseURL, -1) != '/') {
			$baseURL .= '/';
		}

			// -- do a 301 redirect to the "main" sitemap.xml if not already there
		if ($this->conf['redirectToMainSitemap'] && $baseURL) {
			$sitemapURL = $baseURL . 'sitemap.xml';
			$requestURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
			if ($requestURL != $sitemapURL && strpos($requestURL, 'sitemap.xml')) {
				header('Location: ' . t3lib_div::locationHeaderUrl($sitemapURL), true, 301);
			}
		}

			// Initializing the tree object
		$treeStartingRecord = $GLOBALS['TSFE']->sys_page->getRawRecord('pages', $id);


			// now we need to see if this page is a redirect from the parent page
			// and loop while parentid is not null and the parent is still a redirect
		$parentId = $treeStartingRecord['pid'];
		while ($parentId > 0) {
			$parentRecord = $GLOBALS['TSFE']->sys_page->getRawRecord('pages', $parentId);
	
			if ($parentRecord['doktype'] == 4 && ($parentRecord['shortcut'] == $id || $parentRecord['shortcut_mode'] > 0)) {
				$treeStartingRecord = $parentRecord;
				$parentId = $parentRecord['pid'];
				$id = $parentRecord['uid'];
			} else {
				break;
			}
		}

		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->addField('SYS_LASTCHANGED', 1);
		$tree->addField('crdate', 1);
		if ($this->conf['renderHideInMenu'] != 1) {
			$addWhere = ' AND doktype != 5 AND nav_hide = 0';
		}
		$tree->init('AND deleted = 0 AND no_search = 0 AND hidden = 0 AND (starttime = 0 || starttime > NOW()) AND (endtime = 0 || endtime < NOW()) AND doktype NOT IN (199, 254, 255) '.$addWhere);


			// create the tree from starting point
		$tree->getTree($id, $depth, '');

			// creating the XML output
		$content = '';
		$usedUrls = array();
		foreach ($tree->tree as $row) {
			$item = $row['row'];
			$conf = array(
				'parameter' => $item['uid']
			);
#			$link = $GLOBALS['TSFE']->tmpl->linkData($item, '', 0, '');
#			$url  = $link['totalURL'];
			$url  = $GLOBALS['TSFE']->cObj->typoLink_URL($conf);
			if ($item['doktype'] != 3 || strpos($url, '://') === false) {
				if (t3lib_div::isFirstPartOfStr($url, '/')) {
					$url = substr($url, 1);
				}
				$url = $baseURL . $url;
			}

			if (in_array($url, $usedUrls)) {
				continue;
			}
			$usedUrls[] = $url;

			$lastmod = ($item['SYS_LASTCHANGED'] ? $item['SYS_LASTCHANGED'] : $item['crdate']);

			// format date, see http://www.w3.org/TR/NOTE-datetime for possible formats
			// if version is php5 or higher, we use "c" for the complete datetime
			$timeident = (str_replace('.', '', phpversion()) >= 500 ? "c" : "Y-m-d");
			$lastmod = date($timeident, $lastmod);

			$content .= '
	<url>
		<loc>'.$url.'</loc>
		<lastmod>'.$lastmod.'</lastmod>
	</url>';
		}

		// see https://www.google.com/webmasters/tools/docs/en/protocol.html for complete format
		$content =
'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'.$content.'
</urlset>';

		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']) {
   include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seo_basics/class.tx_seobasics_sitemap.php']);
}
?>
