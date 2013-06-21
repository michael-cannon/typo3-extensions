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
 * Page Title Changer
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id: class.pagetitle.php,v 1.1.1.1 2010/04/15 10:04:08 peimic.comprock Exp $
 */
class user_tqseo_pagetitle {

	/**
	 * Add SEO-Page Title
	 *
	 * @param	string	$title	Default page title (rendered by TYPO3)
	 * @return	string			Modified page title
	 */
	public function main($title) {
		global $TSFE;

		// INIT
		$ret				= $title;
		$rawTitel			= $TSFE->page['title'];
		$tsSetup			= $TSFE->tmpl->setup;
		$rootLine			= $TSFE->rootLine;
		$currentPid			= $TSFE->id;
		$skipPrefixSuffix	= false;
		$applySitetitle		= false;

		#######################################################################
		# RAW PAGE TITEL
		#######################################################################
		if(!empty($TSFE->page['tx_tqseo_pagetitle'])) {
			$ret = $TSFE->page['tx_tqseo_pagetitle'];

			// Add template prefix/suffix
			if(!empty($tsSetup['plugin.']['tq_seo.']['pageTitle.']['applySitetitleToPagetitle'])) {
				$applySitetitle = true;
			}

			$skipPrefixSuffix = true;
		}


		#######################################################################
		# PAGE TITEL PREFIX/SUFFIX
		#######################################################################
		 if(!$skipPrefixSuffix) {
			$pageTitelPrefix = false;
			$pageTitelSuffix = false;

			foreach($rootLine as $page) {
				switch( (int)$page['tx_tqseo_inheritance'] ) {
					case 0:
						###################################
						# Normal
						###################################
						if( !empty($page['tx_tqseo_pagetitle_prefix']) ) {
							$pageTitelPrefix = $page['tx_tqseo_pagetitle_prefix'];
						}

						if( !empty($page['tx_tqseo_pagetitle_suffix']) ) {
							$pageTitelSuffix = $page['tx_tqseo_pagetitle_suffix'];
						}

						if($pageTitelPrefix !== FALSE || $pageTitelSuffix !== FALSE) {
							// pagetitle found - break foreach
							break 2;
						}
						break;

					case 1:
						###################################
						# Skip
						# (don't herit from this page)
						###################################
						if( (int)$page['uid'] != $currentPid ) {
							continue 2;
						}

						if( !empty($page['tx_tqseo_pagetitle_prefix']) ) {
							$pageTitelPrefix = $page['tx_tqseo_pagetitle_prefix'];
						}

						if( !empty($page['tx_tqseo_pagetitle_suffix']) ) {
							$pageTitelSuffix = $page['tx_tqseo_pagetitle_suffix'];
						}

						break 2;
						break;
				}
			}

			// Apply prefix and suffix
			if($pageTitelPrefix !== FALSE || $pageTitelSuffix !== FALSE) {
				$ret = $rawTitel;

				if($pageTitelPrefix !== FALSE) {
					$ret = $pageTitelPrefix.' '.$ret;
				}

				if($pageTitelSuffix !== FALSE) {
					$ret .= ' '.$pageTitelSuffix;
				}

				if(!empty($tsSetup['plugin.']['tq_seo.']['pageTitle.']['applySitetitleToPrefixSuffix'])) {
					$applySitetitle = true;
				}
			}
		}

		#######################################################################
		# APPLY SITETITLE (from setup)
		#######################################################################
		if($applySitetitle) {
			// add overall pagetitel from template/ts-setup
			if(!empty($tsSetup['config.']['pageTitleFirst'])) {
				// suffix
				$ret .= ': '.$tsSetup['sitetitle'];
			} else {
				// prefix (default)
				$ret = $tsSetup['sitetitle'].': '.$ret;
			}
		}

		return $ret;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagetitle.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagetitle.php']);
}

?>