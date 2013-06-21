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
	document.getElementById('###RADIO_CHOICE###').value='create_package';
	theform.submit();
}
//-->
</script>
<TABLE>
	###SPONSOR###
	###CREATE_NEW_SPONSOR###
	###CREATE_NEW_PACKAGE###
	<TR>
		<TD>Edit Job Bank</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_job_bank" ></INPUT></TD>
	</TR>
	<TR>
		<TD>Edit Inventory</TD>
		<TD><INPUT type="radio" name="###RADIO_CHOICE###" value="edit_inventory" ></INPUT></TD>
	</TR>
	<TR>
		<TD></TD>
		<TD><INPUT type="submit" name="###SUBMIT_SPONSOR###" value="Submit" ></INPUT></TD>
	</TR>
	
	<TR>
		<TD colspan="2"><BR><BR></TD>
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

<!-- ###SPONSOR_TEMPLATE_MENU### begin -->
<H1>Content Contributor</h1>
Welcome ###USERNAME###, <br/>
<table width="100%" border="0">
<tr valign="top">
	<td>Please scan each section below for important information and please see deadlines below if any.</td>
	<td align="center">###SPONSOR_LOGO###<br/><b>Your primary contact is : <br/>###SALES_REP###</b>###OFFICE_CONTACT_INFO### ###SPONSOR_EMAIL###</td>
</tr>
<tr valign="top">
	<td><font color="#cc3300">You have <b>IMPORTANT DEADLINES</b> in the next two weeks: </font></td>
	<td></td>
</tr>
</table>
<H1>Your sponsorship package includes the following:</H1>
<ol>
###SPONSORS_PACKAGE_LIST###
</ol>
<h1>Email reminders sent</h1>
<h1>Registered Members for upcoming round tables</h1>
        Click on "link to" links below to get materials and help promote your
        roundtable.
        <table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">

          <tbody>
            <tr>
              <td colspan="2" style="font-weight: bold; text-decoration: underline;">                Title </td>
              <td style="font-weight: bold; text-decoration: underline;" width="30%">Date</td>
              <td style="font-weight: bold; text-decoration: underline;" width="15%">Count</td>
            </tr>
</table>
<h1>Attendance Report for past round tables</h1>
 Click on title to download attendance report.
        <table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">
          <tbody>
            <tr>
              <td style="font-weight: bold; text-decoration: underline;"> Title </td>
              <td style="font-weight: bold; text-decoration: underline;">Date</td>
              <td style="font-weight: bold; text-decoration: underline;">Count</td>
            </tr>
</table>
<h1>Leads</h1>
 Click on title to download leads.
<table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="2">
<tbody>
<tr>
	<td style="font-weight: bold; text-decoration: underline;" width="30%">Title</td>
        <td style="font-weight: bold; text-decoration: underline;" width="17%">Type</td>
        <td style="font-weight: bold; text-decoration: underline;" width="11%">Leads</td>
        <td style="font-weight: bold; text-decoration: underline;" width="21%">Leads  Not Sent </td>
        <td style="font-weight: bold; text-decoration: underline;" width="21%">Leads Date</td>
</tr>
<!-- ###LEAD_DATA### begin -->
<!--<tr>
	<td style="font-weight: bold; text-decoration: underline;" width="30%">###LEAD_TITLE###</td>
        <td style="font-weight: bold; text-decoration: underline;" width="17%">###LEAD_TYPE###</td>
        <td style="font-weight: bold; text-decoration: underline;" width="11%">###LEAD_LEADS###</td>
        <td style="font-weight: bold; text-decoration: underline;" width="21%">###LEAD_NOT_SENT###</td>
        <td style="font-weight: bold; text-decoration: underline;" width="21%">###LEAD_DATE###</td>
</tr>-->
<!-- ###LEAD_DATA### end -->
</table>

<!-- ###SPONSOR_TEMPLATE_MENU### end -->
