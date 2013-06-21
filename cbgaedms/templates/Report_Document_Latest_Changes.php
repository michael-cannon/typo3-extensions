<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<div id="EDMS">
	<h1>Latest Document Changes Report</h1>
	<h2 class="reports" title="reports"></h2>
	<div class="DownloadReport"><a href="<?php $this->printAsLink('reportsPid', 'Report_Document_Latest_Changes', array('download'=>true, 'period'=>$this->controller->parameters->get('period'))); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/reports_download.gif" alt="Download all locations with location profile information" title="Download all locations with location profile information" border="0"/></a><?php $this->printAsLinkText('Download all locations with location profile information', 'reportsPid', 'Pid', 'Report_Document_Latest_Changes', array('download'=>true, 'period'=>$this->controller->parameters->get('period'))); ?></div>
	<div class="clearer"></div>
	<div id="reportsForm">
		<form action="<?php $this->printAsLink('reportsPid', 'Report_Document_Latest_Changes'); ?>" method="post">
			<div id="EditButtons">
				<?php $this->printAsPeriodSelect('period',$this->controller->parameters->get('period')); ?>
				<input type="submit" value="Change Report Period" id="Submit" />
			</div>
		</form>
	</div>
	<!--  docs -->
		<div id="Docs">
		<h3 class="long">Latest Document Changes</h3>
		<div class="clearer"></div>
		<div id="DocsWrap">
			<div class="btm">
			<div class="clearer"></div>
			<?php if ( $this->count() ) { ?>
				<?php require_once( 'Report_Document_Latest_Changes_Detail.php' ); ?>
			<?php } else { ?>
				<?php require_once( 'Report_No_Detail.php' ); ?>
				<p><a href="javascript:history.go(-1);">%%%goBack%%%</a></p>
			<?php } ?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
	<!-- / docs -->
	<div class="DownloadReport"><a href="<?php $this->printAsLink('reportsPid', 'Report_Document_Latest_Changes', array('download'=>true, 'period'=>$this->controller->parameters->get('period'))); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/reports_download.gif" alt="Download all locations with location profile information" title="Download all locations with location profile information" border="0"/></a><?php $this->printAsLinkText('Download all locations with location profile information', 'reportsPid', 'Pid', 'Report_Document_Latest_Changes', array('download'=>true, 'period'=>$this->controller->parameters->get('period'))); ?></div>
</div>
