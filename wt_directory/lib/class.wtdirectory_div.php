<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Alexander Kellner <alexander.kellner@einpraegsam.net>
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

require_once(PATH_tslib.'class.tslib_pibase.php');

class wtdirectory_div extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any script in pi1 for locallang
	var $secParams = array('show' => 'int', 'vCard' => 'int', 'pointer' => 'int', 'catfilter' => 'int'); // Allowed piVars
	var $secParamsSecondLevel = array('filter' => 'text'); // Allowed piVars for second level
	var $cbDining				= array( 
									'tx_cbdiningguide_price'
									, 'tx_cbdiningguide_cuisine'
									, 'tx_cbdiningguide_specialty'
									, 'tx_cbdiningguide_meals'
									, 'tx_cbdiningguide_neighborhood'
								);
	
	
	// Function linker() generates link (email and url) from pure text string within an email or url ('test www.test.de test' => 'test <a href="http://www.test.de">www.test.de</a> test')
    function linker($link,$additinalParams = '') {
		$link = str_replace("http://www.","www.",$link);
        $link = str_replace("www.","http://www.",$link);
        $link = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\"$additinalParams>$1</a>", $link);
        $link = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\"$additinalParams>$1</a>",$link);
		$link = nl2br(trim($link));
    	
        return $link;
    }
	
	
	// Function clearName() to disable not allowed letters (only A-Z and 0-9 allowed) (e.g. Perfect Extension -> perfectextension)
	function clearName($string,$strtolower = 0,$cut = 0) {
		$string = preg_replace("/[^a-zA-Z0-9]/","",$string); // replace not allowed letters with nothing
		if($strtolower) $string = strtolower($string); // string to lower if active
		if($cut) $string = substr($string,0,$cut); // cut after X signs if active
		
		if(isset($string)) return $string;
	}
	
	
	// Function sec() is a security function against sql injection
	function sec($piVars) {
		if(isset($piVars) && is_array($piVars)) { // if piVars
			foreach ($piVars as $key => $value) {
				if (!is_array($piVars[$key])) { // first level
				
					if (array_key_exists($key, $this->secParams)) { // Allowed parameter
						if ($this->secParams[$key] == 'int') $piVars[$key] = intval($value); // show: should be an integer
						elseif ($this->secParams[$key] == 'text') { // show: should be text
							$piVars[$key] = strip_tags(trim($value)); // strip_tags removes html and php code
							$piVars[$key] = addslashes($piVars[$key]); // use addslashes if escape_string is not available
						}
						elseif (strpos($this->secParams[$key], '"') !== false) { // if a quote exists
							$piVars[$key] = str_replace('"','',$this->secParams[$key]);
						}
						else unset($piVars[$key]); // delete
					}
					else unset($piVars[$key]); // delete
					
				} else { // second level
					if (array_key_exists($key, $this->secParamsSecondLevel)) { // if this key is allowed
						if (is_array($piVars[$key]) && isset($piVars[$key])) { // only if exists
							foreach ($piVars[$key] as $key2 => $value2) { // one row for every key in second level
								if (in_array($key2, $this->getAddressFields())) { // if key is a field of tt_address
									if ($this->secParamsSecondLevel[$key2] == 'int') $piVars[$key][$key2] = intval($value2); // show: should be an integer
									elseif ($this->secParamsSecondLevel[$key2] == 'text') { // if text
										$piVars[$key][$key2] = strip_tags(trim($value2)); // strip_tags removes html and php code
										$piVars[$key][$key2] = addslashes($piVars[$key][$key2]); // use addslashes if escape_string is not available
									}
									elseif (strpos($this->secParamsSecondLevel[$key2], '"') !== false) { // if a quote exists
										$piVars[$key][$key2] = str_replace('"','',$this->secParamsSecondLevel[$key2]);
									}
									else { // not defined
										$piVars[$key][$key2] = strip_tags(trim($value2)); // strip_tags removes html and php code
										$piVars[$key][$key2] = addslashes($piVars[$key][$key2]); // use addslashes if escape_string is not available
										//unset($piVars[$key][$key2]); // delete
									}
								
								} else { // key is not a field of tt_address
									unset($piVars[$key][$key2]); // delete
								}
							}
						}
					} else {
						unset($piVars[$key]); // delete
					}
				}
			}
			return $piVars; // return cleaned piVars
		} 
	}
	
	
	// Function getAddressFields() returns tt_address fieldlist in an array
	function getAddressFields() {
		// config
		$notAllowedFields = array('uid','pid','tstamp','hidden','deleted'); // fields which are not allowed to show
		$fieldarray = array(); // init
		
		// query
		$res = mysql_query('SHOW COLUMNS FROM tt_address'); // mysql query
		if ($res) { // If there is a result
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every result
				if($row['Field'] && !in_array($row['Field'], $notAllowedFields)) {
					$fieldarray[] = $row['Field']; // add fieldname to array
				}
			}
			if (!empty($fieldarray)) return $fieldarray;
		}
	}
	
	
	// Function pageBrowser() enables pagebrowser in listview
	function pageBrowser($num = 1000, $numf = 10, $a = 0, $b = 10) {
		$n = $a + 1;
		$m = $a + $numf;

		$content = '<br><br>'.$n .' '.$this->LANG->getLL('pagebrowser_upto','up to').' '.$m.' '.$this->LANG->getLL('pagebrowser_within', 'within').' '.$num.'<br><br>';
		$pointer = 0;

		for($x=0; $x < ceil($num / $b); $x++) {
			$y = $x + 1;
			if($pointer == $_GET['pointer']) {
				$page = '<strong>'.$this->LANG->getLL('pagebrowser_page', 'page').' '.$y.'</strong>';
			}
			else {
				$page = $this->LANG->getLL('pagebrowser_page', 'page').' '.$y;
			}
			$content .= '<a href="index.php?id='.$this->pid.'&pointer='.$pointer. (isset($_GET['startdate']) ? '&startdate='.$this->startdate : '') . (isset($_GET['enddate']) ? '&enddate='.$this->enddate : '') .'">'.$page.'</a> :: ';
			$pointer = $pointer + $b;
		};
		$content = substr($content,0,-4); // delete last ::
		return $content;
	}
	
	
	// Function addFilterParams returns params from current setted piVars (like &tx_wtdirectory_pi1[filter][name]=x&tx_wt...)
	function addFilterParams($piVars) {
		if (isset($piVars['filter']) && is_array($piVars['filter'])) { // if filter piVars set
			$content = ''; // init
			foreach ($piVars['filter'] as $key => $value) { // one loop for every filter
				$content .= '&'.$this->prefixId.'[filter]['.$key.']='.$value;
			}
			if (!empty($content)) return $content;
		}
	}
	
	
	// Function marker2value() replaces ###WTDIRECTORY_TTADDRESS_NAME### with its value from database
	function marker2value($string, $row) {
		$this->row = $row; // database array
		
		$string = preg_replace_callback ( // Automaticly replace ###UID55### with value from session to use markers in query strings
			'#\#\#\#WTDIRECTORY_TTADDRESS_(.*)\#\#\##Uis', // regulare expression
			array($this,'replaceIt'), // open function
			$string // current string
		);
	
		return $string;
	}
	
	
	// Function replace is used for the callback function to replace ###WTDIRECTORY_TTADDRESS_NAME## with value
	function replaceIt($field) {
		if (isset($this->row[strtolower($field[1])])) {
			return $this->row[strtolower($field[1])]; // return name (e.g.)
		}
	}
	
	function relationValues ( $conf, $row, $value, $uid = false )
	{
		$string					= '';

		if ( ! is_array( $row ) )
		{
			$temp				= array();
			$temp[ $row ]		= $value;
			$temp[ 'uid' ]		= $uid;
			$value				= $row;
			$row				= $temp;
			unset( $temp );
		}

		// MLC 20080421
		// cbdining addon handling
		if ( ! in_array( $value, $this->cbDining ) )
		{
			$string = ($conf['enable.']['autoChange'] ? $this->linker($row[$value]) : $row[$value]); // value
		}
		else
		{
			$shortName		= array_pop( explode( '_', $value ) );
			$lookupValue	= $row[$value];
			$result		= $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
							"{$value}.{$shortName}"
							, 'tt_address'
							, 'tt_address_' . $value . '_mm'
							, $value
							, "AND ( tt_address.uid = '{$row['ttaddress_uid']}'
							OR tt_address.uid = '{$row['uid']}' )"
						);

			$newValues	= array();
			while ( $result && $vData =
				$GLOBALS['TYPO3_DB']->sql_fetch_row( $result ) )
			{
				$newValue	= array_pop( $vData );
				$newValues[]	= $conf['enable.']['autoChange']
					? $this->linker($newValue)
					: $newValue; // value
			}

			$string = implode( ', ', $newValues );
		}

		return $string;
	}
	
	function dropdownValues ( $conf, $row, $value )
	{
		$string					= '';

		// cbdining addon handling
		if ( ! in_array( $value, $this->cbDining ) )
		{
			$string = isset( $row[$value] ) ? $row[$value] : ''; // value
			$string = <<<EOD
				<input type="text"
					name="tx_wtdirectory_pi1[filter][{$value}]"
					id="{$value}"
					class="wtdirectory_filter_text wtdirectory_filter_text_{$value}"
					value="{$string}" />
EOD;
		}

		else
		{
			$shortName		= array_pop( explode( '_', $value ) );
			$lookupValue	= $row[$value];
			$result		= $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							"uid, {$shortName}"
							, $value
							, 'hidden = 0 AND deleted = 0'
							, "{$shortName} ASC"
						);

			$newValues			= array();
			$newValues[]		= '<option value="">'
									. 'No preference'
									. '</option>';
			while ( $result && $vData =
				$GLOBALS['TYPO3_DB']->sql_fetch_assoc( $result ) )
			{
				$selected		= ( isset( $row[$value] )
									&& $row[$value] == $vData['uid'] )
									? 'selected="selected"'
									: '';

				$newValues[]	= '<option value="' . $vData['uid'] . '" '
									. $selected . '>'
									. $vData[$shortName]
									. '</option>';
			}

			$string = <<<EOD
				<select type="text"
					name="tx_wtdirectory_pi1[filter][{$value}]"
					id="{$value}"
					class="wtdirectory_filter_text wtdirectory_filter_text_{$value}">
EOD;
			$string .= implode( "\n", $newValues ) . '</select>';
		}

		return $string;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php']);
}

?>
