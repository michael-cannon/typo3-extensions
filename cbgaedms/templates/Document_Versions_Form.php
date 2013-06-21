<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) ) {
	// because of errors, data is spread out than centralized
	$entry = $this->offsetGet(0);
	$entry->overwriteArray($this->controller->parameters);
	$entry->set('hasErrors', $this->get('hasErrors'));
	$entry->set('_errorCount', $this->get('_errorCount'));
	$entry->set('_errorList', $this->get('_errorList'));
	$entry->set('errorMessageList', $this->get('errorMessageList'));
}
$doctype = $entry->get('doctypeEntry');
$version = $entry->get('versionEntry');
?>
<div id="EDMS">
	<h1>Upload New Version of <?php $entry->printAsText('doc'); ?></h1>
	<h2 class="docs" title="Documents"></h2>
	<div id="help"><a target="_blank" href="<?php $this->printAsLink('helpPidNewDocumentVersion'); ?>"></a></div>
	<div class="clearer"></div>
	<form id="edmsdd" name="edmsdd" action="<?php $this->printAsLink(); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="tx_cbgaedms[docId]" value="<?php $entry->printAsInteger('uid'); ?>" />
		<input type="hidden" name="tx_cbgaedms[agencyId]" value="<?php $entry->printAsInteger('agencyId'); ?>" />
		<div id="EditButtons">
				<input type="submit" value="Document_Versions_Edit" id="Save"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Document_Versions_Cancel" id="Cancel"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Document_Versions_Form" id="Reset"
					name="tx_cbgaedms[action]" />
			</div>
		<div class="clearer"></div>
		<!-- docs version -->
		<div id="DocsVersions">
		<h3>new version</h3>
		<div class="clearer"></div>
		<div id="DocsVersionsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<div class="entry">
					<label>Upload:</label>
					<div class="input"><input name="tx_cbgaedms[newfile]" type="file" /><?php $entry->printAsError('newfile'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Notes:</label>
					<div class="input"><textarea rows="5" cols="30" name="tx_cbgaedms[versiondescription]"><?php $entry->printAsForm('versiondescription'); ?></textarea><?php $entry->printAsError('versiondescription'); ?></div>
					<div class="clearer"></div>	
				</div>
			</div>
		</div>
		</div>
		<!-- /docs version-->
<!-- docs -->
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
		<!-- /docs -->
				<div id="EditButtons">
				<input type="submit" value="Document_Versions_Edit" id="Save"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Document_Versions_Cancel" id="Cancel"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="Document_Versions_Form" id="Reset"
					name="tx_cbgaedms[action]" />
			</div>
	</form>
	<div class="clearer"></div>
	<div id="helpbottom"><a target="_blank" href="<?php $this->printAsLink('helpPidNewDocumentVersion'); ?>"></a></div>
</div>
