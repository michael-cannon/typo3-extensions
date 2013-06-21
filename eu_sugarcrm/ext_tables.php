<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPlugin(Array('LLL:EXT:eu_sugarcrm/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:eu_sugarcrm/flexform_pi1.xml');

$tempColumns = Array (
    'tx_eusugarcrm_createlead' => Array (
        'exclude' => 1,
        'label' => 'LLL:EXT:eu_sugarcrm/locallang_db.php:tt_content.tx_eusugarcrm_createlead',
        'config' => Array (
			'type' => 'check',
        )
    ),
	'tx_eusugarcrm_mapping' => Array (
        'exclude' => 1,
        'label' => 'LLL:EXT:eu_sugarcrm/locallang_db.php:tt_content.tx_eusugarcrm_mapping',
        'config' => Array (
			'type' => 'text',
			'cols' => '30',	
			'rows' => '5',
        )
    ),
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_content','tx_eusugarcrm_createlead;;;;1-1-1,tx_eusugarcrm_mapping','mailform');
?>
