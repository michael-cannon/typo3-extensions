<table cellpadding="0" cellspacing="0">
	<th class="first">Location</th>
	<th>Company Silo</th>
	<th>Country</th>
	<th>State</th>
	<th>City</th>
	<th>Address</th>
	<th>Address 2</th>
	<th>Postal Code</th>
	<th>Number of Employees</th>
	<th>Office Phone</th>
	<th>Incident Manager</th>
	<th>Alternate Incident Managers</th>
	<th>Building POC</th>
	<th>Building POC Phone</th>
	<th>Building POC Phone After Hours</th>
	<th>Building POC Alternate</th>
	<th>Building POC Alternate Phone</th>
	<th>Building POC Alternate Phone After Hours</th>
	<th>Emergency Call</th>
	<th>Emergency Bridgeline</th>
	<th>Passcode</th>
	<th>Chair Passcode</th>
	<th>Security Phone</th>
	<th>Phone 24/7 US</th>
	<th>Phone 24/7 non-US</th>
	<th>Administrators</th>
	<th>Viewers</th>
<?php
for($this->rewind(); $this->valid(); $this->next()) {
$entry = $this->current();
?>
<tr>
	<td><?php $entry->printAsLinkText($entry->asText('agency'),'locationsPid','View','Location_View',true); ?></td>
	<td><?php $entry->printAsText('silo'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('cn_short_en'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('zn_name_local'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('city'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('address'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('address2'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('postalcode'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('numberofemployees'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('officephone'); ?>&nbsp;</td>
	<td><?php $entry->printAsUsers('incidentmanager', true); ?>&nbsp;</td>
	<td><?php $entry->printAsUsers('alternateincidentmanagers', true); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingpoc'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingpocphone'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingpocphoneafterhours'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingalternatepoc'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingalternatepocphone'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('buildingalternatepocphoneafterhours'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('emergencycall'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('emergencybridgeline'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('passcode'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('chairpasscode'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('securityphone'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('phone247us'); ?>&nbsp;</td>
	<td><?php $entry->printAsText('phone247nonus'); ?>&nbsp;</td>
	<td><?php $entry->printAsUsers('administrator', true); ?>&nbsp;</td>
	<td><?php $entry->printAsUsers('viewers', true); ?>&nbsp;</td>
</tr>
<?php } ?>
</table>
