<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php 
require_once( 'access.php' );
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
?>
<?php $agencyId = $entry->controller->parameters->get("agencyId"); ?>
<div id="EDMS">
	<h1>Edit User <?php $entry->printAsText('first_name'); ?> <?php $entry->printAsText('last_name'); ?></h1>
	<div id="Users">
	<h2 title="User Profile"></h2>
	<div class="clearer"></div>
	<form id="edmsdd" name="edmsdd" action="<?php $this->printAsLink(); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
		<input type="hidden" name="tx_cbgaedms[agencyId]" value="<?php echo $agencyId; ?>" />
		<?php $this->printErrorList(); ?>
	<div id="EditButtons">
			<input type="submit" value="FE_Users_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="FE_Users_Cancel" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="FE_Users_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
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
					<label>First Name:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[first_name]" value="<?php $entry->printAsForm('first_name'); ?>" /><?php $entry->printAsError('first_name'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Last Name:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[last_name]" value="<?php $entry->printAsForm('last_name'); ?>" /><?php $entry->printAsError('last_name'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Title:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[title]" value="<?php $entry->printAsForm('title'); ?>" /><?php $entry->printAsError('title'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Email Address:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[email]" value="<?php $entry->printAsForm('email'); ?>" /><?php $entry->printAsError('email'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Work Phone:</label>
					<div class="input"><input type="text"
					name="tx_cbgaedms[officephone]" value="<?php
					$entry->printAsForm('officephone'); ?>" /><?php
					$entry->printAsError('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Mobile Phone:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[mobilephone]" value="<?php $entry->printAsForm('mobilephone'); ?>" /><?php $entry->printAsError('mobilephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Home Phone:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[homephone]" value="<?php $entry->printAsForm('homephone'); ?>" /><?php $entry->printAsError('homephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<?php if ( $isAdmin ) { ?>
				<div class="entry">
					<label>Metro Rep:</label>
					<!-- This fills checkmetro with the value of metrorep 1 or 0 -->
					<div class="input"><input type="checkbox" name="tx_cbgaedms[metrorep]" value="1" <?php if ($entry->asInteger('metrorep')) {echo 'checked="checked"';} ?>/></div>
					<div class="clearer"></div>	
				</div>
				<?php } ?>
				<div class="entry">
					<label>Hide:</label>
					<div class="input"><input type="checkbox" name="tx_cbgaedms[hidden]" value="1" /></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /User -->
		<div class="clearer"></div>	
		<div id="EditButtons">
			<input type="submit" value="FE_Users_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="FE_Users_Cancel" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="FE_Users_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
	</form>
	</div>
</div>
