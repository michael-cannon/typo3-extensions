<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
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
 * Metatags generator
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id: class.metatags.php,v 1.1.1.1 2010/04/15 10:04:08 peimic.comprock Exp $
 */
class user_tqseo_metatags {

	/**
	 * Add MetaTags
	 *
	 * @return	string			XHTML Code with metatags
	 */
	public function main() {
		global $TSFE;

		// INIT
		$ret		= array();
		$tsSetup	= $TSFE->tmpl->setup;
		$cObj		= $TSFE->cObj;
		$pageMeta	= array();
		$tsfePage	= $TSFE->page;

		if(!empty($tsSetup['plugin.']['tq_seo.']['metaTags.'])) {
			$tsSetupSeo = $tsSetup['plugin.']['tq_seo.']['metaTags.'];

			#####################################
			# FETCH METADATA FROM PAGE
			#####################################

			// description
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['description_page'], $tsSetupSeo['conf.']['description_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['description'] = $tmp;
			}

			// keywords
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['keywords_page'], $tsSetupSeo['conf.']['keywords_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['keywords'] = $tmp;
			}

			// title
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['title_page'], $tsSetupSeo['conf.']['title_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['title'] = $tmp;
			}

			// author
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['author_page'], $tsSetupSeo['conf.']['author_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['author'] = $tmp;
			}

			// email
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['email_page'], $tsSetupSeo['conf.']['email_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['email'] = $tmp;
			}

			// last-update
			$tmp = $cObj->stdWrap( $tsSetupSeo['conf.']['lastUpdate_page'], $tsSetupSeo['conf.']['lastUpdate_page.'] );
			if( !empty($tmp) ) {
				$pageMeta['lastUpdate'] = $tmp;
			}

			// language
			if( !empty($tsSetupSeo['useDetectLanguage'])
				&& !empty( $tsSetup['config.']['language'] ) ) {
				$pageMeta['language'] = $tsSetup['config.']['language'];
			}

			// process page meta data
			foreach($pageMeta as $metaKey => $metaValue) {
				$metaValue = trim($metaValue);

				if( !empty($metaValue) ) {
					$tsSetupSeo[$metaKey] = $metaValue;
				}
			}

			#####################################
			# PAGE META
			#####################################

			// title
			if( !empty($tsSetupSeo['title']) ) {
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.title" content="'.htmlspecialchars($tsSetupSeo['title']).'" />';
			}

			// description
			if( !empty($tsSetupSeo['description']) ) {
				$ret[] = '<meta name="description" content="'.htmlspecialchars($tsSetupSeo['description']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Description" content="'.htmlspecialchars($tsSetupSeo['description']).'" />';
			}

			// keywords
			if( !empty($tsSetupSeo['keywords']) ) {
				$ret[] = '<meta name="keywords" content="'.htmlspecialchars($tsSetupSeo['keywords']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Subject" content="'.htmlspecialchars($tsSetupSeo['keywords']).'" />';
			}

			// copyright
			if( !empty($tsSetupSeo['copyright']) ) {
				$ret[] = '<meta name="copyright" content="'.htmlspecialchars($tsSetupSeo['copyright']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Rights" content="'.htmlspecialchars($tsSetupSeo['copyright']).'" />';
			}

			// language
			if( !empty($tsSetupSeo['language']) ) {
				$ret[] = '<meta http-equiv="content-language" content="'.htmlspecialchars($tsSetupSeo['language']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Language" scheme="NISOZ39.50" content="'.htmlspecialchars($tsSetupSeo['language']).'" />';
			}

			// email
			if( !empty($tsSetupSeo['email']) ) {
				$ret[] = '<link rev="made" href="mailto:'.htmlspecialchars($tsSetupSeo['email']).'" />';
				$ret[] = '<meta http-equiv="reply-to" content="'.htmlspecialchars($tsSetupSeo['email']).'" />';
			}

			// author
			if( !empty($tsSetupSeo['author']) ) {
				$ret[] = '<meta name="author" content="'.htmlspecialchars($tsSetupSeo['author']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Creator" content="'.htmlspecialchars($tsSetupSeo['author']).'" />';
			}

			// author
			if( !empty($tsSetupSeo['publisher']) ) {
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.Publisher" content="'.htmlspecialchars($tsSetupSeo['publisher']).'" />';
			}

			// distribution
			if( !empty($tsSetupSeo['distribution']) ) {
				$ret[] = '<meta name="distribution" content="'.htmlspecialchars($tsSetupSeo['distribution']).'" />';
			}

			// rating
			if( !empty($tsSetupSeo['rating']) ) {
				$ret[] = '<meta name="rating" content="'.htmlspecialchars($tsSetupSeo['rating']).'" />';
			}

			// last-update
			if( !empty($tsSetupSeo['useLastUpdate']) && !empty($tsSetupSeo['lastUpdate']) ) {
				$ret[] = '<meta name="date" content="'.htmlspecialchars($tsSetupSeo['lastUpdate']).'" />';
				if( $tsSetupSeo['enableDC'] )
					$ret[] = '<meta name="DC.date" content="'.htmlspecialchars($tsSetupSeo['lastUpdate']).'" />';
			}

			#####################################
			# CRAWLER ORDERS
			#####################################

			// robots
			$crawlerOrder = array();
			if( !empty($tsSetupSeo['robotsIndex']) && empty($tsfePage['tx_tqseo_is_exclude']) ) {
				$crawlerOrder['index'] = 'index';
			} else {
				$crawlerOrder['index'] = 'noindex';
			}

			if( !empty($tsSetupSeo['robotsFollow']) ) {
				$crawlerOrder['follow'] = 'follow';
			} else {
				$crawlerOrder['follow'] = 'nofollow';
			}

			if( empty($tsSetupSeo['robotsArchive']) ) {
				$crawlerOrder['archive'] = 'noarchive';
			}

			$ret[] = '<meta name="robots" content="'.implode(',',$crawlerOrder).'" />';

			// revisit
			if( !empty($tsSetupSeo['revisit']) ) {
				$ret[] = '<meta name="revisit-after" content="'.htmlspecialchars($tsSetupSeo['revisit']).'" />';
			}

			#####################################
			# GEO POSITION
			#####################################

			// Geo-Position
			if( !empty($tsSetupSeo['geoPositionLatitude']) && !empty($tsSetupSeo['geoPositionLongitude']) ) {
				$ret[] = '<meta name="ICBM" content="'.htmlspecialchars($tsSetupSeo['geoPositionLatitude']).', '.htmlspecialchars($tsSetupSeo['geoPositionLongitude']).'" />';
				$ret[] = '<meta name="geo.position" content="'.htmlspecialchars($tsSetupSeo['geoPositionLatitude']).';'.htmlspecialchars($tsSetupSeo['geoPositionLongitude']).'" />';
			}

			// Geo-Region
			if( !empty($tsSetupSeo['geoRegion']) ) {
				$ret[] = '<meta name="geo.region" content="'.htmlspecialchars($tsSetupSeo['geoRegion']).'" />';
			}

			// Geo Placename
			if( !empty($tsSetupSeo['geoPlacename']) ) {
				$ret[] = '<meta name="geo.placename" content="'.htmlspecialchars($tsSetupSeo['geoPlacename']).'" />';
			}

			#####################################
			# MISC (Vendor specific)
			#####################################

			// Google Verification
			if( !empty($tsSetupSeo['googleVerification']) ) {
				$ret[] = '<meta name="verify-v1" content="'.htmlspecialchars($tsSetupSeo['googleVerification']).'" />';
			}

			// MSN Verification
			if( !empty($tsSetupSeo['msnVerification']) ) {
				$ret[] = '<meta name="msvalidate.01" content="'.htmlspecialchars($tsSetupSeo['msnVerification']).'" />';
			}

			// Yahoo Verification
			if( !empty($tsSetupSeo['yahooVerification']) ) {
				$ret[] = '<meta name="y_key" content="'.htmlspecialchars($tsSetupSeo['yahooVerification']).'" />';
			}


			// PICS label
			if( !empty($tsSetupSeo['picsLabel']) ) {
				$ret[] = '<meta http-equiv="PICS-Label" content="'.htmlspecialchars($tsSetupSeo['picsLabel']).'" />';
			}

			#####################################
			# OTHERS (generated tags)
			#####################################

			// Canonical
			if(!empty($tsSetupSeo['useCanonical']) && empty($TSFE->cHash)) {
				$linkConf = array(
					'parameter'	=> $TSFE->id,
				);

				$pageUrl = t3lib_div::locationHeaderUrl( $TSFE->cObj->typoLink_URL($linkConf) );

				$ret[] = '<link rel="canonical" href="'.htmlspecialchars($pageUrl).'" />';
			}
		}

		$seperator = "\n	";

		return $seperator.'<!-- MetaTags :: begin -->'.$seperator.implode($seperator, $ret).$seperator.'<!-- MetaTags :: end -->'.$seperator;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.metatags.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.metatags.php']);
}

?>