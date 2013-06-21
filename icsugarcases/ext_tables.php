<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_icsugarcases_sugar_portal_configuration'] = array (
	'ctrl' => array ( 'title'	 => 'LLL:EXT:icsugarcases/locallang_db.xml:tx_icsugarcases_sugar_portal_configuration',		
		'label'	 => 'server',	
		'tstamp'	=> 'tstamp',
		'crdate'	=> 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY server',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'		  => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_icsugarcases_sugar_portal_configuration.gif',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_icsugarcases_sugar_portal_configuration');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:icsugarcases/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_icsugarcases_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_icsugarcases_pi1_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/sugarcrm_case_portal/', 'SugarCRM Case Portal');
?>