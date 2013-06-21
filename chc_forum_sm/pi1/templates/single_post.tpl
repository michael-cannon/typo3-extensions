{anchor}
<div class="tx-chcforum-pi1-singlePost">
	{message}
	<div class="author">
		{img_tag}
		<strong>{author_lbl} </strong>{author_name}<br />
		<strong>{date_lbl} </strong>{date} - {time}<br />
		<strong>{subject_lbl} </strong> {subject}<br />
		<div class="im">{aim_link} {yahoo_link} {msn_link} {customim_link} {cwt_buddylist_link} {cwt_user_pm_message_new}</div>
	</div>
	<div class="text"><div class="scroller">{parsed_post_body}</div></div>
	<!-- START BLOCK : attachment -->
	<div class="attachment">{attachment}</div>
	<!-- END BLOCK : attachment -->
	<!-- START BLOCK : rate -->
	<div class="rate">		
		<div class="rateStars">
				{stars} {score}
		</div>
		<div class="rateMenu" >
			<form action="{action}" enctype="multipart/form-data" name="rate" method="post">
				<!--{rate_label}-->
				{rate_select}
				<input type="submit" name="submit" value="{rate_submit_value}"/>
			</form>
		</div>
	</div>
	<!-- END BLOCK : rate -->
	<div class="edit">{reply_link} {quote_link} {admin_ip} {admin_edit_link} {admin_delete_link} {admin_unhide_link}</div>
</div>