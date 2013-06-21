<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php include_once( 'access.php' ); ?>
<script src="typo3conf/ext/cbgaedms/templates/scripts/dhtml-suite-for-applications.js" type="text/javascript"></script>
<?php $entry = $this->current(); ?>
<?php $incidentmanager = $entry->get('incidentmanagerEntry'); ?>
<?php $alternateincidentmanagers = $entry->get('alternateincidentmanagerEntries'); ?>
<?php $saved = $this->controller->parameters->get('saved'); ?>
<div id="EDMS">
	<h1><?php $entry->printAsText('agency'); ?></h1>
	<div id="Locations">
		<h2>location Dashboard</h2>
		<?php if ( $isAdmin || $isManager ) { ?>
		<div class="EditProfile" title="Edit Location Profile"><a href="<?php $entry->printAsLink(false, 'Location_Form', true); ?>">Edit Location Profile</a></div>
		<?php } ?>
		<div class="LinkedUsers" title="Associated Location Users">Associated location users:
<?php
if ( $incidentmanager->asText('uid') )
	$entry->printAsLinkText($incidentmanager->asText('name'),'usersPid','View','FE_Users_View',array('uid' => $incidentmanager->asText('uid'),'agencyId' => $entry->asText('uid')));
else
	echo '%%%incidentManagerNotSet%%%';
?>
 | 
