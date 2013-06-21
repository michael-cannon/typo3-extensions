<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}
	
	/**
	 * Slideshows TCA
	 */
	$TCA['tx_newsshow_newsshows'] = Array (
		
		// Control section
		'ctrl' => $TCA['tx_newsshow_newsshows']['ctrl'],
		
		// Backend interface
		'interface' => Array (
			'showRecordFieldList' => 'hidden,title'
		),
		
		// Frontend interface
		'feInterface' => $TCA['tx_newsshow_newsshows']['feInterface'],
		
		// Fields configuration
		'columns' => Array (
			'hidden' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
				'config' => Array (
					'type' => 'check',
					'default' => '0'
				)
			),
			'title' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.title',
				'config' => Array (
					'type' => 'input',
					'size' => '30',
					'eval' => 'required',
				)
			),
			'type' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.type',
				'config' => Array (
					'type' => 'select',
					'items' => Array (
						Array('LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.type.I.0', '0'),
						Array('LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.type.I.1', '1'),
					),
					'size' => 1,
					'maxitems' => 1,
				)
			),
			'pictures' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.pictures',
				'config' => Array (
					'type' => 'flex',
					'ds' => Array(
						'default' => 'FILE:EXT:newsshow/flexform_ds.xml',
					),
				)
			),
			'dir_path' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.dir_path',
				'config' => Array (
					'type' => 'input',
					'size' => '30',
					'eval' => 'nospace',
				)
			),
			'dir_pictures' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.dir_pictures',
				'config' => Array (
					'type' => 'select',
					'items' => Array (),
					'itemsProcFunc' => 'tx_newsshow_handleJPGFiles->main',
					'size' => 10,
					'maxitems' => 50,
				)
			),
			'dir_url' => Array (
				'exclude' => 1,
				'label' => 'LLL:EXT:newsshow/locallang_db.php:tx_newsshow_newsshows.dir_url',
				'config' => Array (
					'type' => 'input',
					'size' => '30',
					'max' => '255',
					'eval' => 'nospace',
					'checkbox' => 1,
				)
			),
		),
		
		// Types configuration
		'types' => Array (
			'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;3-3-3, pictures;;;;3-3-3'),
			'1' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;3-3-3, dir_path;;;;3-3-3, dir_pictures,dir_url'),
		),
		
		// Palettes configuration
		'palettes' => Array (
			'1' => Array('showitem' => '')
		)
	);
?>
