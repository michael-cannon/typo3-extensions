<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


	// Adding Web>Info module for SEO management
if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_seobasics_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_seobasics_modfunc1.php',
		'LLL:EXT:seo_basics/locallang_db.xml:moduleFunction.tx_seobasics_modfunc1',
		'function',
		'online'
	);
}



	// Adding title tag field to pages TCA
$tmpCol = array(
	'tx_seo_titletag' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:seo_basics/locallang_db.xml:pages.titletag',
		'config' => Array (
			'type' => 'input',
			'size' => '70',
			'max' => '70',
			'eval' => 'trim'
		)
	)
);
t3lib_extMgm::addTCAcolumns('pages', $tmpCol, 1);
t3lib_extMgm::addTCAcolumns('pages_language_overlay', $tmpCol, 1);
if (t3lib_div::compat_version('4.2')) {
	t3lib_extMgm::addToAllTCAtypes('pages', 'tx_seo_titletag;;;;', "1", 'after:subtitle');
	t3lib_extMgm::addToAllTCAtypes('pages', 'tx_seo_titletag, nav_title, tx_realurl_pathsegment;;;;', "4,5", 'after:subtitle');
} else {
	t3lib_extMgm::addToAllTCAtypes('pages', 'tx_seo_titletag, keywords, description, nav_title;;;;', "1,4,5", 'after:subtitle');
	t3lib_extMgm::addToAllTCAtypes('pages', 'tx_seo_titletag;;;;', 2, 'before:keywords');
	t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', 'tx_seo_titletag, keywords, description, nav_title;;;;', "0", 'after:subtitle');
}
$TCA['pages_language_overlay']['interface']['showRecordFieldList'] .= ',tx_seo_titletag';

t3lib_extMgm::addStaticFile($_EXTKEY,'static', 'SEO Basics');
?>
