<form action="{action}" enctype="multipart/form-data" id="search" name="search" method="post" class="tx-chcforum-pi1-postForm">
<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table contains the simple search fields" class="tx-chcforum-pi1-Table">
<caption>{simple_search_hdr}</caption>
<colgroup>	
	<col class="col-0" />
	<col class="col-last" />
</colgroup>
<thead>	
	<tr> 
		<th colspan="2">{simple_search_hdr}</th>
	</tr>
</thead>

<tbody>
	<tr>
		<td>
				<fieldset id="searchKeywords">
					<legend>{search_kw}</legend>
					<input type="text" name="search[keywords]" size="25" />
					<br />
					<input name="search[keyword_exact]" type="checkbox" /> {exact_match}
				</fieldset>
		</td>
		<td>
				<fieldset id="searchUsers">
					<legend>{search_uname}</legend>
					<input type="text" name="search[uname]" size="25" />
					<br />
					<input name="search[uname_exact]" type="checkbox" /> {exact_match}
				</fieldset>
		</td>
	</tr>
</tbody>
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table contains the advanced search fields" class="tx-chcforum-pi1-Table">
<caption>{adv_search_hdr}</caption>
<colgroup>	
	<col class="col-0" />
</colgroup>
<thead>	
	<tr> 
		<th>{adv_search_hdr}</th>
	</tr>
</thead>
<tfoot />
<tbody>
	<tr>
		<td>
			<fieldset>
			<legend>{search_cats}</legend>
			<select multiple="multiple" style="width: 100%;" name="search[where][]" size="5">
				{where_options}
			</select>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td>
			<fieldset>
			<legend>{post_age}</legend>
			<select name="search[post_age]">
				{age_options}
			</select>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td>
			<fieldset>
			<legend>{post_fields}</legend>
			<input checked="checked" name="search[post_fields]" type="radio" value="1" /> {post_fields_all}
			<input name="search[post_fields]" type="radio" value="2" /> {post_fields_titles}
			</fieldset>
		</td>
	</tr>
	<tr>
		<td>
			<fieldset>
			<legend>{display_results}</legend>
			<input checked="checked" name="search[display_results]" type="radio" value="1" /> {display_posts}
			<input name="search[display_results]" type="radio" value="2" /> {display_threads}
			</fieldset>
		</td>
	</tr>
</tbody>
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table contains the form for searching" class="tx-chcforum-pi1-Table">
<caption>{submit_search_hdr}</caption>
<colgroup>	
	<col class="col-0" />
</colgroup>
<thead>	
	<tr> 
		<th>{submit_search_hdr}</th>
	</tr>
</thead>
<tfoot />
<tbody>
	<tr>
		<td align="center">
			<input type="hidden" name="view" value="search" />
			<input type="submit" name="search[submit]" value="{submit_inpt}" />
		</td>
	</tr>
</tbody>
</table>
</form>