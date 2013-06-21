<!-- ###EDIT_INVENTORY_ITEM_PAGE_ONE### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSCALENDAR_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<script language="JavaScript" type="text/javascript" src="###RTE_LOCATION###"></script>
<script language="JavaScript" type="text/javascript">
<!--
initRTE("###RTE_IMAGES_LOCATION###", "###RTE_PATH###", "", false);
function submitForm(theform)
{
	updateRTE('###CONTENT###');
	return validateStandard(theform);
}


function validateStandard(theform){
	if(document.getElementById('tx_sponsorcontentscheduler_pi1[date]').value==''){
		alert('Please enter the date');
		document.getElementById('tx_sponsorcontentscheduler_pi1[date]').focus();
		return false;
	}
	return true;
	
}
//-->
</script>

<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return submitForm(this);">
<INPUT type="hidden" name="###SPONSOR_ID###" value="###SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_ID###" value="###ITEM_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_TYPE_ID###" value="###ITEM_TYPE_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<H2>Editing Package "###PACKAGE_TITLE###", ###ITEM_TYPE### "###TITLE_VALUE###"</H2>
<TABLE>
	<TR>
		<TD colspan="2">Please complete the following fields for your content<BR><BR></TD>
	</TR>
	<TR>
		<TD>Title</TD>
		<TD><INPUT type="text" name="###TITLE###" value="###TITLE_VALUE###" required="1" size="80"/></TD>
	</TR>
	<TR>
		<TD>Date</TD>
		<TD><INPUT type="text" name="###DATE###" id="###DATE###" value="###DATE_VALUE###" /><A href="javascript:NewCal('###DATE###', 'ddmmmyyyy', true, 24);">###IMG_CALENDAR###</A></TD>
	</TR>
	<TR>
		<TD>Author</TD>
		<TD>
			<INPUT type="text" name="###AUTHOR_ID###" value="###AUTHOR_NAME###"/>
		</TD>
	</TR>
	<TR>
		<TD colspan="2"><B>Content:</B><BR>
		<script language="JavaScript" type="text/javascript">
		<!--
			writeRichText('###CONTENT###', '###CONTENT_VALUE###', 520, 200, true, false);
		//-->
		</script>
		</TD>
	</TR>
	<TR>
		<TD colspan="2">
			###PACKAGE_INFO_HIDDEN###
			<INPUT type="submit" name="###SUBMIT_BACK###" value="Back" onClick="document.getElementById('###FORMACTION_NAME###').value='Page1Back';" />
			<INPUT type="submit" name="###SUBMIT_NEXT###" value="Next" onClick="document.getElementById('###FORMACTION_NAME###').value='Page1Next';" />
		</TD>
	</TR>
</TABLE>

</FORM>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_ONE### end -->
	



<!-- ###EDIT_INVENTORY_ITEM_PAGE_TWO### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return validateStandard(this);" enctype="multipart/form-data">
<INPUT type="hidden" name="###SPONSOR_ID###" value="###SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_ID###" value="###ITEM_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_TYPE_ID###" value="###ITEM_TYPE_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<H2>Editing Package "###PACKAGE_TITLE###", ###ITEM_TYPE### "###TITLE_VALUE###"</H2>
<TABLE>
	<TR>
		<TD colspan="2">Please complete the following fields for your content<BR><BR></TD>
	</TR>
	<TR>
		<TD><B>Categories</B><BR>Please associate categories with your content to help searchers find it.</TD>
		<TD><SELECT name="###ITEM_CATEGORIES###" size="6" multiple="yes" required="1" err="Please select atleast one category for your content" />###ITEM_CATEGORIES_VALUE###</SELECT></TD>
	</TR>
	
		###FILE_UPLOAD_PLACEHOLDER###
	
	<TR>
		<TD colspan="2">
			<INPUT type="submit" name="###SUBMIT_BACK###" value="Back" onClick="document.getElementById('###FORMACTION_NAME###').value='Page2Back';" />
			<INPUT type="submit" name="###SUBMIT_NEXT###" value="Next" onClick="document.getElementById('###FORMACTION_NAME###').value='Page2Next';" />
		</TD>
	</TR>
</TABLE>
###PACKAGE_INFO_HIDDEN###
</FORM>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_TWO### end -->
<!-- ###EDIT_INVENTORY_ITEM_PAGE_TWO_FILE_PART### begin -->
	<TR>
		<TD><B>Files</B><BR>Upload files associated with your content.</TD>
		<TD>
			<INPUT type="hidden" name="MAX_FILE_SIZE" value="###MAX_FILE_SIZE###" />
			<!-- TODO - we may want to handle this differently, so that increasing/decreaseing the number of file is easier -->
			###FILE1###
			###FILE2###
			###FILE3###
		</TD>
	</TR>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_TWO_FILE_PART### end -->


