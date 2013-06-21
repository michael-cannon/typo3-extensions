<?php

/***************************************************************
*  Copyright notice
*  
*  (c) 2005 Michael Cannon <michael@peimic.com>
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
 * Use news category image as default news image when news item has no image.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newsdefaultimage.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */

require_once(PATH_tslib."class.tslib_pibase.php");

class tx_newsdefaultimage extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newsdefaultimage';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newsdefaultimage.php';
	// The extension key.
	var $extKey					= 'news_default_image';
	var $conf					= array();
	
	function extraItemMarkerProcessor( $markerArray, $row, $lConf
		, $parentObject
	)
	{
		// news whole conf is needed because of size property isn't in $lConf
		$this->newsConf			= $parentObject->conf;

		// grab news_default_image conf
		$this->conf				= $parentObject->conf[ 'news_default_image.' ];

		//Initiate language
		$this->pi_loadLL();

		if ( '' == $markerArray[ '###NEWS_IMAGE###' ]
			&& $this->conf[ 'useCategoryImage' ]
		)
		{
			$catImage			= $markerArray[ '###NEWS_CATEGORY_IMAGE###' ];
			$search				= '#border="\d+"#';
			$replace			= '\1 '
									. $this->conf[ "categoryImageParams" ];
			$catImage			= preg_replace( $search, $replace, $catImage );

			$markerArray[ '###NEWS_IMAGE###' ]			= $catImage;
			$markerArray[ '###NEWS_CATEGORY_IMAGE###' ]	= $catImage;
		}

		return $markerArray;
	}
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_default_image/class.tx_newsdefaultimage.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_default_image/class.tx_newsdefaultimage.php' ]
// 	);
// }

?>
