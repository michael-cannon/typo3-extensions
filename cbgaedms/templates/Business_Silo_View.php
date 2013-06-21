<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php $entry = $this->current(); ?>
<div id="EDMS">
	<h1><?php $entry->printAsText('silo'); ?> Company Silo</h1>
	<h2 class="docs" title="Company Silo"></h2>
	<div class="CreateDoc"><a href="<?php $this->printAsLink(false, 'Business_Silo_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new silo" title="Create/add new silo" border="0"/></a><a href="<?php $this->printAsLink(false, 'Business_Silo_Form', true); ?>">Create/add new silo</a></div>
	<!--  docs -->
		<div id="Docs">
		<h3>silo</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Edit</th>
					<th>Company Silo</th>
					<th>Details</th>
					<tr>
						<td><a href="<?php $entry->printAsLink(false, 'Business_Silo_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this silo" title="Edit this silo" border="0"/></a></td>
						<td><?php $entry->printAsText('silo'); ?></td>
						<td><?php $entry->printAsText('description'); ?>&nbsp;</td>
					</tr>
					</table>
				<div class="create"><a href="<?php $this->printAsLink(false, 'Business_Silo_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new silo" title="Create/add new silo" border="0"/></a><a href="<?php $this->printAsLink(false, 'Business_Silo_Form', true); ?>">Create/add new silo</a></div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
