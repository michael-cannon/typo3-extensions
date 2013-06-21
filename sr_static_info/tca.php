<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['static_taxes'] = Array (
	'ctrl' => $TCA['static_taxes']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'tx_name_en,tx_scope,tx_code,tx_country_iso_3,tx_country_iso_2,tx_country_iso_nr,tx_zn_code,tx_class,tx_rate,tx_priority,crdate,hidden,starttime,endtime'
	),
	'columns' => Array (
		'hidden' => Array (		
			'exclude' => 0,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (		
			'exclude' => 0,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (		
			'exclude' => 0,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'tx_country_iso_nr' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.cn_iso_nr',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '20',
				'eval' => 'int',
				'default' => '0'
			)
		),
		'tx_country_iso_2' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.cn_iso_2',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '2',
				'eval' => '',
				'default' => ''
			)
		),
		'tx_country_iso_3' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.cn_iso_3',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '3',
				'eval' => 'required,trim',
				'default' => ''
			)
		),
		'tx_zn_code' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.zn_code',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '18',
				'max' => '45',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'tx_name_en' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_name_en',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'required,trim',
				'default' => ''
			)
		),
		'tx_code' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_code',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'tx_scope' => Array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_scope',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_scope.I.0', '1'),
					Array('LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_scope.I.1', '2'),
				),
			)
		),
		'tx_class' => Array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_class',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_class.I.0', '1'),
					Array('LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_class.I.1', '2'),
					Array('LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_class.I.2', '3'),
				),
			)
		),
		'tx_rate' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_rate',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'required,trim,double',
				'default' => '0',
				'range' => Array (
					'upper' => 1,
					'lower' => 0)
			)
		),
		'tx_priority' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes_item.tx_priority',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '2',
				'eval' => 'int',
				'default' => '1'
			)
		)
	),
	'types' => Array (
		'1' => Array (
			'showitem' => 'tx_name_en;;4;;1-1-1,--palette--;;1;;2-2-2,--palette--;;3;;3-3-3'
		),
		'2' => Array (
			'showitem' => 'tx_name_en;;4;;1-1-1,--palette--;;2;;2-2-2,--palette--;;3;;3-3-3'
		),
	),
	'palettes' => Array (
		'1' => Array(
			'showitem' => 'tx_country_iso_3,tx_country_iso_2,tx_country_iso_nr',
			'canNotCollapse' => '1'
		),
		'2' => Array(
			'showitem' => 'tx_country_iso_3,tx_country_iso_2,tx_country_iso_nr,tx_zn_code',
			'canNotCollapse' => '1'
		),
		'3' => Array(
			'showitem' => 'hidden,starttime,endtime',
			'canNotCollapse' => '1'
		),
		'4' => Array(
			'showitem' => 'tx_scope,tx_code,tx_class,tx_rate,tx_priority',
			'canNotCollapse' => '1'
		)
	)
);

?>