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
 * Adds sponsor display capabilities to tt_news.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @version $Id: class.tx_newsfilelinkalt.php,v 1.1.1.1 2010/04/15 10:03:55 peimic.comprock Exp $
 */

require_once(PATH_tslib."class.tslib_pibase.php");

class tx_newsfilelinkalt extends tslib_pibase
{
	// Same as class name
	var $prefixId				= 'tx_newsfilelinkalt';
	// Path to this script relative to the extension dir.
	var $scriptRelPath			= 'class.tx_newsfilelinkalt.php';
	// The extension key.
	var $extKey					= 'news_filelink_alt';
	var $conf					= array();
	var $iconMatches			= array();
	var $nameMatches			= array();
	var $linkMatches			= array();
	var $iconDir				= 't3lib/gfx/fileicons/';
	
	function extraItemMarkerProcessor( $markerArray, $row, $lConf
		, $parentObject
	)
	{
		// news whole conf is needed because of size property isn't in $lConf
		$this->newsConf			= $parentObject->conf;

		// grab news_filelink_alt conf
		$this->conf				= $parentObject->conf[ 'news_filelink_alt.' ];

		//Initiate language
		$this->pi_loadLL();

		$markerArray[ '###FILE_LINK###' ] = $this->allInOne(
											$markerArray[ '###FILE_LINK###' ]
										);

		$markerArray[ '###NEWS_LINKS###' ] = $this->allInOne(
											$markerArray[ '###NEWS_LINKS###' ]
										);

		return $markerArray;
	}

	/**
	 * All in one pretty printing.
	 *
	 * @param string link strings
	 * @return string
	 */
	function allInOne( $filelinkText )
	{
		$this->nameMatches( $filelinkText );
		// cbDebug( 'nameMatches', $this->nameMatches );	

		$this->iconMatches( $filelinkText );
		// cbDebug( 'iconMatches', $this->iconMatches );	

		$this->linkMatches( $filelinkText );
		// cbDebug( 'linkMatches', $this->linkMatches );	

		$this->removeFilesize( $filelinkText );
		// cbDebug( 'removeFilesize', $filelinkText );	
		
		if ( $this->conf[ 'iconSpace' ] )
		{
			$this->iconSpace( $filelinkText );
			// cbDebug( 'iconSpace', $filelinkText );	
		}

		if ( $this->conf[ 'prettyPrint' ] && ! $this->conf[ 'altFilename' ] )
		{
			$this->prettyPrint( $filelinkText );
			// cbDebug( 'prettyPrint', $filelinkText );	
		}

		if ( $this->conf[ 'altFilename' ] )
		{
			$this->altFilename( $filelinkText );
			// cbDebug( 'altFilename', $filelinkText );	
		}

		if ( $this->conf[ 'addIcon' ] )
		{
			$this->addIcon( $filelinkText );
			// cbDebug( 'addIcon', $filelinkText );	
		}

		if ( $this->conf[ 'icon2text' ] && ! $this->conf[ 'addIcon' ] )
		{
			$this->icon2text( $filelinkText );
			// cbDebug( 'icon2text', $filelinkText );	
		}

		if ( $this->conf[ 'removePWrap' ] )
		{
			$this->removePWrap( $filelinkText );
			// cbDebug( 'removePWrap', $filelinkText );	
		}

		$this->noIconBorder( $filelinkText );
		// cbDebug( 'noIconBorder', $filelinkText );	

		return $filelinkText;
	}

	/**
	 * Remove p tag wrapper
	 *
	 * @param array marker array
	 * @return void
	 */
	function removePWrap( & $marker )
	{
		$search					= '#<p[^>]*>#i';
		$replace				= '';
		$marker					= preg_replace( $search, $replace, $marker );

		$search					= '#</p>#i';
		$marker					= preg_replace( $search, $replace, $marker );
	}

