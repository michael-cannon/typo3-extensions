<form action="{action}" enctype="multipart/form-data" id="post" name="post" method="post" onsubmit="return checkForm(this)" class="tx-chcforum-pi1-postForm">
	{message}
	<input type="hidden" name="view" value="{hash_view}" />
	<input type="hidden" name="fe_user_uid" value="{hash_fe_user_uid}" />
	<input type="hidden" name="page" value="{page}" />
	<input type="hidden" name="post_uid" value="{hash_post_uid}" />
	<input type="hidden" name="thread_uid" value="{hash_thread_uid}" />
	<input type="hidden" name="conf_uid" value="{hash_conf_uid}" />
	<input type="hidden" name="cat_uid" value="{hash_cat_uid}" />
	<input type="hidden" name="where" value="{where}" />
	<input type="hidden" name="stage" value="{stage}" />
<div id="formWhere">{label_where}</div>
<fieldset id="formTop">
	<legend>&nbsp;{label_author_and_subject}&nbsp;</legend>
	<label for="name">{label_name}</label>
	{name} {username}<br />
	<label for="email">{label_email}</label>
	{email}<br />
	<label for="subject">{label_subject}</label>
	<input type="text" name="subject" size="40" value="{subject}" /><br style="clear: both" />
</fieldset>

<fieldset id="formBottom">
	<legend>&nbsp;{label_instruct}&nbsp;</legend>
	<img src="{img_path}bold_large.png" accesskey="b" name="addfcode0" value=" B " onclick="fcstyle(0)" onmouseover="helpline('b');"/>
	<img src="{img_path}italic_large.png" accesskey="i" name="addfcode2" value=" i " onclick="fcstyle(2)" onmouseover="helpline('i');"/>
	<img src="{img_path}underline_large.png" accesskey="u" name="addfcode4" value=" u " onclick="fcstyle(4)" onmouseover="helpline('u');"/>
	<img src="{img_path}color_large.png" accesskey="c" name="addfcode5" value=" c " onclick="fcstyle(12)" onmouseover="helpline('c');"/>
	<img src="{img_path}quote_large.png" accesskey="q" name="addfcode6" value="Quote" onclick="fcstyle(6)" onmouseover="helpline('q');"/>
	<img src="{img_path}image_large.png" accesskey="p" name="addfcode8" value="Img" onclick="fcstyle(8)" onmouseover="helpline('p');"/>
	<img src="{img_path}url_large.png" accesskey="w" name="addfcode10" value="URL" onclick="fcstyle(10)" onmouseover="helpline('w');"/>
	<span><a href="javascript:fcstyle(-1)" onmouseover="helpline('a')" title="{close_tags}"><img style="border: 0px;" alt="{close_tags}" src="{img_path}close.png" /></a></span>
<br />
<input type="text" disabled="disabled" name="helpbox" size="45" maxlength="100" style="width:450px; font-size:10px" class="tx-chcforum-pi1-helpBox" value="{form_help}" />
<!-- START BLOCK : emoticons -->
<hr />
{emoticons}
<!-- END BLOCK : emoticons -->
<textarea name="text" rows="15" cols="35" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">{text}</textarea>
<!-- START BLOCK : thread_endtime -->
{label_endtime} {endtime_inpt} {label_max_endtime}
<hr />
<!-- END BLOCK : thread_endtime -->
{label_attachment} {attach_inpt}<br />
<hr />
<div class="tx-chcforum-pi1-formBtn"><input type="submit" name="submit" value="{submit}" onmouseover="this.className='tx-chcforum-pi1-formBtnHov'" onmouseout="this.className='tx-chcforum-pi1-formBtn'"/>
<input type="submit" name="preview" value="{preview_btn}" onmouseover="this.className='tx-chcforum-pi1-formBtnHov'" onmouseout="this.className='tx-chcforum-pi1-formBtn'"/></div>
<!-- START BLOCK : cancel -->
<div class="tx-chcforum-pi1-formBtn"><input type="submit" name="cancel" value="{cancel}" onmouseover="this.className='tx-chcforum-pi1-formBtnHov'" onmouseout="this.className='tx-chcforum-pi1-formBtn'" onclick="return(confirm('{cancel_confirm}'))"/></div>
<!-- END BLOCK : cancel -->
</fieldset>
</form>