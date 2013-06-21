<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_location');

$TCA['tx_jwcalendar_location'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location',		
		'label' => 'location',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY location',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_location.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'location',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_organizer');

$TCA['tx_jwcalendar_organizer'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_organizer.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_categories');

$TCA['tx_jwcalendar_categories'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_categories',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_categories.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_exc_groups');

$TCA['tx_jwcalendar_exc_groups'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_groups',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_exc_groups.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_exc_events');

$TCA['tx_jwcalendar_exc_events'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_exc_events.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_jwcalendar_events');
t3lib_extMgm::addToInsertRecords('tx_jwcalendar_events');

$TCA['tx_jwcalendar_events'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'type' => 'type',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY begin DESC',
		'delete' => 'deleted',	
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
		),
		'useColumnsForDefaultValues' => 'image',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jwcalendar_events.gif",
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, category, begin, end, location, organiser, email, title, teaser, description, link, image, directlink,starttime,endtime',
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", 'FILE:EXT:jw_calendar/flexform_ds.xml');


t3lib_extMgm::addPlugin(Array('LLL:EXT:jw_calendar/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

//t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','JW Calendar Eventlist');



if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_jwcalendar_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_jwcalendar_pi1_wizicon.php';
?>