<?php
/**
 * Example template for phpTemplateEngine.
 *
 * Edit this template to match your needs.
 * $entry is of type tx_lib_object and represents a single data row.
 */
?>
<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<div id="EDMS">
	<h1>Documents</h1>
	<h2 class="docs" title="Documents"></h2>
	<!--
	<div class="CreateDoc"><a href="<?php $this->printAsLink(false, 'Document_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new document" title="Create/add new document" border="0"/></a><a href="<?php $this->printAsLink(false, 'Document_Form', true); ?>">Create/add new document</a></div>
	-->
	<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>documents</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
			<?php $this->printResultBrowser(); ?>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Edit</th>
					<th>Document Name</th>
					<th>Document Type</th>
					<th>Version Number</th>
					<th>Last Modification Date</th>
					<th>Details</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
     $doctype = $entry->get('doctype');
     $version = $entry->get('version');
?>
	<tr>
		<td><a href="<?php $entry->printAsLink(false, 'Document_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this document" title="Edit this document" border="0"/></a></td>
		<td><?php $entry->printAsLinkText($entry->asText('doc'),false,'View','Document_View',true); ?></td>
		<td><?php $doctype->printAsText('doctype'); ?> &nbsp;</td>
		<td><?php $version->printAsText('docversion'); ?></td>
		<td><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
		<td class="description"><?php $entry->printAsText('description'); ?></td>
	</tr>
<?php } ?>

				</table>
				<?php $this->printResultBrowser(); ?>
				<!--
				<div class="create"><a href="<?php $this->printAsLink(false, 'Document_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new document" title="Create/add new document" border="0"/></a><a href="<?php $this->printAsLink(false, 'Document_Form', true); ?>">Create/add new document</a></div>
				-->
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
