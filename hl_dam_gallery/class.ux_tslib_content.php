<?php
// class.ux_tslib_content.php
class ux_tslib_cObj extends tslib_cObj {
    /**
     * Returns a <img> tag with the image file defined by $file and processed according to the properties in the TypoScript array.
     * Mostly this function is a sub-function to the IMAGE function which renders the IMAGE cObject in TypoScript. This function is called by "$this->cImage($conf['file'],$conf);" from IMAGE().
     *
     * @param       string          File TypoScript resource
     * @param       array           TypoScript configuration properties
     * @return      string          <img> tag, (possibly wrapped in links and other HTML) if any image found.
     * @access private
     * @see IMAGE()
     */
    function cImage($file,$conf) {
        $info = $this->getImgResource($file,$conf['file.']);
        $GLOBALS['TSFE']->lastImageInfo=$info;

        if($conf['imageLinkWrap'] || $conf['is_HL_Dam_Gallery_Image']) {
            // Make sure, we are not dealing with images inside the text, like added by dh_linklayout
            // Bad hack, but it works ...
            if($this->data['tx_hldamgallery_squarethumbs'] != 0) {
                list($width, $height, $type, $attr) = getImageSize($info[3]);
                $orientation = ($width >= $height) ? 'landscape' : 'portrait';

                $tempWidth = $this->data['imagewidth'];
                $tempHeight = $this->data['imageheight'];

                $size = 100;
                if($this->data['imagewidth'] > 0) {
                    $size = $this->data['imagewidth'];
                } else if($this->data['imageheight'] > 0) {
                    $size = $this->data['imageheight'];
                }

                $this->data['imagewidth'] = '';
                $this->data['imageheight'] = '';

                if ($orientation === 'landscape') {
                    $conf['file.']['height'] = $size;
                    $conf['file.']['width'] = $size * ($width / $height);
                } else {
                    $conf['file.']['height'] = $size * ($height / $width);
                    $conf['file.']['width'] = $size;
                }

                $conf['file.']['maxW'] = $conf['file.']['width'] + 10;  // let's avoid errors based on rounding ...
                $conf['file.']['params'] = ' -gravity Center -crop ' . $size . 'x' . $size . '+0+0';

                $info = $this->getImgResource($file,$conf['file.']);
                $GLOBALS['TSFE']->lastImageInfo=$info;

                // Preserve imagewidth and -height for the next images ...
                $this->data['imagewidth'] = $tempWidth;
                $this->data['imageheight'] =$tempHeight;
            } else if($this->data['imagewidth'] > 0 && $this->data['imageheight']) {
                list($width, $height, $type, $attr) = getImageSize($info[3]);
                $orientation = ($width >= $height) ? 'landscape' : 'portrait';

                $tempWidth = $this->data['imagewidth'];
                $tempHeight = $this->data['imageheight'];

                $this->data['imagewidth'] = '';
                $this->data['imageheight'] = '';

                if(($tempWidth / $tempHeight) == ($width / $height)) {
                    $conf['file.']['height'] = $tempHeight;
                    $conf['file.']['width'] = $tempWidth;
                } else if(($tempWidth / $tempHeight) < ($width / $height)) {
                    $conf['file.']['height'] = $tempHeight;
                    $conf['file.']['width'] = $tempHeight * ($width / $height);
                } else {
                    $conf['file.']['height'] = $tempWidth * ($height / $width);
                    $conf['file.']['width'] = $tempWidth;
                }

                $conf['file.']['maxW'] = $conf['file.']['width'] + 10;  // let's avoid errors based on rounding ...
                $conf['file.']['params'] = ' -gravity Center -crop ' . $tempWidth . 'x' . $tempHeight . '+0+0';

                $info = $this->getImgResource($file,$conf['file.']);
                $GLOBALS['TSFE']->lastImageInfo=$info;

                // Preserve imagewidth and -height for the next images ...
                $this->data['imagewidth'] = $tempWidth;
                $this->data['imageheight'] =$tempHeight;
            }

            if(!$conf['is_HL_Dam_Gallery_Image']) {
                // We are not called from the extension.  Therefore we have to check for
                // the meta data ourself.
                $conf = $this->getDAM_MetaData($conf, $info);
            }
        }

        if (is_array($info)) {
            $info[3] = t3lib_div::png_to_gif_by_imagemagick($info[3]);
            $GLOBALS['TSFE']->imagesOnPage[]=$info[3];              // This array is used to collect the image-refs on the page...

            if (!strlen($conf['altText']) && !is_array($conf['altText.']))  {       // Backwards compatible:
                $conf['altText'] = $conf['alttext'];
                $conf['altText.'] = $conf['alttext.'];
            }

            $altParam = $this->getAltParam($conf);

// echo("<pre>");
// print_r($conf);
// echo("</pre>");

            $theValue = '<img src="'.htmlspecialchars($GLOBALS['TSFE']->absRefPrefix.t3lib_div::rawUrlEncodeFP($info[3])) .
                    '" width="'.$info[0].'" height="' . $info[1] . '"' .
                    $this->getBorderAttr(' border="' . intval($conf['border']).'"') . ($altParam) . ' />';

            if($this->data['tx_hldamgallery_usepage'] != 0 && $conf['imageLinkWrap']) {
                $theValue = $this->imageLinkWrap($theValue,$info['origFile'],$conf['imageLinkWrap.']);
            } elseif ($conf['linkWrap'])  {
                $theValue = $this->linkWrap($theValue,$conf['linkWrap']);
            } elseif ($conf['imageLinkWrap']) {
                $theValue = $this->imageLinkWrap($theValue,$info['origFile'],$conf['imageLinkWrap.']);
            }
            return $this->wrap($theValue,$conf['wrap']);
        }
    }

