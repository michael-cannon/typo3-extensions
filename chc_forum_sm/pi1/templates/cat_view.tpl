<div class="tx-chcforum-pi1-preTableWrap">
	{sub_tool_bar}
	{nav_path}
	{page_links_top}
</div>
<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table lists all categories and their respective conferences" class="tx-chcforum-pi1-Table">
<caption>Overview of Forum Categories</caption>
<colgroup>	
	<col class="col-0" />
	<col class="col-1" />
	<col class="col-2" />
	<col class="col-last" />
</colgroup>
<thead>	
	<tr> 
		<th>{header_title}</th>
		<th>{header_thread}</th>
		<th>{header_post}</th>
		<th>{header_last}</th>
	</tr>
</thead>
<tfoot></tfoot>
	<!-- START BLOCK : cat_list -->
<tbody>
	<!-- START BLOCK : cat_row -->
	<tr> 
		<th colspan="4" scope="rowgroup">{cat_title}</th>
	</tr>
	<!-- END BLOCK : cat_row -->
	<!-- START BLOCK : conf_row -->
	<tr>
		<td class="tx-chcforum-pi1-catConferenceCell">
		<dl>
		<dt>{conf_name}</dt>
		<dd>{conf_desc}</dd>
		<dd class="new">{conf_new}</dd>
		</dl>
		</td>
		<td class="tx-chcforum-pi1-catThreadCell">{conf_thread_count}</td>
		<td class="tx-chcforum-pi1-catPostCell">{conf_post_count}</td>
		<td class="tx-chcforum-pi1-catLastCell">{conf_last_post_data}</td>
	</tr>
	<!-- END BLOCK : conf_row -->
</tbody>
	<!-- END BLOCK : cat_list -->
</table>
{link_to_cats}
{footer_box}