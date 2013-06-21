<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php $entry = $this->current(); ?>
<div id="EDMS">
	<h1><?php $entry->printAsText('doctype'); ?> Document Type</h1>
	<h2 class="docs" title="Document Type"></h2>
	<div class="CreateDoc"><a href="<?php $this->printAsLink(false, 'Document_Type_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new document type" title="Create/add new document type" border="0"/></a><a href="<?php $this->printAsLink(false, 'Document_Type_Form', true); ?>">Create/add new document type</a></div>
	<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>document type</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Edit</th>
					<th>Document Type</th>
					<th>Details</th>
					<th>Required</th>
					<tr>
						<td><a href="<?php $entry->printAsLink(false, 'Document_Type_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this document type" title="Edit this document type" border="0"/></a></td>
						<td><?php $entry->printAsText('doctype'); ?></td>
						<td><?php $entry->printAsText('description'); ?>&nbsp;</td>
						<?php if ( $entry->asInteger('required') ) { ?>
						<td>Yes</td>
						<?php } else { ?>
						<td>&nbsp;</td>
						<?php } ?>
					</tr>
					</table>
				<div class="create"><a href="<?php $this->printAsLink(false, 'Document_Type_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new document type" title="Create/add new document type" border="0"/></a><a href="<?php $this->printAsLink(false, 'Document_Type_Form', true); ?>">Create/add new document type</a></div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