<?php
$aimcount = $alternateincidentmanagers->count();
if ( 0 < $aimcount ) {
	$max = 2;
	$run = 0;
	for($alternateincidentmanagers->rewind(); $alternateincidentmanagers->valid(); $alternateincidentmanagers->next()) {
		$alternateincidentmanager = $alternateincidentmanagers->current();
		$entry->printAsLinkText($alternateincidentmanager->asText('name'),'usersPid','View','FE_Users_View',array('uid' => $alternateincidentmanager->asText('uid'),'agencyId' => $entry->asText('uid')));

		// break after max links reached
		$run++;
		if ( $run >= $max ) {
			break;
		} elseif ( 1 < $aimcount ) {
			echo ' | ';
		}
	}
} else {
	echo '%%%alternateIncidentManagersNotSet%%%';
}
?>
</div>
		<div class="clearer"></div>
		<div class="jumplinks">Quick Links:
			<a href="#LocationProfile">Location Profile</a>
			| <a href="#LocationDocs">Documents</a>
			| <a href="#incident">Incident Management</a>
			| <a href="#emergency">Emergency Contacts</a>
			| <a href="#google">Google Map</a>
		</div>
		<div id="help"><a target="_blank" href="<?php $this->printAsLink('helpPidLocationDashboard'); ?>"></a></div>
	<div class="clearer"></div>
		<div class="clearer"></div>
		<?php if ( $saved ) { ?>
			<div class="saved">Location Saved</div>
			<div class="clearer"></div>
		<?php } ?>
		<!-- location profile -->
		<div id="LocationProfile">
		<h3>Location Profile</h3>
		<div class="clearer"></div>
		<div id="LocationProfileWrap">
			<div class="btm">
				<div class="entry">
					<label>Agency Name:</label>
					<div class="input"><?php $entry->printAsText('agency'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Company Silo:</label>
					<div class="input"><?php $entry->printAsText('silo'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Address 1:</label>
					<div class="input"><?php $entry->printAsText('address'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Address 2:</label>
					<div class="input"><?php $entry->printAsText('address2'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>City, State/Province:</label>
					<div class="input"><?php $entry->printAsText('city'); ?>, <?php $entry->printAsText('zn_name_local'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Country, Postal Code:</label>
					<div class="input"><?php $entry->printAsText('cn_short_en'); ?>, <?php $entry->printAsText('postalcode'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Telephone:</label>
					<div class="input"><?php $entry->printAsText('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Number of Employees:</label>
					<div class="input"><?php $entry->printAsText('numberofemployees'); ?></div>
					<div class="clearer"></div>	
				</div>
				</div>
			</div>
		</div>
		<!-- /location profile -->
		<!-- location docs -->
		<div id="LocationDocs">
		<h3>documents</h3>
		<div class="clearer"></div>
		<div id="LocationDocsWrap">
			<div class="btm">
			<div class="clearer"></div>
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="create"><a href="<?php $this->printAsCreateDocumentLink($entry->asInteger('uid')); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_createdetails.gif" alt="Create/add new document" title="Create/add new document" border="0"/></a><a href="<?php $this->printAsCreateDocumentLink($entry->asInteger('uid')); ?>">Create/add new document</a></div>
				<br />
				<?php } ?>
				<div class="instructions">Click any bolded column title to arrange the documents by that column.<br />To view additional documents use the scroll bar on the right.</div>
				<div>
				<table id="LocationDocsPaging" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<?php if ( $isAdmin || $isManager ) { ?>
					<td class="first">Edit</td>
					<?php } ?>
					<td><b>Document Name</b></td>
					<td><b>Document Type</b></td>
					<td>Version Number</td>
					<td><b>Last Modified</b></td>
					<td><b>Creation Date</b></td>
            </tr>
				</thead>
				<tbody>
<?php
	$docs = $entry->get('docs');
	for($docs->rewind(); $docs->valid(); $docs->next()) {
		$doc = $docs->current();
		$doctype = $doc->get('doctypeEntry');
		$version = $doc->get('versionEntry');
?>
	<tr>
		<?php if ( $isAdmin || $isManager ) { ?>
		<td><a href="<?php $doc->printAsLink('documentPid', 'Document_Form', true); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_edit.gif" alt="Edit this document" title="Edit this document" border="0"/></a></td>
		<?php } ?>
		<td><?php $doc->printAsLinkText($doc->asText('doc'),'documentPid','View','Document_View',true); ?></td>
		<td><?php $doctype->printAsText('doctype'); ?>&nbsp;</td>
		<td><?php $version->printAsLinkText($version->asText('docversion'),'documentPid','View','Document_Versions_View',true); ?></td>
		<td><?php $version->printAsDate('tstamp', '%b %e, %Y %l:%M %p'); ?></td>
		<td><?php $doc->printAsDate('crdate', '%b %e, %Y %l:%M %p'); ?></td>
	</tr>
<?php } ?>
				</tbody>
				</table>
				</div>
				<?php if ( $isAdmin || $isManager ) { ?>
				<div class="create"><a href="<?php $this->printAsCreateDocumentLink($entry->asInteger('uid')); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/docs_createdetails.gif" alt="Create/add new document" title="Create/add new document" border="0"/></a><a href="<?php $this->printAsCreateDocumentLink($entry->asInteger('uid')); ?>">Create/add new document</a></div>
				<?php } ?>
			</div>
		</div>
		</div>
		<div class="clearer"></div>
		<!-- /location docs -->
		<!-- Incident Manager -->
		<a name="incident"></a>
		<div id="IncidentMgr">
		<h3>Incident Management</h3>
		<div class="clearer"></div>
		<div id="IncidentMgrWrap">
			<div class="btm">
				<div class="entry">
					<label>Incident Manager:</label>
					<div class="input">
<?php
if ( $incidentmanager->asText('uid') )
	$entry->printAsLinkText($incidentmanager->asText('name'),'usersPid','View','FE_Users_View',array('uid' => $incidentmanager->asText('uid'),'agencyId' => $entry->asText('uid')));
else
	echo '%%%incidentManagerNotSet%%%';
?>
					</div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Office Telephone:</label>
					<div class="input"><?php $incidentmanager->printAsText('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Home Telephone:</label>
					<div class="input"><?php $incidentmanager->printAsText('homephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="strongentry">
					<label>Mobile Telephone:</label>
					<div class="input"><?php $incidentmanager->printAsText('mobilephone'); ?></div>
					<div class="clearer"></div>	
				</div>
<?php
if ( 0 < $aimcount ) {
	for($alternateincidentmanagers->rewind(); $alternateincidentmanagers->valid(); $alternateincidentmanagers->next()) {
		$alternateincidentmanager = $alternateincidentmanagers->current();
	?>
				<div class="entry">
					<label>Alt. Incident Manager:</label>
					<div class="input">
	<?php $entry->printAsLinkText($alternateincidentmanager->asText('name'),'usersPid','View','FE_Users_View',array('uid' => $alternateincidentmanager->asText('uid'),'agencyId' => $entry->asText('uid'))); ?>
					</div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Office Telephone:</label>
					<div class="input"><?php $alternateincidentmanager->printAsText('officephone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Home Telephone:</label>
					<div class="input"><?php $alternateincidentmanager->printAsText('homephone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="strongentry">
					<label>Mobile Telephone:</label>
					<div class="input"><?php $alternateincidentmanager->printAsText('mobilephone'); ?></div>
					<div class="clearer"></div>	
				</div>
	<?php
			}
} else {
	?>
				<div class="strongentry">
					<label>Alt. Incident Manager:</label>
					<div class="input">
	<?php echo '%%%alternateIncidentManagersNotSet%%%'; ?>
					</div>
					<div class="clearer"></div>	
				</div>
	<?php
}
?>
				<div class="entry">
					<label>Viewer Access:</label>
					<div class="input"><?php $entry->printAsListUserAccess( array('uid' => $entry->asInteger('uid'),'agencyId' => $entry->asInteger('uid')), 'viewers' ); ?></div>
					<div class="clearer"></div>	
				</div>	
				</div>
			</div>
		</div>
		<!-- /Incident Manager -->
		<!-- Emergency Contact -->
		<a name="emergency"></a>
		<div id="EmergencyContact">
		<h3>Emergency Contacts</h3>
		<div class="clearer"></div>
		<div id="EmergencyContactWrap">
			<div class="btm">
				<div class="entry">
					<label>Bldg / Property Mgmt Contact Name (Primary):</label>
					<div class="input"><?php $entry->printAsText('buildingpoc'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Phone Number (business hours):</label>
					<div class="input"><?php $entry->printAsText('buildingpocphone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Phone Number (after hours):</label>
					<div class="input"><?php $entry->printAsText('buildingpocphoneafterhours'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Bldg / Property Mgmt Contact Name (Alternate):</label>
					<div class="input"><?php $entry->printAsText('buildingalternatepoc'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Phone Number (business hours):</label>
					<div class="input"><?php $entry->printAsText('buildingalternatepocphone'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="strongentry">
					<label>Phone Number (after hours):</label>
					<div class="input"><?php $entry->printAsText('buildingalternatepocphoneafterhours'); ?></div>
					<div class="clearer"></div>	
				</div>	
				<div class="entry">
					<label>Emergency Call in Number/Hotline:</label>
					<div class="input"><?php $entry->printAsText('emergencycall'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Emergency Bridgeline:</label>
					<div class="input"><?php $entry->printAsText('emergencybridgeline'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>Passcode:</label>
					<div class="input"><?php $entry->printAsText('passcode'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Chairperson Passcode:</label>
					<div class="input"><?php $entry->printAsText('chairpasscode'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="strongentry">
					<label>Building Security Phone Number:</label>
					<div class="input"><?php $entry->printAsText('securityphone'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>24/7 Notification Center (US):</label>
					<div class="input"><?php $entry->printAsText('phone247us'); ?></div>
					<div class="clearer"></div>	
				</div>
				<div class="entry">
					<label>24/7 Notification Center (outside US):</label>
					<div class="input"><?php $entry->printAsText('phone247nonus'); ?></div>
					<div class="clearer"></div>	
				</div>
			</div>
		</div>
		</div>
		<!-- /Emergency Contact -->
		<!-- Google Map -->
		<a name="google"></a>
		<div id="GoogleMap">
		<h3>google map</h3>
		<div class="clearer"></div>
		<div id="GoogleMapWrap">
<!--
    <script src="https://www.google.com/jsapi?key=<?php $this->printAsText('googleMapApi'); ?>" type="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[
	google.load("maps", "2");
    var map = null;
    var geocoder = null;
    var zoomlevel = 15;

    function initialize() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(37.4419, -122.1419), zoomlevel);
        geocoder = new GClientGeocoder();
      }
    }

    function showAddress(address) {
      if (geocoder) {
        geocoder.getLatLng(
          address,
          function(point) {
            if (!point) {
				// alert(address + " not found");
				showAddress("<?php $entry->printAsText('city'); ?>, <?php $entry->printAsText('zn_name_local'); ?>, <?php $entry->printAsText('cn_short_en'); ?>");
            } else {
				map.checkResize();
				map.setCenter(point, zoomlevel);
				var marker = new GMarker(point);
				map.addOverlay(marker);
				map.addControl(new GSmallMapControl());
            }
          }
        );
      }
    }
    //]]>
    </script>
    <div id="map" style="width: 300px; height: 300px"><p>Coming to Internet Explorer users Fall 2008</p></div>
-->
    <div id="map">
		<p><a href="http://maps.google.com/?key=<?php $this->printAsText('googleMapApi'); ?>&q=<?php $entry->printAsText('address'); ?>, <?php $entry->printAsText('city'); ?>, <?php $entry->printAsText('zn_name_local'); ?>, <?php $entry->printAsText('cn_short_en'); ?>, <?php $entry->printAsText('postalcode'); ?>" target="_blank">View in Google Maps</a></p><br />
		<p>To view your location, select the link above which will open up Google maps in a new window.</p><br />
		<p align="center"><a href="http://maps.google.com/?key=<?php $this->printAsText('googleMapApi'); ?>&q=<?php $entry->printAsText('address'); ?>, <?php $entry->printAsText('city'); ?>, <?php $entry->printAsText('zn_name_local'); ?>, <?php $entry->printAsText('cn_short_en'); ?>, <?php $entry->printAsText('postalcode'); ?>" target="_blank"><img src="typo3conf/ext/cbgaedms/templates/images/GoogleMaps.gif" alt="Google Maps" border="0" /></a></p><br />
	</div>
		</div>
		</div>
		<!-- /Google -->
		<div class="clearer"></div>
		<div id="helpbottom"><a target="_blank" href="<?php $this->printAsLink('helpPidLocationDashboard'); ?>"></a></div>
	</div>
</div>
<!--
<script type="text/javascript">
//<![CDATA[
initialize();
showAddress("<?php $entry->printAsText('address'); ?>, <?php $entry->printAsText('city'); ?>, <?php $entry->printAsText('zn_name_local'); ?>, <?php $entry->printAsText('cn_short_en'); ?>, <?php $entry->printAsText('postalcode'); ?>");
//]]>
</script>
-->
<script type="text/javascript">
var tableWidgetObj = new DHTMLSuite.tableWidget();
tableWidgetObj.setTableId('LocationDocsPaging');
tableWidgetObj.setTableWidth(550);
tableWidgetObj.setTableHeight(338);
<?php if ( $isAdmin || $isManager ) { ?>
	// S string
	// N numeric
	tableWidgetObj.setColumnSort(Array(false,'S','S',false,'S','S'));
	tableWidgetObj.init();
	tableWidgetObj.sortTableByColumn(1);
<?php } else { ?>
	tableWidgetObj.setColumnSort(Array('S','S',false,'S','S'));
	tableWidgetObj.init();
	tableWidgetObj.sortTableByColumn(0);	// Initially sort the table by the first column
<?php } ?>
</script>
