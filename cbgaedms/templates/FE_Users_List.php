<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php include_once('scripts/include.php'); ?>
<div id="EDMS">
	<h1>Users</h1>
	<div id="Users">
	<h2 title="User Profile"></h2>
	<!--
	<div class="CreateUser" title="Create user"><a href="<?php $this->printAsLink(false, 'FE_Users_Form'); ?>">Create/add new user</a></div>
	-->
	<div class="clearer"></div>
	<div id="reportsForm">
		<form action="<?php $this->printAsLink('usersPid', 'FE_Users_List'); ?>" method="post">
			<div id="EditButtons">
				<?php $this->printAsUsersSuggest('user',$this->controller->parameters->get('user')); ?>
				<input type="submit" value="Lookup" id="Submit" />
						<p align = "left">	<a href="<?php $this->printAsLink('usersPid', 'FE_Users_List', array('download'=>true,'userStr'=>$this->controller->parameters->get('userStr'))); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/reports_download.gif" alt="Download all Users Information" title="Download all Users Information" border="0"/></a><?php $this->printAsLinkText('Download all Users Information', 'usersPid', 'Pid', 'FE_Users_List', array('download'=>true,'userStr'=>$this->controller->parameters->get('userStr'))); ?> </p>

			</div>
		</form>

	</div>

	<!-- Users List -->
		<div id="UsersList">
		<h3>all users</h3>
		<div class="clearer"></div>
		<div id="UsersListWrap">
			<div class="clearer"></div>
			<?php $this->printResultBrowser(); ?>
				<table width="2000" cellpadding="0" cellspacing="0">
					<th class="first">Edit Profile</th>
					<th>Name</th>
					<th>Email</th>
					<th>Office Phone</th>
					<th>Mobile Phone</th>
					<th>Manager of</th>
					<th>Viewer of</th>
					<th>Edit Access</th>
					<th>Title</th>
					<th>Company</th>
					<th>Company Silo</th>
					<th>Region</th>
					<th>Address</th>
					<th>City</th>
					<th>Metro Area Rep?</th>
					<th>State/Province</th>
					<th>Zip</th>
					<th>Country</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
	<tr>
		<td><a href="<?php $entry->printAsLink(false, 'FE_Users_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/user_edit.gif" alt="Edit this user" title="Edit this user" border="0"/></a></td>
		<td><?php $entry->printAsLinkText($entry->asText('name'),false,'View','FE_Users_View',true); ?></td>
		<td><?php $entry->printAsEmail('email'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('officephone'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('mobilephone'); ?>&nbsp;</td>
		<td><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'incidentmanager' );?>&nbsp;
			<?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'alternateincidentmanagers' );?></td>
		<td><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'viewers' ); ?>&nbsp;</td>
		<td><a href="<?php $entry->printAsLink(false, 'User_Access_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/user_link.gif" alt="Edit user access" title="Edit user access" border="0"/></a></td>
 		<td><?php $entry->printAsText('title'); ?>&nbsp;</td>
    <td><?php $entry->printAsText('company'); ?>&nbsp;</td>
    <td><?php $entry->printAsUserStatusType('status'); ?>&nbsp;</td>
    <td><?php $entry->printAsRegionType('region'); ?>&nbsp;</td>
    <td><?php $entry->printAsText('address'); ?>&nbsp;</td>
    <td><?php $entry->printAsText('city'); ?>&nbsp;</td>
    <td><?php if ($entry->asInteger('metrorep')) {echo 'Yes';} ?>&nbsp;</td>
    <td><?php $entry->printAsText('zone'); ?>&nbsp;</td>
    <td><?php $entry->printAsText('zip'); ?>&nbsp;</td>    
    <td><?php $entry->printAsText('static_info_country'); ?>&nbsp;</td>
	</tr>
<?php } ?>

				</table>
				<?php $this->printResultBrowser(); ?>
				<!--
				<div class="create"><a href="<?php $this->printAsLink(false, 'FE_Users_Form'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/user_create.gif" alt="Create/add new user" title="Create/add new user" border="0"/></a><a href="<?php $this->printAsLink(false, 'FE_Users_Form'); ?>">Create/add new user</a></div>
				-->
			</div>
		</div>
	<div class="clearer"></div>
	<!-- / Users List -->
</div>
