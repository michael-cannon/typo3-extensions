<?php
class tx_sponsorcontentscheduler_utils{

     
    /**
     * Get Form Controls
     *
     * @param string $controlType Type of the Form Control(input, select)
     * @param array $conf Array of different values of Control( array('type' => 'text','value' => 'Some Value','size' => '17')
     * @return string Returns the Form Control
     */
    function getControl($controlType,$conf,$addtionalParams=''){
    	$returnVal      = '';
    	$control_fields = '';
    	$option_fields  = array();
    	$controlType    = strtolower($controlType);
		$optionList 	= '';
		$optSelectedArr =array();
		 
    	foreach ($conf as $fldKey=>$fldVal){
    	    $fldKey = strtolower($fldKey);
    	    
    		switch($fldKey){
    			case 'checked':
    			case 'multiple':
    				$control_fields.=" $fldVal ";
    				break;
    			case 'selected':
    				$optSelectedArr = $fldVal;			
    				break;
    			case 'option':
    				foreach($fldVal as $optionKey=>$optionVal){
    					$option_fields[$optionKey]=$optionVal;
    				}
    				break;
    			case 'additionalparam':
    		    	$addtionalParams=$fldVal;
    		    	break;
    		    case 'value':
    		        if ('textarea' == $controlType) {
    		            break;
    		        }
    		        
    		    default:
    				$control_fields.=" $fldKey='$fldVal' ";
    		}
    	}
    	switch($controlType){
    		case 'select':
    			foreach ($option_fields as $optKey=>$optVal){
    				$selected=in_array($optKey,$optSelectedArr)?'selected':'';
    				$optionList.="<option value='$optKey' $selected>$optVal</option>";
				}
				
    			$returnVal="<SELECT $control_fields $addtionalParams>$optionList</SELECT>";
    			break;
    		case 'input':
    			$returnVal="<INPUT $control_fields $addtionalParams>";
    			break;
    		case 'textarea':
    			$returnVal="<TEXTAREA $control_fields>".(isset($conf['value']) ? $conf['value'] : '')."</TEXTAREA>";
    			break;
    	}

    	return $returnVal;
    }
    
    /**
     * Function used for debugging the output
     *
     * @param mixed $val
     * @return void
     */
    function _debug($val){
    	echo "<PRE>";
    	var_export($val);
    	echo "</PRE>";
    }

