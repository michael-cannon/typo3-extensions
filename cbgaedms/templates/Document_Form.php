<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php include_once('scripts/include.php'); ?>
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
$versions = $entry->get('versions');
?>
<div id="EDMS">
	<h1>Create/Edit <?php $entry->printAsText('doc'); ?> Document</h1>
	<h2 class="docs" title="Documents"></h2>
	<div id="help"><a target="_blank" href="<?php $this->printAsLink('helpPidNewDocument'); ?>"></a></div>
	<div class="clearer"></div>
	<form id="edmsdd" name="edmsdd" action="<?php $this->printAsLink(); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
		<input type="hidden" name="tx_cbgaedms[agencyId]" value="<?php $entry->printAsInteger('agencyId'); ?>" />
	<div id="EditButtons">
			<input type="submit" value="Document_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Cancel" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
		<div class="clearer"></div>
		<div id="DocDetailEdit">
		<?php $this->printErrorList(); ?>
		<h3>document detail</h3>
		<div class="clearer"></div>
		<div id="DocDetailEditWrap">
			<div class="btm">
				<div class="entry">
					<label>Company / Location:</label>
					<div class="input"><?php $entry->printAsText('agency'); ?>&nbsp;</div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Document Name:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[doc]" value="<?php $entry->printAsForm('doc'); ?>" /><?php $entry->printAsError('doc'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Document Type:</label>
					<div class="input"><?php $entry->printAsDoctypeSelect('doctype', $entry->asInteger('doctype')); ?><?php $entry->printAsError('doctype'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Description:</label>
					<div class="input"><textarea rows="5" cols="30" name="tx_cbgaedms[description]"><?php $entry->printAsForm('description'); ?></textarea><?php $entry->printAsError('description'); ?></div>
					<div class="clearer"></div>	
				</div>
			<?php if ( ! is_object( $versions ) ) { ?>
				<div class="entry">
					<label>File:</label>
					<div class="input"><input name="tx_cbgaedms[newfile]" type="file" /><?php $entry->printAsError('newfile'); ?></div>
					<div class="clearer"></div>	
				</div>
			<?php } else { ?>
				<input name="tx_cbgaedms[newfile]" type="hidden" value="useversions" />
				<div class="entry">
					<label>Created By:</label>
					<div class="input"><?php $entry->printAsText('feuser'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Creation Date:</label>
					<div class="input"><?php $entry->printAsDate('crdate', '%b %e, %Y %l:%M %p'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Hide:</label>
					<div class="input"><input type="checkbox" name="tx_cbgaedms[hidden]" value="1" /></div>
					<div class="clearer"></div>	
				</div>
			<?php } ?>
				</div>
			</div>
		</div>
	<?php if ( is_object( $versions ) ) { ?>
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
					for($versions->rewind(); $versions->valid(); $versions->next()) {
						$version = $versions->current();
				?>
					<tr>
						<!--
						<td><a href="<?php $version->printAsLink('documentPid','Document_Versions_Form',true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_editdetails.gif" alt="Edit this document" title="Edit this document" border="0"/></a></td>
						-->
						<td><a href="<?php $version->printAsLinkDownload(); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_download.gif" alt="Download this document" title="Download this document" border="0"/></a></td>
						<td><?php $version->printAsLinkText($version->asText('docversion'),'documentPid','View','Document_Versions_View',true); ?></td>
						<td><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
						<td><?php $version->printAsText('feuser'); ?></td>
					</tr>
				<?php } ?>
				</table>
				<!--
				<div class="uploadDoc"><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_upload.gif" alt="Upload updated version" title="Upload updated version" border="0"/></a><a href="<?php $this->printAsCreateVersionLink($entry->asInteger('uid')); ?>">Upload updated version</a></div>
				-->
			</div>
		</div>
		</div>
		<!-- /docs version-->
	<?php } ?>
		<div class="clearer"></div>
		<div id="EditButtons">
			<input type="submit" value="Document_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Cancel" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Document_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
	</form>
	<div id="helpbottom"><a target="_blank" href="<?php $this->printAsLink('helpPidNewDocument'); ?>"></a></div>
</div>