<!-- ###EDIT_INVENTORY_ITEM_PAGE_THREE### begin -->
<!-- This page is used only for roundtables -->
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" src="###JSCALENDAR_LOCATION###"></SCRIPT>
<script language="JavaScript" type="text/javascript" src="###RTE_LOCATION###"></script>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
initRTE("###RTE_IMAGES_LOCATION###", "", "", false);
function submitForm(theform)
{
	updateRTE('###BEFORE_CONTENT###');
	updateRTE('###AFTER_CONTENT###');
	return validateStandard(theform);
}
function validateStandard(theform){
	if(document.getElementById('tx_sponsorcontentscheduler_pi1[start_date]').value==''){
		alert('Please enter the Event Start Date and Time');
		document.getElementById('tx_sponsorcontentscheduler_pi1[start_date]').focus();
		return false;
	}
	if(document.getElementById('tx_sponsorcontentscheduler_pi1[end_date]').value==''){
		alert('Please enter the Event End Date and Time');
		document.getElementById('tx_sponsorcontentscheduler_pi1[end_date]').focus();
		return false;
	}
	return true;
	
}
function updateEndDate(){
	exDateTime = document.getElementById('tx_sponsorcontentscheduler_pi1[start_date]').value;
	Sp1=exDateTime.indexOf(DateSeparator,0);
	Sp2=exDateTime.indexOf(DateSeparator,(parseInt(Sp1)+1));
	Sp3=exDateTime.indexOf(DateSeparator,(parseInt(Sp2)+1));
	strMonth=exDateTime.substring(Sp1+1,Sp2);
	strDate=exDateTime.substring(0,Sp1);
	strYear = exDateTime.substring(Sp2+1,Sp2+5);
	tSp1=exDateTime.indexOf(":",0)
	tSp2=exDateTime.indexOf(":",(parseInt(tSp1)+1));
	strHour=exDateTime.substring(tSp1,(tSp1)-2);
	strMinute=exDateTime.substring(tSp1+1,tSp2);
	strSecond=exDateTime.substring(tSp2+1,tSp2+3);
	dtStr = new Date(GetMonthIndex(strMonth)+"/"+strDate+"/"+strYear+" "+strHour+":"+strMinute+":"+strSecond);
	dtStr.setHours(dtStr.getHours() + 2);
	strMonth = (dtStr.getMinutes()<10)?'0'+dtStr.getMinutes():dtStr.getMinutes();
	newEndDate = dtStr.getDate()+'-'+MonthName[dtStr.getMonth()+1].substring(0,3)+'-'+dtStr.getFullYear()+' '+dtStr.getHours()+':'+strMonth+':'+dtStr.getSeconds();
	document.getElementById('tx_sponsorcontentscheduler_pi1[end_date]').value=newEndDate;
	
}
//-->
</SCRIPT>
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return submitForm(this);" >
<INPUT type="hidden" name="###SPONSOR_ID###" value="###SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_ID###" value="###ITEM_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_TYPE_ID###" value="###ITEM_TYPE_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<H2>Editing Package "###PACKAGE_TITLE###", ###ITEM_TYPE### "###TITLE###"</H2>
<TABLE>
	<TR>
		<TD colspan="2">Please select the dates and if you'd like to "offer" something for roundtable registration<BR><BR></TD>
	</TR>
	<TR>
		<TD><B>Event Start Date and Time</B></TD>
		<TD><INPUT type="text" name="###START_DATE###" id="###START_DATE###" value="###START_DATE_VALUE###" onFocus="updateEndDate();"/><A href="javascript:NewCal('###START_DATE###', 'ddmmmyyyy', true, 24);document.getElementById('tx_sponsorcontentscheduler_pi1[start_date]').focus();">Pick a date/time</A></TD>
	</TR>
	<TR>
		<TD><B>Event End Date and Time</B></TD>
		<TD><INPUT type="text" name="###END_DATE###" id="###END_DATE###" value="###END_DATE_VALUE###"/><A href="javascript:NewCal('###END_DATE###', 'ddmmmyyyy', true, 24);">Pick a date/time</A></TD>
	</TR>
	<TR>
		<TD colspan="2"><BR><BR><B>Special Offer</B><BR>Shown before and during registration<BR>
		<script language="JavaScript" type="text/javascript">
		<!--
			writeRichText('###BEFORE_CONTENT###', '###BEFORE_CONTENT_VALUE###', 520, 50, true, false);
		//-->
		</script>
		</TD>
	</TR>
	<TR>
		<TD colspan="2"><BR><BR><B>Special Offer Fulfillment</B><BR>Shown after registration<BR>
		<script language="JavaScript" type="text/javascript">
		<!--
			writeRichText('###AFTER_CONTENT###', '###AFTER_CONTENT_VALUE###', 520, 50, true, false);
		//-->
		</script>
		</TD>
	</TR>
	<TR>
		<TD colspan="2">
			<INPUT type="submit" name="###SUBMIT_BACK###" value="Back" onClick="document.getElementById('###FORMACTION_NAME###').value='Page3Back';" />
			<INPUT type="submit" name="###SUBMIT_NEXT###" value="Next" onClick="document.getElementById('###FORMACTION_NAME###').value='Page3Next';" />
		</TD>
	</TR>