	/**
	 * Create lead icon if none
	 *
	 * @param array marker array
	 * @return void
	 */
	function addIcon( & $marker )
	{
		$matches				= $this->linkMatches;

		if ( 1 > count( $matches ) )
		{
			return;
		}

		$match0					= $matches[ 0 ];
		$match2					= $matches[ 2 ];

		$match0Count			= count( $match0 );

		for ( $i = 0; $i < $match0Count; $i++ )
		{
			// look to see if icon exists already
			if ( $this->iconMatches[ 2 ][ $i ] )
			{
				continue;
			}

			// get link extension
			$extension			= preg_replace( '#.*\.([a-z0-9]{3,4})\b.*#i'
									, '\1'
									, basename( $match2[ $i ] )
								);

			// check for extension gif
			$iconPath			= $this->iconDir . $extension . '.gif';

			if ( false && ! is_file( '../../../' . $iconPath ) )
			{
				continue;
			}

			// prepend icon

			$search				= $match0[ $i ];

			$replace			= '<img src="' . $iconPath 
									. '" alttext="'
									.$this->pi_getLL( 'icon-' . $extension )
									. '"> '
									. $search;
			$marker				= str_replace( $search
									, $replace
									, $marker
								);
		}
	}

	/**
	 * Add space after icon before file link
	 *
	 * @param array marker array
	 * @return void
	 */
	function iconSpace( & $marker )
	{
		// lookup icon between <a ...> and </a> or standalone in marker
		// add space
		$search					= '#(<a[^>]+>)?(<img[^>]+>)(</a>)?#i';
		$replace				= '\1\2\3 ';

		$marker					= preg_replace( $search, $replace, $marker );
	}

	/**
	 * Remove icon border
	 *
	 * @param array marker array
	 * @return void
	 */
	function noIconBorder( & $marker )
	{
		// set no bordeer
		$search					= '# border="0"#i';
		$replace				= ' style="border: 0;"';

		$marker					= preg_replace( $search, $replace, $marker );
	}

	/**
	 * Remove filesize if needed
	 *
	 * @param array marker array
	 * @return void
	 */
	function removeFilesize( & $marker )
	{
		// lookup file name between <a ...> and </a> in marker
		$matches				= $this->nameMatches;

		if ( 1 > count( $matches ) )
		{
			return;
		}

		// pretty print
		// put pretty print back into marker
		$match0					= $matches[ 0 ];
		$match1					= $matches[ 1 ];
		$match2					= $matches[ 2 ];
		$match3					= $matches[ 3 ];

		$match0Count			= count( $match0 );

		for ( $i = 0; $i < $match0Count; $i++ )
		{
			$extension			= array();
			@preg_match( "#(\.)([[:alpha:]]+$)#"
				, $match2[ $i ]
				, $extension
			);

			// check for extension
			$extension			= ( isset( $extension[ 2 ] ) )
									? $extension[ 2 ]
									: false;

			if ( $extension
				&& 'none' == $this->pi_getLL( 'filesize-' . $extension ) 
			)
			{
				$search			= $match0[ $i + 1 ];
				$replace1		= $match1[ $i + 1 ];
				// filesize is removed
				$replace2		= '';
				$replace3		= $match3[ $i + 1 ];
				$marker			= preg_replace( "#$search#"
									, $replace1 . $replace2 . $replace3
									, $marker
								);
			}
		}
	}

	/**
	 * Convert link file name to pretty printed name
	 *
	 * @param array marker array
	 * @return void
	 */
	function prettyPrint( & $marker )
	{
		// lookup file name between <a ...> and </a> in marker
		$matches				= $this->nameMatches;

		if ( 1 > count( $matches ) )
		{
			return;
		}

		// pretty print
		// put pretty print back into marker

		// original
		$match0					= $matches[ 0 ];

		// > endings
		$match1					= $matches[ 1 ];

		// content
		$match2					= $matches[ 2 ];

		// < beginnings
		$match3					= $matches[ 3 ];

		$match0Count			= count( $match0 );

		for ( $i = 0; $i < $match0Count; $i++ )
		{
			$search				= $match0[ $i ];
			$replace1			= $match1[ $i ];
			$replace2			= cbMkReadableStr( $match2[ $i ] );
			$replace2			= preg_replace( '#([0-9]+) ([0-9]+)#'
									, "$1.$2"
									, $replace2
								);
			$replace3			= $match3[ $i ];
			$marker				= preg_replace( "#$search#"
									, $replace1 . $replace2 . $replace3
									, $marker
								);
		}
	}

