<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php
require_once( 'access.php' );
$entry = $this->current();
$agencies = $entry->get('agencies');
$agencyCount = $agencies->count();
if ( $agencyCount )
	$agency = $agencies->current();

?>
<h2>Emergency Document Management System (EDMS)</h2>
<div id="EDMS">
<div id="EDMShome">
	<ul>
	<div class="clearer"></div>
		<?php if ( 1 == $agencyCount ) { ?>
		<li><img src="typo3conf/ext/cbgaedms/templates/images/icons/mini_YourDocs.gif" alt="View Your Location Dashboard and Planning Document" title="View Your Location Dashboard and Planning Document" border="0"/><a href="<?php $this->printAsLink('locationsPid', 'Location_View', $agency->get('uid') ); ?>">View Your Location Dashboard and Planning Document</a></li>
		<?php } else { ?>
		<li><img src="typo3conf/ext/cbgaedms/templates/images/icons/mini_YourDocs.gif" alt="View Your Location Dashboard and Planning Documents" title="View Your Location Dashboard and Planning Documents" border="0"/><a href="<?php $this->printAsLink('locationsPid'); ?>">View Your Location Dashboard and Planning Documents</a></li>
		<?php } ?>
		<?php if ( $isAdmin ) { ?>
		<li><img src="typo3conf/ext/cbgaedms/templates/images/icons/mini_ControlPanel.gif" alt="Control Panel" title="Control Panel" border="0"/><a href="<?php $this->printAsLink('edmsHomePid'); ?>">Control Panel</a></li>
		<?php } ?>
	<div class="clearer"></div>
	</ul>		
</div>
</div>
