<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<div id="EDMS">
	<h1>EDMS Administration</h1>
	<div id="Admin">
	<!-- Location Module -->
	<div class="AdminModule">
	<div class="ModBottom">
		<h4>locations</h4>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsPid', 'Location_Search'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_search.gif" alt="Search all locations" title="Search all locations" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Search all locations', 'locationsPid', '', 'Location_Search'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_viewLocations.gif" alt="View all locations" title="View all locations"border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('View all locations', 'locationsPid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsPid', 'Locations_Form'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_addLocation.gif" alt="Create new location" title="Create new location"border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Create new location', 'locationsPid', '','Location_Form'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('locationsPid', 'Location_Search'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_search.gif" alt="Search by company silo" title="Search by company silo" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Search by company silo', 'locationsPid', '', 'Location_Search'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('siloPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_viewLocations.gif" alt="View all silos" title="View all silos"border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('View all silos', 'siloPid'); ?></div>
			<div class="clearer"></div>	
		</div>
	</div>
	</div>
	<!--/Location-->
	<!-- Users Module -->
	<div class="AdminModule">
	<div class="ModBottom">
		<h4>users</h4>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('usersPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_userList.gif" alt="List all users" title="List all users" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('List all users', 'usersPid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('usersPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_link.gif" alt="Link locations to front-end users" title="Link locations to front-end users" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Link locations to front-end users', 'usersPid'); ?><a href="#"></a></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('usersPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_editUser.gif" alt="Set user permissions" title="Set user permissions" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Set user permissions', 'usersPid'); ?></div>
			<div class="clearer"></div>	
		</div>
	</div>
	</div>
	<!--/Users-->
	<!-- Docs Module -->
	<div class="AdminModule">
	<div class="ModBottom">
		<h4>documents</h4>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('documentPid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_DocList.gif" alt="List all documents" title="List all documents" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('List all documents', 'documentPid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('docTypePid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_DocType.gif" alt="List all document types" title="List all document types" border="0"/></a></div> <div class="AdminLinkRight"><?php $this->printAsLinkText('List all document types', 'docTypePid'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('docTypePid', 'Document_Type_Form'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_createDoc.gif" alt="Create new document type" title="Create new document type" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Create new document type', 'docTypePid','', 'Document_Type_Form'); ?></div>
			<div class="clearer"></div>	
		</div>
		<div class="AdminLink">
			<div class="AdminIcon"><a href="<?php $this->printAsLink('docTypePid'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_editDoc.gif" alt="Set document types as required" title="Set document types as required" border="0"/></a></div>
			<div class="AdminLinkRight"><?php $this->printAsLinkText('Set document types as required', 'docTypePid'); ?></div>
			<div class="clearer"></div>	
		</div>
	</div>
	</div>
	<!--/Docs-->
	<?php require_once('Control_Panel_Reports_Detail.php'); ?>
</div>
</div>
