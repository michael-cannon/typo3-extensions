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


class tx_powermail_cond_fields extends tslib_pibase {

	var $prefixId = 'tx_powermailcond_pi1'; // Prefix
	var $scriptRelPath = 'lib/class.tx_powermailcond_fields.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'powermail_cond';	// The extension key.
	var $eventHandler = 'onchange'; // eventhandler
	var $JSfunctionname = 'pmcond_main'; // function name of JavaScript
	
	
	// Function PM_FieldWrapMarkerHookInner() for manipulation of inner parts of fields (for checkboxes, etc...)
	function PM_FieldWrapMarkerHookInner($uid, $xml, $type, $title, &$markerArray, $piVarsFromSession, $obj) {
		// config
		$this->uid = $uid;
		$this->markerArray = &$markerArray;
		$this->markerArray['###JS_INNER###'] = '';
		$this->type = $type;
		
		
		// Let's go
		// Base Field
		if ($this->activateConditionsBase($uid)) { // Check if there are conditions on a base field to make some changes (adding eventhandler)
			$this->manipulateFieldBase($uid, 'inner'); // start function to add an eventhandler
		}
	}

	// Function PM_FieldWrapMarkerHook() to manipulate content from powermail
	function PM_FieldWrapMarkerHook($uid, $xml, $type, $title, &$markerArray, $piVarsFromSession, $obj) {
		// config
		$this->uid = $uid;
		$this->markerArray = &$markerArray;
		$this->type = $type;
		$this->addJS(); // add javascript to header
		$this->manualCode($uid); // add manual html code
		
		
		// Let's go
		// Target Field
		if ($this->activateConditionsTarget($uid)) { // Check if there are conditions on a target field to make some changes (disable, etc...)
			$this->manipulateFieldTarget($uid, $piVarsFromSession); // start function to manipulate fields (unhide, deactivate: <input /> => <input style="display: none;" />)
		}
		
		// Base Field
		if ($this->activateConditionsBase($uid)) { // Check if there are conditions on a base field to make some changes (adding eventhandler)
			$this->manipulateFieldBase($uid); // start function to add an eventhandler
		}
	}
	
	
	// Function manipulateFieldBase() to add eventhandler
	function manipulateFieldBase($uid, $mode = '') {
		// config
		$fieldname = $fieldsetname = $value = ''; $i=0; 
		$preSettings = array('valueFromField' => '', 'targetID' => '', 'baseID' => '', 'targetFunction' => '', 'targetCondition' => '');
		
		// Let's go
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // Get all rules to current field
			'tx_powermailcond_rules.uid ruleuid, tx_powermailcond_rules.ops, tx_powermailcond_rules.condstring, tx_powermailcond_rules.actions, tx_powermail_fields.uid, tx_powermailcond_rules.fieldname, tx_powermailcond_rules.fieldsetname', // ops = equal, not equal, is set, not set // condstring = anyvalue // actions = hide, unhide, deactivate, activate
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermail_fields.uid = ' . $uid . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules') . tslib_cObj::enableFields('tx_powermail_fields'),
			$groupBy = 'tx_powermailcond_rules.uid',
			$orderBy = '',
			$limit = ''
		);
		if ($res) { // If there is a result
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every rule to current field
				
				if ($row['ops'] == 0) $row['ops'] = 'ifValue'; // equal to
				elseif ($row['ops'] == 1) $row['ops'] = 'ifNotValue'; // not equal to
				elseif ($row['ops'] == 8) $row['ops'] = 'ifSet'; // if set
				elseif ($row['ops'] == 9) $row['ops'] = 'ifNotSet'; // if not set
				else $row['ops'] = '';
				$tempvaluearray = t3lib_div::trimExplode("\n", $row['condstring'], 1); // each line of current value in one array
				
				if ($row['ops'] == 'ifValue' || $row['ops'] == 'ifNotValue') { // only if it should be checked to a special value
					for ($i=0; $i < count($tempvaluearray); $i++) { // one loop for every line of current value
						
						// Value
						$preSettings['valueFromField'] .= str_replace(array('"', "'", ','), '', $tempvaluearray[$i]).','; // create value1,value2,value3,
						
						// Target ID
						if ($row['actions'] != 2) { // if mode is not set to mandatory (manipulate the DIV container)
							if ($row['fieldname']) $preSettings['targetID'] .= 'powermaildiv_uid'.$row['fieldname'].','; // id of target html element (DIV arround field)
							else $preSettings['targetID'] .= 'tx-powermail-pi1_fieldset_'.$row['fieldsetname'].','; // id of target html element (DIV arround fieldset)
						} else { // mode is set to mandatory (manipulate the field - not the DIV container)
							if ($row['fieldname']) $preSettings['targetID'] .= 'uid'.$row['fieldname'].','; // id of target html element (input)
						}
						
						// Base ID
						$preSettings['baseID'] .= 'uid'.$row['uid'].($mode == 'inner' && is_numeric($this->curline) ? '_'.$this->curline : '').','; // create own id uid33,uid33,uid33,
						
						// Condition
						if ($row['actions'] == 1) $preSettings['targetFunction'] .= 'show,'; // show // create functionlist hide,show,hide,
						elseif ($row['actions'] == 0) $preSettings['targetFunction'] .= 'hide,'; // hide
						elseif ($row['actions'] == 2) $preSettings['targetFunction'] .= 'setToMandatory,'; // setToMandatory
						$preSettings['targetCondition'] .= $row['ops'].','; // create conditionlist ifNotSet,ifSet,ifNotValue,
						
					}
				} else { // ifSet or ifNotSet
					
					// Value
					$preSettings['valueFromField'] .= ','; // create ,,,
					
					// Target ID
					if ($row['actions'] != 2) { // if mode is not set to mandatory (manipulate the DIV container)
						if ($row['fieldname']) $preSettings['targetID'] .= 'powermaildiv_uid'.$row['fieldname'].','; // id of target html element (DIV arround field)
						else $preSettings['targetID'] .= 'tx-powermail-pi1_fieldset_'.$row['fieldsetname'].','; // id of target html element (DIV arround fieldset)
					} else { // mode is set to mandatory (manipulate the field - not the DIV container)
						if ($row['fieldname']) $preSettings['targetID'] .= 'uid'.$row['fieldname'].','; // id of target html element (input)
						else die($this->extKey.' ERROR: No target field was chosen in current rule (uid '.$row['ruleuid'].')'); // write error if $row['fieldname'] is empty
					}
					
					// Base ID
					$preSettings['baseID'] .= 'uid'.$row['uid'].($mode == 'inner' && is_numeric($this->curline) ? '_'.$this->curline : '').','; // create own id uid33,uid33,uid33,
					
					// Condition
					if ($row['actions'] == 1) $preSettings['targetFunction'] .= 'show,'; // show
					elseif ($row['actions'] == 0) $preSettings['targetFunction'] .= 'hide,'; // hide
					elseif ($row['actions'] == 2) $preSettings['targetFunction'] .= 'setToMandatory,'; // setToMandatory
					$preSettings['targetCondition'] .= $row['ops'].','; // create conditionlist ifNotSet,ifSet,ifNotValue,
					
				}
				
			}
		}
		$preSettings = $this->deleteLastSign($preSettings); // delete last ,
		if ($mode == 'inner') $this->markerArray['###JS_INNER###'] .= ($this->type == 'check' && $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail_pi1.']['field.']['checkboxJS'] == 1 ? ' ' : 'onclick="').'return '.$this->JSfunctionname.'(\''.$preSettings['valueFromField'].'\', \''.$preSettings['targetID'].'\', \''.$preSettings['baseID'].'\', \''.$preSettings['targetFunction'].'\', \''.$preSettings['targetCondition'].'\');'.($this->type == 'check' && $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail_pi1.']['field.']['checkboxJS'] == 1 ? '' : '"').' '; // onclick=""
		else $this->markerArray['###JS###'] .= $this->eventHandler.'="return '.$this->JSfunctionname.'(\''.$preSettings['valueFromField'].'\', \''.$preSettings['targetID'].'\', \''.$preSettings['baseID'].'\', \''.$preSettings['targetFunction'].'\', \''.$preSettings['targetCondition'].'\');" ';
		
	}
	
	
	// Function manipulateFieldTarget() to add styles or other html code to prepare target fields
	function manipulateFieldTarget($uid, $piVarsFromSession) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_rules.actions, tx_powermail_fields.uid',
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermailcond_rules.fieldname = ' . $uid . ' AND tx_powermail_fields.uid > 0' . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules') . tslib_cObj::enableFields('tx_powermail_fields'),
			$groupBy = 'tx_powermailcond_rules.uid',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['uid'] > 0) { // if there is min. one field in backend which uses this condition
			if ($row['actions'] == 0) { // field should be deactivate - so activate before
				$this->markerArray['###DIVJS###'] .= ' style="display: block;"'; // add style to DIV container arround fields
			}
			elseif ($row['actions'] == 1) { // field should be unhide - so hide before
				$this->markerArray['###DIVJS###'] .= ' style="display: none;"'; // add style to DIV container arround fields
			}
		}
		
		// now check from sessions
		if (array_key_exists('nosend', (array) $piVarsFromSession['uid' . $uid])) { // field should be unhide - so hide before
			$this->markerArray['###DIVJS###'] = ' style="display: none;"'; // add style to DIV container arround fields
			$this->markerArray['###NAME###'] = ' name="tx_powermail_pi1[uid' . $uid . '][nosend]"';
		}
		
	}
	
	
	// Function activateConditionsBase() search if the current field is a base field (if than TRUE)
	function activateConditionsBase($uid) {
		// check if current field has min. 1 condition
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_conditions.uid, tx_powermailcond_conditions.line',
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermail_fields.uid = ' . $uid . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules') . tslib_cObj::enableFields('tx_powermail_fields'),
			$groupBy = 'tx_powermailcond_conditions.uid',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if ($row['uid'] > 0) { // if there is a condition
			switch ($this->type) {
				case 'check':
				case 'radio':
					$temp = t3lib_div::trimExplode('_', $this->markerArray['###ID###'], 1); // split on _
					$this->curline = intval($temp[1]); // number of line is second part of split
					
					if ($row['line'] > 0) { // basefield for only one line
						if ($this->curline == ($row['line']-1)) return true; // if current line is the line with the condition, return true
						else return false; // current line is not the line with the condition
					} else {
						return true; // return true for all lines
					}
					break;
				
				default: // normal fields like text and textarea
					return true; // return true
					break;
			}
		}
		return false; // return false
	}
	
	
	// Function activateConditionsTarget() search if the current field is a target field (if than TRUE)
	function activateConditionsTarget($uid) {
		// check if current field has min. 1 condition
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_conditions.uid',
			'tx_powermail_fields LEFT JOIN tx_powermailcond_conditions ON (tx_powermail_fields.tx_powermailcond_conditions = tx_powermailcond_conditions.uid) LEFT JOIN tx_powermailcond_rules ON (tx_powermailcond_conditions.uid = tx_powermailcond_rules.conditions)',
			$where_clause = 'tx_powermailcond_rules.fieldname = ' . $uid . ' AND tx_powermail_fields.tx_powermailcond_conditions > 0 ' . tslib_cObj::enableFields('tx_powermailcond_conditions') . tslib_cObj::enableFields('tx_powermailcond_rules') . tslib_cObj::enableFields('tx_powermail_fields'),
			$groupBy = 'tx_powermailcond_conditions.uid',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if ($row['uid'] > 0) return true;
		else return false;
	
	}
	
	
	// Function manualCode() adds manual html code for current field
	function manualCode($uid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_powermailcond_manualcode',
			'tx_powermail_fields',
			$where_clause = 'tx_powermail_fields.uid = ' . $uid,
			$groupBy = '',
			$orderBy = '',
			$limit = '1'
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if ($row['tx_powermailcond_manualcode']) $this->markerArray['###JS###'] .= $row['tx_powermailcond_manualcode'].' '; // add manual code
	}
	
	
	// Function addJS() simply adds javascript to the header
	function addJS() {
		if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailcond.']['js'] != '') { // if js path is set in constants
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = "\t" . '<script src="'.$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailcond.']['js'] . '" type="text/javascript"></script>' . "\n"; // include JS from constants
		} else { // no js path in constants found
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = "\t" . '<script src="'.t3lib_extMgm::siteRelPath($this->extKey) . 'js/powermail_cond.js' . '" type="text/javascript"></script>' . "\n"; // include default JS
		}
	}
	
	
	// Function deleteLastSign() deletes last sign of every value in the array
	function deleteLastSign($array) {
		if (count($array) > 0) { // if there are values
			foreach ($array as $key => $value) {
				$array[$key] = substr($value, 0, -1); // delete last sign
			}
		}
		return $array;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_fields.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/lib/class.tx_powermailcond_fields.php']);
}
?>