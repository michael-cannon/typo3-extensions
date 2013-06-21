script_ended = 0;
function jumpToUrl(URL)	{
	document.location = URL;
}


function debug(obj, vals) {
	cnt = 0;
	out = "";
	lines = 0;
	for (prop in obj) {
		out = out + prop;
		if (vals) out = out + " : " + obj[prop];
		if (!(++cnt%(vals?2:6))) {
			out = out + "\n";
			lines++;
		} else out = out + "       ";
		if (lines>(vals?4:10)) {
			alert(out);
			out = "";
			lines = 0;
		}
	}
	alert(out);
}

function getSelectVal(select)	{
	return select.options[select.selectedIndex].value;
}

function removeAllItems(select)	{
	while (select.length)	{
		select.remove(0);
	}
}

function addItem(select, text, value)	{
	var entry = document.createElement("option");
	entry.text = text;
	entry.value = value;
	select.options.add(entry, select.length);
	return true;
}

function updateTOselect(selectId)	{
	var ds = document.getElementById("select_"+selectId+"_ds");
	var to = document.getElementById("select_"+selectId+"_to");
	if ((sel_ds = parseInt(getSelectVal(ds))))	{
		removeAllItems(to);
		addItem(to, please_select, "0");
		for (val in selectVals[selectId][sel_ds])	{
			addItem(to, selectVals[selectId][sel_ds][val], val);
		}
	} else {
		removeAllItems(to);
		addItem(to, select_ds_first, "0");
	}
}
function getMainDTM()	{
	for (var name in DTM_array)	{
		parts = name.split("-");
		if (parts[0]=="DTM")	{
			return name;
		}
	}
}

function updateValid(selectId, tab)	{
	var to = document.getElementById("select_"+selectId+"_to");
	sel_to = getSelectVal(to);
	if (sel_to>0)	{
		validTO[tab][selectId] = 1;
	} else {
		validTO[tab][selectId] = 0;
	}
		// Check over this TAB
	ok = 1;
	for (var selId in validTO[tab])	{
		if (!validTO[tab][selId])	{
			ok = 0;
			break;
		}
	}
	dtm = getMainDTM();
	if (ok)	{
		setMenuImg(dtm+"-"+(tab+1)+"-MENU", "icon_ok.gif");
		validTAB[tab] = 1;
	} else {
		validTAB[tab] = 0;
		setMenuImg(dtm+"-"+(tab+1)+"-MENU", "icon_warning.gif");
	}
		// Check over all TABs
	ok = 1;
	for (var tabId in validTAB)	{
		if (!validTAB[tabId])	{
			ok = 0;
			break;
		}
	}
	if (ok)	{
		validALL = 1;
		var submit = document.getElementById("submit");
		submit.style.display = "block";
	} else {
		validALL = 0;
		var submit = document.getElementById("submit");
		submit.style.display = "none";
	}
}

function setMenuImg(id, file)	{
	var menu = document.getElementById(id);
	html = menu.innerHTML;
	repl = html.replace(/\/typo3\/gfx\/([^"]*)\"/, '/typo3/gfx/'+file+'"');
	menu.innerHTML = repl;
}


