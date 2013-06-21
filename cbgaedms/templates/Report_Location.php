<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php include_once('scripts/include.php'); ?>
<div id="EDMS">
	<h1>Location Profiles Report</h1>
	<h2 class="reports" title="reports"></h2>
	<div class="DownloadReport"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location_Profiles', array('download'=>true,'agencyStr'=>$this->controller->parameters->get('agencyStr'))); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/reports_download.gif" alt="Download location profile information" title="Download location profile information" border="0"/></a><?php $this->printAsLinkText('Download location profile information', 'reportsPid', 'Pid', 'Report_Location_Profiles', array('download'=>true,'agencyStr'=>$this->controller->parameters->get('agencyStr'))); ?></div>
	<div class="clearer"></div>
	<div id="reportsForm">
		<form action="<?php $this->printAsLink('reportsPid', 'Report_Location'); ?>" method="post">
			<div id="EditButtons">
				<?php $this->printAsLocationsSuggest('agency',$this->controller->parameters->get('agency')); ?>
				<input type="submit" value="Change Location" id="Submit" />
			</div>
		</form>
	</div>
	<!--  docs -->
		<div id="UsersList">
		<h3>location Profiles</h3>
		<div class="clearer"></div>
		<div id="UsersListWrap">
			<div class="btm">
			<div class="clearer"></div>
			<?php if ( $this->count() ) { ?>
				<?php require_once( 'Report_Location_Profiles_Detail.php' ); ?>
			<?php } else { ?>
				<?php require_once( 'Report_No_Detail.php' ); ?>
				<p><a href="javascript:history.go(-1);">%%%goBack%%%</a></p>
			<?php } ?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
	<div class="DownloadReport"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location_Profiles', array('download'=>true,'agencyStr'=>$this->controller->parameters->get('agencyStr'))); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/reports_download.gif" alt="Download location profile information" title="Download location profile information" border="0"/></a><?php $this->printAsLinkText('Download location profile information', 'reportsPid', 'Pid', 'Report_Location_Profiles', array('download'=>true,'agencyStr'=>$this->controller->parameters->get('agencyStr'))); ?></div>
</div>
