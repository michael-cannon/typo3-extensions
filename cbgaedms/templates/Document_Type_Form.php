<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
?>
<div id="EDMS">
	<h1>Create/Edit Document Type</h1>
	<h2 class="docs" title="Document Type"></h2>
	<div id="help"><a href="#"></a></div>
	<div class="clearer"></div>
	<form id="edmsdt" name="edmsdt" method="post" action="<?php $this->printAsLink(); ?>">
			<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
	<div id="EditButtons">
			<input type="submit" value="Document_Type_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Type_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Type_Form" id="Reset"
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
				<th class="first">Document Type</th>
				<th>Details</th>
				<th>Required (check if applicable)</th>
				<?php if ( $entry->isNotEmpty() ) { ?>
				<th>Hide</th>
				<?php } ?>
				<tr>
					<td><input type="text" name="tx_cbgaedms[doctype]" value="<?php $entry->printAsForm('doctype'); ?>" /><?php $entry->printAsError('doctype', true); ?></td>
					<td><textarea name="tx_cbgaedms[description]"><?php $entry->printAsForm('description'); ?></textarea><?php $entry->printAsError('description'); ?></td>
					<td><input type="checkbox" name="tx_cbgaedms[required]" value="1"
						<?php if ( $entry->asInteger('required') ) { ?>
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
			<input type="submit" value="Document_Type_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Type_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Type_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
		</form>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
	<div class="clearer"></div>
	<div id="helpbottom"><a href="#"></a></div>
</div>