</TABLE>
###PACKAGE_INFO_HIDDEN###
</FORM>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_THREE### end -->

	

<!-- ###EDIT_INVENTORY_ITEM_PAGE_FOUR### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
function submitForm(theform)
{
	return true;
}

function checkOptionList(theList,maxWeeks,selectedvalue)
{
	
	
	var theOptions=theList.options;
	var l=theOptions.length;
	var i=0;
	var s=0; // Number of selected weeks
	var d=0; // number of disabled weeks
	for(i=0; i<l; i++)
	{
		if(theOptions[i].selected)
		{
			s++;
		}
		if(theOptions[i].disabled)
		{
			d++;
		}
	}
	if(s>maxWeeks)
	{
		alert("Number of featured weeks selected should not exceed the permitted limit");
		theOptions[selectedvalue].selected=false;
		return false;
	}
	
}
// -->
</SCRIPT>
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return submitForm(this);" >
<INPUT type="hidden" name="###SPONSOR_ID###" value="###SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_ID###" value="###ITEM_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_TYPE_ID###" value="###ITEM_TYPE_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<INPUT type="hidden" name="###ITEM_TYPE###" value="###ITEM_TYPE_VALUE###" />
<H2>Editing Package "###PACKAGE_TITLE###", ###ITEM_TYPE_REAL_VALUE### "###TITLE_VALUE###"</H2>
<!-- ###EDIT_INVENTORY_ITEM_PAGEFOUR_DATA### begin -->
<p>Select feature dates for "###TITLE_VALUE###"</p>
<TABLE>
	<TR>
		<TD colspan="2">Please select the dates you'd like your content to be featured on BPMInstitute.org<BR><BR></TD>
	</TR>
	<TR>
		<TD valign="top">
			<B>Dates</B><BR>
			You may select ###FEATURED_WEEKS_MAX_NUMBER### of the displayed weeks.
		</TD>
		<TD>###FEATUREDLISTDISPLAY###</TD>
	</TR>
	<TR>
</TABLE>
<!-- ###EDIT_INVENTORY_ITEM_PAGEFOUR_DATA### ends -->
<TABLE>
		<INPUT type="submit" name="###SUBMIT_BACK###" value="Back" onClick="document.getElementById('###FORMACTION_NAME###').value='Page4Back';" />
			<INPUT type="submit" name="###SUBMIT_NEXT###" value="Preview Item" onClick="document.getElementById('###FORMACTION_NAME###').value='Page4Next';" />
		</TD>
	</TR>
</TABLE>
###PACKAGE_INFO_HIDDEN###
</FORM>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_FOUR### end -->

<!-- ###EDIT_INVENTORY_ITEM_PAGE_FOUR_WEEK_SELECTOR### begin -->
###SITENAME_FEATUREDWEEK###<BR>
<SELECT name="###FEATURE_WEEKS###" id="###FEATURE_WEEKS###" size="8" multiple="yes" OnClick="checkOptionList(this,###MAXNOSELECTION###,this.options.selectedIndex);">###FEATURE_WEEKS_LIST###</SELECT><br>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_FOUR_WEEK_SELECTOR### end -->



<!-- ###EDIT_INVENTORY_ITEM_PAGE_FIVE### begin -->
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return submitForm(this);" >
<INPUT type="hidden" name="###SPONSOR_ID###" value="###SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_ID###" value="###ITEM_ID_VALUE###" />
<INPUT type="hidden" name="###ITEM_TYPE_ID###" value="###ITEM_TYPE_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<H2>Editing Package "###PACKAGE_TITLE###", ###ITEM_TYPE### "###TITLE###"</H2>
<H2>Upcoming ###ITEM_TYPE###</H2>
<UL>
	<LI><B>Topic: </B>###TITLE###</LI>
	<LI><B>Date: </B>###DATE###</LI>
	<DIV style="display: ###DISPLAY###;"><LI><B>Start Date: </B>###START_DATE###</LI></DIV>
	<LI style="display: ###DISPLAY###;"><B>End Date: </B>###END_DATE###</LI>
	<LI><B>Sponsor: </B>###SPONSOR###</LI>
	<LI><B>Author: </B>###AUTHOR###</LI>
	<LI><B>Sales Leads Sent: </B>###LEADS###</LI>
</UL>

<H2>###ITEM_TYPE### Details</H2>
###CONTENT###

<DIV style="display: ###DISPLAY###;">
<H2>Special Offer</H2>
###BEFORE_CONTENT###

