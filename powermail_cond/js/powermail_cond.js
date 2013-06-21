/***************************************************************
*  Copyright notice
*
*  (c) 2009 Alexander Kellner <alexander.kellner@einpraegsam.net>
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
 * Function pmcond_main() shows or hides elements if a value is equal to or is set etc...
 *
 *	// EXPLANATION
 *	// valueFromField: Do something if this value is set (e.g.: 'value')
 * 	// targetIDs: ID of target element (field or fieldset) (e.g.: 'uid42')
 *	// baseID: ID of own field element (e.g.: 'uid41')
 *	// targetFunction: What should happen (e.g.: 'hide')
 *	// 		possibilities: hide, show, setToMandatory
 *	// targetCondition: When should be done anything (e.g.: 'ifValue')
 *	// 		possibilities: ifValue, ifNotValue, ifSet, ifNotSet
 * 
 */
function pmcond_main(valueFromField, targetID, baseID, targetFunction, targetCondition) {
	// CONFIG
	var target = new Array(); // init array
	var base = new Array(); // init array
	var doit = new Array(); // init array
	var functionName = 'pmcond_main'; // name of current js function
	var array_ValueFromField = valueFromField.split(","); // explode value at ,
	var array_TargetID = targetID.split(","); // explode ids of target at ,
	var array_baseID = baseID.split(","); // explode ids of target at ,
	var array_targetFunction = targetFunction.split(","); // explode function name at ,
	var array_targetCondition = targetCondition.split(","); // explode conditions at ,
	var mandatoryTag = '<span class="powermail_mandatory">*</span>'; // html code for mandatory labels
	
	// LET'S GO
	// check if length of all given variables are the same
	if (array_ValueFromField.length != array_TargetID.length || array_ValueFromField.length != array_baseID.length || array_ValueFromField.length != array_targetFunction.length || array_ValueFromField.length != array_targetCondition.length) { // If array length is not equal
		alert ('Error in Function ' + functionName + ': Length of array is not equal (' + array_ValueFromField.length + '/' + array_TargetID.length + '/' + array_baseID.length + '/' + array_targetFunction.length + '/' + array_targetCondition.length + ')!'); // errormessage
		return true; // stop function
	}

	// start loop
	for (i=0; i < array_ValueFromField.length; i++) { // one loop for every value
		var target = document.getElementById(array_TargetID[i]); // current target element
		var base = document.getElementById(array_baseID[i]); // current base element
		var current_target = array_TargetID[i]; // current targetid
		
		// check if target and base elements are existing
		if (!target || !base) {
			alert ('Error in Function ' + functionName + ': Target or base ID don\'t exists');
			//alert ('Error in Function ' + functionName + ': Target or base ID don\'t exists (' + getElementById(arrayTargetID[i]) + '/' + getElementById(baseID) + ')');
			return true;
		}

		// 1. get value of basefield
		ty = base.type.toLowerCase();
		switch(ty) {
			case 'text': // if current field is a text field
			case 'hidden': // if current field is a text field
			case 'radio': // if current field is a radiobutton
				var base_value = base.value;
				break;
			case 'checkbox': // if current field is a select field
				if (base.checked == true) { // if checkbox is checked
					var base_value = base.value; // value of checkbox
				} else {
					var base_value = ''; // clear value of checkbox if not checked
				}
				break;
			case 'select-one': // if current field is a select field
				var base_value = base.options[base.selectedIndex].value;
				break;
			default: // default
				alert ('Error in Function ' + functionName + ': base field (' + ty + ') is not allowed');
				break;
		}
		
		// 2. condition part: set doit to 1 or 0
		if (doit[current_target] != 1) { // search for something to do only if there is no calltoaction in one of the last loops
			switch(array_targetCondition[i]) {
				case 'ifValue': // if current value is an especially value
					//alert (current_target+'x');
					if (base_value == array_ValueFromField[i]) {
						doit[current_target] = 1; // do something
					} else {
						doit[current_target] = 0; // do nothing
					}
					break;
				case 'ifNotValue': // if current value is not an especially value
					if (base_value != array_ValueFromField[i]) {
						doit[current_target] = 1; // do something
					} else {
						doit[current_target] = 0; // do nothing
					}
					break;
				case 'ifSet': // if current value is set
					if (base_value != '') {
						doit[current_target] = 1; // do something
					} else {
						doit[current_target] = 0; // do nothing
					}
					break;
				case 'ifNotSet': // if current value is not set
					if (base_value == '') {
						doit[current_target] = 1; // do something
					} else {
						doit[current_target] = 0; // do nothing
					}
					break;
				default: // default
					alert ('Error in Function ' + functionName + ': condition (' + array_targetCondition[i] + ') is not allowed');
					doit[current_target] = 0; // do nothing
			}
		}
		
		// MH: get the object of the target element
		var new_target = current_target.split("_");
		var new_targetID = document.getElementById(new_target[1]);

		// 3. do something if doit == 1
		switch(array_targetFunction[i]) {
			case 'show': // show target
				if (doit[current_target]) { // of conditions is 1
					target.style.display = 'block'; // show element
					new_targetID.name = 'tx_powermail_pi1[' + new_target[1] + ']'; // MH: change name back to normal-state
				} else {
					target.style.display = 'none'; // hide element
					new_targetID.name = 'tx_powermail_pi1[' + new_target[1] + '][nosend]'; // MH: change name to nosend-state
				}
				break;
			case 'hide': // hide target
				if (doit[current_target]) { // of conditions is 1
					target.style.display = 'none'; // show element
					new_targetID.name = 'tx_powermail_pi1[' + new_target[1] + '][nosend]'; // MH: change name to nosend-state
				} else {
					target.style.display = 'block'; // hide element
					new_targetID.name = 'tx_powermail_pi1[' + new_target[1] + ']'; // MH: change name back to normal-state
				}
				break;
			case 'setToMandatory': // set targetfield to mandatory
				if (doit[current_target]) { // of conditions is 1
					
					// 1. add required to class name
					target.className = 'required ' + target.className; // write additional classname
					
					// 2. add * to label
					var div = document.getElementById('powermaildiv_' + array_TargetID[i]); // Get parent div container
					var fEles = div.getElementsByTagName('label'); // get label tag in DIV
					if (div && fEles && fEles[0] != null) fEles[0].innerHTML += mandatoryTag; // rewrite label
					else alert('Label tag in DIV with id "' + 'powermaildiv_' + array_TargetID[i] +  '" is missing!'); // error if label is missing
				
				} else {
					// 1. manipulate class of input field
					target.className = pmcond_str_replace('required', '', target.className); // remove "required " from classname
					target.className = pmcond_str_replace('validation-failed', '', target.className); // remove "required " from classname
					target.className = pmcond_trim(target.className); // trim for the class
					
					// 2. remove div containter "This is a required field"
					if (document.getElementById('advice-required-' + array_TargetID[i]) != undefined) {
						document.getElementById('advice-required-' + array_TargetID[i]).style.display = 'none'; // change to "display: none;"
						//var sp = document.getElementById('advice-required-' + array_TargetID[i]);
						//sp.parentNode.removeChild(sp); // remove whole div
					}
					
					// 3. remove * from label
					var div = document.getElementById('powermaildiv_' + array_TargetID[i]); // Get parent div container
					var fEles = div.getElementsByTagName('label'); // get label tag in DIV
					if (div && fEles && fEles[0] != null) fEles[0].innerHTML = pmcond_str_replace('*', '', pmcond_stripTags(fEles[0].innerHTML)); // rewrite label
					else alert('Label tag in DIV with id "' + 'powermaildiv_' + array_TargetID[i] +  '" is missing!'); // error if label is missing
				}
				break;
			default: // default
				alert ('Error in Function ' + functionName + ': function (' + array_targetFunction[i] + ') is not allowed');
		}

	}
	return true; // after loop	
	
}


// Function pmcond_str_replace() works like the PHP function str_replace()
function pmcond_str_replace(search, replace, string) {
    return string.split(search).join(replace);
}


// Function pmcond_trim() works like the PHP function trim()
function pmcond_trim(string) {
	return string.replace (/^\s+/, '').replace (/\s+$/, '');
}

// Function pmcond_stripTags() works like the PHP function stripTags()
function pmcond_stripTags(string) {
	var matchTag = /<(?:.|\s)*?>/g;
	return string.replace(matchTag, "");
}