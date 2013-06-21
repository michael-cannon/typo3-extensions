<?php

if ( !defined('PATH_tslib')) {
	define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
	define('PATH_site', dirname(PATH_thisScript).'/');
	define('PATH_t3lib', PATH_site.'t3lib/');
	define('PATH_tslib', PATH_site.'tslib/');
}

require_once( t3lib_extMgm::extPath('bahag_photogallery').'pi1/class.tx_bahag_photogallery_base.php');

class tx_bahag_photogallery_cache_control extends tx_bahag_photogallery_base {

	/**
	 * Plugin configuration details
	 * @var array
	 */
	 var $pluginConf = '';

	/**
	 * Thumbnail dimension with respect to scale
	 * @var string
	 */
	 var $thumbDimType;

	/**
	 * Resized value of thumbnail dimension w.r.t scale
	 * @var integer
	 */
	 var $thumbDimValue;

	/**
	 * Image dimension with respect to scale
	 * @var integer
	 */
	 var $imageDimType;

	/**
	 * Resized value of image dimension w.r.t scale
	 * @var integer
	 */
	 var $imgCacheDirName = 'cache';
	 
	 
	/**
	 * Function using the hook
	 *
	 * @param string $status   The status i.e. update or new record. Right now not used
	 * @param string $table    The table where record is created/updated
	 * @param int    $id       The ID of the record created/updated. Right now not used
	 * @param array  $field    Array The array containing the changed fields and theirs values
	 * @param object $obj      The TCEmain object
	 * @return void
	 */	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$obj) {

		if ( strcmp( $fieldArray['pi_flexform'], '')) {

			// Get flexform xml data and convert it to nested array
			$flexXml = $fieldArray['pi_flexform'];
			$flexArray = t3lib_div::xml2array( $flexXml);
	
			// Convert nested flexform array to flat array
			$this->pluginConf = $this->getFlexData( $flexArray);
			$this->initGalleryConf( $this->pluginConf);
	
			if ( $this->pluginConf['refreshGalleryCache'] === 1) {
				if( strcmp( $this->pluginConf['galleryPath'], '')) {
					$this->rootGalleryPath = $this->pluginConf['galleryPath'];
				}
	
				// Generate array structure of gallery
				$this->genGalleryStruc();
				
				if ( is_array( $this->galleries)) {
					foreach ( $this->galleries as $galleyIndex => $galleryContent) {
						// Update gallery cache
						$this->createCache( $galleyIndex, $this->getThumbsConf(), $this->thumbsDirName);
						$this->createCache( $galleyIndex, $this->getLargeThumbsConf(), $this->largeThumbsDirName);
						$this->createCache( $galleyIndex, $this->getImagesConf(), $this->imgCacheDirName);
					}
				}
				
				// Reset `Clear Photo Gallery Cache` field back to 0
				$flexArray['data']['sDEF']['lDEF']['refreshGalleryCache'] = 0;
				$fieldArray['pi_flexform'] = t3lib_div::array2xml( $flexArray, '', 0,'T3FlexForms');
			}
		}
	
		return;
	}
	
	/**
	 * Recreate thumbails cache directory
	 *
	 * @access private
	 * @param string $galleryID	Index of gallery
	 * @param string $conf		Image processing configuration for cached images
	 * @param string $targetDir	Target directories for cached images
	 */
	 function createCache( $galleryID, $conf, $targetDir) {
	 	$galleryPath = $this->galleries[$galleryID]['path'];

	 	// Return if invalid parameters
		if ( $galleryPath === '' || $targetDir === '' || !is_array( $conf) || empty( $conf)) {
			return;
		}
		
		$galleryPath = PATH_site.$galleryPath;
        $galleryImages = $this->getGalleryImages( $galleryID);
        
        if ( !is_array( $galleryImages) || empty( $galleryImages)) {
            return;
        }
        
        foreach ( $galleryImages as $key => $val) {
            $galleryImages[$key] = basename( $val['img']);
        }
		
		// Return if there are no images in gallery
		if ( count( $galleryImages) < 1) {
			return;
		}

		$targetDir = $galleryPath.'/'.$targetDir;
		
		// Create cache directory if it doesn't exists
		if ( !is_dir( $targetDir)) {
			mkdir( $targetDir, 0755);
		}
		
		$existingCachedImages = array_map( 'basename', $this->getDirImages( $targetDir));
		$thumbSuffix = $conf['width'].$conf['height'].$conf['quality'].$conf['grayscale'];
		
		if ( file_exists( $conf['watermarkImage'])) {
            clearstatcache();
            $thumbSuffix .= filemtime( $conf['watermarkImage']);
		}

		foreach ( $galleryImages as $key => $image) {
			$image = str_replace(' ','',$image);
			list( $imageName, $imageExt) = explode( '.', $image);
			$newCachedImages[$image] = $imageName.$thumbSuffix.'.jpg';
			//$newCachedImages[$image] = $imageName.$thumbSuffix.'.'.$imageExt;
		}

		// Get array of cached images to recreate
		$createImages = array_diff( $newCachedImages, $existingCachedImages);

		// Generate cached image
		foreach ( $createImages as $image => $cachedImage) {
			$conf['newImgPath'] = $targetDir.'/'.$cachedImage;
			tx_bahag_photogallery_graphics::transformImage( $galleryPath.'/'.$image, $conf);
		}
		
		// Get and delete cached images
		$existingCachedImages = array_map( 'basename', $this->getDirImages( $targetDir));
		$deleteImages = array_diff( $existingCachedImages, $newCachedImages);

		foreach ( $deleteImages as $key => $image) {
			unlink( $targetDir.'/'.$image);
		}
	 }
	
	/**
	 * Function to convert nested flexform array to flat array
	 *
	 * @access private
	 * @param array $flexArray	Array representation of xml flexform data
	 * @return array	Array containing flexform data in flat array
	 */
	 function getFlexData( $flexArray) {
	 	$piFlexForm = $flexArray;
		$flexData = array();
	 	
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			foreach ( $data as $lang => $value ) {
				foreach ( $value as $key => $val ) {
					$flexData[$key] = ( is_numeric( $val['vDEF']))
									  ? intval( $val['vDEF'])
									  : $val['vDEF'];
				}
			}
		}

		return $flexData;
	 }

	/**
	 * Function to get the listing of all the images of a directory
	 *
	 * @param string $dir	Absolute path of directory to be parsed for images
	 * @return array	Array of images in directory
	 */
	function getDirImages( $dir) {
		$dirImages = array();
		
		if ( is_dir( $dir)) {
			if ( $handle = opendir( $dir)) {
				while ( false !== ( $dir_item = readdir( $handle))) {
					if ( !in_array( $dir_item, $this->excludeDir)) {
						if ( !is_dir( $dir.'/'.$dir_item)) {
							if( $this->isImageFile( $dir_item)) {
								$dirImages[] = $dir.'/'.$dir_item;
							}
						}
					}
				}
			}
		}
		
		return $dirImages;
	}

	/**
	 * Function to initialize gallery configuration variables
	 *
	 * @access private
	 * @param array $galleryConf	Array containing (flexform) gallery configuration
	 */
	function initGalleryConf( $galleryConf) {
		if ( !empty( $galleryConf)) {
			if ( strcmp( strtoupper( $galleryConf['thumbDimType']), 'HEIGHT')) {
				$this->thumbDimType = 'width';
				$this->thumbWidth  = ( empty( $galleryConf['thumbDimValue']))
									 ? THUMB_WIDTH
									 : intval( $galleryConf['thumbDimValue']);
				$this->thumbHeight = '';
			} else {
				$this->thumbDimType = 'height';
				$this->thumbHeight = ( empty( $galleryConf['thumbDimValue']))
									 ? THUMB_HEIGHT
									 : intval( $galleryConf['thumbDimValue']);
				$this->thumbWidth = '';
			}
			
			if ( strcmp( strtoupper( $galleryConf['floatingThumbDimType']), 'HEIGHT')) {
				$this->largeThumbDimType = 'width';
				$this->largeThumbWidth  = ( empty( $galleryConf['floatingThumbDimValue']))
									 	  ? LARGE_THUMB_WIDTH
									 	  : intval( $galleryConf['floatingThumbDimValue']);
				$this->largeThumbHeight = '';
			} else {
				$this->largeThumbDimType = 'height';
				$this->largeThumbHeight = ( empty( $galleryConf['floatingThumbDimValue']))
									 	  ? LARGE_THUMB_HEIGHT
									 	  : intval( $galleryConf['floatingThumbDimValue']);
				$this->largeThumbWidth = '';
			}
			
			if ( strcmp( strtoupper( $galleryConf['imageDimType']), 'HEIGHT')) {
				$this->imageDimtype = 'width';
				$this->imageWidth  = ( empty( $galleryConf['imageDimValue']))
									 ? IMAGE_WIDTH
									 : intval( $galleryConf['imageDimValue']);
				$this->imageHeight = '';
			} else {
				$this->imageDimtype = 'height';
				$this->imageHeight = ( empty( $galleryConf['imageDimValue']))
									 ? IMAGE_HEIGHT
									 : intval( $galleryConf['imageDimValue']);
				$this->imageWidth = '';
			}
			
			$this->tmbGrayscale  =  ( $galleryConf['isThumbGrayscale'])
									? 1
									: 0;

    		if ( !empty( $galleryConf['imageQuality'])) {
    			$this->imgQuality = $galleryConf['imageQuality'];
    		}

			$this->imgGrayscale  =  ( $galleryConf['isImageGrayscale'])
									? 1
									: 0;
									
			$this->watermarkImage = ( empty( $galleryConf['watermarkImage']))
									? ''
									: $this->uploadsFolder.$galleryConf['watermarkImage'];
		}
	}

	/**
	 * Function to get configuration settings for thumbnails
	 *
	 * @access private
	 * @return array	Image processing options for thumbnails
	 */
	function getThumbsConf() {
		$thumbsConf = array(
		'width' => $this->thumbWidth,
		'height' => $this->thumbHeight,
        'quality' => THUMB_QUALITY,
		'grayscale' => $this->tmbGrayscale,
		'watermarkImage' => '',
		'dissolvePercent' => '',
		'depth' => THUMB_DEPTH,
		'newImgPath' => ''
		);
		
		return $thumbsConf;
	}

	/**
	 * Function to get configuration settings for thumbnails 
	 *
	 * @access private
	 * @return array	Image processing options for large thumbnails
	 */
	function getLargeThumbsConf() {
		$largeThumbsConf = array(
		'width' => $this->largeThumbWidth,
		'height' => $this->largeThumbHeight,
        'quality' => THUMB_QUALITY,
		'grayscale' => $this->tmbGrayscale,
		'watermarkImage' => '',
		'dissolvePercent' => '',
		'depth' => THUMB_DEPTH,
		'newImgPath' => ''
		);
		
		return $largeThumbsConf;
	}

	/**
	 * Function to get configuration settings for images
	 *
	 * @access private
	 * @return array	Image processing options for cached images
	 */
	function getImagesConf() {
		$largeThumbsConf = array(
		'width' => $this->imageWidth,
		'height' => $this->imageHeight,
        'quality' => $this->imgQuality,
		'grayscale' => $this->imgGrayscale,
		'watermarkImage' => $this->watermarkImage,
		'dissolvePercent' => $this->watermarkDissolveVal,
		'newImgPath' => ''
		);
		
		return $largeThumbsConf;
	}

	function debug_print( $var) {
		echo '<PRE>';
		print_r( $var);
		echo '</PRE>';
	}
}

?>
