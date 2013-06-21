<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Chetan Thapliyal (chetan@srijan.in)
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
 * Plugin 'BAHAG Photo Gallery' for the 'bahag_photogallery' extension.
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */

//error_reporting(0);
//error_reporting(E_ALL^NOTICE);

require_once(t3lib_extMgm::extPath('bahag_photogallery').'pi1/class.tx_bahag_photogallery_base.php');
require_once(t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_zip.php');

class tx_bahagphotogallery_pi1 extends tx_bahag_photogallery_base {
	var $prefixId = "tx_bahagphotogallery_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_bahagphotogallery_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "bahag_photogallery";	// The extension key.

	var $downloadImageResolutions;

	/**
	 * Gallery view type i.e. normal/sectional
	 * @var string
	 */
	var $galleryViewType;

	/**
	 * Location of gallery view template
	 * @var string
	 */
	var $galleryViewTemplatePath;

	/**
	 * Flag for displaying EXIF data
	 * @var boolean
	 */
	var $dispExifData;

	/**
	 * Flag for displaying IPTC data
	 *
	 * @access private
	 * @var boolean
	 */
	var $dispIPTCData;

	/**
	 * EXIF data items to display
	 * @var array
	 */
        var $reqExifDataItems;

	/**
	 * IPTC data items to display
	 *
	 * @access private
	 * @var array
	 */
        var $reqIPTCDataItems;

	/**
	 * Flag for displaying Thumbnail Highlighting
	 * @var boolean
	 */
	var $dispThumbnailHighlight;

	/**
	 * Color to be used for thumbnail highlighting
	 * @var string
	 */
	var $thumbHighlightColor;

	/**
	 * Plugin flexform data
	 * @var array
	 */
	var $flexData;

	/**
	 * Path to single view template
	 * @var string
	 */
	var $singleViewTemplatePath;

	/**
	 * Path to IPTC data template
	 * @var string
	 */
	var $iptcTemplatePath;

	/**
	 * Download option for images
	 *
	 * @access private
	 * @var string
	 */
	var $imgDownloadOption;

	/**
	 * Flag to know whether to display image size option
	 * in enlarged/single view or not
	 *
	 * @access private
	 * @var boolean
	 */
	var $dispImgSizeOption;

	/**
	 * Flag to know whether to display image colors option
	 * in enlarged/single view or not
	 *
	 * @access private
	 * @var boolean
	 */
	var $dispImgColorsOption;

	/**
	 * Flag to know whether to display image date option
	 * in enlarged/single view or not
	 *
	 * @access private
	 * @var boolean
	 */
	var $dispImgDateOption;

	/**
	 * Flag to know whether to display breadcrumbs or not
	 *
	 * @access private
	 * @var boolean
	 */
	var $dispBreadCrumbs;

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$GLOBALS['TSFE']->set_no_cache();
		$this->init();
		$GPVars = t3lib_div::_GET();
		$GPVars = array_merge( $GPVars, t3lib_div::_POST());
		$content = $this->getContent( $GPVars);

		return $this->pi_wrapInBaseClass( $content);
	}

	/**
	* Initialize plugin variables.
	* @access private
	*/	
	function init() {
		// Get plugin flexform data		
		$this->initFlexData();

		// SET DEFAULT VALUES FOR VARIABLES
		$this->downloadImageResolutions = array();
		$this->galleryViewType = 'normal';
		$this->galleryViewTemplatePath = 'EXT:bahag_photogallery/templates/gallery_view.htm';
		$this->singleViewTemplatePath = 'EXT:bahag_photogallery/templates/single_view.htm';
        $this->iptcTemplatePath = t3lib_extMgm::extPath( $this->extKey).'templates/iptc_fe.htm';
	    $this->dispExifData = false;
		$this->dispThumbnailHighlight = false;
		$this->thumbHighlightColor = '#FF0000';
		//$this->downloadImageResolutions = array();
		
        // Flag to know whether to display image EXIF data or not		
	    $this->dispExifData = (intval( $this->flexData['dispImgExifData']) > 0)
							  ? true 
							  : false;

        // Required image EXIF data for display
        $reqExifDataItems = explode( ',', $this->flexData['requiredExifData']);
        $this->reqExifDataItems = ( !empty( $reqExifDataItems))
                                  ? $reqExifDataItems
                                  : array();

        // Flag to know whether to display image IPTC data or not
		$this->dispIPTCData = (intval( $this->flexData['dispImgIptcData']) > 0)
							  ? true 
							  : false;

		// Required image IPTC data for display
        $reqIPTCDataItems = explode( ',', $this->flexData['requiredIptcData']);
        $this->reqIPTCDataItems = ( !empty( $reqIPTCDataItems))
                                  ? $reqIPTCDataItems
                                  : array();

		$this->dispThumbnailHighlight = (intval( $this->flexData['dispThumbHighlight']) > 0) 
										? true 
										: false;
										
		$this->thumbHighlightColor = ($this->flexData['thumbHighlightColor'] !== '') 
									 ? $this->flexData['thumbHighlightColor'] 
									 : '#FF0000';
		
		if ( !empty( $this->flexData['galleryViewType'])) {
			$this->galleryViewType = $this->flexData['galleryViewType'];
		}
		
		if ( !empty( $this->flexData['galleryPath'])) {
			$this->rootGalleryPath = $this->galleryPath = $this->flexData['galleryPath'];
		}

		$this->parseRecurrsive = $this->flexData['checkSubGalleries'];

		if ( !empty( $this->flexData['galleryImageRows']) && intval( $this->flexData['galleryImageRows']) >= 1) {
			$this->imageRows = intval( $this->flexData['galleryImageRows']);
		}

		if ( !empty( $this->flexData['galleryImageCols']) && intval( $this->flexData['galleryImageCols']) >= 1) {
			$this->imageColumns = intval( $this->flexData['galleryImageCols']);
		}

		if ( !empty( $this->flexData['watermarkImage'])) {
			$this->watermarkImage = $this->uploadsFolder.$this->flexData['watermarkImage'];
		}

		if ( strcmp( strtoupper($this->flexData['thumbDimType']), 'NONE')) {
			if ( !strcmp( strtoupper($this->flexData['thumbDimType']), 'WIDTH')) {
				$this->thumbWidth = intval( $this->flexData['thumbDimValue']);
				$this->thumbHeight = '';
			} else {
				$this->thumbHeight = intval( $this->flexData['thumbDimValue']);
				$this->thumbWidth = '';
			}
		}

		if ( strcmp( strtoupper($this->flexData['imageDimType']), 'NONE')) {
			if ( !strcmp( strtoupper($this->flexData['thumbDimType']), 'WIDTH')) {
				$this->imageWidth = $this->flexData['imageDimValue'];
				$this->imageHeight = '';
			} else {
				$this->imageHeight = $this->flexData['imageDimValue'];
				$this->imageWidth = '';
			}
		}

		if ( !empty( $this->flexData['imageQuality'])) {
			$this->imgQuality = $this->flexData['imageQuality'];
		}
		
		$this->tmbGrayscale  =  ( intval($this->flexData['isThumbGrayscale']) != 0)
								? 1
								: 0;

		$this->dispLargeThumb = intval( $this->flexData['dispFloatingThumb']);
		$this->flexData['floatingThumbDimType'] = strtoupper( $this->flexData['floatingThumbDimType']);

		if ( strcmp( $this->flexData['floatingThumbDimType'], 'NONE')) {
			if ( !strcmp( $this->flexData['floatingThumbDimType'], 'WIDTH')) {
				$this->largeThumbDimType = 'width';
				$this->largeThumbWidth = intval( $this->flexData['floatingThumbDimValue']);
				$this->largeThumbHeight = '';
			} else {
				$this->largeThumbDimType = 'height';
				$this->largeThumbWidth = '';
				$this->largeThumbHeight = intval( $this->flexData['floatingThumbDimValue']);
			}
		}
		
		$this->imgGrayscale  =  ( intval( $this->flexData['isImageGrayscale']) != 0)
								? 1
								: 0;

		/*if ( !empty( $this->flexData['isImageGrayscale'])) {
			$this->imgGrayscale = true;
		}*/

		if ( !empty( $this->flexData['dispImagesInPopup'])) {
			$this->dispInPopup = true;
		}

        $this->imgDownloadOption = ( $this->flexData['dwnldImgOption'] !== '')
                                   ? strtoupper( $this->flexData['dwnldImgOption'])
                                   : 'NONE';
                                        
		if ( !empty( $this->flexData['dwnldImgDimValue1'])) {
			$this->downloadImgResolutions[] = intval( $this->flexData['dwnldImgDimValue1']);
		}

		if ( !empty( $this->flexData['dwnldImgDimValue2'])) {
			$this->downloadImgResolutions[] = intval( $this->flexData['dwnldImgDimValue2']);
		}

		if ( !empty( $this->flexData['dwnldImgDimValue3'])) {
			$this->downloadImgResolutions[] = intval( $this->flexData['dwnldImgDimValue3']);
		}

		if ( !empty( $this->flexData['dwnldImgDimValue4'])) {
			$this->downloadImgResolutions[] = intval( $this->flexData['dwnldImgDimValue4']);
		}

		if ( !empty( $this->flexData['dwnldImgDimValue5'])) {
			$this->downloadImgResolutions[] = intval( $this->flexData['dwnldImgDimValue5']);
		}
		
		$this->downloadImgDimType = ( strtoupper( $this->flexData['dwnldImgDimType']) === 'HEIGHT')
		                            ? 'height'
		                            : 'width';

		$this->subGalleryViewType = $this->flexData['subGalleryPreviewType'];

		switch ( $this->flexData['subGalleryPreviewType']) {
			case 'preview':
			    $this->subGalleryPreviewImgCount = 1;

			    break;
			case 'all':
			    $this->subGalleryPreviewImgCount = -1;
			    break;
			case 'custom':
			    $previewImageCount = intval( $this->flexData['subGalleryPreviewImgCount']);
			    $this->subGalleryPreviewImgCount = ($previewImageCount > 0) 
											       ? $previewImageCount 
											       : 0;
			    break;
			default :
			    $this->subGalleryPreviewImgCount = 0;
		}

        $this->dispImgSizeOption = ( intval( $this->flexData['dispImgSizeOption']) != 0)
                                   ? true
                                   : false;
                                   
        $this->dispImgColorsOption = ( intval( $this->flexData['dispImgColorsOption']) != 0)
                                   ? true
                                   : false;
                                   
        $this->dispImgDateOption = ( intval( $this->flexData['dispImgDateOption']) != 0)
                                   ? true
                                   : false;
                                   
        $this->dispBreadcrumbs = ( intval( $this->flexData['dispBreadcrumbs']) != 0)
                                 ? true
                                 : false;
                                   
		if ( !empty( $this->downloadImgResolutions)) {
			sort( $this->downloadImgResolutions, SORT_NUMERIC);
		}

		if ( t3lib_div::_GET('resultPage')) {
			$this->resultPage = t3lib_div::_GET('resultPage');
		}

		/* initialize gallery template variables */
		$this->galleryOverviewTemplate = t3lib_extMgm::extPath($this->extKey).'templates/gallery_overview.htm';
		$this->singleViewTemplate = t3lib_extMgm::extPath($this->extKey).'templates/single_view.htm';
		$this->storageFolder = $this->flexData['pages'];

		// Don't change the calling sequence of this function
		parent::genGalleryStruc();
	}
	
	/**
	 * Function to get the plugin flexform data
	 *
	 * @access private
	 */
	 function initFlexData() {
	 	$this->pi_initPIflexform();
	 	$piFlexForm = $this->cObj->data['pi_flexform'];
	 	
	 	if ( is_array( $piFlexForm) && !empty( $piFlexForm)) {
    		foreach ( $piFlexForm['data'] as $sheet => $data ) {
    			foreach ( $data as $lang => $value ) {
    				foreach ( $value as $key => $val ) {
    					$this->flexData[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
    				}
    			}
    		}
	 	}
	 }



	/**
	* Get the front-end content for plugin
	* @return string
	* @param array $param - Array containing GET variables
	* @access private
	*/	
	function getContent( $param) {
		$content = '';	/* variable to hold the page content */
		$this->action = $param['action'];

		switch ( $this->action) {
			case 'popView':
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content = $this->getEnlargedView();
    			echo $content;
    			exit();
    			
    			break;
			case 'download':
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content = $this->downloadImage( intval( $param['res']));
    			
    			break;
			case 'zippedDownload':
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content = $this->getZippedImage( intval( $param['res']));
    			
    			break;
			case 'viewImage':
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content = $this->getEnlargedView();

    			break;
			case 'viewGallery':
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content .= $this->getGallery( intval( $param['gallery']));
    			
    			break;
			case 'galleriesOverview':
			default:
    			$this->currentImgID = intval( $param['idx']);
    			$this->currentGalleryID = intval( $param['gallery']);
    			$content .= $this->getGalleryOverview( $param['resultPage'], intval( $param['gallery']));
		}

		return $content;
	}

	/**
	* Get gallery overview content
	* @return string
	* @param integer $resultPage - Page to display within the current gallery
	* @param string $galleryID - ID of gallery
	* @access private
	*/	
	function getGalleryOverview ( $resultPage, $galleryID = 0) {

		$galleryOverviewContent = '';	/* variable to hold the gallery overview content */
		$resultPage = intval( $resultPage);

		if ( $resultPage > 0) {
			$this->resultPage = $resultPage;
		}

		if( !empty($galleryPath)) {
			$this->galleryPath = $galleryPath;
		}

		if ( file_exists( $this->galleryOverviewTemplate)) {
			if ( false !== ( $galleryOverviewContent = file_get_contents( $this->galleryOverviewTemplate))) {
				$galleryOverviewContent = $this->getGalleryContent( $galleryID);
			}
		}

		return $galleryOverviewContent;
	}

	/**
	 * Get breadcrumbs for the gallery
     *
	 * @access private
	 * @param string $galleryID - ID of gallery for which breadcrumbs are required
	 * @return string
	 */	
	function getBreadcrumbs( $galleryID) {
		$breadCrumbs = '';		// Breadcrumbs for Photo Gallery
		$breadCrumbs = $this->galleries[$galleryID]['name'];
		$parentID = intval( $this->galleries[$galleryID]['parent']);
		$parentName = $this->galleries[$parentID]['name'];
		
		while ( $parentID >= 0) {
			$breadCrumbs = '<a href="'.$this->getUrl($GLOBALS['TSFE']->id, array('gallery'=> $parentID)).'">'.$parentName.'</a>&nbsp;&gt;&nbsp;'.$breadCrumbs;
			$parentID = $this->galleries[$parentID]['parent'];
			$parentName = $this->galleries[$parentID]['name'];
		}

		return $breadCrumbs;
	}

	/**
	 * Function to bring the zipped image for download
     *
	 * @access private
	 * @param integer $galleryID   ID of gallery
	 * @param integer $imageID     ID of image in gallery
	 */
	function getZippedImage( $res) {
		$imagePath = PATH_site.$this->imageAt( $this->currentGalleryID, $this->currentImgID);

		if ( !file_exists( $imagePath)) {
			return;
		}

		$imgPath = (!empty($res)) ? $this->getDownloadImage( $imagePath, $res) : $imagePath;

		if ( file_exists( $imgPath)) {
			$imgData = file_get_contents( $imgPath);
			$objZip = new zipfile();
			$objZip->addFile( $imgData, basename( $imgPath), $time = 0);
			unset( $imgData);

			$image['name'] = basename( $imgPath);
			$headerParam = array(
			'contentLength' => filesize( $imgPath),
			'fileName' => array_shift( explode( '.', $image['name'])).'.zip',
			'contentType' => 'application/zip'
			);

			$this->setPageHeader( $headerParam);
			echo $objZip->file();
			exit;
		}
	}

	/**
	 * Get image navigation in enlarged view
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @return array
	 */
	function getImageNavi( $galleryID) {
		$imageNavi = array();
		$galleryImages = $this->getGalleryImages( $galleryID);
		$galleryImgCount = count( $galleryImages);
		$getParam = array(
		'action'=> $this->action,
		'gallery'=> $galleryID,
		'resultPage' => $this->resultPage,
		);

		if ( $galleryImgCount > 1) {
			if ( $this->currentImgID > 0 && $this->currentImgID <= $galleryImgCount-1) {
				$getParam['idx'] = $this->currentImgID - 1;
				$imageNavi[] = '<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $getParam).'">'.$this->pi_getLL('previous_page').'</a>';

				if ( $this->currentImgID == $galleryImgCount - 1) {
					$getParam['idx'] = 0;
					$imageNavi[] = '<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $getParam).'">'.$this->pi_getLL('first').'</a>';
				}
			}

			if ( $this->currentImgID >= 0 && $this->currentImgID < $galleryImgCount-1) {
				if ( $this->currentImgID == 0) {
					$getParam['idx'] = $galleryImgCount - 1;
					$imageNavi[] = '<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $getParam).'">'.$this->pi_getLL('last').'</a>';
				}

				$getParam['idx'] = $this->currentImgID +1;
				$imageNavi[] = '<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $getParam).'">'.$this->pi_getLL('next_page').'</a>';
			}
		}

		return $imageNavi;
	}

	/**
	* Get links for image download 
	* @access private
	* @param string $imagePath - Image path relative to the root gallery
	* @return array
	*/
	function getDownloadImgLinks( $imagePath, $downloadType = DOWNLOAD_NORMAL) {
		$imageLinks = array();
		$this->downloadImageResolutions = array();

		$imageDim = $this->graphics->getImgDimensions( $imagePath);

		$this->downloadImgDimType = ($this->downloadImgDimType == 'height') ? 'height' : 'width';

		if ( empty( $this->downloadImgResolutions)) {
			if ($this->downloadImgDimType == 'height') {
				$this->downloadImgResolutions = array( 480, 600, 768, 960, 1200);
			} else {
				$this->downloadImgResolutions = array( 640, 800, 1024, 1280, 1600);
			}
		}

		$getParam = array(
		'action' => ( $downloadType == DOWNLOAD_NORMAL) ? 'download' : 'zippedDownload',
		'gallery' => $this->currentGalleryID,
		'idx' => $this->currentImgID,
		);

		foreach ( $this->downloadImgResolutions as $key => $value) {
			$getParam['res'] = $value;

			if ( !strcmp( $this->downloadImgDimType, 'width')) {
				if ( $imageDim['width'] >= $value) {
					$scalingFactor = intval( $value) / $imageDim['width'];
					$newImageHeight = ceil($imageDim['height'] * $scalingFactor);
					$this->downloadImageResolutions[] = $value.'x'.$newImageHeight;

					if ( $downloadType == DOWNLOAD_NORMAL) {
						$imageLinks[] = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon1']) ? JPG_ICON : $this->conf['downloadIcon1'] ).'" border="0"></a> ';
					} else {
						$imageLinks[] = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon2']) ? ZIP_ICON : $this->conf['downloadIcon2'] ).'" border="0"></a> ';
					}
				}
			} else {
				if ( $imageDim['height'] >= $value) {
					$scalingFactor = intval( $value) / $imageDim['height'];
					$newImageWidth = ceil($imageDim['width'] * $scalingFactor);
					$this->downloadImageResolutions[] = $newImageWidth.'x'.$value;

					if ( $downloadType == DOWNLOAD_NORMAL) {
						$imageLinks[] = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon1']) ? JPG_ICON : $this->conf['downloadIcon1'] ).'" border="0"></a> ';
					} else {
						$imageLinks[] = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon2']) ? ZIP_ICON : $this->conf['downloadIcon2'] ).'" border="0"></a> ';
					}
				}
			}
		}

		return $imageLinks;
	}

	/**
	 * Get javascript for gallery
	 */
	function getPageJS() {
		$pageJS = '';

		$pageJS = '
			<script type="text/javascript" language="JavaScript">
			   /**
				* Variable to hold the floating div element
				* @var object
				*/
			   var floatingDiv = false;
			   
			   /**
				* Variable to hold the preloaded preview images for current page of gallery
				* @var array
				*/
			   var previewImages = new Array();
			   
			   var active_img_mark = null;
			 
			   window.onerror = function() { 
				return true; 
			   }
			   
			   /**
				* Function to preload floating preview images for curent page of gallery
				*/
				function preloadPreviewImages() {';

		foreach ( $this->previewImages as $key => $value) {
			$pageJS .= '
					previewImages['.$key.'] = new Image();
					previewImages['.$key.'].src = "'.$value.'"';
		}

		$pageJS .= '
				}
			 
			   window.onerror = function() {
			   		return true;
				}
				
			   window.onload = function(e) { 
					preloadPreviewImages();
					
			   		if ( document.getElementById && document.createElement) {
						tooltip.define(); 
					}
				}
			
				function run_after_body() {
				   document.write(\'<textarea id="gate_to_clipboard" style="display:none;"></textarea>\');
				   document.onmousemove = document_onmousemove;
				   if (window.onscroll) window.onscroll = hideDiv();
				   document.write(\'<div class="float" id="div_200" style="left: -3000px; background: #ffffff;"><img id="img_200" class="border_b" width="200" height="150"></div>\');
				   setInterval("changer();",333);
				}
			

			   /**
				* Function to get the html element by id
				* @var string id - id of the html element
				*/
				function getElement( id) {
				   if ( document.getElementById) {
					  return document.getElementById( id);
				   } else if ( document.all) {
					  return document.all[id];
				   } else {
					  return null;
				   }
				}
			
			function document_onmousemove(e) {
			
			   if ( !floatingDiv ) return;
			
			   var pos_X = 0, pos_Y = 0;
			   if ( !e ) e = window.event;
			   if ( e ) {
				  if ( typeof(e.pageX) == "number" ) {
					 pos_X = e.pageX; pos_Y = e.pageY;
				  } else if ( typeof(e.clientX) == "number" ) {
					 pos_X = e.clientX; pos_Y = e.clientY;
					 if ( document.body && ( document.body.scrollTop || document.body.scrollLeft ) && !( window.opera || window.debug || navigator.vendor == "KDE" ) ) {
						pos_X += document.body.scrollLeft; pos_Y += document.body.scrollTop;
					 } else if ( document.documentElement && ( document.documentElement.scrollTop || document.documentElement.scrollLeft ) && !( window.opera || window.debug || navigator.vendor == "KDE" ) ) {
						pos_X += document.documentElement.scrollLeft; pos_Y += document.documentElement.scrollTop;
					 }
				  }
			   }
			 
			   var scroll_X = 0, scroll_Y = 0;
			   if ( document.body && ( document.body.scrollTop || document.body.scrollLeft ) && !( window.debug || navigator.vendor == "KDE" ) ) {
				  scroll_X = document.body.scrollLeft; scroll_Y = document.body.scrollTop;
			   } else if ( document.documentElement && ( document.documentElement.scrollTop || document.documentElement.scrollLeft ) && !( window.debug || navigator.vendor == "KDE" ) ) {
				  scroll_X = document.documentElement.scrollLeft; scroll_Y = document.documentElement.scrollTop;
			   }
			 
			   var win_size_X = 0, win_size_Y = 0;
			   if (window.innerWidth && window.innerHeight) {
				  win_size_X = window.innerWidth; win_size_Y = window.innerHeight;
			   } else if (document.documentElement && document.documentElement.clientWidth && document.documentElement.clientHeight) {
				  win_size_X = document.documentElement.clientWidth; win_size_Y = document.documentElement.clientHeight;
			   } else if (document.body && document.body.clientWidth && document.body.clientHeight) {
				  win_size_X = document.body.clientWidth; win_size_Y = document.body.clientHeight;
			   }
			 
			   pos_X += 15; pos_Y += 15;
			 
			   if (floatingDiv.offsetWidth && floatingDiv.offsetHeight) {
				  if (pos_X - scroll_X + floatingDiv.offsetWidth + 5 > win_size_X) pos_X -= (floatingDiv.offsetWidth + 25);
				  if (pos_Y - scroll_Y + floatingDiv.offsetHeight + 5 > win_size_Y) pos_Y -= (floatingDiv.offsetHeight + 20);
			   }
			
			   floatingDiv.style.left = pos_X + "px"; floatingDiv.style.top = pos_Y + "px";
			 
			}
			

		   /**
			* Function to show the floating preview image
			* @var integer id - Index of the preview image in previewImages array
			*/
			function showPreviewImage( id) {
			   setPreviewImage( id);
			   showDiv("div_200");
			}
		
		   /**
			* Function to set the current preview image
			* @var string id - index of preview image in previewImages array
			*/
			function setPreviewImage( id){
			   var previewImage = getElement("img_200");
			   
			   if ( previewImage) {
				   previewImage.src    = previewImages[id].src;
				   previewImage.width  = previewImages[id].width;
				   previewImage.height = previewImages[id].height;
			   }
			}
		
		   /**
			* Function to make the DIV, containing preview image, visible
			* @var string id - id of the DIV element
			*/
			function showDiv( id) {
			   if ( floatingDiv = getElement( id)) {
				   if ( floatingDiv.offsetWidth) {
					  floatingDiv.style.width = "auto";
					  floatingDiv.style.height = "auto";
					  
					  if ( floatingDiv.offsetWidth > 300) {
						floatingDiv.style.width = "300px";
					  }
				   }
				   
				   document_onmousemove;
				   floatingDiv.style.visibility = "visible";
			   }
			}
		
			function changer() {
			   /* if ( !floatingDiv || !preloads[active_img_mark] || !getElement("img_200")) {
				return;
			   }
		   
			   if ( getElement("img_200").src != preloads[active_img_mark].src && preloads[active_img_mark].complete ) {
				setPreviewImage(active_img_mark);
			   } */
			}
		
		   /**
			* Function to hide the DIV containing preview image
			*/
			function hideDiv() {
			   if ( floatingDiv) {
				   floatingDiv.style.visibility = "hidden";
				   floatingDiv.style.left = "-3000px";
				   floatingDiv = false;
			   }
			}
		
			</script>
			
			<script type="text/javascript" language="JavaScript">run_after_body();</script>
			
		';

		return $pageJS;
	}


	/**
	 * Get CSS for gallery
	 */
	function getPageCSS() {
		$pageCSS = '';

		$pageCSS = '
			<style type="text/css">
			.border_b{
			   border: 1px solid #000000;
			}
			
			.float{
			   visibility: hidden;
			   position: absolute;
			   left: -3000px;
			   z-index: 10;
			}
			</style>
		';

		return $pageCSS;
	}

	/**
	 * Function to get the content of gallery
	 *
	 * @access public
	 * @return array
	 */
	function getGallery( $galleryID) {
		$content = array();
		$imageDetails = '';
		$templateFile = $this->cObj->fileResource( $this->galleryViewTemplatePath);
		$template['galleryView'] = $this->cObj->getSubpart($templateFile,'GALLERY_VIEW_TEMPLATE');
		$template['imageDetails'] = $this->cObj->getSubpart($template['galleryView'],'IMAGE_DETAILS_TEMPLATE');
		$template['GALLERY_IMAGE_COUNT'] = $this->cObj->getSubpart($template['galleryView'],'GALLERY_IMAGE_COUNT_TEMPLATE');
		$template['IMAGE_PROVIDER'] = $this->cObj->getSubpart($template['galleryView'],'IMAGE_PROVIDER_TEMPLATE');
		$template['IMAGE_COMMENT'] = $this->cObj->getSubpart($template['galleryView'],'IMAGE_COMMENT_TEMPLATE');
		$template['IMAGE_INFO_LINK'] = $this->cObj->getSubpart($template['galleryView'],'IMAGE_INFO_LINK_TEMPLATE');
		$gallery = $this->galleries[$galleryID];
		$getParam = array(
		'action'=>'viewImage',
		'gallery'=> $galleryID,
		'resultPage' => $this->resultPage,
		);

		foreach ($gallery['elements'] as $key => $val) {
			if ( is_array($val) && !empty($val)) {
				$content['subGallery'] .= $this->getSubGalleryContent( $val['index']);
			} else {
				$tmp = $template['imageDetails'];
				$imageDetails = $this->getImageDetails(PATH_site.$val);
				$imgDesc = $this->getImageDescription($val);
				$getParam['idx'] = $key;
				$galleryImgCount = $this->getGalleryImgCount( $galleryID);
				$galleryImgCountTmpl = !empty($galleryImgCount)
				                        ? $this->cObj->substituteMarkerArrayCached($template['GALLERY_IMAGE_COUNT'], array('###GALLERY_IMAGE_COUNT###'=> $galleryImgCount), array(),array())
				                        : '';
				$imgProviderTmpl = !empty($imgDesc['source']) ? $this->cObj->substituteMarkerArrayCached($template['IMAGE_PROVIDER'], array('###IMAGE_PROVIDER###'=> $imgDesc['source']), array(),array()) : '';
				$imgCommentTmpl = !empty($imgDesc['comment']) ? $this->cObj->substituteMarkerArrayCached($template['IMAGE_COMMENT'], array('###IMAGE_COMMENT###'=>$imgDesc['comment']), array(), array()) : '';
				$imgInfoLinkTmpl = !empty($imgDesc['info_pid']) ? $this->cObj->substituteMarkerArrayCached($template['IMAGE_INFO_LINK'], array('###IMAGE_INFO_LINK###'=>$this->getUrl($imgDesc['info_pid'])), array(), array()) : '';

				$replace = array(
				'###IMAGE_SRC###' => $this->getLargeThumbnail( $val),
				'###FULL_IMAGE_LINK###' => $this->getUrl($GLOBALS['TSFE']->id, $getParam),
				'###IMAGE_PROVIDER_TEMPLATE###' => $imgProviderTmpl,
				'###IMAGE_COMMENT_TEMPLATE###' => $imgCommentTmpl,
				'###IMAGE_RESOLUTION###' => $imageDetails['resolution'],
				'###IMAGE_FORMAT###' => $imageDetails['format'],
				'###IMAGE_SIZE###' => ceil( filesize(PATH_site.$val) / 1024),
				'###NORMAL_DOWNLOAD###' => $this->getDownloadLink( $key,''),
				'###IMAGE_INFO_LINK_TEMPLATE###' => $imgInfoLinkTmpl,
				'###ZIPPED_DOWNLOAD###' => $this->getDownloadLink( $key,'',DOWNLOAD_ZIPPED)
				);

				$imageList .= $this->cObj->substituteMarkerArrayCached($tmp, array(), $replace, array());
			}
		}

		$replace = array(
		'###GALLERY_IMAGE_COUNT_TEMPLATE###' => $galleryImgCountTmpl,
		'###IMAGE_DETAILS_TEMPLATE###' => $imageList,
		'###SUB_GALLERY###' => $content['subGallery'],
		'###BACK###' => $this->getUrl( $GLOBALS['TSFE']->id, $gallery['parent'] > 0 ? array('action'=>'viewGallery', 'gallery'=>$gallery['parent']) : array('action'=>'galleryOverview'))
		);

		$content['gallery'] .= $this->cObj->substituteMarkerArrayCached($template['galleryView'], array(), $replace, array());

		return $content['gallery'];
	}

	/**
	 * Function to get the download image link
	 *
	 * @access private
	 * @var string $imagePath - Absolute path of the image to download
	 * @var array $res - desired resolution of download image
	 * @var string $downloadType - Type of download ( Normal/Zip )
	 * @return string
	 */
	function getDownloadLink( $imageID, $res, $downloadType=DOWNLOAD_NORMAL) {
		$downloadLink = '';
		$getParam = array(
		'action' => ( $downloadType == DOWNLOAD_NORMAL) ? 'download' : 'zippedDownload',
		'gallery' => $this->currentGalleryID,
		'idx' => $imageID
		);

		if ( !empty($res)) {
			$getParam['res'] = $res;
		}

		if ( $downloadType == DOWNLOAD_NORMAL) {
			$downloadLink = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon1']) ? JPG_ICON : $this->conf['downloadIcon1'] ).'" border="0"></a> ';
		} else {
			$downloadLink = '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'"><img src="'.( empty( $this->conf['downloadIcon2']) ? ZIP_ICON : $this->conf['downloadIcon2'] ).'" border="0"></a> ';
		}

		return $downloadLink;
	}

	/**
	 * Function to get inter gallery navigation
	 *
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @return string
	 */
	function getInterGalleryNavi( $galleryID) {
		$interGalleryNavi = '';
		$template['page'] = $this->cObj->fileResource( $this->singleViewTemplatePath);
		
		if ( $template['page'] !== '') {

			// Template for INTER GALLERY NAVIGATION LINK
			$template['INTER_GALLERY_NAVI'] = $this->cObj->getSubpart( $template['page'], 'INTER_GALLERY_NAVI');
			
			if ( $template['INTER_GALLERY_NAVI'] !== '') {
				$parentGalleryID = $this->galleries[ $galleryID]['parent'];
				
				// Continue if its not a root gallery for which you can't find any other gallery in the same level
				if ( $parentGalleryID >= 0) {
					foreach (  $this->galleries[ $parentGalleryID]['elements'] as $key => $galleryItem) {
						if ( is_array( $galleryItem)) {
							$subGalleries[$galleryItem['index']] = $galleryItem['name'];
						}
					}
				}
				
				if ( count( $subGalleries) > 0) {
					$template['INTER_GALLERY_LINK'] = $this->cObj->getSubpart( $template['INTER_GALLERY_NAVI'], 'INTER_GALLERY_LINK');
					
					foreach ( $subGalleries as $gid => $galleryName) {
						$replace = array (
						'INTER_GALLERY_ID' => $gid,
						'SELECTED' => ( $gid == $this->currentGalleryID)
						              ? 'selected'
						              : '',
						'INTER_GALLERY_NAME' => $galleryName
						);
						
						$interGalleryLinks .= $this->cObj->substituteMarkerArray( $template['INTER_GALLERY_LINK'], $replace, '###|###', 1);
					}
					
					$replace['INTER_GALLERY_LINK'] = $interGalleryLinks;
					$interGalleryNavi = $this->cObj->substituteSubpart( $template['INTER_GALLERY_NAVI'],'###INTER_GALLERY_LINK###',$replace['INTER_GALLERY_LINK'], 0);
				}
			}
		}
		
		return $interGalleryNavi;
	}

    /**
     * Fucntion to add CSS
     *
     * @access private
     */
     function addGalleryCSS() {
        $css = '
    		plugin.tx_bahagphotogallery_pi1 {
                _CSS_DEFAULT_STYLE (
                    .gallImg a:link div{ border:#ffffff solid 1px;}
                    .gallImg a { text-decoration: none;}
                    .gallImg a:hover div{ border:#ff0000 solid 1px;}
                    .gallImg a:visited div{ border:#ffffff solid 1px;}
                    .gallImg a:visited:hover div{ border:#ff0000 solid 1px;}
                    .gallImg a:hover { color: #ff0000;}
                )                                    
    		}
        ';
		
    	    t3lib_extMgm::addTypoScript( $this->extKey, 'setup', $css, 43);        
     }
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/bahag_photogallery/pi1/class.tx_bahagphotogallery_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/bahag_photogallery/pi1/class.tx_bahagphotogallery_pi1.php"]);
}

?>
