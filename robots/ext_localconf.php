<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE == 'FE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['robots'] = 'EXT:robots/class.tx_robots_postproc.php:tx_robots_postproc->contentPostProcOutput';
}

?>