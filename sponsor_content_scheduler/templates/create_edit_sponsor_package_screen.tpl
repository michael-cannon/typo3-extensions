###FORM_JS###
<H2>###HEADER###</H2>
<SCRIPT LANGUAGE="JavaScript">
<!--
var clickedItem;
function checkForm(frm){
	if(clickedItem=='tx_sponsorcontentscheduler_pi1[submit_sponsor_to_create_package]'){
		if(frm.elements['tx_sponsorcontentscheduler_pi1[sponsor_id]'].value<=0){
			alert('Please select a sponsor');
			return false;
		}
	}
	if(clickedItem=='tx_sponsorcontentscheduler_pi1[submit_package_to_edit]'){
		if(frm.elements['tx_sponsorcontentscheduler_pi1[package_id]'].value<=0){
			alert('Please select a Package');
			return false;
		}
	}
}
//-->
</SCRIPT>
 <FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onsubmit="return checkForm(this);">
 <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
         
	  <td >
		 &nbsp;
	  </td>
        </tr>
        <tr>
          ###SPONSOR###
	  <td >
		
		  <INPUT type="submit" name="###SUBMIT_SPONSOR###" value="CREATE A PACKAGE" onClick="document.getElementById('tx_sponsorcontentscheduler_pi1[package_id]').options[0].selected=true;document.forms['tx_sponsorcontentscheduler_pi1[create_edit_sponsor_package]'].action='###REDIRECT_URL_SPONSOR###';clickedItem=this.name;"></INPUT>
	  </td>
        </tr>
	 <tr>
         
	  <td >
		 &nbsp;
	  </td>
        </tr>
        <tr>
          ###SPONSOR_PACKAGE###
		<td>
			
			<INPUT type="submit" name="###SUBMIT_SPONSOR_PACKAGE###" value="EDIT A PACKAGE"  onClick="document.forms['tx_sponsorcontentscheduler_pi1[create_edit_sponsor_package]'].action='###REDIRECT_URL_PACKAGE###';clickedItem=this.name;"></INPUT></INPUT>
		</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
      </table>
  </FORM>

