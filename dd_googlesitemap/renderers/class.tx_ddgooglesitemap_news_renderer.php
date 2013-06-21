<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Dmitry Dulepov <dmitry@typo3.org>
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
 * $Id: class.tx_ddgooglesitemap_news_renderer.php,v 1.1.1.1 2010/04/15 10:03:21 peimic.comprock Exp $
 */

require_once(t3lib_extMgm::extPath('dd_googlesitemap', 'renderers/class.tx_ddgooglesitemap_abstract_renderer.php'));

/**
 * This class contains a renderer for the 'news' sitemap.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ddgooglesitemap
 */
class tx_ddgooglesitemap_news_renderer extends tx_ddgooglesitemap_abstract_renderer {

	/**
	 * Creates end tags for this sitemap.
	 *
	 * @return string	End XML tags
	 * @see tx_ddgooglesitemap_abstract_renderer::getEndTags()
	 */
	public function getEndTags() {
		return '</urlset>';
	}

	/**
	 * Creates start tags for this sitemap.
	 *
	 * @return string	Start tags
	 * @see tx_ddgooglesitemap_abstract_renderer::getStartTags()
	 */
	public function getStartTags() {
		return '<?xml version="1.0" encoding="UTF-8"?>' . chr(10) .
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
			'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"' .
			'>' . chr(10);
	}

	/**
	 * Renders a single entry as a nomal sitemap entry.
	 *
	 * @param	string	$url	URL of the entry
	 * @param	int	$lastModification	News publication time (Unix timestamp)
	 * @param	string	$changeFrequency	Unused for news
	 * @param	string	$keywords	Keywords for this entry
	 * @return	string	Generated entry content
	 * @see tx_ddgooglesitemap_abstract_renderer::renderEntry()
	 */
	public function renderEntry($url, $lastModification = 0, $changeFrequency = '', $keywords = '') {
		$content = '<url>';
		$content .= '<loc>' . $url . '</loc>';
		// News must have a publication date, so we put this unconditionally!
		$content .= '<news:news>';
		$content .= '<news:publication_date>' . date('c', $lastModification) . '</news:publication_date>';
		if ($keywords) {
			$content .= '<news:keywords>' . htmlspecialchars($keywords) . '</news:keywords>';
		}
		$content .= '</news:news>';
		$content .= '</url>';

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/renderers/class.tx_ddgooglesitemap_news_renderer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap/renderers/class.tx_ddgooglesitemap_news_renderer.php']);
}

?>