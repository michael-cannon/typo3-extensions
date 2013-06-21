<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php require_once( 'access.php' ); ?>
<?php $entry = $this->current(); ?>
<?php $agencyId = $entry->controller->parameters->get("agencyId"); ?>
<?php $saved = $this->controller->parameters->get('saved'); ?>
<div id="EDMS">
	<h1>User <?php $entry->printAsText('name'); ?></h1>
	<div id="Users">
	<h2 title="User Profile"></h2>
	<?php if ( $isAdmin || $isManager ) { ?>
	<div class="EditUser" title="Edit this user"><a href="<?php $entry->printAsLink(false, 'FE_Users_Form', array('uid' => $entry->asText('uid'),'agencyId' => $agencyId)); ?>">Edit user</a></div>
	<?php } ?>
	<?php if ( $isAdmin ) { ?>
	<div class="LinkedUsers" title="Edit location access"><a href="<?php $entry->printAsLink(false, 'User_Access_Form', true); ?>">Edit location access</a></div>
	<?php } ?>
	<div class="clearer"></div>
	<?php if ( 'notEditable' == $saved ) { ?>
		<div class="saved">User Not Editable</div>
		<div class="clearer"></div>
	<?php } elseif ( $saved ) { ?>
		<div class="saved">User Saved</div>
		<div class="clearer"></div>
	<?php } ?>
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
				<div class="entry">
					<label>Metro Rep:</label>
					<!-- This fills checkmetro with the value of metrorep 1 or 0 -->
					<div class="input"><?php if ($entry->asInteger('metrorep')) {echo 'Yes';} else { echo 'No'; } ?></div>
					<div class="clearer"></div>	
				</div>
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="EditUserGray" title="Edit this user"><a href="<?php $entry->printAsLink(false, 'FE_Users_Form', array('uid' => $entry->asText('uid'),'agencyId' => $agencyId)); ?>">Edit user</a></div>
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
					<div class="input"><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'incidentmanager' ); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="title">
				Location Alternate Incident Manager
				</div>
				<div class="entry">
					<div class="input"><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'alternateincidentmanagers' ); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="title">
				Location Viewer Access
				</div>
				<div class="entry">
					<div class="input"><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'viewers' ); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
				<?php if ( $isAdmin ) { ?>
				<div class="LinkedUsers" title="Edit location access"><a href="<?php $entry->printAsLink(false, 'User_Access_Form', true); ?>">Edit location access</a></div>
				<?php } ?>
				<div class="clearer"></div>
				</div>
				</div>
			</div>
		</div>
		<!-- /UserLocation -->
	</div>
</div>
