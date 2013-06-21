<?php

/**
 * phpAdsNew helper for displaying ads in Typo3 templates
 *
 * @author Michael Cannon <mcannon@intercomos.com>
 * @version $Id: user_ad.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

require_once( dirname( __FILE__ ) . '/../../phpAdsNew/phpadsnew.inc.php');

class user_ad
{
	var $cObj;

	function show_ad( $content, $conf )
	{
		static $phpAds_context;

		if (!isset($phpAds_context))
		{
			$phpAds_context = array();
		}

		$zone = 'zone:';
		$zone .= ( isset($conf['zone']) )
			? $conf['zone']
			: 0;
	
		$target = ( isset($conf['target']) )
			? $conf['target']
			: '_blank';
	
		$source = ( isset($conf['source']) )
			? $conf['source']
			: '';
	
		$phpAds_raw = view_raw ($zone, 0, $target, $source, '0', $phpAds_context);
		$phpAds_context[] = array('!=' => 'bannerid:'.$phpAds_raw['bannerid']);

		return $phpAds_raw['html'];
	}
}

?>
