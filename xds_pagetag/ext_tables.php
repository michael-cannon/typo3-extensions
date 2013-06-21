<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$tempColumns = Array (
	'tx_xdspagetag_pagetag' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:xds_pagetag/locallang_db.php:pages.tx_xdspagetag_pagetag',
		'config' => Array (
			'type' => 'input',
			'size' => '30',
			'max' => '100',
			'checkbox' => '',
			'eval' => 'trim,is_in,lower,unique',
			'is_in' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-,'
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_xdspagetag_pagetag;;;;1-1-1');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:xds_pagetag/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

?>