<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key, pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';


t3lib_extMgm::addPlugin(Array('LLL:EXT:bahag_photogallery/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","BAHAG Photo Gallery");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds_pi1.xml');


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_bahagphotogallery_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_bahagphotogallery_pi1_wizicon.php';


t3lib_extMgm::allowTableOnStandardPages("tx_bahagphotogallery_galleries");


t3lib_extMgm::addToInsertRecords("tx_bahagphotogallery_galleries");

$TCA["tx_bahagphotogallery_galleries"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_galleries",		
		"label" => "name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_bahagphotogallery_galleries.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, path, name, comment",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_bahagphotogallery_images");


t3lib_extMgm::addToInsertRecords("tx_bahagphotogallery_images");

$TCA["tx_bahagphotogallery_images"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_images",		
		"label" => "comment",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_bahagphotogallery_images.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, path, source, comment, info_pid",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_bahagphotogallery_exif_data_items");


t3lib_extMgm::addToInsertRecords("tx_bahagphotogallery_exif_data_items");

$TCA["tx_bahagphotogallery_exif_data_items"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:bahag_photogallery/locallang_db.php:tx_bahagphotogallery_exif_data_items",		
		"label" => "item_name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_bahagphotogallery_exif_data_items.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, item_name",
	)
);


if (TYPO3_MODE=="BE")	{
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_bahagphotogallery_cm1",
		"path" => t3lib_extMgm::extPath($_EXTKEY)."class.tx_bahagphotogallery_cm1.php"
	);
}
?>
