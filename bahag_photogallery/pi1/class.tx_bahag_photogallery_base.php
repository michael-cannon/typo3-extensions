<?php
require_once(PATH_tslib."class.tslib_pibase.php");
require_once(t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_common.php');
require_once(t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_graphics.php');
require_once(t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_paging.php');
require_once(t3lib_extMgm::extPath('bahag_photogallery').'constants.php');

class tx_bahag_photogallery_base extends tslib_pibase {

	/**
	* Path to root of photo gallery relative to Typo3's root directory
	* @var string
	*/
	var $rootGalleryPath;

	/**
	* Path to Photo Gallery relative to Typo3's root directory
	* @var string
	*/
	var $currentGalleryID;

	/**
	* Image types allowed for gallery
	* @var array
	*/
	var $allowedImageTypes;

	/**
	* Default Gallery name
	* @var string
	*/
	var $defaultGalleryName;

	/**
	* Thumbnails directory name
	* @var string
	*/
	var $thumbsDirName;

	/**
	* Preview Thumbnails directory name
	* @var string
	*/
	var $largeThumbsDirName;

	/**
	* Gallery cache directory
	* @var string
	*/
	var $galleryCacheDir;

	/**
	* Gallery temporary directory
	* @var string
	*/
	var $galleryTempDir;

	/**
	* Prefix to tumbnail's file name
	* @var string
	*/
	var $thumbPrefix;

	/**
	* Widht of thumbnail
	* @var integer
	*/
	var $thumbWidth;

	/**
	* Height of thumbnail
	* @var integer
	*/
	var $thumbHeight;

	/**
	* Flag to display large thumbnail
	* @var boolean
	*/
	var $dispLargeThumb;

	/**
	* Large thumbnail dimension type
	* @var string
	*/
	var $largeThumbDimType;

	/**
	* Width of large thumbnail
	* @var integer
	*/
	var $largeThumbWidth;

	/**
	* Height of large thumbnail
	* @var integer
	*/
	var $largeThumbHeight;

	/**
	* Weight of Image
	* @var integer
	*/
	var $imageWidth;

	/**
	* Height of Image
	* @var integer
	*/
	var $imageHeight;

	/**
	* Flag to determine whether recurrsive parsing for gallery is required or not
	* @var bool
	*/
	var $parseRecurrsive;

	/**
	* Number of image rows in gallery
	* @var integer
	*/
	var $imageRows;

	/**
	* Number of image columns in gallery
	* @var integer
	*/
	var $imageColumns;

	/**
	* Image to be used for watermark
	* @var string
	*/
	var $watermarkImage;

	/**
	* Watermark dissolve value
	* @var integer
	*/
	var $watermarkDissolveVal;


	/**
	* Gallery Page ID
	* @var integer
	*/
	var $resultPage;

	/**
	* Table containing gallery description
	* @var string
	*/
	var $tbGalleries;

	/**
	* Table containing gallery description
	* @var string
	*/
	var $storageFolder;

	/**
	* Table containing gallery image description
	* @var string
	*/
	var $tbGalleryImgDesc;

	/**
	* Flag to indicate grayscale gallery
	* @var boolean
	*/
	var $imgGrayscale;

	/**
	* Flag to indicate grayscale thumbnails
	* @var boolean
	*/
	var $tmbGrayscale;

	/**
	* Quality factor of image
	* @var boolean
	*/
	var $imgQuality;

	/**
	* Flag to know whether images to display in popup window or not
	* @var boolean
	*/
	var $dispInPopup;

	/**
	* Available resolutions for image download
	* @var array
	*/
	var $downloadImgResolutions;

	/**
	* Dimension based on which we have to scale the download image
	* @var string
	*/
	var $downloadImgDimType;

	/**
	* Index of current image within gallery,  in enlarged view
	* @var integer
	*/
	var $currentImgID;

	/**
	 * Current action
	 * @var string
	 */
	var $action;

	/**
	 * Array containing all the galleries
 	 * @var array
	 */
	var $galleries;

	/**
	 * Array containing all gallery items
 	 * @var array
	 */
	var $galleryItems;

	/**
	 * No. of sub-gallery images to preview in each gallery 
 	 * @var integer
	 */
	var $subGalleryPreviewImgCount;

	/**
	 * Flag for displaying Image date in single view
 	 * @var boolean
	 */
	var $dispImageDate;

	/**
	 * Variable to store preview images
 	 * @var array
	 */
	var $previewImages;

	/**
	 * Type of view for sub gallery
 	 * @var array
	 */
	var $subGalleryViewType;

	/**
	 * Directories to be by-passed while recursive
 	 * @var array
	 */
	var $excludeDir;

	/**
	 * Object to handle operations pertaining to image IPTC data
	 *
	 * @access private
 	 * @var class
	 */
	var $IPTCData;

    /**
     * Object to `tx_bahag_photogallery_common` class
     * Contains generic functions
     *
     * @access private
     * @var class
     */
     var $utils;

    /**
     * Object to `tx_bahag_photogallery_graphics` class
     * Contains functions related to image processing
     *
     * @access private
     * @var class
     */
     var $graphics;

	/**
	 * Path to upload folder
	 * @var string
	 */
	var $uploadsFolder;




	/**
	 * Initialize base class variables.
	 * @access private
	 */	
	function tx_bahag_photogallery_base() {

		$this->rootGalleryPath = 'fileadmin/img_gallery';
		$this->currentGalleryID = 0;
		$this->allowedImageTypes = array('jpg', 'gif', 'png');
		$this->defaultGalleryName = 'New Photo Gallery';
		$this->thumbsDirName = 'SmallThumbnails';
		$this->largeThumbsDirName = 'LargeThumbnails';
		$this->galleryCacheDir = 'cache';
		$this->galleryTempDir = 'temp';
		$this->thumbPrefix = 'tmb_';
		$this->thumbWidth = THUMB_WIDTH;
		$this->thumbHeight = '';
		$this->dispLargeThumb = false;
		$this->largeThumbDimType = 'width';
		$this->largeThumbWidth = LARGE_THUMB_WIDTH;
		$this->largeThumbHeight = '';
		$this->imageWidth = IMAGE_WIDTH;
		$this->imageHeight = '';
		$this->parseRecurrsive = true;
		$this->resultPage = 1;
		$this->imageRows = IMAGE_ROWS;
		$this->imageColumns = IMAGE_COLUMNS;
		$this->watermarkImage = '';
		$this->watermarkDissolveVal = 20;
		$this->tbGalleries = 'tx_bahagphotogallery_galleries';
		$this->tbGalleryImgDesc = 'tx_bahagphotogallery_images';
		$this->storageFolder = '';
		$this->imgGrayscale = 0;
		$this->tmbGrayscale = 0;
		$this->imgQuality = 75;
		$this->dispInPopup = false;
		$this->downloadImgResolutions = array();
		$this->currentImgID = 1;
		$this->action = '';
		$this->galleries = array();
		$this->galleryItems = array();
		$this->dispImageDate = false;
		$this->previewImages = array();
		$this->subGalleryPreviewImgCount = 0;
		$this->subGalleryViewType = 'preview';
		$this->excludeDir = array('.', '..', $this->thumbsDirName, $this->galleryCacheDir, $this->galleryTempDir, $this->largeThumbsDirName);
		$this->uploadsFolder = PATH_site.'uploads/tx_bahagphotogallery/';		
   		
   		// Instance of generic functions class
   		$this->utils = t3lib_div::makeInstance( 'tx_bahag_photogallery_common');
   		
   		// Instance of image processing library
   		$this->graphics = t3lib_div::makeInstance( 'tx_bahag_photogallery_graphics');

		clearstatcache();
	}



	/**
	 * Generate an array structure of whole gallery
	 * @access public
	 */
	function genGalleryStruc() {
		$this->galleryItems = $this->getGalleryItems();
	}

	/**
	 * Get recurrsive listing of gallery elements.
	 * @return array
	 * @param string $location - Path to gallery folder, relative to root gallery folder
	 * @access private
	 */	
	function getGalleryItems( $location = null, $parent = -1) {
		$imgList = array();		// array containing list of images in provided gallery location
		$excludeDir = array('.', '..', $this->thumbsDirName, $this->galleryCacheDir, $this->galleryTempDir, $this->largeThumbsDirName);
		$galleryItems = array();
		$subGalleries = array();

		if( empty( $location)) {
			$location = $this->rootGalleryPath;
		}

		if ( is_dir( PATH_site.$location)) {
			if ( $handle = opendir( PATH_site.$location)) {
				while ( false !== ( $dir_item = readdir( $handle))) {
					if ( !in_array( $dir_item, $excludeDir)) {
						if ( is_dir( PATH_site.$location.'/'.$dir_item) && $this->parseRecurrsive) {
							$subGalleries[] = $location.'/'.$dir_item;
						} else {
							if( $this->isImageFile( $dir_item)) {
								array_push( $imgList, $location.'/'.$dir_item);
							}
						}
					}
				}

				if ( (  is_array( $imgList) && !empty( $imgList)) || ( is_array( $subGalleries) && !empty( $subGalleries))) {
				
					$galleryItems['index']    = count( $this->galleries);
					$galleryItems['parent']   = $parent;
					$galleryItems['name']     = $this->getGalleryName( $location);
					$galleryItems['path']     = $location;
					$galleryItems['elements'] = &$imgList;
					$this->galleries[$galleryItems['index']] = $galleryItems;
					
				}

				while ( $subGallery = array_shift($subGalleries)) {
					$subGalleryItems = $this->getGalleryItems( $subGallery, $galleryItems['index']);

					if ( is_array( $subGalleryItems) && !empty( $subGalleryItems)) {
						array_push( $imgList, $subGalleryItems);
					}
				}
			}
		}

		return $galleryItems;
	}



	/**
	 * Get content of corresponding to provided gallery-ID.
	 *
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @return array
	 */	
	function getGalleryContent( $galleryID) {
		$galleryContent = '';
		$counter = 0;

		if( !is_array( $this->galleries) || empty( $this->galleries)) {
			return $galleryContent;
		}

		$maxGalleryID = count( $this->galleries) - 1;

		if ( $gallerID > $maxGalleryID) {
			$galleryID = $maxGalleryID;
		} elseif ( $maxGalleryID < 0) {
			$galleryID = 0;
		}

		$gallery = $this->galleries[ $galleryID];

		if ( false !== ($template = file_get_contents( $this->galleryOverviewTemplate))) {
			$template  = $this->cObj->getSubpart( $template, '###GALLERY_CONTENT###');
			$imageList = '';
			$col = $this->imageColumns;
			$row = 0;
			$start = $this->imageRows * $this->imageColumns * ( $this->resultPage-1);
            $thumbCount = 0;

			foreach( $gallery['elements'] as $key => $value) {
				if ( is_array($value) && !empty($value)) {
					$subGalleryContent .= $this->getSubGalleryContent( $value['index']);
				} else {
					if ( $counter++ >= $start && $row < $this->imageRows) {
						if($col == $this->imageColumns) {
							$imgRow = '<tr>';
						}
						
						$getParam = array(
    						'action'     => 'viewImage',
    						'gallery'    => $gallery['index'],
    						'resultPage' => $this->resultPage,
    						'idx'        => $counter - 1
						);
						
                        $single_img_tmpl = $this->cObj->getSubpart( $template, '###IMAGE###');
                        $thumbSrc =  $this->getThumbnail( $value);
                        
                        if ( strcmp( $thumbSrc, '')) {
                            $thumbCount++;
                            
                            $replace = array(
                                'IMAGE_SOURCE'  => $this->getUrl($GLOBALS['TSFE']->id, $getParam),
                                'THUMB_SOURCE'  => $this->getThumbnail( $value),
                                'IMAGE_COMMENT' => '',
                            );

                            // If floating preview is enable
    						if ( $this->dispLargeThumb) {
    							$this->previewImages[$key] = $this->getLargeThumbnail( $value);
    							$replace['IMG_MOUSEOVER_JS'] = 'showPreviewImage('.$key.')';
    							$replace['IMG_MOUSEOUT_JS' ] = 'hideDiv()';
    						} else {
    							$replace['IMG_MOUSEOVER_JS'] = '';
    							$replace['IMG_MOUSEOUT_JS' ] = '';
    						}
    						
                            // If thumbnail highlighting is enable
    						if ( $this->dispThumbnailHighlight) {
    							$this->previewImages[$key] = $this->getLargeThumbnail( $value);
    							$replace['DIV_MOUSEOVER_JS']  = 'defStyle=this.style.border; ';
    							$replace['DIV_MOUSEOVER_JS'] .= 'this.style.border=\'solid '.$this->thumbHighlightColor.' 1px\'';
    							$replace['DIV_MOUSEOUT_JS' ]  = 'this.style.border=defStyle;';
    						} else {
    							$replace['DIV_MOUSEOVER_JS'] = '';
    							$replace['DIV_MOUSEOUT_JS' ] = '';
    						}

                            $imgRow .= $this->cObj->substituteMarkerArray(  $single_img_tmpl, $replace, '###|###', 1);
    						$col--;

    						if( $col == 0) {
    							$imageList .= $imgRow.'</tr>';
    							$col        = $this->imageColumns;
    							$row++;
    						}
                        }
					} else {
						continue;
					}
				}
			}
			
			if ( ($col > 0 ) && ($col < $this->imageColumns)) {
			    while ( $col--) {
			        $imgRow .= '</td>&nbsp;</td>';
			    }
			    
			    $imageList .= $imgRow.'</tr>';
			}

			$replace = array(
    			'###BREADCRUMBS###'   => ( $this->dispBreadcrumbs)
    			                         ? $this->getBreadcrumbs( $galleryID)
    			                         : '',
    			'###GALLERY_NAME###'  => $galleryItems['name'],
    			'###IMAGE_LIST###'    => ( $thumbCount > 0)
    			                         ? $imageList
    			                         : '',
    			'###PAGING###'        => $this->getNavigation(),
    			'SUB_GALLERY_CONTENT' => $subGalleryContent
			);

			if ( $this->dispLargeThumb) {
				$galleryContent .= $this->getPageCSS();
				$galleryContent .= $this->getPageJS();
			}

			$galleryContent .= $this->cObj->substituteMarkerArrayCached( $template,array(),$replace,array());
		}
		
		return $galleryContent;
	}

	/**
	 * Get sub gallery content.
	 *
	 * @return array
	 * @param array $galleryItems  Listing of sub gallery elements
	 * @return array
	 */	
	function getSubGalleryContent( $galleryID) {
		$subGalleryContent = '';
		$imageList = '';

		if ( !strcmp( strtolower( $this->galleryViewType), 'normal')) {
			$subGalleryLink = $this->getUrl( $GLOBALS['TSFE']->id, array('gallery'=>$galleryID));
		} else {
			$subGalleryLink = $this->getUrl( $GLOBALS['TSFE']->id, array('action'=>'viewGallery', 'gallery'=>$galleryID));
		}

		$template = file_get_contents( $this->galleryOverviewTemplate);
		$htmlTemplateArray['SUB_GALLERY_CONTENT'] = $this->cObj->getSubPart( $template,'###SUB_GALLERY_CONTENT###');
		$subGalleryImages = $this->getGalleryImages( $galleryID, true);
		$galleryImageCount = count( $subGalleryImages);

		if ( $this->subGalleryPreviewImgCount < $galleryImageCount && $this->subGalleryPreviewImgCount != -1) {
			$displayImageCount = $this->subGalleryPreviewImgCount;
		} else {
			$displayImageCount = $galleryImageCount;
		}

		$imageList .= '<table border="0" align="right">';

		for ( $imageCount = 0, $row = 0; $imageCount < $displayImageCount && $row < $this->imageRows; $row++) {
			$imageList .= '<tr>';
			$getParam = array(
			'action'=>'viewImage',
			//'gallery'=> $subGalleryImages[$imageCount]['galleryID'],
			//'gallery'=> $galleryID,
			'resultPage' => '',
			);
			
			//debug( $subGalleryImages[$imageCount]);

            $thumbCount = 0;
            $imgRow = '';
            
			for ( $col = 0; $imageCount < $displayImageCount && $col < $this->imageColumns; $imageCount++) {
				//$getParam['idx'] = $imageCount;
				$getParam['idx'] = $subGalleryImages[$imageCount]['idx'];
			    $getParam['gallery'] = $subGalleryImages[$imageCount]['galleryID'];
				$thumbSrc = $this->getThumbnail( $subGalleryImages[$imageCount]['img']);
				//$thumbSrc = $this->getThumbnail( $subGalleryImages[$imageCount]);
				
				if ( strcmp( $thumbSrc, '')) {
				    $thumbCount++;
				    $col++;
				    
    				if ( !strcmp('preview', $this->subGalleryViewType)) {
    					$imgRow .=  '
    						<td>
    							<img src="'.$thumbSrc.'" border="0">
    						</td>
    					';
    				} else {
    					$imgRow .=  '
    						<td class="gallImg">
    							<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $getParam).'"><div>
    							<img src="'.$thumbSrc.'" border="0" vspace="2" hspace="2"></div></a>
    						</td>
    					';
    				}
				}
			}

            while ( $col++ < $this->imageColumns) {
                $imgRow = '<td>&nbsp;</td>'.$imgRow;
            }
			
			$imageList .= $imgRow.'</tr>';
		}

		$imageList .= '</table>';

		$replace = array(
		'###SUB_GALLERY_LINK###' => $subGalleryLink,
		'###SUB_GALLERY_NAME###' => $this->galleries[$galleryID]['name'],
		'###GALLERY_IMG_COUNT###' => $galleryImageCount,
		'###SUB_GALLERY_PREVIEW_IMAGES###' => ( $thumbCount > 0)
		                                      ? $imageList
		                                      : ''
		);
		$subGalleryContent = $this->cObj->substituteMarkerArrayCached($htmlTemplateArray['SUB_GALLERY_CONTENT'],array(),$replace,array());

		return $subGalleryContent;
	}



	/**
	* Function to check whether provided file is an allowed image file or not.
	* @return boolean
	* @param array $fileName - Image file name
	* @access private
	*/	
	function isImageFile( $fileName) {
		$path_details = pathinfo( $fileName);
		$fileExt = $path_details['extension'];

		if ( in_array( strtolower($fileExt), $this->allowedImageTypes)) {
			return true;
		} else {
			return false;
		}
	}

	/**
 	 * Get path to the thumbnail of the provided image file.
	 *
	 * @access private
 	 * @param array $image - path to image file relative to the root gallery folder
 	 * @return string
	 */	
	function getThumbnail( $image) {
		$thumbnail = '';

		if ( !empty( $image)) {
			$imgPathInfo = pathinfo($image);
			$imageName = str_replace(' ','',$imgPathInfo['basename']);
			$galleryPath = $imgPathInfo['dirname'];
			list($imgName, $imgExt) = explode('.', $imageName);

			// Create and return thumbnail if it doesn't exists
			$basename = PATH_site.$galleryPath.'/'.$this->thumbsDirName.'/'.$this->thumbPrefix.$imgName;
			$thumbSuffix = $this->thumbWidth.$this->thumbHeight.THUMB_QUALITY.$this->tmbGrayscale;

			$thumbPath = $galleryPath.'/'.$this->thumbsDirName.'/'.$imgName.$thumbSuffix.'.jpg';
            //echo '<br />'.$thumbPath.'<br />';

			if( file_exists( $thumbPath)){
			    $thumbnail = $thumbPath;
			}
		}

		return $thumbnail;
	}

	/**
 	 * Get path to floating thumbnail of the provided image.
	 *
	 * @access private
 	 * @param array $image    Path to image file relative to the root gallery folder
 	 * @return string
	 */	
	function getLargeThumbnail( $image) {
		$thumbnail = '';

		if ( !empty( $image)) {
			$imgPathInfo = pathinfo( $image);
			$imageName = str_replace(' ','',$imgPathInfo['basename']);
			$galleryPath = $imgPathInfo['dirname'];
			list($imgName, $imgExt) = explode('.', $imageName);

			$thumbSuffix = $this->largeThumbWidth.$this->largeThumbHeight.THUMB_QUALITY.$this->tmbGrayscale;
			$thumbPath = $galleryPath.'/'.$this->largeThumbsDirName.'/'.$imgName.$thumbSuffix.'.jpg';

			if( file_exists( $thumbPath)){
			    $thumbnail = $thumbPath;
			}
		}

		return $thumbnail;
	}

	/**
	 * Get image for single enlarge view
	 *
	 * @access private
	 * @param string $imagePath - Image path relative to the root gallery
	 * @return string
	 */
	function getCachedImage( $imagePath) {
	    $cachedImage = '';
	    
		if ( !empty( $imagePath) && file_exists( PATH_site.$imagePath)) {
			$imgPathInfo = pathinfo($imagePath);
			$imageName = str_replace(' ','',$imgPathInfo['basename']);
			$galleryPath = $imgPathInfo['dirname'];
			list($imgName, $imgExt) = explode('.', $imageName);

			$basename = $galleryPath.'/'.$this->galleryCacheDir.'/'.$imgName;
			$imgNameSuffix = $this->imageWidth.$this->imageHeight.$this->imgQuality.$this->imgGrayscale;

            if ( file_exists( $this->watermarkImage)) {
                clearstatcache();
                $imgNameSuffix .= filemtime( $this->watermarkImage);
            }

			$cachedImgPath = $basename.$imgNameSuffix.'.jpg';

			if ( file_exists( $cachedImgPath)) {
			    $cachedImage = $cachedImgPath;
			}
		}

		return $cachedImage;
	}

	/**
	 * Get the count of no. of images in provided gallery.
	 * @return integer
	 * @param integer $galleryID - ID of gallery
	 * @access private
	 */	
	function getGalleryImgCount( $galleryID) {
		$imgCount = 0;		/* No. of images in gallery */

		foreach ( $this->galleries[$galleryID]['elements'] as $galleryItem) {
			if ( !is_array($galleryItem)) {
				$imgCount++;
			}
		}

		return $imgCount;
	}

	/**
	 * Get the Typo3 compliant url.
	 * @return string
	 * @param integer $pid - Page ID
	 * @param array $getParam - array containg parameters and their corresponding values to send with URL
	 * @access private
	 */	
	function getUrl($pid, $getParam=null) {
		$url = $this->pi_getPageLink($pid, null, $getParam);

		return $url;
	}



	/**
	 * Get page navigation for gallery
	 * @return string
	 * @access private
	 */	
	function getNavigation() {
		$content = '';
		$itemCount = $this->getGalleryImgCount( $this->currentGalleryID);
		$pageSize = $this->imageRows * $this->imageColumns;

		$objPaging = 	new tx_bahag_photogallery_paging( $this->resultPage, $itemCount, $pageSize);
		$nav = $objPaging->getPageNav();

		if (is_array($nav) && !empty($nav)) {
			if (!empty($nav['prev']['resultPage'])) {
				$urlParameters = array(
				'resultPage' => $nav['prev']['resultPage'],
				'gallery' => $this->currentGalleryID
				);
				$content = '<a href = '.$this->getUrl($GLOBALS["TSFE"]->id, $urlParameters).'>'.$this->pi_getLL('previous_page').'</a> | ';
			}

			for ($i = 1; $i <= count($nav); $i++) {
				if (!empty($nav[$i]['resultPage'])) {
					if ($nav[$i]['resultPage'] == $nav['curr']['resultPage']) {
						$content .= ' '.$i.' ';
					} else {
						$urlParameters = array('resultPage' => $i,
						'gallery' => $this->currentGalleryID
						);
						$content .= ' <a href = '.$this->getUrl($GLOBALS["TSFE"]->id, $urlParameters).'>'.$i.'</a>  ';
					}
				}
			}

			if (!empty($nav['next']['resultPage'])) {
				$urlParameters = array(
				'resultPage' => $nav['next']['resultPage'],
				'gallery' => $this->currentGalleryID
				);
				$content .= ' | <a href = '.$this->getUrl($GLOBALS["TSFE"]->id, $urlParameters).'>'.$this->pi_getLL('next_page').'</a>';
			}
		}

		return $content;
	}

	/**
	* Get Gallery Description.
	* @return array
	* @param string $galleryPath - Path to gallery folder, relative to root gallery folder
	* @access private
	*/	
	function getGalleryDescription( $galleryPath) {
		$galleryDescription = '';

		if ( is_dir(PATH_site.$galleryPath)) {
            $tmp = explode('/', $galleryPath);
			$galleryDescription = array();
			$galleryDescription['galleryFolder'] = array_pop( $tmp);
			$galleryDescription['galleryAlias'] = '';
			$galleryDescription['galleryComment'] = '';

			$select = 'DISTINCT *';
			$from   = $this->tbGalleries;
			$where  = 'path = \''.$galleryPath.'\' AND deleted = 0 AND hidden = 0 ';
			$where .= ( $this->storageFolder > 0) ? 'AND pid = '.$this->storageFolder : '';

            // Uncomment to debug
            // echo $GLOBALS['TYPO3_DB']->SELECTquery($select, $from, $where);
            
			$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);

			if ($rs) {
				if ( mysql_num_rows( $rs)) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);

					$galleryDescription['galleryAlias'] = $row['name'];
					$galleryDescription['galleryComment'] = $row['comment'];
				}
			}
		}

		return $galleryDescription;
	}



	/**
	* Get the name (Alias) of the gallery provided the path.
	* @return string
	* @param srting $galleryPath - Path to the gallery relative to the root gallery folder
	* @access private
	*/	
	function getGalleryName( $galleryPath) {
		$galleryName = '';

		if ( is_dir( $galleryPath)) {
			$galleryDescription = $this->getGalleryDescription( $galleryPath);
			$galleryName = !empty( $galleryDescription['galleryAlias']) ? $galleryDescription['galleryAlias'] : $galleryDescription['galleryFolder'];
		}

		return $galleryName;
	}



	/**
	* Get Image Description.
	* @param string $imagePath - Path to gallery folder, relative to root gallery folder
	* @access private
	* @return array
	*/	
	function getImageDescription( $imagePath) {
		$imageDescription = '';

		if ( file_exists( PATH_site.$galleryPath)) {
			$select = 'DISTINCT *';
			$from   = $this->tbGalleryImgDesc;
			$where  = 'path = \''.$imagePath.'\' AND deleted = 0 AND hidden = 0 ';
			$where .= ( $this->storageFolder > 0)
			          ? 'AND pid = '.$this->storageFolder
			          : '';

            // Uncomment to debug
            // echo $GLOBALS['TYPO3_DB']->SELECTquery($select, $from, $where);
            
			$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);

			if ($rs) {
				if ( mysql_num_rows( $rs)) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
					$imageDescription = $row;
				}
			}
		}

		return $imageDescription;
	}


	/**
	 * Get the front-end content for enlarged image view
	 * @access private
	 * @return string
	 */	
	function getEnlargedView() {
		$singleViewContent = '';		// Page content for single enlarged view
		$template = '';					// template file for single enlarged view

		if(false !== ( $template = file_get_contents( $this->singleViewTemplate))) {
			
			$image    = $this->imageAt( $this->currentGalleryID, $this->currentImgID);
			$pathInfo = pathInfo( $image);
			list($imgName, $imgExt) = explode('.', $pathInfo['basename']);
			$imgName = $imgName.$this->imageWidth.$this->imageHeight.$this->imgQuality.$this->grayscale.'jpg';
			$isValidPopView   = $this->dispInPopup && !strcmp( $this->action, 'popView');
			
		    // Select whole template, including HTML header, if its popup view
		    // ELSE go for sub-template marked as 'GALLERY_CONTENT'
		    if ( !$isValidPopView) {
			    $template = $this->cObj->getSubpart( $template, 'GALLERY_CONTENT');
			}
			
			$enlargedViewIcon = $isValidPopView
			                    ? ''
			                    : ( empty( $this->conf['enlargedViewIcon']) ? ENLARGE_VIEW_ICON : $this->conf['enlargedViewIcon']);
			$zippedDownloadLink = $isValidPopView
			                      ? ''
			                      : $this->getZippedDownloadImgLink( $this->currentGalleryID, $this->currentImgID);

			$replace = array();
			$replace['<!--###DOWNLOAD_OPTION###-->']    = ( $isValidPopView || $this->imgDownloadOption === 'NONE')
			                                              ? ''
			                                              : $this->getDownloadOptions( $image);
			$replace['<!---###IMG_INFO###--->']         = ( $isValidPopView)
			                                              ? ''
			                                              : $this->getImageInfo();
			$replace['<!--###INTER_GALLERY_NAVI###-->'] = ( $isValidPopView)
			                                              ? ''
			                                              : $this->getInterGalleryNavi( $this->currentGalleryID);
			$replace['###IMAGE_IPTC_DATA###']           = ( $isValidPopView || !$this->dispIPTCData)
			                                              ? ''
			                                              : $this->getImgIPTCDetails( PATH_site.$image, $this->reqIPTCDataItems);

			if ( $isValidPopView) {
				$replace['<!--###THUMB_VIEW_LINK###-->'] = '';
			}

			if ( !$this->dispInPopup || $isValidPopView) {
				$replace['<!--###CLICK_TO_ZOOM###-->'] = '';
			}

			$template  = $this->cObj->substituteMarkerArrayCached( $template,array(),$replace,array());
			$imageNavi = $this->getImageNavi( $this->currentGalleryID);
			$imageDesc = $this->getImageDescription( $image);

            // Set whether the `Back to Gallery` link will go to gallery view, or sectional view
			$getParam['action'] = !strcmp( strtolower( $this->galleryViewType), 'normal') 
			                      ? 'galleriesOverview'
			                      : 'viewGallery';
			                      
			$replace = array(
			    '###BREADCRUMBS###'        => ( $isValidPopView || !$this->dispBreadcrumbs)
			                                  ? ''
			                                  : $this->getBreadcrumbs( $this->currentGalleryID),
			    '###GALLERY_NAME###'       => $isValidPopView ? '': $this->getGalleryName( $pathInfo['dirname']),
			    '###IMAGE_COMMENT###'      => $isValidPopView ? '': $imageDesc['comment'],
			    '###IMAGE_NAVI_1###'       => $imageNavi[0],
			    '###FORM_ACTION###'        => $this->getUrl( $GLOBALS['TSFE']->id, $getParam),
			    '###FORM_METHOD###'        => 'POST',
			    '###IMAGE_NAVI_2###'       => $imageNavi[1],
			    '###ENLARGED_VIEW_ICON###' => $enlargedViewIcon,
                '###ZOOM_TEXT###'          => $this->pi_getLL('zoom_text'),
			    '###IMAGE_SRC###'          => $isValidPopView ? $image : $this->getCachedImage( $image),
			    '###ZIPPED_DOWNLOAD###'    => $zippedDownloadLink,
			    '###JS###'                 => (!$this->dispInPopup || $isValidPopView) ? '': $this->getWinPopupJS( $image),
			);
			
			$getParam['gallery'] = $this->currentGalleryID;
			$getParam['resultPage'] = $this->resultPage;

			$replace['###GALLERY_LINK###']      = $isValidPopView
			                                      ? ''
			                                      : $this->getUrl( $GLOBALS['TSFE']->id,$getParam);
			$replace['###GALLERY_LINK_TEXT###'] = $isValidPopView
			                                      ? ''
			                                      : $this->pi_getLL( 'gallery_link_text');
			$replace['###INTER_GALLERY_SUBMIT_LABEL###'] = $isValidPopView
			                                               ? ''
			                                               : $this->pi_getLL( 'inter_gallery_submit_label');
			$singleViewContent = $this->cObj->substituteMarkerArrayCached( $template,$replace,array(),array());
		}
		
		return $singleViewContent;
	}


	/**
	* Get javascript for image popup window 
	* @return string
	* @param string $imagePath - Image path relative to the root gallery
	* @access private
	*/
	function getWinPopupJS()  {
		$getParam = array(
		'action'=> 'popView',
		'gallery'=> $this->currentGalleryID,
		'resultPage' => $this->resultPage,
		'idx' => $this->currentImgID
		);

		$imagePath = $this->imageAt( $this->currentGalleryID, $this->currentImgID);
		$imgDim = tx_bahag_photogallery_graphics::getImgDimensions( $imagePath);
		$imgDim['height'] = $imgDim['height'] + 95;
		$imgDim['width'] = $imgDim['width'] + 40;
		$JS = 'window.open(\''.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'\',\'name\',\'height='.$imgDim['height'].',width='.$imgDim['width'].', resizable=1,scrollbars=1\'); return false;';

		return$JS;
	}

	/**
	 * Get link for zipped image file for download 
	 * @access private
	 * @param string $imagePath - Image path relative to the root gallery
	 * @return array
	 */
	function getZippedDownloadImgLink( $galleryID, $imageID) {
		$getParam = array(
		'action' => 'zippedDownload',
		'gallery' => $galleryID,
		'idx' => $imageID
		);

		return '<a href="'.$this->getUrl( $GLOBALS['TSFE']->id, $getParam).'">'.ZIPPED_DOWNLOAD_TEXT.'</a>';
	}


	/**
	 * Get download image
	 * @access private
	 * @param string $imagePath - Image path relative to the root gallery
	 * @param string $res - Resolution for download image
	 * @return string
	 */
	function getDownloadImage( $imagePath, $res) {
		$downloadImage = '';

		if ( !empty( $imagePath) && file_exists( $imagePath)) {
			$imgPathInfo = pathinfo($imagePath);
			$imageName = str_replace(' ','',$imgPathInfo['basename']);
			$galleryPath = $imgPathInfo['dirname'];
			list($imgName, $imgExt) = explode('.', $imageName);

			// Create temporary directory for download images, if it doesn't exists
			if ( !is_dir( $galleryPath.'/'.$this->galleryTempDir)) {
				$cmd = 'mkdir '.$galleryPath.'/'.$this->galleryTempDir;

				if ( false === popen($cmd, 'r')) {
					return '';
				}
			}

			$basename = $galleryPath.'/'.$this->galleryTempDir.'/'.$imgName;
			$imgNameSuffix = $this->imageWidth.$this->imageHeight.$this->imgQuality.$this->imgGrayscale.$res;
			$downloadImagePath = $basename.$imgNameSuffix.'.jpg';

			if ( !file_exists( $downloadImagePath) && !empty( $downloadImagePath)) {
				if ( false === popen('rm -f '.$basename.'*', 'r')) {
					return '';
				}
				
				$imageConf = array(
				'width' => strcmp( $this->downloadImgDimType, 'width') ? '' : $res,
				'height' => strcmp( $this->downloadImgDimType, 'height') ? '' : $res,
				'grayscale' => $this->imgGrayscale,
				'quality' => $this->imgQuality,
				'newImgPath' => $downloadImagePath
				);

				if ( !tx_bahag_photogallery_graphics::transformImage( $imagePath, $imageConf)) {
					return $imagePath;
				}
			}
		}
		return $downloadImagePath;
	}


	/**
	 * Function to download image
     *
	 * @access private
	 * @param string $res  Resolution for download image
	 * @return string
	 */
	function downloadImage( $res){
		$imagePath = $this->imageAt( $this->currentGalleryID, $this->currentImgID);

        // Get image information. This function is also responsible for checking whether
        // provided file is an image file or not.
        $imgInfo = $this->graphics->getImageInfo( PATH_site.$imagePath);
            
        if ( is_array( $imgInfo) && !empty( $imgInfo)) {
    		$downloadImgPath = (!empty( $res)) 
    		                   ? $this->getDownloadImage( $imagePath, $res) 
    		                   : $imagePath;

    		$image['name'] = basename( $downloadImgPath);
    		$headerParam = array(
    		'contentType' => image_type_to_mime_type( $imgInfo[2]),
    		'contentLength' => filesize( $downloadImgPath),
    		'fileName' => $image['name']
    		);

    		$this->setPageHeader( $headerParam);
    		readfile( $downloadImgPath);
        }

		exit;
	}


	/**
	 * Get download optins for images
     *
	 * @access private
	 * @param string $image - Image path relative to the root gallery
	 * @return string
	 */
	function getDownloadOptions( $image) {
		$downloadOptions = '';
        $template = array();
		$normalDownloadImgLinks = $this->getDownloadImgLinks( $image);
		$zippedDownloadImgLinks = $this->getDownloadImgLinks( $image, DOWNLOAD_ZIPPED);

		if ( is_array( $normalDownloadImgLinks) && !empty( $normalDownloadImgLinks)) {
			if ( $template['all'] = file_get_contents( $this->singleViewTemplate)) {
			
			    // Get template for single download item
			    $template['download_item'] = $this->cObj->getSubpart( $template['all'], 'DOWNLOAD_ITEM');
			    
			    // Array to store download items/links
			    $downloadItems = '';
			    
			    // Check for scaled image download option
			    if ( $this->imgDownloadOption === 'ALL' || $this->imgDownloadOption === 'SCALED') {
                    $index = 0;
                    
    				foreach ( $this->downloadImageResolutions as $key => $resolution) {
                        $replace = array (
                            'DOWNLOAD_IMG_TEXT' => $this->pi_getLL('download_img_text_'.$index),
                            'RESOLUTION'        => $this->downloadImageResolutions[$index],
                            'NORMAL_DOWNLOAD'   => $normalDownloadImgLinks[$index],
                            'ZIPPED_DOWNLOAD'   => $zippedDownloadImgLinks[$index]
                        );                

                        $downloadItems .= $this->cObj->substituteMarkerArray( $template['download_item'], $replace, '###|###', false);
                        $index++;
    				}
    				
    				unset( $replace);
			    }

                // Check for original image download option
                if ( $this->imgDownloadOption === 'ALL' || $this->imgDownloadOption === 'ORIGINAL') {
                    $replace = array (
                        'DOWNLOAD_IMG_TEXT' => $this->pi_getLL('original_img_download_text'),
                        'RESOLUTION'        => '',
                        'NORMAL_DOWNLOAD'   => $this->getDownloadLink( $this->currentImgID,''),
                        'ZIPPED_DOWNLOAD'   => $this->getDownloadLink( $this->currentImgID,'',DOWNLOAD_ZIPPED)
                    );
                    
                    $downloadItems .= $this->cObj->substituteMarkerArray( $template['download_item'], $replace, '###|###', false);
                }
			    
				if ( $downloadItems !== '') {
				    $template['DOWNLOAD_OPTION'] = $this->cObj->getSubpart( $template['all'], 'DOWNLOAD_OPTION');
				    $downloadOptions = $this->cObj->substituteSubpart($template['DOWNLOAD_OPTION'], 'DOWNLOAD_ITEM', $downloadItems);

                    $replace = array( 'DOWNLOADS_HEADER' => $this->pi_getLL( 'downloads_header'));
                    $downloadOptions = $this->cObj->substituteMarkerArray( $downloadOptions, $replace, '###|###', 0);
				}
			}
		}

		return $downloadOptions;
	}


	/**
	 * Get listing of all the images recursively in gallery
     *
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @return array
	 */
	function getGalleryImages( $galleryID, $parseRecursive = false) {
		$galleryImages = array();
		$subGalleryImages = array();

		foreach ( $this->galleries[$galleryID]['elements'] as $key => $value) {
			if ( is_array( $value)) {
				if ( $parseRecursive) {
					$subGalleryImages = array_merge( $subGalleryImages, $this->getGalleryImages( $value['index'], $parseRecursive));
				}
			} else {
				$galleryImages[] = array(
				'galleryID' => $galleryID,
				'idx' => $key,
				'img' => $value
				);
			}
		}

		$galleryImages = array_merge( $galleryImages, $subGalleryImages);

		return $galleryImages;
	}


	/**
	 * Get image at particular index
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @param integer $imageID - Index of image within gallery
	 * @return string
	 */
	function imageAt( $galleryID, $imageID) {
        
		//$galleryImages = $this->getGalleryImages( $galleryID);
		//return $galleryImages[ $imageID];
		//debug( $this->galleries[$galleryID]['elements'][$imageID]);
		return $this->galleries[$galleryID]['elements'][$imageID];
	}


	/**
	 * Function to check whether a gallery with provided gallery-id exists or not
	 * @access private
	 * @param integer $galleryID - ID of gallery
	 * @return string
	 */
	function isValidGallery( $galleryID) {
		$status = false;

		if ( is_array( $this->galleries) && !empty( $this->galleries)) {
			$maxGalleryID = count( $this->galleries) - 1;

			if ( $galleryID >= 0 && $galleryID <= $maxGalleryID) {
				$status = true;
			}
		}

		return $status;
	}


	/**
	 * Function to set the page header for download page
	 * @access public
	 * @param array $headerParam - Array containing header parameter values
	 */
	function setPageHeader( $headerParam) {
		// required for IE, otherwise Content-disposition is ignored
		if ( ini_get( 'zlib.output_compression')) {
			ini_set( 'zlib.output_compression', 'Off');
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: '.$headerParam['contentType']);
		header('Content-Disposition: attachment; filename='.$headerParam['fileName'].';');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$headerParam['contentLength']);
	}

	/**
	 * Function to get the image information
	 * @access public
	 * @return array
	 */
	function getImageInfo () {
		$imageInfo = '';
        $isEmpty = false;

		$htmlTemplate = file_get_contents( $this->singleViewTemplate);

		if ( empty( $htmlTemplate)) {
			return $imageInfo;
		}

		$template['IMG_INFO'] = $this->cObj->getSubpart( $htmlTemplate, 'IMG_INFO');

		if ( !empty( $template['IMG_INFO'])) {

            if ( !$this->dispImgSizeOption) {
                $replace['<!--###IMG_SIZE_OPTION###-->'] = '';
            } else {
                $isEmpty = true;
            }

            if ( !$this->dispImgColorsOption) {
                $replace['<!--###IMG_COLORS_OPTION###-->'] = '';
            } else {
                $isEmpty = true;
            }

            if ( !$this->dispImgDateOption) {
                $replace['<!--###IMG_DATE_OPTION###-->'] = '';
            } else {
                $isEmpty = true;
            }
            
            $template['IMG_INFO'] = $this->cObj->substituteMarkerArrayCached( $template['IMG_INFO'], array(), $replace, array());

			$imagePath = $this->imageAt( $this->currentGalleryID, $this->currentImgID);
			$imageDetails = $this->getImageDetails( PATH_site.$imagePath);

			if ( is_array($imageDetails) && !empty($imageDetails)) {
				$replace = array(
                    '###LABEL_ORIGINAL_SIZE###' => $this->pi_getLL('original_size'), 
				    '###IMG_SIZE###'            => $imageDetails['resolution'],
                    '###LABEL_COLORS###'        => $this->pi_getLL('colors'),
				    '###IMG_COLORS###'          => $imageDetails['colors'],
                    '###LABEL_IMAGE_DATE###'    => $this->pi_getLL( 'image_date'),
				    '###IMG_DATE###'            => $imageDetails['date']
				);

				$imgExifData = '';

				if ( is_array( $imageDetails['exifData']) && !empty( $imageDetails['exifData'])) {
					$exifDataTemplate = $this->cObj->getSubpart( $htmlTemplate, 'IMG_EXIF_DATA');

					if ( !empty( $exifDataTemplate)) {
						foreach ( $imageDetails['exifData'] as $key => $value) {
							$tmp = $exifDataTemplate;
							$replaceSub = array(
							    '###NAME###'        => $this->pi_getLL( $key),
							    '###DESCRIPTION###' => $value
							);

							$imgExifData .= $this->cObj->substituteMarkerArrayCached( $tmp, array(), $replaceSub, array());
						}
					}
				}

				$replace['###LABEL_IMG_DETAILS###']    = $this->pi_getLL( 'image_details');
				$replace['<!--###IMG_EXIF_DATA###-->'] = $imgExifData;
				$imageInfo .= $this->cObj->substituteMarkerArrayCached( $template['IMG_INFO'], array(), $replace, array());
			} else {
                $isEmpty = true;
            }
		}

		return ( false === $isEmpty)
		       ? ''
		       : $imageInfo;
	}

	/**
	 * Function to get the image description
	 * @access public
	 * @param string $imagePath - Absolute path of image
	 * @return array
	 */
	function getImageDetails ( $imagePath) {
		$imageDetails = array();
		$IMAGE_TYPES = array('1' => 'GIF', '2' => 'JPEG', '3' => 'PNG', '4' => 'SWF', '5' => 'PSD', '6' => 'BMP', '7' => 'TIFF', '8' => 'TIFF', '9' => 'JPC', '10' => 'JP2', '11' => 'JPX', '12' => 'JB2', '13' => 'SWC', '14' => 'IFF', '15' => 'WBMP', '16' => 'XBM');

		if ( file_exists( $imagePath)) {
			$imgInfo = getimagesize( $imagePath);
			$imageDetails['format'] = $IMAGE_TYPES[$imgInfo[2]];
			$imageDetails['resolution'] = $imgInfo[0].'x'.$imgInfo[1];
			$imageDetails['colors'] = $this->graphics->getImgColors( $imagePath);
			//$imageDetails['colourDepth'] = $imgInfo['bits'];;
			$imageDetails['date'] = date ('F d Y H:i:s', filemtime( $imagePath));
            $pathDetails = pathinfo( $imagePath);
			$fileExt = strtolower( $pathDetails['extension']);

			if (( !strcmp( $fileExt, 'jpg') || !strcmp( $fileExt, 'jpeg') )  && function_exists( 'exif_read_data') && $this->dispExifData){
				$imgExifData = exif_read_data( $imagePath);

				if ( $imgExifData) {
					$exifDetails = array();

                    foreach ( $this->reqExifDataItems as $key => $value) {
                        if ( trim( $imgExifData[$value]) !== '') {
                            $exifDetails[$value] = $imgExifData[$value];
                        }
                    }
                                      
                    if ( $exifDetails['FileDateTime'] !== '') {
                        $date_time_format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
                        $date_time_format = ' '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
                        $exifDetails['FileDateTime'] = date($date_time_format, $exifDetails['FileDateTime']);
                    }
                    if ( $exifDetails['FileType'] !== '') {
                        $exifDetails['FileType'] = $IMAGE_TYPES[$exifDetails['FileType']];
                    }
                    if ( is_numeric( $exifDetails['FileSize'])) {
                        $exifDetails['FileSize'] = ceil( ( intval( $exifDetails['FileSize']) / 1024)).' Kb';
                    }
				}

				$imageDetails['exifData'] = $exifDetails;
			}
		}

		return $imageDetails;
	}
    
    /**
     * Function to get image IPTC details
     *
     * @access private
     * @param string $image    Absolute path of image file
     * @param  array $tags     Array of APP13 tags
     * @param string           Image IPTC details
     */     
     function getImgIPTCDetails( $image, $tags) {
        $ImgIPTCDetails = '';
         
        $IPTCData = '';

        $template = array();
        $template['all'] = file_get_contents( $this->iptcTemplatePath);
        
        if ( $template['all'] !== '') {

             // If currently selected image supports IPTC data
             if ( $this->graphics->imgSupportsIPTC( $image)) {

                 // Extract template for IPTC data
                 $template['iptc'] = $this->cObj->getSubpart( $template['all'], 'IMG_IPTC_DATA_TEMPLATE');
                 
                 // Fetch image's IPTC data
                 $imgIPTCData = $this->graphics->getImgIPTCData( $image, $tags);
                 
                 if ( is_array( $imgIPTCData) && !empty( $imgIPTCData)) {

                     // Obtain sub-template for single IPTC data item
                     $template['iptcDataItem'] = $this->cObj->getSubpart( $template['iptc'], 'IPTC_DATA_ITEM');
                     
                     if ( is_array( $tags) && !empty( $tags)) {
                         $IPTCDataItems = '';
                         
                         foreach ( $tags as $key => $value) {
                             if ( trim( $imgIPTCData[$value]) !== '') {
                                 $replace = array(
                                 'tag' => $this->pi_getLL($value),
                                 'value' => $imgIPTCData[$value]
                                 );
                                 
                                 $IPTCDataItems .= $this->cObj->substituteMarkerArray( $template['iptcDataItem'], $replace, '###|###', 1);
                             }
                         }
                         
                         unset( $replace);
                         $ImgIPTCDetails = $this->cObj->substituteSubpart( $template['iptc'], 'IPTC_DATA_ITEM', $IPTCDataItems, 0);
                         
                         $replace = array(
                            'IPTC_HEADER' => $this->pi_getLL( 'iptc_header'), 
                            'IMAGE'       => '',
                            'SUBMIT'      => '',
                            'HIDDEN'      => ''
                         );
                         
                         $ImgIPTCDetails = $this->cObj->substituteMarkerArray( $ImgIPTCDetails, $replace, '###|###', 1);
                         unset( $replace);
                     }
                 }
             }
         }
         
         return $ImgIPTCDetails;    
    }
}

?>