<H2>Special Offer Fulfilment</H2>
###AFTER_CONTENT###
</DIV>

<H2>Content Categories</H2>
###CATEGORIES###

<H2>Related Files</H2>
###RELATED_FILES###

<H2>Featured Weeks</H2>
###FEATURED_WEEKS###

<br><br>
<INPUT type="submit" name="###SUBMIT_BACK###" value="Back" onClick="document.getElementById('###FORMACTION_NAME###').value='Page5Back';" />
<INPUT type="submit" name="###SUBMIT_NEXT###" value="Back to Inventory" onClick="document.getElementById('###FORMACTION_NAME###').value='Page5Next';" />
###PACKAGE_INFO_HIDDEN###
</FORM>
<!-- ###EDIT_INVENTORY_ITEM_PAGE_FIVE### end -->




<!-- ###INVENTORY_LISTING_HEADER### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSCALENDAR_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function validate_field(){
		
		alert("inside");
	
	}
	function editItem(itemID)
	{
		var theform=document.getElementById("###FORM_NAME###");
		var formaction=document.getElementById("###FORMACTION_NAME###");
		var theID=document.getElementById("###ITEM_ID###");
		theID.value=itemID;
		formaction.value="Page1";
		theform.submit();
	}

	function sortBy(columnName)
	{
		var theform=document.getElementById("###FORM_NAME###");
		var formaction=document.getElementById("###FORMACTION_NAME###");
		var thecolumn=document.getElementById("###SORTBY###");
		thecolumn.value=columnName;
		formaction.value="SortBy";
		theform.submit();
	}
	
	function showFeaturedWeek(statusOnOff,FeaturedWeek,nosFetauredWeek,siteSelector)
	{
		var objRefsite = eval("document.getElementById('"+siteSelector+"')");
		var frmInfoStart="<form name='frmPostFeature'>";
		var frmInfoEnd="</form>";
		var submitButton="<input type=button value='Update' onclick='window.opener.updateParent(document.frmPostFeature);self.close();'>";
		var interfaceFeature="<h1> Select Featured Weeks</h1>";
		interfaceFeature+=frmInfoStart+"<table width='100%' border=0>";
		var flagShow=false;
		for(var j=0;j<objRefsite.options.length;j++)
		{
			var objRefSiteVal=objRefsite.options[j].value;
			var objRefSiteName=objRefsite.options[j].text;
			if(objRefsite.options[j].selected){
				
				var objRef = eval("document.getElementById('"+FeaturedWeek+"["+objRefSiteVal+"][]')");
				var objRefNos = eval("document.getElementById('"+nosFetauredWeek+"["+objRefSiteVal+"]')");
				var bar="<select name='weekInfo["+objRefSiteVal+"]' size='5' multiple>";
				for(var i=0;i<objRef.options.length;i++)
				{
					if(objRef.options[i].selected)
					{
						bar+="<option value='"+objRef.options[i].value+"' selected>"+objRef.options[i].text+"</option>";
					}else{
						bar+="<option value='"+objRef.options[i].value+"'>"+objRef.options[i].text+"</option>";
					}
				}
				bar+="</select>";
				var hidText="<input type=hidden name='featureName' value='"+FeaturedWeek+"["+objRefSiteVal+"][]'>";
				var hidTextNo="<input type=hidden name='featureNameNo' value='"+nosFetauredWeek+"["+objRefSiteVal+"]'>";
				bar+=hidText;
				bar+=hidTextNo;
				var txtNosFeatured="&raquo; No. of Featured weeks:<br/><input type=text size=2 name='featureNo["+objRefSiteVal+"]' value='"+objRefNos.value+"'>";
				interfaceFeature+="<tr><td colspan=2>&raquo; &nbsp;"+objRefSiteName+"</td></tr>";
				interfaceFeature+="<tr><td>"+bar+"</td><td>"+txtNosFeatured+"</td></tr>";
				flagShow=true;

			}
		}
		interfaceFeature+="<tr><td colspan=2 align=center>"+submitButton+"</tr></table>"+frmInfoEnd;
		if(flagShow){
			var newWin=window.open('','splashWin','width=350,height=250,top=200,left=200,resizable=yes,scrollbars=yes');
		}else{
			var newWin=window.open('','splashWin','width=450,height=150,top=200,left=200,resizable=yes,scrollbars=yes');
			interfaceFeature="<h1> Please select a site for Publishing the news</h1>";
			interfaceFeature+= "<p align='center'><input type='button' value='&nbsp;&nbsp;Close&nbsp;&nbsp;' onclick='self.close();'></p>";
		}
		newWin.document.write('<link href="fileadmin/bpminstitute.org/styles.css" rel="stylesheet" type="text/css">');
		newWin.document.write(interfaceFeature);
		
	}

	function updateParent(featureChild)
	{
		var objLen = featureChild.elements.length;
		var elementInfo='';
		for(var i=0;i<objLen;i++)
		{
			switch(featureChild.elements[i].type){
				case 'select-multiple':
					objFeatureSelect=featureChild.elements[i];
					
					break;
				case 'hidden':
					switch(featureChild.elements[i].name){
						case 'featureName':
							var valFeatureWeek=featureChild.elements[i].value;
							break;
						case 'featureNameNo':
							var valFeatureMaxWeek=featureChild.elements[i].value;break;
					}
					break;
				case 'text':
					var objRef = eval("document.getElementById('"+valFeatureWeek+"')");
					var objRefNos = eval("document.getElementById('"+valFeatureMaxWeek+"')");
					for(var j=0;j<objFeatureSelect.options.length;j++)
					{
						if(objFeatureSelect.options[j].selected)
						{
							objRef.options[j].selected=true;
						}else{
							objRef.options[j].selected=false;
						}
					}
					objRefNos.value=featureChild.elements[i].value;

					break;
			}
			
		}
		document.getElementById('###FORMACTION_NAME###').value='UpdateItemsFeatured';
		document.getElementById('###FORM_NAME###').submit();
		
		
	}
	function validateStandard(frm){
		if(frm.elements['tx_sponsorcontentscheduler_pi1[package_name]'].value==''){
			alert('Please enter a package name');
			return false;
		}
		if((frm.elements['tx_sponsorcontentscheduler_pi1[sponsor_user_id]'].value.length==0) || (frm.elements['tx_sponsorcontentscheduler_pi1[sponsor_user_id]'].value==null)) {
			alert('Please select a sponsor user');
			return false;
		}
	}
