###FORM_JS###
<H2>###HEADER###</H2>
<script language="Javascript">
function checkForm(frm){
	if(frm.elements['tx_sponsorcontentscheduler_pi1[sponsor_user_id]'].value>0){
		return true;
	}else{
		alert('Please select a sponsor user');
		return false;
	}
}
</script>
 <FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onsubmit="return checkForm(this);">
 <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3"><strong>&raquo;&nbsp;###CREATE_NEW_SPONSOR_USER_LINK###</strong></td>
        </tr>
	 <tr>
          <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3"><INPUT type="hidden" name="###ACTION###" id="###ACTION###" value="edit_sponsor_user" ></INPUT></td>
        </tr>
        <tr>
          ###SPONSOR### ###SPONSOR_USER###<td ><INPUT type="submit" name="###SUBMIT_SPONSOR_USER###" value="Edit" ></INPUT></td>
        </tr>
      
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
      </table>
  </FORM>
