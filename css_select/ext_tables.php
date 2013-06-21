<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}
	
	// Include the class to handle CSS files
	if (TYPO3_MODE=='BE') {
		include_once(t3lib_extMgm::extPath('css_select') . 'class.tx_cssselect_handlestylesheets.php');
	}
	
	// Temp TCA
	$tempColumns = Array (
		'tx_cssselect_stylesheets' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:css_select/locallang_db.php:pages.tx_cssselect_stylesheets',
			'config' => Array (
				'type' => 'select',
				'items' => Array (),
				'itemsProcFunc' => 'tx_cssselect_handleStylesheets->main',
				'size' => 10,
				'maxitems' => 10,
			)
		),
		'tx_cssselect_stop' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:css_select/locallang_db.php:pages.tx_cssselect_stop',
			'config' => Array (
				'type' => 'check',
				'default' => 0,
			)
		),
	);
	
	// Load pages TCA and add fields
	t3lib_div::loadTCA('pages');
	t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('pages','tx_cssselect_stylesheets;;;;1-1-1,tx_cssselect_stop');
?>
