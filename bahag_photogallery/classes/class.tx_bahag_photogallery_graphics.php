<?php

require_once( PATH_t3lib."class.t3lib_stdgraphic.php");
require_once( t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_iptc.php');


class tx_bahag_photogallery_graphics {

    /**
     * Path to imagemagick
     *
     * @access private
     * @var string
     */
     var $imPath;
     
     
     /**
      * Function to initialize class variables
      * @access public
      */
     function tx_bahag_photogallery_graphics() {
        $this->imPath = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];
     }

	/**
	 * This function performs various transformations
	 * on provided image like scaling, grayscale,
	 * quality setting etc, on the images
     *
	 * @access public
	 * @param string $imagePath     Absolute path of the image to process
	 * @param string $conf          Configuration settings for new image
	 * @return boolean
	 */	
	function transformImage( $imagePath, $conf) {
		$status = false;	// execution status of function 
		
		if(is_array($conf) && !empty($conf)) {
			if($GLOBALS['TYPO3_CONF_VARS']['GFX']['image_processing'] == 1) {
				if($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] == 1) {
					$command = '';
					$options = '';
					
					$imPath = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];
					$command = 'convert';
					
					if ( !empty( $conf['width']) || !empty( $conf['height'])) {
						if ( false !== ($actualImgDim = getimagesize( $imagePath))) {
							if ( !empty( $conf['width'])) {
								$scalingFactor = $conf['width'] / $actualImgDim[0];
								$conf['height'] = ceil( $actualImgDim[1] * $scalingFactor);
							} else {
								$scalingFactor = $conf['height'] / $actualImgDim[1];
								$conf['width'] = ceil( $actualImgDim[0] * $scalingFactor);
							}
						}
						
						$options .= ' -geometry '.$conf['width'].'x'.$conf['height'];
					}
					
					if ( !empty( $conf['grayscale'])) {
						$options .= ' -type grayscale ';
					}
					
					if ( !empty( $conf['quality'])) {
						$options .= ' -quality '.$conf['quality'];
					}
					
					if ( !empty( $conf['depth'])) {
						$options .= ' -depth '.$conf['depth'];
					}
					
					$options .= ' +profile \'*\'';
					
					$sourceImg = $imagePath;
					$targetImg = $conf['newImgPath'];
					$sourceImg = '\''.$imagePath.'\'';
					$targetImg = '\''.$conf['newImgPath'].'\'';
					$command = $imPath.$command.' '.$options.' '.$imagePath;
					
					
					if ( !empty( $conf['watermarkImage'])) {
						$watermarkImgDim = getimagesize( $conf['watermarkImage']);
						$r1 = $conf['width'] / $conf['height'];
						$r2 = $watermarkImgDim[0] / $watermarkImgDim[1];
						
						if ( $r1 > $r2) {
							$scaledWMImgDim['width'] = ceil( $r2 * $conf['height']);
							$scaledWMImgDim['height'] = $conf['height'];
						} elseif ( $r1 > $r2) {
							$scaledWMImgDim['height'] = ceil( $r2 * $conf['width']);
							$scaledWMImgDim['width'] = $conf['width'];
						} else {
							$scaledWMImgDim['width'] = $conf['width'];
							$scaledWMImgDim['height'] = $conf['height'];
						}
						$command .= ' miff:- | '.$imPath.'composite -dissolve '.$conf['dissolvePercent'].' '.$conf['watermarkImage'].' -gravity center -geometry '.$scaledWMImgDim['width'].'x'.$scaledWMImgDim['height'].' -';
					}
					
					// $command .= ' miff:- | '.$imPath.'convert -frame 10x10+5+5 -mattecolor "#CCCCCC" -';
					$command .= ' '.$conf['newImgPath'];
					// echo $command.'<br><br>';
					
					// $objStdGraphics = new t3lib_stdGraphic();
					
					/*if( false !== $objStdGraphics->imageMagickExec( $sourceImg,$targetImg,$options)){
						$status = true;
					}*/
					
					if( false !== popen($command,'r')){
						$status = true;
					}
				} else {
					die('Imagemagick is not enabled.');
				}
			} else {
				die('Image processing is not enabled in Typo3.');
			}
		}
		
		return $status;
	}
	
	/**
	 * Function to get image as a stream
	 *
	 * @access public
	 * @param string $image    Absolute path of the image
	 * @return string          Image as stream
	 */
    function getInlineImageThumb( $image) {
        $inlineImageThumb = '';

        $imPath = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];
        $command = 'convert';

        // Get original image dimensions
		if ( false !== ($imgDim = getimagesize( $image))) {
            $cmdString = $imPath.$command.' -quality 40 -size '.$imgDim[0].'x'.$imgDim[1].' -thumbnail 240x160 '.$image.' -';
            
            // This function is disabled in safe mode
            $inlineImageThumb = shell_exec( $cmdString);
		}


        return $inlineImageThumb;
    }
	

	/**
	* Transforms images in the gallery.
	* @return array
	* @param string $imagePath - Absolute path of the image
	* @access public
	*/	
	function getImgDimensions( $imagePath) {
		$objStdGraphics = new t3lib_stdGraphic();
		
		$imgDimensions = $objStdGraphics->getImageDimensions( $imagePath);
		$imgDimensions = array(
			'width' => $imgDimensions[0],
			'height' => $imgDimensions[1]
		);
		
		return $imgDimensions;
	}

    function myWriteLog( $text) {
    	if (!empty($text)) {	
    	   static $counter = 0;	$counter++;	
    	   $text = date('Y-m-d').": Write attempt $counter: $text";	
    	   $file = t3lib_extMgm::extPath('bahag_photogallery').'trigger.log';	
    	   
    	   if ($fp = fopen($file, 'w')) {
    	       fwrite($fp, "$text\n");	
    	       fclose($fp);	
    	   }	
        }	
    }
    
    /**
     * Function to get information about provided
     * image like image type, width, height etc.
     *
     * @access private
     * @param string $image     Absolute path of image file
     * @return array            Image description     
     */
     function getImageInfo( $image) {
        $imgInfo = array();
        
        if ( file_exists( $image)) {
            if ( $data = getimagesize( $image)) {
                $imgInfo = $data;
            }
        }
        
        return $imgInfo;
     }
    
    /**
     * Function to get information about provided
     * image like image type, width, height etc.
     *
     * @access private
     * @param string $image     Absolute path of image file
     * @return boolean          Flag indicating whether provided image supports IPTC data or not     
     */
     function imgSupportsIPTC( $image) {
        $status = false;
        $iptcSupportingImg = array(
        'JPG' => 2, 
        'TIFF_IBO' => 7,    // Intel Byte Order
        'TIFF_MBO' => 8     // Motorola Byte Order
        );
        
        if ( file_exists( $image)) {
            if ( $imgInfo = getimagesize( $image)) {
                if ( in_array( $imgInfo[2], $iptcSupportingImg)) {
                    $status = true;
                }
            }
        }
        
        return $status;
     }
    
    /**
     * Function to get the image IPTC header, provided the APP13 tag values
     *
     * @access private
     * @param array $IPTCTagValues      Tag/value pair of image IPTC data
     * @return string                   Image IPTC header
     */ 
    function getImgIPTCHeader( $IPTCTagValues) {
        $imgIPTCHeader = '';
        
        if ( is_array( $IPTCTagValues) && !empty( $IPTCTagValues)) {

            //  Convert special characters in values to HTML entities
            foreach ( $IPTCTagValues as $key => $value) {
                $IPTCTagValues[$key] = htmlspecialchars( $value, ENT_QUOTES);
            }
            
            // Create IPTC header from recieved info
            $imgIPTCHeader  = '8BIM#1028="IPTC"'."\n";
            $imgIPTCHeader .= '2#5#Image Name="'.$IPTCTagValues['object_name'].'"'."\n";
            $imgIPTCHeader .= '2#7#Edit Status="'.$IPTCTagValues['edit_status'].'"'."\n";
            $imgIPTCHeader .= '2#10#Priority="'.$IPTCTagValues['priority'].'"'."\n";
            $imgIPTCHeader .= '2#15#Category="'.$IPTCTagValues['category'].'"'."\n";
            $imgIPTCHeader .= '2#20#Supplemental Category="'.$IPTCTagValues['supplementary_category'].'"'."\n";
            $imgIPTCHeader .= '2#22#Fixture Identifier="'.$IPTCTagValues['fixture_identifier'].'"'."\n";
            $imgIPTCHeader .= '2#25#Keyword="'.$IPTCTagValues['keywords'].'"'."\n";
            $imgIPTCHeader .= '2#30#Release Date="'.$IPTCTagValues['release_date'].'"'."\n";
            $imgIPTCHeader .= '2#35#Release Time="'.$IPTCTagValues['release_time'].'"'."\n";
            $imgIPTCHeader .= '2#40#Special Instructions="'.$IPTCTagValues['special_instructions'].'"'."\n";
            $imgIPTCHeader .= '2#45#Reference Service="'.$IPTCTagValues['reference_service'].'"'."\n";
            $imgIPTCHeader .= '2#47#Reference Date="'.$IPTCTagValues['reference_date'].'"'."\n";
            $imgIPTCHeader .= '2#50#Reference Number="'.$IPTCTagValues['reference_number'].'"'."\n";
            $imgIPTCHeader .= '2#55#Created Date="'.$IPTCTagValues['created_date'].'"'."\n";
            $imgIPTCHeader .= '2#64="'.$IPTCTagValues['originating_program'].'"'."\n";
            $imgIPTCHeader .= '2#70#Program Version="'.$IPTCTagValues['program_version'].'"'."\n";
            $imgIPTCHeader .= '2#75#Object Cycle="'.$IPTCTagValues['object_cycle'].'"'."\n";
            $imgIPTCHeader .= '2#80#Byline="'.$IPTCTagValues['byline'].'"\n';
            $imgIPTCHeader .= '2#85#Byline Title="'.$IPTCTagValues['byline_title'].'"'."\n";
            $imgIPTCHeader .= '2#90#City="'.$IPTCTagValues['city'].'"'."\n";
            $imgIPTCHeader .= '2#95#Province State="'.$IPTCTagValues['province_state'].'"'."\n";
            $imgIPTCHeader .= '2#100#Country Code="'.$IPTCTagValues['country_code'].'"'."\n";
            $imgIPTCHeader .= '2#101#Country="'.$IPTCTagValues['country'].'"'."\n";
            $imgIPTCHeader .= '2#103#Original Transmission Reference="'.$IPTCTagValues['original_transmission_reference'].'"'."\n";
            $imgIPTCHeader .= '2#105#Headline="'.$IPTCTagValues['headline'].'"'."\n";
            $imgIPTCHeader .= '2#110#Credit="'.$IPTCTagValues['credit'].'"'."\n";
            $imgIPTCHeader .= '2#115#Source="'.$IPTCTagValues['source'].'"'."\n";
            $imgIPTCHeader .= '2#116#Copyright String="'.$IPTCTagValues['copyright_string'].'"'."\n";
            $imgIPTCHeader .= '2#120#Caption="'.$IPTCTagValues['caption'].'"'."\n";
            $imgIPTCHeader .= '2#121#Image Orientation="'.$IPTCTagValues['local_caption'].'"'."\n";
        }
        
        return $imgIPTCHeader;
    }
    
     /**
      * Function to write IPTC data to provided image.
      *
      * @access public
      * @param string $image            Absolute path of image
      * @param string $IPTCDataItems    Array containing IPTC data for provided image
      * @param boolean                  Status of write operation
      */
     function writeImgIPTCData( $image, $IPTCDataItems) {
         $status = false;
         
         $imgInfo = $this->getImageInfo( $image);

         // Flag to check whether provided file is a valid image file or not
         $isValidImgFile = ( is_array( $imgInfo) && !empty( $imgInfo))
                           ? true
                           : false;

         // Flag to check that provided $IPTCDataItems array holds data
         $iptcDataNotEmpty = ( is_array( $IPTCDataItems) && !empty( $IPTCDataItems))
                              ? true
                              : false;
         
         if ( $isValidImgFile && $iptcDataNotEmpty) {
         
             // Create and get image IPTC header from received APP13 tag values             
             $IPTCDataHeader = $this->getImgIPTCHeader( $IPTCDataItems);
             
             // Create a temporary file to hold image IPTC header
             $tmpFileName = PATH_site.'typo3temp/bahag_photogallery/'.time();
             
             if ( $fp = @fopen( $tmpFileName, 'w')) {
                 if ( fwrite( $fp, $IPTCDataHeader)) {
                     fclose( $fp);
                     chmod( $tmpFileName, 0777);
                     
                     // Invoke imagemagick commandline utility to write IPTC data to image
                     $imPath = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];
                     $command = 'mogrify';
                     $options = ' -profile 8BIMTEXT:';
                     $cmd = $imPath.$command.$options.'\''.$tmpFileName.'\' '.$image;
                     
                     // Uncomment to debug
                     // echo '<br>'.$cmd;

                     if ( popen( $cmd, 'r')) {
                         $status = true;
                     }
                 } else {
                     fclose( $fp);
                 }
                 
                 // remove above created temporary file
                 @unlink( $tmpFileName);
             }
         }
         
         return $status;
     }
	
	/**
	 * Function to get image IPTC data
     * 
     * @access public
     * @param  string $image    Absolute path of image
     * @param  array  $tags     Array of APP13 tags
     * @return string           HTML output for image IPTC data	 
	 */
	function getImgIPTCData( $image, $tags) {
	    $imageIPTCData = array();
	    
	    // Check if any IPTC tags have been specified or not. If not, then return.
	    if ( is_array( $tags) && !empty( $tags)) {

            // Check for valid image file
            $imgInfo = $this->getImageInfo( $image);
            $validImageFile = ( is_array( $imgInfo) && !empty( $imgInfo))
                              ? true
                              : false;
	        
	        // Proceed if provided file is an existing image file
    	    if ( $validImageFile) {
    	        $embImgInfo = array();     // array to store embedded image information
        	    $IPTCData = new tx_bahag_photogallery_iptc( $image);
        	    
                foreach ( $tags as $key => $tag) {
                    $data = trim( $IPTCData->getTag( $tag));
                    
                    if ( $data !== '') {
                        $imageIPTCData[$tag] = $data;
                    }
	            }
    	    }	    
    	}
	    
	    return $imageIPTCData;
	}
	
	/**
	 * Function to get image IPTC data
     * 
     * @access public
     * @param  string $image    Absolute path of image
     * @return string           HTML output for image IPTC data	 
	 */
	function getImgColors( $image) {
	    $imgColors = 0;
	    
        $cmd = $this->imPath.'identify -format %k '.$image;
        
        $output = array();
        $execStatus = false;
        exec( $cmd, $output, $execStatus);
        
        // shell returns 0 if program exits successfully
        if ( $execStatus == 0) {
            $imgColors = intval( $output[0]);
        }
	    
	    return $imgColors;
	}

}

?>
