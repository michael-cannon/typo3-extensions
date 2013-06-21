<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

t3lib_extMgm::addPlugin(array('LLL:EXT:timtab_sociable/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

$TCA['tx_timtabsociable_shorturls'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:timtab_sociable/locallang_db.xml:tx_timtabsociable_shorturls',		
		'label'     => 'url',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'default_sortby' => 'ORDER BY url',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_timtabsociable_shorturls.gif',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_timtabsociable_shorturls');
t3lib_extMgm::addToInsertRecords('tx_timtabsociable_shorturls');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_timtabsociable_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_timtabsociable_pi1_wizicon.php';

t3lib_extMgm::addStaticFile($_EXTKEY,'static/timtab_sociable/', 'timtab_sociable');
?>
