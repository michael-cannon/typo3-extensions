<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
?>
<div id="EDMS">
	<h1>Create/Edit Company Silo</h1>
	<h2 class="docs" title="Company Silo"></h2>
	<div class="clearer"></div>
	<!--  docs -->
		<div id="SiloEdit">
		<h3>silo</h3>
		<div class="clearer"></div>
		<form id="edmsdt" name="edmsdt" method="post" action="<?php $this->printAsLink(); ?>">
			<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
			<div class="clearer"></div>
		<div id="SiloEditWrap">
			<div class="clearer"></div>
		<?php $this->printErrorList(); ?>
			<table width="935" cellpadding="0" cellspacing="0">
				<th class="first">Company Silo</th>
				<th>Details</th>
				<?php if ( $entry->isNotEmpty() ) { ?>
				<th>Hide</th>
				<?php } ?>
				<tr>
					<td><input type="text" name="tx_cbgaedms[silo]" value="<?php $entry->printAsForm('silo'); ?>" /><?php $entry->printAsError('silo', true); ?></td>
					<td><textarea name="tx_cbgaedms[description]"><?php $entry->printAsForm('description'); ?></textarea><?php $entry->printAsError('[description'); ?></td>
					<?php if ( $entry->isNotEmpty() ) { ?>
					<td><input type="checkbox" name="tx_cbgaedms[hidden]" value="1" /></td>
					<?php } ?>
				</tr>
			</table>
			<div id="EditButtons">
				<input type="submit" value="Business_Silo_Edit" id="Save"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Business_Silo_ListClear" id="Cancel"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Business_Silo_Form" id="Reset"
					name="tx_cbgaedms[action]" />
			</div>
			</div>
		</div>
		</form>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
