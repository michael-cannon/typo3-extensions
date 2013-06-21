<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<div id="EDMS">
	<h1>Report Notifications Sent Report for <?php echo date('r'); ?></h1>
	<h2 class="docs" title="Report Notifications"></h2>
	<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>Sent Reports</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Report Type</th>
					<th>Frequency</th>
					<th>Recipients</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
	<tr>
		<td><?php $entry->printAsLinkText($entry->asReportType($entry->asText('report')),false,'View','Report_Notifications_View',true); ?></td>
		<td><?php $entry->printAsFrequencyType($entry->asText('frequency')); ?>&nbsp;</td>
		<td><?php $entry->printAsUsers('recipients', true); ?>&nbsp;</td>
	</tr>
<?php } ?>

				</table>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