// -->

</SCRIPT>
<FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return validateStandard(this);">
<INPUT type="hidden" name="###HIDDEN_SPONSOR_ID###" value="###HIDDEN_SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" value="" id="###FORMACTION_NAME###" />
<INPUT type="hidden" name="###ITEM_ID###" value="" id="###ITEM_ID###" />
<INPUT type="hidden" name="###SORTBY###" value="" id="###SORTBY###" />
<H2>###BREADCRUMB###</H2>
<table width="100%"  border="0" cellpadding="5" cellspacing="0">
  <tr> 
    <td class="lnav">&nbsp;</td>
  </tr>
  <tr> 
    <td class="lnav"><FORM id=edit_inventory name=edit_inventory 
onsubmit="return validateStandard(this);" action=sales-login.html method=post>
        <table border="0">
          <tbody>
            <tr> 
              <td colspan="3">Sponsor Package Name </td>
              <td colspan="2"><input type="text" name="###PACKAGE_NAME###" value="###PACKAGE_NAME_VALUE###"></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td colspan="3">&nbsp;</td>
              <td colspan="2">&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td colspan="3">Assign to Sponsor User </td>
              <td colspan="2"><select name=###SPONSOR_USER_NAME###>
                  <option selected>--- Select a user ---</option>
		###SPONSOR_LIST###

                </select></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td colspan="2">&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
           
            <tr> 
              <td>&nbsp;</td>
              <td colspan="2">&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr bgcolor="#CCCCCC"> 
              
              <td colspan="2"><a href="javascript:void(0)" onClick="sortBy('pid');"><strong>Type</strong></a></td>
              <td><a href="javascript:void(0)" onClick="sortBy('hidden');"><strong>Live</strong></a></td>
              <td><strong>Select a site</strong></td>
              <td><a href="javascript:void(0)" onClick="sortBy('tx_sponsorcontentscheduler_max_featured_weeks');"><strong>Featured 
                Weeks</strong></a></td>
              <td><strong>Leads</strong></td>
              <td><strong>Send Lead Periods</strong></td>
            </tr>
<!-- ###INVENTORY_LISTING_HEADER### end -->

<!-- ###INVENTORY_LISTING_ROW### begin -->

<TR> 
              <TD colspan="7"><b>News Title:</b> <SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="editItem('###ITEM_ID_VALUE###');">###INVENTORY_ITEM_TITLE###</SPAN></TD>
              
