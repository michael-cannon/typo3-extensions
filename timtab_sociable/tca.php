<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_timtabsociable_shorturls'] = array (
	'ctrl' => $TCA['tx_timtabsociable_shorturls']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,url,shorturl'
	),
	'feInterface' => $TCA['tx_timtabsociable_shorturls']['feInterface'],
	'columns' => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'url' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:timtab_sociable/locallang_db.xml:tx_timtabsociable_shorturls.url',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
				'eval' => 'required',
			)
		),
		'shorturl' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:timtab_sociable/locallang_db.xml:tx_timtabsociable_shorturls.shorturl',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, url, shorturl')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>