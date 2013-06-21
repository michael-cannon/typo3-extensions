<?php
/**
 * Class 'imageUtil'
 *
 * @package TYPO3
 * @author	Suman Debnath <suman@srijan.in>
 */
class imageUtil {
	var $allowedFormats = array(
	'jpg',
	'jpeg',
	'png',
	'gif',
	);

	/**
	 * Writes multiple strings to an image
	 *
	 * @param string Text to be embedded
	 * @param string Source image path
	 * @param int Font Size
	 * @param string Truetype font file path
	 * @return mixed Returns false on failure, generated image path (site relative) on success
	 */
	function embedTextInImageMultiple($textArr, $sourceImagePath, $ttfFontFilePath = '', $finalImageDir = '', $configArr = array()) {
		$output = false;
		if ((!file_exists($sourceImagePath)) || ((1 > count($textArr)) || (!is_array($textArr)))) {
			return $output;
		}

		$fontSize = intval($fontSize);
		$fontSize = (0 < $fontSize) ? $fontSize : 10;

		$temp = explode('.', basename($sourceImagePath));
		if (!in_array(strtolower($temp[count($temp) - 1]), $this->allowedFormats)) {
			return $output;
		}

		$temp = explode('.', basename($ttfFontFilePath));
		if (('ttf' != strtolower($temp[count($temp) - 1])) || (!file_exists($ttfFontFilePath))) {
			return $output;
		}

		$rgb = is_array($configArr['rgb']) ? $configArr['rgb'] : array(0, 0, 0);
		$newWidth = (0 < $configArr['imageWidth']) ? $configArr['imageWidth'] : 720;
		$newHeight = (0 < $configArr['imageHeight']) ? $configArr['imageHeight'] : 540;

		$md5 = '';
		foreach ($textArr as $tempText) {
			$md5 .= implode($tempText);
		}
		$md5 = md5($md5 . implode($rgb) . $newWidth . $newHeight);

		if (empty($finalImageDir) || !is_dir($finalImageDir)) {
			$newImagePath = dirname($sourceImagePath) . DIRECTORY_SEPARATOR . $md5 . '.png';
		} else {
			$newImagePath = $finalImageDir . DIRECTORY_SEPARATOR . $md5 . '.png';
		}

		if (file_exists($newImagePath)) {
			$output = $newImagePath;
		} else {
			$sourceImagePath = $this->getImageCached($sourceImagePath, $newWidth, $newHeight, dirname($newImagePath));
			$newImage = $this->getImageResource($sourceImagePath);
			$textColor = imagecolorallocate($newImage, array_shift($rgb), array_shift($rgb), array_shift($rgb));

			foreach ($textArr as $singleText) {
				$text = $singleText['text'];
				$fontSize = $singleText['fontsize'];
				$fontPath = $ttfFontFilePath;
				$fontAngle = 0;

				if (!empty($singleText['ttfFontFilePath'])) {
					$temp = explode('.', basename($singleText['ttfFontFilePath']));
					if (('ttf' === strtolower(trim($temp[count($temp) - 1]))) || (file_exists(trim($singleText['ttfFontFilePath'])))) {
						$fontPath = $singleText['ttfFontFilePath'];
					}
				}

				$imageWidth = imagesx($newImage);
				$arr = imagettfbbox($fontSize, 0, $fontPath, $text);
				$textWidth = $arr[2] - $arr[0];
				$textHeight = $arr[7] - $arr[1];
				$splitArr = array();
				if ($textWidth * 1.1 > $imageWidth) {
					$splitArr = $this->getSplitLines($text);
				} else {
					$splitArr[0] = $text;
				}
				$text = '';

				foreach ($splitArr as $index => $text) {
					$arr = imagettfbbox($fontSize, 0, $fontPath, $text);
					$textWidth = $arr[2] - $arr[0];
					$textHeight = $arr[7] - $arr[1];

					if ((0 < $singleText['percent_x'])) {
						$textStartX = $singleText['percent_x']/100 * $newWidth;
						$textStartX -= $textWidth/2;
					} else {
						$textStartX = intval((imagesx($newImage) - $textWidth)/2);
					}

					if ((0 < $singleText['percent_y'])) {
						$textStartY = $singleText['percent_y']/100 * $newHeight;
						if (0 < $index) {
							$textStartY -= $textHeight;
						}
					} else {
						$textStartY = intval((imagesy($newImage) - $textHeight)/2);
					}

					$imageResource = $this->embedTextInImage($newImage, $text, $textStartX, $textStartY, $fontSize, $fontAngle, $textColor, $fontPath);
					$newImage = (false == $imageResource) ? $imageResource : $newImage;
				}
				if (!empty($singleText['trademark'])) {
					$iconX = $textStartX + $textWidth;
					$iconY = $textStartY + $textHeight * 0.6;
					$this->combineImages($newImage, $singleText['trademark'], $iconX, $iconY);
				}
			}

			imagepng($newImage, $newImagePath);

			@imagedestroy($newImage);
		}
		if (!empty($newImagePath) && file_exists($newImagePath)) {
			$newImagePath = str_replace(PATH_site, '', $newImagePath);
			$newImagePath = str_replace(DIRECTORY_SEPARATOR, '/', $newImagePath);
			$output = $newImagePath;
		}

		return $output;
	}

