<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'access.php' );
$entry = $this->current();
$doctype = $entry->get('doctypeEntry');
$versionLatest = $entry->get('versionEntry');
$version = $entry->get('versions');
?>
<?php $saved = $this->controller->parameters->get('saved'); ?>
<div id="EDMS">
	<h1>Version <?php $version->printAsText('docversion'); ?> of <?php $entry->printAsText('doc'); ?></h1>
		<?php if ( $saved ) { ?>
			<div class="saved">Location Saved</div>
			<div class="clearer"></div>
		<?php } ?>
<!-- docs versions -->
		<div id="DocsVersions">
		<h3>version detail</h3>
		<div class="clearer"></div>
		<div id="DocsVersionsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<table cellpadding="0" cellspacing="0">
					<!--
					<th>Edit</th>
					-->
					<th class="first">Download</th>
					<th>Version Number</th>
					<th>Date</th>
					<th>Author</th>
					<th>Description</th>
					<tr>
						<!--
						<td><a href="<?php $version->printAsLink('documentPid','Document_Versions_Form',true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_editdetails.gif" alt="Edit this document" title="Edit this document" border="0"/></a></td>
						-->
						<td><a href="<?php $version->printAsLinkDownload(); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_download.gif" alt="Download this document" title="Download this document" border="0"/></a></td>
						<td><?php $version->printAsText('docversion'); ?></td>
						<td><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
						<td><?php $version->printAsText('feuser'); ?></td>
						<td><?php $version->printAsText('description'); ?>&nbsp;</td>
					</tr>
				</table>
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="uploadDoc"><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_upload.gif" alt="Upload updated version" title="Upload updated version" border="0"/></a><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>">Upload updated version</a></div>
				<?php } ?>
			</div>
		</div>
		</div>
<!-- /docs version-->
<!-- Doc Detail -->
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
					<div class="input"><?php $entry->printAsLinkText($entry->asText('doc'),'documentPid','View','Document_View',$entry->get('uid')); ?>&nbsp;</div>
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
					<div class="input"><?php $versionLatest->printAsText('docversion'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Created By:</label>
					<div class="input"><?php $entry->printAsText('feuser'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Created By Date:</label>
					<div class="input"><?php $entry->printAsDate('crdate', '%b %e, %Y %l:%M %p'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Last Modified By:</label>
					<div class="input"><?php $versionLatest->printAsText('feuser'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label> Last Modified:</label>
					<div class="input"><?php $versionLatest->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /Doc Detail -->
</div>
