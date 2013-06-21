<?php

	/**
 	 * phpAdsNew helper for displaying ads in Typo3 templates
	 *
	 * @author Michael Cannon <mcannon@intercomos.com>
	 * @version $Id: display_ad.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
	 */

	require_once( dirname(__FILE__) . '/../../phpAdsNew/phpadsnew.inc.php');
	require_once( dirname(__FILE__) . '/ad.php');

	$GLOBALS['TSFE']->set_no_cache();

	$ad = new ad();
	$contentBefore = $this->cObjGetSingle($conf['cObj'], $conf['.cObj']);
	$content = $ad->show_ad($conf);
	$content = preg_replace("/'/", '"', $content);
	$content = $contentBefore . $content;
	$content = $this->stdWrap($content, $conf['stdWrap.']);

?>
