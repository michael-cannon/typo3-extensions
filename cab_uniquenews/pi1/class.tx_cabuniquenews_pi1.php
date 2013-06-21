<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Martin-Pierre Frenette <typo3@cablan.net>
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
 * Plugin 'Unique news displayer' for the 'cab_uniquenews' extension.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');


              

class tx_cabuniquenews_pi1 extends tslib_pibase {
	var $prefixId = 'tx_cabuniquenews_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cabuniquenews_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cab_uniquenews';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */

     /**
      *  This hook is used to record the news which were displayed, in order 
      *  to exclude them if a "UNIQUE" code is used.
      * 
      */    
    function extraItemMarkerProcessor($markerArray, $row, $lConf, $news){
        
        $GLOBALS["tx_cabuniquenews_pi1"]["displayednews"][] = $row["uid"];
        
        
        return $markerArray;
        
    }
     /**
      *  This hook is used to process new codes in extensions. In our case, we 
      *  handle codes that start by UNIQUE_, by calling the displayList 
      * function with the UNIQUE_ code stripped out and the displayednews 
      * excluded.
      */    
    function extraCodesProcessor($news){
        if ( strpos( $news->theCode , 'UNIQUE_' ) === 0  )
        {
            $codes = explode( "_",    $news->theCode);
            $news->theCode = $codes[1];
            
            $excludeUid =    @implode(",",$GLOBALS["tx_cabuniquenews_pi1"]["displayednews"]);
            
            return $news->displayList($excludeUid );
            
        }
    }
    
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_uniquenews/pi1/class.tx_cabuniquenews_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_uniquenews/pi1/class.tx_cabuniquenews_pi1.php']);
}

?>