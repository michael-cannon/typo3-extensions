<?php

class utils {


	/**
     * Get Form Controls
     *
     * @param string $controlType Type of the Form Control(input, select)
     * @param array $conf Array of different values of Control( array('type' => 'text','value' => 'Some Value','size' => '17')
     * @return string Returns the Form Control
     */
	function getControl($controlType,$conf){
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

				$returnVal="<SELECT $control_fields>$optionList</SELECT>";
				break;
			case 'input':
				$returnVal="<INPUT $control_fields>";
				break;
			case 'textarea':
				$returnVal="<TEXTAREA $control_fields>".(isset($conf['value']) ? $conf['value'] : '')."</TEXTAREA>";
				break;
		}

		return $returnVal;
	}

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
	 * Placeholder Generation
	 *
	 * @param integer $left Left position for Div tag
	 * @param integer $width Width position for Div tag
	 * @param integer $top Top position for Div tag
	 * @param integer $incrementVal Spacing between two Div tag
	 * @param array $addressArr Array for which div tags are to be generated
	 * @return string
	 */
	function placeHolderPdf($left,$width,$top,$incrementVal,$dataArr){
		$content='';
		foreach($dataArr as $dataVal){
			$content .= "<div border=0 align=left left=$left width=$width top=$top>$dataVal</div>";
			$top+=$incrementVal;
		}

		return $content;
	}


	function getCountry($thisObj) {

		$fields = "cn_iso_3, cn_short_en";
		$table='static_countries';
		$where = " 1=1 ".$thisObj->cObj->enableFields($table);
		$order = "cn_short_en";

		$rsCountries = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$order);

		if ( $rsCountries) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows( $rsCountries)) {
				while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rsCountries)) {
					$contentArr[$row['cn_iso_3']] = $row['cn_short_en'];

				}
			}
		}



		return $contentArr;

	}

	function getCurrentURL() {
		$currentURL = 'http';
		$script_name = '';

		if(isset($_SERVER['REQUEST_URI'])) {
			$script_name = $_SERVER['REQUEST_URI'];
		} else {
			$script_name = $_SERVER['PHP_SELF'];
			if(trim($_SERVER['QUERY_STRING']) != '') {
				$script_name .=  '?'.$_SERVER['QUERY_STRING'];
			}
		}

		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$currentURL .=  's';
		}

		$currentURL .=  '://';
		if($_SERVER['SERVER_PORT'] != '80') {
			$currentURL .= $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$script_name;
		} else {
			$currentURL .= $_SERVER['HTTP_HOST'].$script_name;
		}

		return $currentURL;
	}

	function getActionUrl($searchKey, $searchArr){
		$searchKey = str_replace(' ', '', trim(strtolower($searchKey)));
		$retKey='';
		foreach($searchArr as $key => $val) {
			$val = str_replace(' ', '', trim(strtolower($val)));
			if(0 == strcmp($searchKey, $val)) {
				$retKey = $key;
			}
		}
		return $retKey;
	}

	function _fileDownload($filename) {
		if (!file_exists($filename)) {
			return;
		}

		$image['name'] = basename($filename);
		$headerParam = array('contentType' => $this->getContentType('pdf'),
		'contentLength' => filesize($filename),
		'fileName' => $filename
		);

		$this->setPageHeader($headerParam);
		readfile($filename);
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
	 * Calculates the difference for two given dates, and returns the result
	 * in specified unit.
	 *
	 * @param string    Initial date (format: [dd-mm-YYYY hh:mm:ss], hh is in 24hrs format)
	 * @param string    Last date (format: [dd-mm-YYYY hh:mm:ss], hh is in 24hrs format)
	 * @param char    'd' to obtain results as days, 'h' for hours, 'm' for minutes, 's' for seconds, and 'a' to get an indexed array of days, hours, minutes, and seconds
	 *
	 * @return mixed    The result in the unit specified (float for all cases, except when unit='a', in which case an indexed array), or null if it could not be obtained
	 */
	function getDateDifference($dateFrom, $dateTo, $unit = 'd') {
		$difference = null;

		$dateFromElements = split(' ', $dateFrom);
		$dateToElements = split(' ', $dateTo);

		$dateFromDateElements = split('-', $dateFromElements[0]);
		$dateFromTimeElements = split(':', $dateFromElements[1]);
		$dateToDateElements = split('-', $dateToElements[0]);
		$dateToTimeElements = split(':', $dateToElements[1]);

		// Get unix timestamp for both dates
		$date1 = mktime($dateFromTimeElements[0], $dateFromTimeElements[1], $dateFromTimeElements[2], $dateFromDateElements[1], $dateFromDateElements[0], $dateFromDateElements[2]);
		$date2 = mktime($dateToTimeElements[0], $dateToTimeElements[1], $dateToTimeElements[2], $dateToDateElements[1], $dateToDateElements[0], $dateToDateElements[2]);

		if( $date1 > $date2 ) {
			return null;
		}

		$diff = $date2 - $date1;

		$days = 0;
		$hours = 0;
		$minutes = 0;
		$seconds = 0;

		// there are 86,400 seconds in a day
		if ($diff % 86400 <= 0) {
			$days = $diff / 86400;
		}

		if($diff % 86400 > 0) {
			$rest = ($diff % 86400);
			$days = ($diff - $rest) / 86400;

			if( $rest % 3600 > 0 ) {
				$rest1 = ($rest % 3600);
				$hours = ($rest - $rest1) / 3600;

				if( $rest1 % 60 > 0 ) {
					$rest2 = ($rest1 % 60);
					$minutes = ($rest1 - $rest2) / 60;
					$seconds = $rest2;
				} else {
					$minutes = $rest1 / 60;
				}
			} else {
				$hours = $rest / 3600;
			}
		}

		switch($unit) {
			case 'd':
			case 'D':
				$partialDays = 0;
				$partialDays += ($seconds / 86400);
				$partialDays += ($minutes / 1440);
				$partialDays += ($hours / 24);
				$difference = $days + $partialDays;
				break;
			case 'h':
			case 'H':
				$partialHours = 0;
				$partialHours += ($seconds / 3600);
				$partialHours += ($minutes / 60);
				$difference = $hours + ($days * 24) + $partialHours;
				break;
			case 'm':
			case 'M':
				$partialMinutes = 0;
				$partialMinutes += ($seconds / 60);
				$difference = $minutes + ($days * 1440) + ($hours * 60) + $partialMinutes;
				break;
			case 's':
			case 'S':
				$difference = $seconds + ($days * 86400) + ($hours * 3600) + ($minutes * 60);
				break;
			case 'a':
			case 'A':
				$difference = array (
				"days" => $days,
				"hours" => $hours,
				"minutes" => $minutes,
				"seconds" => $seconds
				);
				break;
		}
		return $difference;
	}

	/**
     * Determine whether or not the passed year is a leap year.
     *
     * @param   int     $y      The year as a four digit integer
     * @return  boolean         True if the year is a leap year, false otherwise
     */
	function isLeapYear($y) {
		return $y % 4 == 0 && ($y % 400 == 0 || $y % 100 != 0);
	}
}
?>