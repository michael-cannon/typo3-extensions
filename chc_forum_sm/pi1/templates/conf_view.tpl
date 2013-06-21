<div class="tx-chcforum-pi1-preTableWrap">
	{sub_tool_bar}
	{nav_path}
	{page_links_top}
</div>
{message}
{preview_post}

<!-- START BLOCK : thread_table -->
<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table lists threads within this category" class="tx-chcforum-pi1-Table">
<caption>Threads within this category</caption>
<thead>
	<tr> 
	  <th>{header_image}</th>
		<th>{header_title}</th>
		<th>{header_replies}</th>
		<th>{header_author}</th>
		<th>{header_last}</th>
	</tr>
</thead>
<tfoot />
<tbody>
	<tr>
		<th colspan="5" scope="rowgroup">{cat_title}</th>
	</tr>
	<!-- START BLOCK : thread -->
	<tr>
	  <td class="tx-chcforum-pi1-confThreadImage">{thread_image}</td>
		<td class="tx-chcforum-pi1-confThreadSubjCell">{thread_subject}
		<div class="tx-chcforum-pi1-confThreadNew">{new_posts}</div>
		<div class="tx-chcforum-pi1-confThreadBtns">{hide}</div></td>
		<td class="tx-chcforum-pi1-confThreadRepliesCell">{thread_replies}</td>
		<td class="tx-chcforum-pi1-confThreadAuthorCell">{thread_author}</td>
		<td class="tx-chcforum-pi1-confThreadLastCell">{thread_last}</td>
	</tr>
	<!-- END BLOCK : thread -->
</tbody>
</table>
<!-- END BLOCK : thread_table -->
<div class="tx-chcforum-pi1-postTableWrap">
	{page_links_bottom}
	{link_to_cats}
</div>
{post_form}
