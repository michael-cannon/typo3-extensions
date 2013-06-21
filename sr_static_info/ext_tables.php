<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_srstaticinfo_div.php');

// Country reference data from ISO 3166-1

t3lib_div::loadTCA('static_countries');
$TCA['static_countries']['ctrl']['readOnly'] = 0;
$TCA['static_countries']['columns']['cn_iso_2']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_2';
$TCA['static_countries']['columns']['cn_iso_3']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_3';
$TCA['static_countries']['columns']['cn_iso_nr']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_nr';
$TCA['static_countries']['columns']['cn_currency_iso_nr']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_currency_iso_nr';
$TCA['static_countries']['columns']['cn_currency_iso_3']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_currency_iso_3';
$TCA['static_countries']['columns']['cn_address_format']['config']['items'][] = array('LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_address_format_9', '9');

// Currency reference data from ISO 4217

t3lib_div::loadTCA('static_currencies');
$TCA['static_currencies']['ctrl']['readOnly'] = 0;
$TCA['static_currencies']['columns']['cu_iso_3']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_currencies_item.cu_iso_3';
$TCA['static_currencies']['columns']['cu_iso_nr']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_currencies_item.cu_iso_nr';

// Language reference data from ISO 639-1

$tempColumns = Array (
		'lg_country_iso_nr' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_nr',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '20',
				'eval' => 'int',
				'default' => '0'
			)
		),
		'lg_country_iso_2' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_2',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '2',
				'eval' => '',
				'default' => ''
			)
		),
		'lg_country_iso_3' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_countries_item.cn_iso_3',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '3',
				'eval' => '',
				'default' => ''
			)
		),
		'lg_collate_locale' => Array (
			'label' => 'LLL:EXT:sr_static_info/locallang_db.php:static_languages_item.lg_collate_locale',
			'exclude' => '0',
			'config' => Array (
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => '',
				'default' => ''
			)
		),
);

t3lib_div::loadTCA('static_languages');
$TCA['static_languages']['ctrl']['readOnly'] = 0;
$TCA['static_languages']['columns']['lg_iso_2']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_languages_item.lg_iso_2';
t3lib_extMgm::addTCAcolumns('static_languages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('static_languages', 'lg_country_iso_nr,lg_country_iso_2,lg_country_iso_3,lg_collate_locale');
$TCA['static_languages']['interface']['showRecordFieldList'] .= ',lg_country_iso_nr,lg_country_iso_2,lg_country_iso_3,lg_collate_locale';

// Country subdivision reference data from ISO 3166-2

t3lib_div::loadTCA('static_country_zones');
$TCA['static_country_zones']['ctrl']['readOnly'] = 0;
$TCA['static_country_zones']['columns']['zn_country_iso_nr']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_country_zones_item.zn_country_iso_nr';
$TCA['static_country_zones']['columns']['zn_country_iso_2']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_country_zones_item.zn_country_iso_2';
$TCA['static_country_zones']['columns']['zn_country_iso_3']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_country_zones_item.zn_country_iso_3';
$TCA['static_country_zones']['columns']['zn_code']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_country_zones_item.zn_code';
$TCA['static_country_zones']['columns']['zn_name_local']['label'] = 'LLL:EXT:sr_static_info/locallang_db.php:static_country_zones_item.zn_name_local';



$TCA['static_taxes'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:sr_static_info/locallang_db.php:static_taxes.title',
		'label' => 'tx_name_en',
		'type' => 'tx_scope',	
		'readOnly' => 0,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY tx_name_en',
		'crdate' => 'crdate',
		'delete' => 'deleted',	
		'enablecolumns' => Array (		
			'disabled' => 'hidden',
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath('static_info_tables').'icon_static_currencies.gif',
	),
	'interface' => Array (
		'showRecordFieldList' => 'tx_name_en,tx_scope,tx_code,tx_country_iso_3,tx_country_iso_2,tx_country_iso_nr,tx_zn_code,tx_class,tx_rate,tx_priority,crdate,hidden,starttime,endtime'
   )
);

// ******************************************************************
// sys_language
// ******************************************************************


$typoVersion = t3lib_div::int_from_ver($GLOBALS['TYPO_VERSION']);
if ($typoVersion >= 3006000 ) {

	t3lib_div::loadTCA('sys_language');
	$TCA['sys_language']['columns']['static_lang_isocode']['config'] = array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
//			'foreign_table' => 'static_languages',
//			'foreign_table_where' => 'AND static_languages.pid=0 ORDER BY static_languages.lg_name_en',
			'itemsProcFunc' => 'tx_srstaticinfo_div->selectItemsTCA',
			'itemsProcFunc_config' => array (
				'table' => 'static_languages',
				'indexField' => 'uid',
				'prependHotlist' => 1,
				//	defaults:
				//'hotlistLimit' => 8,
				//'hotlistSort' => 1,
				//'hotlistOnly' => 0,
				//'hotlistApp' => TYPO3_MODE,
			),
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
	    );
};

?>