<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
?>
<?php include_once('scripts/include.php'); ?>
<div id="EDMS">
	<h1>Edit Report Notifications</h1>
	<h2 class="docs" title="Report Notifications"></h2>
	<!--
	<div id="help"><a href="#"></a></div>
	-->
	<div class="clearer"></div>
	<form id="edmsdt" name="edmsdt" method="post" action="<?php $this->printAsLink(); ?>">
			<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
	<div id="EditButtons">
			<input type="submit" value="Report_Notifications_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Report_Notifications_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Report_Notifications_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
		<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>document type</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
		<?php $this->printErrorList(); ?>
			<table width="935" cellpadding="0" cellspacing="0">
				<th class="first">Report Type</th>
				<th>Frequency</th>
				<th>Recipients</th>
				<th>Message Body</th>
				<th>Send On/Off</th>
				<?php if ( $entry->isNotEmpty() ) { ?>
				<th>Hide</th>
				<?php } ?>
				<tr>
					<td><?php $entry->printAsReportTypeSelect('report', $entry->asForm('report')); ?><?php $entry->printAsError('report', true); ?></td>
					<td><?php $entry->printAsFrequencyTypeSelect('frequency', $entry->asForm('frequency')); ?><?php $entry->printAsError('frequency', true); ?></td>
					<td><?php $entry->printAsDualSelectUsers( null, 'recipients', $entry->get('newrecipientsleft') ); ?></td>
					<td><textarea name="tx_cbgaedms[messagebody]"><?php $entry->printAsForm('messagebody'); ?></textarea><?php $entry->printAsError('messagebody'); ?></td>
					<td><input type="checkbox" name="tx_cbgaedms[reporton]" value="1"
						<?php if ( ! $entry->isNotEmpty() || $entry->asInteger('reporton') ) { ?>
						checked="checked"
						<?php } ?>
					/></td>
					<?php if ( $entry->isNotEmpty() ) { ?>
					<td><input type="checkbox" name="tx_cbgaedms[hidden]" value="1" /></td>
					<?php } ?>
				</tr>
			</table>
			</div>
		</div>
		<div id="EditButtons">
			<input type="submit" value="Report_Notifications_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Report_Notifications_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Report_Notifications_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
		</form>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
	<div class="clearer"></div>
	<!--
	<div id="helpbottom"><a href="#"></a></div>
	-->
</div>
