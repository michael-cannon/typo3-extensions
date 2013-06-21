<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Suman Debnath (suman@srijan.in)
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
 * Plugin 'Certificate Listing' for the 'gb_certificate' extension.
 *
 * @author	Suman Debnath <suman@srijan.in>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Class tx_gbcertificate_pi1
 *
 */
class tx_gbcertificate_pi1 extends tslib_pibase {
	/**
	 * Prefix for variables, etc
	 *
	 * @var string
	 */
	var $prefixId = 'tx_gbcertificate_pi1';		// Same as class name
	/**
	 * File path relative to the extension folder
	 *
	 * @var string
	 */
	var $scriptRelPath = 'pi1/class.tx_gbcertificate_pi1.php';	// Path to this script relative to the extension dir.
	/**
	 * Extension key
	 *
	 * @var string
	 */
	var $extKey = 'gb_certificate';	// The extension key.
	/**
	 * To check cHash or not
	 *
	 * @var bool
	 */
	var $pi_checkCHash = TRUE;

	var $leadingTitle = 'is certified to lead Shared Inquiry';
	var $sharedTitle = 'has achieved proficiency in Shared Inquiry';

	/**
	 * Main function. What else to write ?
	 */
	function main($content, $conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//Sanitizing the incoming configuration array
		$this->conf['fontSizeSmall'] = floatval($this->conf['fontSizeSmall']);
		$this->conf['fontSizeBig'] = floatval($this->conf['fontSizeBig']);
		$this->conf['fontColor'] = trim(strval($this->conf['fontColor']));
		$this->conf['outputPath'] = t3lib_div::fixWindowsFilePath(PATH_site . trim(strval($this->conf['outputPath'])));

		$this->conf['fontSizeSmall'] = (0 < $this->conf['fontSizeSmall']) ? $this->conf['fontSizeSmall'] : 11.5;
		$this->conf['fontSizeBig'] = (0 < $this->conf['fontSizeBig']) ? $this->conf['fontSizeBig'] : 16;
		$this->conf['fontSizeHeader'] = (0 < $this->conf['fontSizeHeader']) ? $this->conf['fontSizeHeader'] : 45;
		$this->conf['fontColor'] = empty($this->conf['fontColor']) ? array(0, 0, 0) : $this->getRGBFromHex($this->conf['fontColor']);
		$this->conf['outputPath'] = ((is_dir($this->conf['outputPath'])) && (is_writable($this->conf['outputPath']))) ? $this->conf['outputPath'] : PATH_site . 'typo3temp';

		//Adding the Javascript file
		$GLOBALS['TSFE']->additionalHeaderData[] = '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/functions.js' . '"></script>';

		//If certificate is being requested
		$certificate = t3lib_div::_GET('certificate');
		if (0 < intval($certificate)) {
			$content = $this->getCertificateDisplay($certificate);
			echo $content;
			die();
		}

		//If listing is being requested
		$content = $this->getListingDisplay($this->getData());

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Gets the listing
	 *
	 * @param array $dataArr
	 * @return string
	 */
	function getListingDisplay($dataArr) {
		$output = '';
		if ((1 > count($dataArr)) || (!is_array($dataArr))) {
			return $output;
		}

		$templateTable = $this->cObj->getSubpart($this->cObj->fileResource('EXT:' . $this->extKey . '/res/listing.html'), 'TEMPLATE_TABLE');
		$templateRow = $this->cObj->getSubpart($templateTable, 'TEMPLATE_ROW');

		$rowContent = '';
		foreach ($dataArr as $dataRow) {
			$imageURL = '#';
			$thumbNail = '';
			$tempTemplateRow = $templateRow;
			if (0 < $dataRow['certificate']) {
				//Generating the certificate url
				$urlParameters = array();
				$urlParameters['no_cache'] = 1;
				$urlParameters['certificate'] = $dataRow['id'];
				$imageURL = $this->pi_getPageLink($GLOBALS['TSFE']->id, null, $urlParameters);

				//Generating the certificate link
				$thumbNail = '<img src="' . $this->getThumbnail($dataRow['certifyText']) . '">';
			} else {
				$tempTemplateRow = $this->cObj->substituteSubpart($templateRow, '###TEMPLATE_THUMBNAIL###', '&nbsp;', 0);
			}

			//Generating the detail link
			$detailURL = (1 > intval($dataRow['detail_pid'])) ? '#' : $this->pi_getPageLink($dataRow['detail_pid']);

			//Marker replacement array
			$replaceArr = array();
			$replaceArr['COURSE_NAME'] = empty($dataRow['title']) ? '&nbsp;' : $dataRow['title'];
			/*$replaceArr['COURSE_NAME'] .= '<BR/>' . $dataRow['certificate'];
			$replaceArr['COURSE_NAME'] .= '<BR/>' . $dataRow['code'];*/
			$replaceArr['NAME'] = empty($dataRow['name']) ? '&nbsp;' : $dataRow['name'];
			$replaceArr['USER_NAME'] = empty($dataRow['username']) ? '&nbsp;' : $dataRow['username'];
			$replaceArr['HOURS'] = empty($dataRow['hours']) ? '&nbsp;' : $dataRow['hours'];
			$replaceArr['DATE'] = empty($dataRow['Dates']) ? '&nbsp;' : $dataRow['Dates'];
			$replaceArr['COURSE_NUMBER'] = empty($dataRow['number']) ? '&nbsp;' : $dataRow['number'];
			$replaceArr['DETAIL_LINK'] = $detailURL;
			$replaceArr['CERTIFICATE_THUMB'] = $thumbNail;
			$replaceArr['CERTIFICATE_LINK'] = $imageURL;
			$replaceArr['BASE_URL'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');

			$rowContent .= $this->cObj->substituteMarkerArray($tempTemplateRow, $replaceArr, '###|###', 1);
		}

		$output = $this->cObj->substituteSubpart($templateTable, '###TEMPLATE_ROW###', $rowContent, 0);

		return $output;
	}

	/**
	 * Gets data for single or multiple row/s
	 *
	 * @param int $cerificateID
	 * @return array
	 */
	function getData($id = 0) {
		$output = array();
		$id = intval($id);

		//If nobody is logged in
		if (empty($GLOBALS['TSFE']->fe_user->user['username'])) {
			return $output;
		}

		$selectFields = 'user.uid as id, course.uid as course_id, course.code, course.title, course.detail_pid, user.name, user.username, course.hours, course.course_prerequisites, user.dates as Dates, user.number, course.show_certificate';
		$table = '`tx_gbcertificate_course_users` AS user LEFT JOIN `tx_gbcertificate_courses` AS course ON (user.code = course.code)';
		$whereClause = array();
		$whereClause[] = 'user.username = \'' . $GLOBALS['TSFE']->fe_user->user['username'] . '\'';
		$whereClause[] = '(course.title <> \'\') OR (course.title IS NOT NULL)';
		$whereClause[] = '(user.name <> \'\') OR (user.name IS NOT NULL)';
		$whereClause[] = '(user.dates <> \'\') OR (user.dates IS NOT NULL)';
		$whereClause[] = 'user.number > 0';
		$whereClause[] = 'course.deleted = 0';
		$whereClause[] = 'course.hidden = 0';
		$whereClause[] = 'user.deleted = 0';
		$whereClause[] = 'user.hidden = 0';
		if (0 < $id) {
			$whereClause[] = 'user.uid = \'' . $id . '\'';
		}
		$whereClause = (0 < count($whereClause)) ? '(' . implode(') AND (', $whereClause) . ')' : '';

		$orderBy = 'user.number ASC';
		$groupBy = '';
		$limit = '';

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $table, $whereClause, $groupBy, $orderBy, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
		}

		//Process data
		$output = $this->getProcessedDataArray($output);

		return $output;
	}

	/**
	 * Generates certificate and returns relative path
	 *
	 * @return mixed
	 */
	function getCertificateDisplay($certificateID) {
		$output = '';

		$certificateID = intval($certificateID);
		if (1 > $certificateID) {
			return $output;
		}

		//Get data for this ID
		$dataArr = $this->getData($certificateID);
		if ((1 > count($dataArr)) || (!is_array($dataArr))) {
			return $output;
		}

		$hours = $dataArr[0]['hours'];
		$code = trim(strtoupper($dataArr[0]['code']));
		if (0 < preg_match('/^T1/', $code)) {
			$hours = 10.0;
		} elseif (0 < preg_match('/^T[23]/', $code)) {
			$fullDataArr = $this->getData();
			$hours = 0;
			foreach ($fullDataArr as $index => $dataRow) {
				$code = trim(strtoupper($dataRow['code']));
				if ((0 < preg_match('/^T[23]/', $code)) && (0 < intval($dataRow['id']))) {
					$hours += floatval($dataRow['hours']);
				}
			}
		}

		//Data + formatting details of text to be embedded in image
		$textArr = array();

		$textArr['cert_title']['text'] = $dataArr[0]['header'];
		$textArr['cert_title']['fontsize'] = $this->conf['fontSizeHeader'];
		$textArr['cert_title']['percent_x'] = 0;
		$textArr['cert_title']['percent_y'] = 33;
		$textArr['cert_title']['ttfFontFilePath'] = t3lib_extMgm::extPath($this->extKey) . 'res/fonts/llitalic.ttf';

		/*$textArr['course_name_big']['text'] = $dataArr[0]['title'];
		$textArr['course_name_big']['fontsize'] = $this->conf['fontSizeBig'];
		$textArr['course_name_big']['percent_x'] = 0;
		$textArr['course_name_big']['percent_y'] = 40;

		$textArr['cert_text_1']['text'] = 'This document certifies that';
		$textArr['cert_text_1']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['cert_text_1']['percent_x'] = 0;
		$textArr['cert_text_1']['percent_y'] = 40;*/

		$textArr['name']['text'] = $dataArr[0]['name'];
		$textArr['name']['fontsize'] = $this->conf['fontSizeBig'];
		$textArr['name']['percent_x'] = 0;
		$textArr['name']['percent_y'] = 50;

		$textArr['cert_text_2']['text'] = $dataArr[0]['certifyText'];
		$textArr['cert_text_2']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['cert_text_2']['percent_x'] = 0;
		$textArr['cert_text_2']['percent_y'] = 57;
		$textArr['cert_text_2']['trademark'] = t3lib_extMgm::extPath($this->extKey) . 'res/trademark.gif';

		/*$textArr['course_name_small']['text'] = $dataArr[0]['title'];
		$textArr['course_name_small']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['course_name_small']['percent_x'] = 0;
		$textArr['course_name_small']['percent_y'] = 56;*/

		$textArr['par_number']['text'] = $dataArr[0]['username'];
		$textArr['par_number']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['par_number']['percent_x'] = 20.5;
		$textArr['par_number']['percent_y'] = 70;

		$textArr['num_hours']['text'] = sprintf('%0.1f', $hours) . ' hours';
		$textArr['num_hours']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['num_hours']['percent_x'] = 0;
		$textArr['num_hours']['percent_y'] = $textArr['par_number']['percent_y'];

		$textArr['date_completion']['text'] = $dataArr[0]['Dates'];
		$textArr['date_completion']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['date_completion']['percent_x'] = 75;
		$textArr['date_completion']['percent_y'] = $textArr['par_number']['percent_y'];

		/*$textArr['course_number']['text'] = $dataArr[0]['number'];
		$textArr['course_number']['fontsize'] = $this->conf['fontSizeSmall'];
		$textArr['course_number']['percent_x'] = 75;
		$textArr['course_number']['percent_y'] = $textArr['par_number']['percent_y'];*/

		//Configuration array
		$configArr = array();
		$configArr['rgb'] = $this->conf['fontColor'];
		$configArr['imageWidth'] = 720;
		$configArr['imageHeight'] = 540;

		//Embed text in image
		require_once(t3lib_extMgm::extPath($this->extKey) . 'classes/class.imageUtil.php');
		$imageUtil = new imageUtil();
		$imageSrc = $imageUtil->embedTextInImageMultiple($textArr, t3lib_extMgm::extPath($this->extKey) . 'res/certificate.gif', t3lib_extMgm::extPath($this->extKey) . 'res/fonts/ll.ttf', $this->conf['outputPath'], $configArr);
		unset($imageUtil);

		$templateTable = $this->cObj->getSubpart($this->cObj->fileResource('EXT:' . $this->extKey . '/res/certificate.html'), 'TEMPLATE_TABLE');

		//Marker replacement array
		$replaceArr = array();
		$replaceArr['IMAGE_ALT'] = empty($dataArr[0]['title']) ? '' : $dataArr[0]['title'];
		$replaceArr['IMAGE_SOURCE'] = empty($imageSrc) ? '' : $imageSrc;
		$replaceArr['BASE_URL'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');

		$output = $this->cObj->substituteMarkerArray($templateTable, $replaceArr, '###|###', 1);

		return $output;
	}

	/**
	 * Processes the data array and adds some elements
	 *
	 * @param array $dataArr
	 * @return array
	 */
	function getProcessedDataArray($dataArr) {
		//Extracting the course ids to an array
		$courseDataArr = $this->getCourseData();
		$coursePreqArr = array();
		foreach ($courseDataArr as $dataRow) {
			$coursePreqArr[] = substr($dataRow['course_prerequisites'], 0, 4);
		}
		$coursePreqArr = array_values(array_unique($coursePreqArr));

		$storedIndex = -1;
		$counter = 0;
		$title = 'Certificate';
		foreach ($dataArr as $index => $dataRow) {
			$showCertificate = intval($dataRow['show_certificate']);
			$ifShowCertificate = 0;
			$code = trim(strtoupper($dataRow['code']));
			$code = substr($code, 0, 4);
			//If course is T1xx and ends with 0, 1, 2 or 3
			if (0 < preg_match('/^T10(0|1|2|3)$/', $code)) {
				//If valid id and show_certificate
				if ((0 < intval($dataRow['id'])) && (0 < $showCertificate)) {
					$ifShowCertificate = 0;
					//Check if course_prerequisites has been satisfied
					if (false === in_array($code, $coursePreqArr)) {
						$ifShowCertificate = 1;
					}
				}
				$certifyText = $this->leadingTitle;
				//If course id is T2xx or T3xx
			} elseif (0 < preg_match('/^T[23]/', $code)) {
				//If valid id and show_certificate
				if ((0 < intval($dataRow['id'])) && (0 < $showCertificate)) {
					$certifyText = $this->sharedTitle;
					$counter++;
					//$storedIndex = (-1 < $storedIndex) ? $storedIndex : $index;
					if (4 == $counter) {
						$storedIndex = $index;
					}
				}
			}

			$dataArr[$index]['header'] = $title;
			$dataArr[$index]['certifyText'] = $certifyText;
			$dataArr[$index]['certificate'] = $ifShowCertificate;
		}

		if (4 <= $counter) {
			$dataArr[$storedIndex]['certificate'] = 1;
		}

		return $dataArr;
	}

	/**
	 * Gets complete course information
	 *
	 * @return array
	 */
	function getCourseData() {
		$output = array();

		$selectFields = 'course.code, course.course_prerequisites';
		$table = 'tx_gbcertificate_courses AS course';
		$whereClause = array();
		$whereClause[] = '1 = 1';
		$whereClause[] = '(course.title <> \'\') OR (course.title IS NOT NULL)';
		$whereClause[] = 'course.deleted = 0';
		$whereClause[] = 'course.hidden = 0';
		$whereClause = (0 < count($whereClause)) ? '(' . implode(') AND (', $whereClause) . ')' : '';
		$orderBy = 'course.code ASC';
		$groupBy = '';
		$limit = '';

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $table, $whereClause, $groupBy, $orderBy, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
		}

		return $output;
	}

	/**
	 * Gets thumbnail address
	 *
	 * @param string $header
	 * @return string
	 */
	function getThumbnail($header = '') {
		$imagePath = t3lib_extMgm::extPath($this->extKey) . ((0 == strcmp($this->sharedTitle, $header)) ? 'res/shared.png' : 'res/leading.png');

		require_once(t3lib_extMgm::extPath($this->extKey) . 'classes/class.imageUtil.php');
		$imageUtil = new imageUtil();
		$imageSrc = $imageUtil->getImageCached($imagePath, 120, 120, $this->conf['outputPath']);
		unset($imageUtil);

		if (!empty($imageSrc) && file_exists($imageSrc)) {
			$imageSrc = str_replace(PATH_site, '', $imageSrc);
			$imageSrc = str_replace(DIRECTORY_SEPARATOR, '/', $imageSrc);
		}

		return $imageSrc;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $hex
	 * @param unknown_type $asString
	 * @return unknown
	 */
	function getRGBFromHex($hex, $asString = false) {
		// strip off any leading #
		if (0 === strpos($hex, '#')) {
			$hex = substr($hex, 1);
		} else if (0 === strpos($hex, '&H')) {
			$hex = substr($hex, 2);
		}

		// break into hex 3-tuple
		$cutpoint = ceil(strlen($hex) / 2) - 1;
		$rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

		// convert each tuple to decimal
		$rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
		$rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
		$rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

		return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gb_certificate/pi1/class.tx_gbcertificate_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gb_certificate/pi1/class.tx_gbcertificate_pi1.php']);
}
?>
