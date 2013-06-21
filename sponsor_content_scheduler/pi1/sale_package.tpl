<!-- ###TEMPLATE_SPONSOR_PACKAGE_LIST### begin -->
<h1>Sponsor Package Management</h1>
<table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">
<tbody>
<tr>
	<td style="font-weight: bold; text-decoration: underline;" width="20%">Package Name</td>
        <td style="font-weight: bold; text-decoration: underline;" width="50%">Description</td>
        <td style="font-weight: bold; text-decoration: underline;" width="25%">Options</td>
</tr>
<tr>
<td colspan="3" style="border-bottom: 1px solid #d2d2d2">&nbsp;</td>
</tr>
	###TEMPLATE_PACKAGE_DATA###
<tr>
<td colspan="3"><input type="checkbox" name="###PREFIXID###[rights][jobbank]"> Enable Job Bank</td>
</tr>
<tr>
<td colspan="3"><input type="checkbox" name="###PREFIXID###[rights][bulletinsponsor]"> Enable Bulletin Sponsorship</td>
</tr>
<tr>
<td colspan="3"><input type="checkbox" name="###PREFIXID###[rights][emailblast]"> Enable Email Blast</td>
</tr>
</table>
<a href="###PACKAGE_CREATE_LINK###">Create a package</a>&nbsp;&nbsp;&nbsp;&nbsp; <a href="###PACKAGE_LINK_BACK###" class="lnav">Back</a>
<!-- ###TEMPLATE_SPONSOR_PACKAGE_LIST### end -->

<!-- ###TEMPLATE_SPONSOR_PACKAGE_DATA### begin -->
	<tr valign="top">
		<td width="25%" style="border-bottom: 1px dashed #d2d2d2">###PACKAGE_NAME###</td>
        	<td width="50%" style="border-bottom: 1px dashed #d2d2d2">###PACKAGE_DESCRIPTION###</td>
        	<td width="25%" style="border-bottom: 1px dashed #d2d2d2"><a href="###PACKAGE_OPTION_EDIT###">Edit</a> | <a href="###PACKAGE_OPTION_DELETE###">Delete</a></td>
	</tr>
	<!-- ###TEMPLATE_SPONSOR_PACKAGE_DATA### end -->

<!-- ###TEMPLATE_SPONSOR_PACKAGE_CREATE### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSCALENDAR_LOCATION###"></SCRIPT>
<h1>Sponsor Package Management >> create</h1>
<form method="POST" action="###FORM_ACTION###">
<table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">
<tbody>
<tr>
	<td  width="25%">Package Name</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[title]"></td>
</tr>
<tr>
	<td  width="25%">Can edit Company Profile</td>
        <td  width="75%"><input type="radio" name="###EXTENSION###[company_profile]" value="1">Yes <input type="radio" name="###EXTENSION###[company_profile]" value="0">No</td>
</tr>
<tr>
	<td  width="25%">No of Bulletins</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[bulletin]"></td>
</tr>
<tr>
	<td  width="25%">No of Roundtable</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[roundtable]"></td>
</tr>
<tr>
	<td  width="25%">No of Whitepaper</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[whitepaper]"></td>
</tr>
<tr>
	<td  width="25%">Due Date</td>
        <td  width="75%"><input type="text" name="###EXTENSION_ENDTIME###" id="###EXTENSION_ENDTIME###"> <A href="javascript:NewCal('###EXTENSION_ENDTIME###', 'ddmmmyyyy', true, 24);">###IMG_CALENDAR###</A></td>
</tr>
</table>
<input type="hidden" name="###EXTENSION###[action]" value="create_package_new">
<input type="submit" name="" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
</form>
<!-- ###TEMPLATE_SPONSOR_PACKAGE_CREATE### end -->

<!-- ###TEMPLATE_SPONSOR_PACKAGE_EDIT### begin -->
<SCRIPT LANGUAGE="JavaScript" src="###JSCALENDAR_LOCATION###"></SCRIPT>
<h1>Sponsor Package Management >>Edit</h1>
<form method="POST" action="###FORM_ACTION###">
<table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">
<tbody>
<tr>
	<td  width="25%">Package Name</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[title]" value="###PACKAGE_TITLE###"></td>
</tr>
<tr>
	<td  width="25%">Can edit Company Profile</td>
        <td  width="75%"><input type="radio" name="###EXTENSION###[company_profile]" value="1" ###SELECTED_YES###>Yes <input type="radio" name="###EXTENSION###[company_profile]" value="0" ###SELECTED_NO###>No</td>
</tr>
<tr>
	<td  width="25%">No of Bulletins</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[bulletin]" value="###PACKAGE_BULLETIN###"></td>
</tr>
<tr>
	<td  width="25%">No of Roundtable</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[roundtable]" value="###PACKAGE_ROUNDTABLE###"></td>
</tr>
<tr>
	<td  width="25%">No of Whitepaper</td>
        <td  width="75%"><input type="text" name="###EXTENSION###[whitepaper]" value="###PACKAGE_WHITEPAPER###"></td>
</tr>
<tr>
	<td  width="25%">Due Date</td>
        <td  width="75%"><input type="text" name="###EXTENSION_ENDTIME###" id="###EXTENSION_ENDTIME###"  value="###PACKAGE_ENDTIME###"> <A href="javascript:NewCal('###EXTENSION_ENDTIME###', 'ddmmmyyyy', true, 24);">###IMG_CALENDAR###</A></td>
</tr>
</table>
<input type="hidden" name="###EXTENSION###[action]" value="edit_package">
<input type="hidden" name="###EXTENSION###[uid]" value="###PACKAGE_UID###">
<input type="submit" name="" value="&nbsp;&nbsp;&nbsp;Save Changes&nbsp;&nbsp;&nbsp;"> 
</form>
<!-- ###TEMPLATE_SPONSOR_PACKAGE_EDIT### end -->
