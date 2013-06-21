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
 * Page Footer
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id: class.pagefooter.php,v 1.1.1.1 2010/04/15 10:04:08 peimic.comprock Exp $
 */
class user_tqseo_pagefooter {

	/**
	 * Add Page Footer
	 *
	 * @param	string	$title	Default page title (rendered by TYPO3)
	 * @return	string			Modified page title
	 */
	public function main($title) {
		global $TSFE;

		// INIT
		$ret				= array();
		$tsSetup			= $TSFE->tmpl->setup;
		$tsServices			= array();

		if( !empty($tsSetup['plugin.']['tq_seo.']['services.']) ) {
			$tsServices = $tsSetup['plugin.']['tq_seo.']['services.'];
		}

		#########################################
		# GOOGLE ANALYTICS
		#########################################
		if( !empty($tsServices['googleAnalytics']) ) {
			$ret[] = '<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." :"http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost +"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("'.htmlspecialchars($tsServices['googleAnalytics']).'");
pageTracker._trackPageview();
} catch(err) {}</script>';
		}


		return implode("\n", $ret);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagefooter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagefooter.php']);
}

?>