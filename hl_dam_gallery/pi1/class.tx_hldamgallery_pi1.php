<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Heiner Lamprecht <typo3@heiner-lamprecht.net>
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
 * Plugin 'DAM Show Image' for the 'hl_dam_gallery' extension.
 *
 * @author	Heiner Lamprecht <typo3@heiner-lamprecht.net>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_hldamgallery_pi1 extends tslib_pibase {
    var $prefixId = 'tx_hldamgallery_pi1';                      // Same as class name
    var $scriptRelPath = 'pi1/class.tx_hldamgallery_pi1.php';   // Path to this script relative to the extension dir.
    var $extKey = 'hl_dam_gallery';                             // The extension key.
    var $pi_checkCHash = TRUE;

    /**
     * The main method of the PlugIn
     *
     * @param   string      $content: The PlugIn content
     * @param   array       $conf: The PlugIn configuration
     * @return  The content that is displayed on the website
     */
    function main($content, $conf) {
//         $this->pi_USER_INT_obj = 0;
        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        $galleryPID = intval($this->piVars['galleryPID']);
        $galleryCID = intval($this->piVars['galleryCID']);
        $imageOrderID = intval($this->piVars['imgID']);

        $hideMeta = 0;
        $hideNav = 0;

        if($galleryPID > 0 && $galleryCID > 0) {
            $GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
            $GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_hldamgallery_hidemeta, tx_hldamgallery_hidenav',
                'tt_content', 'uid=' . $galleryCID . ' and pid = ' . $galleryPID, '', '', '');

            if($res) {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $hideMeta = $row['tx_hldamgallery_hidemeta'];
                $hideNav = $row['tx_hldamgallery_hidenav'];
            }

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_dam_mm_ref.sorting, tx_dam.file_name, tx_dam.file_path, tx_dam.tx_hldamgallery_viewcount',
                'tx_dam_mm_ref, tx_dam',
                'tx_dam.uid=tx_dam_mm_ref.uid_local and tx_dam_mm_ref.uid_foreign=' . $galleryCID .
                ' and tx_dam_mm_ref.sorting = ' . $imageOrderID, '', '', '');

            $viewCount = 1;

            if($res) {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $this->file = $row['file_path'] . $row['file_name'];
                $viewCount = $row['tx_hldamgallery_viewcount'] + 1;

                $updateArray = array(
                    'tx_hldamgallery_viewcount' => $viewCount
                );
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_dam', 'file_name="' . $row['file_name'] .
                        '" and file_path="' . $row['file_path'] . '"', $updateArray);
            }

            // ========================================================================================================
            //
            // Creating meta data
            //
            $metaContent = '';
            $metaConn = '';

            if($this->conf['useIPTC']) {
                //
                // Using IPTC data, currently the fields are hardcoded.
                //
                $metaContent = htmlspecialchars($this->getIPTC($this->file));
                $conf['altText'] = $metaContent;
                $conf['titleText'] = $metaContent;

                $this->appendMetaTags($metaContent);

                $metaContent = '<div class="tx_hldamgallery_iptc">' . $metaContent . '</div>';
            } else {
                //
                // Reading meta data from DAM-table
                //

                //
                //
                $fileName = basename($this->file);
                $filePath = dirname($this->file) . '/';
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_dam',
                        "file_name='" . $fileName . "' and file_path='" . $filePath . "'", '', '', 1);

                $title = '';
                $caption = '';
                $imgUID = -1;

                $imgAltText = '';
                $imgTitleText = '';
                $keywords = '';

                if($res) {
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    $imgUID = intval($row['uid']);
					$row['title']	= $this->cbMkReadableStr($row['title']);
					// MLC set page title
					$GLOBALS['TSFE']->page['title'] = $row['title'];
                    $title = $this->prepareMetaEntry('showTitle', $row['title'], 'tx_hldamgallery_meta_title');
                    $imgTitleText = $row['title'];

                    $description = $this->prepareMetaEntry('showDescription', $row['description'], 'tx_hldamgallery_meta_desc');
                    $country = $this->prepareMetaEntry('showCountry', $row['loc_country'], 'tx_hldamgallery_meta_country');
                    $city = $this->prepareMetaEntry('showCity', $row['loc_city'], 'tx_hldamgallery_meta_city');
                    $location = $this->prepareMetaEntry('showLocDesc', $row['loc_desc'], 'tx_hldamgallery_meta_locdesc');
                    $caption = $this->prepareMetaEntry('showCaption', $row['caption'], 'tx_hldamgallery_meta_caption');
                    $keywords = $this->prepareMetaEntry('showKeywords', $row['keywords'], 'tx_hldamgallery_meta_keywords');
                    $this->appendMetaTags($row['keywords']);
                    $creator = $this->prepareMetaEntry('showCreator', $row['creator'], 'tx_hldamgallery_meta_creator');
                    $publisher = $this->prepareMetaEntry('showPublisher', $row['publisher'], 'tx_hldamgallery_meta_publisher');
                    $copyright = $this->prepareMetaEntry('showCopyright', $row['copyright'], 'tx_hldamgallery_meta_copyright');
                    $usage = $this->prepareMetaEntry('showUsage', $row['instructions'], 'tx_hldamgallery_meta_usage');

                    $imgAltText = $row['alt_text'];
                    $category = $row['category'];
                }

                $conf['altText'] = $imgAltText;
                $conf['titleText'] = $imgTitleText;

                if(intval($hideMeta) != 1) {
                    $metaContent = '
                        <div class="tx_hldamgallery_meta">' . $title . $caption . $description . $country . $city . $location .
                            $keywords . $creator . $publisher . $copyright . $usage . '
                        </div>';
                }
            }

            if($this->conf['showHits']) {
                $metaContent .= '
                    <div class="tx_hldamgallery_count">
                        ' . $viewCount . ' Hits
                    </div>';
            }

            $conf['file.'] = $this->cObj->image_compression[$this->cObj->data['image_compression']];

            list($width, $height, $type, $attr) = getImageSize($this->file);
            $orientation = ($width >= $height) ? 'landscape' : 'portrait';
            if($this->conf['shrinkImagesToWidth']) {
                $conf['file.']['maxW'] = $this->conf['shrinkImagesToWidth'];
                if($orientation == 'landscape') {
                    $conf['file.']['width'] = $this->conf['shrinkImagesToWidth'];
                } else {
                    $conf['file.']['height'] = $this->conf['shrinkImagesToWidth'];
                }
            }

            // ========================================================================================================
            //
            // Prepare img-Tag and back-link
            //
            $a1 = '<a href="'. htmlspecialchars($this->pi_getPageLink($galleryPID, '','')) . '" >';
            $a2 = '</a>';
            $backLink = $a1 . 'back' . $a2;

            $info = $this->cObj->getImgResource($this->file, $conf['file.']);
            $info[3] = t3lib_div::png_to_gif_by_imagemagick($info[3]);

            $altParam = $this->cObj->getAltParam($conf);
            $bigImageTag = '<img src="'.htmlspecialchars($GLOBALS['TSFE']->absRefPrefix.t3lib_div::rawUrlEncodeFP($info[3])) .
                    '" width="' . $info[0] . '" height="' . $info[1] . '"' .
                    $this->cObj->getBorderAttr(' border="' . intval($conf['border']).'"') . ($altParam) . ' />';

            $downloadLink = '';
            if($this->conf['addDownloadLink']) {
                $downloadLink = '<div class="tx_hldamgallery_download"><a href="' . $info['origFile'] . '"' .
                        $target.$GLOBALS['TSFE']->ATagParams . '>download original image</a></div>';
            }

            // ========================================================================================================
            //
            // Checking for other images in the same gallery
            //
            if(intval($hideNav) != 1) {
                $backLink = $a1 . $this->pi_getLL( 'back' ) . $a2;

                if($this->conf['numberOfThumbs']) {
                    $numberOfThumbs = intval($this->conf['numberOfThumbs.']);
                } else {
                    $numberOfThumbs = 2;
                }

                $linkToNextImage = '';

                $navArray = array();

                if($imgUID >= 0 && $numberOfThumbs > 0) {
                    $refreshTimeout = -1;

                    if($this->conf['slideShowTimeOut']) {
                        $refreshTimeout = intval($this->conf['slideShowTimeOut']);
                    }

                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                        'tx_dam_mm_ref.sorting, tx_dam.title, tx_dam.description, tx_dam.alt_text, tx_dam.keywords, tx_dam.file_name, tx_dam.file_path',
                        'tx_dam_mm_ref, tx_dam',
                        'tx_dam.uid=tx_dam_mm_ref.uid_local and tx_dam_mm_ref.uid_foreign=' . $galleryCID .
                        ' and abs(tx_dam_mm_ref.sorting - ' . $imageOrderID . ')<=' . $numberOfThumbs, '',
                        'tx_dam_mm_ref.sorting', $numberOfThumbs * 2 + 1);

                    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                        $imageFile = $row['file_path'] . $row['file_name'];

                        $img = array();
						$row['title']	= $this->cbMkReadableStr($row['title']);
                        if($this->conf['useIPTC']) {
                            $metaConn = htmlspecialchars($this->getIPTC($imageFile));
                            $img['altText'] = $metaConn;
                            $img['titleText'] = $metaConn;
                        } else {
                            $img['altText'] = strlen($row['alt_text']) > 0 ? $row['alt_text'] : $row['description'];
                            $img['titleText'] = strlen($row['title']) > 0 ? $row['title'] : $row['description'];
                        }

                        $this->appendMetaTags($row['keywords']);

                        $imgID = $row['sorting'];
                        $imgTag = $this->createImageTag($imageFile, $img);
                        $url = $this->createNavigationURL($galleryPID, $galleryCID, $imgID);
                        $imgLink = $this->createNavigationLink($url, $imgTag);

                        if($row['sorting'] == ($imageOrderID + 1)) {
                            // Make the big image a link to the next
                            $bigImageTag = $this->createNavigationLink($url, $bigImageTag);
                            if($refreshTimeout > 0) {
                                $GLOBALS['TSFE']->additionalHeaderData['tx_hldamgallery_refresh'] =
                                    '<meta http-equiv="refresh" content="' . $refreshTimeout . '; URL=' . $url . '" />';
                            }
                        } else if($row['sorting'] == $imageOrderID) {
                            $imgLink = '<span class="tx_hldamgallery_current_thumb">' . $imgTag . '</span>';
                        }

                        $navArray[$row['sorting'] - $imageOrderID + $numberOfThumbs] = $imgLink;
                    }

                    $navContent = '';
                    for($i = 0; $i < ($numberOfThumbs * 2 + 1); $i++) {
                        $navContent .= $navArray[$i];
                    }

                    $navContent = '
                    <div class="tx_hldamgallery_navigation">' .
                        $navContent . '
                    </div>';
                }
            }
            // ========================================================================================================
            //
            // Creating final output
            //
            $content = '
                <div class="tx_hldamgallery_back_link">' . $backLink . '</div>
                <div class="tx_hldamgallery_img">' . $bigImageTag . '</div>' . $downloadLink . $metaContent . $navContent;
        }
        return $this->pi_wrapInBaseClass($content);
    }

    function createNavigationURL($galleryPID, $galleryCID, $imgID) {
        $typolink_conf = array(
            'no_cache' => $this->conf['showHits'],
            'parameter' => $GLOBALS['TSFE']->id,
            'additionalParams' => '&tx_hldamgallery_pi1[galleryPID]=' . $galleryPID .
                '&tx_hldamgallery_pi1[galleryCID]=' . $galleryCID . '&tx_hldamgallery_pi1[imgID]=' . $imgID,
            'useCacheHash' => true
        );

        return $this->cObj->typolink_URL($typolink_conf);
    }

    //
    // size:  0 := small, 1:= medium, 2:= large
    function createNavigationURLSize($galleryPID, $galleryCID, $imgID, $size) {
        $typolink_conf = array(
            'no_cache' => $this->conf['showHits'],
            'parameter' => $GLOBALS['TSFE']->id,
            'additionalParams' => '&tx_hldamgallery_pi1[galleryPID]=' . $galleryPID .
                '&tx_hldamgallery_pi1[galleryCID]=' . $galleryCID . '&tx_hldamgallery_pi1[imgID]=' . $imgID .
                '&tx_hldamgallery_pi1[size]=' . $size,
            'useCacheHash' => true
        );

        return $this->cObj->typolink_URL($typolink_conf);
    }

    function createNavigationLink($url, $string) {
        $a1 = '<a href="' . $url . '"' . $target.$GLOBALS['TSFE']->ATagParams . '>';
        $a2 = '</a>';
        $content = $a1 . $string . $a2;
        return $content;
    }

    function createImageTag($imageFile, $img) {
        $conf = $this->cObj->image_compression[$this->cObj->data['image_compression']];
        $conf['altText'] = $img['altText'];
        $conf['titleText'] = $img['titleText'];
        $conf['is_HL_Dam_Gallery_Image'] = true;
        $imgTag = $this->cObj->cImage($imageFile, $conf);
        return $imgTag;
    }

    function prepareMetaEntry($confID, $value, $style) {
        $html = '';
        if($this->conf['defaultIfEmpty']) {
            $html = $this->conf['defaultIfEmpty'];
        }

        $divA = '<div class="' . $style . '">';
        $divB = '</div>';

        $defaultConfID = str_replace('show', 'default', $confID);
        switch($this->conf[$confID]) {
            case 1:
                if(strlen($value) > 0) {
                    $html = $value;
                } else {
                    $divA = '';
                    $divB = '';
                    $html = '';
                }
                break;
            case 2:
                if(strlen($value) > 0) {
                    $html = $value;
                }
                break;
            case 3:
                if(strlen($value) > 0) {
                    $html = $value;
                } else if($this->conf[$defaultConfID]) {
                    $html = $this->conf[$defaultConfID];
                }
                break;
            case 4:
                if($this->conf[$defaultConfID]) {
                    $html = $this->conf[$defaultConfID];
                }
                break;
            default:
                return '';
                break;
        }

        // Add tag and style
        $html = $divA . $html . $divB;
        return $html;
    }

    function getIPTC($file) {
        $metaConn = '';
        $size = getImageSize($file, $info);

        if(is_array($info)) {
            $iptc = iptcparse($info["APP13"]);

            if(strlen($iptc["2#092"][0]) > 0) {
                $metaContent = $iptc["2#092"][0];
                $metaConn = ', ';
            }

            if(strlen($iptc["2#090"][0]) > 0) {
                $metaContent .= $metaConn . $iptc["2#090"][0];
                $metaConn = ', ';
            }
/*
            if(strlen($iptc["2#095"][0]) > 0) {
                $metaContent .= $metaConn . $iptc["2#095"][0];
                $metaConn = ', ';
            }
*/
            if(strlen($iptc["2#101"][0]) > 0) {
                $metaContent .= $metaConn . $iptc["2#101"][0];
                $metaConn = ', ';
            }
        }
        return $metaContent;
    }


    function appendMetaTags($keywordsText) {
        // <meta name="keywords" content=" ... " />
        $pageKeyString = $GLOBALS['TSFE']->additionalHeaderData['tx_hldamgallery_meta'];

        if(strlen($pageKeyString)) {
            $pageKeyString = str_replace('<meta name="keywords" content="', '', $pageKeyString);
            $pageKeyString = str_replace('" />', '', $pageKeyString);
            $pageKeys = explode(',', $pageKeyString);
        } else {
            $pageKeys = array();
        }

        $keywords = explode(',', $keywordsText);
        foreach($keywords as $key) {
            if(!in_array($key, $pageKeys) && strlen(trim($key))) {
                if(strlen(trim($pageKeyString)) == 0) {
                    $pageKeyString = trim($key);
                } else {
                    $pageKeyString .= ',' . trim($key);
                }
            }
        }

        $pageKeyString = trim($pageKeyString);
        if(strlen($pageKeyString)) {
            $GLOBALS['TSFE']->additionalHeaderData['tx_hldamgallery_meta'] =
                    '<meta name="keywords" content="' . trim($pageKeyString) . '" />';
        }
    }

	/**
	 * Returns string of a filename or string converted to a spaced extension
	 * less header type string.
	 *
	 * @author Michael Cannon <michael@peimic.com>
	 * @param string filename or arbitrary text
	 * @return mixed string/boolean
	 */
	function cbMkReadableStr($str)
	{
		if ( is_string($str) )
		{
			$clean_str = htmlspecialchars($str);

			// remove file extension
			$clean_str = preg_replace('/\.[[:alnum:]]+$/i', '', $clean_str);

			// remove funky characters
			$clean_str = preg_replace('/[^[:print:]]/', '_', $clean_str);

			// Convert camelcase to underscore
			$clean_str = preg_replace('/([[:alpha:]][a-z]+)/', "$1_", $clean_str);

			// try to cactch N.N or the like
			$clean_str = preg_replace('/([[:digit:]\.\-]+)/', "$1_", $clean_str);

			// change underscore or underscore-hyphen to become space
			$clean_str = preg_replace('/(_-|_)/', ' ', $clean_str);

			// remove extra spaces
			$clean_str = preg_replace('/ +/', ' ', $clean_str);

			// convert stand alone s to 's
			$clean_str = preg_replace('/ s /', "'s ", $clean_str);

			// remove beg/end spaces
			$clean_str = trim($clean_str);

			// capitalize
			$clean_str = ucwords($clean_str);

			// restore previous entities facing &amp; issues
			$clean_str = preg_replace( '/(&amp ;)([a-z0-9]+) ;/i'
				, '&\2;'
				, $clean_str
			);

			return $clean_str;
		}

		return false;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hl_dam_gallery/pi1/class.tx_hldamgallery_pi1.php'])	{
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hl_dam_gallery/pi1/class.tx_hldamgallery_pi1.php']);
}
/*
function linkPage($pid) {
        $typolink_conf = array(
            "no_cache" => false, // cache this page
            "returnLast" => "url", // don't wrap the link in anchor tags
            "parameter" => $pid, // page id
            "additionalParams" = "&xx_myext[aaa]=bbb", // extra params
            "useCacheHash" => true); // generate cHash
        return $this->cObj->typolink("", $typolink_conf);
}

tslib_pibase:  http://typo3api.ueckermann.de/index.html :

Example create link function in class.tslib_pibase.php :
tslib_pibase::pi_linkTP ( $str,
  $urlParameters = array(),
  $cache = 0,
  $altPageId = 0
)
*/
?>