	/**
	 * Writes a string to an image
	 *
	 * @param resource $imageResource The image resource
	 * @param string $text
	 * @param int $startX
	 * @param int $startY
	 * @param float $fontSize
	 * @param float $fontAngle
	 * @param int $fontColor
	 * @param string $ttfFontFilePath
	 * @return resource
	 */
	function embedTextInImage($imageResource, $text, $startX, $startY, $fontSize, $fontAngle, $fontColor, $ttfFontFilePath) {
		$val = imagettftext($imageResource, $fontSize, $fontAngle, $startX, $startY, $fontColor, $ttfFontFilePath, $text);

		return $imageResource;
	}

	/**
	* @param string Source image path
	* @return mixed Returns false on failure, image resource on success
	**/
	function getImageResource($imagePath) {
		$output = false;
		$temp = explode('.', basename($imagePath));
		switch (strtolower($temp[count($temp) - 1])) {
			case ('jpeg'):
			case ('jpg'): {
				$resource = imagecreatefromjpeg($imagePath);
				break;
			}
			case ('gif'): {
				$resource = imagecreatefromgif($imagePath);
				break;
			}
			case ('png'): {
				$resource = imagecreatefrompng($imagePath);
				break;
			}
			default: {
				break;
			}
		}
		$output = (!empty($resource)) ? $resource : $output;

		return $output;
	}

	/**
	 * Tries to get a cached form of the image to avoid resampling every time.
	 *
	 * @param string $imagePath
	 * @param int $newWidth
	 * @param int $newHeight
	 * @param string $storageDir
	 * @return mixed
	 */
	function getImageCached($imagePath, $newWidth, $newHeight, $storageDir = null) {
		$output = false;

		$storageDir = is_dir($storageDir) ? rtrim($storageDir, '/\\') . '/' : dirname($imagePath) . '/';
		$temp = explode('.', basename($imagePath));
		$cachedImagePath = $storageDir . md5(md5_file($imagePath) . $newWidth . $newHeight) . '.' . strtolower($temp[count($temp) - 1]);

		//Try to create a new cached source image if it does not exist
		if (!file_exists($cachedImagePath)) {
			$originalImage = $this->getImageResource($imagePath);
			if (empty($originalImage)) {
				return $output;
			}

			list($originalWidth, $originalHeight) = getimagesize($imagePath);
			$newImage = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
			switch (strtolower($temp[count($temp) - 1])) {
				case ('jpeg'):
				case ('jpg'): {
					imagejpeg($newImage, $cachedImagePath, 100);
					break;
				}
				case ('gif'): {
					imagegif($newImage, $cachedImagePath);
					break;
				}
				case ('png'): {
					imagepng($newImage, $cachedImagePath);
					break;
				}
				default: {
					break;
				}
			}

			@imagedestroy($originalImage);
			@imagedestroy($newImage);
		}

		$output = @is_readable($cachedImagePath) ? $cachedImagePath : $imagePath;

		return $output;
	}

	/**
	* Tries to split a sentence into 2 parts as equal as possible
	*
	* @param string $line
	* @return array
	*/
	function getSplitLines($line) {
		$breakPoint = ceil(strlen($line)/2);
		$words = explode(' ', $line);
		$currentLine = '';
		$lines = array();
		foreach ($words as $word) {
			$currentLine .= $word . ' ';
			if (strlen(trim($currentLine)) >= $breakPoint) {
				$lines[] = $currentLine;
				$currentLine = '';
			}
		}
		if (!empty($currentLine)) {
			$lines[] = $currentLine;
		}

		return $lines;
	}

	/**
	 * Combines two images
	 *
	 * @param reosurce $imageResource
	 * @param string $iconImagePath
	 * @param float $startX
	 * @param float $startY
	 * @return bool
	 */
	function combineImages(&$imageResource, $iconImagePath, $startX, $startY) {
		$output = false;

		if ((empty($imageResource)) || (false === $this->ifValidImageFile($iconImagePath))) {
			return $output;
		}

		list($width, $height) = getimagesize($fullImagePath);

		$iconImage = $this->getImageResource($iconImagePath);
		list($iconWidth, $iconHeight) = getimagesize($iconImagePath);

		$output = imagecopyresampled($imageResource, $iconImage, $startX, $startY, 0, 0, $iconWidth, $iconHeight, $iconWidth, $iconHeight);

		imagedestroy($iconImage);

		return $output;
	}

	/**
	 * Checks validity of an image file
	 *
	 * @param string $imagePath
	 * @return boolean
	 */
	function ifValidImageFile($imagePath) {
		$output = false;

		if (file_exists($imagePath)) {
			$temp = explode('.', basename($imagePath));
			if (in_array(strtolower($temp[count($temp) - 1]), $this->allowedFormats)) {
				$output = true;
			}
		}

		return $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gb_certificate/classes/class.imageUtil.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gb_certificate/classes/class.imageUtil.php']);
}
?>