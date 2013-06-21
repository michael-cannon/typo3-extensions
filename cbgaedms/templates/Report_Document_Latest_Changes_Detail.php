<table cellpadding="0" cellspacing="0">
	<th class="first">Location</th>
	<th>Document</th>
	<th>Doc. Description</th>
	<th>Doc. Type</th>
	<th>Doc. Version</th>
	<th>Version Date</th>
	<th>Version Description</th>
	<th>Filename</th>
	<th>Author</th>
<?php
for($this->rewind(); $this->valid(); $this->next()) {
$entry = $this->current();
?>
<tr>
	<td><?php $entry->printAsLinkText($entry->asText('agency'),'locationsPid','View','Location_View',$entry->asText('uid')); ?></td>
	<td><?php $entry->printAsLinkText($entry->asText('doc'),'documentPid','View','Document_View',$entry->asText('docuid')); ?></td>
	<td><?php $entry->printAsText('description'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('doctype'); ?>&nbsp;</td>
	<td><?php $entry->printAsLinkText($entry->asText('docversion'),'documentPid','View','Document_Versions_View',$entry->asText('versionuid')); ?></td>
	<td><?php $entry->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
	<td><?php $entry->printAsText('versiondescription'); ?>&nbsp;</td>
	<td><a href="<?php $entry->printAsLinkDownload($entry->asText('versionuid')); ?>"><?php $entry->printAsText('filename'); ?></a></td>
	<td><?php $entry->printAsUsers('feuser', true); ?>&nbsp;</td>
</tr>
<?php } ?>
</table>
