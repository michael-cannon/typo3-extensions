<form action="{action}" enctype="multipart/form-data" id="ulist" name="ulist" method="post" class="tx-chcforum-pi1-postForm">
<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table contains the simple search fields" class="tx-chcforum-pi1-Table">
<caption>User List</caption>
<thead>	
	<tr> 
		<th colspan="4">{hdr_user_list}</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th>{hdr_name}</th>
		<th>{hdr_joined}</th>
		<th>{hdr_posts}</th>
		<th>{hdr_email}</th>
	</tr>
	<!-- START BLOCK : user -->
	<tr>
		<td>{name}</td>
		<td>{joined}</td>
		<td>{posts}</td>
		<td align="center">{email}</td>
	</tr>
	<!-- END BLOCK : user -->
	<thead>	
		<tr> 
			<th colspan="4">{hdr_search}</th>
		</tr>
	</thead>
	<tr>
		<td colspan="4">
			{lbl_search} <input type="text" size="30" name="search[uname]" /> <input type="submit" name="submit" value="{submit}"/>
		</td>
	</tr>	
</tbody>
</table>
</form>