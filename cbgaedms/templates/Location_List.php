<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php require_once( 'access.php' ); ?>
<div id="EDMS">
	<h1>Locations</h1>
	<h2 class="locations" title="Locations"></h2>
	<?php if ( $isAdmin ) { ?>
	<div class="CreateDoc"><a href="<?php $this->printAsLink(false, 'Location_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new location" title="Create/add new location" border="0"/></a><a href="<?php $this->printAsLink(false, 'Location_Form', true); ?>">Create/add new location</a></div>
	<?php } ?>
	<div class="clearer"></div>
	<!--  docs -->
		<div id="Docs">
		<h3>locations</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
			<?php if ( $this->count() ) { ?>
				<?php $this->printResultBrowser(); ?>
				<table width="935" cellpadding="0" cellspacing="0">
					<th class="first">Edit</th>
					<th>Location</th>
					<th>Company Silo</th>
					<th>Country</th>
					<th>State/Province</th>
					<th>City</th>
					<th>Address</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
	<tr>
		<td><a href="<?php $entry->printAsLink(false, 'Location_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this location" title="Edit this location" border="0"/></a></td>
		<td><?php $entry->printAsLinkText($entry->asText('agency'),false,'View','Location_View',true); ?></td>
		<td><?php $entry->printAsText('silo'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('cn_short_en'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('zn_name_local'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('city'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('address'); ?>&nbsp;</td>
	</tr>
<?php } ?>

				</table>
	<?php $this->printResultBrowser(); ?>
		<?php } else { ?>
		<p class="notice">%%%noLocationsFound%%%</p>
		<p><a href="javascript:history.go(-1);">%%%goBack%%%</a></p>
		<?php } ?>
				<?php if ( $isAdmin ) { ?>
				<div class="create"><a href="<?php $this->printAsLink(false, 'Location_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/location_create.gif" alt="Create/add new location" title="Create/add new location" border="0"/></a><a href="<?php $this->printAsLink(false, 'Location_Form', true); ?>">Create/add new location</a></div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
</div>
