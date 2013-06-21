<?php
if (!defined ('TYPO3_MODE'))	 die ('Access denied.');

$TCA['tx_icsugarcases_sugar_portal_configuration'] = array (
	'ctrl' => $TCA['tx_icsugarcases_sugar_portal_configuration']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,username,password,server,file_storage,appname,component'
	),
	'feInterface' => $TCA['tx_icsugarcases_sugar_portal_configuration']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'	=> 'check',
				'default' => '0'
			)
		),
		'username' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.username',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'password' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.password',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,password',
			)
		),
		'server' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.server',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'file_storage' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.file_storage',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'appname' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.appname',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'component' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration.component',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, username, password,
server, file_storage, appname, component')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>
