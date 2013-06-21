<!-- Reports Module -->
<div class="AdminModule">
<div class="ModBottom">
	<h4>reporting</h4>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location_Profiles'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="All locations with location profile information" title="All locations with location profile information" border="0"/></a></div>
		<div class="AdminLinkRight"><?php $this->printAsLinkText('All locations with location profile information', 'reportsPid', 'Pid', 'Report_Location_Profiles'); ?></div>
		<div class="clearer"></div>	
	</div>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php $this->printAsLink('reportsPid', 'Report_Document_Types'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="All document types" title="All document types" border="0"/></a></div>
		<div class="AdminLinkRight"><?php $this->printAsLinkText('All document types', 'reportsPid', 'Pid', 'Report_Document_Types'); ?></div>
		<div class="clearer"></div>	
	</div>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php $this->printAsLink('reportsPid', 'Report_Document_Latest_Changes'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="Latest document changes" title="Latest document changes" border="0"/></a></div>
		<div class="AdminLinkRight"><?php $this->printAsLinkText('Latest document changes', 'reportsPid', 'Pid', 'Report_Document_Latest_Changes'); ?></div>
		<div class="clearer"></div>	
	</div>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="Location lookup" title="Location lookup" border="0"/></a></div>
		<div class="AdminLinkRight"><?php $this->printAsLinkText('Location lookup', 'reportsPid', 'Pid', 'Report_Location'); ?></div>
		<div class="clearer"></div>	
	</div>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location_Documents'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="Documents for a single location – version numbers, modification dates" title="Documents for a single location – version numbers, modification dates" border="0"/></a></div>
		<div class="AdminLinkRight"><a href="<?php $this->printAsLink('reportsPid', 'Report_Location_Documents'); ?>">Documents for a single location – version numbers, modification dates</a></div>
		<div class="clearer"></div>
	</div>
	<div class="AdminLink">
		<div class="AdminIcon"><a href="<?php
		$this->printAsLink('notificationsPid',
		'Report_Notifications_List'); ?>"><img src="typo3conf/ext/cbgaedms/templates/images/icons/admin_reports.gif" alt="Notifications admin" title="Notifications admin" border="0"/></a></div>
		<div class="AdminLinkRight"><a href="<?php
		$this->printAsLink('notificationsPid', 'Report_Notifications_List'); ?>">Notifications admin</a></div>
		<div class="clearer"></div>
	</div>
</div>
</div>
<!--/Reports-->
