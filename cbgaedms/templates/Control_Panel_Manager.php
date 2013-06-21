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
	<h1>EDMS Manager Administration</h1>
	<div id="Admin">
	<!-- Location Module -->
	<div class="AdminModule">
	<div class="ModBottom">
		<h4>locations</h4>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsSearchPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_search.gif" alt="Search all locations" title="Search all locations" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Search all locations', 'locationsSearchPid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_viewLocations.gif" alt="View all locations" title="View all locations"border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('View all locations', 'locationsPid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('siloPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_search.gif"
			alt="Search by agency silo" title="Search by agency silo" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Search by agency silo', 'siloPid'); ?></div>
			<div class="clearer"></div>	
		</div>
	</div>
	</div>
	<!--/Location-->
	<!-- Docs Module -->
	<div class="AdminModule">
	<div class="ModBottom">
		<h4>documents</h4>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('documentPid', 'Document_List'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_DocList.gif" alt="List all documents" title="List all documents" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('List all documents', 'documentPid','', 'Document_List'); ?></div>
			<div class="clearer"></div>	
		</div>
	</div>
	</div>
	<!--/Docs-->
</div>
</div>