	/**
	 * To get the key value from extension
	 *
	 * @param string $key Name of the Extension Key value to be retrived
	 * @param string $keyVal Name of the Extension Key
	 * @return mixed
	 */
	function getKeyVal($key,$keyVal=''){
		$data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$keyVal]);
		return $data[$key];
	}

	/**
	 * Get Template Content
	 *
	 * @param string $templateFileName Template File Name
	 * @return string 
	 */
	function getTemplate($templateFileName){
		$template_path = $templateFileName;
		if( file_exists( $template_path)) {
			if( $fp = fopen( $template_path, 'r')) {
				$content = trim(fread( $fp, filesize( $template_path)));
				fclose($fp);
			}
		}
		return $content;
	}

	/**
	 * Replace the Markers in the template
	 *
	 * @param string $template Template File Name
	 * @param array $markerArray Array of markers to be replaced
	 * @return string
	 */
	function replaceMarker($template,$markerArray){
		foreach ( $markerArray as $key => $value) {
              $template = str_replace( '###'.$key.'###', $value, $template);
          }
		  return $template;
	}
	
	/**
	 * Date Manipulation function to find out difference and future dates
	 *
	 * @param string $sourceDate Source date
	 * @param string $format Date format
	 * @param string $action Action to be performed on date [Supported format 'D' for difference and 'A' for addition] 
	 * @param string $interval interval period
	 * @param string $seprator date seperator
	 * @return string Timestamp format of the result
	 */
	function dateTimeFunction($sourceDate,$format='d-m-Y',$action='D',$interval,$seprator='-'){
		$arr_dateTime=explode($seprator,$sourceDate);
		$newDate='';
		for($i=(count($arr_dateTime)-1);$i>=0;$i--){
			$newDate.=$arr_dateTime[$i];
			$newDate.=$seprator;
		}
		$newDate=substr($newDate,0,-1);
		switch($action){
			case 'D':
				$timeStamp =  strtotime($newDate) - (intval($interval)*60*60*24);
				break;
			case 'A':
				$timeStamp =  strtotime($newDate) + (intval($interval)*60*60*24);
				break;
		}
		return $timeStamp;
	}
	
	
	/**
	 * Function for the downloading of a file [to invoke download box]
	 *
	 * @param string $filename file name with full path
	 */
	function _fileDownload($filename,$data) {
		

		$image['name'] = basename($filename);
		$headerParam = array('contentType' => $this->getContentType('xls'),
		'contentLength' => ($data!='')?filesize($filename):strlen($data),
		'fileName' => $filename
		);

		$this->setPageHeader($headerParam);
		if (!file_exists($filename)) {
			echo $filename;
		}else{
			readfile($filename);
		}
		exit;
	}
	
	
	/**
    * Get content type for specified media
    * 
    * @access private 
    * @param string $fileExt - File name extension
    * @return string 
    */
	function getContentType($fileExt) {
		$contentType = '';

		switch ($fileExt) {
			case "zip":
			$ctype = "application/zip";
			break;
			case "eps":
			$ctype = "image/eps";
			break;
			case "gif":
			$ctype = "image/gif";
			break;
			case "png":
			$ctype = "image/png";
			break;
			case "jpeg":
			case "jpg": $ctype = "image/jpg";
			break;
			case "pdf": $ctype = "application/pdf";
			break;
			default: $ctype = "application/force-download";
		}

		return $ctype;
	}
	
	/**
	 * Set the header parmeter for the page output
	 *
	 * @param array $headerParam Header parameters
	 */
	function setPageHeader($headerParam) {
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $headerParam['contentType']);
		header('Content-Disposition: inline; filename=' . $headerParam['fileName'] . ';');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $headerParam['contentLength']);
	}
	
	
	
	/**
	 * Function  to create option list based on the list as array
	 *
	 * @param array $__arrFld Array of fields with key as value of option and value as the option text 
	 * @param string $__selected Default selected value optional
	 * @return string
	 */
	function createOption($__arrFld,$__selected=''){
		$option='';
		foreach($__arrFld as $key=>$Val){
			$selected = ($key==$__selected)?'selected':'';
			$option .="<option value='$key' $selected>$Val</option>";
		}
		return $option;
	}
	
	
	/**
	 * Transformation or resizing the image
	 *
	 * @param string $imagePath Image file Name with complete path
	 * @param array $conf Configuration array 
	 * Example array(
								'width' => 75,
								'height' => 75,
						        'newImgPath' => t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/typo3temp/$imageFile",
							);
	 * @return boolean 
	 */
	function transformImage( $imagePath, $conf) {

		$status = false;	// execution status of function

		if(is_array($conf) && !empty($conf)) {
			if($GLOBALS['TYPO3_CONF_VARS']['GFX']['image_processing'] == 1) {
				if($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] == 1) {
					$command = '';
					$options = '';

					$imPath = tx_bookingengine_utils::get_im_path();
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

					/*
					Changes by Suman:
					1. Removed the str_replace for spaces
					2. Used $sourceImg and $targetImg where not used earlier
					3. Corrected slashes to be system-independent
					*/
					$sourceImg = $imagePath;
					$targetImg = $conf['newImgPath'];
					$sourceImg = '"'.$imagePath.'"';
					$sourceImg = str_replace('/', DIRECTORY_SEPARATOR, $sourceImg);
					$targetImg = '"'.$conf['newImgPath'].'"';
					$targetImg = str_replace('/', DIRECTORY_SEPARATOR, $targetImg);
					$command = $imPath.$command.' '.$options.' '.$sourceImg;


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
						$command .= ' miff:- | '.$imPath.'composite -dissolve '.$conf['dissolvePercent'].' "'.$conf['watermarkImage'].'" -gravity center -geometry '.$scaledWMImgDim['width'].'x'.$scaledWMImgDim['height'].' -';
					}

					// $command .= ' miff:- | '.$imPath.'convert -frame 10x10+5+5 -mattecolor "#CCCCCC" -';
					$command .= ' '.$targetImg;

//					 echo $command.'<br><br>';

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
	 * function to get Image magic path 
	 *
	 * @return string Path of the Image magic installation
	 */
	function get_im_path() {
		return $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];

	}
	
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/booking_engine/classes/class.tx_bookingengine_utils.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/booking_engine/classes/class.tx_bookingengine_utils.php']);
}
?>