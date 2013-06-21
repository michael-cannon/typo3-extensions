<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php 
require_once( 'access.php' );
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
?>
<script language="JavaScript" src="typo3conf/ext/cbgaedms/templates/scripts/OptionTransfer.js"></script>
<div id="EDMS">
	<h1>Edit User Locations for <?php $entry->printAsText('first_name'); ?> <?php $entry->printAsText('last_name'); ?></h1>
	<div id="Users">
	<h2 title="User Profile"></h2>
	<div class="clearer"></div>
	<form id="edmsul" name="edmsul" action="<?php $this->printAsLink(); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
		<div id="EditButtonsUser">
				<input type="submit" value="User_Access_Edit" id="Save"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="User_Access_Cancel" id="Cancel"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="User_Access_Form" id="Reset"
					name="tx_cbgaedms[action]" />
		</div>
		<?php $this->printErrorList(); ?>
		<div class="clearer"></div>
	<!-- User -->
		<div id="UserProfile">
		<h3>user</h3>
		<div class="clearer"></div>
		<div id="UserProfileWrap">
			<div class="btm">
				<div class="title">
				Planner Profile
				</div>
				<div class="entry">
					<label>Name:</label>
					<div class="input"><?php $entry->printAsText('name'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Title:</label>
					<div class="input"><?php $entry->printAsText('title'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Email Address:</label>
					<div class="input"><?php $entry->printAsEmail('email'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Work Phone:</label>
					<div class="input"><?php $entry->printAsText('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Mobile Phone:</label>
					<div class="input"><?php $entry->printAsText('mobilephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Home Phone:</label>
					<div class="input"><?php $entry->printAsText('homephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="EditUserGray" title="Edit this user"><a href="<?php $entry->printAsLink(false, 'FE_Users_Form', true); ?>">Edit user</a></div>
				<?php } ?>
				</div>
			</div>
		</div>
		<!-- /User -->
	<!-- UserLocation -->
		<div id="LocationUsers">
		<h3>user locations</h3>
		<div class="clearer"></div>
		<div id="LocationUsersWrap">
			<div class="btm">
				<div class="title">
				Location Incident Manager
				</div>
				<div class="entry">
					<div class="input"><?php $entry->printAsDualSelectLocations( $entry->asInteger('uid'), 'incidentmanager' ); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="title">
				Location Alternate Incident Managers
				</div>
				<div class="entry">
					<div class="input"><?php $entry->printAsDualSelectLocations( $entry->asInteger('uid'), 'alternateincidentmanagers' ); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="title">
				Location Viewer Access
				</div>
				<div class="entry">
					<div class="input"><?php $entry->printAsDualSelectLocations( $entry->asInteger('uid'), 'viewers' ); ?></div>
					<div class="clearer"></div>	
				</div>	
				</div>
			</div>
		</div>
		<!-- /UserLocation -->
		<div class="clearer"></div>	
		<div id="EditButtonsUser">
				<input type="submit" value="User_Access_Edit" id="Save"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="User_Access_Cancel" id="Cancel"
					name="tx_cbgaedms[action]" />
				<input type="submit" value="User_Access_Form" id="Reset"
					name="tx_cbgaedms[action]" />
		</div>
	</form>
	</div>
</div>