<!-- ###TEMPLATE_MENU### begin -->
<H2>###HEADER###</H2>
<FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onsubmit="return checkForm(this);">
<script language="JavaScript" type="text/javascript">
<!--
function createUser()
{
	var theform=document.getElementById('###FORM_NAME###');
	document.getElementById('###RADIO_CHOICE###').value='create_user';
	theform.submit();
}

function createSponsor()
{
	var theform=document.getElementById('###FORM_NAME###');
	document.getElementById('###RADIO_CHOICE###').value='create_sponsor';
	theform.submit();
}

function createPackage()
{
	var theform=document.getElementById('###FORM_NAME###');
	document.getElementById('###RADIO_CHOICE###').value='package_manager';
	theform.submit();
}
//-->
</script>
<TABLE>
	###SPONSOR###
	###CREATE_NEW_SPONSOR###
	###CREATE_NEW_PACKAGE###
	<TR>
		<TD>Edit Sponsor Profile</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" id="###RADIO_CHOICE###" value="edit_sponsor_profile" CHECKED ></INPUT></TD>
	</TR>
	<TR>
		<TD>Edit Job Bank</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_job_bank" ></INPUT></TD>
	</TR>
	<TR>
		<TD>Edit Inventory</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_inventory" ></INPUT></TD>
	</TR>
<!--	<TR>
		<TD>Enable Sponsor Page</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="enable_sponsor_page"></INPUT></TD>
	</TR>
	<TR>
		<TD>Disable Sponsor Page</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="disable_sponsor_page"></INPUT></TD>
	</TR>
	<TR>
		<TD>Edit Sponsor Page</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_sponsor_page"></INPUT></TD>
	</TR>
	<TR>
		<TD>Enable Job-bank for Sponsor</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="enable_job_bank"></INPUT></TD>
	</TR>
	<TR>
		<TD>Disable Job-bank for Sponsor</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="disable_job_bank"></INPUT></TD>
	</TR>
-->
	<TR>
		<TD></TD>
		<TD><INPUT type="submit" name="###SUBMIT_SPONSOR###" value="Submit" ></INPUT></TD>
	</TR>
	
	<TR>
		<TD colspan="2"><BR><BR></TD>
	</TR>
	
	<TR>
		<TD>Select Sponsor User</TD>
		<TD><SELECT name="###SPONSOR_USER_LIST_NAME###">###SPONSOR_USER_LIST###</SELECT></TD>
	</TR>
	<TR>
		<TD><A href="javascript: createUser()" class="lnav">Create new sponsor user</A></TD>
		<TD></TD>
	</TR>
	<TR>
		<TD>Edit Sponsor User</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_sponsor_user"></INPUT></TD>
	</TR>
	<TR>
		<TD></TD>
		<TD><INPUT type="submit" name="###SUBMIT_SPONSOR_USER###" value="Submit"></INPUT></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###TEMPLATE_MENU### end -->
