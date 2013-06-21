<!-- ###TEMPLATE_CREATE_SPONSOR### begin -->
<SCRIPT language="javascript" src="###JSVAL_LOCATION###"></SCRIPT>
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###" onSubmit="return validateStandard(this);" enctype="multipart/form-data">
<INPUT type="hidden" name="###HIDDEN_SPONSOR_ID###" value="###HIDDEN_SPONSOR_ID_VALUE###" />
<TABLE>
	<TR>
		<TD colspan="2"><FONT color="red">###ERROR_MESSAGE###</FONT></TD>
	</TR>
	<TR>
		<TD>Sponsor Name</TD>
		<TD><INPUT type="text" name="###INPUT_SPONSOR_NAME###" value="###INPUT_SPONSOR_NAME_VALUE###" required="1"  err="Please enter a valid Sponsor Name" /></TD>
	</TR>
	<TR>
		<TD>Sponsor Description</TD>
		<TD><TEXTAREA name="###INPUT_SPONSOR_DESC###" rows="5" cols="40" required="0"  err="Please enter a valid Sponsor Description">###INPUT_SPONSOR_DESC_VALUE###</TEXTAREA></TD>
	</TR>
	<TR>
		<TD>Contact Name</TD>
		<TD><INPUT type="text" name="###INPUT_CONTACT_NAME###" value="###INPUT_CONTACT_NAME_VALUE###" required="0"  err="Please enter a valid Contact Name"/></TD>
	</TR>
	<TR>
		<TD>Website</TD>
		<TD><INPUT type="text" name="###INPUT_WEBSITE###" value="###INPUT_WEBSITE_VALUE###" required="0"  regexp="/^(http|https)\:\/\/([\w-]+\.)+(/[\w- ./?%&=]*)?$/" err="Please enter a valid website address"/></TD>
	</TR>
	<TR>
		<TD>Logo</TD>
		<TD>
			###LOGO_IMG###
			<INPUT type="hidden" name="MAX_FILE_SIZE" value="###MAX_LOGO_SIZE###" />
			<INPUT type="file" name="###INPUT_LOGO###"
			value="###INPUT_LOGO_VALUE###" required="0"
			regexp="/(eps|jpg|jpeg|gif|png|tif|tiff)$/" err="You can upload only EPS, JPEG, GIF, PNG, and TIFF files as the sponsor logo" />
		</TD>
	</TR>
	<TR>
		<TD>Sponsor Categories</TD>
		<TD><SELECT name="###SPONSOR_CATEGORIES###" size="10" multiple="yes" required="1"  err="Please select at least one sponsor category">###SPONSOR_CATEGORIES_VALUE###</SELECT></TD>
	</TR>
	<TR>
		<TD>Country</TD>
		<TD><SELECT name="###COUNTRY###" required="1"  err="Please select a country">###COUNTRY_VALUE###</SELECT></TD>
	</TR>

	<TR>
		<TD>Sponsor User (Owner)</TD>
		<TD><SELECT name="###SPONSOR_USER###">###SPONSOR_USER_VALUE###</SELECT></TD>
	</TR>

	<TR>
		<TD>Enable Sponsor Page</TD>
		<TD><INPUT type="checkbox" name="###CHECK_SPONSOR_PAGE###" ###CHECKED_SPONSOR_PAGE### /></TD>
	</TR>
	<TR>
		<TD>Enable Job Bank</TD>
		<TD><INPUT type="checkbox" name="###CHECK_JOB_BANK###" ###CHECKED_JOB_BANK### /></TD>
	</TR>

	<TR>
		<TD>
			<INPUT type="hidden" name="###ACTION_NAME###" value="###ACTION_VALUE###" />
			<INPUT type="hidden" name="###FORMACTION_NAME###" value="###FORMACTION_VALUE###" />
		</TD>
		<TD><INPUT type="submit" name="###SUBMIT_SPONSOR_FORM###" value="Submit"></INPUT></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###TEMPLATE_CREATE_SPONSOR### end -->


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
		<TD>Package</TD>
		<TD><SELECT name="###INPUT_PACKAGE_NAME###">###PACKAGE_LIST###</SELECT></TD>
	</TR>
	<TR>
		<TD>
			<INPUT type="hidden" name="###ACTION_NAME###" value="create_user" id="###ACTION_NAME###" />
			<INPUT type="hidden" name="###FORMACTION_NAME###" value="" id="###FORMACTION_NAME###" />
		</TD>
		<TD><INPUT type="submit" name="###SUBMIT_CREATE_SPONSOR###" value="Submit"  onClick="document.getElementById('###FORMACTION_NAME###').value='CreateSponsorUser';" /></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###TEMPLATE_CREATE_SPONSOR_USER### end -->



<!-- ###TEMPLATE_EDIT_SPONSOR_USER### begin -->
<H2>Edit User for Sponsor "###SPONSOR_NAME###"</H2>
<FORM name="###FORM_NAME###" method="post" action="###FORM_ACTION###">
<INPUT type="hidden" name="###HIDDEN_SPONSOR_USER_ID###" value="###HIDDEN_SPONSOR_USER_ID_VALUE###" />
<TABLE>
	<TR>
		<TD colspan="2"><FONT color="red">###ERROR_MESSAGE###</FONT></TD>
	</TR>
<!-- TODO - Using FIRST_NAME as full name, convert this later -->
	<TR>
		<TD>Name</TD>
		<TD><INPUT type="text" name="###INPUT_FIRST_NAME###" value="###INPUT_FIRST_NAME_VALUE###" id="###ID_FIRST_NAME###" /></TD>
	</TR>
<!--	<TR>
		<TD>Last Name</TD>
		<TD><INPUT type="text" name="###INPUT_LAST_NAME###" value="###INPUT_LAST_NAME_VALUE###" id="###ID_LAST_NAME###" /></TD>
	</TR>
-->
	<TR>
		<TD>Email</TD>
		<TD><INPUT type="text" name="###INPUT_EMAIL###" value="###INPUT_EMAIL_VALUE###" /></TD>
	</TR>
	<TR>
		<TD>Username</TD>
		<TD><INPUT type="text" name="###INPUT_USERNAME###" value="###INPUT_USERNAME_VALUE###" id="###ID_USERNAME###"/>
	</TR>
	<TR>
		<TD>Password</TD>
		<TD><INPUT type="text" name="###INPUT_PASSWORD###" value="###INPUT_PASSWORD_VALUE###" /></TD>
	</TR>
	<TR>
		<TD>Phone</TD>
		<TD><INPUT type="text" name="###INPUT_PHONE###" value="###INPUT_PHONE_VALUE###" /></TD>
	</TR>
	<TR>
		<TD>Package</TD>
		<TD><SELECT name="###INPUT_PACKAGE_NAME###">###PACKAGE_LIST###</SELECT></TD>
	</TR>
	<TR>
		<TD>
			<INPUT type="hidden" name="###ACTION_NAME###" value="edit_sponsor_user" id="###ACTION_NAME###" />
			<INPUT type="hidden" name="###FORMACTION_NAME###" value="EditSponsorUser" id="EditSponsorUser" />
		</TD>
		<TD><INPUT type="submit" name="###SUBMIT_EDIT_SPONSOR_USER###" value="Submit"></INPUT></TD>
	</TR>
</TABLE>
</FORM>
<!-- ###TEMPLATE_EDIT_SPONSOR_USER### end -->
