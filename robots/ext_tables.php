<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array(
	'tx_robots_flags' => array(
		'exclude' => true,
		'label' => 'LLL:EXT:robots/locallang_db.xml:pages.tx_robots_flags',
		'config' => array(
			'type' => 'check',
			'cols' => 2,
			'items' => array(
				array('LLL:EXT:robots/locallang_db.xml:pages.tx_robots_flags.I.0', ''),
				array('LLL:EXT:robots/locallang_db.xml:pages.tx_robots_flags.I.1', ''),
			),
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_robots_flags;;;;1-1-1', '1,2', 'before:TSconfig');

?>