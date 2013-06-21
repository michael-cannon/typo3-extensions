<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_lifegroups");

$TCA["tx_tfclifegroups_lifegroups"] = Array (
	"ctrl" => Array (
		"versioningWS" => TRUE,
		"origUid" => "t3_origuid",
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_lifegroups",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_lifegroups.gif",
	),
	"feInterface" => Array ( "fe_admin_fieldList" => "hidden, title, semesters, leader1_firstname, leader1_lastname, leader1_phone, leader1_email, leader2_firstname, leader2_lastname, leader2_phone, leader2_email, day, time, location, recurrence, category, ages, interests, url, descr, address, city, zone, zip, country",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_categories");

$TCA["tx_tfclifegroups_categories"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_categories",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_categories.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_interests");

$TCA["tx_tfclifegroups_interests"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_interests",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_interests.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_recurrences");

$TCA["tx_tfclifegroups_recurrences"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_recurrences",		
		"label" => "descr",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY descr",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_recurrences.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, descr",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_days");

$TCA["tx_tfclifegroups_days"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_days",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_days.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_semesters");

$TCA["tx_tfclifegroups_semesters"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_semesters",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_semesters.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_tfclifegroups_ages");

$TCA["tx_tfclifegroups_ages"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tfc_lifegroups/locallang_db.php:tx_tfclifegroups_ages",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_tfclifegroups_ages.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(Array('LLL:EXT:tfc_lifegroups/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','TFC Lifegroup Search');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:tfc_lifegroups/flexform_ds_pi1.xml');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';


t3lib_extMgm::addPlugin(Array('LLL:EXT:tfc_lifegroups/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","TFC Lifegroup Tree");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:tfc_lifegroups/flexform_ds_pi2.xml');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:tfc_lifegroups/locallang_db.php:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","TFC Lifegroup Leader");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:tfc_lifegroups/locallang_db.php:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi4/static/","TFC Lifegroup Updater");

$TCA['tx_tfclifegroups_lifegroups']['ctrl']['EXT']['wec_map'] = array (
	'isMappable' => 1,
	'addressFields' => array (
		'street' => 'address',
		'city' => 'city',
		'state' => 'zone',
		'zip' => 'zip',
		'country' => 'country',
	),
);
?>