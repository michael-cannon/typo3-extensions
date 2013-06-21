<?php

  function fetchimage($file_source, $file_target) 
  {
    if (($rh = fopen($file_source, 'rb')) === FALSE) { return false; } // fopen() handles
    if (($wh = fopen($file_target, 'wb')) === FALSE) { return false; } // error messages.
    while (!feof($rh))
    {
      if (fwrite($wh, fread($rh, 1024)) === FALSE) { fclose($rh); fclose($wh); return false; }
    }
    fclose($rh);
    fclose($wh);
    return true;
  }
  function writeresized($img, $target, $w, $h) {
    $im = @ImageCreateFromJPEG ($img) or // Read JPEG Image
    $im = @ImageCreateFromGIF ($img) or // or GIF Image
    $im = @ImageCreateFromPNG ($img) or // or PNG Image
    $im = false; // If image is not JPEG, PNG, or GIF
    
    if(!$im) {
      return false;
    }
    $dst = resizeimage($im, $w, $h);
    @imagejpeg($dst, $target, 80);
    return true;
  }

  function mkey($len = 12)
  {
    // Register the lower case alphabet array
    $alpha = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
    'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
    $key = array();
    
    for($i = 0; $i <= $len-1; $i++)
    {
      $r = rand(0,count($alpha)-1);
      $key[$i] = $alpha[$r];
    }        
    return strval(implode("",$key));
  }


  /**
	 * Resize and image while maintaining its aspect ratio.
	 *
	 * @param resource $src The image to resize.
	 * @param int $w The target width.
	 * @param int $h The target height.
	 * @return resource The resized image or the original image if it did not need to be scaled.
	 */
  function resizeimage($src, $w, $h) {

    // $image = ImageCreateFromJPEG('images.jpg');
    // $image = resizeimage($image, 400, 400);

    // Get the current size
    $width = ImageSx($src);
    $height = ImageSy($src);

    // If one dimension is right then nothing to do
    if($width <= $w && $height <= $h)
    return($src);

    // Calculate new size
    if(($w - $width) > ($h - $height)) { // use height
      $s = $h / $height;
      $nw = round($width * $s);
      $nh = round($height * $s);
    }
    else { // Use width
      $s = $w / $width;
      $nw = round($width * $s);
      $nh = round($height * $s);
    }

    // Resize to correct size
    $im = ImageCreateTrueColor($nw, $nh);
    ImageCopyResampled($im, $src, 0, 0, 0, 0, $nw, $nh, $width, $height);

    // Return the new image
    return($im);
  }


class tx_shopping_tcemainprocdm {

  function tx_shopping_tcemainprocdm() {

  }

  function processDatamap_postProcessFieldArray ($status, $table, $id, &$fieldArray, &$instance) {

    /** write log **/

    //$fieldArray['tx_shoppingsystem_product_fetch_url'] = "http://www.google.com";
    /*$dataArr = array ();
    $dataArr['pages'][$row['pid']]['hidden'] = 1;
    $tce = t3lib_div::makeInstance('t3lib_TCEmain');

    $tce->start($dataArr, array());
    $tce->process_datamap();*/


    // needed fieds are updated

    if ($status != 'delete' && $table == 'tt_news') {
	    ini_set('display_errors',true);
	    error_reporting(E_ALL ^ E_NOTICE);

/*      $dir = dirname(__FILE__);
      $f = fopen($dir."/log.txt","a+");*/

      $imageIndex = 'tx_shoppingsystem_product_image';
      $imageSmallIndex = 'tx_shoppingsystem_product_image_small';
      $fetchUrlIndex = 'tx_shoppingsystem_product_fetch_url';


      $smallImageEmpty = isset($fieldArray[$imageSmallIndex]) && empty($fieldArray[$imageSmallIndex]);
      $bigImageEmpty = isset($fieldArray[$imageIndex]) && empty($fieldArray[$imageIndex]);


      $row = t3lib_BEfunc::getRecord($table, $id);

//      $tmp = print_r($fieldArray, true);
//      fwrite($f, "status = {$status}, table = {$table}, id = {$id}, fields = {$tmp}");


if($status != 'new') {
      if (!is_array ($row)) {
	      return;
      }

      // No fetch URI set

      if(empty($row[$fetchUrlIndex])) {
        //die('no fetch uri');
        return;
      }
}

if(isset($fieldArray[$fetchUrlIndex]) && empty($fieldArray[$fetchUrlIndex])) {
	return;
}

      $fetchUrl = isset($fieldArray[$fetchUrlIndex]) ? $fieldArray[$fetchUrlIndex] : (isset($row[$fetchUrlIndex]) ? $row[$fetchUrlIndex] : '');

	
if($status != 'new') {	
      if(!isset($fieldArray[$imageSmallIndex])) {
        if(empty($row[$imageSmallIndex])) {
          $smallImageEmpty = true;
        }
      }
      if(!isset($fieldArray[$imageIndex])) {
        if(empty($row[$imageIndex])) {
          $bigImageEmpty = true;
        }
      }
}

      // Both are filled correctly

      if(!$smallImageEmpty && !$bigImageEmpty) {
        //die("Both images are filled correctly");
        return;
      }

      //$databaseDataEmpty = empty($row[$imageIndex]) || empty($row[$imageSmallIndex]);


      $dir_u = dirname(__FILE__) . '/../../../uploads/tx_shoppingsystem';
      $filename = $dir_u.'/'.mkey(12);

      //die('Mustdie 2');


      if(!fetchimage($fetchUrl, $filename)) {
        //die("Cannot fetch image");
        return;
      }

                                    	
      if($smallImageEmpty) {
	// MLC 20070627 make product listing and search bigger
		  // MLC 20070627 was 115, set to 130,130
          // MPF 20070628 From 130x130 to 165x165
        writeresized($filename, $filename.'_small.jpg', 165, 165);
        $fieldArray[$imageSmallIndex] = basename($filename.'_small.jpg');
      }
      if($bigImageEmpty) {
		  // MLC 20070627 was 145
        writeresized($filename, $filename.'_big.jpg', 145, 145);
        $fieldArray[$imageIndex] = basename($filename.'_big.jpg');
      }
      @unlink($filename);
      
//      die('Finished:'.$filename);      

    }
  }

}
?>
