<?php
	require_once('./genImage.conf.php');

	$text = stripslashes(strtoupper(trim(rawurldecode($_GET['text']))));

	$type = (int)rawurldecode($_GET['typeT']);

	header('Content-type: image/png');
	createImage($text, $types[$type]);

	function createImage($text, $typeInfo)
	{
		// geting color of text
		if(!isset($typeInfo['color']))return;
		$color = array(
			'red' => (int)hexdec(substr($typeInfo['color'], 1, 2)),
			'blue' => (int)hexdec(substr($typeInfo['color'], 3, 2)),
			'green' => (int)hexdec(substr($typeInfo['color'], 5, 2))
			);
		// geting background color of text
		if(!isset($typeInfo['bgcolor']))return;
		$bgColor = array(
			'red' => (int)hexdec(substr($typeInfo['bgcolor'], 1, 2)),
			'blue' => (int)hexdec(substr($typeInfo['bgcolor'], 3, 2)),
			'green' => (int)hexdec(substr($typeInfo['bgcolor'], 5, 2))
			);
		// geting path to file with font
		if(!isset($typeInfo['fontPath']))return;
		if(!file_exists($typeInfo['fontPath'])) return;
		$fontPath = $typeInfo['fontPath'];

		// geting font size
		if(!isset($typeInfo['size']))return;
		$fontSize = (int)$typeInfo['size'];

		// check 'bold' option
		$bold = (isset($typeInfo['bold']) && $typeInfo['bold'] == 1);

		// get dimensions of future image
		$bboxInfo = convertBoundingBox(imagettfbbox($fontSize, 0, $fontPath, $text));
		$width = $bboxInfo['width'] + 2;
		$height = $bboxInfo['height'] + 5;

		// create image resource
		$im = imagecreatetruecolor($width, $height);

		// Create colors
		$textColor = imagecolorallocate($im, $color['red'], $color['green'], $color['blue']);
		$bgColor = imagecolorallocate($im, $bgColor['red'], $bgColor['green'], $bgColor['blue']);
		// filling background
		imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);

		// Add the text
		imagettftext($im, $fontSize, 0, $bboxInfo['xOffset']+2, $bboxInfo['yOffset']+2, $textColor, $fontPath, $text);
		 
		// Using imagepng() results in clearer text compared with imagejpeg()
		imagepng($im);
		imagedestroy($im);
	}
	/**
	 * Enter description here...
	 *
	 * @author Nate Sweet
	 *
	 * @param array $bbox
	 * @return unknown
	 */
	function convertBoundingBox($bbox)
	{
	    if ($bbox[0] >= -1)
	        $xOffset = -abs($bbox[0] + 1);
	    else
	        $xOffset = abs($bbox[0] + 2);
	    $width = abs($bbox[2] - $bbox[0]);
	    if ($bbox[0] < -1) $width = abs($bbox[2]) + abs($bbox[0]) - 1;
	    $yOffset = abs($bbox[5] + 1);
	    if ($bbox[5] >= -1) $yOffset = -$yOffset; // Fixed characters below the baseline.
	    $height = abs($bbox[7]) - abs($bbox[1]);
	    if ($bbox[3] > 0) $height = abs($bbox[7] - $bbox[1]) - 1;
	    return array(
	        'width' => $width,
	        'height' => $height,
	        'xOffset' => $xOffset, // Using xCoord + xOffset with imagettftext puts the left most pixel of the text at xCoord.
	        'yOffset' => $yOffset, // Using yCoord + yOffset with imagettftext puts the top most pixel of the text at yCoord.
	        'belowBasepoint' => max(0, $bbox[1])
	    );
	}

?>