</TR>
<TR> 
             
              <TD colspan="2"><SELECT name="###INVENTORY_ITEM_TYPE###">###INVENTORY_ITEM_TYPE_LIST###</SELECT></TD>
              <TD rowspan="2" align="center"><INPUT type="checkbox" name="###CHECK_ON_OFF###" ###CHECK_ON_OFF_VALUE### /></TD>
              <TD rowspan="2"><SELECT name=###SITEMAPPER### id=###SITEMAPPER### multiple size=3>
                  ###OPTIONSITEMAPPER###
                </SELECT>
				<div style="display:none;">###FETUREDWEEKS###</div>
			  </TD>
              <TD rowspan="2"><div align="center">
                  <a href="javascript:void(0);" onclick="javascript:showFeaturedWeek(true,'###FEATURE_WEEKS###','###INPUT_MAX_FEATURED_WEEKS###','###SITEMAPPER###');">Assign</a>

                </div></TD>
              <TD rowspan="2" align="center">
                <label><INPUT type="checkbox" name="###CHECK_LEADS_ON_OFF###" ###CHECK_LEADS_ON_OFF_VALUE### />&nbsp;enable</label><br/>
                ###LEADS_UNUSED###&nbsp;leads&nbsp;unused.<br/>
                ###LEADS_UNSENT###&nbsp;leads&nbsp;unsent.<br/>
                <label>Add&nbsp;unused&nbsp;leads:&nbsp;<input type="text" name="###LEADS_ADD_UNUSED###" size="2"/></label>
                <input type="hidden" name="###LEADS_CURRENT_UNUSED###" value="###LEADS_UNUSED###"/>
              </TD>
              <TD rowspan="2"><SELECT name="###LEAD_DATES###" id="###LEAD_DATES###" size="3" multiple="yes">###LEAD_DATES_LIST###</SELECT></TD>
</TR>
<TR> 
              <TD><img src="###IMAGEFILEPATH###pi1/images/date.gif" alt="Select Due Date" title="Select Due Date" width="20" height="14" border="0" onclick="javascript:NewCal('###PACKAGE_DUEDATE###', 'ddmmmyyyy', true, 24);" style="cursor:hand;"></TD>
              <TD><input name="###PACKAGE_DUEDATE###" type="text"  id="###PACKAGE_DUEDATE###" value="###PACKAGE_DUEDATEVALUE###" size="14"></TD>
</TR>
<TR> 
              <TD colspan="7">&nbsp;</TD>
              
</TR>

<!-- ###INVENTORY_LISTING_ROW### end -->


<!-- ###INVENTORY_PERMISSIBLE_LISTING_ROW### begin -->
<SELECT name="###FEATURE_WEEKS###" size="3" multiple="yes" id="###FEATURE_WEEKS###">###FEATURE_WEEKS_LIST###</SELECT><INPUT type="text" size="2" name="###INPUT_MAX_FEATURED_WEEKS###" id="###INPUT_MAX_FEATURED_WEEKS###" value="###INPUT_MAX_FEATURED_WEEKS_VALUE###" required="1" callback="validateNumberOfFeaturedWeeks" err="Permissible feature weeks should not be less than the maximum number allowed" />
<!-- ###INVENTORY_PERMISSIBLE_LISTING_ROW### end -->

<!-- ###INVENTORY_PACKAGE_ITEM### begin -->
<input type="hidden" name="###INVENTORY_PACKAGE_NAME###" id="###INVENTORY_PACKAGE_NAME###" value="###INVENTORY_PACKAGE_VALUE###" >
<!-- ###INVENTORY_PACKAGE_ITEM### end -->


<!-- ###INVENTORY_LISTING_HEADER_OLD### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSVAL_LOCATION###"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function editItem(itemID)
	{
		var theform=document.getElementById("###FORM_NAME###");
		var formaction=document.getElementById("###FORMACTION_NAME###");
		var theID=document.getElementById("###ITEM_ID###");
		theID.value=itemID;
		formaction.value="Page1";
		theform.submit();
	}

	function sortBy(columnName)
	{
		var theform=document.getElementById("###FORM_NAME###");
		var formaction=document.getElementById("###FORMACTION_NAME###");
		var thecolumn=document.getElementById("###SORTBY###");
		thecolumn.value=columnName;
		formaction.value="SortBy";
		theform.submit();
	}
	
	// -->
</SCRIPT>
<FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return validateStandard(this);">
<INPUT type="hidden" name="###HIDDEN_SPONSOR_ID###" value="###HIDDEN_SPONSOR_ID_VALUE###" />
<INPUT type="hidden" name="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" value="" id="###FORMACTION_NAME###" />
<INPUT type="hidden" name="###ITEM_ID###" value="" id="###ITEM_ID###" />
<INPUT type="hidden" name="###SORTBY###" value="" id="###SORTBY###" />
<H2>Editing inventory for "###SPONSOR_NAME###"</H2>
<TABLE>
	<TR>
		<TD><SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="sortBy('title');">Content Title & ID</SPAN></TD>
		<TD><SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="sortBy('pid');">Type</SPAN></TD>
		<TD><SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="sortBy('hidden');">On/Off</SPAN></TD>
		<TD>Permissible Feature Weeks</TD>
		<TD><SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="sortBy('tx_sponsorcontentscheduler_max_featured_weeks');">Featured Weeks</SPAN></TD>
		<TD>Leads On/off</TD>
		<TD>Send Lead Periods</TD>
	</TR>
<!-- ###INVENTORY_LISTING_HEADER_OLD### end -->




