<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once('access.php');
$entry = $this->current();
$doctype = $entry->get('doctypeEntry');
$version = $entry->get('versionEntry');
?>
<?php $saved = $this->controller->parameters->get('saved'); ?>
<div id="EDMS">
	<h1><?php $entry->printAsText('doc'); ?></h1>
	<h2 class="docs" title="Documents"></h2>
<!-- docs -->
		<?php if ( $isAdmin || $isManager ) { ?>
		<div class="EditDocument" title="Edit document"><a href="<?php $entry->printAsLink(false, 'Document_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Upload updated version" title="Upload updated version" border="0"/></a>&nbsp;<a href="<?php $entry->printAsLink(false, 'Document_Form', true); ?>">Edit document detail</a></div>
		<?php } ?>
		<div class="clearer"></div>
		<div id="help" style="margin-top:15px;"><a target="_blank" href="<?php $this->printAsLink('helpPidDocumentDetail'); ?>"></a></div>
	<div class="clearer"></div>
		<div class="clearer"></div>
		<?php if ( $saved ) { ?>
			<div class="saved">Document Update Saved</div>
			<div class="clearer"></div>
		<?php } ?>
		<div id="DocDetail">
		<h3>document detail</h3>
		<div class="clearer"></div>
		<div id="DocDetailWrap">
			<div class="btm">
				<div class="entry">
					<label>Company / Location:</label>
					<div class="input"><?php $entry->printAsLinkText($entry->asText('agency'),'locationsPid','View','Location_View',$entry->get('agencyId')); ?>&nbsp;</div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Site Incident Manager:</label>
					<div class="input"><?php $entry->printAsText('incidentmanager'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Document Name:</label>
					<div class="input"><?php $entry->printAsText('doc'); ?>&nbsp;</div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Document Type:</label>
					<div class="input"><?php $doctype->printAsText('doctype'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Document Type Required:</label>
					<div class="input"><?php $doctype->asInteger('required') ?  print 'Yes' : print '&nbsp;' ; ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Latest Version:</label>
					<div class="input"><?php $version->printAsLinkText($version->asText('docversion'),'documentPid','View','Document_Versions_View',true); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Created By Name:</label>
					<div class="input"><?php $entry->printAsText('feuser'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Created By Date:</label>
					<div class="input"><?php $entry->printAsDate('crdate', '%b %e, %Y %l:%M %p'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Description:</label>
					<div class="input"><?php $entry->printAsText('description'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Last Modified By Name:</label>
					<div class="input"><?php $version->printAsText('feuser'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Last Modified By Date:</label>
					<div class="input"><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /User -->
		<!-- docs versions -->
		<div id="DocsVersions">
		<h3>versions</h3>
		<div class="clearer"></div>
		<div id="DocsVersionsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table width="315" cellpadding="0" cellspacing="0">
					<!--
					<th>Edit</th>
					-->
					<th class="first">Download</th>
					<th>Version Number</th>
					<th>Date</th>
					<th>Author</th>
				<?php
					$versions = $entry->get('versions');
					for($versions->rewind(); $versions->valid(); $versions->next()) {
						$version = $versions->current();
				?>
					<tr>
						<!--
						<td><a href="<?php $version->printAsLink('documentPid','Document_Versions_Form',true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_editdetails.gif" alt="Edit this document" title="Edit this document" border="0"/></a></td>
						-->
						<td><a href="<?php $version->printAsLinkDownload(); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_download.gif" alt="Download this document" title="Download this document" border="0" /></a></td>
						<td><?php $version->printAsLinkText($version->asText('docversion'),'documentPid','View','Document_Versions_View',true); ?></td>
						<td><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
						<td><?php $version->printAsText('feuser'); ?>&nbsp;</td>
					</tr>
				<?php } ?>
				</table>
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="uploadDoc"><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_upload.gif" alt="Upload updated version" title="Upload updated version" border="0"/></a><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>">Upload updated version</a></div>
				<?php } ?>
			</div>
		</div>
		</div>
		<div class="clearer"></div>
		<!-- /docs version-->
		<div class="clearer"></div>
		<div id="helpbottom"><a target="_blank" href="<?php $this->printAsLink('helpPidDocumentDetail'); ?>"></a></div>
</div>
