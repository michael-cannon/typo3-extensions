<table cellpadding="0" cellspacing="0">
	<th class="first">Document Type</th>
	<th>Details</th>
	<th>Required</th>
<?php
for($this->rewind(); $this->valid(); $this->next()) {
$entry = $this->current();
?>
<tr>
		<td><?php $entry->printAsLinkText($entry->asText('doctype'),false,'View','Document_Type_View',true); ?></td>
		<td><?php $entry->printAsText('description'); ?>&nbsp;</td>
		<?php if ( $entry->asInteger('required') ) { ?>
		<td>Yes</td>
		<?php } else { ?>
		<td>&nbsp;</td>
		<?php } ?>
</tr>
<?php } ?>
</table>
