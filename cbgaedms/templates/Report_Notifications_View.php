<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
     $entry = $this->current();
 ?>
<div id="EDMS">
	<h1><?php $entry->printAsReportType($entry->asText('report')); ?> Report Notifications</h1>
	<h2 class="docs" title="Report Notifications"></h2>
	<div class="CreateDoc"><a href="<?php $this->printAsLink(false,
	'Report_Notifications_Form', true); ?>"><img
	src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif"
	alt="Create report notification" title="Create report notification"
	border="0"/></a><a href="<?php $this->printAsLink(false,
	'Report_Notifications_Form', true); ?>">Create report notification</a></div>
	<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>report notifications</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Edit</th>
					<th>Report Type</th>
					<th>Frequency</th>
					<th>Recipients</th>
					<th>Send On/Off</th>
					<th>Message Body</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
	<tr>
		<td><a href="<?php $entry->printAsLink(false, 'Report_Notifications_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this report notification" title="Edit this report notification" border="0"/></a></td>
		<td><?php $entry->printAsReportType($entry->asText('report')); ?></td>
		<td><?php $entry->printAsFrequencyType($entry->asText('frequency')); ?>&nbsp;</td>
		<td><?php $entry->printAsUsers('recipients', true); ?>&nbsp;</td>
		<?php if ( $entry->asInteger('reporton') ) { ?>
		<td>On</td>
		<?php } else { ?>
		<td>Off</td>
		<?php } ?>
		<td><?php $entry->printAsText('messagebody', true); ?>&nbsp;</td>
	</tr>
<?php } ?>

				</table>
				<div class="create"><a href="<?php $this->printAsLink(false,
				'Report_Notifications_Form', true); ?>"><img
				src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif"
				alt="Create report notification" title="Create report notification"
				border="0"/></a><a href="<?php $this->printAsLink(false,
				'Report_Notifications_Form', true); ?>">Create report notification</a></div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
