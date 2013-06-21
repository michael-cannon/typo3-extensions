<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_tqseo_pagetitle,tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix';
$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,tx_tqseo_inheritance';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][] = 'EXT:tq_seo/lib/class.linkparser.php:user_tqseo_linkparser->main';

?>