	/**
	 * Convert filelink icon to text.
	 *
	 * @param array marker array
	 * @return void
	 */
	function icon2text( & $marker )
	{
		// lookup icon between <a ...> and </a> or standalone in marker
		// add space
		// set no bordeer
		$matches				= $this->iconMatches;

		if ( 1 > count( $matches ) )
		{
			return;
		}

		$match0					= $matches[ 0 ];
		$match2					= $matches[ 2 ];

		$match0Count			= count( $match0 );

		for ( $i = 0; $i < $match0Count; $i++ )
		{
			$search				= $match0[ $i ];
			$extension			= basename( $match2[ $i ] );

			$replace			= $this->pi_getLL( 'icon-' . $extension );
			$marker				= preg_replace( "#$search#"
									, $replace
									, $marker
								);
		}
	}

	/**
	 * Search marker for links and save the results.
	 *
	 * @param string marker string
	 * @return void
	 */
	function linkMatches ( $marker )
	{
		$pattern				= '/(<a href=")([^"]+)("[^>]*>)/i';
		$matches				= array();

		preg_match_all( $pattern, $marker, $matches );

		$this->linkMatches		= $matches;
	}

	/**
	 * Search marker for link names and save the results.
	 *
	 * @param string marker string
	 * @return void
	 */
	function nameMatches ( $marker )
	{
		$pattern				= '/(>)([^>]+)(<)/';
		$matches				= array();

		preg_match_all( $pattern, $marker, $matches );

		$this->nameMatches		= $matches;
	}

	/**
	 * Search marker for icons and save the results.
	 *
	 * @param string marker string
	 * @return void
	 */
	function iconMatches ( $marker )
	{
		$pattern				= '#(<img src=")([^\.]+)(\.[^>]+>)#i';
		$matches				= array();

		preg_match_all( $pattern, $marker, $matches );

		$this->iconMatches		= $matches;
	}

	/**
	 * Convert link name to an alternative
	 *
	 * @param array marker array
	 * @return void
	 */
	function altFilename( & $marker )
	{
		// lookup icon between <a ...> and </a> or standalone in marker
		// add space
		// set no bordeer
		$matches				= $this->nameMatches;
		$iconMatches			= $this->iconMatches;

		$sizesOn				= ( isset(
									$this->newsConf[ 'newsFiles.' ][ 'size' ]
									)
								)
									? $this->newsConf[ 'newsFiles.' ][ 'size' ]
									: false;

		if ( 1 > count( $matches ) )
		{
			return;
		}

		$match0					= $matches[ 0 ];
		$match1					= $matches[ 1 ];
		$match2					= $matches[ 2 ];
		$match3					= $matches[ 3 ];
		$iconMatch				= $iconMatches[ 2 ];

		$match0Count			= count( $match0 );

		// since iconMatch doesn't take sizes into consideration a separate
		// counter is used to keep track of icon position
		$sizeCount				= 0;

		for ( $i = 0; $i < $match0Count; $i++ )
		{
			$search				= $match0[ $i ];
			$extension			= basename( $iconMatch[ $sizeCount ] );

			if ( ! $extension )
			{
				$extension		= "html";
			}

			$replace1			= $match1[ $i ];
			$replace2			= $this->pi_getLL( 'text-' . $extension );
			$replace3			= $match3[ $i ];
			$marker				= preg_replace( "#$search#"
									, $replace1 . $replace2 . $replace3
									, $marker
								);

			// second set of information is size info, need to skip or else it
			// goes away
			if ( $sizesOn )
			{
				$i++;
			}

			$sizeCount++;
		}
	}
}

// if ( defined( 'TYPO3_MODE' )
// 	&& $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_filelink_alt/class.tx_newsfilelinkalt.php' ]
// )
// {
// 	include_once(
// 		$TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/news_filelink_alt/class.tx_newsfilelinkalt.php' ]
// 	);
// }

?>
