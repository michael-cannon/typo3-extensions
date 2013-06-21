

<script language="JavaScript" type="text/javascript">
/*<![CDATA[*/
<!--
// The following code is taken more or less verbatim from the javascript used in the 
// PHPbb discussion board, including their method of applying bbcode (renamed fcode here) 
// to messages.

// bbCode control by
// subBlue design
// www.subBlue.com

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

// Helpline messages
b_help = "{b_help}";
i_help = "{i_help}";
u_help = "{u_help}";
q_help = "{q_help}";
c_help = "{c_help}";
p_help = "{p_help}";
w_help = "{w_help}";
a_help = "{a_help}";

// Define the bbCode tags
fcode = new Array();
ftags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[img]','[/img]','[url]','[/url]','[color]','[/color]');
imageTag = false;

// Shows the help messages in the helpline window
function helpline(help) {
	document.getElementById("post").helpbox.value = eval(help + "_help");
}


// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}

function emoticon(text) {
	var txtarea = document.getElementById("post").text;
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function fcstyle(fcnumber) {
	var txtarea = document.getElementById("post").text;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	fclast = 0;

	if (fcnumber == -1) { // Close all open tags & default button names
		while (fcode[0]) {
			butnumber = arraypop(fcode) - 1;
			txtarea.value += ftags[butnumber + 1];
			buttext = eval('document.getElementById("post").addfcode' + butnumber + '.value');
			eval('document.getElementById("post").addfcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			// Add tags around selection
			document.selection.createRange().text = ftags[fcnumber] + theSelection + ftags[fcnumber+1];
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, ftags[fcnumber], ftags[fcnumber+1]);
		return;
	}

	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < fcode.length; i++) {
		if (fcode[i] == fcnumber+1) {
			fclast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (fcode[fclast]) {
				butnumber = arraypop(fcode) - 1;
				txtarea.value += ftags[butnumber + 1];
				buttext = eval('document.getElementById("post").addfcode' + butnumber + '.value');
				eval('document.getElementById("post").addfcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags

		if (imageTag && (fcnumber != 14)) {		// Close image tag before adding another
			txtarea.value += ftags[15];
			lastValue = arraypop(fcode) - 1;	// Remove the close image tag from the list
			document.getElementById("post").addfcode14.value = "Img";	// Return button back to normal state
			imageTag = false;
		}

		// Open tag
		txtarea.value += ftags[fcnumber];
		if ((fcnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(fcode,fcnumber+1);
		eval('document.getElementById("post").addfcode'+fcnumber+'.value += "*"');
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

function checkForm() {

	formErrors = false;
  if (!document.getElementById("post").cancel) {
		if (document.getElementById("post").text.value.length < 2) {
			formErrors = "{missing_message}";
		}
		if (document.getElementById("post").subject.value.length < 2) {
			formErrors = "{missing_subject}";
		}
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		fcstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}
//-->
/*]]>*/
</script>
