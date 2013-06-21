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
 * Plugin 'extraGlobalMarkerProcessor' for the 'cab_newsmultipleimages' extension.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_cabnewsmultipleimages_pi1 extends tslib_pibase {
	var $prefixId = 'tx_cabnewsmultipleimages_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cabnewsmultipleimages_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cab_newsmultipleimages';	// The extension key.
    var $uploadDir = 'uploads/tx_cabnewsmultipleimages/';
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
	}
    
    
    /**
     * Fills the image markers with data. if a userfunction is given in "imageMarkerFunc",
     * the marker Array is processed by this function.
     *
     * @param    array        $markerArray : partly filled marker array
     * @param    array        $row : result row for a news item
     * @param    array        $lConf : configuration for the current templatepart
     * @param    string        $textRenderObj : name of the template subpart
     * @return    array        $markerArray: filled markerarray
     */
    function getSingleImageMarkers($news, $markerArray, $row, $lConf, $imageindex) {
        // overwrite image sizes from TS with the values from the content-element if they exist.
      /*  if ($this->config['FFimgH'] || $this->config['FFimgW']) {
            $lConf['image.']['file.']['maxW'] = $this->config['FFimgW'];
            $lConf['image.']['file.']['maxH'] = $this->config['FFimgH'];
        }
        */
        unset($lConf['image.']);
        unset($lConf['image.']['file.']['maxW']);
        unset($lConf['image.']['file.']['maxH']);
        
        if ($this->conf['imageMarkerFunc']) {
            $markerArray = $this->userProcess('imageMarkerFunc', array($markerArray, $lConf));
        } else {
            $imgs = t3lib_div::trimExplode(',', $row['tx_cabnewsmultipleimages_directimages'], 1);
            
            //$imgsCaptions = explode(chr(10), $row['imagecaption']);
            $imgsAltTexts = explode(chr(10), $row['tx_cabnewsmultipleimages_directimages_alttext']);
            //$imgsTitleTexts = explode(chr(10), $row['imagetitletext']);

            reset($imgs);

            $cc = 0;
           
            // get img array parts for single view pages
            if ($this->piVars[$this->config['singleViewPointerName']]) {
                $spage = $this->piVars[$this->config['singleViewPointerName']];
                $astart = $imageNum*$spage;
                $imgs = array_slice($imgs,$astart,$imageNum);
                $imgsCaptions = array_slice($imgsCaptions,$astart,$imageNum);
                $imgsAltTexts = array_slice($imgsAltTexts,$astart,$imageNum);
                $imgsTitleTexts = array_slice($imgsTitleTexts,$astart,$imageNum);
            }
            
            if ( $imageindex < count( $imgs )  )
            {
                
                 $cc = $imageindex;
                 $val = $imgs[$cc];
                 
                
                if ($val) {
                    $lConf['image.']['altText'] = $imgsAltTexts[$cc];
                    //$lConf['image.']['titleText'] = $imgsTitleTexts[$cc];
                    $lConf['image.']['file'] = $this->uploadDir  . $val;
                }
                $theImgCode .= $news->local_cObj->IMAGE($lConf['image.']); //. $this->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
            }
            $markerArray['###NEWS_DIRECT_IMAGE'.($imageindex+1).'###'] = '';
            if (isset($theImgCode)) {
                $markerArray['###NEWS_DIRECT_IMAGE'.($imageindex+1).'###'] = $news->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
                
                //   echo '###NEWS_DIRECT_IMAGE'.($imageindex+1).'###'. $markerArray['###NEWS_DIRECT_IMAGE'.($imageindex+1).'###'] . "<br>";
             
            }
        }
        return $markerArray;
    }

    
    function extraItemMarkerProcessor($markerArray, $row, $lConf, $news){
        for ( $i = 0; $i < 5; $i++ )
        {
            $markerArray =  $this->getSingleImageMarkers(  $news, $markerArray, $row, $lConf, $i);
        }
        
        
               return $markerArray;
    }
}

/*
if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'] as $_classRef) {
                $_procObj = & t3lib_div::getUserObj($_classRef);
                $markerArray = $_procObj->extraItemMarkerProcessor
        
  */


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_newsmultipleimages/pi1/class.tx_cabnewsmultipleimages_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_newsmultipleimages/pi1/class.tx_cabnewsmultipleimages_pi1.php']);
}

?>