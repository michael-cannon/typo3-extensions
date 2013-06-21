<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Peimic.com (http://peimic.com)
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
 * Module extension (addition to function menu) 'Affiliate Reports' for the 'affiliate_tracker' extension.
 *
 * @author	Suman Debnath <suman@srijan.in>
 * @author	Michael Cannon <michael@peimic.com>
 */

require_once(PATH_t3lib."class.t3lib_extobjbase.php");

class tx_affiliatetracker_modfunc1 extends t3lib_extobjbase {

	var $extKey = 'affiliate_tracker';	// The extension key.

	var $tb_consultancies = 'tx_t3consultancies'; //fe user table
	var $tb_visitor_tracking = 'tx_affiliatetracker_visitor_tracking'; //visitor tracking data table
	var $tb_affiliate_code = 'tx_affiliatetracker_codes'; //affiliate code data table

	var $frm_download_submit = 'frm_affiliatetracker_download';
	var $frm_view_submit = 'frm_affiliatetracker_view';
	var $frm_report_type = 'frm_affiliatetracker_report_type';
	var $frm_view_type = 'frm_affiliatetracker_view_type';

	var $err_msg = '&nbsp;';
	var $result_display = '&nbsp;';
	var $file_name = 'affiliate_report'; //without file extension
	var $newline_char = "\n";
	var $current_view_mode;

	function modMenu() {
		global $LANG;

		return Array (
		"tx_affiliatetracker_modfunc1_check" => "",
		);
	}

	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE, $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		$theOutput .= $this->pObj->doc->spacer(5);
		$theOutput .= $this->pObj->doc->section($LANG->getLL("title"), $this->getContent(), 0, 1);

		//$GLOBALS['TYPO3_DB']->debugOutput = true;

		return $theOutput;
	}

	/**
	* Gets content to be shown
	* @return string
	* @access private
	*/
	function getContent() {
		$content = '';

		if (false === $this->checkPageData($this->pObj->id)) {
			$content = "No tracking data found on this page. Pleae choose a page or folder with tracking data.";
			return $content;
		}

		//Setting the newline character according to the client platform
		$this->setNewLineChar();

		//If CVS is requested
		if (t3lib_div::_GP($this->frm_download_submit)) {
			$data_arr = $this->getDataArray(t3lib_div::_GP($this->frm_report_type), t3lib_div::_GP($this->frm_view_type), t3lib_div::_GP('sponsor_id'), t3lib_div::_GP('referer'), t3lib_div::_GP('start_date'), t3lib_div::_GP('end_date'), null, $this->pObj->id);
			if (is_array($data_arr) && (count($data_arr) > 0)) {
				$this->getCSV($data_arr);
			} else {
				$this->err_msg = 'No Data!';
			}
		} elseif (t3lib_div::_GP($this->frm_view_submit)) {
			//If in-page view is requested
			$data_arr = $this->getDataArray(t3lib_div::_GP($this->frm_report_type), t3lib_div::_GP($this->frm_view_type), t3lib_div::_GP('sponsor_id'), t3lib_div::_GP('referer'), t3lib_div::_GP('start_date'), t3lib_div::_GP('end_date'), null, $this->pObj->id);
			if (is_array($data_arr) && (count($data_arr) > 0)) {
				$this->result_display = $this->getDataDisplay($data_arr, $this->current_view_mode);
			} else {
				$this->err_msg = 'No Data!';
			}
		}

		$content .= $this->getForm($this->pObj->id);

		return $content;
	}

	/**
	* Generates a data array based upon form input
	* @return array
	* @param int $sponsor_id Optional sponsor id
	* @param string $referer Optional referer URL
	* @param int $start_date Optional start date timestamp
	* @param int $end_date Optional end date timestamp
	* @param int $feuser_id Optional fe user id
	* @access private
	*/
	function getDataArray($report_type = null, $view_type = null, $sponsor_id = null, $referer = null, $start_date = null, $end_date = null, $feuser_id = null, $page_id = null) {
		$output = array();

		if ($report_type == 'detail') {
			$this->current_view_mode = 'detail';
			switch ($view_type) {
				case 'day':
				case 'month':
				$output = $this->getDetailDataArray($view_type, $sponsor_id, $referer, $start_date, $end_date, $feuser_id, $page_id);
				break;
				default:
				break;
			}
		} elseif ($report_type == 'summary') {
			$this->current_view_mode = 'summary';
			switch ($view_type) {
				case 'day':
				case 'month':
				$output = $this->getSummaryDataArray($view_type, $sponsor_id, $referer, $start_date, $end_date, $feuser_id, $page_id);
				break;
				default:
				break;
			}
		} else {
			$this->current_view_mode = 'generic';
			$output = $this->getGenericDataArray($sponsor_id, $referer, $start_date, $end_date, $feuser_id, $page_id);
		}

		return $output;
	}

	function getGenericDataArray($sponsor_id = null, $referer = null, $start_date = null, $end_date = null, $feuser_id = null, $page_id = null) {
		$format = ($report_type == 'month') ? '%M %Y' : '%W, %M %e, %Y';

		$select_fields = "FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format') AS date,
		tx_affiliatetracker_visitor_tracking.landing_url, 
		tx_affiliatetracker_visitor_tracking.referer_url, 
		fe_users.name as fe_user_name, 
		TRIM(TRAILING CONCAT(tx_affiliatetracker_visitor_tracking.affiliate_source_code, tx_affiliatetracker_visitor_tracking.affiliate_index_code) FROM tx_affiliatetracker_visitor_tracking.full_affiliate_code) as affiliate_code,
		tx_t3consultancies.title as Sponsor";

		$table = "tx_affiliatetracker_visitor_tracking
		LEFT JOIN fe_users ON (fe_users.uid = tx_affiliatetracker_visitor_tracking.feuser_id) 
		LEFT JOIN tx_t3consultancies ON (tx_t3consultancies.uid = tx_affiliatetracker_visitor_tracking.affiliate_id)";

		$orderBy = "FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format'),
		tx_t3consultancies.title";
		$groupBy = "";
		$limit = "";

		$this->removeEmptyValues($sponsor_id);
		if (0 < count($sponsor_id)) {
			foreach ($sponsor_id as $key => $value) {
				$sponsor_id[$key] = "(tx_t3consultancies.uid = ".$sponsor_id[$key].")";
			}
			$where_clause[] = (1 < count($sponsor_id)) ? '('.implode(' OR ', $sponsor_id).')' : implode(' OR ', $sponsor_id);
		}
		$this->removeEmptyValues($referer);
		if (0 < count($referer)) {
			foreach ($referer as $key => $value) {
				$referer[$key] = "(tx_affiliatetracker_visitor_tracking.referer_url = '".$referer[$key]."')";
			}
			$where_clause[] = (1 < count($referer)) ? '('.implode(' OR ', $referer).')' : implode(' OR ', $referer);
		}
		$start_date = trim($start_date);
		if ('' != $start_date) {
			$start_date = explode('/', $start_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp > '.mktime(0, 0, 0, $start_date[0], $start_date[1], $start_date[2]).')';
		}
		$end_date = trim($end_date);
		if ('' != $end_date) {
			$end_date = explode('/', $end_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp < '.mktime(0, 0, 0, $end_date[0], $end_date[1], $end_date[2]).')';
		}
		$feuser_id = intval($feuser_id);
		if (0 < $feuser_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.feuser_id = '.$feuser_id.')';
		}
		$page_id = intval($page_id);
		if (0 < $page_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.pid = '.$page_id.')';
		}

		if (is_array($where_clause) && (count($where_clause) > 0)) {
			$where_clause = implode(' AND ', $where_clause);
		}

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause, $groupBy, $orderBy, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
		}

		return $output;
	}

	function getDetailDataArray($report_type = null, $sponsor_id = null, $referer = null, $start_date = null, $end_date = null, $feuser_id = null, $page_id = null) {
		$format = ($report_type == 'month') ? '%M %Y' : '%W, %M %e, %Y';

		$select_fields = "FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format') as Date,
		tx_t3consultancies.title as Sponsor, 
		TRIM(TRAILING CONCAT(tx_affiliatetracker_visitor_tracking.affiliate_source_code, tx_affiliatetracker_visitor_tracking.affiliate_index_code) FROM tx_affiliatetracker_visitor_tracking.full_affiliate_code) as affiliate_code, 
		tx_affiliatetracker_visitor_tracking.affiliate_source_code as sponsor_code, 
		tx_affiliatetracker_visitor_tracking.affiliate_index_code as index_code, 
		COUNT(*) as Count, 
		tx_affiliatetracker_visitor_tracking.full_affiliate_code as full_code";

		$table = "tx_affiliatetracker_visitor_tracking
		LEFT JOIN fe_users ON (fe_users.uid = tx_affiliatetracker_visitor_tracking.feuser_id) 
		LEFT JOIN tx_t3consultancies ON tx_t3consultancies.uid = tx_affiliatetracker_visitor_tracking.affiliate_id";

		$groupBy = "tx_t3consultancies.title,
		TRIM(TRAILING CONCAT(tx_affiliatetracker_visitor_tracking.affiliate_source_code, tx_affiliatetracker_visitor_tracking.affiliate_index_code) FROM tx_affiliatetracker_visitor_tracking.full_affiliate_code), 
		tx_affiliatetracker_visitor_tracking.affiliate_source_code, 
		tx_affiliatetracker_visitor_tracking.affiliate_index_code, 
		tx_affiliatetracker_visitor_tracking.full_affiliate_code, 
		FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format')";
		$orderBy = "";
		$limit = "";

		$this->removeEmptyValues($sponsor_id);
		if (0 < count($sponsor_id)) {
			foreach ($sponsor_id as $key => $value) {
				$sponsor_id[$key] = "(tx_t3consultancies.uid = ".$sponsor_id[$key].")";
			}
			$where_clause[] = (1 < count($sponsor_id)) ? '('.implode(' OR ', $sponsor_id).')' : implode(' OR ', $sponsor_id);
		}
		$this->removeEmptyValues($referer);
		if (0 < count($referer)) {
			foreach ($referer as $key => $value) {
				$referer[$key] = "(tx_affiliatetracker_visitor_tracking.referer_url = '".$referer[$key]."')";
			}
			$where_clause[] = (1 < count($referer)) ? '('.implode(' OR ', $referer).')' : implode(' OR ', $referer);
		}
		$start_date = trim($start_date);
		if ('' != $start_date) {
			$start_date = explode('/', $start_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp > '.mktime(0, 0, 0, $start_date[0], $start_date[1], $start_date[2]).')';
		}
		$end_date = trim($end_date);
		if ('' != $end_date) {
			$end_date = explode('/', $end_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp < '.mktime(0, 0, 0, $end_date[0], $end_date[1], $end_date[2]).')';
		}
		$feuser_id = intval($feuser_id);
		if (0 < $feuser_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.feuser_id = '.$feuser_id.')';
		}
		$page_id = intval($page_id);
		if (0 < $page_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.pid = '.$page_id.')';
		}

		if (is_array($where_clause) && (count($where_clause) > 0)) {
			$where_clause = implode(' AND ', $where_clause);
		}

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause, $groupBy, $orderBy, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
		}

		if (1 > count($output)) return $output;
		$count = 0;
		$data_arr = array();
		foreach ($output as $index => $value) {
			$count += $value['Count'];
			$data_arr[] = $value;
			if ($output[$index+1]['affiliate_code'].$output[$index+1]['sponsor_code'] != $value['affiliate_code'].$value['sponsor_code']) {
				$temp_arr = array(
				'Date' => '',
				'Sponsor' => '',
				'affiliate_code' => '',
				'sponsor_code' => $value['Sponsor']." ".$value['affiliate_code'].$value['sponsor_code']." total",
				'index_code' => '',
				'Count' => $count,
				'full_code' => ''
				);
				$data_arr[] = $temp_arr;
				$count = 0;
			}
		}

		return $data_arr;
	}

	function getSummaryDataArray($report_type = null, $sponsor_id = null, $referer = null, $start_date = null, $end_date = null, $feuser_id = null, $page_id = null) {
		$format = ($report_type == 'month') ? '%M %Y' : '%W, %M %e, %Y';

		$select_fields = "FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format') as Date,
		tx_t3consultancies.title as Sponsor, 
		COUNT(*) as Count";

		$table = "tx_affiliatetracker_visitor_tracking
		LEFT JOIN fe_users ON (fe_users.uid = tx_affiliatetracker_visitor_tracking.feuser_id) 
		LEFT JOIN tx_t3consultancies ON tx_t3consultancies.uid = tx_affiliatetracker_visitor_tracking.affiliate_id";

		$groupBy = "tx_t3consultancies.title,
		FROM_UNIXTIME(tx_affiliatetracker_visitor_tracking.tstamp, '$format')";
		$orderBy = "";
		$limit = "";

		$this->removeEmptyValues($sponsor_id);
		if (0 < count($sponsor_id)) {
			foreach ($sponsor_id as $key => $value) {
				$sponsor_id[$key] = "(tx_t3consultancies.uid = ".$sponsor_id[$key].")";
			}
			$where_clause[] = (1 < count($sponsor_id)) ? '('.implode(' OR ', $sponsor_id).')' : implode(' OR ', $sponsor_id);
		}
		$this->removeEmptyValues($referer);
		if (0 < count($referer)) {
			foreach ($referer as $key => $value) {
				$referer[$key] = "(tx_affiliatetracker_visitor_tracking.referer_url = '".$referer[$key]."')";
			}
			$where_clause[] = (1 < count($referer)) ? '('.implode(' OR ', $referer).')' : implode(' OR ', $referer);
		}
		$start_date = trim($start_date);
		if ('' != $start_date) {
			$start_date = explode('/', $start_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp > '.mktime(0, 0, 0, $start_date[0], $start_date[1], $start_date[2]).')';
		}
		$end_date = trim($end_date);
		if ('' != $end_date) {
			$end_date = explode('/', $end_date);
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.tstamp < '.mktime(0, 0, 0, $end_date[0], $end_date[1], $end_date[2]).')';
		}
		$feuser_id = intval($feuser_id);
		if (0 < $feuser_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.feuser_id = '.$feuser_id.')';
		}
		$page_id = intval($page_id);
		if (0 < $page_id) {
			$where_clause[] = '(tx_affiliatetracker_visitor_tracking.pid = '.$page_id.')';
		}

		if (is_array($where_clause) && (count($where_clause) > 0)) {
			$where_clause = implode(' AND ', $where_clause);
		}

		//echo $GLOBALS['TYPO3_DB']->SELECTquery($select_fields, $table, $where_clause, $groupBy, $orderBy, $limit);
		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause, $groupBy, $orderBy, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
		}

		if (1 > count($output)) return $output;
		$count = 0;
		$data_arr = array();
		foreach ($output as $index => $value) {
			$count += $value['Count'];
			$data_arr[] = $value;
			if ($output[$index+1]['Sponsor'] != $value['Sponsor']) {
				$temp_arr = array(
				'Date' => '',
				'Sponsor' => $value['Sponsor']." total",
				'Count' => $count
				);
				$data_arr[] = $temp_arr;
				$count = 0;
			}
		}

		return $data_arr;
	}

	function getSponsorList($page_id = null) {
		$page_id = intval($page_id);
		$output = '<select name="sponsor_id[]" size="6" multiple="multiple">
					<option value="">Show All</option>';

		$select_fields = 'DISTINCT tx_t3consultancies.uid, tx_t3consultancies.title';
		$table = 'tx_t3consultancies, tx_affiliatetracker_visitor_tracking';
		$where_clause[] = (0 < $page_id) ? "(tx_t3consultancies.pid = $page_id)" : '';
		$where_clause[] = '(tx_affiliatetracker_visitor_tracking.affiliate_id = tx_t3consultancies.uid)';
		$where_clause = implode(' AND ', $where_clause);
		$group_by = '';
		$order_by = 'tx_t3consultancies.title';
		$limit = '';

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table, $where_clause, $group_by, $order_by, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output .= "<option value=\"".$row['uid']."\">".$row['title']."</option>";
				}
			}
		}
		$output .= '</select>';

		return $output;
	}

	/**
	* Gets Referer URL list in a select box
	* @return string
	* @param int $page_id Optional page id to show only data from a particular page
	* @access private
	*/
	function getRefererList($page_id = null) {
		$output = '<select name="referer[]" size="6" multiple="multiple">
					<option value="">Show All</option>';
		$select_fields = 'DISTINCT referer_url';
		$where_clause = "(referer_url <> '')";
		$where_clause .= (intval($page_id) > 0) ? ' AND (pid = '.intval($page_id).')' : '';
		$group_by = '';
		$order_by = 'referer_url';
		$limit = '';

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $this->tb_visitor_tracking, $where_clause, $group_by, $order_by, $limit)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$output .= '<option value="'.$row['referer_url'].'">'.$row['referer_url'].'</option>';
				}
			}
		}
		$output .= '</select>';

		return $output;
	}

	/**
	* Transforms timestamp to US style date string
	* @return string
	* @param string $type Can be 'start' or 'end'
	* @param int $date Timestamp
	* @access private
	*/
	function getDate($type, $date = null) {
		$type = ($type == 'start') ? 'start' : 'end';
		$date = (intval($date) > 0) ? date('m/d/Y', $date) : '';

		$output = '<input type="text" name="'.$type.'_date" value="'.$date.'">';

		return $output;
	}

	/**
	* Generates CSV file. File name is determined by class member 'file_name'
	* @return void
	* @param array $data_arr Incoming data array
	* @access private
	*/
	function getCSV($data_arr) {
		header('Content-Type: text/plain; name='.$this->file_name.'.csv');
		header('Content-disposition: attachment; filename='.$this->file_name.'.csv');

		array_unshift($data_arr, array_keys($data_arr[0]));

		$content = '';
		$row_count = 0;
		foreach ($data_arr as $single_arr) {
			foreach ($single_arr as $index => $value) {
				if ($row_count == 0) $value = ucwords(str_replace('_', ' ', $value));
				$single_arr[$index] = '"'.$value.'"';
			}
			$row_count++;
			$content .= implode(',', $single_arr).$this->newline_char;
		}
		echo $content;

		exit;
	}

	/**
	* Uses template to generate the HTML data display
	* @return void
	* @param array $data_arr Incoming data array
	* @access private
	*/
	function getDataDisplay($data_arr, $type) {
		if ($type == 'detail') {
			$file = t3lib_extMgm::extPath($this->extKey).'res/detail_display.html';
		} elseif ($type == 'summary') {
			$file = t3lib_extMgm::extPath($this->extKey).'res/summary_display.html';
		} else {
			$file = t3lib_extMgm::extPath($this->extKey).'res/data_display.html';
		}
		//Using custom function since Typo3 function has problem sometimes when used multiple times in the same template
		$content = $this->getSubTemplate(file_get_contents($file), 'MAIN_TEMPLATE');
		$template = $this->getSubTemplate($content, 'SUB_TEMPLATE');
		$output = '';

		foreach ($data_arr as $single_arr) {
			$temp_content = $template;
			foreach ($single_arr as $key => $value) {
				$temp_content = str_replace('###'.strtoupper($key).'###', $value, $temp_content);
			}
			$output .= $temp_content;
		}
		$content = str_replace($template, $output, $content);

		return $content;
	}

	/**
	* Uses template to generate the form
	* @return string
	* @param int $page_id Optional page id to show only data from a particular page
	* @access private
	*/
	function getForm($page_id = null) {
		$content = file_get_contents(t3lib_extMgm::extPath($this->extKey).'res/form.html');

		$replace_arr['SPONSOR_SELECT'] = $this->getSponsorList($page_id);
		$replace_arr['REFERER_SELECT'] = $this->getRefererList($page_id);
		$replace_arr['DOWNLOAD_SUBMIT'] = '<input type="submit" name="'.$this->frm_download_submit.'" value="Download as CSV">';
		$replace_arr['VIEW_SUBMIT'] = '<input type="submit" name="'.$this->frm_view_submit.'" value="View Data">';
		$replace_arr['START_DATE'] = $this->getDate('start');
		$replace_arr['END_DATE'] = $this->getDate('end');
		$replace_arr['ERR_MSG'] = $this->err_msg;
		$replace_arr['REPORT_TYPE'] = $this->frm_report_type;
		$replace_arr['VIEW_TYPE'] = $this->frm_view_type;
		$replace_arr['DATA_DISPLAY'] = $this->result_display;

		foreach ($replace_arr as $tag => $value) {
			$content = str_replace('###'.$tag.'###', $value, $content);
		}

		return $content;
	}

	/**
	* Gets the subtemplate from a string enclosed by markers
	* @return string
	* @param string $contents The input string. Generally the template contents
	* @param string $marker Marker enclosing the subtemplate
	* @access private
	*/
	function getSubTemplate($contents, $marker) {
		if ($marker && strstr($contents, $marker)) {
			$start = strpos($contents, $marker)+strlen($marker);
			$stop = @strpos($contents, $marker, $start+1);
			$sub = substr($contents, $start, $stop-$start);

			$reg=Array();
			ereg('^[^<]*-->',$sub,$reg);
			$start+=strlen($reg[0]);

			$reg=Array();
			ereg('<!--[^>]*$',$sub,$reg);
			$stop-=strlen($reg[0]);

			return substr($contents, $start, $stop-$start);
		}
	}

	/**
	* Removes empty values from an array
	* @return void
	* @access private
	*/
	function removeEmptyValues(&$arr) {
		if (is_array($arr)) {
			foreach ($arr as $key => $value) {
				if ((1 > intval($value)) && ('' == trim($value))) {
					unset($arr[$key]);
				}
			}
			$arr = array_values($arr);
		}
	}

	/**
	* Checks if any Visitor Tracking data is available in a given page
	* @return bool
	* @param int Page ID
	* @access private
	*/
	function checkPageData($page_id) {
		$output = false;
		$page_id = intval($page_id);
		if (1 > $page_id) {
			return $output;
		}

		$select_fields = "*";
		$where_clause = "pid = $page_id";

		if ($result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $this->tb_visitor_tracking, $where_clause)) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
				$output = true;
			}
		}

		return $output;
	}

	/**
	* Sets the newline character
	* @return void
	* @access private
	*/
	function setNewLineChar() {
		if (eregi('Windows', $_SERVER['HTTP_USER_AGENT'])) {
			$this->newline_char = "\r\n";
		}
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/affiliate_tracker/modfunc1/class.tx_affiliatetracker_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/affiliate_tracker/modfunc1/class.tx_affiliatetracker_modfunc1.php"]);
}
?>
