// Declaring valid date character, minimum year and maximum year
var dtCh= "-";
var minYear=1990;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(dtStr,str){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strMonth=dtStr.substring(0,pos1)
	var strDay=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert("The date format should be : mm-dd-yyyy" + " in " + str)
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert("Please enter a valid month"+" in "+str)
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert("Please enter a valid day"+" in "+str)
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear+" in "+str)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert("Please enter a valid date"+" in "+str)
		return false
	}
return true
}

function ValidateForm(obj) {
	var dt_from = obj.from_date;
   var dt_to = obj.to_date;
   
	if (isDate(dt_from.value,"from date") == false || isDate(dt_to.value,"to date") == false){
		return false
	}
   if (!checkDates())
      return false;
    return true
 }

function checkDates() {
   var str1  = document.getElementById("from_date").value;
   var str2  = document.getElementById("to_date").value;
   if (str1 == "") {
      alert("empty from date field");
      return false;
   }

   if (str2 == "") {
      alert("empty to date field");
      return false;
   }
   
   var regexpr = /^\d\d-\d\d-\d\d\d\d$/;
   if (!regexpr.test(str1)) {
      alert("Wrong from date format");
      return false;
   }

   if (!regexpr.test(str2)) {
      alert("Wrong to date format");
      return false;
   }
   
   var mon1   = parseInt(str1.substring(0,2),10);
   var dt1  = parseInt(str1.substring(3,5),10);
   var yr1   = parseInt(str1.substring(6,10),10);
   var mon2   = parseInt(str2.substring(0,2),10);
   var dt2  = parseInt(str2.substring(3,5),10);
   var yr2   = parseInt(str2.substring(6,10),10);
   var date1 = new Date(yr1, mon1, dt1);
   var date2 = new Date(yr2, mon2, dt2);
   if (date2 < date1) {
      alert("To date cannot be greater than from date");
      return false;
   }
   return true;
}