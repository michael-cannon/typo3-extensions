<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'access.php' );
require_once( 'formErrors.php' );
$entry = $this->current();
if ( ! is_object( $entry ) )
	$entry = $this;
if ( '' == $entry->get('country') )
	$entry->set('country', 220);
?>
<?php include_once('scripts/include.php'); ?>
<div id="EDMS">
	<h1>Create/Edit <?php $entry->printAsText('agency'); ?> Location</h1>
	<div id="Locations">
		<h2>location profile</h2>
		<form id="edmsdt" name="edmsdt" method="post" action="<?php $this->printAsLink(); ?>">
			<input type="hidden" name="tx_cbgaedms[uid]" value="<?php $entry->printAsInteger('uid'); ?>" />
		<?php $this->printErrorList(); ?>
		<div id="help"><a target="_blank" href="<?php $this->printAsLink('helpPidLocationEdit'); ?>"></a></div>
	<div class="clearer"></div>
		<div id="EditButtons">
			<input type="submit" value="Location_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Location_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Location_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
		<div class="clearer"></div>
		<!-- location profile -->
		<div id="LocationProfileEdit">
		<h3>location</h3>
		<div class="clearer"></div>
		<div id="LocationProfileEditWrap">
			<div class="btm">
				<?php if ( $isAdmin ) { ?>
				<div class="entry" style="background-color:#bfbfbf; color: #000;">
					<label>Hide Location:</label>
					<div class="input"><input type="checkbox" class="checkbox" name="tx_cbgaedms[hidden]" value="1" /></div>
					<div class="clearer"></div>	
				</div>
				<?php } ?>
				<div class="entry">
					<label>Agency Name:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[agency]" value="<?php $entry->printAsForm('agency'); ?>" /><?php $entry->printAsError('agency'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Company Silo:</label>
					<div class="input"><?php $entry->printAsSilosSelect('agencysilo', $entry->asInteger('agencysilo')); ?><?php $entry->printAsError('agencysilo'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Country:</label>
					<div class="input"><?php $entry->printAsCountriesSelect('country', $entry->asInteger('country'), null, 'javascript:getStates()'); ?><?php $entry->printAsError('country'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Address 1:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[address]" value="<?php $entry->printAsForm('address'); ?>" /><?php $entry->printAsError('address'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Address 2:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[address2]" value="<?php $entry->printAsForm('address2'); ?>" /><?php $entry->printAsError('address2'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>City:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[city]" value="<?php $entry->printAsForm('city'); ?>" /><?php $entry->printAsError('city'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>State/Province:</label>
					<div class="input" id="states"><select name="tx_cbgaedms[state]" id="state"></select></div>
					<div class="input"><?php $entry->printAsError('state'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Postal Code:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[postalcode]" value="<?php $entry->printAsForm('postalcode'); ?>" /><?php $entry->printAsError('postalcode'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Telephone:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[officephone]" value="<?php $entry->printAsForm('officephone'); ?>" /><?php $entry->printAsError('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Number of Employees:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[numberofemployees]" value="<?php $entry->printAsForm('numberofemployees'); ?>" /><?php $entry->printAsError('numberofemployees'); ?></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /location profile -->
		<!-- emergency contact -->
		<div id="EmergencyContactEdit">
		<h3>emergency contact</h3>
		<div class="clearer"></div>
		<div id="EmergencyContactEditWrap">
			<div class="btm">
				<div class="entry">
					<label>Building Point of Contact (Primary):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingpoc]" value="<?php $entry->printAsForm('buildingpoc'); ?>" /><?php $entry->printAsError('buildingpoc'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Phone Number (business hours):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingpocphone]" value="<?php $entry->printAsForm('buildingpocphone'); ?>" /><?php $entry->printAsError('buildingpocphone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Phone Number (after hours):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingpocphoneafterhours]" value="<?php $entry->printAsForm('buildingpocphoneafterhours'); ?>" /><?php $entry->printAsError('buildingpocphoneafterhours'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Building Point of Contact (Alternate):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingalternatepoc]" value="<?php $entry->printAsForm('buildingalternatepoc'); ?>" /><?php $entry->printAsError('buildingalternatepoc'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Phone Number (business hours):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingalternatepocphone]" value="<?php $entry->printAsForm('buildingalternatepocphone'); ?>" /><?php $entry->printAsError('buildingalternatepocphone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Phone Number (after hours):</label>
					<div class="input"><input type="text" name="tx_cbgaedms[buildingalternatepocphoneafterhours]" value="<?php $entry->printAsForm('buildingalternatepocphoneafterhours'); ?>" /><?php $entry->printAsError('buildingalternatepocphoneafterhours'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Emergency Call in Number/Hotline:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[emergencycall]" value="<?php $entry->printAsForm('emergencycall'); ?>" /><?php $entry->printAsError('emergencycall'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Emergency Bridgeline:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[emergencybridgeline]" value="<?php $entry->printAsForm('emergencybridgeline'); ?>" /><?php $entry->printAsError('emergencybridgeline'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Passcode:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[passcode]" value="<?php $entry->printAsForm('passcode'); ?>" /><?php $entry->printAsError('passcode'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="strongentry">
					<label>Chairperson Passcode:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[chairpasscode]" value="<?php $entry->printAsForm('chairpasscode'); ?>" /><?php $entry->printAsError('chairpasscode'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Security Phone Number:</label>
					<div class="input"><input type="text" name="tx_cbgaedms[securityphone]" value="<?php $entry->printAsForm('securityphone'); ?>" /><?php $entry->printAsError('securityphone'); ?></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /emergency contact -->
		<div class="clearer"></div>
		<?php if ( $isAdmin ) { ?>
		<!-- Incident Manager -->
		<div id="IncidentMgrEdit">
		<h3>incident manager</h3>
		<div class="clearer"></div>
		<div id="IncidentMgrEditWrap">
			<div class="btm">
				<div class="entry">
					<label>Name:</label>
					<div class="input"><?php $entry->printAsUsersSelect('incidentmanager', $entry->asInteger('incidentmanager')); ?><?php $entry->printAsError('incidentmanager'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Alt. Incident Managers:</label>
					<div class="input"><?php $entry->printAsDualSelectUsers( $entry->asInteger('uid'), 'alternateincidentmanagers', $entry->get('newalternateincidentmanagersleft') ); ?><?php $entry->printAsError('alternateincidentmanagers'); ?></div>
					<div class="clearer"></div>	
				</div>
			</div>
		</div>
		</div>
		<!-- /Incident Manager -->
		<?php } else { ?>
			<input type="hidden" name="tx_cbgaedms[incidentmanager]" value="<?php $entry->printAsInteger('incidentmanager'); ?>" />
			<input type="hidden" name="tx_cbgaedms[alternateincidentmanagers]" value="<?php $entry->printAsText('alternateincidentmanagers'); ?>" />
		<?php } ?>
		<?php if ( $isAdmin ) { ?>
		<!-- Access Manager -->
		<div id="IncidentMgrEdit">
		<h3>access manager</h3>
		<div class="clearer"></div>
		<div id="IncidentMgrEditWrap">
			<div class="btm">
				<div class="entry">
					<label>Location Viewer Access:</label>
					<br />
					<div class="input"><?php $entry->printAsDualSelectUsers( $entry->asInteger('uid'), 'viewers', $entry->get('newviewersleft') ); ?></div>
					<div class="clearer"></div>	
				</div>	
			</div>
		</div>
		</div>
		<!-- /Access Manager -->
		<?php } else { ?>
			<input type="hidden" name="tx_cbgaedms[newviewersleft]" value="<?php $entry->printAsText('viewers'); ?>" />
		<?php } ?>
		<div class="clearer"></div>
		<div id="EditButtons">
			<input type="submit" value="Location_Edit" id="Save"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Location_ListClear" id="Cancel"
				name="tx_cbgaedms[action]" />
			<input type="submit" value="Location_Form" id="Reset"
				name="tx_cbgaedms[action]" />
		</div>
	</form>
	<div class="clearer"></div>
	<div id="helpbottom"><a target="_blank" href="<?php $this->printAsLink('helpPidLocationEdit'); ?>"></a></div>
	</div>
</div>
<script language="JavaScript">
<!--


function statesComplete(request) {
    var oldSelections = '<?php $entry->printAsInteger('state'); ?>';
    var outputDiv = document.getElementById('states');
    // alert(request.responseText);
    error = request.responseXML.getElementsByTagName('error');
    if(error[0]) { alert(error[0].firstChild.data); return; }
    content = request.responseXML.getElementsByTagName('content');
    // alert(content[0]);
    outputDiv.innerHTML = content[0].firstChild.data;

    element = document.getElementById('state');
    options = element.getElementsByTagName('option');

    for(i=0, iCount = options.length; i<iCount; i++) {
        if(oldSelections == options[i].value) {
            options[i].selected = 'selected';
        }
    }
}

function getStates() {
    var uri = '<?php $this->printAsLink('locationsPid','State_List'); ?>';
    var country = document.getElementById("country").value;
    params = 'countryId='+country;
    var fetchErrorMessage = 'Error fetching data from server';
    var outputDiv = document.getElementById('state');

    var ajaxRequest = new Ajax.Request(
    uri,
    {
    'method': 'post',
    'parameters': params,
    'onComplete': statesComplete
    });
}

// preload states
getStates();
-->
</script>