    /**
    * Wraps the input string in link-tags that opens the image in the same window.
    *
    * @param       string          String to wrap, probably an <img> tag
    * @param       string          The original image file
    * @param       array           TypoScript properties for the "imageLinkWrap" function
    * @return      string          The input string, $string, wrapped as configured.
    * @see cImage()
    * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=316&cHash=2848266da6
    */
    function imageLinkWrap($string,$imageFile,$conf) {
        $a1='';
        $a2='';
        $content=$string;

        $content = $this->typolink($string, $conf['typolink.']);
        // imageFileLink:
        if ($content == $string && @is_file($imageFile)) {
            if($this->data['tx_hldamgallery_usepage'] &&
                        ($this->data['tx_hldamgallery_displaypage'] > 0 ||
                                $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hldamgallery_pi1.']['defaultGalleryPID'] > 0)) {
                $galleryPID = $this->data['pid'];
                $galleryCID = $this->data['uid'];
                $img = $GLOBALS['TSFE']->register['IMAGE_NUM'] + 1;

                if($this->data['tx_hldamgallery_displaypage'] > 0) {
                    $imagePage = $this->data['tx_hldamgallery_displaypage'];
                } else {
                    $imagePage = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hldamgallery_pi1.']['defaultGalleryPID'];
                }

                $cache = false;
                if($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hldamgallery_pi1.']['showHits']) {
                    $cache = true;
                }

                $typolink_conf = array(
                    'no_cache' => $cache,
                    'parameter' => $imagePage,
                    'additionalParams' => '&tx_hldamgallery_pi1[galleryPID]=' . $galleryPID .
                        '&tx_hldamgallery_pi1[galleryCID]=' . $galleryCID . '&tx_hldamgallery_pi1[imgID]=' . $img,
                    'useCacheHash' => true
                );

                $url = $this->typolink_URL($typolink_conf);

                $a1='<a href="'.htmlspecialchars($url).'"'.$target.$GLOBALS['TSFE']->ATagParams.'>';
                $a2='</a>';
                $content=$a1.$string.$a2;
            } else {
                $content = tslib_cObj::imageLinkWrap($string,$imageFile,$conf);
            }
        }
        return $content;
    }

    /**
    * Fills the $conf array with additional meta data, based on DAM and/or IPTC records.
    *
    * @param       array           TypoScript properties
    * @return      string          The input string, $string, wrapped as configured.
    * @see cImage()
    * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=316&cHash=2848266da6
    */
    function getDAM_MetaData($conf, $info) {
        $GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
        $GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

        $fileName = basename($info['origFile']);
        $filePath = dirname($info['origFile']) . '/';

        // Shall we take care of IPTC records here?
//         $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hldamgallery_pi1.']['useIPTC']);

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_dam',
                "file_name='" . $fileName . "' and file_path='" . $filePath . "'", '', '', 1);

        $imgTitleText = '';
        $imgAltText = '';

        if($res) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            $imgTitleText = $row['title'];
            $imgAltText = $row['alt_text'];
            $keywordsText = $row['keywords'];
        }

        $conf['altText'] = $imgAltText;
        unset($conf['altText.']);
        $conf['titleText'] = $imgTitleText;

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
        if(strlen($pageKeyString) > 0) {
            $GLOBALS['TSFE']->additionalHeaderData['tx_hldamgallery_meta'] =
                    '<meta name="keywords" content="' . trim($pageKeyString) . '" />';
        }

        return $conf;
    }
}

?>