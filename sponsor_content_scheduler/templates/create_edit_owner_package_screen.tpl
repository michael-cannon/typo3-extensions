###FORM_JS###
<H2>###HEADER###</H2>
 <FORM name="###FORM_NAME###" id="###FORM_NAME###" method="post" action="###FORM_ACTION###" onsubmit="return checkForm(this);">
 <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        
	 <tr>
         
	  <td >
		 ###SPONSOR###
	  </td>
        </tr>
        <tr>
          ###SPONSOR_PACKAGE###
		<td>
			
			<INPUT type="submit" name="###SUBMIT_SPONSOR_PACKAGE###" value="EDIT A PACKAGE"  onClick="document.forms['tx_sponsorcontentscheduler_pi1[create_edit_sponsor_package]'].action='###REDIRECT_URL_PACKAGE###';"></INPUT></INPUT>
		</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
      </table>
  </FORM>
