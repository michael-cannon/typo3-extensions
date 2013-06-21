// @author Jaspreet Singh
// $Id: clearOptions.js,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $

/**
Clears the options of all the elements on the first form in the document.  (I.e., unselects all options, 
doesn't just reset the options--which would set the options to their original
selected or unselected states.)

How to include:
<script type="text/javascript" src="clearOptions.js"></script>

How to call:
<input type="button" value="Clear Options" onclick="clearOptions()">

*/
function clearOptions() {
	var form=document.forms[0];
    clearOptions2(form);
}

/**
Clears the options of all the elements on the passed form.  (I.e., unselects all options, 
doesn't just reset the options--which would set the options to their original
selected or unselected states.)
@return void
*/
function clearOptions2(form) {
    var element;

        for (var i=0; i<form.elements.length; i++) {
		element = form.elements[i];
		switch(element.type) {
			case 'checkbox':
				 element.checked = false;
				break;
			case 'select-one':
			case 'select-multiple':
				element.selectedIndex = -1;
				break;
			case "text":    //fallthrough
			case "password": //fallthrough
			case "textarea":
				element.value = "";
			default:
				//nothing
		}
	}
	//return output;
}


