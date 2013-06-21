<table width="935" cellpadding="0" cellspacing="0">
					<th>Name</th>
					<th>Email</th>
					<th>Office Phone</th>
					<th>Mobile Phone</th>
					<th>Manager of</th>
					<th>Viewer of</th>
					<th>Title</th>
					<th>Company</th>
					<th>Company Silo</th>
					<th>Region</th>
					<th>Address</th>
					<th>City</th>
					<th>Metro Rep</th>
					<th>State/Province</th>
					<th>Zip</th>
					<th>Country</th>

<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
	<tr>
		<td><?php $entry->printAsText('name'); ?>&nbsp;</td>
		<td><?php $entry->printAsEmail('email'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('officephone'); ?>&nbsp;</td>
		<td><?php $entry->printAsText('mobilephone'); ?>&nbsp;</td>
		<td><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'incidentmanager' );?>&nbsp;
			<?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'alternateincidentmanagers' );?></td>
		<td><?php $entry->printAsListLocationAccess( $entry->asInteger('uid'), 'viewers' ); ?>&nbsp;</td>
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
