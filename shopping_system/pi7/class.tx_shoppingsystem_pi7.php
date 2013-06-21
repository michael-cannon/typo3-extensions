<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Titarenko Dmitri <td@krendls.eu>
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
 * Plugin 'Generate Text Image' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi7 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi7';		// Same as class name
	var $scriptRelPath = 'pi7/class.tx_shoppingsystem_pi7.php';	// Path to this script relative to the extension dir.
	var $extKey = 'shopping_system';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$text = t3lib_div::_GP('text');
		$type = t3lib_div::_GP('typeT');
		if($text == '') $text = 'Undefined';
		if($type == null) die();
		$text = strtoupper(rawurldecode($text));

		$pathToImage = $this->getPathToImage($text, $type);
		header("Content-type: image/png");

		die();
//		return $this->pi_wrapInBaseClass($content);
	}
	function getPathToImage($text, $type)
	{
		$dbText = $GLOBALS['TYPO3_DB']->exec_SELECTquery('`img_path`', '`tx_shoppingsystem_txtimages`', '`name` = "'.$text.'" AND `type` = '.$type);

		if($GLOBALS['TYPO3_DB']->sql_num_rows($dbText) < 1)
		{
			$this->createImage($text, $type);
		}else{
			$imageInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbText);
			return $imageInfo['img_src'];
		}
	}
	function createImage($text, $type)
	{
		// geting info about text color, size etc.
		if(!isset($this->conf['types.']["$type."]))return;
		$typeInfo = $this->conf['types.']["$type."];

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
		$bboxInfo = $this->convertBoundingBox(imagettfbbox($fontSize, 0, $fontPath, $text));
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
	function convertBoundingBox ($bbox)
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi7/class.tx_shoppingsystem_pi7.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi7/class.tx_shoppingsystem_pi7.php']);
}

?>