<!-- ###INVENTORY_LISTING_FIRST_ROW_OLD### begin -->
	<TR>
		<SCRIPT LANGUAGE="JavaScript">
		<!--
		function validateNumberOfFeaturedWeeks(id, name, value)
		{
			var theList=document.getElementById("###FEATURE_WEEKS###");
			var theOptions=theList.options;
			var l=theOptions.length;
			var i=0;
			var s=0; // Number of selected weeks
			for(i=0; i<l; i++)
			{
				if(theOptions[i].selected)
				{
					s++;
				}
			}
			if(s<value)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

			
		// -->
		</SCRIPT>
		<TD><INPUT type="submit" name="###SUBMIT_ADD_ITEM###" value="Add New Item" onClick="document.getElementById('###FORMACTION_NAME###').value='AddItem';" /></TD>
		<TD><SELECT name="###INVENTORY_ITEM_TYPE###">###INVENTORY_ITEM_TYPE_LIST###</SELECT></TD>
		<TD><INPUT type="checkbox" name="###CHECK_ON_OFF###" ###CHECK_ON_OFF_VALUE### /></TD>
		<TD><SELECT name="###FEATURE_WEEKS###" id="###FEATURE_WEEKS###" size="3" multiple="yes">###FEATURE_WEEKS_LIST###</SELECT></TD>
		<TD><INPUT type="text" size="2" name="###INPUT_MAX_FEATURED_WEEKS###" id="###INPUT_MAX_FEATURED_WEEKS###" value="###INPUT_MAX_FEATURED_WEEKS_VALUE###" required="1" callback="validateNumberOfFeaturedWeeks" err="Permissible feature weeks should not be less than the maximum number allowed" /></TD>
		<TD><INPUT type="checkbox" name="###CHECK_LEADS_ON_OFF###" ###CHECK_LEADS_ON_OFF_VALUE### /></TD>
		<TD><SELECT name="###LEAD_DATES###" size="3" multiple="yes">###LEAD_DATES_LIST###</SELECT></TD>
	</TR>
<!-- ###INVENTORY_LISTING_FIRST_ROW_OLD### end -->



<!-- ###INVENTORY_LISTING_ROW_OLD### begin -->
	<TR>
		<TD><SPAN style="text-decoration: underline; color: blue; cursor: pointer;" onClick="editItem('###ITEM_ID_VALUE###');">###INVENTORY_ITEM_TITLE###</SPAN></TD>
		<TD><SELECT name="###INVENTORY_ITEM_TYPE###">###INVENTORY_ITEM_TYPE_LIST###</SELECT></TD>
		<TD><INPUT type="checkbox" name="###CHECK_ON_OFF###" ###CHECK_ON_OFF_VALUE### /></TD>
		<TD><SELECT name="###FEATURE_WEEKS###" size="3" multiple="yes" >###FEATURE_WEEKS_LIST###</SELECT></TD>
		<TD><INPUT type="text" size="2" name="###INPUT_MAX_FEATURED_WEEKS###" value="###INPUT_MAX_FEATURED_WEEKS_VALUE###" /></TD>
		<TD><INPUT type="checkbox" name="###CHECK_LEADS_ON_OFF###" ###CHECK_LEADS_ON_OFF_VALUE### /></TD>
		<TD><SELECT name="###LEAD_DATES###" size="3" multiple="yes">###LEAD_DATES_LIST###</SELECT></TD>
	</TR>
<!-- ###INVENTORY_LISTING_ROW_OLD### end -->



<!-- ###INVENTORY_LISTING_FOOTER### begin -->
	<tr> 
              <td colspan="8">&nbsp;</td>
     </tr>
     <tr>
<td colspan="8"><input type="checkbox" name="###PREFIXID###[rights][jobbank]" ###SELECTEDJOB###> Enable Job Bank</td>
</tr>
<tr>
<td colspan="8"><input type="checkbox" name="###PREFIXID###[rights][bulletinsponsor]" ###SELECTEDBULLETIN###> Enable Bulletin Sponsorship</td>
</tr>
<!-- <tr>
<td colspan="8"><input type="checkbox" name="###PREFIXID###[rights][emailblast]" ###SELECTEDEMAIL###> Enable Email Blast</td>
</tr> -->
    <tr> 
			<td colspan="4"><div align="center"> 
	                        <input onClick="document.getElementById('tx_sponsorcontentscheduler_pi1[formaction]').value='AddItem';" type=submit value="Add New Item" name="###SUBMIT_ADD_ITEM###" >
	                      </div>
			</td>
            <td colspan="8"><div align="center"> 
					###PACKAGE_INFO_HIDDEN###
                  <INPUT type="submit" name="###SUBMIT_UPDATE###" id="###SUBMIT_UPDATE###" value="Update Package with News items" onClick="document.getElementById('###FORMACTION_NAME###').value='UpdateItems';" />
                </div>
			</td>
     </tr>
          </tbody>
        </table>
      </FORM>
     
    </td>
  </tr>
  <tr> 
    <td>
     
    </td>
  </tr>
</table>
<!-- ###INVENTORY_LISTING_FOOTER### end -->

<!-- ###INVENTORY_LISTING_FOOTER_OLD### begin -->
	<TR>
		<TD COLSPAN="2" ALIGN="LEFT"><INPUT type="submit" name="###SUBMIT_UPDATE###" value="Update" onClick="document.getElementById('###FORMACTION_NAME###').value='UpdateItems';" /></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###INVENTORY_LISTING_FOOTER_OLD### end -->


<!-- ###TEMPLATE_CREATE_SPONSOR_USER### begin -->
<SCRIPT language="javascript" src="###JSVAL_LOCATION###"></SCRIPT>
<script type="text/javascript">
<!--
	function suggestUsername()
	{
		var firstName=document.getElementById("###ID_FIRST_NAME###");
		var lastName=document.getElementById("###ID_LAST_NAME###");
	}
	
	function sponsorContentSchedulerSubmitForm(theform)
	{
		var username=document.getElementById("###INPUT_USERNAME###");
		var password=document.getElementById("###INPUT_PASSWORD###");
		var formaction=document.getElementById("###FORMACTION_NAME###");
		
		if(formaction.value=="CreateSponsorUser")
		{
			username.setAttribute("required", "1");
			password.setAttribute("required", "1");
		}
		if(formaction.value=="SuggestUsername")
		{
			username.setAttribute("required", "0");
			password.setAttribute("required", "0");
		}
		return validateStandard(theform);
		
	}
	
// -->
</script>


<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return sponsorContentSchedulerSubmitForm(this);">
<INPUT type="hidden" name="###SPONSOR_ID_NAME###" value="###SPONSOR_ID###" />
<INPUT type="hidden" name="###ACTION_NAME###" id="###ACTION_NAME###" value="edit_inventory" />
<INPUT type="hidden" name="###FORMACTION_NAME###" id="###FORMACTION_NAME###" value="" />
<INPUT type="hidden" name="###ITEM_ID_NAME###" value="###ITEM_ID###" />
<TABLE>
	<TR>
		<TD colspan="2"><FONT color="red">###ERROR_MESSAGE###</FONT></TD>
	</TR>
	<TR>
		<TD colspan="2"><h2>Creating user for sponsor "###SPONSOR_NAME###"</TD>
	</TR>
	</TR>
<!-- TODO - Using FIRST_NAME as full name, convert this later -->
	<TR>
		<TD>Name</TD>
		<TD><INPUT type="text" name="###INPUT_FIRST_NAME###" value="###INPUT_FIRST_NAME_VALUE###" id="###ID_FIRST_NAME###" required="1" err="Please enter the name of the user" /></TD>
	</TR>
<!--	<TR>
		<TD>Last Name</TD>
		<TD><INPUT type="text" name="###INPUT_LAST_NAME###" value="###INPUT_LAST_NAME_VALUE###" id="###ID_LAST_NAME###" /></TD>
	</TR>
-->
	<TR>
		<TD>Email</TD>
		<TD><INPUT type="text" name="###INPUT_EMAIL###" value="###INPUT_EMAIL_VALUE###" required="1" regexp="JSVAL_RX_EMAIL" err="Please enter a valid email" /></TD>
	</TR>
	<TR>
		<TD>Phone</TD>
		<TD><INPUT type="text" name="###INPUT_PHONE###" value="###INPUT_PHONE_VALUE###" required="0" regexp="JSVAL_RX_TEL" err="Please enter a valid phone number" /></TD>
	</TR>
	<TR>
		<TD>Username</TD>
		<TD>
			<INPUT type="text" name="###INPUT_USERNAME###" id="###INPUT_USERNAME###" value="###INPUT_USERNAME_VALUE###" id="SponsorContentSchedulerUsername" required="1" regexp="/^[\w_\.].*$/" err="Please enter a valid username" />
			<INPUT type="submit" name="###SUBMIT_SUGGEST_USERNAME###" value="Suggest Username & Password" onClick="document.getElementById('###FORMACTION_NAME###').value='SuggestUsername';" />
		</TD>
	</TR>
	<TR>
		<TD>Password</TD>
		<TD><INPUT type="text" name="###INPUT_PASSWORD###" id="###INPUT_PASSWORD###" value="###INPUT_PASSWORD_VALUE###" id="SponsorContentSchedulerPassword" required="1" minlength="4" maxlength="40" err="Pleae enter a password between 4 to 40 characters" /></TD>
	</TR>
	<TR>
		<TD colspan="2"><INPUT type="submit" name="###SUBMIT_CREATE_SPONSOR###" value="Create User & Resume Editing"  onClick="document.getElementById('###FORMACTION_NAME###').value='CreateSponsorUser';" /></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###TEMPLATE_CREATE_SPONSOR_USER### end -->
