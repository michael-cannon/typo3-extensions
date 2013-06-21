<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Dmitry Dulepov <dmitry@typo3.org>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * $Id: class.tx_ddgooglesitemap_eid.php,v 1.1.1.1 2010/04/15 10:03:21 peimic.comprock Exp $
 */

require_once(PATH_tslib . 'class.tslib_pagegen.php');
require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_t3lib . 'class.t3lib_userauth.php' );
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
require_once(PATH_t3lib . 'class.t3lib_cs.php');

/**
 * This class implements a Google sitemap.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class tx_ddgooglesitemap_eid {

	const	SITEMAP_TYPE_PAGES = 0;
	const	SITEMAP_TYPE_NEWS = 1;

	public function __construct() {
		@set_time_limit(300);
		$this->initTSFE();
	}

	/**
	 * Main function of the class. Outputs sitemap.
	 *
	 * @return	void
	 */
	public function main() {
		switch ($this->getSitemapType()) {
			case self::SITEMAP_TYPE_PAGES:
				$this->generatePagesSitemap();
				break;
			case self::SITEMAP_TYPE_NEWS:
				$this->generateNewsSitemap();
				break;
			default:
				$this->generateError();
		}
	}

	/**
	 * Sends error message.
	 *
	 * @return	void
	 */
	protected function generateError() {
		header('400 Bad request');
		echo 'The request cannot be understood.';
	}

	/**
	 * Generates sitemap for pages
	 *
	 * @return	void
	 */
	protected function generatePagesSitemap() {
		t3lib_div::requireOnce(t3lib_extMgm::extPath('dd_googlesitemap', 'class.tx_ddgooglesitemap_pages.php'));
		$generator = t3lib_div::makeInstance('tx_ddgooglesitemap_pages');
		/* @var $generator tx_ddgooglesitemap_pages */
		$generator->main();
	}

	/**
	 * Generates sitemap for news
	 *
	 * @return	void
	 */
	protected function generateNewsSitemap() {
		t3lib_div::requireOnce(t3lib_extMgm::extPath('dd_googlesitemap', 'class.tx_ddgooglesitemap_ttnews.php'));
		$generator = t3lib_div::makeInstance('tx_ddgooglesitemap_ttnews');
		/* @var $generator tx_ddgooglesitemap_ttnews */
		$generator->main();
	}

	/**
	 * Determines what sitemap we should send
	 *
	 * @return	int	One of SITEMAP_TYPE_xxx constants
	 */
	protected function getSitemapType() {
		$type = t3lib_div::_GP('sitemap');
		return ($type == 'news' ? self::SITEMAP_TYPE_NEWS : self::SITEMAP_TYPE_PAGES);
	}

	/**
	 * Initializes TSFE and sets $GLOBALS['TSFE']
	 *
	 * @return	void
	 */
	protected function initTSFE() {
		$tsfeClassName = t3lib_div::makeInstanceClassName('tslib_fe');

		$GLOBALS['TSFE'] = new $tsfeClassName($GLOBALS['TYPO3_CONF_VARS'], t3lib_div::_GP('id'), '');

		$initCache = !isset($GLOBALS['typo3CacheManager']) && version_compare(TYPO3_branch, '4.3', '>=');
		if ($initCache) {
			require_once(PATH_t3lib . 'class.t3lib_cache.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_abstractbackend.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_abstractcache.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_exception.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_factory.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_manager.php');
			require_once(PATH_t3lib . 'cache/class.t3lib_cache_variablecache.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_classalreadyloaded.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_duplicateidentifier.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_invalidbackend.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_invalidcache.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_invaliddata.php');
			require_once(PATH_t3lib . 'cache/exception/class.t3lib_cache_exception_nosuchcache.php');
			$GLOBALS['typo3CacheManager'] = t3lib_div::makeInstance('t3lib_cache_Manager');
			$cacheFactoryClass = t3lib_div::makeInstanceClassName('t3lib_cache_Factory');
			$GLOBALS['typo3CacheFactory'] = new $cacheFactoryClass($GLOBALS['typo3CacheManager']);
			unset($cacheFactoryClass);
			$GLOBALS['TSFE']->initCaches();
		}
		$GLOBALS['TSFE']->connectToMySQL();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->checkAlternativeIdMethods();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getCompressedTCarray();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();

		// Get linkVars, absRefPrefix, etc
		TSpagegen::pagegenInit();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_eid.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/class.tx_ddgooglesitemap_eid.php']);
}

$generator = t3lib_div::makeInstance('tx_ddgooglesitemap_eid');
/* @var $generator tx_ddgooglesitemap_eid */
$generator->main();

?>